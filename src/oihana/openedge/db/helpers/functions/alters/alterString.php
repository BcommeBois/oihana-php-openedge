<?php

namespace oihana\openedge\db\helpers\functions\alters;

use oihana\enums\Char;

use oihana\openedge\db\enums\functions\StringFunction;

use function oihana\openedge\db\helpers\functions\strings\ascii;
use function oihana\openedge\db\helpers\functions\strings\char;
use function oihana\openedge\db\helpers\functions\strings\chr;
use function oihana\openedge\db\helpers\functions\strings\concat;
use function oihana\openedge\db\helpers\functions\strings\difference;
use function oihana\openedge\db\helpers\functions\strings\initCap;
use function oihana\openedge\db\helpers\functions\strings\insertInString;
use function oihana\openedge\db\helpers\functions\strings\inString;
use function oihana\openedge\db\helpers\functions\strings\lcase;
use function oihana\openedge\db\helpers\functions\strings\left;
use function oihana\openedge\db\helpers\functions\strings\length;
use function oihana\openedge\db\helpers\functions\strings\locate;
use function oihana\openedge\db\helpers\functions\strings\lower;
use function oihana\openedge\db\helpers\functions\strings\lpad;
use function oihana\openedge\db\helpers\functions\strings\ltrim;
use function oihana\openedge\db\helpers\functions\strings\prefix;
use function oihana\openedge\db\helpers\functions\strings\proArrayDescape;
use function oihana\openedge\db\helpers\functions\strings\proArrayEscape;
use function oihana\openedge\db\helpers\functions\strings\proElement;
use function oihana\openedge\db\helpers\functions\strings\repeat;
use function oihana\openedge\db\helpers\functions\strings\replace;
use function oihana\openedge\db\helpers\functions\strings\right;
use function oihana\openedge\db\helpers\functions\strings\rpad;
use function oihana\openedge\db\helpers\functions\strings\rtrim;
use function oihana\openedge\db\helpers\functions\strings\substr;
use function oihana\openedge\db\helpers\functions\strings\substring;
use function oihana\openedge\db\helpers\functions\strings\suffix;
use function oihana\openedge\db\helpers\functions\strings\translate;
use function oihana\openedge\db\helpers\functions\strings\ucase;
use function oihana\openedge\db\helpers\functions\strings\upper;

/**
 * Applies a string SQL function to a given key (column name or expression) in OpenEdge SQL.
 *
 * This function maps a high-level string function identifier (`StringFunction`) to its
 * corresponding SQL expression by delegating to the appropriate helper function.
 *
 * Supported functions include:
 * - `ASCII`, `CHAR`, `CHR`, `CONCAT`, `DIFFERENCE`, `INITCAP`, `INSERT`, `INSTR`, `LCASE`, `LEFT`
 * - `LENGTH`, `LTRIM`, `PRO_ARR_DESCAPE`, `PRO_ARR_ESCAPE`, `PRO_ELEMENT`, `LOCATE`, `LOWER`, `LPAD`
 * - `PREFIX`, `REPEAT`, `REPLACE`, `RIGHT`, `RPAD`, `RTRIM`, `SUBSTR`, `SUBSTRING`, `SUFFIX`
 * - `TRANSLATE`, `UCASE`, `UPPER`
 *
 * **Usage Examples:**
 * ```php
 * alterString('name', StringFunction::ASCII ) ;                   // ASCII(name)
 * alterString('first_name', StringFunction::INITCAP ) ;           // INITCAP(first_name)
 * alterString('text', StringFunction::CONCAT, ['_suffix'] ) ;     // CONCAT(text,'_suffix')
 * alterString('full_name', StringFunction::INSTR, [' ', 1, 1] ) ; // INSTR(full_name,' ',1,1)
 * ```
 *
 * @param string        $key       The column name or SQL expression to which the string function is applied.
 * @param string|null   $function  Optional string function identifier from `StringFunction` enum.
 * @param array         $args      Optional additional arguments for functions that accept multiple parameters
 *                                 (e.g., `CONCAT`, `INSTR`, `LEFT`, `RPAD`, `TRANSLATE`).
 *
 * @return string The generated SQL expression corresponding to the chosen string function.
 *
 * @see StringFunction
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/string-functions
 */
function alterString( string $key , ?string $function = null , array $args = [] ):string
{
    return match ( $function )
    {
        StringFunction::ASCII           => ascii($key),
        StringFunction::CHAR            => char($key),
        StringFunction::CHR             => chr($key),
        StringFunction::CONCAT          => concat(...([$key, ...$args])),
        StringFunction::DIFFERENCE      => difference(...([$key, ...$args])),
        StringFunction::INITCAP         => initCap($key),
        StringFunction::INSERT          => insertInString(...([$key, ...$args])),
        StringFunction::INSTR           => inString($key, $args[0] ?? null, $args[1] ?? 1, $args[2] ?? 1),
        StringFunction::LCASE           => lcase(...([$key, ...$args])),
        StringFunction::LEFT            => left($key, $args[0] ?? null),
        StringFunction::LENGTH          => length($key),
        StringFunction::LTRIM           => ltrim(...([$key, ...$args])),
        StringFunction::PRO_ARR_DESCAPE => proArrayDescape($key),
        StringFunction::PRO_ARR_ESCAPE  => proArrayEscape($key),
        StringFunction::PRO_ELEMENT     => proElement(...([$key, ...$args])),
        StringFunction::LOCATE          => locate(...([$key, ...$args])),
        StringFunction::LOWER           => lower($key),
        StringFunction::LPAD            => lpad(...([$key, ...$args])),
        StringFunction::PREFIX          => prefix(...([$key, ...$args])),
        StringFunction::REPEAT          => repeat(...([$key, ...$args])),
        StringFunction::REPLACE         => replace(...([$key, ...$args])),
        StringFunction::RIGHT           => right($key, $args[0] ?? null),
        StringFunction::RPAD            => rpad($key, $args[0] ?? null, $args[1] ?? null),
        StringFunction::RTRIM           => rtrim(...([$key, ...$args])),
        StringFunction::SUBSTR          => substr(...([$key, ...$args])),
        StringFunction::SUBSTRING       => substring(...([$key, ...$args])),
        StringFunction::SUFFIX          => suffix(...([$key, ...$args])),
        StringFunction::TRANSLATE       => translate($key, $args[0] ?? Char::EMPTY, $args[1] ?? Char::EMPTY),
        StringFunction::UCASE           => ucase($key),
        StringFunction::UPPER           => upper($key),
        default                         => $key,
    };
}