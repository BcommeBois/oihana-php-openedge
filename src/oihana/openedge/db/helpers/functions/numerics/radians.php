<?php

namespace oihana\openedge\db\helpers\functions\numerics;

use oihana\openedge\db\enums\functions\NumericFunction;
use function oihana\core\strings\func;

/**
 * Generates a Progress OpenEdge SQL `RADIANS()` expression.
 *
 * The `RADIANS()` function converts an angle from degrees to radians.
 *
 * **SQL Syntax:**
 * ```
 * RADIANS(angle_in_degrees)
 * ```
 *
 * @param float|int|string $angle The angle in degrees, provided as a numeric SQL expression or column name.
 *
 * @return string The generated SQL `RADIANS()` expression.
 * Example: `RADIANS(column)`
 *
 * @example
 * ```php
 * echo radians('angle_deg'); // outputs: RADIANS(angle_deg)
 * echo radians(180);         // outputs: RADIANS(180)
 * ```
 *
 * @see NumericFunction::RADIANS
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/RADIANS.html
 */
function radians( float|int|string $angle ):string
{
    return func(NumericFunction::RADIANS , $angle ) ;
}
