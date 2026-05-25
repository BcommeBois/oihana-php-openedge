<?php

namespace oihana\openedge\db\helpers\functions\conditionals;

use oihana\openedge\db\enums\functions\ConditionalFunction;

use function oihana\core\strings\func;

/**
 * Generates an OpenEdge SQL `NVL` function expression.
 *
 * The `NVL` function returns the value of the first expression if the first expression value is not NULL.
 * If the first expression value is NULL, the value of the second expression is returned.
 *
 * **Notes:**
 * - The NVL function is not ODBC compatible. Use the IFNULL function when ODBC‑compatible syntax is required.
 * - The first argument to the function can be of any type.
 * - The type of the second argument must be compatible with that of the first argument.
 * - The type of the result is the same as the first argument.
 *
 * **SQL Syntax:**
 * ```
 * NVL( expression1 , expression2 )
 * ```
 *
 * **Behavior:**
 * - If `expression1 != NULL` → returns `expression1`
 * - Else → returns `expression2`
 *
 * **Examples:**
 * ```php
 * use function oihana\openedge\db\helpers\functions\conditionals\nvl;
 *
 * echo nvl('price', 0);
 * ```
 *
 * @param mixed              $expression1 The first expression to evaluate and potentially return.
 * @param mixed              $expression2 The value to compare against `$expression1`.
 * @param callable|null      $map         Optional. A callback applied to `$expression2` before generating the SQL expression.
 *
 * @return string The generated SQL `NVL` function call.
 *
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/NVL.html
 */
function nvl( mixed $expression1 , mixed $expression2 , ?callable $map = null ) :string
{
    if( $expression2 === null )
    {
        $expression2 = 'NULL';
    }

    if( $map !== null )
    {
        $expression2 = $map( $expression2 ) ;
    }
    return func( ConditionalFunction::NVL , [ $expression1 , $expression2 ] ) ;
}