<?php

namespace oihana\openedge\db\helpers\functions\conditionals;

use oihana\openedge\db\enums\functions\ConditionalFunction;

use function oihana\core\strings\func;

/**
 * Generates an OpenEdge SQL `NULLIF` function expression.
 *
 * The `NULLIF` function compares two expressions and returns `NULL` if they
 * are equal. If the two expressions are not equal, it returns the value of
 * the first expression (`expression1`).
 *
 * This is particularly useful when you want to transform specific values
 * into `NULL` for normalization or consistency in your dataset.
 *
 * **SQL Syntax:**
 * ```
 * NULLIF(expression1, expression2)
 * ```
 *
 * **Behavior:**
 * - If `expression1 = expression2` → returns `NULL`
 * - Else → returns `expression1`
 *
 * **Examples:**
 * ```php
 * use function oihana\openedge\db\helpers\functions\conditionals\nullIf;
 *
 * // Simple usage: returns "NULLIF(price, 0)"
 * echo nullIf('price', 0);
 *
 * // Transform 'N/A' into NULL: returns "NULLIF(description, 'N/A')"
 * echo nullIf('description', "'N/A'");
 *
 * // If both expressions are identical: returns "NULLIF(status, status)"
 * echo nullIf('status', 'status');
 *
 * // Nested example: returns "NULLIF(NULLIF(col1, col2), 0)"
 * echo nullIf(nullIf('col1', 'col2'), 0);
 * ```
 *
 * @param mixed              $expression1 The first expression to evaluate and potentially return.
 * @param mixed              $expression2 The value to compare against `$expression1`.
 * @param callable|null      $map         Optional. A callback applied to `$value` before generating the SQL expression.
 *
 * @return string The generated SQL `NULLIF` function call.
 *
 * @see https://docs.progress.com/bundle/openedge-sql-reference-122/page/NULLIF.html
 */
function nullIf( mixed $expression1 , mixed $expression2 , ?callable $map = null ) :string
{
    if( isset( $map ) )
    {
        $expression2 = $map( $expression2 ) ;
    }
    return func( ConditionalFunction::NULLIF , [ $expression1 , $expression2 ] ) ; // FIXME $this->expression( $expression2 )
}