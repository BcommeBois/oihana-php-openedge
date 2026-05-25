<?php

namespace oihana\openedge\db\helpers;

use oihana\enums\Char;
use oihana\openedge\db\enums\ConcatOperator;
use oihana\openedge\enums\OpenEdge;

use function oihana\core\arrays\getFirstKey;
use function oihana\core\strings\compile;

/**
 * Generates a concatenated SQL expression from an array of expressions.
 *
 * This function handles three types of concatenation:
 * - **ARRAY**: Concatenates expressions with semicolon separator (`;`)
 * - **LIST**: Concatenates expressions with a custom separator (default: `,`)
 * - **CONCAT**: Concatenates expressions with space separator
 *
 * **Definition array keys:**
 * - `OpenEdge::ARRAY` (array) — List of expressions to concatenate with `;`
 * - `OpenEdge::LIST` (array) — List of expressions to concatenate with custom separator
 * - `OpenEdge::CONCAT` (array) — List of expressions to concatenate with space
 * - `OpenEdge::SEPARATOR` (string|null) — Custom separator for LIST (optional, default: `,`)
 *
 * **Example usage:**
 * ```php
 * use oihana\openedge\db\enums\OpenEdge;
 *
 * // ARRAY concatenation
 * $definition =
 * [
 *     OpenEdge::ARRAY =>
 *     [
 *         ['column' => 'firstName'],
 *         ['column' => 'lastName']
 *     ]
 * ];
 * echo concatExpression($definition, $callable);
 * // Output: firstName || ';' || lastName
 *
 * // LIST with custom separator
 * $definition =
 * [
 *     OpenEdge::LIST =>
 *     [
 *         ['column' => 'firstName'],
 *         ['column' => 'lastName']
 *     ],
 *     OpenEdge::SEPARATOR => ' - '
 * ];
 * echo concatExpression($definition, $callable);
 * // Output: firstName || ' - ' || lastName
 *
 * // CONCAT with space
 * $definition =
 * [
 *     OpenEdge::CONCAT =>
 *     [
 *         ['column' => 'firstName'],
 *         ['column' => 'lastName']
 *     ]
 * ];
 * echo concatExpression($definition, $callable);
 * // Output: firstName || ' ' || lastName
 * ```
 *
 * @param array         $definition The concatenation definition array.
 * @param callable|null $callable   Optional callback to transform each expression.
 *
 * @return string The concatenated SQL expression, or empty string if no valid expressions.
 *
 * @see getFirstKey()
 * @see compile()
 * @see ConcatOperator::concatSeparator()
 */
function concatExpression
(
    array     $definition ,
    ?callable $callable   = null
)
:string
{
    $key         = getFirstKey( $definition , [ OpenEdge::ARRAY , OpenEdge::LIST ] , OpenEdge::CONCAT ) ;
    $expressions = $definition[ $key ] ?? null;

    if ( !is_array( $expressions ) || count( $expressions ) === 0 )
    {
        return Char::EMPTY ;
    }

    $separator = match ( $key )
    {
        OpenEdge::ARRAY  => ConcatOperator::concatSeparator( Char::SEMI_COLON ) ,
        OpenEdge::LIST   => ConcatOperator::concatSeparator( $definition[ OpenEdge::SEPARATOR ] ?? null ) ,
        default          => ConcatOperator::CONCAT_WITH_SPACE ,
    };

    return compile
    (
        expressions : $expressions ,
        separator   : $separator ,
        callback    : $callable
    );
}