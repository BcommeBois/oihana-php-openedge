# Numeric functions

OpenEdge exposes 23 numeric functions (arithmetic, trigonometry, rounding, scalar min/max). The [`NumericFunction`](../../../src/oihana/openedge/db/enums/functions/NumericFunction.php) enum lists them, and the framework provides a PHP helper per function under [`db/helpers/functions/numerics/`](../../../src/oihana/openedge/db/helpers/functions/numerics/).

> **Canonical reference.** [Progress SQL — Numeric functions](https://docs.progress.com/bundle/openedge-sql-reference/page/Numeric-functions.html).

## Overview by family

| Family | Functions |
|---|---|
| Absolute value and sign | `ABS`, `SIGN` |
| Rounding | `CEILING`, `FLOOR`, `ROUND` |
| Modulo and power | `MOD`, `POWER`, `SQRT`, `EXP` |
| Logarithms | `LOG10` |
| Trigonometry | `SIN`, `COS`, `TAN`, `ASIN`, `ACOS`, `ATAN`, `ATAN2` |
| Angle conversion | `DEGREES`, `RADIANS` |
| Constants and random | `PI`, `RAND` |
| Scalar min / max | `GREATEST`, `LEAST` |

## Absolute value and sign

### `ABS`

```php
use function oihana\openedge\db\helpers\functions\numerics\abs ;

echo abs( 'amount' ) ;
// ABS(amount)
```

### `SIGN`

Returns `-1`, `0`, or `+1` according to the sign of the expression.

```php
use function oihana\openedge\db\helpers\functions\numerics\sign ;

echo sign( 'solde' ) ;
// SIGN(solde)
```

## Rounding

### `CEILING`

Smallest integer greater than or equal to.

```php
use function oihana\openedge\db\helpers\functions\numerics\ceiling ;

echo ceiling( 'net_price * 1.20' ) ;
// CEILING(net_price * 1.20)
```

### `FLOOR`

Largest integer less than or equal to.

```php
use function oihana\openedge\db\helpers\functions\numerics\floor ;

echo floor( 'net_price * 1.20' ) ;
// FLOOR(net_price * 1.20)
```

### `ROUND`

Rounding to N decimal places.

```php
use function oihana\openedge\db\helpers\functions\numerics\round ;

echo round( 'net_price * 1.20' , 2 ) ;
// ROUND(net_price * 1.20, 2)
```

> To format a number as a string with thousands and decimal separators, prefer [`TO_CHAR`](sql-functions-conversions.md#to_char) with a format mask.

## Modulo and power

### `MOD`

Remainder of integer division.

```php
use function oihana\openedge\db\helpers\functions\numerics\mod ;

echo mod( 'customer_id' , 100 ) ;
// MOD(customer_id, 100)
```

### `POWER`

Raise to a power.

```php
use function oihana\openedge\db\helpers\functions\numerics\power ;

echo power( 'base' , 'exposant' ) ;
// POWER(base, exposant)

echo power( 2 , 10 ) ;
// POWER(2, 10) → 1024
```

### `SQRT`

Square root.

```php
use function oihana\openedge\db\helpers\functions\numerics\sqrt ;

echo sqrt( 'surface' ) ;
// SQRT(surface)
```

### `EXP`

Exponential (e^x).

```php
use function oihana\openedge\db\helpers\functions\numerics\exp ;

echo exp( 'x' ) ;
// EXP(x)
```

## Logarithms

### `LOG10`

Decimal logarithm.

```php
use function oihana\openedge\db\helpers\functions\numerics\log10 ;

echo log10( 'revenue' ) ;
// LOG10(revenue)
```

OpenEdge doesn't expose `LN` (natural log) — use `LOG10` and convert if needed.

## Trigonometry

All trigonometric functions expect or return radians.

```php
use function oihana\openedge\db\helpers\functions\numerics\sin   ;
use function oihana\openedge\db\helpers\functions\numerics\cos   ;
use function oihana\openedge\db\helpers\functions\numerics\tan   ;
use function oihana\openedge\db\helpers\functions\numerics\asin  ;
use function oihana\openedge\db\helpers\functions\numerics\acos  ;
use function oihana\openedge\db\helpers\functions\numerics\atan  ;
use function oihana\openedge\db\helpers\functions\numerics\atan2 ;

echo sin  ( 'theta'   ) ;             // SIN(theta)
echo cos  ( 'theta'   ) ;             // COS(theta)
echo tan  ( 'theta'   ) ;             // TAN(theta)
echo asin ( 'x'       ) ;             // ASIN(x)
echo acos ( 'x'       ) ;             // ACOS(x)
echo atan ( 'x'       ) ;             // ATAN(x)
echo atan2( 'y' , 'x' ) ;             // ATAN2(y, x)
```

`ATAN2(y, x)` is preferable to `ATAN(y / x)` because it handles the correct quadrant (and division by zero).

## Angle conversion

### `DEGREES` / `RADIANS`

```php
use function oihana\openedge\db\helpers\functions\numerics\degrees ;
use function oihana\openedge\db\helpers\functions\numerics\radians ;

echo degrees( 'angle_rad' ) ;  // DEGREES(angle_rad)
echo radians( 'angle_deg' ) ;  // RADIANS(angle_deg)
```

## Constants and random

### `PI`

```php
use function oihana\openedge\db\helpers\functions\numerics\pi ;

echo pi() ;
// PI()
```

### `RAND`

Random number between 0 (inclusive) and 1 (exclusive).

```php
use function oihana\openedge\db\helpers\functions\numerics\rand ;

echo rand() ;
// RAND()
```

> To generate a number in `[a, b]`, compute `a + (b - a) * RAND()`.

## Scalar min / max

### `GREATEST` / `LEAST`

Largest / smallest value among N arguments. They are not aggregates (which operate on N rows) — see [`MAX`/`MIN`](sql-functions-aggregates.md) for aggregates.

```php
use function oihana\openedge\db\helpers\functions\numerics\greatest ;
use function oihana\openedge\db\helpers\functions\numerics\least    ;

echo greatest([ 'net_price' , 'min_price' ]) ;  // GREATEST(net_price, min_price)
echo least   ([ 'net_price' , 'max_price' ]) ;  // LEAST(net_price, max_price)
```

## Composition

As with strings, numeric functions compose with [`columnExpression`](../helpers.md) through `SQL::ALTER` or `SQL::ALTERS`:

```php
use oihana\openedge\db\enums\functions\NumericFunction ;
use oihana\openedge\enums\OpenEdge as SQL ;
use function oihana\openedge\db\helpers\expression ;

echo expression([
    SQL::COLUMN => 'net_price'                    ,
    SQL::TABLE  => 'produits'                   ,
    SQL::ALTERS =>
    [
        [ NumericFunction::POWER , 2 ]          , // POWER(x, 2)
        NumericFunction::ROUND                    , // ROUND(POWER(x, 2))
    ],
]) ;
// → ROUND(POWER(products.net_price, 2))
```

## See also

- [Aggregates](sql-functions-aggregates.md) — `COUNT`, `SUM`, `AVG`, `MIN`, `MAX` (not to be confused with `LEAST`/`GREATEST`).
- [`CAST`](sql-functions-casts.md) — explicit precision bounding after calculation.
- [Conversions](sql-functions-conversions.md) — `TO_NUMBER` to parse a string into a number.
- [Progress SQL — Numeric functions](https://docs.progress.com/bundle/openedge-sql-reference/page/Numeric-functions.html) — canonical reference.
