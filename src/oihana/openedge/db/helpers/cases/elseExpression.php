<?php

namespace oihana\openedge\db\helpers\cases;

use oihana\openedge\db\enums\Conditions;
use oihana\openedge\db\enums\Type;
use oihana\openedge\enums\OpenEdge;

use function oihana\core\strings\compile;
use function oihana\openedge\db\helpers\expression;

/**
 * Specifies an optional expression whose value SQL returns if none of the conditions specified in WHEN-THEN clauses are satisfied.
 * Syntax : ELSE expression|NULL
 *
 * @param array $definition
 * @param callable|null $map
 *
 * @return string
 */
function elseExpression( array $definition = [] , ?callable $map = null ) :string
{
    $exprFunc = $map ?? fn( ...$args ) => expression( ...$args ) ;
    return compile
    ([
        Conditions::ELSE ,
        isset( $definition[ OpenEdge::ELSE ] ) ? $exprFunc( $definition[ OpenEdge::ELSE ] ) : Type::NULL
    ]);
}
