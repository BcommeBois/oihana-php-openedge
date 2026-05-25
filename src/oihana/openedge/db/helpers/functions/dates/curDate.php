<?php

namespace oihana\openedge\db\helpers\functions\dates;

use oihana\openedge\db\enums\functions\DateFunction;
use function oihana\core\strings\func;

/**
 * Generates a Progress OpenEdge SQL expression that returns the current date.
 *
 * This function corresponds to the `CURDATE()` function in OpenEdge SQL.
 * It does **not** take any arguments and returns the current system date as a DATE value.
 *
 * **SQL Syntax:**
 * ```
 * CURDATE()
 * ```
 *
 * **Example usage:**
 * ```php
 * echo curDate(); // Outputs: CURDATE()
 * ```
 *
 * @return string The SQL expression for the current date.
 *
 * @see DateFunction::CURDATE
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/CURDATE.html
 */
function curDate() :string
{
    return func(DateFunction::CURDATE ) ;
}