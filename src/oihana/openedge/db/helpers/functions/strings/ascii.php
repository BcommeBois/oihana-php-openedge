<?php

namespace oihana\openedge\db\helpers\functions\strings;

use oihana\openedge\db\enums\functions\StringFunction;
use function oihana\core\strings\func;

/**
 * Generates a Progress OpenEdge SQL `ASCII()` expression.
 *
 * The `ASCII()` function returns the ASCII value of the first character of a character expression.
 *
 * **SQL Syntax:**
 * ```
 * ASCII(character_expression)
 * ```
 *
 * @param string $char A character expression.
 *
 * @return string The generated SQL `ASCII()` expression.
 * Example: `ASCII(column)`
 *
 * @example
 * ```php
 * echo ascii('A'); // outputs: ASCII('A')
 * ```
 *
 * @see StringFunction::ASCII
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/ASCII.html
 */
function ascii( string $char ): string
{
    return func(StringFunction::ASCII , $char ) ;
}
