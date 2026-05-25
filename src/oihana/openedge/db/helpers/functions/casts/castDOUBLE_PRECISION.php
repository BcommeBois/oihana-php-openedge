<?php

namespace oihana\openedge\db\helpers\functions\casts ;

use oihana\openedge\db\enums\Type;
use oihana\reflect\exceptions\ConstantException;

use function oihana\openedge\db\helpers\functions\cast;

/**
 * Generates a Progress OpenEdge SQL `CAST` expression to convert a value to DOUBLE PRECISION.
 *
 * This function wraps the given SQL expression or column name with `CAST(... AS DOUBLE PRECISION)`.
 * It internally relies on the `cast()` helper with `Type::DOUBLE_PRECISION`.
 *
 * @param string $expression The SQL expression or column name to be cast to DOUBLE PRECISION.
 *
 * @return string The resulting CAST expression, e.g., `CAST(column AS DOUBLE PRECISION)`.
 *
 * @throws ConstantException If the internal type constant `Type::DOUBLE_PRECISION` is invalid or cannot be used.
 *
 * @example
 * ```php
 * echo castDOUBLEPRECISION('column'); // CAST(column AS DOUBLE PRECISION)
 * ```
 *
 * @see Type::DOUBLE_PRECISION
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/CAST.html
 */
function castDOUBLE_PRECISION( string $expression ) :string
{
    return cast( $expression , Type::DOUBLE_PRECISION ) ;
}