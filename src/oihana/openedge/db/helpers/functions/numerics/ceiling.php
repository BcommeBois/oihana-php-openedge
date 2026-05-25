<?php

namespace oihana\openedge\db\helpers\functions\numerics;

use oihana\openedge\db\enums\functions\NumericFunction;
use function oihana\core\strings\func;

/**
 * Generates a Progress OpenEdge SQL `CEILING()` expression.
 *
 * The `CEILING()` function returns the smallest integer value that is greater than
 * or equal to a numeric expression.
 *
 * **SQL Syntax:**
 * ```
 * CEILING(numeric_expression)
 * ```
 *
 * @param float|int|string $expression The numeric SQL expression or column name.
 *
 * @return string The generated SQL `CEILING()` expression.
 * Example: `CEILING(column)`
 *
 * @example
 * ```php
 * echo ceiling('price'); // outputs: CEILING(price)
 * echo ceiling(4.2);     // outputs: CEILING(4.2)
 * ```
 *
 * @see NumericFunction::CEILING
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/CEILING.html
 */
function ceiling( float|int|string $expression ):string
{
    return func(NumericFunction::CEILING , $expression ) ;
}
