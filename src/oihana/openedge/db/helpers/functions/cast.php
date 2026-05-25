<?php

namespace oihana\openedge\db\helpers\functions ;

use oihana\enums\Char;
use oihana\openedge\db\enums\Clause;
use oihana\openedge\db\enums\functions\ConversionFunction;
use oihana\reflect\exceptions\ConstantException;

use function oihana\core\strings\func;
use function oihana\openedge\db\helpers\openEdgeType;

/**
 * Generates a Progress OpenEdge SQL `CAST()` expression.
 *
 * Converts an expression to a specified data type, optionally including
 * type parameters such as length, precision, or scale.
 *
 * **SQL Syntax:**
 * ```
 * CAST ( {expression | NULL} AS data_type[(length)] )
 * ```
 *
 * **Examples:**
 * ```php
 * use function oihana\openedge\db\helpers\functions\cast;
 * use oihana\openedge\db\enums\Type;
 *
 * // Cast a column to INTEGER
 * echo cast('price', Type::INTEGER); // "CAST(price AS INTEGER)"
 *
 * // Cast a column to VARCHAR with length
 * echo cast('username', Type::VARCHAR, 20); // "CAST(username AS VARCHAR(20))"
 *
 * // Cast a column to DECIMAL with precision and scale
 * echo cast('amount', Type::DECIMAL, [10,2]);  // "CAST(amount AS DECIMAL(10,2))"
 * ```
 *
 * @param string $expression The expression or column to convert.
 * @param string $type The target OpenEdge SQL type (validated by `openEdgeType()`).
 * @param string|int|array|null $args Optional arguments for the target type, e.g., length, precision/scale.
 *
 * @return string The generated SQL `CAST()` expression.
 *
 * @throws ConstantException If `$type` is not a valid OpenEdge SQL type constant.
 *
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/CAST.html
 * @see openEdgeType()
 */
function cast( string $expression , string $type , null|string|int|array $args = null ) :string
{
    return func
    (
        name      : ConversionFunction::CAST ,
        arguments : [ $expression , Clause::AS , openEdgeType( $type , $args ) ] ,
        separator : Char::SPACE
    ) ;
}