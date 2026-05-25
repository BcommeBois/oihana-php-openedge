<?php

namespace oihana\openedge\db\helpers\functions\numerics;

use oihana\openedge\db\enums\functions\NumericFunction;
use function oihana\core\strings\func;

/**
 * Generates a Progress OpenEdge SQL `SIGN()` expression.
 *
 * The `SIGN()` function returns the sign of a numeric expression:
 * - -1 if the expression is negative.
 * - 0 if the expression is zero.
 * - 1 if the expression is positive.
 *
 * **SQL Syntax:**
 * ```
 * SIGN(numeric_expression)
 * ```
 *
 * @param float|int|string $expression The numeric SQL expression or column name.
 *
 * @return string The generated SQL `SIGN()` expression.
 * Example: `SIGN(balance)`
 *
 * @example
 * ```php
 * echo sign('balance'); // outputs: SIGN(balance)
 * echo sign(-100);      // outputs: SIGN(-100)
 * ```
 *
 * @see NumericFunction::SIGN
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/SIGN.html
 */
function sign( float|int|string $expression ):string
{
    return func(NumericFunction::SIGN , $expression ) ;
}
