# SQL clauses

The [`Clause`](../../../src/oihana/openedge/db/enums/Clause.php) enum lists the SQL keywords the framework consumes to assemble a query. This page enumerates them and pairs each with its writing trait or helper.

## `Clause` enum

```php
use oihana\openedge\db\enums\Clause ;
```

| Constant | SQL value | Role |
|---|---|---|
| `Clause::SELECT` | `SELECT` | Start of a read query. |
| `Clause::FROM` | `FROM` | Data source (table, join, subquery). |
| `Clause::WHERE` | `WHERE` | Filtering conditions. |
| `Clause::GROUP_BY` | `GROUP BY` | Row grouping. |
| `Clause::HAVING` | `HAVING` | Post-grouping conditions. |
| `Clause::ORDER_BY` | `ORDER BY` | Sorting. |
| `Clause::OFFSET` | `OFFSET` | Pagination — rows to skip. |
| `Clause::FETCH` | `FETCH` | Pagination — number of rows to return. |
| `Clause::FIRST` | `FIRST` | Synonym of `NEXT` in `FETCH FIRST x ROWS`. |
| `Clause::NEXT` | `NEXT` | Synonym of `FIRST`. |
| `Clause::ONLY` | `ONLY` | Suffix of `FETCH FIRST x ROWS ONLY`. |
| `Clause::ROW` | `ROW` | Singular of `ROWS`. |
| `Clause::ROWS` | `ROWS` | Suffix of `OFFSET x ROWS` and `FETCH FIRST x ROWS`. |
| `Clause::TOP` | `TOP` | SQL Server-style pagination: `SELECT TOP 50`. |
| `Clause::ON` | `ON` | Join condition. |
| `Clause::AS` | `AS` | Expression or table alias. |
| `Clause::SET` | `SET` | `UPDATE` assignments. |
| `Clause::VALUES` | `VALUES` | `INSERT` values. |
| `Clause::COUNT` | `COUNT` | Counting keyword (used by the builder's `count()` helper). |
| `Clause::INSERT` | `INSERT INTO` | Start of an insert query. |
| `Clause::UPDATE` | `UPDATE` | Start of an update query. |
| `Clause::DELETE` | `DELETE` | Start of a delete query. |
| `Clause::WITH` | `WITH` | Prefix of a table-level locking hint: `WITH (NOLOCK)`. |
| `Clause::FOR_UPDATE` | `FOR UPDATE` | Exclusive lock on read rows. |
| `Clause::TENANT` | `TENANT` | Progress multi-tenant (rarely used). |
| `Clause::NO_REORDER` | `{ NO REORDER }` | Disable join-order optimisation. |

## Query-builder traits

The [`OpenEdgeQueryBuilder`](../query-builder.md) delegates each clause to a specialised trait. This section lists them with their role. Each trait initialises a public property of the same name at the constructor through `OpenEdge::*`.

### `FromTrait`

Manages the builder's `FROM` string, which may include inline joins.

```php
use oihana\openedge\enums\OpenEdge as SQL ;

new OpenEdgeQueryBuilder([
    SQL::FROM => 'PUB.customers clients LEFT JOIN PUB.countries pays ON clients.country_code = pays.country_code' ,
]) ;
```

`SQL::FROM` accepts a string, not an array. The framework doesn't dynamically build joins — the developer pre-compiles the string. This is intentional: Progress joins often contain quirks (`(+)`, case-sensitive aliases, filter conditions disguised as join conditions) that a generic builder would struggle to model.

### `WhereTrait`

Handles the `WHERE` clause. Accepts a recursive structure (single condition, `AND`/`OR` group, special predicates).

```php
SQL::WHERE =>
[
    SQL::LOGIC      => Logic::AND ,
    SQL::CONDITIONS =>
    [
        [ SQL::COLUMN => 'active'   , SQL::OPERATOR => '=' , SQL::VALUE => 1        ] ,
        [ SQL::COLUMN => 'country_code' , SQL::OPERATOR => '=' , SQL::BIND  => 'country' ] ,
    ]
]
```

See [SQL predicates](sql-predicates.md) for the accepted forms.

### `GroupByTrait`

Handles `GROUP BY` and its optional `HAVING` clause.

```php
SQL::GROUP_BY => [ 'country_code' , 'segment' ] ,
SQL::HAVING   =>
[
    SQL::COLUMN   => 'country_code' ,
    SQL::OPERATOR => '<>'      ,
    SQL::VALUE    => 'XX'      ,
]
```

`SQL::GROUP_BY` accepts a string (single column) or an array (multiple).

### `OrderByTrait`

Handles the default sort **and** the `SORTABLE` whitelist.

```php
SQL::ORDER_BY => 'customer_name'         , // default sort server-side
SQL::SORTABLE =>
[
    'id'   => 'customer_id'  ,             // ?sort=id → ORDER BY customer_id
    'name' => 'customer_name' ,             // ?sort=name → ORDER BY customer_name
]
```

When the HTTP controller receives `?sort=name` or `?sort=-name`, it checks the key is in `SORTABLE`. A missing key is silently ignored — that's the anti-injection guard.

### `ColumnTrait`

Handles the `SELECT` column list. Accepts an array of expression definitions:

```php
SQL::COLUMNS =>
[
    [ SQL::COLUMN => 'customer_id'  , SQL::TABLE => 'clients' , SQL::ALIAS => 'id'   ] ,
    [ SQL::COLUMN => 'customer_name' , SQL::TABLE => 'clients' , SQL::ALIAS => 'name' ] ,
    [
        SQL::CONCAT =>
        [
            [ SQL::COLUMN => 'first_name' , SQL::TABLE => 'clients' ] ,
            ' '                                                            ,
            [ SQL::COLUMN => 'customer_name'    , SQL::TABLE => 'clients' ] ,
        ],
        SQL::ALIAS => 'fullName' ,
    ],
]
```

Each definition flows through the [`expression()`](../helpers.md#expression) helper.

### `BindTrait`

Manages the query's bind variables. The trait exposes the `bindVars` property (associative array `[ name => value ]`) that PDO receives at execution.

```php
$builder->bindVars[ 'country' ] = 'FR' ;
```

In practice, you don't write directly to `bindVars`: you declare `SQL::BIND => 'country'` in a condition, and the framework injects the value from the call context.

### `LookingHintTrait`

Handles Progress locking hints (`NOLOCK`, `READPAST`, …). See [Locking hints](../progress/locking-hints.md).

```php
use oihana\openedge\db\enums\LockingHint ;

SQL::LOCKING_HINT => LockingHint::WITH_NOLOCK ,
// → SELECT ... FROM table WITH (NOLOCK)
```

### `FacetsTrait`

Manages the optional pieces after `WHERE`: `GROUP BY`, `HAVING`, `ORDER BY`, `LIMIT`, `OFFSET`, `DISTINCT`. It's a meta-trait that orchestrates the others.

### `OpenEdgeQueryBuilderTrait`

Initialisation trait: PSR-3 logger, DI container, query identifier (for log lines).

## See also

- [Building a SQL query step by step](sql-building-queries.md) — assembling a complete SELECT.
- [SQL predicates](sql-predicates.md) — the seven forms accepted in `WHERE` and `HAVING`.
- [SQL operators](sql-operators.md) — allowed operators in a condition.
- [`OpenEdgeQueryBuilder`](../query-builder.md) — detail of the builder that composes these traits.
