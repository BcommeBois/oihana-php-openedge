<?php

namespace oihana\openedge\db\traits;

use DateInvalidTimeZoneException;
use DateMalformedStringException;
use DI\DependencyException;
use DI\NotFoundException;
use oihana\enums\Char;
use oihana\openedge\db\enums\Clause;
use oihana\openedge\enums\OpenEdge;

use oihana\reflect\exceptions\ConstantException;
use function oihana\core\arrays\isAssociative;
use function oihana\core\strings\betweenDoubleQuotes;
use function oihana\core\strings\compile;
use function oihana\openedge\db\helpers\expression;

/**
 * Provides logic to build an SQL `GROUP BY` clause for OpenEdge SQL queries.
 *
 * This trait supports multiple input formats, reflecting OpenEdge SQL rules:
 *
 * 1. **Grouping by column**
 *    - Simple string: `"columnName"` or `"col1, col2"`
 *    - Indexed array of strings: `["col1", "col2"]`
 *
 * 2. **Grouping by alias**
 *    - Any string representing a column alias in the SELECT list
 *
 * 3. **Grouping by expression**
 *    - Associative array(s) compatible with `expression()`
 *      Example: `[ [OpenEdge::COLUMN => "YEAR(orderDate)"] ]`
 *
 * 4. **OpenEdge::GROUP_BY container**
 *    - When `$init` is an array, you can pass `[OpenEdge::GROUP_BY => [...]]`
 *
 * 5. **Fallback property**
 *    - If `$init` is null, `$this->groupBy` is used
 *
 * The resulting clause is always properly prefixed with `GROUP BY` or
 * an empty string is returned if no valid definitions exist.
 *
 * ### Notes
 * - Strings are used as-is; if multiple columns are specified as comma-separated, they are preserved.
 * - Indexed arrays are treated as lists of columns or expressions.
 * - Associative arrays are compiled through `expression()`.
 * - Empty strings, empty arrays, or invalid entries are ignored.
 *
 * ### Examples
 *
 * ```php
 * // Single column string
 * $query->groupBy("name");
 * // → "GROUP BY name"
 *
 * // Multiple columns in a string
 * $query->groupBy("name, country");
 * // → "GROUP BY name, country"
 *
 * // Indexed array of expressions or columns
 * $query->groupBy([
 *     [OpenEdge::COLUMN => 'name', OpenEdge::TABLE => 'places'],
 *     [OpenEdge::COLUMN => 'country']
 * ]);
 * // → "GROUP BY places.name, country"
 *
 * // OpenEdge::GROUP_BY container in an array
 * $query->groupBy([
 *     OpenEdge::GROUP_BY => [
 *         [OpenEdge::COLUMN => 'name', OpenEdge::TABLE => 'places'],
 *         [OpenEdge::COLUMN => 'country']
 *     ]
 * ]);
 * // → "GROUP BY places.name, country"
 *
 * // Fallback on $this->groupBy property
 * $this->groupBy = [
 *     [OpenEdge::COLUMN => "YEAR(orderDate)"],
 *     "customerId"
 * ];
 * $query->groupBy();
 * // → "GROUP BY YEAR(orderDate), "customerId""
 *
 * // Empty string or array
 * $query->groupBy("");
 * // → ""
 * $query->groupBy([]);
 * // → ""
 * ```
 *
 * @package oihana\openedge\db\traits
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
trait GroupByTrait
{
    /**
     * Stores the definitions used to build the GROUP BY clause.
     *
     * Accepted formats:
     * - string : A raw comma-separated list of grouping columns or alias or expressions
     * - array  : Indexed array of alias (string) or columns or expressions definitions (associative arrays)
     * - null   : Indicates no default definition
     *
     * @var array|string|null
     */
    public array|string|null $groupBy ;

    /**
     * Builds the SQL GROUP BY clause for OpenEdge SQL queries.
     *
     * @param array|string|null $init
     *        The grouping definition. May be:
     *        - a string (single column, comma-separated list, or alias)
     *        - an indexed array of columns or associative expressions
     *        - an array containing OpenEdge::GROUP_BY
     *        - null (defaults to $this->groupBy)
     *
     * @return string
     *        The complete GROUP BY clause prefixed with "GROUP BY", or an empty string if no valid elements exist.
     *
     * @throws DependencyException
     * @throws NotFoundException If an associative expression cannot be processed by expression().
     * @throws DateInvalidTimeZoneException
     * @throws DateMalformedStringException
     * @throws ConstantException
     */
    public function groupBy( array|string|null $init = null ): string
    {
        $definitions = match ( true )
        {
            is_string ( $init ) => $init ,
            is_array  ( $init ) => $init[ OpenEdge::GROUP_BY ] ?? null ,
            default             => null ,
        };

        $definitions = $definitions ?? $this->groupBy ?? null ;

        if ( $definitions === null || $definitions === Char::EMPTY )
        {
            return Char::EMPTY ;
        }

        if ( is_string( $definitions ) )
        {
            return Clause::GROUP_BY . Char::SPACE . $definitions ;
        }

        if ( !is_array( $definitions ) )
        {
            return Char::EMPTY ;
        }

        $parts = [] ;

        foreach ( $definitions as $definition )
        {
            if ( is_string( $definition ) && $definition !== Char::EMPTY )
            {
                $parts[] = betweenDoubleQuotes( $definition ) ; // Alias
            }
            else if ( is_array( $definition ) && isAssociative( $definition ) )
            {
                $parts[] = expression( $definition ) ; // columns or complex expressions.
            }
        }

        if ( !$parts )
        {
            return Char::EMPTY ;
        }

        return Clause::GROUP_BY . Char::SPACE . compile( $parts ,  Char::COMMA . Char::SPACE ) ;
    }
}