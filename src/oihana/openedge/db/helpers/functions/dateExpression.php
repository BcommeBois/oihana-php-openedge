<?php

namespace oihana\openedge\db\helpers\functions ;

use DateInvalidTimeZoneException;
use DateMalformedStringException;

use oihana\openedge\enums\OpenEdge;
use function oihana\core\date\formatDateTime;

/**
 * Builds an OpenEdge DATE literal expression.
 *
 * OpenEdge DATE literals support the ODBC escape clause format: { d 'yyyy-mm-dd' }
 *
 * @param ?string $expression The date expression to format.
 * @param array   $definition Associative array with optional keys:
 *                           - OpenEdge::TIMEZONE => timezone identifier for date parsing (defaults to 'UTC')
 *
 * @return ?string The formatted DATE literal expression.
 *
 * @throws DateInvalidTimeZoneException If the timezone is invalid.
 * @throws DateMalformedStringException If the date string cannot be parsed.
 *
 * @example
 * ```php
 * dateExpression('2025-07-20', []);
 * // Returns: '{ d '2025-07-20' }'
 *
 * dateExpression('2025-07-20', [OpenEdge::TIMEZONE => 'Europe/Paris']);
 * // Returns: '{ d '2025-07-20' }'
 * ```
 */
function dateExpression( ?string $expression , array $definition = [] ) :?string
{
    if ( !isset( $expression ) )
    {
        return null ;
    }

    $timezone = $definition[ OpenEdge::TIMEZONE ] ?? 'UTC' ;

    return "{ d '" . formatDateTime( $expression , $timezone , 'Y-m-d' ) . "' }" ;
}