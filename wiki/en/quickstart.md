# OpenEdge quickstart

This page walks through the three minimal steps to query a Progress OpenEdge database from PHP with `oihana/openedge`:

1. Build a clean ODBC PDO with the right attributes.
2. Run a read-only SQL query manually to validate the connection.
3. Switch to a high-level `Documents` model for `list` / `get` / `count`.

The next steps (multi-database, Slim integration, harvest) are covered in the dedicated pages.

## Step 1 — Build a PDO

The [`OpenEdgePDOBuilder`](../../src/oihana/openedge/db/OpenEdgePDOBuilder.php) class is an invokable factory that assembles an ODBC DSN then instantiates a `PDO` with Progress-friendly attributes.

```php
use oihana\openedge\db\OpenEdgePDOBuilder ;

$pdo = ( new OpenEdgePDOBuilder
([
    // Block shared across all databases on the server
    'scheme'   => 'odbc'                        ,
    'driver'   => '/usr/dlc/odbc/lib/pgoe27.so' ,
    'hostName' => 'erp.example.com'             ,
    'charSet'  => 106                           , // IANAAppCodePage 106 = UTF-8
    'queryTimeout' => 300                       ,
    'logonID'  => 'reader'                      ,
    'password' => 'secret'                      ,
    // Block specific to the target database
    'database'   => 'erp_database' ,
    'portNumber' => 20931      ,
]) )() ;
```

Three things happen in that line:

1. **DSN construction.** `new OpenEdgePDOBuilder([...])` internally builds an [`OpenEdgeDSN`](dsn.md) that maps camelCase keys (`hostName`, `arraySize`, `charSet`, …) to the syntax the Progress driver expects (`HostName=…;ArraySize=…;IANAAppCodePage=…`).
2. **PDO construction.** Invoking `()` (the factory is callable) triggers `__invoke()`, which calls `new PDO( $dsn , $logonID , $password )`.
3. **PDO attribute setup.** Six attributes are set so you don't have to think about them:

   | Attribute | Value | Reason |
   |---|---|---|
   | `PDO::ATTR_DEFAULT_FETCH_MODE` | `PDO::FETCH_ASSOC` | Associative array instead of numeric + assoc duplicate. |
   | `PDO::ATTR_ERRMODE` | `PDO::ERRMODE_EXCEPTION` | Every SQL error throws a `PDOException`. |
   | `PDO::ATTR_CURSOR` | `PDO::CURSOR_FWDONLY` | One-way cursor, the fast mode for streaming. |
   | `PDO::ATTR_PERSISTENT` | `true` | Persistent connection between requests (important on Progress, connection cost is non-trivial). |
   | `PDO::ATTR_EMULATE_PREPARES` | `false` | Real server-side `PREPARE`, no client emulation that breaks types. |
   | `PDO::ATTR_STRINGIFY_FETCHES` | `false` | Numeric columns come back as `int` / `float`, not as `string`. |

If one of those attributes doesn't fit your use case, override it after the fact: `$pdo->setAttribute(...)`.

## Step 2 — First query in raw SQL

At this point you have a standard `PDO`. Anything you'd do with PDO works:

```php
$stmt = $pdo->prepare( <<<SQL
    SELECT customer_id , customer_name , created_at
    FROM   PUB.customers
    WHERE  country_code = :country
    ORDER  BY customer_name
    SQL
) ;

$stmt->execute([ 'country' => 'FR' ]) ;

while ( $row = $stmt->fetch() )
{
    echo $row[ 'customer_id' ] . ' — ' . $row[ 'customer_name' ] . PHP_EOL ;
}
```

That's the lowest layer. It works, but it leaves several things to the developer: dynamically composing the `SELECT`, adding optional conditions in `WHERE`, handling pagination, projecting to `array<Customer>` instead of `array<array>`, caching, etc. That's precisely what the model layer automates.

## Step 3 — High-level `Documents` model

The [`Documents`](models.md) model wraps a `PDO`, an `OpenEdgeQueryBuilder`, an optional output schema, and an optional cache. At the constructor you pass a DI container and an options array.

```php
use DI\Container                          ;
use oihana\models\enums\ModelParam        ;
use oihana\openedge\enums\OpenEdge as SQL ;
use oihana\openedge\models\Documents      ;

$container = /* PHP-DI or another PSR-11 */ ;

$customers = new Documents( $container ,
[
    ModelParam::PDO    => $pdo                     ,
    ModelParam::SCHEMA => Customer::class          , // optional hydration
    ModelParam::QUERY_BUILDER =>
    [
        SQL::COLUMNS  => [ 'customer_id' , 'customer_name' , 'created_at' ] ,
        SQL::FROM     => 'PUB.customers' ,
        SQL::WHERE    => [ /* see sql/sql-clauses.md */ ] ,
        SQL::ORDER_BY => 'customer_name' ,
        SQL::SORTABLE => // whitelist allowed for HTTP sorting
        [
            'id'   => 'customer_id'   ,
            'name' => 'customer_name'  ,
            'created' => 'created_at'  ,
        ],
    ],
]) ;
```

From there, the 13 CRUD traits of the model are usable:

```php
// Paginated list + sorting
$list = $customers->list
([
    SQL::LIMIT    => 50          ,
    SQL::OFFSET   => 0           ,
    SQL::ORDER_BY => 'customer_name' ,
]) ;

// Fetch by key
$one = $customers->get([ 'customer_id' => 1274 ]) ;

// Count
$total = $customers->count() ;

// Existence check
$exists = $customers->exist([ 'customer_id' => 1274 ]) ;

// Lazy stream for large volumes (harvest)
foreach ( $customers->stream([ SQL::LIMIT => 10000 ]) as $row )
{
    handle( $row ) ;
}
```

The model also exposes `insert`, `update`, `upsert`, `replace`, `delete`, `deleteAll`, `truncate`, `last` — usable from CLI or migration scripts. See [models.md](models.md) for the full catalog.

## Step 4 — DI container (in production)

In production, you almost never instantiate `OpenEdgePDOBuilder` directly: it's registered as a service in the container, parametrised from the TOML configuration. In a typical host application's convention, **one definition file per database** under `definitions/odbc/`.

```php
// definitions/odbc/erp.php
use app\enums\Databases ;
use app\enums\DBConfig  ;
use app\enums\Definition ;
use oihana\openedge\db\OpenEdgePDOBuilder ;
use Psr\Container\ContainerInterface ;

return
[
    Databases::ODBC_ERP => fn( ContainerInterface $container ) => new OpenEdgePDOBuilder
    ([
        // Shared [odbc] block
        ...$container->get( Definition::CONFIG )[ DBConfig::ODBC ] ?? [] ,
        // Database-specific [databases.erp] block
        ...$container->get( Definition::CONFIG )[ DBConfig::DATABASES ][ DBConfig::ERP ] ?? [] ,
    ])() ,
] ;
```

On the consumer side, the model receives the service identifier (not the PDO instance):

```php
new Documents( $container ,
[
    ModelParam::PDO => Databases::ODBC_ERP , // DI identifier, resolved by the model
    // ...
]) ;
```

The model itself resolves `$container->get(Databases::ODBC_ERP)` on first access, which gives **lazy PDO**: as long as no model method is called, no ODBC connection is opened. Useful when a container declares dozens of models but only a few are touched per HTTP request.

See [ODBC connection and multi-database](connection.md) for the full multi-database pattern and TOML configuration.

## What's next

- [ODBC connection and multi-database](connection.md) — TOML configuration, per-database factory, connection troubleshooting.
- [ODBC DSN in detail](dsn.md) — config → DSN mapping, PDO attributes, special values of `queryTimeout`.
- [Building a SQL query step by step](sql/sql-building-queries.md) — assembling a complete SELECT with helpers.
- [`Documents` model](models.md) — full catalog of `OpenEdge::*` keys and the 13 CRUD traits.
- [Slim controllers](controllers.md) — exposing the model as a read-only HTTP route.
