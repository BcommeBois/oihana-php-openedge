<?php

namespace oihana\openedge\db\traits;

use oihana\enums\Char;
use oihana\openedge\db\enums\Clause;
use oihana\openedge\enums\OpenEdge;

use function oihana\core\arrays\clean;
use function oihana\core\strings\compile;
use function oihana\openedge\db\helpers\orderByExpression;

/**
 * Provides support for generating SQL ORDER BY clauses using a simplified
 * sorting expression syntax (e.g. `name,-created`).
 *
 * This trait expects:
 * - `$orderBy` : the default ORDER BY expression (optional)
 * - `$sortable`: a map of sortable field keys to their SQL column names
 *
 * A sorting expression accepts a comma-separated list of sortable keys,
 * optionally prefixed with `-` to indicate DESC order.
 *
 * Example:
 *   ?sort=-name,created  →  ORDER BY name DESC, created
 */
trait OrderByTrait
{
    /**
     * Default ORDER BY expression used when no other expression is provided.
     *
     * Example:
     *   $orderBy = "name";
     *
     * @var ?string
     */
    public ?string $orderBy ;

    /**
     * Map of sortable keys to SQL column names.
     *
     * Example:
     * ```php
     * $sortable = [
     *     'name'    => 'name',
     *     'created' => 'created_at', // alias
     * ];
     * ```
     *
     * @var ?array<string,string>
     */
    public ?array $sortable ;

    /**
     * Builds the SQL ORDER BY clause from the given initialization options.
     *
     * If no sorting expression is found in `$init`, the trait uses `$this->orderBy`
     * as the default expression. The sorting expression follows the simplified syntax:
     *
     * - `"field"`   → `field ASC`
     * - `"-field"`  → `field DESC`
     * - `"a,-b,c"`  → `a ASC, b DESC, c ASC`
     *
     * ### Usage Example
     * ```
     * /route?sort=name,-city&offset=0&limit=100
     * ```
     *
     * ### Options
     * - **OpenEdge::SORT**
     *   Sorting expression to parse (highest priority)
     *
     * - **OpenEdge::ORDER_BY**
     *   Generic ORDER BY expression, fallback if SORT is not provided
     *
     * - **$this->orderBy**
     *   Default ORDER BY expression when nothing is provided
     *
     * @param array<string,mixed> $init
     *     Initialization array containing sorting directives.
     *
     * @return string
     *     The generated SQL ORDER BY clause, or an empty string if no valid
     *     sorting rule was found.
     */
    public function orderBy( array $init = [] ): string
    {
        $expression = $init[ OpenEdge::SORT     ]
                   ?? $init[ OpenEdge::ORDER_BY ]
                   ?? $this->orderBy ;

        $orders = clean( orderByExpression( $expression , $this->sortable ) ) ;

        return $orders
            ? compile([ Clause::ORDER_BY, implode(Char::COMMA . Char::SPACE, $orders) ])
            : Char::EMPTY;
    }
}