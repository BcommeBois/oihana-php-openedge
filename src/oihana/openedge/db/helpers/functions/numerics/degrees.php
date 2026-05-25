<?php

namespace oihana\openedge\db\helpers\functions\numerics;

use oihana\openedge\db\enums\functions\NumericFunction;
use function oihana\core\strings\func;

/**
 * Generates a Progress OpenEdge SQL `DEGREES()` expression.
 *
 * The `DEGREES()` function converts an angle from radians to degrees.
 *
 * **SQL Syntax:**
 * ```
 * DEGREES(angle_in_radians)
 * ```
 *
 * @param float|int|string $angle The angle in radians, provided as a numeric SQL expression or column name.
 *
 * @return string The generated SQL `DEGREES()` expression.
 * Example: `DEGREES(column)`
 *
 * @example
 * ```php
 * echo degrees('angle_rad'); // outputs: DEGREES(angle_rad)
 * echo degrees(1.5708);      // outputs: DEGREES(1.5708)
 * ```
 *
 * @see NumericFunction::DEGREES
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/DEGREES.html
 */
function degrees( float|int|string $angle ):string
{
    return func(NumericFunction::DEGREES , $angle ) ;
}
