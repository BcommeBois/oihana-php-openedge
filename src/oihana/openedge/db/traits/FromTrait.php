<?php

namespace oihana\openedge\db\traits;

use DI\DependencyException;
use DI\NotFoundException;

use oihana\enums\Char;
use oihana\openedge\db\enums\Clause;
use oihana\openedge\db\enums\Join;
use oihana\openedge\enums\OpenEdge;

use function oihana\core\arrays\isAssociative;
use function oihana\core\strings\compile;
use function oihana\openedge\db\helpers\asAlias;
use function oihana\openedge\db\helpers\searchCondition;

trait FromTrait
{
    use WhereTrait ;

    /**
     * The table definition to build the FROM clause.
     * @var null|string|array
     */
    public null|string|array $from ;

    /**
     * The 'joins' definitions to build the query.
     * @var ?array
     */
    public mixed $joins ;

    /**
     * Disables join order optimization for the FROM clause.
     * Use { NO REORDER } when you choose to override the join order chosen by the optimizer.
     * The braces are part of the syntax for this optional clause.
     * @var bool
     */
    public bool $noReorder = false ;

    /**
     * Generates the FROM clause of the query.
     * @param array $init An associative array to configure the FROM clause with the following keys.
     * @param bool $compileJoinedTables Indicates if the jointed tables are compiled (true by default).
     * <ul>
     *     <i>array from - The from definition to sets the table(s)</i>
     *     <i>array joins - The joined table(s) definitions</i>
     * </ul>
     * @return string
     * @throws DependencyException
     * @throws NotFoundException
     * @example
     * ### Basic Example
     * ```
     * $openEdge->from( [ OpenEdge::FROM => 'PUB.produit AS products' ] ) -> FROM PUB.produit AS products
     * $openEdge->from( [ OpenEdge::FROM => [ OpenEdge::TABLE => 'DB.produit AS products' ] ] ) -> FROM DB.produit AS products
     * $openEdge->from( [ OpenEdge::FROM => [ OpenEdge::TABLE => 'DB.produit' , OpenEdge::ALIAS => 'products' ] ] ) -> FROM DB.produit AS products
     * ```
     *
     * ### Cross joined tables :
     * ```
     * $openEdge->from( [ OpenEdge::FROM => [ [ OpenEdge::TABLE => 'DB.produit' , OpenEdge::ALIAS => 'products' ] , 'DB.services as services'  ] )
     * -> FROM DB.produit as products, DB.services as services
     * ```
     * Important: use joins to combine multiple tables (more flexible)
     *
     * ### Joins
     * $init =
     * [
     *     OpenEdge::FROM => 'DB.products AS products' ,
     *     OpenEdge::JOINS =>
     *     [
     *         [
     *             OpenEdge::JOIN       => Join::INNER ,
     *             OpenEdge::TABLE      => 'DB.offers' ,
     *             OpenEdge::ALIAS      => 'offers',
     *             OpenEdge::CONDITIONS =>
     *             [
     *                  [ [ OpenEdge::COLUMN => 'products.id' ] , Comparator::EQUAL , [ OpenEdge::COLUMN => 'offers.id' ] ] ,
     *             ]
     *         ],
     *     ]
     * ] ;
     * $openEdge->from( $init ) ;
     * // FROM DB.products AS products
     * // INNER JOIN DB.offers AS offers ON products.id = offers.id
     */
    public function from( array $init = [] , bool $compileJoinedTables = true ) :string
    {
        $definition = $init[ OpenEdge::FROM ] ?? $this->from ;

        if( is_array( $definition ) )
        {
            if( isAssociative( $definition ) )
            {
                $definition = $this->tableRef( $definition ) ;
            }
            else
            {
                $tables = [] ;

                foreach( $definition as $current )
                {
                    $table = $this->tableRef( $current ) ;
                    if( $table != Char::EMPTY )
                    {
                        $tables[] = $table ;
                    }
                }

                if( count( $tables ) > 0 )
                {
                    $definition = compile( $tables , Char::COMMA . Char::SPACE ) ;
                }
            }
        }

        if( is_string( $definition ) && $definition !== Char::EMPTY )
        {
            $expression = [ Clause::FROM , $definition ] ;

            if( $compileJoinedTables )
            {
                $joins = $this->joinedTables( $init ) ;
                if( $joins != Char::EMPTY )
                {
                    $expression[] = $joins ;
                }
            }

            $noReorder = $init[ OpenEdge::NO_REORDER ] ?? $this->noReorder ;
            if( $noReorder === true )
            {
                $expression[] = Clause::NO_REORDER ;
            }
            return compile( $expression );
        }

        return Char::EMPTY ;
    }

    /**
     * Generates the joins SQL clause of the query.
     * @param array $init
     * @return string
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function joinedTables( array $init = [] ) :string
    {
        $joins = $init[ OpenEdge::JOINS ] ?? $this->joins ;

        if( is_string( $joins ) && $joins !== Char::EMPTY )
        {
            return $joins ;
        }
        else if( is_array( $joins ) && count( $joins ) > 0 )
        {
            $definitions = [] ;
            foreach( $joins as $definition )
            {
                if( is_string( $definition ) )
                {
                    $definitions[] = $definition ;
                    continue ;
                }

                $table = $definition[ OpenEdge::TABLE ] ?? null ;
                if( is_string( $table ) && $table !== Char::EMPTY )
                {
                    $alias  = $definition[ OpenEdge::ALIAS  ] ?? null  ;
                    $join   = $definition[ OpenEdge::JOIN ] ?? Join::INNER ;
                    $definitions[] = compile
                    ([
                        $join ,
                        asAlias( $table , $alias , false ) ,
                        $this->on( $definition ) ,
                    ] ) ;
                }
            }

            return compile( $definitions ) ;
        }

        return Char::EMPTY ;
    }

    /**
     * Creates the ON clause expression.
     * @throws DependencyException
     * @throws NotFoundException
     */
    protected function on( array $init = [] ) :string
    {
        $conditions = compile( searchCondition( $init[ OpenEdge::ON ] ?? null )  ) ;
        return $conditions === Char::EMPTY ? Char::EMPTY : ( Clause::ON  . Char::SPACE . $conditions ) ;
    }

    /**
     * Creates a table reference expression.
     * @param string|array|null $definition
     * @return string|null
     */
    public function tableRef( string|array|null $definition ):?string
    {
        $expression = [] ;
        if( is_array( $definition ) && isAssociative( $definition ) )
        {
            $table = $definition[ OpenEdge::TABLE ] ?? null ;
            if( is_string( $table ) && $table !== Char::EMPTY )
            {
                $alias  = $definition[ OpenEdge::ALIAS ] ?? null  ;
                $expression[] = asAlias( $table , $alias , false ) ;
            }
        }
        return compile( $expression );
    }
}