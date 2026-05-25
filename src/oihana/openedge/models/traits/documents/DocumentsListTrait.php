<?php

namespace oihana\openedge\models\traits\documents;

use DateInvalidTimeZoneException;
use DateMalformedStringException;

use DI\DependencyException;
use DI\NotFoundException;

use oihana\models\traits\EnsureKeysTrait;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\SimpleCache\InvalidArgumentException;

use org\schema\Thing;

use oihana\enums\Char;
use oihana\exceptions\BindException;
use oihana\exceptions\ValidationException;
use oihana\enums\http\HttpMethod;
use oihana\models\pdo\PDOTrait;
use oihana\models\traits\CacheableTrait;
use oihana\openedge\db\traits\OpenEdgeQueryBuilderTrait;
use oihana\openedge\enums\OpenEdge;
use oihana\reflect\exceptions\ConstantException;

use function oihana\core\objects\toAssociativeArray;
use function oihana\core\strings\compile;
use function oihana\openedge\db\helpers\limit;

/**
 * Provides functionality to build queries and retrieve collections of
 * OpenEdge "Thing" documents, with optional caching and generator support.
 *
 * @package oihana\openedge\models\traits\documents
 */
trait DocumentsListTrait
{
    use CacheableTrait ,
        EnsureKeysTrait ,
        OpenEdgeQueryBuilderTrait ,
        PDOTrait ;

    /**
     * Builds the OpenEdge SQL query for list operations based on initialization parameters.
     *
     * This method compiles a query using the OpenEdge query builder,
     * including SELECT, FROM, WHERE, GROUP BY, ORDER BY, LIMIT, and optional locking hints.
     * If a custom query is provided in `$init[OpenEdge::QUERY]`, it will be used directly.
     *
     * @param array $init      Initialization options for the query, e.g. context, filters, order, etc.
     * @param array &$bindVars Reference to an array where bind variables will be collected.
     *
     * @return string Compiled SQL query ready for execution.
     *
     * @throws BindException If binding variables fails.
     * @throws ConstantException If a required constant is missing or invalid.
     * @throws DateInvalidTimeZoneException If a date field has an invalid timezone.
     * @throws DateMalformedStringException If a date field has a malformed string.
     * @throws DependencyException If a dependency cannot be resolved from the container.
     * @throws NotFoundException If a required dependency is not found.
     * @throws ValidationException If any initialization parameter fails validation.
     */
    protected function buildListQuery( array $init , array &$bindVars ): string
    {
        $context  = $init[ OpenEdge::CONTEXT ] ?? HttpMethod::list ;
        $sql      = $this->openEdge ;

        $query = $init[ OpenEdge::QUERY ] ?? $sql->query ?? null ;

        if( !is_string( $query ) || empty( $query ) )
        {
            $select  = $sql->select( $init ) ;
            $column  = $sql->columnList( $init ) ;
            $from    = $sql->from( $init ) ;
            $groupBy = $sql->groupBy( $init ) ;
            $where   = $sql->where( $init , $bindVars, $context ) ;
            $order   = $sql->orderBy( $init ) ;
            $limit   = limit( $init ) ;
            $locking = $sql->withLockingHint( $init ) ;

            $query = compile
            ([
                $select ,
                $column ,
                $from ,
                $where ,
                $groupBy ,
                $order ,
                $limit ,
                $locking ,
            ] ) ;
        }

        // echo '> query    : ' . $query . PHP_EOL . PHP_EOL ;
        // echo '> bindVars : ' . json_encode( $bindVars ) . PHP_EOL . PHP_EOL ;
        // $this->debug( 'documents list :: query : ' . $query ) ;

        if( $this->isDebug( $init ) )
        {
            $this->debug( __METHOD__ ) ;
            $this->debug( 'bindVars : ' . json_encode( $bindVars ) ) ;
            $this->debug( 'query    : ' . $query ) ;
        }

        // $this->debug( 'query    : ' . $query ) ;

        return $query ;
    }

    /**
     * Retrieves a list of Thing objects.
     *
     * The method executes the query built by `buildListQuery`, fetches all results,
     * and optionally caches them if caching is enabled. Only objects of type
     * `Thing` with a valid `id` are cached.
     *
     * @param array $init Optional initialization parameters for filtering, sorting, etc.
     *
     * @return array List of `Thing` objects.
     *
     * @throws BindException
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws InvalidArgumentException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ValidationException
     * @throws DateInvalidTimeZoneException
     * @throws DateMalformedStringException
     * @throws ConstantException|\ReflectionException
     */
    public function list( array $init = [] ) :array
    {
        $bindVars  = $this->prepareBindVars ( $init ) ;
        $cacheable = $this->isCacheable     ( $init ) ;
        $throwable = $init [ OpenEdge::THROWABLE ] ?? $this->throwable ;
        $query     = $this->buildListQuery( $init , $bindVars ) ;
        $things    = $this->fetchAll( $query , $bindVars , $throwable ) ;

        if( $cacheable && count( $things ) > 0 )
        {
            $elements = array_filter
            (
                $things ,
                fn( $thing ) => $thing instanceof Thing && isset( $thing->id ) && $thing->id != Char::EMPTY && !$this->hasCache( (string) $thing->id )
            ) ;

            if( count( $elements ) > 0 )
            {
                foreach( $elements as $thing )
                {
                    $this->setCache( (string) $thing->id , toAssociativeArray( $thing ) ) ;
                }
            }
        }

        $this->ensureDocumentKeys( $things , $init ) ;

        return $things ;
    }
}