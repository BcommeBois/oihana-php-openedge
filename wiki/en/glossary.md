# Glossary

This page defines key terms used throughout the framework documentation. It does not replace the [official Progress OpenEdge documentation](https://docs.progress.com/bundle/openedge-sql-reference/) — it fixes a shared vocabulary for this doc.

## SQL / ODBC / Progress terminology

### ABL

*Advanced Business Language*, historically called Progress 4GL. The application and data manipulation language native to Progress OpenEdge. `oihana/openedge` **does not touch ABL**: it goes through the standard SQL interface Progress exposes in parallel.

### `(+)` (Progress outer join)

Non-standard suffix used in the `WHERE` clause of Progress OpenEdge to express an *outer join* in the historical Oracle style. Exposed in the framework through the `OpenEdge::NULLABLE_COLUMN = '(+)'` constant. See [Progress outer join](progress/outer-join.md).

### `ArraySize`

Progress ODBC DSN parameter controlling how many rows the driver fetches per server round-trip. Too low multiplies network calls; too high consumes client RAM. Exposed in the framework via the camelCase key `arraySize`.

### Bind variable

Variable injected into a SQL query as `:name` instead of being inlined in the string. Its value is provided separately to PDO via `bindValue` / `bindParam`. Prevents SQL injection and allows server-side query plan reuse. See the [`bindExpression`](helpers.md#bindexpression) helper.

### CAST

Explicit conversion from one SQL type to another, syntax `CAST(expression AS type[(length[,scale])])`. OpenEdge has its own type catalog (`VARCHAR`, `INTEGER`, `DECIMAL`, `TIMESTAMP`, `BLOB`, …). See [`CAST` and SQL types](sql/sql-functions-casts.md).

### DSN

*Data Source Name*. ODBC connection string describing where and how to connect to a database. For Progress: `Driver=...;HostName=...;PortNumber=...;Database=...;IANAAppCodePage=...`. Built by the [`OpenEdgeDSN`](dsn.md) class.

### `IANAAppCodePage`

Progress ODBC DSN parameter indicating the IANA codepage to use for string conversions on the client side. Common value: `106` (UTF-8). Exposed in the framework via the camelCase key `charSet`.

### Locking hint

Explicit indication passed to OpenEdge on the locking strategy for a query. Examples: `NOLOCK` (lockless read, *dirty read*), `READ COMMITTED`. On a reporting ERP, `NOLOCK` is often essential to avoid freezing production. See [Locking hints](progress/locking-hints.md).

### ODBC

*Open Database Connectivity*. Standard for accessing a SQL database through a third-party driver. PHP exposes it through the `ext-odbc` extension and the `PDO_ODBC` driver. Progress ships its proprietary SQL ODBC driver (`pgoe27.so` on Linux).

### Outer join

Join that keeps rows from one side even when the opposite side has no match. Standard SQL expresses this through `LEFT JOIN` / `RIGHT JOIN` / `FULL JOIN`; Progress also accepts the historical `(+)` syntax in the `WHERE` clause (see above).

### PDO

*PHP Data Objects*. Standard PHP abstraction for accessing a database through various drivers (`mysql`, `pgsql`, `sqlite`, `odbc`, `sqlsrv`, …). `oihana/openedge` produces its PDO instances through the `OpenEdgePDOBuilder` factory.

### Predicate

Fragment of a `WHERE` clause that evaluates to `TRUE`, `FALSE`, or `UNKNOWN`. The seven forms supported by the framework:

- *basic* — binary comparison (`=`, `<>`, `<`, `>`, …)
- *between* — `expr BETWEEN x AND y`
- *in* — `expr IN (a, b, c)`
- *like* — `expr LIKE 'pattern%'`
- *exists* — `EXISTS ( subquery )`
- *null* — `IS NULL`, `IS NOT NULL`
- *quantified* — `expr op { ANY | ALL | SOME } ( subquery )`

See [SQL predicates](sql/sql-predicates.md).

### Query timeout

Maximum duration (in seconds) a query can take on the server side before being cancelled. For Progress, exposed via the DSN parameter `QueryTimeout`. Three special values: `-1` (no timeout, driver ignores `SQL_ATTR_QUERY_TIMEOUT`), `0` (no default timeout but driver respects `SQL_ATTR_QUERY_TIMEOUT`), `x > 0` (effective timeout).

## `oihana/openedge` terminology

### Alter / Alters

Transformation function applied to an expression when it's serialised to SQL. Examples: adding a `LOWER(...)`, a `CAST(...)`, or a post-fetch normalisation through `Alter::GET` that looks up a thesaurus. Exposed through the `OpenEdge::ALTER` (single transformation) and `OpenEdge::ALTERS` (transformation chain) keys. See [Alters and denormalisation](alters.md).

### `bindExpression` vs `valueExpression` vs `literal`

Three helpers that produce a SQL fragment for a value, picked according to the value's nature:

- **`bindExpression(['bind' => 'userId'])`** → produces `:userId` (PDO placeholder). Use for any **user-input value**.
- **`valueExpression(['value' => 'admin'])`** → produces the inline expression (typically via `literal`). Use for **server-side constants**.
- **`literal('admin')`** → produces `'admin'` (escaped string). Lower-level, used internally by `valueExpression`.

The absolute rule: **any dynamic value goes through `bindExpression`**, never through `literal` or `valueExpression`. See [tips.md](tips.md).

### Capability *(absent in openedge)*

Concept present in `oihana/arango` (fine-grained permission on a URL parameter value). The current OpenEdge controller is read-only with no skin system or capabilities — output projection is handled by the model's `ALTERS`. Mentioned to avoid confusion with `oihana/arango`.

### Composition of traits

Central architecture pattern of the framework: the `Documents` class barely contains any code of its own — it aggregates 13 single-responsibility CRUD traits (`DocumentsGetTrait`, `DocumentsListTrait`, `DocumentsInsertTrait`, …) plus 3 cross-cutting traits and 1 Progress-specific trait. Same pattern for `OpenEdgeQueryBuilder` which aggregates 9 clause traits.

### Container (DI)

PSR-11 (`Psr\Container\ContainerInterface`) dependency-injection container. The framework accepts a container at the constructor of models and controllers and resolves its dependencies (PDO connection, schemas, cache, logger) by service identifier. PHP-DI is used in the examples but the code isn't coupled to it.

### Definition

PHP file that returns a DI definitions array consumed by the container. In a typical host application, `oihana/openedge` definitions live under `definitions/...` (HTTP models) and `definitions/odbc/` (PDO connections).

### Expression

Basic unit produced by the framework helpers: a string representing a valid SQL fragment. An expression can be a literal (`'42'`), a bind (`:userId`), a qualified column (`clients.customer_id`), a function (`CAST(price AS INTEGER)`), a `CASE WHEN`, a concatenation (`a || b`), etc. The `expression()` function is the polymorphic entry point that dispatches based on the shape of the input array.

### Facet

Optional element of a SELECT query that comes after `SELECT … FROM … WHERE`. Covered by the `Facet` enum (`HAVING`, `GROUP_BY`, `ORDER_BY`, `LIMIT`, `OFFSET`, `DISTINCT`) and the `FacetsTrait` of the query builder.

### Harvest

Massive read synchronisation of an OpenEdge table to a target system (cache, document database, file). In a typical host application, `harvest:*` commands read OpenEdge with a dedicated model (typically `Models::<X>_HARVEST`) that projects only the useful columns, then write to the target. See [`Harvest` models](harvest.md).

### Model (`Documents`)

High-level class representing an OpenEdge table and exposing CRUD + listing + count + exist + stream operations. Configured by an `OpenEdge::*` keys array at the constructor. HTTP projection is read-only, but the model exposes all write operations (usable via CLI scripts, for instance).

### `OpenEdge::NULLABLE_COLUMN`

Constant equal to `'(+)'`. Used to suffix a column in the `WHERE` clause to express a historical Progress outer join. See [Progress outer join](progress/outer-join.md).

### Sortable

Explicit whitelist of fields allowed for HTTP sorting, declared under `OpenEdge::SORTABLE` in the query builder definition. Without this whitelist, the HTTP API offers no sorting — it's a protection against SQL injection through the `?sort=` parameter.

### Validate context

The `validateContext()` helper that introspects an expression definition array and throws a `ConstantException` if an unexpected key is present. Catches typos and obsolete constant names at query-construction time instead of at SQL runtime.

## See also

- [Introduction](introduction.md) — framework overview.
- [Dependencies](dependencies.md) — required packages.
- [Official Progress OpenEdge SQL documentation](https://docs.progress.com/bundle/openedge-sql-reference/) — canonical reference for syntax and types.
