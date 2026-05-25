<?php

namespace oihana\openedge\db\helpers\functions\casts ;

use oihana\openedge\db\enums\Type;
use oihana\reflect\exceptions\ConstantException;

use function oihana\openedge\db\helpers\functions\cast;

/**
 * Generates a Progress OpenEdge SQL `CAST` expression to convert a value to a TIME type.
 *
 * This function wraps the given SQL expression or column name with `CAST(... AS TIME)`.
 * In OpenEdge SQL, `TIME` stores the time of day, including hours, minutes, seconds, and milliseconds.
 *
 * @param string $expression The SQL expression or column name to be cast to TIME.
 *
 * @return string The resulting CAST expression, e.g., `CAST(column AS TIME)`.
 *
 * @throws ConstantException If the internal type constant `Type::TIME` is invalid or cannot be used.
 *
 * @example
 * ```php
 * echo castTIME('start_column'); // outputs: CAST(start_column AS TIME)
 * ```
 *
 * @see Type::TIME
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/CAST.html
 */
function castTIME( string $expression ): string
{
    return cast( $expression , Type::TIME ) ;
}