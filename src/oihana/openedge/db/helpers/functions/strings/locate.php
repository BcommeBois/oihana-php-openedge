<?php

namespace oihana\openedge\db\helpers\functions\strings;

use oihana\openedge\db\enums\functions\StringFunction;
use function oihana\core\strings\func;

/**
 * Generates a Progress OpenEdge SQL `LOCATE()` expression.
 *
 * The `LOCATE()` function finds the starting position of the first occurrence of a
 * substring within another string.
 *
 * **SQL Syntax:**
 * ```
 * LOCATE(substring, string_to_search, [start_position])
 * ```
 *
 * @param string $search The substring to search for.
 * @param string $expression The string to search within.
 * @param int|null $startPosition Optional. The position to start the search.
 *
 * @return string The generated SQL `LOCATE()` expression.
 *
 * @example
 * ```php
 * echo locate('b', 'abcabc', 2); // outputs: LOCATE('b','abcabc',2)
 * ```
 *
 * @see StringFunction::LOCATE
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/LOCATE.html
 */
function locate(string $search, string $expression, ?int $startPosition): string
{
    $args = [$search, $expression];
    if (isset($startPosition)) {
        $args[] = $startPosition;
    }
    return func(StringFunction::LOCATE, $args);
}
