<?php

namespace oihana\openedge\db\helpers\functions\casts ;

use InvalidArgumentException;
use oihana\openedge\db\enums\Type;
use oihana\reflect\exceptions\ConstantException;

use function oihana\openedge\db\helpers\functions\cast;

/**
 * Generates a Progress OpenEdge SQL `CAST` expression to convert a value to the `CHAR` type.
 *
 * This function wraps the given SQL expression or column name in a
 * `CAST(... AS CHAR(length))` clause. It internally relies on the {@see cast()} helper
 * using {@see Type::CHAR}.
 **Important:** The `CHAR` data type in OpenEdge requires an explicit length between **1** and **2000** characters.
 * If you need variable-length character data, consider using {@see castVARCHAR()} instead.
 *
 * @param string $expression The SQL expression or column name to cast.
 * @param int $length [optional] The fixed length of the `CHAR` type. Defaults to **1**.
 *
 * @return string The generated SQL `CAST()` expression.
 * Example: `CAST(column AS CHAR(16))`
 *
 * @throws ConstantException If the internal type constant `Type::CHAR` is invalid or cannot be used.
 *
 * @throws InvalidArgumentException If the provided `$length` is less than **1** or greater than **2000**.
 * @throws ConstantException        If the internal type constant `Type::CHAR` is invalid or undefined.
 *
 * @example
 * ```php
 * echo castCHAR( 'column' , 16 ) ; // outputs: CAST(column AS CHAR(16))
 * ```
 *
 * @see Type::CHAR
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/CAST.html
 */
function castCHAR( string $expression , int $length = 1 ) :string
{
    if ( $length < 1 || $length > 2000 )
    {
        throw new InvalidArgumentException('CHAR length must be between 1 and 2000 characters.' ) ;
    }
    return cast( $expression , Type::CHAR , $length ) ;
}