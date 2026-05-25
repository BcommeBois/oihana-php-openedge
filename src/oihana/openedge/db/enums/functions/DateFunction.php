<?php

namespace oihana\openedge\db\enums\functions;

use oihana\reflect\traits\ConstantsTrait;

/**
 * OpenEdge SQL functions that do not fall into other categories are listed here.
 * @see https://docs.progress.com/fr-FR/bundle/openedge-sql-reference/page/Date-and-time-functions.html
 */
class DateFunction
{
    use ConstantsTrait ;

    public const string ADD_MONTHS     = 'ADD_MONTHS' ;
    public const string CURDATE        = 'CURDATE' ;
    public const string CURTIME        = 'CURTIME' ;
    public const string DAYNAME        = 'DAYNAME' ;
    public const string DAYOFMONTH     = 'DAYOFMONTH' ;
    public const string DAYOFWEEK      = 'DAYOFWEEK' ;
    public const string DAYOFYEAR      = 'DAYOFYEAR' ;
    public const string HOUR           = 'HOUR' ;
    public const string ISOWEEKDAY     = 'ISOWEEKDAY' ;
    public const string ISOWEEK        = 'ISOWEEK' ;
    public const string ISOYEAR        = 'ISOYEAR' ;
    public const string LAST_DAY       = 'LAST_DAY' ;
    public const string MINUTE         = 'MINUTE' ;
    public const string MONTHNAME      = 'MONTHNAME' ;
    public const string MONTHS_BETWEEN = 'MONTHS_BETWEEN' ;
    public const string NEXT_DAY       = 'NEXT_DAY' ;
    public const string NOW            = 'NOW' ;
    public const string QUARTER        = 'QUARTER' ;
    public const string SECOND         = 'SECOND' ;
    public const string SYSDATE        = 'SYSDATE' ;
    public const string SYSTIME        = 'SYSTIME' ;
    public const string SYSTIMESTAMP   = 'SYSTIMESTAMP' ;
    public const string TIMESTAMPADD   = 'TIMESTAMPADD' ;
    public const string TIMESTAMPDIFF  = 'TIMESTAMPDIFF' ;
    public const string WEEK           = 'WEEK' ;
    public const string YEAR           = 'YEAR' ;
}
