<?php

namespace oihana\openedge\db\helpers\functions\strings;

use oihana\openedge\db\enums\functions\StringFunction;
use function oihana\core\strings\func;

/**
 * Generates a Progress OpenEdge SQL `CONCAT()` expression.
 *
 * The `CONCAT()` function concatenates two character expressions into a single string.
 *
 * **SQL Syntax:**
 * ```
 * CONCAT(character_expression1, character_expression2)
 * ```
 *
 * @param mixed $expression1 The first character expression.
 * @param mixed $expression2 The second character expression.
 *
 * @return string The generated SQL `CONCAT()` expression.
 * Example: `CONCAT(first_name, last_name)`
 *
 * @example
 * ```php
 * echo concat('first_name', 'last_name'); // outputs: CONCAT(first_name,last_name)
 * ```
 *
 * @see StringFunction::CONCAT
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/CONCAT.html
 */
function concat(mixed $expression1, mixed $expression2): string
{
    return func(StringFunction::CONCAT, [$expression1, $expression2]);
}
