<?php

namespace oihana\openedge\db\helpers\functions\numerics;

use oihana\openedge\db\enums\functions\NumericFunction;
use function oihana\core\strings\func;

/**
 * Generates a Progress OpenEdge SQL `MOD()` expression.
 *
 * The `MOD()` function returns the remainder of a division operation between two
 * numeric expressions.
 *
 * **SQL Syntax:**
 * ```
 * MOD(dividend, divisor)
 * ```
 *
 * @param float|int|string $expression1 The dividend.
 * @param float|int|string $expression2 The divisor.
 *
 * @return string The generated SQL `MOD()` expression.
 * Example: `MOD(col1, col2)`
 *
 * @example
 * ```php
 * echo mod('a', 'b'); // outputs: MOD(a, b)
 * echo mod(10, 3);    // outputs: MOD(10, 3)
 * ```
 *
 * @see NumericFunction::MOD
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/MOD.html
 */
function mod( float|int|string $expression1 , float|int|string $expression2 ):string
{
    return func(NumericFunction::MOD , [ $expression1 , $expression2 ] ) ;
}

