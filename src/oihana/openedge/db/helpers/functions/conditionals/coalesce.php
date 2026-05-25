<?php

namespace oihana\openedge\db\helpers\functions\conditionals;

use oihana\enums\Char;
use oihana\openedge\db\enums\functions\ConditionalFunction;

use function oihana\core\strings\func;

/**
 * Generates an OpenEdge SQL `COALESCE` function expression.
 *
 * The `COALESCE` function returns the first non-NULL value from a list of expressions.
 * If all expressions evaluate to NULL, it returns NULL.
 *
 * This function is useful for providing default values for potentially NULL columns
 * in SQL queries.
 *
 * **SQL Syntax:**
 * ```
 * COALESCE(expr1, expr2, ..., exprN)
 * ```
 *
 * **Examples:**
 * ```php
 * use function oihana\openedge\db\helpers\functions\conditionals\coalesce;
 *
 * // Returns: "COALESCE(price, 0)"
 * echo coalesce(['price', 0]);
 *
 * // Returns: "COALESCE(name, 'Unknown', 'N/A')"
 * echo coalesce(['name', "'Unknown'", "'N/A'"]);
 *
 * // Using a callback to quote values
 * echo coalesce(['name', 'city'], fn($v) => "'$v'");
 * // Returns: "COALESCE('name', 'city')"
 * ```
 *
 * @param array<string|int|float>|null $expressions Optional. List of expressions to evaluate.
 * @param callable|null $map Optional callback applied to each expression before generating SQL.
 *
 * @return string The generated SQL `COALESCE` function call, or empty string if no expressions are provided.
 *
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/COALESCE.html
 */
function coalesce( ?array $expressions = null , ?callable $map = null ) :string
{
    if( is_array( $expressions ) && count( $expressions ) > 0 )
    {
        if( isset( $map ) )
        {
            $expressions = array_map( $map , $expressions );
        }
        return func( ConditionalFunction::COALESCE , $expressions ) ;
    }
    return Char::EMPTY ;
}