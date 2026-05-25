<?php

namespace oihana\openedge\db\helpers;

use DateInvalidTimeZoneException;
use DateMalformedStringException;
use oihana\enums\Char;
use oihana\openedge\enums\OpenEdge;

use oihana\reflect\exceptions\ConstantException;
use function oihana\core\strings\key;

/**
 * Generates a full SQL column expression for OpenEdge, including table prefix,
 * transformations, and nullability.
 *
 * This function will:
 * 1. Retrieve the column name from the definition or use a default value.
 * 2. Optionally prepend a table name as a prefix, using the standard separator.
 * 3. Apply any transformations defined in `overrideExpression()` (e.g., CAST, ALTER, ALTERS).
 * 4. Append the nullable marker if the column is defined as nullable.
 *
 * **Definition array keys:**
 * - `OpenEdge::COLUMN` (string) — The column name.
 * - `OpenEdge::TABLE` (string|null) — Optional table name to prefix the column.
 * - `OpenEdge::NULLABLE` (bool) — Whether the column is nullable.
 * - Other keys supported by `overrideExpression()` for transformations (`CAST`, `ALTER`, `ALTERS`).
 *
 * **Example usage:**
 * ```php
 * use oihana\openedge\db\enums\OpenEdge;
 *
 * $definition =
 * [
 *     OpenEdge::TABLE    => 'users',
 *     OpenEdge::COLUMN   => 'name',
 *     OpenEdge::CAST     => 'VARCHAR',
 *     OpenEdge::ALTER    => 'UPPER',
 *     OpenEdge::NULLABLE => true
 * ];
 *
 * echo columnExpression($definition);
 * // Example output: UPPER(CAST(users.name AS VARCHAR)) NULL
 * ```
 *
 * @param array $definition The column definition array.
 * @param string $default Default column name if none is provided in the definition.
 * @param callable|null $callback Optional callback to transform the arguments before passing them to the function.
 *
 * @return string The fully constructed SQL column expression.
 *
 * @throws ConstantException
 * @throws DateInvalidTimeZoneException
 * @throws DateMalformedStringException
 *
 * @see key()
 * @see overrideExpression()
 */
function columnExpression
(
    array     $definition ,
    string    $default    = Char::EMPTY ,
    ?callable $callback   = null
)
:string
{
    $expression = $definition[ OpenEdge::COLUMN ] ?? $default ;

    if( $expression )
    {
        $table = $definition[ OpenEdge::TABLE ] ?? null ;
        if( isset( $table ) )
        {
            $expression = key( $expression , $table ) ;
        }

        $expression = overrideExpression( $expression , $definition , $callback ) ;

        $nullable = $definition[ OpenEdge::NULLABLE ] ?? false ;
        if( $nullable )
        {
            $expression .= OpenEdge::NULLABLE_COLUMN ;
        }
    }

    return $expression ;
}