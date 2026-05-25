<?php

namespace oihana\openedge\db\helpers\functions\numerics;

use oihana\openedge\db\enums\functions\NumericFunction;
use function oihana\core\strings\func;

/**
 * Generates a Progress OpenEdge SQL `GREATEST()` expression.
 *
 * The `GREATEST()` function returns the largest value from a list of numeric expressions.
 *
 * **SQL Syntax:**
 * ```
 * GREATEST(expression1, expression2, ...)
 * ```
 *
 * @param float|int|string $expression The first numeric SQL expression or column name.
 * @param float|int|string ...$args    Additional numeric expressions.
 *
 * @return string The generated SQL `GREATEST()` expression.
 * Example: `GREATEST(col1, col2, 100)`
 *
 * @example
 * ```php
 * echo greatest('val1', ['val2', 'val3']); // outputs: GREATEST(val1,val2,val3)
 * echo greatest(10, [20, 5]);              // outputs: GREATEST(10, 20, 5)
 * ```
 *
 * @see NumericFunction::GREATEST
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/GREATEST.html
 */
function greatest( float|int|string $expression , float|int|string ...$args ):string
{
    return func(NumericFunction::GREATEST , [ $expression , ...$args ] ) ;
}

