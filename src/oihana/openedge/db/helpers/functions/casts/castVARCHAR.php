<?php

namespace oihana\openedge\db\helpers\functions\casts ;

use oihana\openedge\db\enums\Type;
use oihana\reflect\exceptions\ConstantException;

use function oihana\openedge\db\helpers\functions\cast;

/**
 * Generates a Progress OpenEdge SQL `CAST` expression to convert a value to a VARCHAR type.
 *
 * This function wraps the given SQL expression or column name with `CAST(... AS VARCHAR(length))`.
 * It internally relies on the `cast()` helper with `Type::VARCHAR`.
 *
 * @param string $expression The SQL expression or column name to be cast to VARCHAR.
 * @param int    $length     Optional. The length of the VARCHAR type. Defaults to 256.
 *
 * @return string The resulting CAST expression, e.g., `CAST(column AS VARCHAR(16))`.
 *
 * @throws ConstantException If the internal type constant `Type::VARCHAR` is invalid or cannot be used.
 *
 * @example
 * ```php
 * echo castBINARY( 'column' , 16 ) ; // outputs: CAST(column AS VARCHAR(16))
 * ```
 *
 * @see Type::BIGINT
 *
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/CAST.html
 */
function castVARCHAR( string $expression , int $length = 1 ) :string
{
    return cast( $expression , Type::VARCHAR , $length ) ;
}