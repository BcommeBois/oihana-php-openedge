<?php

namespace oihana\openedge\models\traits\documents;

use DateInvalidTimeZoneException;
use DateMalformedStringException;
use oihana\models\traits\EnsureKeysTrait;
use ReflectionException;

use DI\DependencyException;
use DI\NotFoundException;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\SimpleCache\InvalidArgumentException;

use oihana\enums\http\HttpMethod;
use oihana\exceptions\BindException;
use oihana\exceptions\ValidationException;
use oihana\logging\LoggerTrait;
use oihana\models\pdo\PDOTrait;
use oihana\models\traits\CacheableTrait;
use oihana\openedge\db\traits\OpenEdgeQueryBuilderTrait;
use oihana\openedge\enums\OpenEdge;
use oihana\reflect\exceptions\ConstantException;
use oihana\traits\DebugTrait;

use function oihana\core\objects\toAssociativeArray;
use function oihana\core\strings\compile;
use function oihana\core\strings\uniqueKey;

/**
 * Helper to fetch a single document/row using the OpenEdge SQL query builder.
 *
 * This trait assembles a SELECT query (SELECT + column list + FROM + WHERE [+ locking hint])
 * with the help of an OpenEdgeQueryBuilder instance, executes it via PDO, and optionally
 * leverages PSR-16 caching.
 *
 * It also supports a mock mode (debug only) where the built SQL is logged but not executed.
 *
 * Expectations for the consuming class (host):
 * - Must provide an OpenEdgeQueryBuilder instance via property $this->openEdge
 *   (typically from oihana\openedge\db\traits\OpenEdgeQueryBuilderTrait).
 * - Must provide PDO-based fetching via method fetch(string $query, array $bindVars): mixed
 *   (from oihana\models\pdo\PDOTrait).
 * - Should provide caching capabilities (hasCache/getCache/setCache/isCacheable) if caching is desired
 *   (from oihana\models\traits\CacheableTrait).
 * - May provide debug/mock capabilities and a PSR-3 logger (oihana\traits\DebugTrait & oihana\logging\LoggerTrait).
 *
 * @mixin PDOTrait
 * @mixin CacheableTrait
 * @mixin OpenEdgeQueryBuilderTrait
 * @mixin DebugTrait
 * @mixin LoggerTrait
 */
trait DocumentsGetTrait
{
    use EnsureKeysTrait ;

    /**
     * Fetch a single document/row matching the provided initialization options.
     *
     * Flow:
     * 1. Merge default binds with runtime binds via prepareBindVars($init).
     * 2. If caching is enabled and a cache hit exists for OpenEdge::ID, return the cached value.
     * 3. Build the SQL query using the OpenEdge query builder (select/columnList/from/where/withLockingHint).
     * 4. If mock mode is enabled (debug + mock flag), log and return null without executing.
     * 5. Execute the query with fetch($query, $bindVars) and cache the result by OpenEdge::ID if applicable.
     *
     * Accepted $init options (non-exhaustive):
     * - OpenEdge::BINDS        : array Bind variables to merge.
     * - OpenEdge::CACHEABLE    : bool Enable/disable caching for this call.
     * - OpenEdge::CACHE_KEY    : string The optional key to use to
     * - OpenEdge::COLUMNS      : array|string Column selection definition.
     * - OpenEdge::CONTEXT      : string|int HTTP-like context for the where() builder, defaults to HttpMethod::get.
     * - OpenEdge::FROM         : array|string Table/source definition.
     * - OpenEdge::ID           : scalar Cache key used when caching is enabled.
     * - OpenEdge::KEY          : null|string The name of the key attribute to use to retrieve the resource (By default OpenEdge::ID -> 'id').
     * - OpenEdge::LOCKING_HINT : string|null Locking hint.
     * - OpenEdge::MOCK         : bool Enable mock mode (no execution, logs only; requires debug=true).
     * - OpenEdge::WHERE        : array|string Where conditions definition.
     *
     * Return value:
     * - mixed|null The fetched row mapped by PDOTrait::fetch():
     *   - If a schema class is configured, an instance of that class; otherwise stdClass or array cast to object.
     *   - null when no row is found, or when mock mode is enabled.
     *
     * @param array $init Optional execution and SQL-builder options. See list above.
     * @return mixed|null The fetched document/row or null.
     *
     * @throws BindException When bind preparation fails in upstream logic.
     * @throws ContainerExceptionInterface From DI container interactions.
     * @throws DependencyException From DI resolution in nested components.
     * @throws InvalidArgumentException From PSR-16 cache operations.
     * @throws NotFoundException From DI resolution in nested components.
     * @throws NotFoundExceptionInterface From DI container interactions.
     * @throws ReflectionException
     * @throws ValidationException When upstream validation fails.
     * @throws DateInvalidTimeZoneException
     * @throws DateMalformedStringException
     * @throws ConstantException
     */
    public function get( array $init = [] ): mixed
    {
        $bindVars = $this->prepareBindVars( $init ) ;

        $key   = $init[ OpenEdge::KEY   ] ?? OpenEdge::ID ;
        $value = $init[ OpenEdge::VALUE ] ?? null ;

        if( isset( $value ) )
        {
            $bindVars[ $key ] = $value ;
        }

        $cacheable = $this->isCacheable( $init ) ;
        $context   = $init[ OpenEdge::CONTEXT ] ?? HttpMethod::get ;
        $bindVars  = $this->alterBindVars( $bindVars , $context ) ;
        $cacheKey  = $init[ OpenEdge::CACHE_KEY ] ?? uniqueKey( context: get_class( $this ) , binds: $bindVars ) ;

        if( $cacheable && $this->hasCache( $cacheKey ) )
        {
            $thing = $this->getCache( (string) $cacheKey ) ;
            if( is_array( $thing ) && is_string( $this->schema ) && class_exists( $this->schema ) )
            {
                $document = $this->hydrate( $thing , $this->schema ) ;
                $this->ensureDocumentKeys( $document , $init ) ;
                return $document ;
            }
            return null ;
        }

        $sql   = $this->openEdge ;
        $query = compile
        ([
            $sql->select( $init ) ,
            $sql->columnList( $init ) ,
            $sql->from( $init ) ,
            $sql->where( $init , $bindVars , $context ) ,
            $sql->withLockingHint( $init ) ,
        ] ) ;

        if( $this->isDebug( $init ) )
        {
            $this->debug( 'query    : ' . $query ) ;
            $this->debug( 'bindVars : ' . json_encode( $bindVars , JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) ) ;
            $this->debug( __METHOD__ ) ;
        }

        $throwable = $init [ OpenEdge::THROWABLE ] ?? $this->throwable ;
        $thing     = $this->fetch( $query , $bindVars , $throwable ) ;

        if( $cacheable && isset( $thing ) && isset( $cacheKey ) && !$this->hasCache( $cacheKey ) )
        {
            $this->setCache( (string) $cacheKey , toAssociativeArray( $thing ) ) ;
        }

        $this->ensureDocumentKeys( $thing , $init ) ;

        return $thing ;
    }
}