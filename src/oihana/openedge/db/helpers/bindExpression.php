<?php

namespace oihana\openedge\db\helpers;

use DateInvalidTimeZoneException;
use DateMalformedStringException;
use oihana\enums\Char;
use oihana\openedge\enums\OpenEdge;
use oihana\reflect\exceptions\ConstantException;

/**
 * Generates a SQL binding expression for use in OpenEdge queries.
 *
 * This function prepends a colon (`:`) to the binding key specified in
 * `$definition[OpenEdge::BIND]` and applies any alterations defined
 * via `overrideExpression()`. It is typically used to represent
 * bind variables in parameterized queries.
 *
 * If the `BIND` key is empty or not provided, an empty string is returned.
 *
 * **Example:**
 * ```php
 * use function oihana\openedge\db\helpers\bindExpression;
 * use oihana\openedge\enums\OpenEdge;
 *
 * $definition = [ OpenEdge::BIND => 'userId' ];
 * echo bindExpression($definition); // outputs: ":userId"
 *
 * // With alterations
 * $definition = [
 *     OpenEdge::BIND  => 'price',
 *     OpenEdge::ALTER => 'ROUND'
 * ];
 * echo bindExpression($definition); // outputs something like: "ROUND(:price)"
 * ```
 *
 * @param array $definition
 *       Array defining the binding, possible keys:
 *         - `OpenEdge::BIND` (string) : The name of the bind variable.
 *         - Other keys supported by `overrideExpression()` (e.g., CAST, ALTER, ALTERS)
 *
 * @param callable|null $map
 *        Optional callback to transform the arguments before passing them to the function.
 *
 * @return string The generated bind expression, potentially wrapped with SQL alterations.
 *
 * @throws ConstantException If an invalid alteration or function is provided in the definition.
 * @throws DateInvalidTimeZoneException
 * @throws DateMalformedStringException
 *
 * @see overrideExpression()
 */
function bindExpression
(
    array     $definition ,
    ?callable $map = null
)
:string
{
    $expression = $definition[ OpenEdge::BIND ] ?? null ;

    if ( !is_string( $expression ) || $expression === Char::EMPTY )
    {
        return Char::EMPTY;
    }

    return overrideExpression
    (
        expression : $expression != Char::EMPTY ? Char::COLON . $expression : Char::EMPTY  ,
        definition : $definition ,
        map        : $map
    ) ;
}