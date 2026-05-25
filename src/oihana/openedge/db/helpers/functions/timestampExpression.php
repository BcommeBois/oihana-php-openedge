<?php

namespace oihana\openedge\db\helpers\functions ;

use DateInvalidTimeZoneException;
use DateMalformedStringException;

use oihana\openedge\enums\OpenEdge;
use function oihana\core\date\formatDateTime;

/**
 * Builds an OpenEdge TIMESTAMP literal expression.
 *
 * OpenEdge TIMESTAMP literals support the ODBC escape clause format: { ts 'yyyy-mm-dd hh:mi:ss' }
 *
 * @param ?string $expression The timestamp expression to format.
 * @param array   $definition Associative array with optional keys:
 *                           - OpenEdge::TIMEZONE => timezone identifier for timestamp parsing (defaults to 'UTC')
 *
 * @return ?string The formatted TIMESTAMP literal expression.
 *
 * @throws DateInvalidTimeZoneException If the timezone is invalid.
 * @throws DateMalformedStringException If the timestamp string cannot be parsed.
 *
 * @example
 * ```php
 * timestampExpression('2025-07-20 15:30:45', []);
 * // Returns: '{ ts '2025-07-20 15:30:45' }'
 *
 * timestampExpression('2025-07-20 15:30:45', [OpenEdge::TIMEZONE => 'Europe/Paris']);
 * // Returns: '{ ts '2025-07-20 15:30:45' }'
 * ```
 */
function timestampExpression( ?string $expression , array $definition = [] ) :?string
{
    if ( !isset( $expression ) )
    {
        return null ;
    }

    $timezone = $definition[ OpenEdge::TIMEZONE ] ?? 'UTC' ;

    return "{ ts '" . formatDateTime( $expression , $timezone , 'Y-m-d H:i:s' ) . "' }" ;
}