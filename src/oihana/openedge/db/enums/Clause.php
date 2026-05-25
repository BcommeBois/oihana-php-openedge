<?php

namespace oihana\openedge\db\enums;

use oihana\reflect\traits\ConstantsTrait;

class Clause
{
    use ConstantsTrait ;

    /**
     * The AS clause.
     */
    const string AS = 'AS' ;

    /**
     * The COUNT clause.
     */
    const string COUNT = 'COUNT' ;

    /**
     * The DELETE clause deletes table rows specified in the statement's WHERE clause.
     */
    const string DELETE = 'DELETE' ;

    /**
     * The FETCH clause specifies the number of rows to return, after processing the OFFSET clause.
     */
    const string FETCH = 'FETCH' ;

    /**
     * The FIRST clause (synonym of NEXT).
     */
    const string FIRST = 'FIRST' ;

    /**
     * The FOR UPDATE clause.
     */
    const string FOR_UPDATE = 'FOR UPDATE' ;

    /**
     * Specifies one or more table references.
     */
    const string FROM = 'FROM' ;

    /**
     * Specifies grouping of rows in the result table.
     */
    const string GROUP_BY = 'GROUP BY' ;

    /**
     * Allows you to set conditions on the groups returned by the GROUP BY clause.
     */
    const string HAVING = 'HAVING' ;

    /**
     * The INSERT clause to add new rows to a table.
     */
    const string INSERT = 'INSERT INTO' ;

    /**
     * The NEXT clause (synonym of FIRST).
     */
    const string NEXT = 'NEXT' ;

    /**
     * Disables join order optimization for the FROM clause.
     * Use NO REORDER when you choose to override the join order chosen by the optimizer.
     * The braces are part of the syntax for this optional clause.
     * @example
     * SELECT * FROM table { NO REORDER }
     */
    const string NO_REORDER = '{ NO REORDER }' ;

    /**
     * The OFFSET clause specifies the number of rows to skip, before starting to return rows from the query expression.
     */
    const string OFFSET = 'OFFSET' ;

    /**
     * The ON clause.
     */
    const string ON = 'ON' ;

    /**
     * The ONLY clause.
     * @example SELECT * FROM PUB.table FETCH FIRST 10 ROWS ONLY
     * @see https://docs.progress.com/fr-FR/bundle/openedge-sql-reference/page/OFFSET-and-FETCH-clauses.html
     */
    const string ONLY = 'ONLY' ;

    /**
     * Allows ordering of the rows selected by the SELECT statement.
     */
    const string ORDER_BY = 'ORDER BY' ;

    /**
     * The number of rows (synonym of ROWS).
     */
    const string ROW = 'ROW' ;

    /**
     * The number of rows (synonym of ROW).
     */
    const string ROWS = 'ROWS' ;

    /**
     * The select clause.
     */
    const string SELECT = 'SELECT' ;

    /**
     * Used in the UPDATE statement to update values.
     */
    const string SET = 'SET' ;

    /**
     * The TENANT clause.
     */
    const string TENANT = 'TENANT' ;

    /**
     * Limits the rows returned by an OpenEdge SQL query at the statement level and is supported in subqueries.
     */
    const string TOP = 'TOP' ;

    /**
     * The UPDATE clause updates the rows and columns of the specified table with the given values for rows that satisfy the search_condition.
     */
    const string UPDATE = 'UPDATE' ;

    /**
     * The VALUES clause.
     */
    const string VALUES = 'VALUES' ;

    /**
     * Specifies a search condition that applies conditions to restrict the number of rows in the result table.
     */
    const string WHERE = 'WHERE' ;

    /**
     * Enables table-level locking when a finer control of the types of locks acquired on an object is required.
     */
    const string WITH = 'WITH' ;
}
