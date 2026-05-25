<?php

namespace oihana\openedge\db\helpers\functions\strings;

use oihana\openedge\db\enums\functions\StringFunction;
use function oihana\core\strings\func;

/**
 * Generates a Progress OpenEdge SQL `PRO_ARR_DESCAPE()` expression.
 *
 * The `PRO_ARR_DESCAPE()` function removes escape characters from a single
 * element of a character array.
 *
 * **SQL Syntax:**
 * ```
 * PRO_ARR_DESCAPE('character_element')
 * ```
 *
 * @param string $char A character expression.
 *
 * @return string The generated SQL `PRO_ARR_DESCAPE()` expression.
 *
 * @example
 * ```php
 * echo proArrayDescape('a~;b'); // outputs: PRO_ARR_DESCAPE('a~;b')
 * ```
 *
 * @see StringFunction::PRO_ARR_DESCAPE
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/PRO_ARR_DESCAPE-function.html
 */
function proArrayDescape(string $char): string
{
    return func(StringFunction::PRO_ARR_DESCAPE, $char);
}
