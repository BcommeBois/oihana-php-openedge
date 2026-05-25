# Tips and pitfalls

Collection of golden rules to respect when working with `oihana/openedge`. Any violation discovered (with its incident) should grow this page rather than stay in a session's memory.

## Table of contents

### SQL security

- [`bindExpression` is mandatory for any dynamic value](#bindexpression-is-mandatory-for-any-dynamic-value)
- [`SORTABLE` is mandatory to allow a public sort](#sortable-is-mandatory-to-allow-a-public-sort)
- [No SELECT read-only means no HTTP write](#no-select-read-only-means-no-http-write)

### Performance and locks

- [`WITH (NOLOCK)` for reporting reads](#with-nolock-for-reporting-reads)
- [`arraySize` to tune per use case](#arraysize-to-tune-per-use-case)
- [`Alter::GET` without cache creates an N+1](#altergetwithout-cache-creates-an-n1)

### Progress specifics

- [The outer join `(+)` goes on the right side](#the-outer-join--goes-on-the-right-side)
- [`charSet = 106` for UTF-8, almost always](#charset--106-for-utf-8-almost-always)
- [`(+) AND filter` cancels the outer join](#-and-filter-cancels-the-outer-join)

### Testing and debugging

- [No Progress driver on macOS — integration tests on preprod](#no-progress-driver-on-macos--integration-tests-on-preprod)
- [`SQL::DEBUG => true` to see the generated query](#sqldebug--true-to-see-the-generated-query)
- [Test gap on high-level classes](#test-gap-on-high-level-classes)

---

## SQL security

### `bindExpression` is mandatory for any dynamic value

**Rule.** Any value coming from user input or an uncontrolled context goes through `SQL::BIND`, never through `SQL::VALUE`, never through a direct `literal()`.

```php
// ❌ Injection risk — value is inlined into the SQL
SQL::WHERE => [
    SQL::COLUMN   => 'nom_client'      ,
    SQL::OPERATOR => '='               ,
    SQL::VALUE    => $_GET['search']   , // never this
]

// ✅ Value is bound at execution, PDO escapes correctly
SQL::WHERE => [
    SQL::COLUMN   => 'nom_client' ,
    SQL::OPERATOR => '='          ,
    SQL::BIND     => 'search'     ,
]
$model->list([ SQL::BINDS => [ 'search' => $_GET['search'] ] ]) ;
```

### Why

`SQL::VALUE` flows through `valueExpression()` which calls `literal()` for strings. `literal()` escapes single quotes by doubling — but **doesn't** protect against injection on other characters (comments `--`, null bytes, Progress control codes). It's explicitly designed for **server-side constants** (current date, status code, etc.), not for user inputs.

`SQL::BIND` produces a `:name` placeholder that PDO correctly escapes at execution and also activates Progress's plan cache.

### Symptoms of an oversight

- A `'` character in a searched value causes a Progress-side SQL error (parsing broken).
- A code audit shows `literal( $input )` or `SQL::VALUE => $input` with a variable.

---

### `SORTABLE` is mandatory to allow a public sort

**Rule.** The `SQL::SORTABLE` key is a whitelist — any missing key is silently ignored. Without `SORTABLE`, no public sort works. **Never** build an `ORDER BY` from an HTTP parameter directly.

```php
// ❌ Guaranteed injection on ?sort=
SQL::ORDER_BY => $_GET['sort']

// ✅ Explicit whitelist; ?sort=name → ORDER BY nom_client
SQL::ORDER_BY => 'nom_client' ,
SQL::SORTABLE =>
[
    'id'   => 'cd_client'  ,
    'name' => 'nom_client' ,
]
```

The controller reads `?sort=name` (or `?sort=-name` for `DESC`), parses via `orderByExpression()`, checks the key against `SORTABLE`, and uses the mapped value. Unknown keys fall through silently.

### Symptoms of an oversight

- A `?sort=field; DROP TABLE x` isn't filtered → disaster.
- A client reports "I added `?sort=foo` but nothing changes" → the key isn't in `SORTABLE` (expected behaviour, not a bug).

---

### No SELECT read-only means no HTTP write

**Rule.** The framework's `DocumentsController` exposes only `count`, `get`, `list`. The router enforces `RouteFlag::READ_ONLY`. **Don't bypass** this flag without a documented reason.

Three reasons for this doctrine (see [introduction.md](introduction.md#a-doctrine-openedge-is-read-only-over-http)):

1. **Source of truth elsewhere.** The ERP has its own client for business mutations.
2. **Synchronisation, not dual write.** CLI harvests read OpenEdge and write to the document target — that target serves public writes.
3. **Progress locking.** Production's long ABL transactions take locks; mutating in parallel exposes you to deadlock.

The model (`Documents`) exposes `insert`, `update`, `upsert`, `delete` — usable on the CLI side or in migration scripts. That's intentional. **The HTTP controller must not re-expose them without audit.**

---

## Performance and locks

### `WITH (NOLOCK)` for reporting reads

**Rule.** Any reporting / catalog HTTP / dashboard read uses `LockingHint::WITH_NOLOCK`. Production Progress has long ABL transactions taking locks — without `NOLOCK`, the API can block for minutes.

```php
SQL::LOCKING_HINT => LockingHint::WITH_NOLOCK ,
```

Trade-off: `NOLOCK` sees uncommitted data (*dirty read*). Acceptable for 95 % of catalog use cases; avoid for accounting-consistency queries. See [Locking hints](progress/locking-hints.md).

---

### `arraySize` to tune per use case

**Rule.** The DSN `arraySize` parameter controls how many rows the Progress driver fetches per server round-trip. Driver default = 1 → catastrophic on any volume.

| Use case | `arraySize` |
|---|---|
| HTTP catalog read (limit ~50-200) | `200` |
| Bulk CLI harvest | `1000` to `5000` |
| Document-by-document streaming | `100` to `200` |

On a harvest fetching a million rows with `arraySize = 1`, that's a million network round-trips. Switching to `5000` divides total time by the same factor.

---

### `Alter::GET` without cache creates an N+1

**Rule.** When a model declares `Alter::GET` for denormalisation, the **target** model must have `ModelParam::CACHE` configured. Without cache, a 1000-row list with 5 denormalised references triggers 5000 lookups.

```php
// Target model: MANDATORY to configure a cache
new Documents( $container ,
[
    ModelParam::PDO    => /* ... */ ,
    ModelParam::CACHE  => Caches::THESAURUS_CATEGORIES , // ← essential
    ModelParam::QUERY_BUILDER => /* ... */ ,
]) ;
```

See [Alters](alters.md#pitfalls).

---

## Progress specifics

### The outer join `(+)` goes on the right side

**Rule.** The `(+)` goes on the column **of the side that may be missing**, not on the side you want to "keep".

```sql
-- Keep employees with no department: (+) on d, not on e
WHERE e.cd_dept = d.cd_dept(+)
```

Mnemonic: "*plus something that isn't really there*". See [Progress outer join](progress/outer-join.md).

---

### `charSet = 106` for UTF-8, almost always

**Rule.** The Progress DSN `charSet` parameter uses IANA codepages, not PHP names. `106` = UTF-8. Set it by default.

If you see broken accented characters (`é` instead of `é`), it's this parameter. See [DSN](dsn.md#charset).

---

### `(+) AND filter` cancels the outer join

**Rule.** With the `(+)` syntax, the join and the filter live in the same `WHERE`. An `AND d.libelle = 'X'` filter added **eliminates** rows where `d.libelle IS NULL`, which cancels the outer-join effect.

```sql
-- ❌ Employees without a department are dropped here
WHERE  e.cd_dept = d.cd_dept(+)
  AND  d.libelle = 'VENTES'

-- ✅ NULL-compatible filter
WHERE  e.cd_dept = d.cd_dept(+)
  AND  ( d.libelle = 'VENTES' OR d.libelle IS NULL )

-- ✅✅ Better: LEFT JOIN with ON
FROM   PUB.employes e
LEFT JOIN PUB.departements d ON e.cd_dept = d.cd_dept AND d.libelle = 'VENTES'
```

See [Progress outer join](progress/outer-join.md#pitfalls-of-).

---

## Testing and debugging

### No Progress driver on macOS — integration tests on preprod

**Factual constraint.** The Progress SQL ODBC driver only ships for Linux x86_64 and Windows. It doesn't exist for macOS. As a result, on a Mac development workstation:

- The pure SQL helpers (which produce strings) are unit-testable — no connection needed.
- The high-level classes that open a connection (`OpenEdgePDOBuilder`, `Documents`, `DocumentsController`) **cannot be tested in local unit tests on Mac**.

Test strategy:

1. **Local Mac unit tests** — cover the SQL helpers (`db/helpers/**`), the enums, the string-producing functions. That's what the package's existing 58 tests cover.
2. **Preprod Debian integration** — cover model boot, PDO execution, HTTP controller. Run before deployment, not locally.

> Consequence: the high-level layers (`OpenEdgeQueryBuilder` itself, `OpenEdgePDOBuilder`, `OpenEdgeDSN`, `Documents`, `DocumentsController`) **have no dedicated unit tests** in the package. See also [Test gap on high-level classes](#test-gap-on-high-level-classes).

### Possible workarounds (not implemented)

- Mock `PDO` behind `PDOTrait`: doable but heavy, and the mock won't reproduce Progress quirks (odd casting, `CHAR` padding, etc.).
- Use a SQLite or MySQL backend in tests: doesn't cover Progress specifics (outer join `(+)`, `ARRAY` types).
- Run a Progress test Docker container: Progress doesn't ship an official Docker image.

The current trade-off is to accept the constraint and prioritise integration tests on the real preprod environment.

---

### `SQL::DEBUG => true` to see the generated query

**Tip.** When a query fails or returns an unexpected result, enable debug mode to see the generated SQL and bind variables:

```php
$customers->list([
    SQL::DEBUG => true ,
    SQL::SORT  => '-name' ,
]) ;
```

Typical output in logs:

```
query    : SELECT clients.cd_client AS "id", clients.nom_client AS "name" FROM PUB.clients_clients clients ORDER BY nom_client DESC FETCH FIRST 50 ROWS ONLY
bindVars : {"country":"FR"}
```

Copy the SQL into `isql` to test in isolation and locate the error.

---

### Test gap on high-level classes

**Factual state** (May 2026). The package has 58 tests that cover well:

- the SQL helpers (`db/helpers/**`),
- the string-producing functions (`functions/**`),
- the predicates (`predicates/**`),
- the `WhereTrait`.

But there are **no** unit tests for:

- `OpenEdgeQueryBuilder` (the central builder class),
- `OpenEdgePDOBuilder` (the PDO factory),
- `OpenEdgeDSN` (the DSN builder),
- `Documents` (the high-level model),
- `DocumentsController` (the HTTP controller).

Reason: these classes need an ODBC Progress PDO connection, so a driver unavailable on the standard development workstation (Mac). See [No Progress driver on macOS](#no-progress-driver-on-macos--integration-tests-on-preprod).

**Practical implications:**

- Modifications to these classes require **preprod** validation before deployment.
- A regression introduced on the builder or model side will **not** be caught by local CI — only by integration tests on preprod.
- Any framework extension (inheritance, method addition) should come with an integration test on the host project side.

To address: could be tackled via a Progress test Docker (if an official image appears) or via a minimal mocked PDO harness. Not a priority as long as the preprod strategy works.

---

## Code conventions

### Importing `OpenEdge as SQL`

The dominant pattern in host applications imports the central enum `OpenEdge` under the short alias `SQL` for readability:

```php
use oihana\openedge\enums\OpenEdge as SQL ;

SQL::COLUMN
SQL::TABLE
SQL::WHERE
```

More readable than `OpenEdge::WHERE` repeated 30 times in a model definition.

### Externalise `COLUMNS` / `FROM` / `WHERE` into functions

For complex models, externalise the blocks into named PHP functions under `app\definitions\openedge\<entity>\`:

```php
SQL::COLUMNS  => customerAllColumns() ,
SQL::FROM     => customerFrom()       ,
SQL::WHERE    => customerWhere()      ,
```

Benefits: more readable DI, SQL code reusable across models (API + harvest), unit tests possible on those functions. See [models.md](models.md#the-columns--from--where-externalisation-pattern).

### `Schema::MODIFIED`, not `updatedAt`

oihana convention aligned with Schema.org: the modification-date field is named **`modified`** (not `updatedAt`, not `updated_at`). Same with `created`, not `createdAt`.

```php
Prop::MODIFIED ,  // ← convention
Prop::CREATED  ,
```

---

## See also

- [Introduction](introduction.md) — framework doctrine.
- [`Documents` model](models.md) — full domain layer.
- [Building a SQL query step by step](sql/sql-building-queries.md) — SELECT assembly.
- [Progress outer join](progress/outer-join.md) — `(+)` detail.
- [Locking hints](progress/locking-hints.md) — when to use `NOLOCK`.
