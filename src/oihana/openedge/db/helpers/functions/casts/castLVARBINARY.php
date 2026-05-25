<?php

namespace oihana\openedge\db\helpers\functions\casts ;

use InvalidArgumentException;
use oihana\openedge\db\enums\Type;
use oihana\reflect\exceptions\ConstantException;

use function oihana\openedge\db\helpers\functions\cast;

/**
 * Generates a Progress OpenEdge SQL `CAST` expression to convert a value to an LVARBINARY type.
 *
 * This function wraps the given SQL expression or column name with `CAST(... AS LVARBINARY(length))`.
 * Note: The minimum allowed length for LVARBINARY is 256 bytes.
 *
 * @param string $expression The SQL expression or column name to be cast to LVARBINARY.
 * @param int $length The maximum length in bytes of the LVARBINARY value.
 *
 * @return string The resulting CAST expression, e.g., `CAST(column AS LVARBINARY(256))`.
 *
 * @throws ConstantException If the internal type constant `Type::LVARBINARY` is invalid or cannot be used.
 *
 * @example
 * ```php
 * echo castLVARBINARY('column', 256); // outputs: CAST(column AS LVARBINARY(256))
 * ```
 *
 * @throws InvalidArgumentException If the length is not greater or equals to 256.
 * @throws ConstantException If the internal type constant `Type::LVARBINARY` is invalid or cannot be used.
 *
 * @see Type::LVARBINARY
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/CAST.html
 */
function castLVARBINARY( string $expression , int $length = 256 ) :string
{
    if ( $length < 256 )
    {
        throw new InvalidArgumentException('LVARBINARY length must be at least 256 bytes.' ) ;
    }

    return cast( $expression , Type::LVARBINARY , $length ) ;
}