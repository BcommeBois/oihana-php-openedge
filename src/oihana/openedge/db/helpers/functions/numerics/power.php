<?php

namespace oihana\openedge\db\helpers\functions\numerics;

use oihana\openedge\db\enums\functions\NumericFunction;
use function oihana\core\strings\func;

/**
 * Generates a Progress OpenEdge SQL `POWER()` expression.
 *
 * The `POWER()` function raises a numeric expression to the power of another.
 *
 * **SQL Syntax:**
 * ```
 * POWER(base, exponent)
 * ```
 *
 * @param float|int|string $expression1 The base.
 * @param float|int|string $expression2 The exponent.
 *
 * @return string The generated SQL `POWER()` expression.
 * Example: `POWER(col1, col2)`
 *
 * @example
 * ```php
 * echo power('base', 'exp'); // outputs: POWER(base, exp)
 * echo power(2, 3);          // outputs: POWER(2, 3)
 * ```
 *
 * @see NumericFunction::POWER
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/POWER.html
 */
function power( float|int|string $expression1 , float|int|string $expression2 ):string
{
    return func(NumericFunction::POWER , [ $expression1 , $expression2 ] ) ;
}
