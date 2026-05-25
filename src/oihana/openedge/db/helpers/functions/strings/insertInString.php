<?php

namespace oihana\openedge\db\helpers\functions\strings;

use oihana\openedge\db\enums\functions\StringFunction;
use function oihana\core\strings\func;

/**
 * Generates a Progress OpenEdge SQL `INSERT()` expression.
 *
 * The `INSERT()` function inserts a string into another string at a specified position,
 * replacing a certain number of characters.
 *
 * **SQL Syntax:**
 * ```
 * INSERT(string_to_modify, start_position, length_to_replace, string_to_insert)
 * ```
 *
 * @param string $expression1 The string to modify.
 * @param int    $startPosition The starting position for insertion.
 * @param int    $length The number of characters to replace.
 * @param string $expression2 The string to insert.
 *
 * @return string The generated SQL `INSERT()` expression.
 *
 * @example
 * ```php
 * echo insertInString('abcdef', 2, 3, 'xyz'); // outputs: INSERT('abcdef',2,3,'xyz')
 * ```
 *
 * @see StringFunction::INSERT
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/INSERT_4.html
 */
function insertInString(string $expression1, int $startPosition, int $length, string $expression2): string
{
    return func(StringFunction::INSERT, [$expression1, $startPosition, $length, $expression2]);
}
