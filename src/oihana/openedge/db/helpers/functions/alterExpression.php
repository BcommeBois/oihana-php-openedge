<?php

namespace oihana\openedge\db\helpers\functions\alters;

use oihana\reflect\exceptions\ConstantException;

/**
 * Alters an SQL expression based on a given function definition.
 *
 * This function allows transforming a given SQL expression by applying a
 * specific function (e.g., conditional, conversion, date, numeric, or string
 * function) with optional arguments.
 *
 * The function definition can be provided in multiple formats:
 *
 * - **String:** The name of the function to apply.
 *   ```php
 *   alterExpression('price', 'IFNULL', null);
 *   // Returns: "IFNULL(price)"
 *   ```
 *
 * - **Array:** The first element is the function name, and the rest are its arguments.
 *   ```php
 *   alterExpression('price', ['IFNULL', 0], null);
 *   // Returns: "IFNULL(price, 0)"
 *   ```
 *
 * - **Null:** The expression remains unchanged.
 *   ```php
 *   alterExpression('price', null, null);
 *   // Returns: "price"
 *   ```
 *
 * @param string $expression The SQL expression or column name to alter.
 * @param array|string|null $definition The function definition:
 *                                         - string → function name only.
 *                                         - array  → [functionName, arg1, arg2, ...].
 *                                         - null   → no alteration.
 * @param callable|null $callback Optional callback to transform the arguments
 *                                        before passing them to the function.
 *
 * @return string The altered SQL expression.
 *
 * @throws ConstantException If the provided function is invalid or unsupported.
 *
 * @see alterKey()
 */
function alterExpression( string $expression , array|string|null $definition , ?callable $callback = null ) :string
{
    if( isset( $definition ) )
    {
        $function = null ;
        $args     = [] ;

        if( is_string( $definition ) )
        {
            $function = $definition ;
        }
        else if( is_array( $definition ) )
        {
            [ $function ] = $definition ;
            if( is_string( $function ) )
            {
                $args = array_slice( $definition , 1 ) ;
            }
            else
            {
                $function = null ;
            }
        }

        return alterKey( $expression , $function , $args , $callback ) ;
    }

    return $expression ;
}