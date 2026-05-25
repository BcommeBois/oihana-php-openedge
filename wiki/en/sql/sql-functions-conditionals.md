# SQL conditionals

**SQL conditionals** are functions that return a **different value depending on a condition** — typically to replace a `NULL` with a default, or to mark sentinel values (empty string, zero) as `NULL`. The [`ConditionalFunction`](../../../src/oihana/openedge/db/enums/functions/ConditionalFunction.php) enum lists Progress's available functions, and the framework provides six PHP helpers under [`db/helpers/functions/conditionals/`](../../../src/oihana/openedge/db/helpers/functions/conditionals/).

> **Canonical reference.** [Progress SQL — Conditional expressions](https://docs.progress.com/bundle/openedge-sql-reference/page/CASE.html).

## Overview

| Helper | SQL produced | When to use |
|---|---|---|
| [`coalesce`](#coalesce) | `COALESCE(a, b, c, …)` | First non-`NULL`. Standard SQL. |
| [`ifNull`](#ifnull) | `IFNULL(expr, fallback)` | Standard ODBC. Strictly two arguments. |
| [`nvl`](#nvl) | `NVL(expr, fallback)` | Oracle. Two arguments. **Not ODBC compatible**. |
| [`nullIf`](#nullif) | `NULLIF(a, b)` | Returns `NULL` if `a = b`, else `a`. |
| [`nullIfEmpty`](#nullifempty) | `NULLIF(expr, '')` | Shortcut for `''` ↔ `NULL`. |
| [`nullIfZero`](#nullifzero) | `NULLIF(expr, 0)` | Shortcut for `0` ↔ `NULL`. |

## `coalesce()` {#coalesce}

Returns the first non-`NULL` expression in the list.

```php
use function oihana\openedge\db\helpers\functions\conditionals\coalesce ;

echo coalesce([ 'promo_price' , 'net_price' , 0 ]) ;
// COALESCE(promo_price, net_price, 0)
```

Three typical cases:

- **Value cascade**: promo price if set, else regular price, else zero.
- **Displayable default**: `COALESCE(customer_name, '(deleted customer)')`.
- **Forces a non-NULL type on the API side**: useful when the API contract doesn't tolerate `null` on a field.

The optional second argument is a callback that transforms each expression before insertion:

```php
echo coalesce([ 'name' , 'city' ] , fn( $v ) => "'" . $v . "'" ) ;
// COALESCE('name', 'city')
```

## `ifNull()` {#ifnull}

Returns `fallback` if `expr` is `NULL`, else `expr`. Strictly two arguments.

```php
use function oihana\openedge\db\helpers\functions\conditionals\ifNull ;

echo ifNull( 'net_price' , 0 ) ;
// IFNULL(net_price, 0)
```

`IFNULL` is standard ODBC; it's the two-operand equivalent of `COALESCE`. For more than two values, use `COALESCE` directly.

## `nvl()` {#nvl}

Oracle synonym of `IFNULL`. Avoid when ODBC portability matters — Progress documentation explicitly notes that `NVL` is not ODBC compatible.

```php
use function oihana\openedge\db\helpers\functions\conditionals\nvl ;

echo nvl( 'net_price' , 0 ) ;
// NVL(net_price, 0)
```

> The helper is exposed for codebases consuming `oihana/openedge` through a non-ODBC client or for staying compatible with old SQL written in Oracle style. Prefer `IFNULL` or `COALESCE` otherwise.

## `nullIf()` {#nullif}

Returns `NULL` if `a = b`, else `a`. The inverse of `IFNULL`.

```php
use function oihana\openedge\db\helpers\functions\conditionals\nullIf ;

echo nullIf( 'country_code' , "'XX'" ) ;
// NULLIF(country_code, 'XX')
```

Typical case: an ERP column uses a sentinel value (`'XX'`, `0`, `'N/A'`) to mean "absent" — you replace it with `NULL` at projection time.

## `nullIfEmpty()` {#nullifempty}

Shortcut: `NULLIF(expr, '')`. Heavily used on `CHAR` Progress columns, which are space-padded — often a value stored as `'    '` (4 spaces) should be treated as `NULL` on the API side.

```php
use function oihana\openedge\db\helpers\functions\conditionals\nullIfEmpty ;

echo nullIfEmpty( 'description' ) ;
// NULLIF(description, '')
```

> **Warning.** `NULLIF(x, '')` doesn't handle whitespace-only strings. For that, combine with `LTRIM(RTRIM(x))` on the `ALTERS` side.

## `nullIfZero()` {#nullifzero}

Shortcut: `NULLIF(expr, 0)`. Useful for numeric columns where `0` means "not filled" on the ERP side.

```php
use function oihana\openedge\db\helpers\functions\conditionals\nullIfZero ;

echo nullIfZero( 'industry_code' ) ;
// NULLIF(industry_code, 0)
```

## Typical composition

The recurring pattern in a typical host application: `NULLIF` to normalise, then `COALESCE` for a displayable fallback.

```php
// "Trimmed customer name, default value if empty"
echo coalesce
([
    nullIfEmpty( 'LTRIM(RTRIM(customer_name))' ) ,
    "'(no name)'" ,
]) ;
// COALESCE(NULLIF(LTRIM(RTRIM(customer_name)), ''), '(no name)')
```

In a model definition:

```php
use oihana\openedge\db\enums\functions\ConditionalFunction ;
use oihana\openedge\enums\OpenEdge as SQL ;

SQL::COLUMNS =>
[
    [
        SQL::COLUMN => 'description'                      ,
        SQL::TABLE  => 'produits'                         ,
        SQL::ALTER  => ConditionalFunction::NULLIF_EMPTY  , // → NULLIF(products.description, '')
        SQL::ALIAS  => 'description'                      ,
    ],
]
```

> `NULLIF_EMPTY` and `NULLIF_ZERO` are **framework-specific** constants (not native Progress). They are recognised by `overrideExpression()` which unfolds them to `NULLIF(..., '')` or `NULLIF(..., 0)`. Convenient to stay within the typed constants system.

## See also

- [`CASE` expressions](sql-functions-cases.md) — generalisation of conditional logic.
- [Conversions](sql-functions-conversions.md) — `TO_*` returning `NULL` on a non-parseable value, to combine with `COALESCE`.
- [`CAST`](sql-functions-casts.md) — strict version (throws instead of returning `NULL`).
- [Progress SQL — Conditional functions](https://docs.progress.com/bundle/openedge-sql-reference/page/CASE.html) — canonical reference.
