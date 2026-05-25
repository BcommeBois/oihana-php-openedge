<?php

namespace oihana\openedge\db\helpers\functions\strings;

use oihana\openedge\db\enums\functions\StringFunction;
use function oihana\core\strings\func;

/**
 * Generates a Progress OpenEdge SQL `PRO_ARR_ESCAPE()` expression.
 *
 * The `PRO_ARR_ESCAPE()` function adds required escape characters to a single
 * element of a character array.
 *
 * **SQL Syntax:**
 * ```
 * PRO_ARR_ESCAPE('character_element')
 * ```
 *
 * @param string $char A character expression.
 *
 * @return string The generated SQL `PRO_ARR_ESCAPE()` expression.
 *
 * @example
 * ```php
 * echo proArrayEscape('a;b'); // outputs: PRO_ARR_ESCAPE('a;b')
 * ```
 *
 * @see StringFunction::PRO_ARR_ESCAPE
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/PRO_ARR_ESCAPE-function.html
 */
function proArrayEscape(string $char): string
{
    return func(StringFunction::PRO_ARR_ESCAPE, $char);
}
