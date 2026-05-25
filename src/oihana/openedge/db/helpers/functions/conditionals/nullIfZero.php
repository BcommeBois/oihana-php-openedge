<?php

namespace oihana\openedge\db\helpers\functions\conditionals;

use oihana\openedge\db\enums\functions\ConditionalFunction;

use function oihana\core\strings\func;

/**
 * Generates an OpenEdge SQL `NULLIF` function expression that returns `NULL`
 * when the given expression is a 0 value or expression, otherwise returns the expression itself.
 *
 * This is useful to normalize empty strings into `NULL` values in queries,
 * ensuring consistency when handling optional or missing data.
 *
 * **SQL Syntax:**
 * ```
 * NULLIF(expression, 0)
 * ```
 *
 * **Behavior:**
 * - If `expression = 0` → returns `NULL`
 * - Else → returns `expression`
 *
 * **Examples:**
 * ```php
 * use function oihana\openedge\db\helpers\functions\conditionals\nullIfZero;
 *
 * // Returns: "NULLIF(latitude, 0)"
 * echo nullIfZero('latitude');
 *
 * // Works with literals: returns "NULLIF(latitude, 0)"
 * echo nullIfZero("'N/A'");
 * ```
 *
 * @param mixed $expression The expression to evaluate.
 *
 * @return string The generated SQL `NULLIF(expression, '')` function call.
 *
 * @see https://docs.progress.com/bundle/openedge-sql-reference-122/page/NULLIF.html
 */
function nullIfZero( mixed $expression ) :string
{
    return func( ConditionalFunction::NULLIF , $expression . ', 0' ) ;
}