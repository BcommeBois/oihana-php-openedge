<?php

namespace oihana\openedge\db\helpers\cases;

use oihana\openedge\db\enums\Conditions;
use oihana\openedge\db\enums\Type;
use oihana\openedge\enums\OpenEdge;
use function oihana\core\strings\compile;
use function oihana\openedge\db\helpers\expression;

/**
 * Generates the THEN... expression
 *
 * @param array $definition
 * @param callable|null $map
 *
 * @return string
 */
function thenExpression( array $definition = [] , ?callable $map = null ) :string
{
    $exprFunc = $map ?? fn( ...$args ) => expression( ...$args ) ;
    return compile
    ([
        Conditions::THEN,
        isset( $definition[ OpenEdge::THEN ] ) ? $exprFunc( $definition[ OpenEdge::THEN ] ) : Type::NULL
    ]);
}
