<?php

namespace oihana\openedge\db\enums;

use oihana\reflect\traits\ConstantsTrait;

class Facet
{
    use ConstantsTrait ;

    // -------- default parameters

    public const string EXPRESSION = 'expression' ;
    public const string TYPE       = 'type' ;

    // -------- types of facets

    public const string EQUAL = 'eq' ;
    public const string IN    = 'in' ;
}