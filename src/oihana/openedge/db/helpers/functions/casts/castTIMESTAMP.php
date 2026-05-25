<?php

namespace oihana\openedge\db\helpers\functions\casts ;

use oihana\openedge\db\enums\Type;
use oihana\reflect\exceptions\ConstantException;

use function oihana\openedge\db\helpers\functions\cast;

/**
 * Generates a Progress OpenEdge SQL `CAST` expression to convert a value to a TIMESTAMP type.
 *
 * This function wraps the given SQL expression or column name with:
 * - `CAST(... AS TIMESTAMP)` by default
 * - `CAST(... AS TIMESTAMP WITH TIME ZONE)` if `$useTimeZone` is true
 *
 * `TIMESTAMP` represents a date and time value including fractional seconds.
 *
 * @param string $expression The SQL expression or column name to be cast to TIMESTAMP.
 * @param bool   $useTimeZone Optional. If true, casts to TIMESTAMP WITH TIME ZONE. Defaults to false.
 *
 * @return string The resulting CAST expression.
 *
 * @throws ConstantException If the internal type constant is invalid or cannot be used.
 *
 * @example
 * ```php
 * echo castTIMESTAMP('created_at');              // CAST(created_at AS TIMESTAMP)
 * echo castTIMESTAMP('created_at', true);       // CAST(created_at AS TIMESTAMP WITH TIME ZONE)
 * ```
 *
 * @see Type::TIMESTAMP
 * @see Type::TIMESTAMP_WITH_TIME_ZONE
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/CAST.html
 */
function castTIMESTAMP( string $expression , bool $useTimeZone = false ) :string
{
    return cast( $expression , $useTimeZone ? Type::TIMESTAMP_WITH_TIME_ZONE : Type::TIMESTAMP) ;
}