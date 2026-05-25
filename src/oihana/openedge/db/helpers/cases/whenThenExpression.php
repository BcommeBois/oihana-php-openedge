<?php

namespace oihana\openedge\db\helpers\cases;

use DI\DependencyException;
use DI\NotFoundException;
use oihana\openedge\enums\OpenEdge;
use function oihana\core\strings\compile;

/**
 * Creates a WHEN expr|search_expression THEN expression string definition.
 * @param array $definition
 * @param bool $simple
 * @param callable|null $map
 * @return string
 * @throws DependencyException
 * @throws NotFoundException
 */
function whenThenExpression( array $definition = [] , bool $simple = false , ?callable $map = null ) :string
{
    $expressions = [];
    $conditions = $definition[OpenEdge::CONDITIONS] ?? [] ;
    if ( is_array($conditions) && count($conditions) > 0 )
    {
        foreach ($conditions as $condition)
        {
            $expressions[] = whenExpression( $condition , $simple , $map ) ;
        }
    }
    return compile( $expressions ) ;
}
