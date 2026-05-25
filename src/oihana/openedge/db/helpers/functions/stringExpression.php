<?php

namespace oihana\openedge\db\helpers\functions ;

use function oihana\core\strings\betweenQuotes;

/**
 * Builds an OpenEdge STRING literal expression.
 *
 * OpenEdge STRING literals are enclosed in single quotation marks ( '' ).
 * Single quotes within the string are escaped by doubling them.
 *
 * @param ?string $expression The string expression to convert to a literal.
 *
 * @return ?string The formatted STRING literal expression with escaped quotes and surrounding quotes.
 *
 * @example
 * ```php
 * stringExpression('unquoted literal');
 * // Returns: 'unquoted literal'
 *
 * stringExpression('O\'Hare');
 * // Returns: 'O''Hare'
 *
 * stringExpression('"double-quoted"');
 * // Returns: '"double-quoted"'
 * ```
 */
function stringExpression( ?string $expression ) :?string
{
    if ( !isset( $expression ) )
    {
        return null ;
    }

    // Escape single quotes by doubling them, then wrap in quotes
    return betweenQuotes( str_replace( "'" , "''" , $expression ) , trim:false ) ;
}