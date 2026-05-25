<?php

namespace oihana\openedge\db\helpers\functions\numerics;

use oihana\openedge\db\enums\functions\NumericFunction;
use function oihana\core\strings\func;

/**
 * Generates a Progress OpenEdge SQL `FLOOR()` expression.
 *
 * The `FLOOR()` function returns the largest integer value that is less than
 * or equal to a numeric expression.
 *
 * **SQL Syntax:**
 * ```
 * FLOOR(numeric_expression)
 * ```
 *
 * @param float|int|string $expression The numeric SQL expression or column name.
 *
 * @return string The generated SQL `FLOOR()` expression.
 * Example: `FLOOR(column)`
 *
 * @example
 * ```php
 * echo floor('price'); // outputs: FLOOR(price)
 * echo floor(4.8);     // outputs: FLOOR(4.8)
 * ```
 *
 * @see NumericFunction::FLOOR
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/FLOOR.html
 */
function floor( float|int|string $expression ):string
{
    return func(NumericFunction::FLOOR , $expression ) ;
}
