<?php

namespace oihana\openedge\db\helpers\functions\casts ;

use oihana\openedge\db\enums\Type;
use oihana\reflect\exceptions\ConstantException;

use function oihana\openedge\db\helpers\functions\cast;

/**
 * Generates a Progress OpenEdge SQL `CAST` expression to convert a value to a BIGINT type.
 *
 * This function wraps the given expression with `CAST(... AS BIGINT)`, which represents
 * a signed 64-bit integer. Valid values range from -9223372036854775808 to 9223372036854775807.
 *
 * Use this function when you need to ensure that a value or column is treated as a
 * BIGINT in SQL queries, for example when performing arithmetic or comparison operations
 * on large integer values.
 *
 * Internally, the function uses the `cast()` helper with `Type::BIGINT`.
 *
 * @param string $expression The SQL expression or column name to cast to BIGINT.
 *
 * @return string The resulting CAST expression, e.g., `CAST(column AS BIGINT)`.
 *
 * @throws ConstantException If the internal type constant `Type::BIGINT` is invalid or cannot be used.
 *
 * @example
 * ```php
 * echo castBIGINT('column'); // outputs: CAST(column AS BIGINT)
 * echo castBIGINT('123456789'); // outputs: CAST(123456789 AS BIGINT)
 * ```
 *
 * @see Type::BIGINT
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/CAST.html
 */
function castBIGINT( string $expression ) :string
{
    return cast( $expression , Type::BIGINT ) ;
}