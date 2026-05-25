<?php

namespace oihana\openedge\db\enums;

use oihana\reflect\traits\ConstantsTrait;

/**
 * The enumeration of the relational operator in BNF (Backus Naur Form).
 */
class QuantifiedOperator
{
    use ConstantsTrait ;

    /**
     * The ALL operator.
     */
    const string ALL = 'ALL' ;

    /**
     * The ANY operator.
     */
    const string ANY = 'ANY' ;

    /**
     * The ALL operator.
     */
    const string SOME = 'SOME' ;
}
