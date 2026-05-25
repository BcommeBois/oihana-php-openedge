<?php

namespace oihana\openedge\db\helpers\functions\dates;

use oihana\openedge\db\enums\functions\DateFunction;

use function oihana\core\strings\func ;

/**
 * Generates a Progress OpenEdge SQL expression that returns the current date.
 *
 * Corresponds to the `SYSDATE()` function in OpenEdge SQL.
 * Takes no arguments and returns a DATE value.
 * Trailing parentheses are optional in SQL.
 *
 * **SQL Syntax:**
 * ```
 * SYSDATE()
 * ```
 *
 * **Example usage:**
 * ```php
 * echo sysDate(); // Outputs: SYSDATE()
 * ```
 *
 * @return string The SQL expression for the current date.
 * @see DateFunction::SYSDATE
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/SYSDATE.html
 */
function sysDate(): string
{
    return func( DateFunction::SYSDATE ) ;
}