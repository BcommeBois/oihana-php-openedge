<?php

namespace oihana\openedge\db\helpers\functions\casts ;

use oihana\openedge\db\enums\Type;
use oihana\reflect\exceptions\ConstantException;

use function oihana\openedge\db\helpers\functions\cast;

/**
 * Generates a Progress OpenEdge SQL `CAST` expression to convert a value to a FLOAT type.
 *
 * This function wraps the given SQL expression or column name with `CAST(... AS FLOAT)` or
 * `CAST(... AS FLOAT(precision))` if a precision is provided.
 *
 * @param string    $expression The SQL expression or column name to be cast to FLOAT.
 * @param int|null  $precision  Optional precision (number of bits for the mantissa). If null, the default system precision is used.
 *
 * @return string The resulting CAST expression, e.g., `CAST(column AS FLOAT)` or `CAST(column AS FLOAT(16))`.
 *
 * @throws ConstantException If the internal type constant `Type::FLOAT` is invalid or cannot be used.
 *
 * @example
 * ```php
 * echo castFLOAT('column');       // outputs: CAST(column AS FLOAT)
 * echo castFLOAT('column', 16);   // outputs: CAST(column AS FLOAT(16))
 * ```
 *
 * @see Type::FLOAT
 *
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/CAST.html
 */
function castFLOAT( string $expression, ?int $precision = null ): string
{
    return cast
    (
        expression : $expression,
        type       : Type::FLOAT ,
        args       : $precision
    );
}