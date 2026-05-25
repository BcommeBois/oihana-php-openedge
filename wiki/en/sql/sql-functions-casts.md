# `CAST` and SQL types

`CAST` is the standard SQL operation to convert an expression from one type to another. It's the most used tool in practice with OpenEdge — ERP columns are often typed in unusual ways (customers identified by `DECIMAL(15,0)` instead of `INTEGER`, country codes as `CHAR(3)` padded with spaces, dates encoded as `INTEGER` or as `CHAR`…). You cast to normalise to the API-expected format.

> **Canonical reference.** [Progress SQL — CAST](https://docs.progress.com/bundle/openedge-sql-reference/page/CAST.html).

## The generic `cast()` helper

```php
use oihana\openedge\db\enums\Type ;
use function oihana\openedge\db\helpers\functions\cast ;

// Simple cast, no parameter
echo cast( 'prix_ht' , Type::INTEGER ) ;
// CAST(prix_ht AS INTEGER)

// Cast with length
echo cast( 'nom_client' , Type::VARCHAR , 20 ) ;
// CAST(nom_client AS VARCHAR(20))

// Cast with precision and scale
echo cast( 'montant' , Type::DECIMAL , [ 10 , 2 ] ) ;
// CAST(montant AS DECIMAL(10, 2))
```

`cast()` validates that `$type` is a `Type::*` constant (through `openEdgeType()`) and throws a `ConstantException` otherwise. No risk of writing `CAST(... AS UNKNOWN_TYPE)`.

## Specialised helpers — one per type

For the most common types, the framework exposes a dedicated helper with idiomatic arguments. This avoids having to remember whether `VARCHAR` takes `(length)` or `(precision, scale)`.

### `castVARCHAR( expr , length = 1 )`

```php
use function oihana\openedge\db\helpers\functions\casts\castVARCHAR ;

echo castVARCHAR( 'cd_client' , 10 ) ;
// CAST(cd_client AS VARCHAR(10))
```

### `castCHAR( expr , length = 1 )`

Fixed-length string, right-padded with spaces.

### `castINTEGER( expr )`

```php
use function oihana\openedge\db\helpers\functions\casts\castINTEGER ;

echo castINTEGER( 'cd_client' ) ;
// CAST(cd_client AS INTEGER)
```

### `castBIGINT( expr )`

64-bit integer, useful when converting an ERP `DECIMAL(15,0)` that exceeds `2^31`.

### `castSMALLINT( expr )` / `castTINYINT( expr )`

Small-format integers — `SMALLINT` is 16-bit (-32 768 to 32 767), `TINYINT` is 8-bit (-128 to 127).

### `castDECIMAL( expr , precision , scale )`

```php
use function oihana\openedge\db\helpers\functions\casts\castDECIMAL ;

echo castDECIMAL( 'prix_ht' , 10 , 2 ) ;
// CAST(prix_ht AS DECIMAL(10, 2))
```

### `castFLOAT( expr )` / `castREAL( expr )` / `castDOUBLE_PRECISION( expr )`

Three floating-point precisions. `REAL` is single precision (32-bit), `DOUBLE PRECISION` is double (64-bit), `FLOAT` is configurable.

### `castDATE( expr )`

```php
use function oihana\openedge\db\helpers\functions\casts\castDATE ;

echo castDATE( 'dat_crt_str' ) ;
// CAST(dat_crt_str AS DATE)
```

### `castTIME( expr )` / `castTIMESTAMP( expr )`

```php
use function oihana\openedge\db\helpers\functions\casts\castTIMESTAMP ;

echo castTIMESTAMP( 'horodatage_str' ) ;
// CAST(horodatage_str AS TIMESTAMP)
```

### `castBIT( expr )`

1-bit boolean (0 or 1).

### `castBINARY( expr , length )` / `castVARBINARY( expr , length )` / `castLVARBINARY( expr )`

Binary data. `BINARY` is fixed-length, `VARBINARY` is variable, `LVARBINARY` is long (synonym of `BLOB`).

### `castBLOB( expr )` / `castCLOB( expr )`

`BLOB` is long binary; `CLOB` is long character. See also `defaultLongDataBuffLen` in the DSN ([dsn.md](../dsn.md#defaultlongdatabufflen)).

## OpenEdge type catalog

The [`Type`](../../../src/oihana/openedge/db/enums/Type.php) enum lists the Progress types accepted in a `CAST`:

| Category | Types |
|---|---|
| Integers | `TINYINT`, `SMALLINT`, `INTEGER`, `BIGINT` |
| Decimals | `DECIMAL` (alias `NUMERIC`, `NUMBER`), `REAL`, `FLOAT`, `DOUBLE_PRECISION` |
| Characters | `CHAR`, `VARCHAR`, `LVARCHAR`, `CHAR_VARYING`, `CLOB` |
| Dates and times | `DATE`, `TIME`, `TIMESTAMP`, `TIMESTAMP_WITH_TIME_ZONE` |
| Binaries | `BINARY`, `VARBINARY`, `LVARBINARY`, `BLOB` |
| Bit | `BIT` |
| Arrays | `ARRAY`, `VARARRAY` |
| Special | `NULL` |

`DECIMAL` is the standard ODBC spelling; `NUMERIC` and `NUMBER` are synonyms accepted by Progress, exposed for easier copy-paste from Oracle snippets.

## Typical usage pattern

In a model definition, you cast on the `SQL::COLUMNS` side to normalise:

```php
use oihana\openedge\db\enums\Type ;
use oihana\openedge\enums\OpenEdge as SQL ;

SQL::COLUMNS =>
[
    // ERP stores cd_client as DECIMAL(15,0); we want it as a string on the API side
    [
        SQL::COLUMN => 'cd_client'                ,
        SQL::TABLE  => 'clients'                  ,
        SQL::CAST   => [ Type::VARCHAR , 15 ]     ,
        SQL::ALIAS  => 'id'                       ,
    ],
    // ERP stores prix_ht as DECIMAL(10,4); we truncate to 2 decimals for billing
    [
        SQL::COLUMN => 'prix_ht'                  ,
        SQL::TABLE  => 'produits'                 ,
        SQL::CAST   => [ Type::DECIMAL , [ 10 , 2 ] ] ,
        SQL::ALIAS  => 'price'                    ,
    ],
]
```

> The `SQL::CAST` key accepts three forms: `string` (type name only, no parameter), `[type, length]` (one parameter), `[type, [p, s]]` (precision + scale).

## When `CAST` isn't the right answer

`CAST` throws an error if the value doesn't fit in the target type (e.g. `CAST('abc' AS INTEGER)`). To parse a string while tolerating failure, prefer [`TO_NUMBER`](sql-functions-conversions.md#to_number), [`TO_DATE`](sql-functions-conversions.md#to_date), etc. These functions return `NULL` instead of throwing.

See [Conversions](sql-functions-conversions.md).

## See also

- [Conversions](sql-functions-conversions.md) — `TO_CHAR`, `TO_DATE`, `TO_NUMBER`, `TO_TIME`, `TO_TIMESTAMP`.
- [Helpers](../helpers.md#columnexpression) — `columnExpression()` that integrates `SQL::CAST` at the column level.
- [Progress SQL — CAST](https://docs.progress.com/bundle/openedge-sql-reference/page/CAST.html) — canonical reference.
