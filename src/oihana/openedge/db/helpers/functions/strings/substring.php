<?php

namespace oihana\openedge\db\helpers\functions\strings;

use oihana\openedge\db\enums\functions\StringFunction;
use function oihana\core\strings\func;

/**
 * Generates a Progress OpenEdge SQL `SUBSTRING()` expression.
 *
 * The `SUBSTRING()` function extracts a substring of a specified length starting
 * from a given position. This is an ODBC-compatible function.
 *
 * **SQL Syntax:**
 * ```
 * SUBSTRING(character_expression, start_position, [length])
 * ```
 *
 * @param string   $expression The source string.
 * @param int      $startPosition The starting position for extraction.
 * @param int|null $length Optional. The number of characters to extract.
 *
 * @return string The generated SQL `SUBSTRING()` expression.
 *
 * @example
 * ```php
 * echo substring('abcdef', 2, 3); // outputs: SUBSTRING('abcdef',2,3)
 * ```
 *
 * @see StringFunction::SUBSTRING
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/SUBSTRING.html
 */
function substring(string $expression, int $startPosition, ?int $length = null): string
{
    $args = [$expression, $startPosition];
    if (isset($length)) {
        $args[] = $length;
    }
    return func(StringFunction::SUBSTRING, $args);
}
