<?php

namespace oihana\openedge\db\enums;

use oihana\reflect\traits\ConstantsTrait;

class Logic
{
    use ConstantsTrait ;

    const string AND = 'AND' ;

    const string AND_NOT = 'AND NOT' ;

    const string OR = 'OR' ;

    const string OR_NOT = 'OR NOT' ;

    const string NOT = 'NOT' ;
}