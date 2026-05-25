<?php

namespace oihana\openedge\db\helpers\functions\dates;

use oihana\openedge\db\enums\functions\DateFunction;

use function oihana\core\strings\func ;

/**
 * Generates a Progress OpenEdge SQL expression that returns the current date and time.
 *
 * Corresponds to the `NOW()` function in OpenEdge SQL.
 * Takes no arguments and returns a TIMESTAMP value.
 *
 * **SQL Syntax:**
 * ```
 * NOW()
 * ```
 *
 * **Example usage:**
 * ```php
 * echo now(); // Outputs: NOW()
 * ```
 *
 * @return string The SQL expression for the current timestamp.
 *
 * @see DateFunction::NOW
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/NOW.html
 */
function now(): string
{
    return func(DateFunction::NOW ) ;
}