<?php

namespace oihana\openedge\db\helpers\functions\casts ;

use oihana\openedge\db\enums\Type;
use oihana\reflect\exceptions\ConstantException;

use function oihana\openedge\db\helpers\functions\cast;

/**
 * Generates a Progress OpenEdge SQL `CAST` expression to convert a value to a CLOB type.
 *
 * This function wraps the given SQL expression or column name with `CAST(... AS CLOB(length))`.
 * It internally relies on the `cast()` helper with `Type::CLOB`.
 *
 * @param string $expression The SQL expression or column name to be cast to CLOB.
 *
 * @return string The resulting CAST expression, e.g., `CAST(column AS CLOB)`.
 *
 * @throws ConstantException If the internal type constant `Type::CLOB` is invalid or cannot be used.
 *
 * @example
 * ```php
 * echo castCLOB( 'column' ) ; // outputs: CAST(column AS CLOB)
 * ```
 *
 * @see Type::BIGINT
 *
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/CAST.html
 */
function castCLOB( string $expression ) :string
{
    return cast( $expression , Type::CLOB ) ;
}