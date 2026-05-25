<?php

namespace oihana\openedge\db\helpers\functions\numerics;

use oihana\openedge\db\enums\functions\NumericFunction;
use function oihana\core\strings\func;

/**
 * Generates a Progress OpenEdge SQL `TAN()` expression.
 *
 * The `TAN()` function returns the tangent of an angle specified in radians.
 *
 * **SQL Syntax:**
 * ```
 * TAN(angle_in_radians)
 * ```
 *
 * @param float|int|string $expression The angle in radians, provided as a numeric SQL expression or column name.
 *
 * @return string The generated SQL `TAN()` expression.
 * Example: `TAN(column)`
 *
 * @example
 * ```php
 * echo tan('angle');  // outputs: TAN(angle)
 * echo tan(0.7854);  // outputs: TAN(0.7854)
 * ```
 *
 * @see NumericFunction::TAN
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/TAN.html
 */
function tan( float|int|string $expression ):string
{
    return func(NumericFunction::TAN , $expression ) ;
}

