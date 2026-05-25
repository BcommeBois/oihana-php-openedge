<?php

namespace oihana\openedge\db\traits;

use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;

use oihana\models\enums\ModelParam;
use oihana\openedge\db\OpenEdgeQueryBuilder;
use oihana\openedge\enums\OpenEdge;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

trait OpenEdgeQueryBuilderTrait
{
    /**
     * The OpenEdge SQL query builder reference.
     * @var OpenEdgeQueryBuilder
     */
    public OpenEdgeQueryBuilder $openEdge ;

    /**
     * Initialize the internal OpenEdge query builder.
     *
     * @param array $init The configuration to initialize the OpenEdge builder.
     * @param Container|null $container The DI container reference.
     *
     * @return static
     *
     * @throws DependencyException
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function initializeOpenEdgeQueryBuilder( array $init = [] , ?Container $container = null ):static
    {
        $builder = $init[ ModelParam::QUERY_BUILDER ] ?? null ;

        if( is_string( $builder ) && isset( $container ) && $container->has( $builder ) )
        {
            $builder = $container->get( $builder ) ;
        }

        $this->openEdge = $builder instanceof OpenEdgeQueryBuilder ? $builder : new OpenEdgeQueryBuilder
        ([
            OpenEdge::CONTAINER => $container ,
            ...( is_array( $builder ) ? $builder : $init )
        ]) ;

        return $this ;
    }
}