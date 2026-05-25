<?php

namespace oihana\openedge\db\enums\functions;

use oihana\reflect\traits\ConstantsTrait;

/**
 * OpenEdge SQL functions that do not fall into other categories are listed here.
 */
class SystemFunction
{
    use ConstantsTrait ;

    const string DATABASE = 'DATABASE' ;
    const string DB_NAME  = 'DB_NAME' ;
    const string IFNULL   = 'IFNULL' ;
    const string ROWID    = 'ROWID' ;
    const string USER     = 'USER' ;
}
