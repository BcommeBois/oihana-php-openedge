<?php

namespace oihana\openedge\db\helpers\functions\strings;

use oihana\openedge\db\enums\functions\StringFunction;
use function oihana\core\strings\func;

/**
 * Generates a Progress OpenEdge SQL `RIGHT()` expression.
 *
 * The `RIGHT()` function returns the specified number of characters from the
 * end of a string.
 *
 * **SQL Syntax:**
 * ```
 * RIGHT(character_expression, count)
 * ```
 *
 * @param string $expression The character expression.
 * @param int    $count The number of characters to return.
 *
 * @return string The generated SQL `RIGHT()` expression.
 *
 * @example
 * ```php
 * echo right('abcdef', 3); // outputs: RIGHT('abcdef',3)
 * ```
 *
 * @see StringFunction::RIGHT
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/RIGHT.html
 */
function right(string $expression, int $count): string
{
    return func(StringFunction::RIGHT, [$expression, $count]);
}
