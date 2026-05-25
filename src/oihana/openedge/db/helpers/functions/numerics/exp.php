<?php

namespace oihana\openedge\db\helpers\functions\numerics;

use oihana\openedge\db\enums\functions\NumericFunction;
use function oihana\core\strings\func;

/**
 * Generates a Progress OpenEdge SQL `EXP()` expression.
 *
 * The `EXP()` function returns the exponential value (e raised to the power) of a numeric expression.
 *
 * **SQL Syntax:**
 * ```
 * EXP(numeric_expression)
 * ```
 *
 * @param float|int|string $expression The numeric SQL expression or column name.
 *
 * @return string The generated SQL `EXP()` expression.
 * Example: `EXP(column)`
 *
 * @example
 * ```php
 * echo exp('power_value'); // outputs: EXP(power_value)
 * echo exp(2);             // outputs: EXP(2)
 * ```
 *
 * @see NumericFunction::EXP
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/EXP.html
 */
function exp( float|int|string $expression ):string
{
    return func(NumericFunction::EXP , $expression ) ;
}
