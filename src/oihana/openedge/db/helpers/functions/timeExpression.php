<?php

namespace oihana\openedge\db\helpers\functions ;

use DateInvalidTimeZoneException;
use DateMalformedStringException;

use oihana\openedge\enums\OpenEdge;
use function oihana\core\date\formatDateTime;

/**
 * Builds an OpenEdge TIME literal expression with optional milliseconds.
 *
 * OpenEdge TIME literals support the format: { t 'hh:mi:ss[:mls]' }
 * where mls is an optional 3-digit milliseconds value (000-999).
 *
 * @param ?string $expression The time expression to format.
 * @param array   $definition Associative array with optional keys:
 *                           - OpenEdge::TIMEZONE     => timezone identifier for date/time parsing (defaults to 'UTC')
 *                           - OpenEdge::MILLISECONDS => whether to include milliseconds in TIME literals (defaults to false)
 *
 * @return ?string The formatted TIME literal expression.
 *
 * @throws DateInvalidTimeZoneException If the timezone is invalid.
 * @throws DateMalformedStringException If the time string cannot be parsed.
 *
 * @example
 * ```php
 * timeExpression('15:30:45');
 * // Returns: '{ t '15:30:45' }'
 *
 * timeExpression('15:30:45.678',
 * [
 *     OpenEdge::TIMEZONE     => 'UTC',
 *     OpenEdge::MILLISECONDS => true
 * ]);
 * // Returns: '{ t '15:30:45:678' }'
 * ```
 */
function timeExpression( ?string $expression , array $definition = []  ) :?string
{
    if ( !isset( $expression ) )
    {
        return null ;
    }

    $withMilliseconds = $definition[ OpenEdge::MILLISECONDS ] ?? false ;
    $timezone         = $definition[ OpenEdge::TIMEZONE     ] ?? 'UTC' ;

    if ( $withMilliseconds )
    {
        $formatted = formatDateTime( $expression , $timezone , 'H:i:s.v' ) ;
        // Convert the decimal format .v into the OpenEdge :mls format
        // '15:30:45.678' -> '15:30:45:678'
        $formatted = str_replace( '.' , ':' , $formatted ) ;
        return "{ t '" . $formatted . "' }" ;
    }

    return "{ t '" . formatDateTime( $expression , $timezone , 'H:i:s' ) . "' }" ;
}