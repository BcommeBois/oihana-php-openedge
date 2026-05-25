<?php

namespace oihana\openedge\db\helpers\cases;

use DI\DependencyException;
use DI\NotFoundException;
use oihana\enums\Char;
use oihana\openedge\db\enums\Conditions;
use oihana\openedge\enums\OpenEdge;

use function oihana\core\strings\compile;
use function oihana\openedge\db\helpers\expression;
use function oihana\openedge\db\helpers\searchCondition;

/**
 * Generates the WHEN ... clause.
 *
 * @param array $definition
 * @param bool $simple
 * @param callable|null $map
 *
 * @return string
 *
 * @throws DependencyException
 * @throws NotFoundException
 */
function whenExpression(array $definition = [], bool $simple = false, ?callable $map = null): string
{
    $when = $definition[ OpenEdge::WHEN ] ?? null ;
    if ( isset( $when ) )
    {
        $exprFunc = $map ?? fn(...$args) => expression(...$args) ;

        $whenResult = $simple ? $exprFunc($when) : searchCondition( $when , map: $map ) ;
        
        if ( is_array( $whenResult ) )
        {
            $whenResult = compile( $whenResult ) ;
        }
        
        return compile( [ Conditions::WHEN , $whenResult , thenExpression( $definition , $map ) ] ) ;
    }
    return Char::EMPTY ;
}
