<?php

namespace oihana\openedge\db\enums;

use oihana\reflect\traits\ConstantsTrait;

class Predicate
{
    use ConstantsTrait ;

    /**
     * The ALL predicate : 'ALL'.
     */
    const string ALL = 'ALL' ;

    /**
     * The BETWEEN predicate : 'BETWEEN'.
     */
    const string BETWEEN = 'BETWEEN' ;

    /**
     * The DISTINCT predicate : 'DISTINCT'.
     */
    const string DISTINCT = 'DISTINCT' ;

    /**
     * The ESCAPE keyword.
     */
    const string ESCAPE = 'ESCAPE' ;

    /**
     * The EXISTS predicate : 'EXISTS'.
     */
    const string EXISTS = 'EXISTS' ;

    /**
     * The IN predicate : 'IN'.
     */
    const string IN = 'IN' ;

    /**
     * The LIKE predicate : 'LIKE'.
     */
    const string LIKE = 'LIKE' ;

    /**
     * The NOT_BETWEEN predicate : 'NOT BETWEEN'.
     */
    const string NOT_BETWEEN = 'NOT BETWEEN' ;

    /**
     * The NOT_EXISTS predicate : 'NOT EXISTS'.
     */
    const string NOT_EXISTS = 'NOT EXISTS' ;

    /**
     * The NOT_IN predicate : 'NOT IN'.
     */
    const string NOT_IN = 'NOT IN' ;

    /**
     * The NOT_LIKE predicate : 'NOT LIKE'.
     */
    const string NOT_LIKE = 'NOT LIKE' ;

    /**
     * The NOT_NULL predicate : 'IS NOT NULL'.
     */
    const string NOT_NULL = 'IS NOT NULL' ;

    /**
     * The NULL predicate : 'IS NULL'.
     */
    const string NULL = 'IS NULL' ;
}
