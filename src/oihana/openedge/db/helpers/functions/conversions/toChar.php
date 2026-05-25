<?php

namespace oihana\openedge\db\helpers\functions\conversions;

use oihana\openedge\db\enums\functions\ConversionFunction;

use function oihana\core\strings\func;

/**
 * Generates a Progress OpenEdge SQL `TO_CHAR()` expression.
 *
 * The `TO_CHAR()` function converts the given SQL expression into a string.
 * Its primary use is to format the output of date, time, or numeric expressions
 * using an optional formatting string.
 *
 * **SQL Syntax:**
 * ```
 * TO_CHAR(expression [, format_string])
 * ```
 *
 * @param string      $expression The SQL expression or column name to convert to a string.
 * @param string|null $format     Optional. The format string used to control the output format.
 *                                If omitted, OpenEdge applies the default formatting.
 *
 * @return string The generated SQL `TO_CHAR()` expression.
 *
 * @example
 * ```php
 * echo toChar('order_date', 'YYYY-MM-DD');
 * // Outputs: TO_CHAR(order_date, 'YYYY-MM-DD')
 *
 * echo toChar('salary');
 * // Outputs: TO_CHAR(salary)
 * ```
 *
 * @see https://docs.progress.com/fr-FR/bundle/openedge-sql-reference/page/TO_CHAR.html
 * @see https://docs.progress.com/fr-FR/bundle/openedge-abl-internationalize-applications/page/Format-specifiers-allowed-with-the-TO_CHAR-and-TO_DATE-functions.html
 */
function toChar( string $expression , ?string $format = null ) :string
{
    return func( ConversionFunction::TO_CHAR , [ $expression , $format ] ) ;
}