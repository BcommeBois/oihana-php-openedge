<?php

namespace oihana\openedge\db\helpers\functions\strings;

use oihana\openedge\db\enums\functions\StringFunction;
use function oihana\core\strings\func;

/**
 * Generates a Progress OpenEdge SQL `LOWER()` expression.
 *
 * The `LOWER()` function converts a string to lowercase.
 *
 * **SQL Syntax:**
 * ```
 * LOWER(character_expression)
 * ```
 *
 * @param string $chars A character expression.
 *
 * @return string The generated SQL `LOWER()` expression.
 *
 * @example
 * ```php
 * echo lower('JOHN DOE'); // outputs: LOWER('JOHN DOE')
 * ```
 *
 * @see StringFunction::LOWER
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/LOWER.html
 */
function lower(string $chars): string
{
    return func(StringFunction::LOWER, $chars);
}
