<?php

namespace oihana\openedge\db\helpers;

use DateInvalidTimeZoneException;
use DateMalformedStringException;

use oihana\openedge\enums\OpenEdge;
use oihana\reflect\exceptions\ConstantException;

use function oihana\openedge\db\helpers\functions\alters\alterExpression;
use function oihana\openedge\db\helpers\functions\alters\altersExpression;
use function oihana\openedge\db\helpers\functions\castExpression;
use function oihana\openedge\db\helpers\functions\literalExpression;

/**
 * Applies a sequence of transformations to a given SQL expression or column.
 *
 * This function allows overriding or transforming an SQL expression in a controlled order:
 *
 * 1. **Literal alteration** (`OpenEdge::LITERAL`)
 * - Converts the expression into a literal using {@see literalExpression()}.
 * - Supported literals: NUMERIC, STRING, DATE, TIME, TIMESTAMP.
 * - Examples:
 * ```php
 * literalExpression('123', [OpenEdge::LITERAL => Literal::NUMERIC]); // '123'
 * literalExpression("O'Hare", [OpenEdge::LITERAL => Literal::STRING]); // 'O''Hare'
 * literalExpression('2025-07-20', [OpenEdge::LITERAL => Literal::DATE]); // { d '2025-07-20' }
 * ```
 *
 * 2. **CAST operation** (`OpenEdge::CAST`)
 * - Converts the expression to a specific SQL type using {@see castExpression()}.
 * - `$definition[OpenEdge::CAST]` can be:
 * - A string → type constant (e.g., `Type::VARCHAR`, `Type::INTEGER`).
 * - An array → `[type, length?, scale?]`.
 * - Example:
 * ```php
 * castExpression('price', [Type::DECIMAL, 10, 2]); // CAST(price AS DECIMAL(10,2))
 * ```
 *
 * 3. **Single alteration** (`OpenEdge::ALTER`)
 * - Applies a single transformation function using {@see alterExpression()}.
 * - `$definition[OpenEdge::ALTER]` can be a string (function name) or an array `[function, arg1, arg2,...]`.
 * - Supported functions: StringFunction, NumericFunction, DateFunction, ConversionFunction, ConditionalFunction.
 *
 * 4. **Multiple alterations** (`OpenEdge::ALTERS`)
 * - Applies a sequence of alterations using {@see altersExpression()}.
 * - Each alteration can be a string or array, applied in order.
 **Optional mapping**:
 * A callable `$map` can be provided to pre-process arguments before applying alterations.
 *
 * @param string|null $expression The SQL expression or column to transform. Returns `null` if not provided.
 * @param array|null  $definition Associative array defining transformations:
 *                                - OpenEdge::LITERAL => literal type (see {@see literalExpression()})
 *                                - OpenEdge::CAST    => cast definition (see {@see castExpression()})
 *                                - OpenEdge::ALTER   => single alteration (see {@see alterExpression()})
 *                                - OpenEdge::ALTERS  => array of alterations (see {@see altersExpression()})
 * @param callable|null $map Optional callback to transform arguments before passing to alteration functions.
 *
 * @return string|null The transformed SQL expression, or `null` if `$expression` is `null`.
 *
 * @throws ConstantException If a provided function in `$definition` is invalid or unsupported.
 * @throws DateInvalidTimeZoneException If a date/time literal contains an invalid timezone.
 * @throws DateMalformedStringException If a date/time literal cannot be parsed.
 *
 * @example
 * **Usage example:**
 * ```php
 * use oihana\openedge\db\enums\OpenEdge;
 * use oihana\openedge\db\enums\Type;
 * use oihana\openedge\db\enums\StringFunction;
 *
 * $sql = overrideExpression( 'user.age' ,
 * [
 *     OpenEdge::CAST   => [Type::INTEGER],
 *     OpenEdge::ALTER  => StringFunction::RPAD,
 *     OpenEdge::ALTERS =>
 *     [
 *        [ StringFunction::RPAD, 5, '-' ] ,
 *        StringFunction::LOWER
 *     ]
 * ]);
 * // Returns: LOWER(RPAD(RPAD(CAST(user.age AS INTEGER),5,'-')))
 *
 * @see castExpression()   Converts an SQL expression to a specific type.
 * @see alterExpression()  Applies a single SQL function transformation.
 * @see altersExpression() Applies multiple transformations in sequence.
 * @see literalExpression() Generates an OpenEdge SQL literal for the given column or value.
 * @see literalExpression() Generates a OpenEdge SQL literal expression for the given column or value.
 *
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/CAST.html
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/Literals.html
 */
function overrideExpression
(
    ?string   $expression ,
    ?array    $definition ,
    ?callable $map = null
)
:?string
{
    if( isset( $expression ) && isset( $definition ) )
    {
        $expression = literalExpression( $expression , $definition ) ;

        $cast = $definition[ OpenEdge::CAST ] ?? null ;
        if( isset( $cast ) )
        {
            $expression = castExpression( $expression , $cast ) ;
        }

        $alter = $definition[ OpenEdge::ALTER ] ?? null ;
        if( isset( $alter ) )
        {
            $expression = alterExpression( $expression , $alter , $map ) ;
        }

        $alters = $definition[ OpenEdge::ALTERS ] ?? null ;
        if( isset( $alters ) )
        {
            $expression = altersExpression( $expression , $alters , $map ) ;
        }
    }
    return $expression ;
}