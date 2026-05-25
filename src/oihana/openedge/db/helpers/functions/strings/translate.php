<?php

namespace oihana\openedge\db\helpers\functions\strings;

use oihana\openedge\db\enums\functions\StringFunction;
use function oihana\core\strings\func;

/**
 * Generates a Progress OpenEdge SQL `TRANSLATE()` expression.
 *
 * The `TRANSLATE()` function replaces a sequence of characters in a string with
 * another sequence of characters.
 *
 * **SQL Syntax:**
 * ```
 * TRANSLATE(string_to_translate, from_set, to_set)
 * ```
 *
 * @param string $chars The string to translate.
 * @param string $from  The set of characters to replace.
 * @param string $to    The set of replacement characters.
 *
 * @return string The generated SQL `TRANSLATE()` expression.
 *
 * @example
 * ```php
 * echo translate('abc', 'a', 'x'); // outputs: TRANSLATE('abc','a','x')
 * ```
 *
 * @see StringFunction::TRANSLATE
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/TRANSLATE.html
 */
function translate( string $chars , string $from , string $to ) :string
{
    return func(StringFunction::TRANSLATE, [$chars, $from, $to]);
}
