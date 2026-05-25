<?php

namespace oihana\openedge\db\helpers\functions\strings;

use oihana\openedge\db\enums\functions\StringFunction;
use function oihana\core\strings\func;

/**
 * Generates a Progress OpenEdge SQL `REPEAT()` expression.
 *
 * The `REPEAT()` function repeats a string a specified number of times.
 *
 * **SQL Syntax:**
 * ```
 * REPEAT(string_to_repeat, count)
 * ```
 *
 * @param string $expression The string to repeat.
 * @param int    $count The number of times to repeat the string.
 *
 * @return string The generated SQL `REPEAT()` expression.
 *
 * @example
 * ```php
 * echo repeat('abc', 3); // outputs: REPEAT('abc',3)
 * ```
 *
 * @see StringFunction::REPEAT
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/REPEAT.html
 */
function repeat( string $expression , int $count ) :string
{
    return func(StringFunction::REPEAT, [ $expression , $count ] );
}
