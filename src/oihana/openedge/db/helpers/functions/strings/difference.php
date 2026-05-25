<?php

namespace oihana\openedge\db\helpers\functions\strings;

use oihana\openedge\db\enums\functions\StringFunction;
use function oihana\core\strings\func;

/**
 * Generates a Progress OpenEdge SQL `DIFFERENCE()` expression.
 *
 * The `DIFFERENCE()` function returns an integer value indicating the difference
 * between the `SOUNDEX` values of two character expressions.
 *
 * **SQL Syntax:**
 * ```
 * DIFFERENCE(character_expression1, character_expression2)
 * ```
 *
 * @param string $expression1 The first character expression.
 * @param string $expression2 The second character expression.
 *
 * @return string The generated SQL `DIFFERENCE()` expression.
 * Example: `DIFFERENCE(word1, word2)`
 *
 * @example
 * ```php
 * echo difference('word1', 'word2'); // outputs: DIFFERENCE(word1,word2)
 * ```
 *
 * @see StringFunction::DIFFERENCE
 */
function difference(string $expression1, string $expression2): string
{
    return func(StringFunction::DIFFERENCE, [$expression1, $expression2]);
}
