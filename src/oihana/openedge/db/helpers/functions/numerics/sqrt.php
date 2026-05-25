<?php

namespace oihana\openedge\db\helpers\functions\numerics;

use oihana\openedge\db\enums\functions\NumericFunction;
use function oihana\core\strings\func;

/**
 * Generates a Progress OpenEdge SQL `SQRT()` expression.
 *
 * The `SQRT()` function returns the square root of a non-negative numeric expression.
 *
 * **SQL Syntax:**
 * ```
 * SQRT(numeric_expression)
 * ```
 *
 * @param float|int|string $expression The numeric SQL expression or column name. Must be non-negative.
 *
 * @return string The generated SQL `SQRT()` expression.
 * Example: `SQRT(column)`
 *
 * @example
 * ```php
 * echo sqrt('area'); // outputs: SQRT(area)
 * echo sqrt(16);     // outputs: SQRT(16)
 * ```
 *
 * @see NumericFunction::SQRT
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/SQRT.html
 */
function sqrt( float|int|string $expression ):string
{
    return func(NumericFunction::SQRT , $expression ) ;
}
