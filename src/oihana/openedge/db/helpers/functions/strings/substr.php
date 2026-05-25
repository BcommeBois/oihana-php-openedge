<?php

namespace oihana\openedge\db\helpers\functions\strings;

use oihana\openedge\db\enums\functions\StringFunction;
use function oihana\core\strings\func;

/**
 * Generates a Progress OpenEdge SQL `SUBSTR()` expression.
 *
 * The `SUBSTR()` function extracts a substring of a specified length starting
 * from a given position. This is a Progress extension.
 *
 * **SQL Syntax:**
 * ```
 * SUBSTR(character_expression, start_position, [length])
 * ```
 *
 * @param string   $expression The source string.
 * @param int      $startPosition The starting position for extraction.
 * @param int|null $length Optional. The number of characters to extract.
 *
 * @return string The generated SQL `SUBSTR()` expression.
 *
 * @example
 * ```php
 * echo substr('abcdef', 2, 3); // outputs: SUBSTR('abcdef',2,3)
 * ```
 *
 * @see StringFunction::SUBSTR
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/SUBSTR.html
 */
function substr(string $expression, int $startPosition, ?int $length = null): string
{
    $args = [$expression, $startPosition];
    if (isset($length)) {
        $args[] = $length;
    }
    return func(StringFunction::SUBSTR, $args);
}
