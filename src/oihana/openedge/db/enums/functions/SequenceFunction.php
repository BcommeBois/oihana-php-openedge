<?php

namespace oihana\openedge\db\enums\functions;

use oihana\reflect\traits\ConstantsTrait;

/**
 * The OpenEdge SQL sequence functions enumeration.
 */
class SequenceFunction
{
    use ConstantsTrait ;

    const string CURRVAL = 'CURRVAL' ;
    const string NEXTVAL = 'NEXTVAL' ;
}
