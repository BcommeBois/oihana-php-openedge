<?php

namespace oihana\openedge\db\helpers\functions ;

use oihana\openedge\db\enums\Type;
use oihana\reflect\exceptions\ConstantException;

use function oihana\openedge\db\helpers\functions\casts\castBIGINT;
use function oihana\openedge\db\helpers\functions\casts\castBINARY;
use function oihana\openedge\db\helpers\functions\casts\castBIT;
use function oihana\openedge\db\helpers\functions\casts\castBLOB;
use function oihana\openedge\db\helpers\functions\casts\castCHAR;
use function oihana\openedge\db\helpers\functions\casts\castCLOB;
use function oihana\openedge\db\helpers\functions\casts\castDATE;
use function oihana\openedge\db\helpers\functions\casts\castDECIMAL;
use function oihana\openedge\db\helpers\functions\casts\castDOUBLE_PRECISION;
use function oihana\openedge\db\helpers\functions\casts\castFLOAT;
use function oihana\openedge\db\helpers\functions\casts\castINTEGER;
use function oihana\openedge\db\helpers\functions\casts\castLVARBINARY;
use function oihana\openedge\db\helpers\functions\casts\castREAL;
use function oihana\openedge\db\helpers\functions\casts\castSMALLINT;
use function oihana\openedge\db\helpers\functions\casts\castTIME;
use function oihana\openedge\db\helpers\functions\casts\castTIMESTAMP;
use function oihana\openedge\db\helpers\functions\casts\castTINYINT;
use function oihana\openedge\db\helpers\functions\casts\castVARBINARY;
use function oihana\openedge\db\helpers\functions\casts\castVARCHAR;


/**
 * Generates a Progress OpenEdge SQL `CAST()` expression for the given column or expression.
 *
 * This function provides a unified interface to cast a column or SQL expression to a specific
 * OpenEdge data type. It supports all major scalar types, binary types, character types,
 * date/time types, and numeric types.
 *
 * **Usage:**
 *
 * ```sql
 * CAST ( {expression | NULL} AS data_type[(length[, scale])])
 * ```
 *
 * **Examples:**
 *
 * ```php
 * // Simple INTEGER cast
 * echo castKey('price', Type::INTEGER);
 * // Result: CAST(price AS INTEGER)
 *
 * // Decimal with precision & scale
 * echo castKey('amount', Type::DECIMAL, [12, 2]);
 * // Result: CAST(amount AS DECIMAL(12, 2))
 *
 * // VARCHAR with length
 * echo castKey('name', Type::VARCHAR, [50]);
 * // Result: CAST(name AS VARCHAR(50))
 *
 * // TIMESTAMP WITH TIME ZONE
 * echo castKey('created_at', Type::TIMESTAMP_WITH_TIME_ZONE);
 * // Result: CAST(created_at AS TIMESTAMP WITH TIME ZONE)
 * ```
 *
 * @param string      $key   The SQL expression or column name to be cast.
 * @param string|null $type  The target type (see {@see Type} constants).
 *                           If null or unrecognized, the original expression is returned.
 * @param array       $args  Optional additional parameters for the cast:
 *                           - `$args[0]`: length (CHAR, VARCHAR, VARBINARY, etc.)
 *                           - `$args[1]`: scale (for DECIMAL only)
 *
 * @return string The generated SQL `CAST()` expression, or the original `$key` if `$type` is null or unsupported.
 *
 * @throws ConstantException If the given `$type` does not match a valid {@see Type} constant.
 *
 * @see Type
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/CAST.html
 */
function castKey( string $key , ?string $type = null , array $args = [] ):string
{
    return match ( $type )
    {
        Type::BIGINT                   => castBIGINT           ( $key ) ,
        Type::BIT                      => castBIT              ( $key ) ,
        Type::BINARY                   => castBINARY           ( $key , $args[0] ?? 1 ) ,
        Type::BLOB                     => castBLOB             ( $key ),
        Type::CHAR                     => castCHAR             ( $key , $args[0] ?? 1 ) ,
        Type::CLOB                     => castCLOB             ( $key ),
        Type::DATE                     => castDATE             ( $key ),
        Type::DECIMAL                  => castDECIMAL          ( $key , $args[0] ?? 32 , $args[1] ?? 0) ,
        Type::DOUBLE_PRECISION         => castDOUBLE_PRECISION ( $key ),
        Type::FLOAT                    => castFLOAT            ( $key , $args[0] ?? null ) ,
        Type::INTEGER                  => castINTEGER          ( $key ),
        Type::LVARBINARY               => castLVARBINARY       ( $key , $args[0] ?? 256 ) ,
        Type::REAL                     => castREAL             ( $key ) ,
        Type::SMALLINT                 => castSMALLINT         ( $key ) ,
        Type::TIME                     => castTIME             ( $key ),
        Type::TIMESTAMP                => castTIMESTAMP        ( $key  ),
        Type::TIMESTAMP_WITH_TIME_ZONE => castTIMESTAMP        ( $key , true ) ,
        Type::TINYINT                  => castTINYINT          ( $key ),
        Type::VARBINARY                => castVARBINARY        ( $key , $args[0] ?? 1 ) ,
        Type::VARCHAR                  => castVARCHAR          ( $key , $args[0] ?? 1 ) ,
        default                        => $key ,
    };
}