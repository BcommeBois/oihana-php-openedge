<?php

namespace oihana\openedge\db\enums\functions;

use oihana\reflect\traits\ConstantsTrait;

/**
 * OpenEdge SQL functions that do not fall into other categories are listed here.
 */
class ConversionFunction
{
    use ConstantsTrait ;

    const string CAST    = 'CAST' ;
    const string CONVERT = 'CONVERT' ;
    const string DECODE  = 'DECODE' ;

    /**
     * Converts the given expression to character form and returns the result. The primary use for TO_CHAR is to format the output of date-time expressions through the format_string argument.
     * @see https://docs.progress.com/fr-FR/bundle/openedge-sql-reference/page/TO_CHAR.html
     */
    public const string TO_CHAR = 'TO_CHAR' ;

    /**
     * Converts the given date literal to a date value.
     * @see https://docs.progress.com/fr-FR/bundle/openedge-sql-reference/page/TO_DATE.html
     */
    public const string TO_DATE = 'TO_DATE' ;

    /**
     * Converts the given character expression to a number value.
     * @see https://docs.progress.com/fr-FR/bundle/openedge-sql-reference/page/TO_NUMBER.html
     */
    public const string TO_NUMBER = 'TO_NUMBER' ;

    /**
     * Converts the given time literal to a time value.
     * @see https://docs.progress.com/fr-FR/bundle/openedge-sql-reference/page/TO_TIME.html
     */
    public const string TO_TIME = 'TO_TIME' ;

    /**
     * Converts the given time literal to a time value.
     * @see https://docs.progress.com/fr-FR/bundle/openedge-sql-reference/page/TO_TIMESTAMP.html
     */
    public const string TO_TIMESTAMP = 'TO_TIMESTAMP' ;
}
