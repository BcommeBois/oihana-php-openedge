<?php

namespace oihana\openedge\db\helpers\functions\strings;

use oihana\openedge\db\enums\functions\StringFunction;
use function oihana\core\strings\func;

/**
 * Generates a Progress OpenEdge SQL `PREFIX()` expression.
 *
 * The `PREFIX()` function returns the substring that precedes a specific character indicator.
 *
 * **SQL Syntax:**
 * ```
 * PREFIX(character_expression, start_position, indicator_expression)
 * ```
 *
 * @param string $expression The character expression to search within.
 * @param int    $startPosition The position to start the search.
 * @param string $indicator The character to search for.
 *
 * @return string The generated SQL `PREFIX()` expression.
 *
 * @example
 * ```php
 * echo prefix('a-b-c', 1, '-'); // outputs: PREFIX('a-b-c',1,'-')
 * ```
 *
 * @see StringFunction::PREFIX
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/PREFIX.html
 */
function prefix(string $expression, int $startPosition, string $indicator): string
{
    return func(StringFunction::PREFIX, [$expression, $startPosition, $indicator]);
}
