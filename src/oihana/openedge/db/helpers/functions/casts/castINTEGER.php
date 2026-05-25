<?php

namespace oihana\openedge\db\helpers\functions\casts ;

use oihana\openedge\db\enums\Type;
use oihana\reflect\exceptions\ConstantException;

use function oihana\openedge\db\helpers\functions\cast;

/**
 * Generates a Progress OpenEdge SQL `CAST` expression to convert a value to an INTEGER type.
 *
 * This function wraps the given SQL expression or column name with `CAST(... AS INTEGER)`.
 *
 * @param string $expression The SQL expression or column name to be cast to INTEGER.
 *
 * @return string The resulting CAST expression, e.g., `CAST(column AS INTEGER)`.
 *
 * @throws ConstantException If the internal type constant `Type::INTEGER` is invalid or cannot be used.
 *
 * @example
 * ```php
 * echo castINTEGER('column'); // outputs: CAST(column AS INTEGER)
 * ```
 *
 * @see Type::INTEGER
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/CAST.html
 */
function castINTEGER( string $expression ) :string
{
    return cast( $expression , Type::INTEGER ) ;
}