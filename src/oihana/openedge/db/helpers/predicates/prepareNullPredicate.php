<?php

namespace oihana\openedge\db\helpers\predicates ;

use oihana\enums\Char ;
use function oihana\core\strings\compile ;
use function oihana\openedge\db\helpers\expression;
use function oihana\openedge\db\helpers\validateContext;

/**
 * Prepare the Null predicates.
 * ```
 * null_predicate ::= column_name IS [ NOT ] NULL
 * ```
 * @param mixed $expression
 * @param string $operator
 * @param string|null $context
 * @param string|array|null $allowed
 * @param callable|null $map
 * @return string
 */
function prepareNullPredicate
(
    mixed             $expression ,
    string            $operator   ,
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
    $exprFunc = $map ?? fn( ...$args ) => expression( ...$args ) ;
    return compile( [ $exprFunc( $expression ) , $operator ] ) ;
}
