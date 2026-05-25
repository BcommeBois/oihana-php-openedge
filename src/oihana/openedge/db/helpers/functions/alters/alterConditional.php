<?php

namespace oihana\openedge\db\helpers\functions\alters;

use oihana\openedge\db\enums\functions\ConditionalFunction;

use function oihana\openedge\db\helpers\functions\conditionals\ifNull;
use function oihana\openedge\db\helpers\functions\conditionals\nullIf;
use function oihana\openedge\db\helpers\functions\conditionals\nullIfEmpty;
use function oihana\openedge\db\helpers\functions\conditionals\nullIfZero;
use function oihana\openedge\db\helpers\functions\conditionals\nvl;

/**
 * Applies a conditional transformation to a given key using one of the supported OpenEdge SQL functions.
 *
 * This helper wraps OpenEdge conditional functions like `IFNULL()` and `NULLIF()`
 * to modify the value returned in a SQL expression. If no matching function is provided,
 * the key is returned as-is.
 *
 * ## Example:
 *
 * ```php
 * use oihana\openedge\db\enums\functions\ConditionalFunction;
 *
 * echo alterConditional('customer_id', ConditionalFunction::IFNULL, ['0']);
 * // Produces: IFNULL(customer_id, 0)
 *
 * echo alterConditional('status', ConditionalFunction::NULLIF, ["'inactive'"]);
 * // Produces: NULLIF(status, 'inactive')
 *
 * echo alterConditional('price');
 * // Produces: price
 * ```
 *
 * @param string        $key       The column name or SQL expression to alter.
 * @param string|null   $function  The conditional function to apply (e.g. IFNULL, NULLIF).
 * @param array         $args      Additional arguments required by the function.
 * @param callable|null $map       Optional. A callback applied to `$value` before generating the SQL expression.
 *
 * @return string The transformed SQL expression.
 */
function alterConditional( string $key , ?string $function = null , array $args = [] , ?callable $map = null ):string
{
    return match ( $function )
    {
        ConditionalFunction::IFNULL        => ifNull ( $key , $args[0] , $map ) ,
        ConditionalFunction::NULLIF        => nullIf ( $key , $args[0] , $map ) ,
        ConditionalFunction::NULLIF_EMPTY  => nullIfEmpty ( $key ) ,
        ConditionalFunction::NULLIF_ZERO   => nullIfZero  ( $key ) ,
        ConditionalFunction::NVL           => nvl ( $key , $args[0] , $map ) ,
        default                            => $key
    };
}