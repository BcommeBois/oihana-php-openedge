<?php

namespace oihana\openedge\db\helpers\functions ;

use oihana\openedge\db\enums\Type;
use oihana\reflect\exceptions\ConstantException;

/**
 * Generates a SQL `CAST()` expression for the given column or value.
 *
 * This function converts an SQL expression (column or literal) into a specific
 * data type supported by OpenEdge, using the {@see Type} enum.
 *
 * The `$definition` parameter can be:
 *
 * - A **string** → Corresponds directly to a type name (`Type::VARCHAR`, `Type::INTEGER`, etc.)
 * - An **array** → First element = type, following elements = optional arguments (`length`, `scale`, etc.)
 * - `null`      → No casting is applied; the original `$expression` is returned.
 *
 * **SQL Syntax:**
 *
 * ```sql
 * CAST({expression | NULL} AS data_type[(length[, scale])])
 * ```
 *
 * **Example:**
 *
 * ```php
 * use oihana\openedge\db\helpers\functions\castExpression;
 * use oihana\openedge\db\enums\Type;
 *
 * echo castExpression('price', [Type::DECIMAL, 10, 2]);
 * // Output: CAST(price AS DECIMAL(10,2))
 *
 * echo castExpression('username', Type::VARCHAR);
 * // Output: CAST(username AS VARCHAR(1))  ← default length when not provided
 *
 * echo castExpression('created_at', [Type::TIMESTAMP]);
 * // Output: CAST(created_at AS TIMESTAMP)
 *
 * echo castExpression('image', [Type::VARBINARY, 255]);
 * // Output: CAST(image AS VARBINARY(255))
 *
 * echo castExpression('data', null);
 * // Output: data
 * ```
 *
 * @param string               $expression  The SQL column or literal to cast.
 * @param array|string|null    $definition  Either:
 *                                         - A type constant from {@see Type},
 *                                         - An array where the first element is the type, followed by optional arguments,
 *                                         - Or `null` to disable casting.
 *
 * @return string The generated SQL `CAST()` expression, or the original `$expression` if `$definition` is null or invalid.
 *
 * @throws ConstantException If the given `$definition` references an invalid {@see Type} constant.
 *
 * @see Type
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/CAST.html
 */
function castExpression( string $expression , array|string|null $definition ) :string
{
    if( isset( $definition ) )
    {
        $args = [] ;
        $type = null ;

        if( is_string( $definition ) && Type::includes( $definition ) )
        {
            $type = $definition ;
        }
        else if( is_array( $definition ) )
        {
            [ $type ] = $definition ;
            if( is_string( $type ) && Type::includes( $type ) )
            {
                $args = array_slice( $definition , 1 ) ;
            }
            else
            {
                $type = null ;
            }
        }

        return castKey( $expression , $type , $args ) ;
    }

    return $expression ;
}