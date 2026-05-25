<?php

namespace oihana\openedge\db\helpers\predicates;

use oihana\enums\Char;
use function oihana\core\strings\compile;
use function oihana\openedge\db\helpers\expression;
use function oihana\openedge\db\helpers\validateContext;

/**
 * Prepare a Like predicate.
 * ```
 * like_predicate ::= column_name[ NOT ] LIKE string_constant [ ESCAPE escape_character]
 * ```
 * @param mixed $leftExpression
 * @param mixed $operator
 * @param mixed $rightExpression
 * @param mixed $escapeChar
 * @param string|null $context
 * @param string|array|null $allowed
 * @param callable|null $map
 * @return string
 */
function prepareLikePredicate
(
    mixed             $leftExpression  ,
    string            $operator        ,
    mixed             $rightExpression ,
    mixed             $escapeChar      ,
    ?string           $context         = null,
    null|string|array $allowed         = null,
    ?callable         $map             = null
)
: string
{
    if ( !validateContext( $context , $allowed ) )
    {
        return Char::EMPTY ;
    }
    $exprFunc    = $map ?? fn( ...$args ) => expression( ...$args ) ;
    $expressions = [ $exprFunc( $leftExpression ) , $operator , $exprFunc( $rightExpression ) ] ;
    if ( isset( $escapeChar ) )
    {
        $expressions[] = $escapeChar ;
    }
    return compile( $expressions ) ;
}
