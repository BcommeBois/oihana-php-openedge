<?php

namespace oihana\openedge\db\enums\functions;

use oihana\reflect\traits\ConstantsTrait;

/**
 * OpenEdge SQL functions for aggregate processing.
 * https://docs.progress.com/fr-FR/bundle/openedge-sql-reference/page/Aggregate-functions_2.html
 */
class AggregateFunction
{
    use ConstantsTrait ;
    
    public const string AVG   = 'AVG'   ;
    public const string COUNT = 'COUNT' ;
    public const string MAX   = 'MAX'   ;
    public const string MIN   = 'MIN'   ;
    public const string SUM   = 'SUM'   ;
}
