<?php

namespace oihana\openedge\db\helpers\functions\strings;

use oihana\openedge\db\enums\functions\StringFunction;
use function oihana\core\strings\func;

/**
 * Generates a Progress OpenEdge SQL `LEFT()` expression.
 *
 * The `LEFT()` function returns the specified number of characters from the
 * beginning of a string.
 *
 * **SQL Syntax:**
 * ```
 * LEFT(character_expression, count)
 * ```
 *
 * @param string $expression The character expression.
 * @param int    $count The number of characters to return.
 *
 * @return string The generated SQL `LEFT()` expression.
 *
 * @example
 * ```php
 * echo left('abcdef', 3); // outputs: LEFT('abcdef',3)
 * ```
 *
 * @see StringFunction::LEFT
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/LEFT.html
 */
function left(string $expression, int $count): string
{
    return func(StringFunction::LEFT, [$expression, $count]);
}
