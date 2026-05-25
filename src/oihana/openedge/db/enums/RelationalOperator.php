<?php

namespace oihana\openedge\db\enums;

use oihana\reflect\traits\ConstantsTrait;

/**
 * The enumeration of the relational operator in BNF (Backus Naur Form).
 */
class RelationalOperator
{
    use ConstantsTrait ;

    /**
     * The EQUAL operator '='.
     */
    const string EQUAL = '=' ;

    /**
     * The GREATER_THAN operator '>'.
     */
    const string GREATER_THAN = '>' ;

    /**
     * The GREATER_THAN_OR_EQUAL operator '>='.
     */
    const string GREATER_THAN_OR_EQUAL = '>=' ;

    /**
     * The LESS_THAN operator '<'.
     */
    const string LESS_THAN = '<' ;

    /**
     * The LESS_THAN_OR_EQUAL operator '<='.
     */
    const string LESS_THAN_OR_EQUAL = '<=' ;

    /**
     * The EQUAL operator '<>'.
     */
    const string NOT_EQUAL = '<>' ;
}
