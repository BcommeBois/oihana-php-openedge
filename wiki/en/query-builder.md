# `OpenEdgeQueryBuilder`

The [`OpenEdgeQueryBuilder`](../../src/oihana/openedge/db/OpenEdgeQueryBuilder.php) class is the SQL builder backing the [`Documents`](models.md) model. It composes 9 clause traits to produce a query's fragments (`SELECT`, `FROM`, `WHERE`, …) from a typed configuration.

In practice, you almost never instantiate this builder directly — the model takes care of that from the `ModelParam::QUERY_BUILDER` key. This page documents the builder for two audiences:

- **Maintenance** — understand how the model composes its SQL, know where to look when debugging a malformed query.
- **Advanced usage** — instantiate a builder outside a model to produce SQL outside model context (e.g. for an ad-hoc export, a CLI script without a model, or to pre-compile a subquery to pass via `OpenEdge::QUERY`).

## Composition

```
OpenEdgeQueryBuilder
    ├── use BindTrait             ($bindVars + bind() + alterBindVars)
    ├── use ColumnTrait           (columns + columnList())
    ├── use FromTrait             ($from + from())
    ├── use GroupByTrait          ($groupBy + groupBy())
    ├── use OrderByTrait          ($orderBy + $sortable + orderBy())
    ├── use WhereTrait            ($where + where())
    ├── use LookingHintTrait      ($lockingHint + withLockingHint())
    ├── use LoggerTrait           (PSR-3 logger)
    └── use ToStringTrait         (Stringable compatibility)
```

The constructor initialises all properties from an `OpenEdge::*` keys array:

```php
use oihana\openedge\db\OpenEdgeQueryBuilder ;
use oihana\openedge\enums\OpenEdge as SQL  ;

$builder = new OpenEdgeQueryBuilder
([
    SQL::CONTAINER    => $container        ,
    SQL::COUNTER      => '*'               , // for count(*)
    SQL::COLUMNS      => [ /* … */ ]       ,
    SQL::DISTINCT     => false             ,
    SQL::FROM         => 'PUB.clients clients' ,
    SQL::GROUP_BY     => null              ,
    SQL::JOINS        => null              ,
    SQL::LOCKING_HINT => 'WITH (NOLOCK)'   ,
    SQL::NO_REORDER   => false             ,
    SQL::ORDER_BY     => 'nom_client'      ,
    SQL::QUERY        => null              , // if set, short-circuits everything else
    SQL::SORTABLE     => [ /* whitelist */ ] ,
    SQL::WHERE        => [ /* … */ ]       ,
]) ;
```

## Public properties

Properties are public (inheritance of the framework's "fluent PHP" style) — you can therefore modify them after the constructor.

| Property | Type | Role |
|---|---|---|
| `$container` | `?Container` | DI container. Used by some traits to resolve services. |
| `$counter` | `string` | Argument of `COUNT()`. Default `'*'`. |
| `$columns` | `array` | `SELECT` columns. |
| `$distinct` | `bool` | If `true`, adds `DISTINCT` after `SELECT`. |
| `$from` | `?string` | `FROM ... [JOIN ...]` string. |
| `$groupBy` | `?string\|array` | Group column(s). |
| `$joins` | `?array` | Join definitions (rarely used — prefer `$from` as a string). |
| `$lockingHint` | `?string` | Table-level locking hint. |
| `$noReorder` | `bool` | If `true`, adds `{ NO REORDER }` to `FROM` to disable join optimisation. |
| `$orderBy` | `?string\|array` | Default sort. |
| `$query` | `?string` | If non-null, short-circuits all assembly: the query is used as-is. Convenient for complex hand-written queries. |
| `$sortable` | `?array` | Whitelist of fields allowed for public sort (public key → Progress name). |
| `$where` | `?string\|array` | Default conditions. |

## Public methods

### `select( array|string|null $init = null ): string`

Builds the `SELECT [DISTINCT] [TOP n]` clause.

```php
echo $builder->select() ;
// SELECT

echo $builder->select([ SQL::DISTINCT => true ]) ;
// SELECT DISTINCT

echo $builder->select([ SQL::TOP => 50 ]) ;
// SELECT TOP 50
```

Passing a `string` returns it as-is — a short-circuit to pass a custom fragment.

### `count( array $init = [] ): string`

Builds the `COUNT(...)` clause. The argument is read from `SQL::COUNTER` or the `$this->counter` property.

```php
echo $builder->count() ;
// COUNT(*)

echo $builder->count([ SQL::COUNTER => 'cd_pays' ]) ;
// COUNT(cd_pays)
```

### `columnList( array $init = [] ): string`

Builds the `SELECT` column list. Delegates to [`columnExpression()`](helpers.md#columnexpression) and [`asAlias()`](helpers.md#asalias) per column.

### `from( array $init = [] ): string`

Builds the `FROM` clause. Returns `'FROM ' . $this->from` (with any interpolation).

### `where( array $init , array &$bindVars , string $context ): string`

Builds the `WHERE` clause. Modifies `$bindVars` by reference to collect bind values encountered.

### `groupBy( array $init = [] ): string`

Builds the `GROUP BY` clause. Also handles `HAVING` if present.

### `orderBy( array $init = [] ): string`

Builds the `ORDER BY` clause. If `OpenEdge::SORT` is passed in `$init`, parses `orderByExpression()` against `$this->sortable`. Otherwise falls back to default `$this->orderBy`.

### `withLockingHint( array $init = [] ): string`

Builds the locking hint to stick after `FROM`. See [Locking hints](progress/locking-hints.md).

### `bind( array $init = [] ): array`

Returns the bind variables array collected by other clauses. Used internally by the model.

### `toString(): string`

Compiles the whole thing into a single SQL string. Inherited from `ToStringTrait`. Very useful for debug: `echo (string) $builder ;`.

## Short-circuit via `SQL::QUERY`

When the `$query` property (or `SQL::QUERY` at the constructor) is non-null, **all assembly is short-circuited**: the query is used as-is. Useful in two cases:

1. **Hand-written complex query** — for instance an advanced Progress `WITH ... AS (...) SELECT ... FROM ...` that traits don't model.
2. **Query generated by an external subquery** — for instance the content of an `EXISTS ( ... )` built by another builder then injected.

```php
$builder = new OpenEdgeQueryBuilder([
    SQL::QUERY => 'SELECT cd_client FROM PUB.clients_clients WHERE actif = 1' ,
]) ;
```

In this case, other properties are ignored — except `$lockingHint`, which is still applied at the end. And bind variables (`SQL::BINDS`) are still accepted by the model.

## Pattern: instantiate a builder outside a model

Use case: a CLI script wants to produce a custom ad-hoc query without setting up a full model.

```php
use oihana\openedge\db\OpenEdgeQueryBuilder ;
use oihana\openedge\enums\OpenEdge as SQL  ;

$builder = new OpenEdgeQueryBuilder
([
    SQL::COLUMNS => [
        [ SQL::COLUMN => 'cd_pays' , SQL::ALIAS => 'country' ] ,
        [ SQL::COLUMN => 'COUNT(*)' , SQL::ALIAS => 'n' ] ,
    ],
    SQL::FROM     => 'PUB.clients_clients' ,
    SQL::GROUP_BY => 'cd_pays' ,
    SQL::ORDER_BY => 'cd_pays' ,
]) ;

$bindVars = [] ;
$query = compile([
    $builder->select() ,
    $builder->columnList() ,
    $builder->from() ,
    $builder->where([] , $bindVars , 'list') ,
    $builder->groupBy() ,
    $builder->orderBy() ,
]) ;

$stmt = $pdo->prepare( $query ) ;
$stmt->execute( $bindVars ) ;
```

> For repeated CLI usage, still better to wrap a `Documents` model around the builder — you benefit from the cache, the `ALTERS`, the logger, and error handling.

## Debugging a malformed query

The builder logs queries in debug mode. Enable via `$init[OpenEdge::DEBUG] = true` in the model call:

```php
$customers->list([
    SQL::DEBUG => true ,
    SQL::SORT  => '-name' ,
]) ;
```

Typical output:

```
query    : SELECT clients.cd_client AS "id", clients.nom_client AS "name" FROM PUB.clients_clients clients ORDER BY nom_client DESC FETCH FIRST 50 ROWS ONLY
bindVars : {"country":"FR"}
```

Lets you verify the generated query is what you expect, and copy it into `isql` to test in isolation.

## See also

- [`Documents` model](models.md) — main consumer of the builder.
- [SQL clauses](sql/sql-clauses.md) — detail of each clause trait.
- [Helpers reference](helpers.md) — `expression`, `columnExpression`, `asAlias`, etc.
- [Enums reference](enums.md) — catalog of accepted `OpenEdge::*` keys.
