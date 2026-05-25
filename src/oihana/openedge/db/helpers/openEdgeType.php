<?php

namespace oihana\openedge\db\helpers ;

use oihana\enums\Char;
use oihana\openedge\db\enums\Type;
use oihana\reflect\exceptions\ConstantException;

use function oihana\core\strings\betweenParentheses;
use function oihana\core\strings\compile;

/**
 * Generates a Progress OpenEdge SQL type expression, optionally with parameters.
 *
 * This function returns a SQL type string suitable for column definitions,
 * including optional arguments like length or precision/scale.
 *
 * **Examples:**
 * ```php
 * use oihana\openedge\db\helpers\openEdgeType;
 * use oihana\openedge\db\enums\Type;
 *
 * echo openEdgeType(Type::INTEGER);           // "INTEGER"
 * echo openEdgeType(Type::VARCHAR, 5);        // "VARCHAR(5)"
 * echo openEdgeType(Type::DECIMAL, [10, 2]);  // "DECIMAL(10,2)"
 * ```
 *
 * @param string                $type The OpenEdge SQL type constant (validated by `Type::validate()`).
 * @param string|int|array|null $args Optional arguments for the type, e.g., length, precision/scale.
 *
 * @return string The generated SQL type expression, optionally including arguments in parentheses.
 *
 * @throws ConstantException If the provided `$type` is not a valid OpenEdge SQL type constant.
 */
function openEdgeType( string $type , null|string|int|array $args = null ) :string
{
    Type::validate( $type ) ;

    if( isset( $args ) )
    {
        $type .= betweenParentheses( compile( $args , Char::COMMA ) ) ;
    }

    return $type ;
}