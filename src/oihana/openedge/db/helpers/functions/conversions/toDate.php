<?php

namespace oihana\openedge\db\helpers\functions\conversions ;

use oihana\openedge\db\enums\functions\ConversionFunction;

use function oihana\core\strings\func;

/**
 * Generates a Progress OpenEdge SQL `TO_DATE()` expression.
 *
 * Converts the given string expression into a DATE value.
 * OpenEdge SQL does **not** require specifying a format string—
 * it uses an internal default date format for parsing.
 *
 * SQL Syntax:
 * ```
 * TO_DATE(expression)
 * ```
 *
 * @param string $expression The string expression or column name to convert to DATE.
 *
 * @return string The generated SQL `TO_DATE()` expression, e.g., `TO_DATE('2025-08-26')`
 *
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/TO_DATE.html
 */
function toDate( string $expression ) :string
{
    return func( ConversionFunction::TO_DATE , $expression ) ;
}