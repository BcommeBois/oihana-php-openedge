<?php

namespace oihana\openedge\db\helpers;

use oihana\enums\Char;

/**
 * Extracts one element from a Progress extent serialized as a ';'-joined string.
 *
 * A `SELECT *` on an OpenEdge array column returns its elements joined by ';'
 * (e.g. `remise1` → `"5;0;0;0"`). This returns the element at `$index` (0-based,
 * so index 0 is the Progress `[1]` element), trimmed, or an empty string when
 * the value or the requested index is absent.
 *
 * @param string|null $value The raw ';'-joined extent value.
 * @param int         $index The 0-based element to extract (default: the first).
 *
 * @return string The extracted element, or an empty string.
 */
function parseExtent( ?string $value , int $index = 0 ) : string
{
    if ( $value === null || $value === Char::EMPTY )
    {
        return Char::EMPTY ;
    }

    $parts = explode( Char::SEMI_COLON , $value ) ;

    return trim( $parts[ $index ] ?? Char::EMPTY ) ;
}
