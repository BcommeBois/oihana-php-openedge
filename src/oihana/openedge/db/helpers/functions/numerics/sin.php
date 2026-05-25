<?php

namespace oihana\openedge\db\helpers\functions\numerics;

use oihana\openedge\db\enums\functions\NumericFunction;
use function oihana\core\strings\func;

/**
 * Generates a Progress OpenEdge SQL `SIN()` expression.
 *
 * The `SIN()` function returns the sine of an angle specified in radians.
 *
 * **SQL Syntax:**
 * ```
 * SIN(angle_in_radians)
 * ```
 *
 * @param float|int|string $angle The angle in radians, provided as a numeric SQL expression or column name.
 *
 * @return string The generated SQL `SIN()` expression.
 * Example: `SIN(column)`
 *
 * @example
 * ```php
 * echo sin('angle');  // outputs: SIN(angle)
 * echo sin(1.5708);  // outputs: SIN(1.5708)
 * ```
 *
 * @see NumericFunction::SIN
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/SIN.html
 */
function sin( float|int|string $angle ):string
{
    return func(NumericFunction::SIN , $angle ) ;
}
