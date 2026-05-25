<?php

namespace oihana\openedge\db\helpers\functions\conditionals;

use oihana\enums\Char;
use oihana\openedge\db\enums\functions\ConditionalFunction;

use function oihana\core\strings\func;
use function oihana\openedge\db\helpers\literal;


/**
 * Generates an OpenEdge SQL `NULLIF` function expression that returns `NULL`
 * when the given expression is an empty string (`''`), otherwise returns the
 * expression itself.
 *
 * This is useful to normalize empty strings into `NULL` values in queries,
 * ensuring consistency when handling optional or missing data.
 *
 * **SQL Syntax:**
 * ```
 * NULLIF(expression, '')
 * ```
 *
 * **Behavior:**
 * - If `expression = ''` → returns `NULL`
 * - Else → returns `expression`
 *
 * **Examples:**
 * ```php
 * use function oihana\openedge\db\helpers\functions\conditionals\nullIfEmpty;
 *
 * // Returns: "NULLIF(username, '')"
 * echo nullIfEmpty('username');
 *
 * // Works with literals: returns "NULLIF(description, '')"
 * echo nullIfEmpty("'N/A'");
 *
 * // Nested usage example:
 * echo nullIfEmpty(nullIfEmpty('col1')); // "NULLIF(NULLIF(col1, ''), '')"
 * ```
 *
 * @param mixed $expression The expression to evaluate.
 *
 * @return string The generated SQL `NULLIF(expression, '')` function call.
 *
 * @see https://docs.progress.com/bundle/openedge-sql-reference-122/page/NULLIF.html
 */
function nullIfEmpty( mixed $expression ) :string
{
    return func( ConditionalFunction::NULLIF , [ $expression ,  literal(Char::EMPTY ) ] ) ;
}