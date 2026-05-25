<?php

namespace oihana\openedge\db\helpers\functions\alters;

use oihana\openedge\db\enums\functions\ConditionalFunction;

use oihana\openedge\db\enums\functions\ConversionFunction;
use oihana\openedge\db\enums\functions\DateFunction;
use oihana\openedge\db\enums\functions\NumericFunction;
use oihana\openedge\db\enums\functions\StringFunction;

use oihana\reflect\exceptions\ConstantException;

/**
 * Applies a transformation on a given SQL key using the appropriate OpenEdge function.
 *
 * This helper acts as a router between different OpenEdge function categories:
 * - **Conditional functions** → handled by {@see alterConditional()}
 * - **Conversion functions**  → handled by {@see alterConversion()}
 * - **Date functions**        → handled by {@see alterDate()}
 * - **Numeric functions**     → handled by {@see alterNumeric()}
 * - **String functions**      → handled by {@see alterString()}
 *
 * If the provided function does not match any known category, the key is returned unchanged.
 *
 * ## Example:
 *
 * ```php
 * use oihana\openedge\db\enums\functions\ConditionalFunction;
 * use oihana\openedge\db\enums\functions\StringFunction;
 *
 * // Applying a conditional IFNULL function
 * echo alterKey('price', ConditionalFunction::IFNULL, ['0']);
 * // Produces: IFNULL(price, 0)
 *
 * // Applying an UPPER string transformation
 * echo alterKey('name', StringFunction::UPPER);
 * // Produces: UPPER(name)
 *
 * // When no function is provided, the original key is returned
 * echo alterKey('created_at');
 * // Produces: created_at
 * ```
 *
 * @param string        $key       The column name or SQL expression to transform.
 * @param string|null   $function  The OpenEdge function to apply.
 * @param array         $args      Optional arguments for the selected function.
 * @param callable|null $map       Optional. A callback applied to `$value` before generating the SQL expression.
 *
 * @return string The transformed SQL expression.
 *
 * @throws ConstantException If the provided function is invalid or unsupported.
 */
function alterKey( string $key , ?string $function = null , array $args = [] , ?callable $map = null ):string
{
    return match ( true )
    {
        ConditionalFunction::includes ( $function ) => alterConditional ( $key , $function , $args , $map ) ,
        ConversionFunction::includes  ( $function ) => alterConversion  ( $key , $function , $args ) ,
        DateFunction::includes        ( $function ) => alterDate        ( $key , $function , $args ) ,
        NumericFunction::includes     ( $function ) => alterNumeric     ( $key , $function , $args ) ,
        StringFunction::includes      ( $function ) => alterString      ( $key , $function , $args ) ,
        default                                     => $key
    };
}