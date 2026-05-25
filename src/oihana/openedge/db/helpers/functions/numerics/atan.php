<?php

namespace oihana\openedge\db\helpers\functions\numerics;

use oihana\openedge\db\enums\functions\NumericFunction;
use function oihana\core\strings\func;

/**
 * Generates a Progress OpenEdge SQL `ATAN()` expression.
 *
 * The `ATAN()` function returns the arctangent (in radians) of a numeric expression.
 *
 * **SQL Syntax:**
 * ```
 * ATAN(numeric_expression)
 * ```
 *
 * @param float|int|string $expression The numeric SQL expression or column name.
 *
 * @return string The generated SQL `ATAN()` expression.
 * Example: `ATAN(column)`
 *
 * @example
 * ```php
 * echo atan('tan_value'); // outputs: ATAN(tan_value)
 * echo atan(1);          // outputs: ATAN(1)
 * ```
 *
 * @see NumericFunction::ATAN
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/ATAN.html
 */
function atan( float|int|string $expression ):string
{
    return func(NumericFunction::ATAN , $expression ) ;
}
