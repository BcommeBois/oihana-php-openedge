<?php

namespace oihana\openedge\db\helpers\functions\dates;

use oihana\openedge\db\enums\functions\DateFunction;

use function oihana\core\strings\func ;

/**
 * Generates a Progress OpenEdge SQL expression that returns the current time.
 *
 * Corresponds to the `CURTIME()` function in OpenEdge SQL.
 * Takes no arguments and returns the current system time as a TIME value.
 *
 * **SQL Syntax:**
 * ```
 * CURTIME()
 * ```
 *
 * **Example usage:**
 * ```php
 * echo curTime(); // Outputs: CURTIME()
 * ```
 *
 * @return string The SQL expression for the current time.
 * @see DateFunction::CURTIME
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/CURTIME.html
 */
function curTime(): string
{
    return func(DateFunction::CURTIME ) ;
}