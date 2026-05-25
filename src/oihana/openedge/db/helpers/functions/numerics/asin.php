<?php

namespace oihana\openedge\db\helpers\functions\numerics;

use oihana\openedge\db\enums\functions\NumericFunction;
use function oihana\core\strings\func;

/**
 * Generates a Progress OpenEdge SQL `ASIN()` expression.
 *
 * The `ASIN()` function returns the arcsine (in radians) of a numeric expression.
 * The input expression must be between -1 and 1, inclusive.
 *
 * **SQL Syntax:**
 * ```
 * ASIN(numeric_expression)
 * ```
 *
 * @param float|int|string $expression The numeric SQL expression or column name. Must be between -1 and 1.
 *
 * @return string The generated SQL `ASIN()` expression.
 * Example: `ASIN(column)`
 *
 * @example
 * ```php
 * echo asin('sin_value'); // outputs: ASIN(sin_value)
 * echo asin(0.5);        // outputs: ASIN(0.5)
 * ```
 *
 * @see NumericFunction::ASIN
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/ASIN.html
 */
function asin( float|int|string $expression ):string
{
    return func(NumericFunction::ASIN , $expression ) ;
}
