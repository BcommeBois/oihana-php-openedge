<?php

namespace oihana\openedge\db\enums;

class Conditions
{
    /**
     * Specifies a searched case expression.
     * It must be followed by one or more WHEN-THEN clauses, each specifying a search condition and corresponding expression.
     * @see https://docs.progress.com/fr-FR/bundle/openedge-sql-reference/page/CASE.html
     * @example
     * A simple case expression can always be expressed as a searched case expression.
     * This example illustrates a simple case expression:
     * ```
     * CASE primary_expr
     *     WHEN expr1 THEN result_expr1
     *     WHEN expr2 THEN result_expr2
     *     ELSE expr3
     * END
     * ```
     *
     * The simple case expression in the preceding CASE example is equivalent to the following searched case expression:
     * ```
     * CASE
     *     WHEN primary_expr = expr1 THEN result_expr1
     *     WHEN primary_expr = expr2 THEN result_expr2
     *     ELSE expr3
     * END
     * ```
     * @see https://docs.progress.com/fr-FR/bundle/openedge-sql-reference/page/CASE.html
     */
    public const string CASE = 'CASE' ;

    /**
     * Specifies an optional expression whose value SQL returns if none of the conditions specified in WHEN-THEN clauses are satisfied.
     * If the CASE expression omits the ELSE clause, it is the same as specifying ELSE NULL.
     */
    public const string END = 'END' ;

    /**
     * Specifies an optional expression whose value SQL returns if none of the conditions specified in WHEN-THEN clauses are satisfied. If the CASE expression omits the ELSE clause, it is the same as specifying ELSE NULL.
     *  #### Example
     *  ```
     *  CASE
     *      WHEN condition1 THEN result1
     *      WHEN condition2 THEN result2
     *      ELSE exp
     *  END
     *  ```
     * @see https://docs.progress.com/fr-FR/bundle/openedge-sql-reference/page/CASE.html
     */
    public const string ELSE = 'ELSE' ;

    /**
     * Returns value if expr is NULL. If expr is not NULL, IFNULL returns expr.
     * @see https://docs.progress.com/fr-FR/bundle/openedge-sql-reference/page/IFNULL.html
     * @example
     * In this example, which illustrates the IFNULL function, the SELECT statement returns three rows with a NULL value in column C1, and two non-NULL values:
     * ```
     * SELECT C1, IFNULL(C1, 9999) FROM TEMP ORDER BY C1;
     * C1    IFNULL(C1,9999)
     * --    ----------------
     *       9999
     *       9999
     *       9999
     * 1     1
     * 3     3
     * ```
     */
    const string IFNULL = 'IFNULL' ;

    /**
     * Returns a NULL value for expression1 if it is equal to expression2.
     * NULLIF ( expression1, expression2 )
     * It is useful for converting values to NULL from applications that use some other representation for missing or unknown data.
     * The NULLIF scalar function is a type of conditional expression.
     * #### Notes
     * * This function is not allowed in a **GROUP BY** clause.
     * * Arguments to this function cannot be query expressions.
     * * The **NULLIF** expression is shorthand notation for a common case that can also be represented in a CASE expression, as shown
     * @see https://docs.progress.com/fr-FR/bundle/openedge-sql-reference/page/NULLIF.html
     * @example
     * This example uses the NULLIF scalar function to insert a NULL value into an address column if the host‑language variable contains a single space character:
     * ```
     * INSERT INTO employee (add1) VALUES (NULLIF (:address1, ' '));
     * ```
     */
    const string NULLIF = 'NULLIF' ;

    /**
     * Specifies the result of a condition in a CASE expression.
     *
     * THEN is used in conjunction with WHEN in CASE expressions to define the result when a condition is met.
     *
     * #### Example
     * ```
     * CASE
     *     WHEN condition1 THEN result1
     *     WHEN condition2 THEN result2
     *     ELSE exp
     * END
     * ```
     * @see https://docs.progress.com/fr-FR/bundle/openedge-sql-reference/page/CASE.html
     */
    public const string THEN = 'THEN' ;

    /**
     * Specifies a condition in a CASE expression.
     *
     * WHEN is used in conjunction with THEN in CASE expressions to define conditions and their corresponding results.
     *
     * #### Example
     * ```
     * CASE
     *     WHEN condition1 THEN result1
     *     WHEN condition2 THEN result2
     *     ELSE exp
     * END
     * ```
     * @see https://docs.progress.com/fr-FR/bundle/openedge-sql-reference/page/CASE.html
     */
    public const string WHEN = 'WHEN' ;
}