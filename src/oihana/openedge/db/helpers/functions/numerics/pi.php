<?php

namespace oihana\openedge\db\helpers\functions\numerics;

use oihana\openedge\db\enums\functions\NumericFunction;
use function oihana\core\strings\func;

/**
 * Generates a Progress OpenEdge SQL `PI()` expression.
 *
 * The `PI()` function returns the constant value of Pi.
 *
 * **SQL Syntax:**
 * ```
 * PI()
 * ```
 *
 * @return string The generated SQL `PI()` expression.
 * Example: `PI()`
 *
 * @example
 * ```php
 * echo pi(); // outputs: PI()
 * ```
 *
 * @see NumericFunction::PI
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/PI.html
 */
function pi():string
{
    return func(NumericFunction::PI ) ;
}
