<?php

namespace oihana\openedge\db\helpers\functions\conversions ;

use oihana\openedge\db\enums\functions\ConversionFunction;

use function oihana\core\strings\func;

/**
 * Generates a Progress OpenEdge SQL `TO_TIMESTAMP()` expression.
 *
 * SQL Syntax:
 * ```
 * TO_TIMESTAMP( timestamp_literal )
 * ```
 *
 * @param string $expression The string expression or column name to convert to a timestamp value.
 *
 * @return string The generated SQL `TO_TIMESTAMP()` expression, e.g., `TO_TIME( timestamp_literal )`
 *
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/TO_TIMESTAMP.html
 */
function toTimestamp( string $expression ) :string
{
    return func( ConversionFunction::TO_TIMESTAMP , $expression ) ;
}