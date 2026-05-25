<?php

namespace oihana\openedge\db\helpers\functions\numerics;

use oihana\openedge\db\enums\functions\NumericFunction;
use function oihana\core\strings\func;

/**
 * Generates a Progress OpenEdge SQL `ACOS()` expression.
 *
 * The `ACOS()` function returns the arccosine (in radians) of a numeric expression.
 * The input expression must be between -1 and 1, inclusive.
 *
 * **SQL Syntax:**
 * ```
 * ACOS(numeric_expression)
 * ```
 *
 * @param float|int|string $expression The numeric SQL expression or column name. Must be between -1 and 1.
 *
 * @return string The generated SQL `ACOS()` expression.
 * Example: `ACOS(column)`
 *
 * @example
 * ```php
 * echo acos('cos_value'); // outputs: ACOS(cos_value)
 * echo acos(0.5);        // outputs: ACOS(0.5)
 * ```
 *
 * @see NumericFunction::ACOS
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/ACOS.html
 */
function acos( float|int|string $expression ) :string
{
    return func(NumericFunction::ACOS , $expression ) ;
}

