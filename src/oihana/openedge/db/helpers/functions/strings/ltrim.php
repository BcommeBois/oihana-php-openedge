<?php

namespace oihana\openedge\db\helpers\functions\strings;

use oihana\openedge\db\enums\functions\StringFunction;
use function oihana\core\strings\func;

/**
 * Generates a Progress OpenEdge SQL `LTRIM()` expression.
 *
 * The `LTRIM()` function removes leading characters from a string.
 *
 * **SQL Syntax:**
 * ```
 * LTRIM(character_expression, [character_set_to_trim])
 * ```
 *
 * @param string      $expression The string to trim.
 * @param string|null $charSet Optional. The set of characters to remove. Defaults to a space.
 *
 * @return string The generated SQL `LTRIM()` expression.
 *
 * @example
 * ```php
 * echo ltrim('  abc', ' '); // outputs: LTRIM('  abc',' ')
 * ```
 *
 * @see StringFunction::LTRIM
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/LTRIM.html
 */
function ltrim(string $expression, ?string $charSet = null): string
{
    $args = [$expression];
    if (isset($charSet)) {
        $args[] = $charSet;
    }
    return func(StringFunction::LTRIM, $args);
}
