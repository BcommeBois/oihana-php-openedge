<?php

namespace oihana\openedge\db\helpers;

use oihana\enums\Char;
use oihana\enums\Order;

/**
 * Parses a simplified sorting expression and converts it into a list of
 * SQL ORDER BY elements.
 *
 * A sorting expression is a comma-separated list of sortable keys, where:
 *
 * - `"field"`   means ascending order (`field ASC`)
 * - `"-field"`  means descending order (`field DESC`)
 * - `"a,-b,c"`  becomes: `a ASC`, `b DESC`, `c ASC`
 *
 * Only keys defined in the `$sortable` map are accepted. Unknown keys are ignored.
 *
 * ### Examples
 * ```php
 * orderByExpression("-name,city", [
 *     "name" => "user_name",
 *     "city" => "city_name",
 * ]);
 * // Returns: ["user_name DESC", "city_name"]
 * ```
 *
 * ### Behavior
 * - Empty or null expressions return an empty array.
 * - Empty sortable maps also result in an empty array.
 * - Leading `-` indicates descending order.
 * - Whitespace is not trimmed automatically by this function.
 *
 * @param ?string $expression
 *     The raw sorting expression (e.g. `"name,-created"`, `" -a,b "`).
 *
 * @param ?array<string,string> $sortable
 *     Associative array mapping sortable keys to SQL column names.
 *     Example:
 *     ```php
 *     [
 *         "name"    => "user_name",
 *         "created" => "created_at"
 *     ]
 *     ```
 *
 * @return array<int,string>
 *     A list of cleaned SQL ORDER BY components.
 *     Each element contains the SQL field name and its order (ASC or DESC).
 */
function orderByExpression( ?string $expression, ?array $sortable ) : array
{
    if ( empty( $expression ) || empty( $sortable ) )
    {
        return [] ;
    }

    $orders = [] ;

    foreach ( explode(Char::COMMA, $expression) as $key )
    {
        if ($key === Char::EMPTY)
        {
            continue ;
        }

        $order = Char::EMPTY ;

        // Leading '-' → DESC
        if ($key[0] === Char::HYPHEN)
        {
            $key   = substr( $key , 1 ) ;
            $order = Order::DESC ;
        }

        if ( array_key_exists( $key , $sortable ) )
        {
            $orders[] = trim($sortable[$key] . Char::SPACE . $order ) ;
        }
    }

    return $orders ;
}