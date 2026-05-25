<?php

namespace oihana\openedge\db\helpers\functions\casts ;

use oihana\openedge\db\enums\Type;
use oihana\reflect\exceptions\ConstantException;

use function oihana\openedge\db\helpers\functions\cast;

/**
 * Generates a Progress OpenEdge SQL `CAST` expression to convert a value to a BIT type.
 *
 * This function wraps the given expression with `CAST(... AS BIT)`, which represents a single
 * bit value (0 or 1) in OpenEdge SQL. It is commonly used to ensure that a value is treated
 * as a boolean-like field in SQL queries.
 *
 * The function internally uses the `cast()` helper with `Type::BIT`.
 *
 * @param string $expression The SQL expression or column name to cast to BIT.
 *
 * @return string The resulting CAST expression, e.g., `CAST(column AS BIT)`.
 *
 * @throws ConstantException If the internal type constant `Type::BIT` is invalid or cannot be used.
 *
 * @example
 * ```php
 * echo castBIT('column'); // outputs: CAST(column AS BIT)
 * echo castBIT('1');      // outputs: CAST(1 AS BIT)
 * ```
 *
 * @see Type::BIT
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/CAST.html
 */
function castBIT( string $expression ) :string
{
    return cast( $expression , Type::BIT ) ;
}