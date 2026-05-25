<?php

namespace oihana\openedge\db\helpers\functions\alters;

use oihana\openedge\db\enums\functions\DateFunction;

use function oihana\openedge\db\helpers\functions\dates\curDate;
use function oihana\openedge\db\helpers\functions\dates\curTime;
use function oihana\openedge\db\helpers\functions\dates\now;
use function oihana\openedge\db\helpers\functions\dates\sysDate;
use function oihana\openedge\db\helpers\functions\dates\sysTime;
use function oihana\openedge\db\helpers\functions\dates\sysTimestamp;

/**
 * Applies a date/time SQL function to a given key (column or expression) in OpenEdge SQL.
 *
 * Supports standard OpenEdge SQL date/time functions:
 * - `CURDATE()`        : Returns the current date.
 * - `CURTIME()`        : Returns the current time.
 * - `NOW()`            : Returns the current timestamp.
 * - `SYSDATE()`        : Returns the current system date.
 * - `SYSTIME()`        : Returns the current system time.
 * - `SYSTIMESTAMP()`   : Returns the current system timestamp.
 *
 * If the function is not recognized or null, the original key is returned.
 *
 * **Usage examples:**
 * ```php
 * alterDate('order_date', DateFunction::CURDATE);      // Outputs: CURDATE()
 * alterDate('order_time', DateFunction::CURTIME);      // Outputs: CURTIME()
 * alterDate('created_at', DateFunction::NOW);          // Outputs: NOW()
 * alterDate('birth_date');                             // Outputs: birth_date
 * ```
 *
 * @param string      $key      The SQL column name or expression to convert.
 * @param string|null $function Optional. The date/time function to apply. Must be one of {@see DateFunction}.
 * @param array       $args     Optional. Not used by date functions but included for compatibility.
 *
 * @return string The SQL expression after applying the date/time function.
 *
 * @see DateFunction
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/CURDATE.html
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/CURTIME.html
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/NOW.html
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/SYSDATE.html
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/SYSTIME.html
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/SYSTIMESTAMP.html
 */
function alterDate( string $key , ?string $function = null , array $args = [] ):string
{
    return match ( $function )
    {
        DateFunction::CURDATE      => curDate() ,
        DateFunction::CURTIME      => curTime() ,
        DateFunction::NOW          => now() ,
        DateFunction::SYSDATE      => sysDate() ,
        DateFunction::SYSTIME      => sysTime() ,
        DateFunction::SYSTIMESTAMP => sysTimestamp() ,
        default                    => $key ,
    };
}