# ODBC connection and multi-database

This page describes how to configure one (or several) Progress ODBC connection from a TOML file, register it as a service in a PHP-DI container, and consume it from models. It's the recommended pattern when the host application addresses several Progress databases hosted on the same server (for example an ERP database and an analytics database).

## Vocabulary

Before tackling the config, two words to clarify:

- **Shared block** — the ODBC parameters that don't change across databases on the same server: `driver`, `hostName`, `charSet`, `queryTimeout`, `logonID`, `password`. Stored in the `[odbc]` config section.
- **Database block** — the parameters that change per database: `database` (Progress database name) and `portNumber` (port the SQL broker listens on). Stored in `[databases.<name>]`.

The final DSN is the merge of both blocks. This split simplifies credential rotation and the declaration of new databases.

## TOML configuration

A typical multi-database configuration — a shared `[odbc]` block, then one `[databases.<name>]` section per Progress database:

```toml
[odbc]
scheme       = "odbc"
driver       = "/usr/dlc/odbc/lib/pgoe27.so"
hostName     = "erp.example.com"
charSet      = 106         # IANAAppCodePage 106 = UTF-8
queryTimeout = 300         # seconds; -1 for no timeout
logonID      = "reader"
password     = "secret"

[databases.erp]
database   = "erp_database"
portNumber = 20931

[databases.analytics]
database   = "analytics_database"
portNumber = 20932
```

Both databases share the same `[odbc]` block (same host, same credentials, same driver) and only differ on `database` and `portNumber`. To add a third database, append another `[databases.<name>]` section.

> **Security tip.** `logonID` and `password` are secrets: don't commit the real `config.toml`, keep a `config.example.toml` with placeholders, and resolve sensitive values through a vault (env vars, `secrets/` file, Vault, etc.) at boot.

## PDO service per database

Recommended convention: each database has its own DI definition file under `definitions/odbc/<database>.php`. All these files share the same structure:

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
        ...$container->get( Definition::CONFIG )[ DBConfig::ODBC ] ?? [] ,
        ...$container->get( Definition::CONFIG )[ DBConfig::DATABASES ][ DBConfig::ERP ] ?? [] ,
    ])() ,
] ;
```

Three things to notice:

1. **Spread operator to merge the blocks.** `[ ...$shared , ...$database ]` produces a single flat array consumed by `OpenEdgePDOBuilder`. The database block's keys override the shared block's keys, which allows you to override a parameter for a single database (e.g. a specific `queryTimeout` for the analytics database).
2. **`OpenEdgePDOBuilder` is invokable.** The trailing `()` calls `__invoke()`, which returns the PDO instance. The registered service is thus the PDO directly, not the factory.
3. **The service is lazy by default.** Until a model asks for the ERP database, the closure isn't invoked and no ODBC connection is opened. Important when you declare several databases but a given HTTP request only touches one.

### Why one file per database

You could declare everything in one file. Splitting per file eases:

- readability (one file ≈ one connection);
- removing a no-longer-needed database (an `unlink`);
- code-reviewing a new database (a one-file commit);
- keeping naming conventions decoupled (`Databases::ODBC_<X>`) per database.

It's purely organisational — the DI container merges everything at boot.

## Consuming from a model

On the model side, you don't receive the PDO instance directly: you receive the service identifier, and the model takes care of lazy resolution.

```php
use oihana\models\enums\ModelParam        ;
use oihana\openedge\enums\OpenEdge as SQL ;
use oihana\openedge\models\Documents      ;

new Documents( $container ,
[
    ModelParam::PDO    => Databases::ODBC_ERP , // ← DI identifier, not the PDO instance
    ModelParam::SCHEMA => Customer::class     ,
    ModelParam::QUERY_BUILDER =>
    [
        SQL::FROM    => 'PUB.customers' ,
        SQL::COLUMNS => [ 'customer_id' , 'customer_name' ] ,
    ],
]) ;
```

The `Documents` model accepts `ModelParam::PDO` in two forms:

| Form | Behaviour |
|---|---|
| `string` (DI identifier) | Resolved through `$container->get($id)` on first access. **Recommended form**. |
| `PDO` (instance) | Used as-is. Useful in unit tests with a SQLite mock for example. |

## Multi-database in a single request

A model is attached to **one** PDO. If an HTTP request needs to cross two databases, you instantiate two models (one per database) and aggregate either at the controller or the business service layer.

```php
$customers = new Documents( $container , [
    ModelParam::PDO    => Databases::ODBC_ERP       ,
    ModelParam::SCHEMA => Customer::class           ,
    ModelParam::QUERY_BUILDER => [ /* ... */ ]      ,
]) ;

$reports = new Documents( $container , [
    ModelParam::PDO    => Databases::ODBC_ANALYTICS ,
    ModelParam::SCHEMA => Report::class             ,
    ModelParam::QUERY_BUILDER => [ /* ... */ ]      ,
]) ;
```

OpenEdge does not support cross-database SQL joins through the ODBC driver — it's a platform limitation, not a framework one.

## Verify a connection works (without PHP)

Before suspecting PHP code, it's useful to prove the DSN and credentials are good through `unixODBC`:

```bash
# The driver must exist
ls /usr/dlc/odbc/lib/pgoe27.so

# Connection test through isql (unixodbc-bin package)
echo "SELECT TOP 1 customer_id FROM PUB.customers" | \
isql -v "DRIVER=/usr/dlc/odbc/lib/pgoe27.so;HostName=erp.example.com;PortNumber=20931;Database=erp_database;IANAAppCodePage=106" \
     "reader" "secret"
```

If `isql` connects but PHP doesn't, the problem is PHP (missing `ext-odbc` extension, mis-set PDO attribute). If `isql` doesn't connect, it's infrastructure (driver, network, credentials).

## Common errors

| Symptom | Likely cause |
|---|---|
| `SQLSTATE[IM002] Data source name not found and no default driver specified` | The `/usr/dlc/odbc/lib/pgoe27.so` path is wrong or the binary isn't accessible to the PHP user. |
| Misdecoded Latin characters (é → é) | `charSet` misconfigured. Force `charSet = 106` (UTF-8). |
| Timeout at 60 s on a query that should take 90 s | `queryTimeout` too low. Increase or switch to `-1`. |
| All queries take ~500 ms extra | `PDO::ATTR_PERSISTENT` accidentally disabled, or Apache restarting pre-fork. Check the attribute. |
| `INTEGER` columns come back as PHP `string` | `PDO::ATTR_STRINGIFY_FETCHES` enabled. Make sure the factory hasn't been tampered with. |

## See also

- [ODBC DSN in detail](dsn.md) — config → DSN mapping, special values.
- [OpenEdge quickstart](quickstart.md) — first working example.
- [`Documents` model](models.md) — how the model resolves `ModelParam::PDO`.
- [Tips and pitfalls](tips.md) — local test constraint (driver unavailable on Mac).
