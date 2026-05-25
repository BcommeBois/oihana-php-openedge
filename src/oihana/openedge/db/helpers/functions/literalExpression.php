<?php

namespace oihana\openedge\db\helpers\functions ;

use DateInvalidTimeZoneException;
use DateMalformedStringException;

use oihana\openedge\db\enums\Literal;
use oihana\openedge\enums\OpenEdge;
use oihana\reflect\exceptions\ConstantException;

/**
 * Generates a OpenEdge SQL literal expression for the given column or value.
 *
 * A literal, also called a constant, is a type of expression that specifies a constant value.
 * Generally, you can specify a literal wherever SQL syntax allows an expression.
 * Some SQL constructs allow literals but disallow other forms of expressions.
 *
 * Supports:
 * - NUMERIC literals: passed as-is (e.g., 123, 123.456, -123.456, 12.34E-04)
 * - STRING literals: escaped and enclosed in single quotes (single quotes doubled)
 * - DATE literals: formatted with OpenEdge escape syntax { d 'yyyy-mm-dd' }
 * - TIME literals: formatted with OpenEdge escape syntax { t 'hh:mi:ss' }
 * - TIMESTAMP literals: formatted with OpenEdge escape syntax { ts 'yyyy-mm-dd hh:mi:ss' }
 *
 * @param string|null $expression The expression or value to convert to a literal.
 *                                If null, the function returns null without processing.
 * @param array|null  $definition Associative array with optional keys:
 *                                - OpenEdge::LITERAL      => the literal type (required to apply transformations)
 *                                - OpenEdge::TIMEZONE     => timezone identifier for date/time parsing (defaults to 'UTC')
 *                                - OpenEdge::MILLISECONDS => whether to include milliseconds in TIME literals (defaults to false)
 *                                If not provided or null, the expression is returned as-is.
 *
 * @return string|null The formatted literal expression, or null if $expression is null.
 *
 * @throws ConstantException If the literal constant is not valid.
 * @throws DateInvalidTimeZoneException If the provided timezone is invalid.
 * @throws DateMalformedStringException If the date/time string cannot be parsed.
 *
 * @example
 * ```php
 * // Numeric literal
 * literalExpression('123', [OpenEdge::LITERAL => Literal::NUMERIC]);
 * // Returns: '123'
 *
 * // String literal
 * literalExpression('O\'Hare', [OpenEdge::LITERAL => Literal::STRING]);
 * // Returns: 'O''Hare'
 *
 * // Date literal
 * literalExpression('2025-07-20', [OpenEdge::LITERAL => Literal::DATE]);
 * // Returns: '{ d '2025-07-20' }'
 *
 * // Timestamp literal with timezone
 * literalExpression('2025-07-20 15:30:45', [
 *     OpenEdge::LITERAL  => Literal::TIMESTAMP,
 *     OpenEdge::TIMEZONE => 'Europe/Paris'
 * ]);
 * // Returns: '{ ts '2025-07-20 15:30:45' }'
 *
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/Literals.html
 */
function literalExpression( ?string $expression , ?array $definition = [] ) :?string
{
    if ( !isset( $expression ) )
    {
        return null ;
    }

    if ( !isset( $definition ) )
    {
        return $expression ;
    }

    $literal = $definition[ OpenEdge::LITERAL ] ?? null ;

    if ( !isset( $literal ) )
    {
        return $expression ;
    }

    Literal::validate( $literal ) ;

    return match( $literal )
    {
        Literal::STRING     => stringExpression    ( $expression ) ,
        Literal::DATE       => dateExpression      ( $expression , $definition ) ,
        Literal::TIME       => timeExpression      ( $expression , $definition ) ,
        Literal::TIMESTAMP  => timestampExpression ( $expression , $definition ) ,
        default             => $expression
    } ;
}