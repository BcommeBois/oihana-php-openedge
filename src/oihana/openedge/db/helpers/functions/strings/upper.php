<?php

namespace oihana\openedge\db\helpers\functions\strings;

use oihana\openedge\db\enums\functions\StringFunction;
use function oihana\core\strings\func;

/**
 * Generates a Progress OpenEdge SQL `UPPER()` expression.
 *
 * The `UPPER()` function converts a string to uppercase.
 *
 * **SQL Syntax:**
 * ```
 * UPPER(character_expression)
 * ```
 *
 * @param string $chars A character expression.
 *
 * @return string The generated SQL `UPPER()` expression.
 *
 * @example
 * ```php
 * echo upper('john doe'); // outputs: UPPER('john doe')
 * ```
 *
 * @see StringFunction::UPPER
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/UPPER.html
 */
function upper(string $chars): string
{
    return func(StringFunction::UPPER, $chars);
}
