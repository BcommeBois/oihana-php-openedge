<?php

namespace oihana\openedge\db\helpers\functions\strings;

use oihana\openedge\db\enums\functions\StringFunction;
use function oihana\core\strings\func;

/**
 * Generates a Progress OpenEdge SQL `INSTR()` expression.
 *
 * The `INSTR()` function searches for a substring within a string and returns the
 * starting position of the nth occurrence.
 *
 * **SQL Syntax:**
 * ```
 * INSTR(string_to_search, substring, [start_position], [occurrence])
 * ```
 *
 * @param string $expression The string to search within.
 * @param string $search The substring to search for.
 * @param int    $startPosition Optional. The position to start the search. Defaults to 1.
 * @param int    $occurrence Optional. The nth occurrence to find. Defaults to 1.
 *
 * @return string The generated SQL `INSTR()` expression.
 *
 * @example
 * ```php
 * echo inString('abcabc', 'b', 1, 2); // outputs: INSTR('abcabc','b',1,2)
 * ```
 *
 * @see StringFunction::INSTR
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/INSTR.html
 */
function inString(string $expression, string $search, int $startPosition = 1, int $occurrence = 1): string
{
    return func(StringFunction::INSTR, [$expression, $search, $startPosition, $occurrence]);
}
