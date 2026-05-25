<?php

namespace oihana\openedge\db\helpers\predicates ;

use oihana\enums\Char;
use function oihana\core\strings\compile;
use function oihana\openedge\db\helpers\validateContext;

/**
 * Prepare an Exists predicates
 * ```
 * exists_predicate ::= EXISTS (query_expression)
 * ```
 * @param string $operator
 * @param mixed $queryExpression
 * @param string|null $context
 * @param string|array|null $allowed
 * @return string
 */
function prepareExistPredicate
(
    string            $operator,
    mixed             $queryExpression,
    ?string           $context = null,
    null|string|array $allowed = null
)
: string
{
    if ( !validateContext( $context , $allowed ) )
    {
        return Char::EMPTY ;
    }
    return compile([ $operator, $queryExpression ] ) ;
}
