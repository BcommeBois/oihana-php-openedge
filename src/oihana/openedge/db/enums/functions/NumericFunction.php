<?php

namespace oihana\openedge\db\enums\functions;

use oihana\reflect\traits\ConstantsTrait;

/**
 * OpenEdge SQL functions for number processing.
 */
class NumericFunction
{
    use ConstantsTrait ;

    public const string ABS      = 'ABS' ;
    public const string ACOS     = 'ACOS' ;
    public const string ASIN     = 'ASIN' ;
    public const string ATAN     = 'ATAN' ;
    public const string ATAN2    = 'ATAN2' ;
    public const string CEILING  = 'CEILING' ;
    public const string COS      = 'COS' ;
    public const string DEGREES  = 'DEGREES' ;
    public const string EXP      = 'EXP' ;
    public const string FLOOR    = 'FLOOR' ;
    public const string GREATEST = 'GREATEST' ;
    public const string LEAST    = 'LEAST' ;
    public const string LOG10    = 'LOG10' ;
    public const string MOD      = 'MOD' ;
    public const string PI       = 'PI' ;
    public const string POWER    = 'POWER' ;
    public const string RADIANS  = 'RADIANS' ;
    public const string RAND     = 'RAND' ;
    public const string ROUND    = 'ROUND' ;
    public const string SIGN     = 'SIGN' ;
    public const string SIN      = 'SIN' ;
    public const string SQRT     = 'SQRT' ;
    public const string TAN      = 'TAN' ;
}
