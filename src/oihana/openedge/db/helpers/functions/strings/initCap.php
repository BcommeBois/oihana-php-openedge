<?php

namespace oihana\openedge\db\helpers\functions\strings;

use oihana\openedge\db\enums\functions\StringFunction;
use function oihana\core\strings\func;

/**
 * Generates a Progress OpenEdge SQL `INITCAP()` expression.
 *
 * The `INITCAP()` function converts the first character of each word to uppercase
 * and all other characters to lowercase.
 *
 * **SQL Syntax:**
 * ```
 * INITCAP(character_expression)
 * ```
 *
 * @param string $chars A character expression.
 *
 * @return string The generated SQL `INITCAP()` expression.
 * Example: `INITCAP(full_name)`
 *
 * @example
 * ```php
 * echo initCap('john doe'); // outputs: INITCAP('john doe')
 * ```
 *
 * @see StringFunction::INITCAP
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/INITCAP.html
 */
function initCap(string $chars): string
{
    return func(StringFunction::INITCAP, $chars);
}
