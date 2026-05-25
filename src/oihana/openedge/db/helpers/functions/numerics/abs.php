<?php

namespace oihana\openedge\db\helpers\functions\numerics;

use oihana\openedge\db\enums\functions\NumericFunction;
use function oihana\core\strings\func;

/**
 * Generates a Progress OpenEdge SQL `ABS()` expression.
 *
 * The `ABS()` function returns the absolute (non-negative) value of a numeric expression.
 * It takes a single argument that must be of a numeric data type (e.g., `INTEGER`, `NUMERIC`, `REAL`).
 *
 * **SQL Syntax:**
 * ```
 * ABS(numeric_expression)
 * ```
 *
 * @param float|int|string $expression The numeric SQL expression or column name.
 *
 * @return string The generated SQL `ABS()` expression.
 * Example: `ABS(column)`
 *
 * @example
 * ```php
 * echo abs('balance'); // outputs: ABS(balance)
 * echo abs(-100);      // outputs: ABS(-100)
 * ```
 *
 * @see NumericFunction::ABS
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/ABS.html
 */
function abs( float|int|string $expression ):string
{
    return func(NumericFunction::ABS , $expression ) ;
}
