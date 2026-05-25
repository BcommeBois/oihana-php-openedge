# `Documents` model

The [`Documents`](../../src/oihana/openedge/models/Documents.php) class is the framework's high-level layer for reads and writes on an OpenEdge table. It inherits from `oihana\models\pdo\PDOModel` and composes 17 traits (13 CRUD traits + 3 cross-cutting traits + 1 Progress-specific trait) to expose a uniform API: `list / get / count / exist / stream / insert / update / upsert / replace / delete / deleteAll / truncate / last`.

## Layered architecture

```
Documents (the final class)
    ├── extends PDOModel (oihana/php-system)
    │     └── knows the PDO, the output schema, the logger
    │
    ├── use AlterBindVarsTrait       (normalised / typed binds at runtime)
    ├── use CacheableTrait           (PSR-16 cache on GET/LIST)
    ├── use EnsureKeysTrait          (force the presence of keys on returns)
    │
    ├── use OpenEdgeQueryBuilderTrait   (initialises $this->openEdge)
    │
    ├── use DocumentsCountTrait      → count()
    ├── use DocumentsListTrait       → list()
    ├── use DocumentsGetTrait        → get()
    ├── use DocumentsLastTrait       → last()
    ├── use DocumentsExistTrait      → exist()
    ├── use DocumentsInsertTrait     → insert()
    ├── use DocumentsUpsertTrait     → upsert()
    ├── use DocumentsUpdateTrait     → update()
    ├── use DocumentsReplaceTrait    → replace()
    ├── use DocumentsDeleteTrait     → delete()
    ├── use DocumentsDeleteAllTrait  → deleteAll()
    ├── use DocumentsTruncateTrait   → truncate()
    ├── use DocumentsStreamTrait     → stream()
    │
    └── use OpenEdgeHelperTrait      → connectTimeout(), serverTimeout(), updateStatistics()
```

Each CRUD trait is **standalone** and **isolated** — you can read one to understand the operation. No CRUD trait depends on another CRUD trait at runtime; all depend on `OpenEdgeQueryBuilderTrait` (which provides `$this->openEdge`), `PDOTrait` (which provides `$this->pdo` and the `fetch` / `fetchAll` methods), and `CacheableTrait` (on read operations).

## Construction

```php
use DI\Container ;
use oihana\models\enums\ModelParam ;
use oihana\openedge\enums\OpenEdge as SQL ;
use oihana\openedge\models\Documents ;

$customers = new Documents( $container ,
[
    ModelParam::PDO           => Databases::ODBC_ERP ,
    ModelParam::SCHEMA        => Customer::class     ,
    ModelParam::CACHE         => Caches::CUSTOMERS , // optional
    ModelParam::ALTERS        => [ /* see alters.md */ ]   , // optional
    ModelParam::QUERY_BUILDER =>
    [
        SQL::COLUMNS  => [ /* … */ ] ,
        SQL::FROM     => 'PUB.customers clients' ,
        SQL::WHERE    => [ /* … */ ] ,
        SQL::ORDER_BY => 'customer_name'                  ,
        SQL::SORTABLE => [ /* whitelist */ ]           ,
    ],
]) ;
```

## Constructor keys

Keys are defined in the [`ModelParam`](https://github.com/BcommeBois/oihana-php-system/blob/main/src/oihana/models/enums/ModelParam.php) enum (from `oihana/php-system`). The main ones:

| Key `ModelParam::*` | Type | Role |
|---|---|---|
| `PDO` | `string \| \PDO` | PDO connection or DI identifier to resolve. **Required**. |
| `SCHEMA` | `string \| Closure \| null` | Schema.org class to hydrate returns into (e.g. `Customer::class`). Optional. |
| `CACHE` | `string \| Cache` | PSR-16 cache or DI identifier. Enables cache on `get` and `list`. |
| `QUERY_BUILDER` | `array` | `OpenEdgeQueryBuilder` configuration (`OpenEdge::*` keys). |
| `ALTERS` | `array` | Transformation map applied post-fetch on document fields. See [alters.md](alters.md). |
| `BIND_VARS` | `array` | Default bind variables, merged with those passed to each call. |
| `THROWABLE` | `bool` | If `true`, propagates PDO exceptions. Default: `false` (logger + null). |

## The 13 CRUD operations

### Read

#### `list(array $init = []): array`

Paginated list. Pagination via `OpenEdge::LIMIT` + `OpenEdge::OFFSET`. Sorting via `OpenEdge::SORT` (public key mapped by `SORTABLE`).

```php
$customers->list([
    SQL::LIMIT  => 50              ,
    SQL::OFFSET => 100             ,
    SQL::SORT   => '-name'         ,        // descending sort on 'name'
    SQL::BINDS  => [ 'country' => 'FR' ] ,  // bind values
]) ;
```

Cache: if `ModelParam::CACHE` is set, `Thing` objects with an `id` are automatically cached by their `id` after the list.

#### `get(array $init = []): mixed`

Fetches a **single** row. The default lookup key is `OpenEdge::ID` (mapped to the primary column in the builder's `WHERE`). Overridable via `OpenEdge::KEY`.

```php
$customer = $customers->get([ SQL::VALUE => 1274 ]) ;
// → fetch on customer_id = 1274 (according to the WHERE declared in the builder)

// With a different key
$customer = $customers->get([
    SQL::KEY   => 'email'   ,
    SQL::VALUE => 'a@b.com' ,
]) ;
```

Cache: cache key computed from the class and binds via `uniqueKey()`. Customisable via `OpenEdge::CACHE_KEY`.

#### `count(array $init = []): int`

Counts rows. Accepts the same filters as `list`:

```php
$count = $customers->count([ SQL::BINDS => [ 'country' => 'FR' ] ]) ;
```

#### `exist(array $init = []): bool`

Checks the existence of a row. Equivalent to `count > 0` but optimised (doesn't fetch the value).

```php
$exists = $customers->exist([ SQL::VALUE => 1274 ]) ;
```

#### `last(array $init = []): mixed`

Fetches the **last** row according to the default sort (`ORDER BY ... DESC LIMIT 1`).

```php
$mostRecent = $customers->last() ;
```

#### `stream(array $init = []): Generator`

Iterates lazily over the result — use for harvests processing millions of rows without loading everything in RAM.

```php
foreach ( $customers->stream([ SQL::LIMIT => 100000 ]) as $row )
{
    handle( $row ) ;
}
```

### Write

> All write operations are available on the model side. They are **not** exposed by the HTTP controller by doctrine (see [introduction.md](introduction.md#a-doctrine-openedge-is-read-only-over-http)) but usable from CLI or migration scripts.

#### `insert(array $document): mixed`

```php
$customers->insert([
    'customer_id'   => 99999       ,
    'customer_name'  => 'NEW CLIENT' ,
    'country_code'     => 'FR'        ,
]) ;
```

#### `update(array $init = []): mixed`

```php
$customers->update([
    SQL::VALUE => 1274                          ,
    'data'     => [ 'customer_name' => 'RENAMED' ] ,
]) ;
```

#### `upsert(array $document): mixed`

Insert or update depending on whether the primary key already exists.

#### `replace(array $document): mixed`

Replaces the row entirely (equivalent to an atomic `DELETE` + `INSERT` on the Progress side).

#### `delete(array $init = []): mixed`

```php
$customers->delete([ SQL::VALUE => 99999 ]) ;
```

#### `deleteAll(array $init = []): mixed`

Bulk delete filtered by `WHERE`.

#### `truncate(): mixed`

Empties the table (equivalent to `TRUNCATE TABLE`).

## Cross-cutting hooks

### `AlterBindVarsTrait`

Lets you **transform bind values** before SQL execution — typically to normalise a type or apply a conversion.

```php
ModelParam::BIND_VARS_ALTERS =>
[
    'country' => fn( $v ) => strtoupper( (string) $v ) , // uppercase values
    'date'    => fn( $v ) => $v?->format( 'Y-m-d' )    , // DateTime → string
]
```

Applied **per method** (`list` / `get` / `count` / etc.): the context is passed to differentiate transformations per operation.

### `CacheableTrait`

Enables PSR-16 cache on `get` and `list`. Four exposed methods: `hasCache`, `getCache`, `setCache`, `clearCache`. Cache invalidation is manual on the developer's side — no automatic invalidation on write.

### `EnsureKeysTrait`

Guarantees that expected keys are present on returned objects. Useful when the output is forced into a Schema.org schema some of whose fields aren't in the `SELECT` — `EnsureKeysTrait` initialises them to `null` after hydration.

```php
ModelParam::ENSURE_KEYS =>
[
    Prop::AREA_SERVED ,
    Prop::WEBSITE     ,
]
```

### `OpenEdgeHelperTrait`

Exposed on the model to give access to three Progress-specific methods:

- `connectTimeout(int $delay)` — sets the client-side timeout for the current connection.
- `serverTimeout(int $delay)` — sets the server-side timeout.
- `updateStatistics(string $table)` — recomputes the statistics of a table.

See [Connection timeouts](progress/timeouts.md).

## The columns / FROM / WHERE externalisation pattern

In practice, you don't write `QUERY_BUILDER` inline in the DI: you externalise it into named PHP functions per entity.

Convention in a typical host application: under `app\definitions\openedge\<entity>\`, you find one file per block:

```
app/definitions/openedge/customers/
├── customerAllColumns.php  ← function returning the column array
├── customerFrom.php        ← function returning the FROM string
├── customerWhere.php       ← function returning the default WHERE
```

```php
use function app\definitions\openedge\customers\customerAllColumns ;
use function app\definitions\openedge\customers\customerFrom       ;
use function app\definitions\openedge\customers\customerWhere      ;

new Documents( $container ,
[
    ModelParam::PDO    => Databases::ODBC_ERP ,
    ModelParam::SCHEMA => Customer::class     ,
    ModelParam::QUERY_BUILDER =>
    [
        SQL::COLUMNS  => customerAllColumns() ,
        SQL::FROM     => customerFrom()       ,
        SQL::WHERE    => customerWhere()      ,
        SQL::ORDER_BY => 'name'               ,
        SQL::SORTABLE => [ /* … */ ]          ,
    ],
])
```

Benefits:

- Reuse across models (the "normal Customer" model and the "harvest Customer" model share their `FROM` and `WHERE`).
- DI readability (the definition fits in ten lines).
- Unit tests possible on column-generation functions — without instantiating a PDO model.
- Easy overriding via parameters: `customerAllColumns( extraColumns: [...] )` adds harvest-specific columns.

## See also

- [`OpenEdgeQueryBuilder`](query-builder.md) — detail of the underlying builder and its 9 traits.
- [Alters and denormalisation](alters.md) — post-fetch transformation system.
- [`Harvest` models](harvest.md) — source model pattern for synchronisation.
- [Slim controllers](controllers.md) — how to expose a model as a read-only HTTP route.
- [Enums reference](enums.md) — catalog of `OpenEdge::*` and `ModelParam::*` keys.
