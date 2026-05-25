<?php

namespace oihana\openedge\db\helpers\functions\alters;

use oihana\openedge\db\enums\functions\NumericFunction;

use function oihana\openedge\db\helpers\functions\numerics\abs;
use function oihana\openedge\db\helpers\functions\numerics\acos;
use function oihana\openedge\db\helpers\functions\numerics\asin;
use function oihana\openedge\db\helpers\functions\numerics\atan;
use function oihana\openedge\db\helpers\functions\numerics\atan2;
use function oihana\openedge\db\helpers\functions\numerics\ceiling;
use function oihana\openedge\db\helpers\functions\numerics\cos;
use function oihana\openedge\db\helpers\functions\numerics\degrees;
use function oihana\openedge\db\helpers\functions\numerics\exp;
use function oihana\openedge\db\helpers\functions\numerics\floor;
use function oihana\openedge\db\helpers\functions\numerics\greatest;
use function oihana\openedge\db\helpers\functions\numerics\least;
use function oihana\openedge\db\helpers\functions\numerics\log10;
use function oihana\openedge\db\helpers\functions\numerics\mod;
use function oihana\openedge\db\helpers\functions\numerics\pi;
use function oihana\openedge\db\helpers\functions\numerics\power;
use function oihana\openedge\db\helpers\functions\numerics\radians;
use function oihana\openedge\db\helpers\functions\numerics\rand;
use function oihana\openedge\db\helpers\functions\numerics\round;
use function oihana\openedge\db\helpers\functions\numerics\sign;
use function oihana\openedge\db\helpers\functions\numerics\sin;
use function oihana\openedge\db\helpers\functions\numerics\sqrt;
use function oihana\openedge\db\helpers\functions\numerics\tan;

/**
 * Applies a numeric SQL function to a given key (column name or expression) in OpenEdge SQL.
 *
 * This function maps a high-level numeric function identifier (`NumericFunction`) to its
 * corresponding SQL expression by delegating to the appropriate helper function.
 *
 * Supported functions include:
 * - `ABS`, `ACOS`, `ASIN`, `ATAN`, `ATAN2`, `CEILING`, `COS`, `DEGREES`, `EXP`, `FLOOR`
 * - `GREATEST`, `LEAST`, `LOG10`, `MOD`, `PI`, `POWER`, `RADIANS`, `RAND`, `ROUND`
 * - `SIGN`, `SIN`, `SQRT`, `TAN`
 *
 * **SQL Conversion Example:**
 * ```php
 * alterNumeric('salary', NumericFunction::ABS);             // ABS(salary)
 * alterNumeric('angle', NumericFunction::COS);             // COS(angle)
 * alterNumeric('value', NumericFunction::GREATEST, [10, 20]); // GREATEST(value,10,20)
 * ```
 *
 * @param string      $key      The column name or SQL expression to which the function is applied.
 * @param string|null $function Optional numeric function identifier from `NumericFunction` enum.
 * @param array       $args     Optional additional arguments for functions that accept multiple parameters (e.g., `ATAN2`, `GREATEST`, `LEAST`, `MOD`, `POWER`, `ROUND`).
 *
 * @return string The generated SQL expression corresponding to the chosen numeric function.
 *
 * @see NumericFunction
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/numeric-functions
 */
function alterNumeric( string $key , ?string $function = null , array $args = [] ):string
{
    return match ( $function )
    {
        NumericFunction::ABS      => abs($key),
        NumericFunction::ACOS     => acos($key),
        NumericFunction::ASIN     => asin($key),
        NumericFunction::ATAN     => atan($key),
        NumericFunction::ATAN2    => atan2(...([$key, ...$args])),
        NumericFunction::CEILING  => ceiling($key),
        NumericFunction::COS      => cos($key),
        NumericFunction::DEGREES  => degrees($key),
        NumericFunction::EXP      => exp($key),
        NumericFunction::FLOOR    => floor($key),
        NumericFunction::GREATEST => greatest(...([$key, ...$args])),
        NumericFunction::LEAST    => least(...([$key, ...$args])),
        NumericFunction::LOG10    => log10($key),
        NumericFunction::MOD      => mod(...([$key, ...$args])),
        NumericFunction::PI       => pi(),
        NumericFunction::POWER    => power(...([$key, ...$args])),
        NumericFunction::RADIANS  => radians($key),
        NumericFunction::RAND     => rand($key),
        NumericFunction::ROUND    => round(...([$key, ...$args])),
        NumericFunction::SIGN     => sign($key),
        NumericFunction::SIN      => sin($key),
        NumericFunction::SQRT     => sqrt($key),
        NumericFunction::TAN      => tan($key),
        default                   => $key,
    };
}