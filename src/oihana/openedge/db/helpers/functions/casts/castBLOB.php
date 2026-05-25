<?php

namespace oihana\openedge\db\helpers\functions\casts ;

use oihana\openedge\db\enums\Type;
use oihana\reflect\exceptions\ConstantException;

use function oihana\openedge\db\helpers\functions\cast;

/**
 * Generates a Progress OpenEdge SQL `CAST` expression to convert a value to a BLOB type.
 *
 * This function wraps the given SQL expression or column name with `CAST(... AS BLOB(length))`.
 * It internally relies on the `cast()` helper with `Type::BLOB`.
 *
 * @param string $expression The SQL expression or column name to be cast to BLOB.
 *
 * @return string The resulting CAST expression, e.g., `CAST(column AS BLOB)`.
 *
 * @throws ConstantException If the internal type constant `Type::BLOB` is invalid or cannot be used.
 *
 * @example
 * ```php
 * echo castBLOB( 'column' ) ; // outputs: CAST(column AS BLOB)
 * ```
 *
 * @see Type::BIGINT
 *
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/CAST.html
 */
function castBLOB( string $expression ) :string
{
    return cast( $expression , Type::BLOB ) ;
}