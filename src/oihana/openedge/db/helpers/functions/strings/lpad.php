<?php

namespace oihana\openedge\db\helpers\functions\strings;

use oihana\openedge\db\enums\functions\StringFunction;
use function oihana\core\strings\func;

/**
 * Generates a Progress OpenEdge SQL `LPAD()` expression.
 *
 * The `LPAD()` function pads a string on the left to a specified length with a
 * specified set of characters.
 *
 * **SQL Syntax:**
 * ```
 * LPAD(character_expression, length, [pad_expression])
 * ```
 *
 * @param string $expression The string to pad.
 * @param int    $length The desired length of the resulting string.
 * @param mixed  $padExpression The character(s) to use for padding. Defaults to a space.
 *
 * @return string The generated SQL `LPAD()` expression.
 *
 * @example
 * ```php
 * echo lpad('abc', 5, '-'); // outputs: LPAD('abc',5,'-')
 * ```
 *
 * @see StringFunction::LPAD
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/LPAD.html
 */
function lpad(string $expression, int $length, mixed $padExpression = null ): string
{
    return func(StringFunction::LPAD, [$expression, $length, $padExpression]);
}
