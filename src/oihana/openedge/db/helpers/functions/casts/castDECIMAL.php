<?php

namespace oihana\openedge\db\helpers\functions\casts ;

use oihana\openedge\db\enums\Type;
use oihana\reflect\exceptions\ConstantException;

use function oihana\openedge\db\helpers\functions\cast;

/**
 * Generates a Progress OpenEdge SQL `CAST` expression to convert a value to a DECIMAL type.
 *
 * This function wraps the given SQL expression or column name with `CAST(... AS DECIMAL(precision, scale))`.
 * It internally relies on the `cast()` helper with `Type::DECIMAL`.
 *
 * @param string $expression The SQL expression or column name to be cast to DECIMAL.
 * @param int    $precision  Total number of digits (default 32).
 * @param int    $scale      Number of digits after the decimal point (default 0).
 *
 * @return string The resulting CAST expression, e.g., `CAST(column AS DECIMAL(16,2))`.
 *
 * @throws ConstantException If the internal type constant `Type::DECIMAL` is invalid or cannot be used.
 *
 * @example
 * ```php
 * echo castDECIMAL('column');           // CAST(column AS DECIMAL(32,0))
 * echo castDECIMAL('amount', 10, 2);    // CAST(amount AS DECIMAL(10,2))
 * ```
 *
 * @see Type::BIGINT
 *
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/CAST.html
 */
function castDECIMAL( string $expression , int $precision = 32 , int $scale = 0 ) :string
{
    return cast( $expression , Type::DECIMAL , [ $precision , $scale ] ) ;
}