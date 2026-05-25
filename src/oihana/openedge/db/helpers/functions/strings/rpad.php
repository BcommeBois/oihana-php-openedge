<?php

namespace oihana\openedge\db\helpers\functions\strings;

use oihana\openedge\db\enums\functions\StringFunction;
use function oihana\core\strings\func;

/**
 * Generates a Progress OpenEdge SQL `RPAD()` expression.
 *
 * The `RPAD()` function pads a string on the right to a specified length with a
 * specified set of characters.
 *
 * **SQL Syntax:**
 * ```
 * RPAD(character_expression, length, [pad_expression])
 * ```
 *
 * @param string $expression The string to pad.
 * @param int    $length The desired length of the resulting string.
 * @param mixed  $padExpression The character(s) to use for padding. Defaults to a space.
 *
 * @return string The generated SQL `RPAD()` expression.
 *
 * @example
 * ```php
 * echo rpad('abc', 5, '-'); // outputs: RPAD('abc',5,'-')
 * ```
 *
 * @see StringFunction::RPAD
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/RPAD.html
 */
function rpad(string $expression, int $length, mixed $padExpression = null ): string
{
    return func(StringFunction::RPAD, [ $expression , $length , $padExpression ] );
}
