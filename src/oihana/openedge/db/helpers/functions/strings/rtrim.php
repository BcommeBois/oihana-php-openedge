<?php

namespace oihana\openedge\db\helpers\functions\strings;

use oihana\openedge\db\enums\functions\StringFunction;
use function oihana\core\strings\func;

/**
 * Generates a Progress OpenEdge SQL `RTRIM()` expression.
 *
 * The `RTRIM()` function removes trailing characters from a string.
 *
 * **SQL Syntax:**
 * ```
 * RTRIM(character_expression, [character_set_to_trim])
 * ```
 *
 * @param string      $expression The string to trim.
 * @param string|null $charSet Optional. The set of characters to remove. Defaults to a space.
 *
 * @return string The generated SQL `RTRIM()` expression.
 *
 * @example
 * ```php
 * echo rtrim('abc  ', ' '); // outputs: RTRIM('abc  ',' ')
 * ```
 *
 * @see StringFunction::RTRIM
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/RTRIM.html
 */
function rtrim(string $expression, ?string $charSet = null): string
{
    $args = [$expression];
    if (isset($charSet)) {
        $args[] = $charSet;
    }
    return func(StringFunction::RTRIM, $args);
}
