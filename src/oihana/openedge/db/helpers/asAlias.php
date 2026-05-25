<?php

namespace oihana\openedge\db\helpers ;

use oihana\enums\Char;
use oihana\openedge\db\enums\Clause;
use function oihana\core\strings\betweenDoubleQuotes;
use function oihana\core\strings\compile;

/**
 * Generates an OpenEdge SQL expression for a column (key) with an optional alias.
 *
 * This function constructs a valid SQL fragment, optionally appending the `AS` clause
 * with a case-sensitive or case-insensitive alias.
 *
 * **Usage Examples:**
 * ```php
 * asAlias('customer_id');              // customer_id
 * asAlias('customer_id', 'id');        // customer_id AS "id"
 * asAlias('customer_id', 'id', false); // customer_id AS id
 * ```
 *
 * @param string      $key           The column name or expression to be used in the SQL statement.
 * @param string|null $alias         Optional alias for the column. If provided, will be added with `AS`.
 * @param bool        $caseSensitive Whether the alias should be wrapped in double quotes (case-sensitive). Default is `true`.
 *
 * @return string The resulting SQL expression with optional alias.
 *
 * @see Clause::AS
 * @see betweenDoubleQuotes()
 * @see compile()
 */
function asAlias( string $key , ?string $alias = null , bool $caseSensitive = true ) :string
{
    $expression = [ $key ] ;
    if ( is_string( $alias ) && $alias !== Char::EMPTY )
    {
        $expression[] = Clause::AS ;
        $expression[] = $caseSensitive ? betweenDoubleQuotes( $alias ) : $alias ;
    }
    return compile( $expression ) ;
}