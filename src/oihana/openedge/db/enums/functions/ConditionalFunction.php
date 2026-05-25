<?php

namespace oihana\openedge\db\enums\functions;

use oihana\reflect\traits\ConstantsTrait;

/**
 * OpenEdge SQL functions that do not fall into other categories are listed here.
 */
class ConditionalFunction
{
    use ConstantsTrait ;

    /**
     * Specifies a series of search conditions and associated result expressions.
     * @example
     * Simple case
     * ```
     * CASE primary_expr
     *     WHEN expr1 THEN result_expr1
     *     WHEN expr2 THEN result_expr2
     *     ELSE expr3
     * END
     * ```
     * Or
     * ```
     * CASE
     *     WHEN primary_expr = expr1 THEN result_expr1
     *     WHEN primary_expr = expr2 THEN result_expr2
     *     ELSE expr3
     * END
     * ```
     * @see https://docs.progress.com/fr-FR/bundle/openedge-sql-reference/page/CASE.html
     */
    const string CASE = 'CASE' ;

    /**
     * Specifies a series of expressions and returns the first expression whose value is not NULL
     * @example
     * ```
     * COALESCE ( expression1 , expression2 , expression3 )
     * ```
     * Same as
     * ```
     * CASE
     *     WHEN expression1 IS NOT NULL THEN expression1
     *     WHEN expression2 IS NOT NULL THEN expression2
     *     ELSE expression3
     * END
     * ```
     * @see https://docs.progress.com/bundle/openedge-sql-reference/page/COALESCE.html
     */
    const string COALESCE = 'COALESCE' ;

    /**
     * Compares the value of the first argument expression with each search_expression and, if a match is found, returns the corresponding match_expression.
     * If no match is found, then the function returns the default_expression.
     * If a default_expression is not specified and no match is found, then the function returns a NULL value.
     * @example
     * ```
     * DECODE ( expression, search_expression, match_expression
     * [ , search_expression, match_expression...]
     * [ , default_expression] )
     * ```
     * Apply the example
     * ```
     * SELECT name, DECODE( type ,
     *    10, 'ACCOUNTS',
     *    20, 'RESEARCH',
     *    30, 'SALES',
     *    40, 'SUPPORT',
     *    'NOT ASSIGNED'
     * ) AS department_name
     * FROM employee;
     * ```
     * @see https://docs.progress.com/fr-FR/bundle/openedge-sql-reference/page/DECODE.html
     */
    const string DECODE = 'DECODE' ;

    /**
     * Returns value if expr is NULL. If expr is not NULL, IFNULL returns expr.
     * @example
     * ```
     * IFNULL( expr , value )
     * ```
     * @see https://docs.progress.com/fr-FR/bundle/openedge-sql-reference/page/IFNULL.html
     */
    const string IFNULL = 'IFNULL' ;

    /**
     * Returns a NULL value for expression1 if it is equal to expression2.
     * It is useful for converting values to NULL from applications that use some other representation for missing or unknown data.
     * The NULLIF scalar function is a type of conditional expression.
     * @example
     * ```
     * NULLIF ( expression1, expression2 )
     * ```
     * Same as
     * ```
     * CASE
     *     WHEN expression1 = expression2 THEN NULL
     *     ELSE expression1
     * END
     * ```
     * @see https://docs.progress.com/fr-FR/bundle/openedge-sql-reference/page/NULLIF.html
     */
    const string NULLIF = 'NULLIF' ;

    /**
     * Custom helper function to return NULL if the given expression is empty.
     *
     * This is **not an official OpenEdge SQL Progress function**. It is a helper
     * constant used in combination with the `nullIfEmpty()` PHP function defined
     * in `oihana\openedge\db\helpers\functions\conditionals`.
     *
     * Usage of this constant signals that the expression should be transformed
     * into a `NULL` value whenever it is an empty string (`''`).
     *
     * **Behavior:**
     * - If the expression is an empty string → returns `NULL`
     * - Otherwise → returns the original expression
     *
     * **Example in PHP with Oihana helpers:**
     * ```php
     * use function oihana\openedge\db\helpers\functions\conditionals\nullIfEmpty;
     * use oihana\openedge\db\enums\functions\ConditionalFunction;
     *
     * $column = 'description';
     * $sql = nullIfEmpty($column);
     * // Generates: "NULLIF(description, '')"
     * ```
     *
     * **Equivalent SQL logic with CASE:**
     * ```sql
     * CASE
     *     WHEN description = '' THEN NULL
     *     ELSE description
     * END
     * ```
     */
    const string NULLIF_EMPTY = 'NULLIF_EMPTY' ;

    /**
     * Custom helper function to return NULL if the given expression is 0 numeric value.
     *
     * This is **not an official OpenEdge SQL Progress function**. It is a helper
     * constant used in combination with the `nullIfZero()` PHP function defined
     * in `oihana\openedge\db\helpers\functions\conditionals`.
     *
     * Usage of this constant signals that the expression should be transformed
     * into a `NULL` value whenever it is an empty string (`''`).
     *
     * **Behavior:**
     * - If the expression is an empty string → returns `NULL`
     * - Otherwise → returns the original expression
     *
     * **Example in PHP with Oihana helpers:**
     * ```php
     * use function oihana\openedge\db\helpers\functions\conditionals\nullIfZero;
     * use oihana\openedge\db\enums\functions\ConditionalFunction;
     *
     * $column = 'description';
     * $sql = nullIfZero($column);
     * // Generates: "NULLIF(description, 0)"
     * ```
     *
     * **Equivalent SQL logic with CASE:**
     * ```sql
     * CASE
     *     WHEN description = 0 THEN NULL
     *     ELSE description
     * END
     * ```
     */
    const string NULLIF_ZERO = 'NULLIF_ZERO' ;

    /**
     * Returns the value of the first expression if the first expression value is not NULL.
     * If the first expression value is NULL, the value of the second expression is returned.
     * Note : The NVL function is not ODBC compatible. Use the IFNULL function when ODBC‑compatible syntax is required.
     * @example
     * ```
     * NVL ( expression , expression )
     * ``
     * @see https://docs.progress.com/fr-FR/bundle/openedge-sql-reference/page/NVL.html
     */
    const string NVL = 'NVL' ;
}
