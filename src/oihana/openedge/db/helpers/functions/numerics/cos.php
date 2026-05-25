<?php

namespace oihana\openedge\db\helpers\functions\numerics;

use oihana\openedge\db\enums\functions\NumericFunction;
use function oihana\core\strings\func;

/**
 * Generates a Progress OpenEdge SQL `COS()` expression.
 *
 * The `COS()` function returns the cosine of an angle specified in radians.
 *
 * **SQL Syntax:**
 * ```
 * COS(angle_in_radians)
 * ```
 *
 * @param float|int|string $angle The angle in radians, provided as a numeric SQL expression or column name.
 *
 * @return string The generated SQL `COS()` expression.
 * Example: `COS(column)`
 *
 * @example
 * ```php
 * echo cos('angle');  // outputs: COS(angle)
 * echo cos(3.14159); // outputs: COS(3.14159)
 * ```
 *
 * @see NumericFunction::COS
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/COS.html
 */
function cos( float|int|string $angle ):string
{
    return func(NumericFunction::COS , $angle ) ;
}
