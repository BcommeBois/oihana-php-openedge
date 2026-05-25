<?php

namespace oihana\openedge\db\helpers\functions\numerics;

use oihana\openedge\db\enums\functions\NumericFunction;
use function oihana\core\strings\func;

/**
 * Generates a Progress OpenEdge SQL `ATAN2()` expression.
 *
 * The `ATAN2()` function returns the arctangent (in radians) of the x and y coordinates
 * specified by two numeric expressions. It is useful for converting coordinates from
 * Cartesian to polar.
 *
 * **SQL Syntax:**
 * ```
 * ATAN2(y_expression, x_expression)
 * ```
 *
 * @param float|int|string $expression1 The y-coordinate expression.
 * @param float|int|string $expression2 The x-coordinate expression.
 *
 * @return string The generated SQL `ATAN2()` expression.
 * Example: `ATAN2(y_col, x_col)`
 *
 * @example
 * ```php
 * echo atan2('y', 'x'); // outputs: ATAN2(y, x)
 * echo atan2(1, -1);    // outputs: ATAN2(1, -1)
 * ```
 *
 * @see NumericFunction::ATAN2
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/ATAN2.html
 */
function atan2( float|int|string $expression1 , float|int|string $expression2 ):string
{
    return func(NumericFunction::ATAN2 , [ $expression1 , $expression2 ] ) ;
}
