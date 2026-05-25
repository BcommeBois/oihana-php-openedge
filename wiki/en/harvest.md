# `Harvest` models

A **`Harvest` model** is a [`Documents`](models.md) model with a specific purpose: **massive reads** of an OpenEdge table for synchronisation to a target system (cache, document database, export file). It's a pattern, not a standalone class — you instantiate a normal `Documents` model but with a configuration optimised for harvesting.

This page documents the pattern on the **OpenEdge source model** side. The CLI command that consumes this model (`HarvestDocumentsCommand`) lives in another package (`oihana/arango`) — the link will become cross-package once this library is extracted.

## Differences between an "API" model and a "Harvest" model

| Aspect | API model (HTTP read) | Harvest model (CLI sync) |
|---|---|---|
| Projected columns | Full catalog for display | Minimal subset — only what the target needs |
| `Alters` | Rich — `URL`, cross-database `GET`, `CALL`, Schema.org wrapping | Sparse — only type casts (`INT`, `FLOAT`) |
| DSN `arraySize` | Moderate (~200) | High (~1000-5000) |
| `queryTimeout` | Short (~300 s) | Long or `-1` (no timeout) |
| Cache | Enabled (PSR-16) | Disabled — streaming, no cache |
| Called method | `list()` or `get()` | `stream()` |
| `LIMIT` / `OFFSET` | User pagination | Often absent — read everything |
| Locking hint | `WITH (NOLOCK)` recommended | `WITH (NOLOCK)` almost mandatory |

## Defining a Harvest model

Convention in a typical host application: one `harvest.php` file per entity, alongside the main file. Example `customers/harvest.php`:

```php
use DI\Container ;

use app\enums\Caches    ;
use app\enums\Databases ;
use app\enums\Models    ;
use app\enums\Prop      ;
use app\schema\Customer ;

use oihana\models\enums\Alter      ;
use oihana\models\enums\ModelParam ;
use oihana\openedge\enums\OpenEdge as SQL ;
use oihana\openedge\models\Documents      ;

use function app\definitions\openedge\customers\customerAllColumns ;
use function app\definitions\openedge\customers\customerFrom       ;

return
[
    Models::CUSTOMERS_HARVEST => fn( Container $container ) => new Documents
    (
        $container ,
        [
            ModelParam::PDO           => Databases::ODBC_ERP ,
            ModelParam::SCHEMA        => Customer::class     ,
            ModelParam::CACHE         => Caches::CUSTOMERS ,
            ModelParam::ALTERS =>
            [
                // Minimal alters: only type casts
                Prop::AREA_SERVED        => Alter::INT ,
                Prop::PRICE_SEGMENTATION => Alter::INT ,
            ],
            ModelParam::QUERY_BUILDER =>
            [
                // Same columns as the "API" model + an injected Schema.org "type" field
                SQL::COLUMNS => customerAllColumns( extraColumns:
                [
                    Prop::ADDITIONAL_TYPE => [ SQL::VALUE => Customer::getSchemaType() ] ,
                ]) ,
                SQL::FROM    => customerFrom() ,
                // No WHERE → read everything
                // No ORDER BY → stream
            ]
        ]
    ) ,
] ;
```

## Key differences line by line

### No `WHERE` or `ORDER_BY`

On a harvest, you read **everything**. A `WHERE` would filter, an `ORDER_BY` would slow the query (Progress must sort in memory before streaming). Let Progress return rows in natural order.

### No `SORTABLE`

The harvest model isn't exposed over HTTP — no need for a public sort whitelist.

### Minimal `Alters`

No `Alter::GET` (cross-database lookup) on a harvest: denormalisation happens **on the target side** (ArangoDB receiving the data) with the appropriate arango models. Keep the source model fast and independent.

### `extraColumns` for the Schema.org type

The host application pushes Schema.org documents to the target. So you add a `@type` column on the SELECT side, computed from the Schema.org class: `Customer::getSchemaType()` returns `'Customer'`. It's the only useful `extraColumns` on most harvests.

### `ModelParam::CACHE` is often reused from the API model

Surprising at first sight: a streaming harvest doesn't use the cache. But sharing it with the API model has a useful side effect: at the end of the harvest, the API model's cache can be invalidated (`$model->clearCache()`) to force the next HTTP reads to hit the updated database.

## Usage on the CLI side

The command consuming a harvest model typically extends `HarvestDocumentsCommand` (which lives in `oihana/arango`, see that package's documentation). Pseudo-code:

```php
class HarvestCustomersCommand extends HarvestDocumentsCommand
{
    protected function execute( InputInterface $input , OutputInterface $output ) : int
    {
        $source = $this->container->get( Models::CUSTOMERS_HARVEST ) ;
        $target = $this->container->get( Models::CUSTOMERS )                  ; // Arango model

        foreach ( $source->stream() as $customer )
        {
            $target->upsert( $customer->toArray() ) ;
        }

        return Command::SUCCESS ;
    }
}
```

The flow: `stream()` on the OpenEdge side → upsert row-by-row on the target. On large volumes, buffer and commit per batch (1000 rows per Arango transaction).

## Recommended configurations

### For a nightly harvest with no duration constraint

```toml
[odbc]
queryTimeout = -1   # no timeout
arraySize    = 5000 # heavy fetch on the driver side
```

```php
ModelParam::QUERY_BUILDER =>
[
    SQL::LOCKING_HINT => LockingHint::WITH_NOLOCK , // never block production
    // no ORDER_BY
    // no WHERE
]
```

### For an incremental (delta sync) harvest

```php
ModelParam::QUERY_BUILDER =>
[
    SQL::LOCKING_HINT => LockingHint::WITH_NOLOCK ,
    SQL::WHERE        =>
    [
        SQL::COLUMN   => 'updated_at'    ,
        SQL::OPERATOR => '>='         ,
        SQL::BIND     => 'since'      ,
    ],
]
```

On the command side, pass the date of the last sync:

```php
$source->stream([ SQL::BINDS => [ 'since' => $lastSync ] ]) ;
```

## Pitfalls

### 1. `arraySize` too low

On a harvest fetching a million rows, an `arraySize = 1` (driver default) makes a million network round-trips. Switching to `1000` or `5000` divides total time by the same factor.

### 2. Missing `WITH (NOLOCK)`

On a harvest running in parallel with production, a lock taken by a long ABL transaction can block the read for minutes — or fail the harvest. `WITH (NOLOCK)` is almost mandatory.

### 3. Cross-database `Alter::GET` on a harvest

It works, but it's slow: for each row, a lookup to the target is triggered. On a million rows, that's a deal-breaker. **Always denormalise on the target side**, not on the source.

### 4. `ORDER_BY` on a streaming harvest

Progress must build the entire result set in memory before sorting. On a ten-million-row table, that's several gigabytes server-side. Always leave the natural order.

### 5. Full PSR-16 cache

If the harvest model shares its cache with the API model and you harvest without configuring an eviction strategy (TTL or LRU), the Memcached cache can saturate after the harvest. Either disable the cache on the harvest side (`ModelParam::CACHEABLE => false`), or configure eviction.

## See also

- [`Documents` model](models.md) — common base for the API model and the Harvest model.
- [Alters and denormalisation](alters.md) — why we keep them minimal on the harvest side.
- [ODBC DSN in detail](dsn.md#arraysize) — how to tune `arraySize` for a harvest.
- [Locking hints](progress/locking-hints.md) — choosing a hint for a harvest.
- [Tips and pitfalls](tips.md) — cross-cutting golden rules.
