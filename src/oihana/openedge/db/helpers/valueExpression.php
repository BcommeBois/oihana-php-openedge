<?php

namespace oihana\openedge\db\helpers;

use DateInvalidTimeZoneException;
use DateMalformedStringException;
use oihana\enums\Char;
use oihana\openedge\db\enums\functions\DateFunction;
use oihana\openedge\db\enums\functions\NumericFunction;
use oihana\openedge\enums\OpenEdge;
use oihana\reflect\exceptions\ConstantException;

use function oihana\openedge\db\helpers\functions\dates\curDate;
use function oihana\openedge\db\helpers\functions\dates\curTime;
use function oihana\openedge\db\helpers\functions\dates\now;
use function oihana\openedge\db\helpers\functions\dates\sysDate;
use function oihana\openedge\db\helpers\functions\dates\sysTime;
use function oihana\openedge\db\helpers\functions\dates\sysTimestamp;
use function oihana\openedge\db\helpers\functions\numerics\pi;

/**
 * Generates a SQL-ready expression for a value, optionally applying OpenEdge-specific alterations.
 *
 * This function interprets special constants for dates, times, and numeric values,
 * and applies any transformations defined in the `$definition` array using `overrideExpression`.
 *
 * Recognized special values include:
 * - `DateFunction::CURDATE`      → current date
 * - `DateFunction::CURTIME`      → current time
 * - `DateFunction::NOW`          → current timestamp
 * - `NumericFunction::PI`        → π
 * - `DateFunction::SYSDATE`      → system date
 * - `DateFunction::SYSTIME`      → system time
 * - `DateFunction::SYSTIMESTAMP` → system timestamp
 *
 * **Note:** If you wish to support additional special values (e.g., `PI()`, `NOW()`, etc.),
 * you will need to extend this function and handle them in the `match` statement.
 *
 * Any other value is treated as a literal via `literal()`.
 *
 * @param array $definition Array containing at least the key `OpenEdge::VALUE` and optional alterations.
 * @param callable|null $map Optional callback to transform the arguments before passing them to the function.
 *
 * @return string The resulting SQL expression, ready to be used in a query.
 *
 * @throws ConstantException If a provided function in the definition is invalid or unsupported.
 * @throws DateInvalidTimeZoneException
 * @throws DateMalformedStringException
 *
 * @example
 * ```php
 * use oihana\openedge\db\enums\OpenEdge;
 * use oihana\openedge\db\enums\functions\DateFunction;
 *
 * $definition =
 * [
 *     OpenEdge::VALUE => DateFunction::NOW
 * ];
 * echo valueExpression($definition);
 * // Output: "NOW()" (or equivalent OpenEdge SQL expression for the current timestamp)
 *
 * $definition =
 * [
 *     OpenEdge::VALUE  => 'price',
 *     OpenEdge::ALTERS => [ ['UPPER'] ]
 * ];
 * echo valueExpression( $definition ) ;
 * // Output: "UPPER(price)"
 * ```
 *
 * @see overrideExpression()
 * @see literal()
 */
function valueExpression( array $definition , ?callable $map = null ) :string
{
    $value = $definition[ OpenEdge::VALUE ] ?? null ;
    if( isset( $value ) )
    {
        $value = match ( $value )
        {
            DateFunction::CURDATE      => curDate(),
            DateFunction::CURTIME      => curTime(),
            DateFunction::NOW          => now(),
            NumericFunction::PI        => pi(),
            DateFunction::SYSDATE      => sysDate(),
            DateFunction::SYSTIME      => sysTime(),
            DateFunction::SYSTIMESTAMP => sysTimestamp(),
            default                    => literal( $value ) ,
        };
        return overrideExpression( $value , $definition , $map ) ;
    }
    return Char::EMPTY ;
}