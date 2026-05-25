<?php

namespace oihana\openedge\models\traits\documents;

use Generator;

use DateInvalidTimeZoneException;
use DateMalformedStringException;

use DI\DependencyException;
use DI\NotFoundException;

use oihana\openedge\enums\OpenEdge;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\SimpleCache\InvalidArgumentException;

use org\schema\Thing;

use oihana\enums\Char;
use oihana\exceptions\BindException;
use oihana\exceptions\ValidationException;
use oihana\reflect\exceptions\ConstantException;

use ReflectionException;
use function oihana\core\objects\toAssociativeArray;

/**
 * Provides functionality to build queries and retrieve collections of
 * OpenEdge "Thing" documents, with optional caching and generator support.
 *
 * @package oihana\openedge\models\traits\documents
 */
trait DocumentsStreamTrait
{
    use DocumentsListTrait ;

    /**
     * Retrieves a list of Thing objects as a generator.
     *
     * This allows iterating over results without loading the entire collection
     * into memory at once. Objects may also be cached during iteration if caching is enabled.
     *
     * @param array $init Optional initialization parameters for filtering, sorting, etc.
     *
     * @return Generator Yields `Thing` objects one by one.
     *
     * @throws BindException
     * @throws ConstantException
     * @throws ContainerExceptionInterface
     * @throws DateInvalidTimeZoneException
     * @throws DateMalformedStringException
     * @throws DependencyException
     * @throws InvalidArgumentException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ValidationException
     * @throws ReflectionException
     */
    public function stream( array $init = [] ): Generator
    {
        $bindVars  = $this->prepareBindVars( $init ) ;
        $cacheable = $this->isCacheable( $init ) ;
        $query     = $this->buildListQuery( $init , $bindVars ) ;
        $throwable = $init [ OpenEdge::THROWABLE ] ?? $this->throwable ;
        $generator = $this->fetchAllAsGenerator( $query , $bindVars , $throwable ) ;

        if ( $cacheable )
        {
            foreach ( $generator as $thing )
            {
                if ( $thing instanceof Thing && isset($thing->id) && $thing->id != Char::EMPTY && !$this->hasCache( (string) $thing->id ) )
                {
                    $this->setCache( ( string ) $thing->id , toAssociativeArray( $thing ) ) ;
                }
                yield $thing ;
            }
        }
        else
        {
            yield from $generator ;
        }
    }
}