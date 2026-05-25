<?php

namespace oihana\openedge\db\helpers\functions\strings;

use oihana\openedge\db\enums\functions\StringFunction;
use function oihana\core\strings\func;

/**
 * Generates a Progress OpenEdge SQL `CHAR()` expression.
 *
 * The `CHAR()` function returns the character corresponding to an ASCII value.
 * The input must be an integer expression.
 *
 * **SQL Syntax:**
 * ```
 * CHAR(integer_expression)
 * ```
 *
 * @param string|int $expression The integer ASCII value.
 *
 * @return string The generated SQL `CHAR()` expression.
 * Example: `CHAR(65)`
 *
 * @example
 * ```php
 * echo char(65); // outputs: CHAR(65)
 * ```
 *
 * @see StringFunction::CHAR
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/CHAR.html
 */
function char(string|int $expression): string
{
    return func(StringFunction::CHAR, $expression);
}
