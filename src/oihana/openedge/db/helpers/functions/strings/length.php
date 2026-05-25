<?php

namespace oihana\openedge\db\helpers\functions\strings;

use oihana\openedge\db\enums\functions\StringFunction;
use function oihana\core\strings\func;

/**
 * Generates a Progress OpenEdge SQL `LENGTH()` expression.
 *
 * The `LENGTH()` function returns the number of characters in a string.
 *
 * **SQL Syntax:**
 * ```
 * LENGTH(character_expression)
 * ```
 *
 * @param string $expression A character expression.
 *
 * @return string The generated SQL `LENGTH()` expression.
 *
 * @example
 * ```php
 * echo length('abcdef'); // outputs: LENGTH('abcdef')
 * ```
 *
 * @see StringFunction::LENGTH
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/LENGTH.html
 */
function length(string $expression): string
{
    return func(StringFunction::LENGTH, $expression);
}
