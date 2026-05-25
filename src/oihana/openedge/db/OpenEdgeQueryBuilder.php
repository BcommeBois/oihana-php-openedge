<?php

namespace oihana\openedge\db;

use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use oihana\logging\LoggerTrait;
use oihana\openedge\db\enums\Clause;
use oihana\openedge\db\enums\Predicate;
use oihana\openedge\db\traits\BindTrait;
use oihana\openedge\db\traits\ColumnTrait;
use oihana\openedge\db\traits\FromTrait;
use oihana\openedge\db\traits\GroupByTrait;
use oihana\openedge\db\traits\LookingHintTrait;
use oihana\openedge\db\traits\OrderByTrait;
use oihana\openedge\db\traits\WhereTrait;
use oihana\openedge\enums\OpenEdge;
use oihana\traits\ToStringTrait;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

use function oihana\core\strings\compile;
use function oihana\core\strings\func;

class OpenEdgeQueryBuilder
{
    /**
     * Creates a new OpenEdgeQueryBuilder instance.
     *
     * @param array $init The option to initialize the OpenEdge query builder.
     * - 'columns'  : Defines the default columns definitions of the query.
     * - 'conditions' : Defines the default conditions definitions of the query.
     * - 'container' : The DI container reference.
     * - 'from' : The table(s) expression or definitions of the query.
     * - 'joins' : Defines the default joins definitions of the query.
     * - 'logger' : The logger reference.
     * - 'orderBy' : Defines the default orderBy field of the query.
     * - 'queryID' : Defines the default default query identifier (by default the identifier is a random number.
     * - 'sortable' : Defines the sortable strategies for a map of specific properties.
     *
     * @throws DependencyException
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct( array $init = [] )
    {
        $this->container   = $init[ OpenEdge::CONTAINER    ] ?? null ;
        $this->counter     = $init[ OpenEdge::COUNTER      ] ?? OpenEdge::ALL ;
        $this->columns     = $init[ OpenEdge::COLUMNS      ] ?? $this->columns ;
        $this->distinct    = $init[ OpenEdge::DISTINCT     ] ?? false ;
        $this->from        = $init[ OpenEdge::FROM         ] ?? null ;
        $this->groupBy     = $init[ OpenEdge::GROUP_BY     ] ?? null ;
        $this->joins       = $init[ OpenEdge::JOINS        ] ?? null ;
        $this->lockingHint = $init[ OpenEdge::LOCKING_HINT ] ?? null ;
        $this->noReorder   = $init[ OpenEdge::NO_REORDER   ] ?? $this->noReorder ;
        $this->orderBy     = $init[ OpenEdge::ORDER_BY     ] ?? null ;
        $this->query       = $init[ OpenEdge::QUERY        ] ?? null ;
        $this->sortable    = $init[ OpenEdge::SORTABLE     ] ?? null ;
        $this->where       = $init[ OpenEdge::WHERE        ] ?? null ;

        $this->initializeLogger( $init , $this->container )
             ->initializeFacets( $init )
             ->initializeQueryID( $init ) ;
    }

    use BindTrait ,
        ColumnTrait ,
        FromTrait ,
        GroupByTrait ,
        LoggerTrait ,
        LookingHintTrait ,
        OrderByTrait ,
        ToStringTrait ,
        WhereTrait ;

    /**
     * The DI container reference.
     * @var ?Container
     */
    public ?Container $container ;

    /**
     * The COUNT() argument value (by default '*').
     * @var string
     */
    public string $counter = OpenEdge::ALL ;

    /**
     * Indicates if the DISTINCT predicates is used in the SELECT queries.
     * @var bool
     */
    public bool $distinct = false ;

    /**
     * Indicates if the default query expression.
     * @var ?string
     */
    public ?string $query = null ;

    /**
     * Returns the total number of rows with the specific query definition.
     * @param array $init
     * @return string
     */
    public function count( array $init = [] ) :string
    {
        return func( Clause::COUNT , $init[ OpenEdge::COUNTER ] ?? $this->counter ) ;
    }

    /**
     * Returns a SELECT clause expression.
     * ```
     * SELECT [ ALL | DISTINCT ] [TOP n]
     * ```
     * @param array|string|null $init
     * @return string
     * @example
     * ```php
     * echo $openEdge->select() ; // SELECT *
     * ```php
     */
    public function select( null|array|string $init = null ) :string
    {
        if( is_string( $init ) )
        {
            return $init ;
        }

        $expressions = [ Clause::SELECT ] ;

        $distinct = $init[ OpenEdge::DISTINCT ] ?? $this->distinct ;
        if( $distinct )
        {
            $expressions[] = Predicate::DISTINCT ;
        }

        $top = $init[ OpenEdge::TOP ] ?? null ;
        if( isset( $top ) )
        {
            $expressions[] = compile( [ Clause::TOP , $top ] ) ;
        }

        return compile( $expressions ) ;
    }
}