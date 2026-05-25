<?php

namespace oihana\openedge\db\helpers\functions\strings;

use oihana\openedge\db\enums\functions\StringFunction;
use function oihana\core\strings\func;

/**
 * Generates a Progress OpenEdge SQL `REPLACE()` expression.
 *
 * The `REPLACE()` function replaces all occurrences of a substring with another substring.
 *
 * **SQL Syntax:**
 * ```
 * REPLACE(string_expression, old_substring, new_substring)
 * ```
 *
 * @param string $expression The string to modify.
 * @param string $old The substring to be replaced.
 * @param string $new The replacement substring.
 *
 * @return string The generated SQL `REPLACE()` expression.
 *
 * @example
 * ```php
 * echo replace('abc-def', '-', '_'); // outputs: REPLACE('abc-def','-','_')
 * ```
 *
 * @see StringFunction::REPLACE
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/REPLACE.html
 */
function replace(string $expression, string $old, string $new): string
{
    return func(StringFunction::REPLACE, [$expression, $old, $new]);
}
