<?php

namespace oihana\openedge\db\helpers ;

use oihana\enums\Char;

/**
 * Encapsulates an expression to be a string between simple quotes.
 * @param mixed $value
 * @return mixed
 * @example
 * ```
 * literal( 'hello' ) ; // 'hello'
 * literal( "hello" ) ; // 'hello'
 * literal( 1 ) ; // '1'
 * literal( true ) ; // 'true'
 * ```
 */
function literal( mixed $value ):mixed
{
    if( is_string( $value ) )
    {
        return Char::SIMPLE_QUOTE
             . str_replace( Char::SIMPLE_QUOTE , Char::SIMPLE_QUOTE . Char::SIMPLE_QUOTE , $value )
             . Char::SIMPLE_QUOTE ;
    }
    return $value ;
}