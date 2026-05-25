<?php

namespace oihana\openedge\db\helpers\predicates;

use oihana\enums\Char;
use function oihana\core\strings\compile;
use function oihana\openedge\db\helpers\expression;
use function oihana\openedge\db\helpers\validateContext;

/**
 * Prepare a quantified predicate with a relational operator or the LIKE/NOT LIKE predicates.
 * ```
 * quantified_predicate ::= expression relop { ALL | ANY | SOME } ( query_expression )
 * ```
 * @param mixed $expression
 * @param string $operator
 * @param mixed $quantified
 * @param mixed $queryExpression
 * @param string|null $context
 * @param string|array|null $allowed
 * @param callable|null $map
 * @return string
 */
function prepareQuantifiedPredicate
(
    mixed             $expression,
    string            $operator,
    mixed             $quantified,
    mixed             $queryExpression,
    ?string           $context = null,
    null|string|array $allowed = null,
    ?callable         $map = null
)
: string
{
    if ( !validateContext( $context , $allowed ) )
    {
        return Char::EMPTY;
    }
    $exprFunc = $map ?? fn( ...$args ) => expression( ...$args ) ;
    return compile
    ([
        $exprFunc( $expression ) ,
        $operator ,
        $quantified ,
        $queryExpression
    ]);
}
