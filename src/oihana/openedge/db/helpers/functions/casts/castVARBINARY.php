<?php

namespace oihana\openedge\db\helpers\functions\casts ;

use InvalidArgumentException;
use oihana\openedge\db\enums\Type;
use oihana\reflect\exceptions\ConstantException;

use function oihana\openedge\db\helpers\functions\cast;

/**
 * Generates a Progress OpenEdge SQL `CAST` expression to convert a value to a VARBINARY type.
 *
 * This function wraps the given SQL expression or column name with `CAST(... AS VARBINARY(length))`.
 *
 * VARBINARY stores small to medium-sized binary data inline. OpenEdge SQL supports
 * a length between 1 and 8000 bytes. For larger binary objects, use LVARBINARY (minimum 256 bytes),
 * which is optimized for very large data storage.
 *
 * @param string $expression The SQL expression or column name to be cast to VARBINARY.
 * @param int $length Optional. The length of the VARBINARY type. Defaults to 1. Must be 1–8000.
 *
 * @return string The resulting CAST expression, e.g., `CAST(column AS VARBINARY(16))`.
 *
 * @throws InvalidArgumentException If the length is not between 1 and 8000.
 * @throws ConstantException If the internal type constant `Type::VARBINARY` is invalid or cannot be used.
 *
 * @example
 * ```php
 * echo castBINARY( 'column' , 16 ) ; // outputs: CAST(column AS VARBINARY(16))
 * ```
 *
 * @see Type::BIGINT
 *
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/CAST.html
 */
function castVARBINARY( string $expression , int $length = 1 ) :string
{
    if ( $length < 1 || $length > 8000 )
    {
        throw new InvalidArgumentException('VARBINARY length must be between 1 and 8000 bytes.' ) ;
    }
    return cast( $expression , Type::VARBINARY , $length ) ;
}