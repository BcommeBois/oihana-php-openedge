<?php

namespace oihana\openedge\db\helpers ;

use oihana\enums\Char;
use oihana\enums\Pagination;
use oihana\openedge\db\enums\Clause;

use function oihana\core\strings\compile;

/**
 * Generates the OFFSET and FETCH clauses for an OpenEdge SQL query.
 *
 * This function is used for pagination, allowing queries to skip a number of rows
 * (`OFFSET`) and limit the number of rows returned (`FETCH`). It improves usability
 * and performance when handling large result sets.
 *
 * **Supported keys in $init array:**
 * - `OpenEdge::LIMIT`  (?int) The maximum number of rows to return.
 * - `OpenEdge::OFFSET` (?int) The number of rows to skip before returning results.
 *
 * **Usage Examples:**
 * ```php
 * limit([OpenEdge::LIMIT => 10]);                           // FETCH FIRST 10 ROWS ONLY
 * limit([OpenEdge::OFFSET => 5]);                           // OFFSET 5 ROWS
 * limit([OpenEdge::LIMIT => 10, OpenEdge::OFFSET => 5] ) ; // OFFSET 5 ROWS FETCH NEXT 10 ROWS ONLY
 * limit([]);                                               // ''
 * ```
 *
 * @param array $init Optional array with `OpenEdge::LIMIT` and/or `OpenEdge::OFFSET`.
 * @return string The SQL fragment representing the pagination clauses.*
 */
function limit( array $init = [] ): string
{
    $limit  = $init[ Pagination::LIMIT  ] ?? 0 ;
    $offset = $init[ Pagination::OFFSET ] ?? 0 ;

    if( $offset > 0 ) // OFFSET $offset ROWS [ FETCH NEXT $limit ROWS ONLY ]
    {
        $expression = [ Clause::OFFSET , $offset , Clause::ROWS ] ;
        if( $limit > 0 )
        {
            $expression = [ ...$expression , Clause::FETCH , Clause::NEXT , $limit , Clause::ROWS , Clause::ONLY ] ;
        }
        return compile( $expression ) ;
    }
    else if( $limit > 0 ) // FETCH $limit ROWS ONLY
    {
        return compile( [ Clause::FETCH , Clause::FIRST , $limit , Clause::ROWS , Clause::ONLY ] ) ;
    }
    return Char::EMPTY ;
}