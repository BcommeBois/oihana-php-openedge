<?php

namespace oihana\openedge\db\helpers\functions\dates;

use oihana\openedge\db\enums\functions\DateFunction;

use function oihana\core\strings\func ;

/**
 * Generates a Progress OpenEdge SQL expression that returns the current system timestamp.
 *
 * Corresponds to the `SYSTIMESTAMP()` function in OpenEdge SQL.
 * Takes no arguments and returns a TIMESTAMP value.
 *
 * **SQL Syntax:**
 * ```
 * SYSTIMESTAMP()
 * ```
 *
 * **Example usage:**
 * ```php
 * echo sysTimestamp(); // Outputs: SYSTIMESTAMP()
 * ```
 *
 * @return string The SQL expression for the current system timestamp.
 *
 * @see DateFunction::SYSTIMESTAMP
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/SYSTIMESTAMP.html
 */
function sysTimestamp(): string
{
    return func(DateFunction::SYSTIMESTAMP ) ;
}