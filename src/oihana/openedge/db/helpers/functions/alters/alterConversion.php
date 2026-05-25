<?php

namespace oihana\openedge\db\helpers\functions\alters;

use oihana\openedge\db\enums\functions\ConversionFunction;

use oihana\reflect\exceptions\ConstantException;

use function oihana\openedge\db\helpers\functions\castKey;
use function oihana\openedge\db\helpers\functions\conversions\toChar;
use function oihana\openedge\db\helpers\functions\conversions\toDate;
use function oihana\openedge\db\helpers\functions\conversions\toNumber;
use function oihana\openedge\db\helpers\functions\conversions\toTime;
use function oihana\openedge\db\helpers\functions\conversions\toTimestamp;

// [ OpenEdge::COLUMN => 'products.identifier' , OpenEdge::ALTER => [ ConversionFunction::CAST , Type::INTEGER ] ] ,
// [ OpenEdge::COLUMN => 'products.identifier' , OpenEdge::ALTER => [ ConversionFunction::CAST , Type::FLOAT , 8 ] ] ,

/**
 * Applies a SQL conversion function to a given key (column or expression) in OpenEdge SQL.
 *
 * This function supports standard OpenEdge SQL conversion functions, including:
 * - `CAST`: Converts the expression to a specified SQL type, optionally with length/scale.
 * - `TO_CHAR`: Converts the expression to a string, optionally using a format string.
 * - `TO_DATE`: Converts a string expression to a DATE value.
 * - `TO_NUMBER`: Converts a string expression to a NUMBER value.
 * - `TO_TIME`: Converts a string expression to a TIME value.
 * - `TO_TIMESTAMP`: Converts a string expression to a TIMESTAMP value.
 *
 * **Usage examples:**
 * ```php
 * alterConversion('products.identifier', ConversionFunction::CAST, [Type::INTEGER]);
 * // Outputs: CAST(products.identifier AS INTEGER)
 *
 * alterConversion('order_date', ConversionFunction::TO_CHAR, ['YYYY-MM-DD']);
 * // Outputs: TO_CHAR(order_date, 'YYYY-MM-DD')
 *
 * alterConversion('birth_date', ConversionFunction::TO_DATE);
 * // Outputs: TO_DATE(birth_date)
 * ```
 *
 * @param string      $key      The SQL column name or expression to convert.
 * @param string|null $function Optional. The conversion function to apply. Must be one of
 *                              {@see ConversionFunction}.
 * @param array       $args     Optional. Additional arguments required by the conversion function,
 *                              such as target type for `CAST` or format string for `TO_CHAR`.
 *
 * @return string The SQL expression after applying the conversion function.
 *
 * @throws ConstantException If the provided function is invalid or a required constant/type is missing.
 *
 * @see ConversionFunction
 */
function alterConversion( string $key , ?string $function = null , array $args = [] ):string
{
    // TODO ConversionFunction::CONVERT
    // TODO ConversionFunction::DECODE

    return match ( $function )
    {
        ConversionFunction::CAST         => castKey     ( $key , array_shift( $args ) , $args ) ,
        ConversionFunction::TO_CHAR      => toChar      ( ...( [ $key, ...$args] ) ) ,
        ConversionFunction::TO_DATE      => toDate      ( $key ) ,
        ConversionFunction::TO_NUMBER    => toNumber    ( $key ) ,
        ConversionFunction::TO_TIME      => toTime      ( $key ) ,
        ConversionFunction::TO_TIMESTAMP => toTimestamp ( $key ) ,
        default                          => $key
    };
}