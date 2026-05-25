<?php

namespace oihana\openedge\db\helpers\predicates;

use oihana\enums\Char;

use function oihana\core\strings\compile;

use function oihana\openedge\db\helpers\expression;
use function oihana\openedge\db\helpers\validateContext;

/**
 * Prepare a basic relational predicate with a relational operator.
 * ```
 * relational_predicate ::= expression relop { expression | ( query_expression ) }
 * ```
 * @param mixed $leftExpression
 * @param string $operator
 * @param mixed $rightExpression
 * @param string|null $context
 * @param string|array|null $allowed
 * @param callable|null $map
 * @return string
 */
function prepareBasicPredicate
(
    mixed             $leftExpression,
    string            $operator,
    mixed             $rightExpression,
    ?string           $context = null,
    null|string|array $allowed = null,
    ?callable         $map = null
)
: string
{
    if ( !validateContext($context, $allowed ) )
    {
        return Char::EMPTY;
    }
    $exprFunc = $map ?? fn( ...$args ) => expression( ...$args ) ;
    return compile( [ $exprFunc( $leftExpression ) , $operator , $exprFunc( $rightExpression ) ] ) ;
}
