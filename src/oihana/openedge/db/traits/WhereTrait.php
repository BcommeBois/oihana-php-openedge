<?php

namespace oihana\openedge\db\traits;

use DI\DependencyException;
use DI\NotFoundException;

use oihana\enums\Char;
use oihana\exceptions\BindException;
use oihana\exceptions\ValidationException;
use oihana\openedge\db\enums\Clause;
use oihana\openedge\enums\OpenEdge;

use function oihana\core\arrays\isAssociative;
use function oihana\core\arrays\toArray;
use function oihana\openedge\db\helpers\searchCondition;

/**
 * This trait is used to manage the WHERE clause in the OpenEdgeQuery builder helper.
 *
 * ### Logical [✓]
 * ```
 * [ 'age' , Comparator::GREATER_THAN_OR_EQUAL , 18 ]
 * ex: WHERE age >= 18
 * ```
 *
 * ### 'IS NULL' OR 'IS NOT NULL' [✓]
 * ```
 * [ value , Predicate::NULL ]
 * ex: WHERE value IS NULL
 * ```
 *
 * ### Comparable (AND, OR, AND NOT, OR NOT) [✓]
 * ```
 * [
 *     [ 'birthYear' , Comparator::GREATER_THAN_OR_EQUAL , 1970 ] ,
 *     [
 *         OpenEdge::OPERATOR   => Logic::OR ,
 *         OpenEdge::CONDITIONS =>
 *         [
 *             [ 'lastname'  , Comparator::NOT_EQUAL , 'foo' ] ,
 *             [ 'firstname' , Comparator::NOT_EQUAL , 'bar'  ] ,
 *         ]
 *     ]
 * ]
 * ex: WHERE birthYear >= 1970 AND ( lastname <> 'foo' OR firstname <> 'bar' )
 * ```
 *
 * ### 'BETWEEN' OR 'NOT BETWEEN' [✓]
 * ```
 * [ value , Predicate::BETWEEN , min , max ]
 * - WHERE column BETWEEN value1 AND value2
 * - WHERE column NOT_BETWEEN value1 AND value2
 * ex: WHERE price BETWEEN 0 AND 500
 * ```
 *
 * ### 'LIKE' OR 'NOT LIKE' [✓]
 * ```
 * [ value , Predicate::LIKE , pattern ]
 * - WHERE value LIKE 'pattern'
 * - WHERE value NOT LIKE 'pattern'
 * ex: SELECT * FROM clients WHERE nom LIKE 'A%'
 * ```
 *
 * ### EXISTS OR NOT EXISTS [✓]
 * [ Predicate::EXISTS , [ query definition ] ]
 * - WHERE EXIST ( query )
 * - WHERE NOT EXIST ( query )
 * ex: SELECT * FROM clients WHERE EXIST ( SELECT 1 FROM orders WHERE clients.id = orders.clientID )
 *
 * ### IN OR NOT IN [✓]
 * ```
 * [ value , Predicate::IN , 'value1' , 'value2' , ... ] // rest
 * [ value , Predicate::IN , [ query definition ] ] // associative array
 * - WHERE column IN (value1, value2, ...)
 * - WHERE column IN (query expression)
 * - WHERE column NOT IN (value1, value2, ...)
 * ex: WHERE country IN ('France', 'Espagne', ...)
 * ```
 * ### Quantified Predicate [x]
 * https://docs.progress.com/fr-FR/bundle/openedge-sql-reference/page/Quantified-Predicate.html
 * expression relop { ALL | ANY | SOME } ( query_expression )
 */
trait WhereTrait
{
    use FacetsTrait ;

    /**
     * The definition to build the WHERE clause.
     * @var string|array|null
     */
    public string|array|null $where = null ;

    /**
     * Generates the WHERE clause of the openedge SQL query.
     * @param null|string|array $init
     * @param array $bindVars
     * @param null|string $context
     * @return string
     * @throws DependencyException
     * @throws NotFoundException
     * @throws BindException|ValidationException
     * @example
     * ### Example 1 :
     * -> WHERE products.status = 0
     * ```
     * echo $this->where( 'products.status = 0') ;
     * ```
     *
     * ### Example 2 :
     * -> WHERE products.status = 0 AND products.price = 150
     * ```
     * echo $this->where
     * ([
     *      [ [ SQL::COLUMN => 'products.status' ] , Operator::EQUAL        ,   0 ] ,
     *      [ [ SQL::COLUMN => 'products.price'  ] , Operator::GREATER_THAN , 150 ]
     * ]) ;
     * ```
     *
     * ### Example 3 :
     * -> WHERE products.status = 0 OR products.price = 150
     *  ```
     *   echo $this->where
     *   ([
     *      SQL::OPERATOR   => Logic::OR  ,
     *      SQL::CONDITIONS =>
     *      [
     *         [ [ SQL::COLUMN => 'products.status' ] , Operator::EQUAL , 0 ] ,
     *         [ [ SQL::COLUMN => 'products.price'  ] , Operator::GREATER_THAN , 150 ] ,
     *      ],
     *  ]) ;
     *  ```
     * ### Example 4 :
     * -> WHERE products.statut = 0 AND (products.name = 'product1' OR products.name = 'product2')
     * ```
     *  echo $this->where
     *  ([
     *     [ [ SQL::COLUMN => 'products.status' ] , Operator::EQUAL , 0 ] ,
     *     [
     *         SQL::OPERATOR   => Logic::OR  ,
     *         SQL::CONDITIONS =>
     *         [
     *             [ [ SQL::COLUMN => 'products.name' ] , Operator::EQUAL , 'product1' ] ,
     *             [ [ SQL::COLUMN => 'products.name' ] , Operator::EQUAL , 'product2' ] ,
     *         ]
     *     ]
     * ]) ;
     * ```
     */
    public function where
    (
        null|string|array $init      = [] ,
        array             &$bindVars = [] ,
        ?string           $context   = null
    )
    :string
    {
        $where  = toArray( $this->where ?? Char::EMPTY ) ;
        $facets = $this->prepareFacets( $init , $bindVars ) ;

        if( $facets )
        {
            $where = [ ...$where , ...toArray( $facets ) ] ;
        }

        if( is_array( $init ) )
        {
            if( isAssociative( $init ) )
            {
                $init[ OpenEdge::CONDITIONS ] = [ ...$where , ...( $init[ OpenEdge::CONDITIONS ] ?? [] ) ] ; // , $facets
                $definition = $init ;
            }
            else
            {
                $definition = [ OpenEdge::CONDITIONS => [ ...$where , ...$init ] ] ;
            }
        }
        else if ( is_string( $init ) && $init != Char::EMPTY )
        {
            $definition = [ OpenEdge::CONDITIONS => [ ...$where , $init ] ] ;
        }
        else
        {
            $definition = $where ;
        }

        $conditions = searchCondition( $definition , [ OpenEdge::CONTEXT => $context ] ) ;

        // TODO EXISTS predicate => https://docs.progress.com/bundle/openedge-sql-reference/page/EXISTS-Predicate.html

        // echo PHP_EOL ;
        // echo '### where -> conditions ::::: ' . json_encode( $conditions , JSON_UNESCAPED_SLASHES ) . PHP_EOL;
        // echo PHP_EOL ;

        return $conditions === Char::EMPTY ? Char::EMPTY : ( Clause::WHERE . Char::SPACE . $conditions ) ;
    }
}

/* -------------

SQL::WHERE => 'products.statut == 0' ,

SQL::WHERE =>
[
    // string predicate
    'client.name = ooop' ,

    // basic predicate -> expression relop { expression | ( query_expression ) }

    [ [ SQL::COLUMN => 'client.name' ] , Operator::EQUAL                 , 'foo' ] ,
    [ [ SQL::COLUMN => 'client.age'  ] , Operator::EQUAL                 , new OpenEdgeQuery() | [ SQL::QUERY => '' ] ] ,
    [ [ SQL::COLUMN => 'client.name' ] , Operator::NOT_EQUAL             , 'foo' ] ,
    [ [ SQL::COLUMN => 'client.age'  ] , Operator::GREATER_THAN          , 25 ] ,
    [ [ SQL::COLUMN => 'client.age'  ] , Operator::GREATER_THAN_OR_EQUAL , 25 ] ,
    [ [ SQL::COLUMN => 'client.age'  ] , Operator::LESS_THAN             , 25 ] ,
    [ [ SQL::COLUMN => 'client.age'  ] , Operator::LESS_THAN_OR_EQUAL    , 25 ] ,

    // null_predicate -> column_name IS [ NOT ] NULL
    [ [ SQL::COLUMN => 'client.name'  ] , Predicate::NULL     ] ,
    [ [ SQL::COLUMN => 'client.name'  ] , Predicate::NOT_NULL ] ,

    // between_predicate -> expression [ NOT ] BETWEEN expression AND expression
    [ [ SQL::COLUMN => 'client.age' ] , Predicate::BETWEEN     , 18 , 50 ] ,
    [ [ SQL::COLUMN => 'client.age' ] , Predicate::NOT_BETWEEN , 18 , 50 ] ,

    // exists_predicate -> EXISTS (query_expression)
    [ Predicate::EXISTS     , new OpenEdgeQuery() | [ SQL::QUERY => '' ] ] ,
    [ Predicate::NOT_EXISTS , new OpenEdgeQuery() | [ SQL::QUERY => '' ] ] ,

    // in_predicate -> expression [ NOT ] IN { (query_expression) | (constant , constant[ , ...] )  }
    [ [ SQL::COLUMN => 'client.age' ] , Predicate::IN | Predicate::NOT_IN , 'value1' , 'value2' , 'value3' , ... ] ,
    [ [ SQL::COLUMN => 'client.age' ] , Predicate::IN | Predicate::NOT_IN , [ 'value1' , 'value2' , 'value3' , ... ] ] , // indexed array
    [ [ SQL::COLUMN => 'client.age' ] , Predicate::IN | Predicate::NOT_IN , new OpenEdgeQuery() | [ SQL::QUERY => '' ] ] ,

    // like predicate -> column_name [  NOT  ] LIKE string_constant [ESCAPE escape_character]
    [ [ SQL::COLUMN => 'firstName' , Predicate::LIKE | Predicate::NOT_LIKE  , 'A%' ] ,
    [ [ SQL::COLUMN => 'firstName' , Predicate::LIKE | Predicate::NOT_LIKE  , 'A%' , "\\" ] ,

    // quantified predicate -> quantified_predicate ::= relop { ALL | ANY | SOME } ( query_expression )
    [ [ SQL::COLUMN => 'firstName' , Operator::EQUAL , QuantifiedOperator::ANY , new OpenEdgeQuery() | [ SQL::QUERY => '' ] ] ,

    // outer_join_predicate -> [ table_name. ] column  =  [ table_name . ] column  (+) | [ table_name.]column (+) = [table_name.]column
    [ [ SQL::COLUMN => 'client.department_id' ] , Operator::EQUAL , [ SQL::COLUMN => 'department.id' , SQL::NULLABLE => true ] ] ,
],

// WHERE products.statut = 0 AND (products.name = 'ooop' OR products.name = 'xyz')
SQL::WHERE =>
[
    SQL::OPERATOR   => Logic::AND  , // default AND
    SQL::CONDITIONS =>
    [
        [ [ SQL::COLUMN => 'products.statut' ] , Operator::EQUAL , 0 ] ,
        [
            SQL::OPERATOR   => Logic::OR  ,
            SQL::CONDITIONS =>
            [
                [ [ SQL::COLUMN => 'products.name' ] , Operator::EQUAL , 'ooop'   ] ,
                [ [ SQL::COLUMN => 'products.name' ] , Operator::EQUAL , 'xyz' ] ,
            ],
        ]
    ],
],

// WHERE NOT( products.statut = 0 AND (products.name = 'ooop' OR products.name = 'xyz') )
SQL::WHERE =>
[
    SQL::OPERATOR   => Logic::NOT  ,
    SQL::CONDITIONS =>
    [
        SQL::OPERATOR => Logic::AND  ,
        SQL::CONDITIONS =>
        [
            [ [ SQL::COLUMN => 'products.statut' ] , Operator::EQUAL , 0 ] ,
            [
                SQL::OPERATOR   => Logic::OR  ,
                SQL::CONDITIONS =>
                [
                    [ [ SQL::COLUMN => 'products.name' ] , Operator::EQUAL , 'ooop' ] ,
                    [ [ SQL::COLUMN => 'products.name' ] , Operator::EQUAL , 'xyz'  ] ,
                ],
            ]
        ]
    ],
],

------------- */