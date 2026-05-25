<?php

namespace oihana\openedge\db\helpers\functions\conversions ;

use oihana\openedge\db\enums\functions\ConversionFunction;

use function oihana\core\strings\func;

/**
 * Generates a Progress OpenEdge SQL `TO_NUMBER()` expression.
 *
 * SQL Syntax:
 * ```
 * TO_NUMBER( charExpression )
 * ```
 *
 * @param string $expression The string expression or column name to convert to NUMBER.
 *
 * @return string The generated SQL `TO_NUMBER()` expression, e.g., `TO_NUMBER( charExpression )`
 *
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/TO_NUMBER.html
 */
function toNumber( string $expression ) :string
{
    return func( ConversionFunction::TO_NUMBER , $expression ) ;
}