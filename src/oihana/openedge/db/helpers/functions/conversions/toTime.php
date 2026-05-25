<?php

namespace oihana\openedge\db\helpers\functions\conversions ;

use oihana\openedge\db\enums\functions\ConversionFunction;

use function oihana\core\strings\func;

/**
 * Generates a Progress OpenEdge SQL `TO_TIME()` expression.
 *
 * SQL Syntax:
 * ```
 * TO_TIME( time_literal )
 * ```
 *
 * @param string $expression The string expression or column name to convert to a time value.
 *
 * @return string The generated SQL `TO_TIME()` expression, e.g., `TO_TIME( time_literal )`
 *
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/TO_TIME.html
 */
function toTime( string $expression ) :string
{
    return func( ConversionFunction::TO_TIME , $expression ) ;
}