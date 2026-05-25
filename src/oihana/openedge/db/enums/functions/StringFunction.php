<?php

namespace oihana\openedge\db\enums\functions;

use oihana\reflect\traits\ConstantsTrait;

/**
 * OpenEdge SQL functions for string processing.
 * @see https://docs.progress.com/fr-FR/bundle/openedge-sql-reference/page/String-functions.html
 */
class StringFunction
{
    use ConstantsTrait ;

    public const string ASCII           = 'ASCII' ;
    public const string CHAR            = 'CHAR' ;
    public const string CHR             = 'CHR' ;
    public const string CONCAT          = 'CONCAT' ;
    public const string DIFFERENCE      = 'DIFFERENCE' ;
    public const string INITCAP         = 'INITCAP' ;
    public const string INSERT          = 'INSERT' ;
    public const string INSTR           = 'INSTR' ;
    public const string LCASE           = 'LCASE' ;
    public const string LEFT            = 'LEFT' ;
    public const string LENGTH          = 'LENGTH' ;
    public const string LOCATE          = 'LOCATE' ;
    public const string LOWER           = 'LOWER' ;
    public const string LPAD            = 'LPAD' ;
    public const string LTRIM           = 'LTRIM' ;
    public const string PREFIX          = 'PREFIX' ;
    public const string PRO_ARR_DESCAPE = 'PRO_ARR_DESCAPE' ;
    public const string PRO_ARR_ESCAPE  = 'PRO_ARR_ESCAPE' ;
    public const string PRO_ELEMENT     = 'PRO_ELEMENT' ;
    public const string REPEAT          = 'REPEAT' ;
    public const string REPLACE         = 'REPLACE' ;
    public const string RIGHT           = 'RIGHT' ;
    public const string RPAD            = 'RPAD' ;
    public const string RTRIM           = 'RTRIM' ;
    public const string SUBSTR          = 'SUBSTR' ;
    public const string SUBSTRING       = 'SUBSTRING' ;
    public const string SUFFIX          = 'SUFFIX' ;
    public const string TRANSLATE       = 'TRANSLATE' ;
    public const string UCASE           = 'UCASE' ;
    public const string UPPER           = 'UPPER' ;
}
