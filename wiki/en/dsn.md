# ODBC DSN in detail

The [`OpenEdgeDSN`](../../src/oihana/openedge/db/OpenEdgeDSN.php) class assembles a Progress ODBC connection string from a configuration array. It has no business logic — it translates an idiomatic PHP configuration (camelCase keys) to the syntax the Progress driver expects (PascalCase keys). This page documents that mapping and the special values to know.

## Why this class exists

The Progress SQL ODBC driver expects a DSN like:

```
odbc:Driver=/usr/dlc/odbc/lib/pgoe27.so;HostName=erp.example.com;PortNumber=20931;Database=gcow0501;IANAAppCodePage=106;ArraySize=200;QueryTimeout=300
```

Three drawbacks to manipulating this string directly:

1. Casing matters on the ODBC side (`HostName`, not `hostname`), but is anti-idiomatic on the PHP side (where you'd prefer `hostName`).
2. The Progress parameter names (`IANAAppCodePage`, `DefaultLongDataBuffLen`) aren't memorable.
3. Building the string via concatenation amounts to `sprintf` and loses typing.

`OpenEdgeDSN` translates camelCase input keys to the Progress DSN syntax and produces a valid `string` via `__toString()`.

## The mapping at a glance

| Input key (camelCase) | Progress DSN key | PHP type | Description |
|---|---|---|---|
| `scheme` | *(prefix `:` before the DSN)* | `string` | PDO prefix. **Always `'odbc'`** for Progress. |
| `driver` | `Driver` | `string` | Absolute path to the driver binary (`pgoe27.so` on Linux). |
| `hostName` | `HostName` | `string` | DNS name or IP of the Progress server. |
| `portNumber` | `PortNumber` | `string\|int` | Port the SQL broker listens on for this database. Differs per database. |
| `database` | `Database` | `string` | Progress database name (typically `gcow0501`, not a path). |
| `charSet` | `IANAAppCodePage` | `int` | IANA codepage for client-side string conversions. **Always `106` (UTF-8)** unless special case. |
| `arraySize` | `ArraySize` | `?int` | Number of rows fetched per server round-trip. Driver default = 1. Recommended for bulk reads: `200` to `5000`. |
| `defaultLongDataBuffLen` | `DefaultLongDataBuffLen` | `?int` | Buffer size (in multiples of 1024) for long columns (`CLOB`, `BLOB`, `LVARBINARY`). Driver default = 1024. |
| `queryTimeout` | `QueryTimeout` | `?int` | Per-query timeout in seconds. Three special values: `-1`, `0`, `> 0`. See below. |

Input keys are defined as `OpenEdgeDSN::CONFIG_*` constants (for example `OpenEdgeDSN::CONFIG_HOST_NAME = 'hostName'`); DSN keys as `OpenEdgeDSN::*` constants (for example `OpenEdgeDSN::HOST_NAME = 'HostName'`). In practice, you almost never manipulate these directly — `OpenEdgePDOBuilder` passes them to `OpenEdgeDSN` which produces the final string.

## Special values

### `charSet`

The Progress driver uses IANA codepages, not PHP names. Recommended values:

| `charSet` | Codepage | When |
|---|---|---|
| `106` | UTF-8 | **Default, almost always**. |
| `4` | ISO-8859-1 (Latin-1) | Very old database stored in Latin-1 without server conversion. |

If you see broken accented characters in responses (e.g. `é` instead of `é`), it's a `charSet` issue: the database probably stores in UTF-8 but the client asks for Latin-1, or vice versa.

### `queryTimeout`

Three special values documented by Progress:

| Value | Behaviour |
|---|---|
| `-1` | No timeout. The driver also ignores the `SQL_ATTR_QUERY_TIMEOUT` ODBC attribute. Recommended for long CLI harvests. |
| `0` | No default timeout, but the driver respects an `SQL_ATTR_QUERY_TIMEOUT` set through another channel. |
| `x > 0` | All queries time out after `x` seconds. Recommended for HTTP exposure (e.g. `300` to cap ad-hoc queries at 5 min). |

### `arraySize`

The most sensitive performance parameter.

- A value too low multiplies network round-trips. On a list returning 10 000 rows with `arraySize = 1`, you make 10 000 round-trips.
- A value too high allocates too much client RAM: `arraySize × row_width × N_connections` can exceed several gigabytes.

Recommended settings:

| Use case | `arraySize` |
|---|---|
| HTTP catalog read (limit 50–200) | `200` |
| Bulk CLI harvest (millions of rows) | `1000` to `5000` |
| Document-by-document streaming | `100` to `200` (PHP-DI reuses the persistent connection) |

### `defaultLongDataBuffLen`

Only relevant if you project `CLOB`, `BLOB`, or `LVARBINARY` columns (e.g. a "long description" field or a PDF stored in the database). The parameter is in **multiples of 1024** (so `defaultLongDataBuffLen = 64` = 64 KB per field).

If a `CLOB` column comes back truncated, this is the parameter to bump — not a PHP-side PDO attribute.

## Reconstructing the DSN

To debug a DSN, you can simply cast `OpenEdgeDSN` to string:

```php
use oihana\openedge\db\OpenEdgeDSN ;

$dsn = new OpenEdgeDSN
([
    'scheme'   => 'odbc'                        ,
    'driver'   => '/usr/dlc/odbc/lib/pgoe27.so' ,
    'hostName' => 'erp.example.com'             ,
    'portNumber' => 20931                       ,
    'database' => 'gcow0501'                    ,
    'charSet'  => 106                           ,
    'arraySize' => 200                          ,
    'queryTimeout' => 300                       ,
]) ;

echo (string) $dsn ;
// odbc:Driver=/usr/dlc/odbc/lib/pgoe27.so;HostName=erp.example.com;PortNumber=20931;Database=gcow0501;IANAAppCodePage=106;ArraySize=200;QueryTimeout=300
```

The parameter order in the string is fixed by `__toString()`:

1. `Driver`
2. `HostName`
3. `PortNumber`
4. `Database`
5. `IANAAppCodePage`
6. `ArraySize`
7. `DefaultLongDataBuffLen`
8. `QueryTimeout`

`scheme` is prefixed with a `:` (`odbc:Driver=…`).

## PDO attributes set by `OpenEdgePDOBuilder`

Once the DSN is built, [`OpenEdgePDOBuilder::__invoke()`](../../src/oihana/openedge/db/OpenEdgePDOBuilder.php) creates the PDO and sets six attributes:

```php
$pdo = new PDO( (string) $this->dsn , $this->logonID , $this->password ) ;

$pdo->setAttribute( PDO::ATTR_DEFAULT_FETCH_MODE , PDO::FETCH_ASSOC ) ;
$pdo->setAttribute( PDO::ATTR_ERRMODE            , PDO::ERRMODE_EXCEPTION ) ;
$pdo->setAttribute( PDO::ATTR_CURSOR             , PDO::CURSOR_FWDONLY ) ;
$pdo->setAttribute( PDO::ATTR_PERSISTENT         , true ) ;
$pdo->setAttribute( PDO::ATTR_EMULATE_PREPARES   , false ) ;
$pdo->setAttribute( PDO::ATTR_STRINGIFY_FETCHES  , false ) ;
```

Details in the [Quickstart](quickstart.md#step-1--build-a-pdo).

## What to do on connection failure

If `OpenEdgePDOBuilder::__invoke()` throws a `PDOException`, the recommended order:

1. **Driver present?** `ls -la <driver>`. Often a wrong path or missing permissions for the PHP user (typically `www-data`).
2. **Network?** `nc -vz <hostName> <portNumber>` to check the SQL broker is listening.
3. **Credentials?** Test through `isql` (see [connection.md](connection.md#verify-a-connection-works-without-php)).
4. **Database exists?** Database name is case-sensitive on the Progress side.
5. **`ext-odbc` loaded?** `php -m | grep -i odbc`. Must display `odbc` and `PDO_ODBC`.

## See also

- [OpenEdge quickstart](quickstart.md) — full config → PDO assembly.
- [ODBC connection and multi-database](connection.md) — TOML-per-database config pattern.
- [Tips and pitfalls](tips.md) — local test limitation (Progress driver unavailable on Mac).
- [Official Progress — Connection parameters](https://docs.progress.com/bundle/openedge-data-management-sql-development/page/Connection-parameters-keywords.html) — canonical DSN reference.
