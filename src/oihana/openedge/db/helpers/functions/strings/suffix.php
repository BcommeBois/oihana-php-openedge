<?php

namespace oihana\openedge\db\helpers\functions\strings;

use oihana\openedge\db\enums\functions\StringFunction;
use function oihana\core\strings\func;

/**
 * Generates a Progress OpenEdge SQL `SUFFIX()` expression.
 *
 * The `SUFFIX()` function returns the substring that follows a specific character indicator.
 *
 * **SQL Syntax:**
 * ```
 * SUFFIX(character_expression, start_position, indicator_expression)
 * ```
 *
 * @param string $expression The character expression to search within.
 * @param int    $startPosition The position to start the search.
 * @param string $indicator The character to search for.
 *
 * @return string The generated SQL `SUFFIX()` expression.
 *
 * @example
 * ```php
 * echo suffix('a-b-c', 1, '-'); // outputs: SUFFIX('a-b-c',1,'-')
 * ```
 *
 * @see StringFunction::SUFFIX
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/SUFFIX.html
 */
function suffix(string $expression, int $startPosition, string $indicator): string
{
    return func(StringFunction::SUFFIX, [$expression, $startPosition, $indicator]);
}
