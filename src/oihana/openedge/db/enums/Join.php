<?php

namespace oihana\openedge\db\enums;

use oihana\reflect\traits\ConstantsTrait;

class Join
{
    use ConstantsTrait ;
    /**
     * Specifies a Cartesian product of rows in the two tables.
     * Every row in one table is joined to every row in the other table.
     */
    public const string CROSS = 'CROSS JOIN' ;

    /**
     * Specifies an inner join using the supplied search condition.
     */
    public const string INNER = 'INNER JOIN' ; // INNER JOIN === JOIN

    /**
     * Specifies the same conditions as an inner join.
     */
    public const string LEFT = 'LEFT JOIN'  ;

    /**
     * Specifies a left outer join using the supplied search condition.
     */
    public const string LEFT_OUTER  = 'LEFT OUTER JOIN'  ;
}