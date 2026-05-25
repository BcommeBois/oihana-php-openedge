<?php

namespace oihana\openedge\db\helpers\predicates;

use oihana\enums\Char;
use oihana\openedge\db\enums\Logic;
use function oihana\core\strings\compile;
use function oihana\openedge\db\helpers\expression;
use function oihana\openedge\db\helpers\validateContext;

/**
 * Retrieve the between predicates.
 * ```
 * between_predicate ::= expression [ NOT ] BETWEEN expression AND expression
 * ```
 * @param mixed $expression
 * @param string $operator
 * @param mixed $minimum
 * @param mixed $maximum
 * @param string|null $context
 * @param string|array|null $allowed
 * @param callable|null $map
 * @return string
 */
function prepareBetweenPredicate
(
    mixed             $expression ,
    string            $operator   ,
    mixed             $minimum    ,
    mixed             $maximum    ,
    ?string           $context    = null ,
    null|string|array $allowed    = null ,
    ?callable         $map        = null
)
: string
{
    if ( !validateContext( $context , $allowed ) )
    {
        return Char::EMPTY ;
    }
    $exprFunc = $map ?? fn(...$args) => expression(...$args) ;
    return compile
    ([
        $exprFunc( $expression ) ,
        $operator ,
        $exprFunc( $minimum ) ,
        Logic::AND ,
        $exprFunc( $maximum )
    ]);
}
