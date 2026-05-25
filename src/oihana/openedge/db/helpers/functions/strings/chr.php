<?php

namespace oihana\openedge\db\helpers\functions\strings;

use oihana\openedge\db\enums\functions\StringFunction;
use function oihana\core\strings\func;

/**
 * Generates a Progress OpenEdge SQL `CHR()` expression.
 *
 * The `CHR()` function is similar to `CHAR()` and returns the character corresponding
 * to an ASCII value. The input must be an integer expression.
 *
 * **SQL Syntax:**
 * ```
 * CHR(integer_expression)
 * ```
 *
 * @param string|int $expression The integer ASCII value.
 *
 * @return string The generated SQL `CHR()` expression.
 * Example: `CHR(65)`
 *
 * @example
 * ```php
 * echo chr(65); // outputs: CHR(65)
 * ```
 *
 * @see StringFunction::CHR
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/CHR.html
 */
function chr(string|int $expression): string
{
    return func(StringFunction::CHR, $expression);
}
