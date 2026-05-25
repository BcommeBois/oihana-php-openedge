<?php

namespace oihana\openedge\db\helpers\functions\strings;

use oihana\openedge\db\enums\functions\StringFunction;
use function oihana\core\strings\func;

/**
 * Generates a Progress OpenEdge SQL `LCASE()` expression.
 *
 * The `LCASE()` function converts a string to lowercase. It is an ODBC-compatible
 * equivalent of `LOWER()`.
 *
 * **SQL Syntax:**
 * ```
 * LCASE(character_expression)
 * ```
 *
 * @param string $chars A character expression.
 *
 * @return string The generated SQL `LCASE()` expression.
 *
 * @example
 * ```php
 * echo lcase('JOHN DOE'); // outputs: LCASE('JOHN DOE')
 * ```
 *
 * @see StringFunction::LCASE
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/LCASE.html
 */
function lcase(string $chars): string
{
    return func(StringFunction::LCASE, $chars);
}
