<?php

namespace oihana\openedge\db\helpers\functions\strings;

use oihana\openedge\db\enums\functions\StringFunction;
use function oihana\core\strings\func;

/**
 * Generates a Progress OpenEdge SQL `UCASE()` expression.
 *
 * The `UCASE()` function converts a string to uppercase. It is an ODBC-compatible
 * equivalent of `UPPER()`.
 *
 * **SQL Syntax:**
 * ```
 * UCASE(character_expression)
 * ```
 *
 * @param string $chars A character expression.
 *
 * @return string The generated SQL `UCASE()` expression.
 *
 * @example
 * ```php
 * echo ucase('john doe'); // outputs: UCASE('john doe')
 * ```
 *
 * @see StringFunction::UCASE
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/UCASE.html
 */
function ucase(string $chars): string
{
    return func(StringFunction::UCASE, $chars);
}
