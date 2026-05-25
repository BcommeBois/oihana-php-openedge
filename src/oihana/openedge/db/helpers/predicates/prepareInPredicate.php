<?php

namespace oihana\openedge\db\helpers\predicates ;

use oihana\enums\Char ;

use function oihana\core\arrays\isAssociative;
use function oihana\core\strings\betweenParentheses;
use function oihana\core\strings\compile;

use function oihana\openedge\db\helpers\expression;
use function oihana\openedge\db\helpers\literal;
use function oihana\openedge\db\helpers\validateContext;

/**
 * Prepare an IN predicate.
 * ```
 * in_predicate ::= expression [NOT] IN { (query_expression) | (constant , constant[ , ...] ) }
 * ```
 * @param mixed $expression
 * @param string $operator
 * @param mixed $values
 * @param string|null $context
 * @param string|array|null $allowed
 * @param callable|null $map
 *
 * @return string
 */
function prepareInPredicate
(
    mixed             $expression ,
    string            $operator ,
    mixed             $values ,
    ?string           $context = null,
    null|string|array $allowed = null,
    ?callable         $map     = null
)
: string
{
    if ( !validateContext( $context , $allowed ) )
    {
        return Char::EMPTY;
    }

    if ( isset( $values ) )
    {
        $exprFunc    = $map ?? fn(...$args) => expression(...$args) ;
        $expressions = [ $exprFunc( $expression ) , $operator ] ;

        if ( is_array( $values ) && !isAssociative( $values ) )
        {
            $expressions[] = betweenParentheses( compile
            (
                array_map( fn($value) => literal( $value ) , $values ) ,
                Char::COMMA
            )) ;
        }
        else
        {
            $expressions[] = betweenParentheses($values) ; // Query expression
        }
        return compile( $expressions ) ;
    }
    return Char::EMPTY;
}
