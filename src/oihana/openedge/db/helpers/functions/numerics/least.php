<?php

namespace oihana\openedge\db\helpers\functions\numerics;

use oihana\openedge\db\enums\functions\NumericFunction;
use function oihana\core\strings\func;

/**
 * Generates a Progress OpenEdge SQL `LEAST()` expression.
 *
 * The `LEAST()` function returns the smallest value from a list of numeric expressions.
 *
 * **SQL Syntax:**
 * ```
 * LEAST(expression1, expression2, ...)
 * ```
 *
 * @param float|int|string $expression The first numeric SQL expression or column name.
 * @param float|int|string ...$args    Additional numeric expressions.
 *
 * @return string The generated SQL `LEAST()` expression.
 * Example: `LEAST(col1, col2, 100)`
 *
 * @example
 * ```php
 * echo least('val1', ['val2', 'val3']); // outputs: LEAST(val1, val2, val3)
 * echo least(10, [20, 5]);              // outputs: LEAST(10, 20, 5)
 * ```
 *
 * @see NumericFunction::LEAST
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/LEAST.html
 */
function least( float|int|string $expression , float|int|string ...$args ):string
{
    return func(NumericFunction::LEAST , [ $expression , ...$args ] ) ;
}

