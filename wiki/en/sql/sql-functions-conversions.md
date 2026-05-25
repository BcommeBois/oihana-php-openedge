# Explicit conversions

OpenEdge exposes a family of `TO_*` functions that complement `CAST`: they convert between types **tolerantly** (returning `NULL` if the value is not parseable, where `CAST` would throw) and accept a **format mask** for dates and numbers.

The [`ConversionFunction`](../../../src/oihana/openedge/db/enums/functions/ConversionFunction.php) enum lists the seven available conversions, and the framework provides one helper per function under [`db/helpers/functions/conversions/`](../../../src/oihana/openedge/db/helpers/functions/conversions/).

> **Canonical reference.** [Progress SQL — Conversion functions](https://docs.progress.com/bundle/openedge-sql-reference/page/Numeric-functions.html).

## `CAST` vs `TO_*` — when to use which

| Criterion | `CAST` | `TO_*` |
|---|---|---|
| Error if the value doesn't fit | **throws** | returns `NULL` |
| Format mask (dates, numbers) | no | **yes** |
| Standard SQL | yes (SQL-92) | no (Oracle-style) |
| ODBC compatible | yes | partially (`NVL` isn't, for instance) |

Rule of thumb:

- When the value **must** be convertible (e.g. `customer_id DECIMAL → VARCHAR` for API normalisation), use `CAST`. A failure reveals a data bug to fix.
- When the value **may** not be convertible (e.g. a user-typed string converted to a date), use `TO_*`. The resulting `NULL` can be handled by `COALESCE`/`NVL`.

## `TO_CHAR()` {#to_char}

Converts an expression to `CHAR`, with an optional format mask. The most useful function, because it lets you **format** a date or a number on the server side without doing it in PHP.

```php
use function oihana\openedge\db\helpers\functions\conversions\toChar ;

echo toChar( 'created_at' ) ;
// TO_CHAR(created_at)

echo toChar( 'created_at' , "'YYYY-MM-DD'" ) ;
// TO_CHAR(created_at, 'YYYY-MM-DD')

echo toChar( 'net_price' , "'999G990D00'" ) ;
// TO_CHAR(net_price, '999G990D00')  → "  1 234,56" (locale-dependent separator)
```

### Common date format masks

| Mask | Example |
|---|---|
| `'YYYY-MM-DD'` | `2026-05-19` |
| `'DD/MM/YYYY'` | `19/05/2026` |
| `'YYYY-MM-DD HH24:MI:SS'` | `2026-05-19 14:30:00` |
| `'Day, DD Month YYYY'` | `Tuesday, 19 May 2026` |
| `'YYYY-WW'` | `2026-21` (week number) |

### Common number format masks

| Mask | Example |
|---|---|
| `'9999'` | integer, 4 digits |
| `'9990D00'` | decimal with 2 decimals |
| `'999G990D00'` | decimal with thousands separator and 2 decimals |
| `'9999PR'` | negative number in parentheses |
| `'X9'` | hexadecimal |

> The thousands separator (`G`) and decimal separator (`D`) are **locale-dependent** — `,` vs `.` depending on server settings.

## `TO_DATE()` {#to_date}

Converts a string to `DATE` according to a format mask.

```php
use function oihana\openedge\db\helpers\functions\conversions\toDate ;

echo toDate( "'2026-05-19'" , "'YYYY-MM-DD'" ) ;
// TO_DATE('2026-05-19', 'YYYY-MM-DD')

echo toDate( 'date_str' , "'DD/MM/YYYY'" ) ;
// TO_DATE(date_str, 'DD/MM/YYYY')
```

Returns `NULL` if the string doesn't match the format.

## `TO_TIME()` {#to_time}

Converts a string to `TIME`.

```php
use function oihana\openedge\db\helpers\functions\conversions\toTime ;

echo toTime( "'14:30:00'" , "'HH24:MI:SS'" ) ;
// TO_TIME('14:30:00', 'HH24:MI:SS')
```

## `TO_TIMESTAMP()` {#to_timestamp}

Converts a string to `TIMESTAMP`.

```php
use function oihana\openedge\db\helpers\functions\conversions\toTimestamp ;

echo toTimestamp( "'2026-05-19 14:30:00'" , "'YYYY-MM-DD HH24:MI:SS'" ) ;
// TO_TIMESTAMP('2026-05-19 14:30:00', 'YYYY-MM-DD HH24:MI:SS')
```

## `TO_NUMBER()` {#to_number}

Converts a string to a number.

```php
use function oihana\openedge\db\helpers\functions\conversions\toNumber ;

echo toNumber( "'1234.56'" ) ;
// TO_NUMBER('1234.56')

echo toNumber( "'1 234,56'" , "'999G990D00'" ) ;
// TO_NUMBER('1 234,56', '999G990D00')
```

The mask is optional but necessary when the separator isn't the standard `.`.

## Typical usage pattern

In a typical host application, `TO_CHAR` is heavily used to expose an ISO 8601 date through the API from a Progress `DATE` column:

```php
use oihana\openedge\db\enums\functions\ConversionFunction ;
use oihana\openedge\enums\OpenEdge as SQL ;

SQL::COLUMNS =>
[
    [
        SQL::COLUMN => 'created_at'                      ,
        SQL::TABLE  => 'clients'                      ,
        SQL::ALTER  => [
            ConversionFunction::TO_CHAR ,
            "'YYYY-MM-DD'" ,
        ],
        SQL::ALIAS  => 'created' ,
    ],
]
// → TO_CHAR(clients.created_at, 'YYYY-MM-DD') AS "created"
```

## `CONVERT` and `DECODE` — not exposed as helpers

Two constants in [`ConversionFunction`](../../../src/oihana/openedge/db/enums/functions/ConversionFunction.php) have no dedicated PHP helper:

- **`CONVERT`** — ODBC variant of `CAST`, rarely used in practice. Prefer `CAST`.
- **`DECODE`** — Oracle equivalent of `CASE`. Prefer an explicit `CASE WHEN` expression ([`whenExpression`](sql-functions-cases.md)).

## See also

- [`CAST` and SQL types](sql-functions-casts.md) — strict conversion with errors on failure.
- [SQL conditionals](sql-functions-conditionals.md) — `COALESCE` / `NVL` to handle `NULL`s coming from `TO_*`.
- [`CASE` expressions](sql-functions-cases.md) — typed alternative to `DECODE`.
- [Progress SQL — Conversion functions](https://docs.progress.com/bundle/openedge-sql-reference/page/Numeric-functions.html) — canonical reference.
