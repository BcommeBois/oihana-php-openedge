<?php

namespace oihana\openedge\db\helpers\functions\numerics;

use oihana\openedge\db\enums\functions\NumericFunction;
use function oihana\core\strings\func;

/**
 * Generates a Progress OpenEdge SQL `LOG10()` expression.
 *
 * The `LOG10()` function returns the base-10 logarithm of a numeric expression.
 *
 * **SQL Syntax:**
 * ```
 * LOG10(numeric_expression)
 * ```
 *
 * @param float|int|string $expression The numeric SQL expression or column name. Must be greater than 0.
 * @param array ...$args
 *
 * @return string The generated SQL `LOG10()` expression.
 * Example: `LOG10(column)`
 *
 * @example
 * ```php
 * echo log10('value'); // outputs: LOG10(value)
 * echo log10(100);     // outputs: LOG10(100)
 * ```
 *
 * @see NumericFunction::LOG10
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/LOG10.html
 */
function log10( float|int|string $expression , array ...$args ):string
{
    return func(NumericFunction::LOG10 , [ $expression , ...$args ] ) ;
}
