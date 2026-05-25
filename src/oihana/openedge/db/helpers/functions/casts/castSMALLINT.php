<?php

namespace oihana\openedge\db\helpers\functions\casts ;

use oihana\openedge\db\enums\Type;
use oihana\reflect\exceptions\ConstantException;

use function oihana\openedge\db\helpers\functions\cast;

/**
 * Generates a Progress OpenEdge SQL `CAST` expression to convert a value to an SMALLINT type.
 *
 * This function wraps the given SQL expression or column name with `CAST(... AS SMALLINT)`.
 *
 * @param string $expression The SQL expression or column name to be cast to SMALLINT.
 *
 * @return string The resulting CAST expression, e.g., `CAST(column AS SMALLINT)`.
 *
 * @throws ConstantException If the internal type constant `Type::SMALLINT` is invalid or cannot be used.
 *
 * @example
 * ```php
 * echo castSMALLINT('column'); // outputs: CAST(column AS SMALLINT)
 * ```
 *
 * @see Type::SMALLINT
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/CAST.html
 */
function castSMALLINT( string $expression ) :string
{
    return cast( $expression , Type::SMALLINT ) ;
}