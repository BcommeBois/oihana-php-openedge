<?php

namespace oihana\openedge\db\helpers\functions\numerics;

use oihana\openedge\db\enums\functions\NumericFunction;
use function oihana\core\strings\func;

/**
 * Generates a Progress OpenEdge SQL `ROUND()` expression.
 *
 * The `ROUND()` function rounds a numeric expression to a specified number of decimal places.
 *
 * **SQL Syntax:**
 * ```
 * ROUND(numeric_expression, rounding_factor)
 * ```
 *
 * @param float|int|string $expression The numeric SQL expression or column name to round.
 * @param float|int|string ...$args    The rounding factor (an integer specifying the number of decimal places).
 *
 * @return string The generated SQL `ROUND()` expression.
 * Example: `ROUND(price, 2)`
 *
 * @example
 * ```php
 * echo round('price', [2]); // outputs: ROUND(price, 2)
 * echo round(123.456, [1]); // outputs: ROUND(123.456, 1)
 * ```
 *
 * @see NumericFunction::ROUND
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/ROUND.html
 */
function round( float|int|string $expression , float|int|string ...$args ):string
{
    return func(NumericFunction::ROUND , [ $expression , ...$args ] ) ;
}
