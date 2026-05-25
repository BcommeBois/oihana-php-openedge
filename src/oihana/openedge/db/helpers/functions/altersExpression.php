<?php

namespace oihana\openedge\db\helpers\functions\alters;

use oihana\reflect\exceptions\ConstantException;

/**
 * Applies multiple alteration functions sequentially to an expression or column in an OpenEdge SQL query.
 *
 * Each function in `$definitions` is applied in order, with the result of one
 * function passed as input to the next. This allows composing complex SQL
 * transformations in a readable way.
 *
 * @param string                   $expression  The column or expression to alter.
 * @param array<string|array>|null $definitions List of functions or function+arguments to apply.
 * @param callable|null            $callback    Optional callback applied to each definition before execution.
 *
 * @return string The altered SQL expression after applying all functions.
 *
 * @throws ConstantException If a provided function is invalid or unsupported.
 *
 * @example
 * ```php
 * use oihana\openedge\db\enums\functions\StringFunction;
 *
 * // Pads the 'user.name' column to 5 characters with '-' and converts to lowercase
 * $sql = altersExpression(
 *     'user.name',
 *     [
 *         [ StringFunction::RPAD, 5, '-' ] ,
 *         StringFunction::LOWER
 *     ]
 * );
 * // LOWER(RPAD(user.name,5,'-'))
 * ```
 *
 * @see alterExpression()  For applying a single function to an expression.
 */
function altersExpression( string $expression , ?array $definitions , ?callable $callback = null ) :string
{
    if( is_array( $definitions ) && count( $definitions ) > 0 )
    {
        return array_reduce
        (
            $definitions ,
            fn( $expression , $definition ) => alterExpression( $expression , $definition , $callback ) ,
            $expression
        ) ;
    }
    return $expression ;
}