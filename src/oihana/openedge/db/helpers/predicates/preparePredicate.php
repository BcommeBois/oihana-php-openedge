<?php

namespace oihana\openedge\db\helpers\predicates;

use oihana\enums\Char;
use oihana\openedge\db\enums\Predicate;
use oihana\openedge\db\enums\QuantifiedOperator;
use oihana\openedge\db\enums\RelationalOperator;
use function oihana\core\arrays\isAssociative;

/**
 * Prepares a SQL predicate expression based on the provided definition.
 *
 * This function acts as a dispatcher, identifying the type of predicate (e.g., Basic, Between, In, Like, Null, Exists)
 * from the input array and delegating the generation to the appropriate helper function.
 *
 * This function is used to manage the WHERE clause.
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
 *
 * @param mixed $definition
 *        The definition of the predicate. It is usually an array structure specific to the operator:
 *        - **Basic**: `[ left, operator, right ]`
 *        - **Quantified**: `[ left, operator, quantifier, query ]`
 *        - **Between**: `[ expression, BETWEEN, min, max ]`
 *        - **In**: `[ expression, IN, values_list ]`
 *        - **Like**: `[ expression, LIKE, pattern, escape? ]`
 *        - **Null**: `[ expression, IS_NULL ]`
 *        - **Exists**: `[ EXISTS, query ]`
 *
 * @param string|null $context
 *        Optional context string to validate if the predicate is allowed in the current scope (e.g., via `validateContext`).
 *
 * @param callable|null $map
 *        Optional callback function to transform arguments (e.g., columns, values) before generating the SQL.
 *
 * @return string The generated SQL predicate string, or an empty string if the definition is invalid.
 *
 * @example
 * ```php
 * use oihana\openedge\db\enums\Predicate;
 * use oihana\openedge\db\enums\QuantifiedOperator;
 *
 * // 1. Basic Relational Predicate
 * // WHERE age >= 18
 * preparePredicate([ 'age', '>=', 18 ]);
 *
 * // 2. Quantified Predicate
 * // WHERE salary > ALL (SELECT amount FROM salaries)
 * preparePredicate( [ 'salary', '>' , QuantifiedOperator::ALL, 'SELECT amount FROM salaries' ]);
 *
 * // 3. BETWEEN Predicate
 * // WHERE price BETWEEN 10 AND 50
 * preparePredicate([ 'price', Predicate::BETWEEN, 10, 50 ]);
 *
 * // 4. IN Predicate
 * // WHERE status IN ('A', 'B', 'C')
 * preparePredicate([ 'status', Predicate::IN, ['A', 'B', 'C'] ]);
 *
 * // 5. LIKE Predicate
 * // WHERE name LIKE 'J%'
 * preparePredicate([ 'name', Predicate::LIKE, 'J%' ]);
 *
 * // 6. NULL Predicate
 * // WHERE description IS NULL
 * preparePredicate([ 'description', Predicate::NULL ]);
 *
 * // 7. EXISTS Predicate
 * // WHERE EXISTS (SELECT 1 FROM logs)
 * preparePredicate([ Predicate::EXISTS, 'SELECT 1 FROM logs' ]);
 * ```
 */
function preparePredicate
(
    mixed     $definition,
    ?string   $context = null,
    ?callable $map = null
)
: string
{
    if ( is_string( $definition ) )
    {
        return $definition;
    }

    if ( !is_array( $definition ) || isAssociative( $definition ) )
    {
        return Char::EMPTY;
    }

    [ $left , $operator ] = $definition;

    switch (true)
    {
        case RelationalOperator::includes( $operator ) :
        {
            [ , , $right ] = $definition;
            if ( QuantifiedOperator::includes( $right ) )
            {
                [ , , , $queryExpression , $allowed ] = array_pad( $definition , 5 , null ) ;
                return prepareQuantifiedPredicate( $left , $operator , $right , $queryExpression , $context , $allowed , $map ) ;
            }
            else
            {
                [ , , , $allowed ] = array_pad( $definition , 4 , null ) ;
                return prepareBasicPredicate( $left , $operator , $right , $context , $allowed , $map ) ;
            }
        }
        case $operator == Predicate::BETWEEN     :
        case $operator == Predicate::NOT_BETWEEN :
        {
            [ , , $minimum, $maximum, $allowed ] = array_pad( $definition , 5 , null ) ;
            return prepareBetweenPredicate( $left , $operator , $minimum , $maximum , $context , $allowed , $map ) ;
        }
        case $operator == Predicate::IN     :
        case $operator == Predicate::NOT_IN :
        {
            [ , , $values , $allowed ] = array_pad( $definition , 4 , null ) ;
            return prepareInPredicate( $left , $operator , $values , $context , $allowed , $map ) ;
        }
        case $operator == Predicate::LIKE     :
        case $operator == Predicate::NOT_LIKE :
        {
            [ , , $right , $escapeChar , $allowed ] = array_pad( $definition , 5 , null ) ;
            return prepareLikePredicate( $left , $operator , $right , $escapeChar , $context , $allowed , $map ) ;
        }
        case $operator == Predicate::NULL     :
        case $operator == Predicate::NOT_NULL :
        {
            [ , , $allowed ] = array_pad( $definition , 3 , null ) ;
            return prepareNullPredicate( $left , $operator , $context , $allowed , $map ) ;
        }
        case $left == Predicate::EXISTS     :
        case $left == Predicate::NOT_EXISTS :
        {
            [ , , $allowed ] = array_pad( $definition , 3 , null ) ;
            return prepareExistPredicate( $left , $operator , $context , $allowed ) ;
        }

        default :
        {
            return Char::EMPTY ;
        }
    }
}
