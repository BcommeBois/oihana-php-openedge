<?php

namespace oihana\openedge\db\helpers\functions\casts ;

use oihana\openedge\db\enums\Type;
use oihana\reflect\exceptions\ConstantException;

use function oihana\openedge\db\helpers\functions\cast;

/**
 * Generates a Progress OpenEdge SQL `CAST` expression to convert a value to a REAL type.
 *
 * This function wraps the given SQL expression or column name with `CAST(... AS REAL)`.
 * `REAL` is a floating-point numeric type with approximate precision.
 *
 * @param string $expression The SQL expression or column name to be cast to REAL.
 *
 * @return string The resulting CAST expression, e.g., `CAST(column AS REAL)`.
 *
 * @throws ConstantException If the internal type constant `Type::REAL` is invalid or cannot be used.
 *
 * @example
 * ```php
 * echo castREAL('column'); // outputs: CAST(column AS REAL)
 * ```
 *
 * @see Type::REAL
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/CAST.html
 */
function castREAL(string $expression): string
{
    return cast($expression, Type::REAL);
}