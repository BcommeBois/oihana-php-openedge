<?php

namespace oihana\openedge\db\helpers\functions\conditionals;

use oihana\openedge\db\enums\functions\ConditionalFunction;

use function oihana\core\strings\func;

/**
 * Generates an OpenEdge SQL `IFNULL` function expression.
 *
 * The `IFNULL` function returns a specified value when the given expression
 * evaluates to `NULL`. Otherwise, it returns the expression itself.
 *
 * This function is useful for ensuring that queries produce a default value
 * when certain fields are missing or contain `NULL`.
 *
 * **SQL Syntax:**
 * ```
 * IFNULL(expr, value)
 * ```
 *
 * **Examples:**
 * ```php
 * use function oihana\openedge\db\helpers\functions\conversions\ifNull;
 *
 * // Returns: "IFNULL(price, 0)"
 * echo ifNull('price', 0);
 *
 * // Returns: "IFNULL(description, 'N/A')"
 * echo ifNull('description', "'N/A'");
 *
 * // Nested usage example
 * echo ifNull("IFNULL(stock, 0)", 10); // "IFNULL(IFNULL(stock, 0), 10)"
 * ```
 *
 * @param mixed         $expression The expression or column name to evaluate.
 * @param mixed         $value      The value returned when `$expression` is `NULL`.
 * @param callable|null $map        Optional. A callback applied to `$value` before generating the SQL expression.
 *
 * @return string The generated SQL `IFNULL` function call.
 *
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/IFNULL.html
 */
function ifNull( mixed $expression , mixed $value , ?callable $map = null ) :string
{
    if( isset( $map ) )
    {
        $value = $map( $value ) ;
    }
    return func( ConditionalFunction::IFNULL , [ $expression , $value ] ) ; // FIXME expression( $value )
}