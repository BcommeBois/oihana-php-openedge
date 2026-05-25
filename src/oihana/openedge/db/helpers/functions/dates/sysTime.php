<?php

namespace oihana\openedge\db\helpers\functions\dates;

use oihana\openedge\db\enums\functions\DateFunction;

use function oihana\core\strings\func ;

/**
 * Generates a Progress OpenEdge SQL expression that returns the current system time.
 *
 * Corresponds to the `SYSTIME()` function in OpenEdge SQL.
 * Takes no arguments and returns a TIME value.
 *
 * **SQL Syntax:**
 * ```
 * SYSTIME()
 * ```
 *
 * **Example usage:**
 * ```php
 * echo sysTime(); // Outputs: SYSTIME()
 * ```
 *
 * @return string The SQL expression for the current system time.
 *
 * @see DateFunction::SYSTIME
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/SYSTIME.html
 */
function sysTime() :string
{
    return func(DateFunction::SYSTIME ) ;
}