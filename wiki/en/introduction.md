# Introduction

## What is Progress OpenEdge?

[Progress OpenEdge](https://www.progress.com/openedge) is a long-standing application platform from Progress Software, used by many business applications (ERP, sales, accounting). Its core includes:

- a proprietary transactional **database engine** (`.db` extension, tables organised under schemas like `PUB.<table>`);
- the **ABL** language (*Advanced Business Language*, historically "Progress 4GL"), both an application language and a data manipulation language;
- a standard **SQL interface** layered on top of the engine, accessible via the official **SQL ODBC driver** (`pgoe27.so` on Linux, Windows equivalent).

From a modern PHP application's point of view, Progress OpenEdge looks like **a SQL database accessible via ODBC**, with a few quirks inherited from the Progress engine:

- **`(+)` outer join syntax** (historical Oracle style), non-standard SQL;
- explicit *locking hints* (`NOLOCK`, `READ COMMITTED`, …);
- native ARRAY types;
- need to specify the **codepage charset** at connection time (`IANAAppCodePage`);
- need to manage fetch buffer sizes explicitly (`ArraySize`, `DefaultLongDataBuffLen`) for performance.

## Why this library

When you have to interface modern PHP with an OpenEdge ERP — typically to expose a product catalog, run reports, or synchronise data into a more modern system — you almost always hit the same three difficulties:

1. **No PHP ORM seriously supports OpenEdge.** Doctrine, Eloquent and friends target MySQL/PostgreSQL/SQLite/SQL Server. OpenEdge has no dedicated grammar in these ORMs, and Progress quirks (outer join `(+)`, locking hints) can't be modelled in JPQL/DQL.
2. **Progress SQL ends up built with `sprintf`.** Once out of the ORM, you're back to `'SELECT ' . implode(',', $cols) . ' FROM ' . $table . ' WHERE ...'` with all the fragility that implies (injection, literal escaping, optional conditions, `NULL` handling, case-sensitive aliases).
3. **ODBC configuration is finicky.** A Progress ODBC DSN needs about a dozen precise parameters (driver, host, port, codepage charset, buffer sizes, timeouts). The PDO/ODBC attributes to set on the client side (`PDO::ATTR_EMULATE_PREPARES = false`, `PDO::ATTR_STRINGIFY_FETCHES = false`, etc.) are rarely documented together.

`oihana/openedge` addresses all three:

- a **ready-to-use PDO factory** (`OpenEdgePDOBuilder`) that produces a PDO instance with the right attributes;
- a **typed DSN builder** (`OpenEdgeDSN`) that maps a camelCase config (`hostName`, `arraySize`, …) to the syntax the Progress driver expects (`HostName=…;ArraySize=…`);
- a **catalog of functional helpers** to build Progress SQL expressions (`cast`, `concat`, `coalesce`, `nvl`, `toChar`, …) without `sprintf`;
- a **`Documents` model** built by trait composition that exposes `list`/`get`/`count`/`exist`/`stream` and write operations (`insert`/`update`/`upsert`/`delete`) — writes are available at the model level but not exposed by the HTTP controller, see below;
- a **read-only Slim controller** (`DocumentsController`) to quickly expose catalog routes.

## The `oihana/openedge` philosophy

The framework follows five principles that show up throughout the code, shared with [`oihana/arango`](../arango/README.md):

1. **Standalone composable functions, not a heavy ORM.** The SQL layer is made of about a hundred small autoloaded PHP functions (`expression`, `bindExpression`, `cast`, `coalesce`, `concat`, …) you compose. No giant `QueryBuilder` object to learn — you read the generated SQL by looking at its PHP code.
2. **Zero magic strings.** Every option key, every SQL operator, every type or predicate is exposed as a constant on a typed enum (`OpenEdge::COLUMN`, `Predicate::BETWEEN`, `Type::VARCHAR`, `LockingHint::NOLOCK`). Raw strings (`'IS NULL'`, `'NOLOCK'`, `'VARCHAR'`) are systematically replaced with constants — renames stay refactorable and IDE search stays reliable.
3. **Composition of fine-grained traits.** The `Documents` model isn't a giant class: it aggregates 13 CRUD traits with a single responsibility (`DocumentsListTrait`, `DocumentsGetTrait`, `DocumentsInsertTrait`, …) plus a few cross-cutting traits (`AlterBindVarsTrait`, `CacheableTrait`, `EnsureKeysTrait`, `OpenEdgeHelperTrait`).
4. **Container-friendly.** Everything is designed to live behind a PSR-11 container (PHP-DI, Symfony DI, …). The PDO factory, the `Documents` model and the Slim controller all accept a `ContainerInterface` at the constructor and resolve their dependencies by service identifier.
5. **Slim integration out of the box, read-only.** `DocumentsController` produces a `GET/COUNT/LIST` route in a few lines with pagination and sorting (explicit whitelist via `OpenEdge::SORTABLE`). No `POST`/`PATCH`/`PUT`/`DELETE` by default at the controller level — see the doctrine below.

## A doctrine: OpenEdge is read-only over HTTP

In the typical host application — an API that exposes data from an ERP — **OpenEdge is never mutated through an HTTP request**. The Slim controller shipped (`DocumentsController`) only exposes `count`, `get` and `list`; routes use `RouteFlag::READ_ONLY`.

Three reasons for this doctrine:

1. **Source of truth elsewhere.** The ERP has its own client and its own ABL language for business mutations; the web API doesn't compete with that client.
2. **Synchronisation, not dual write.** The `harvest:*` CLI commands read OpenEdge and write to a modern document database (ArangoDB in host applications); it's that document database that serves public writes, never OpenEdge.
3. **Progress locking.** A production OpenEdge ERP has long-running ABL transactions that take locks; opening SQL writes in parallel exposes you to deadlock.

This doctrine is carried by the **controller, not the model**: the `Documents` model still exposes `insert` / `update` / `upsert` / `replace` / `delete` / `truncate`, because CLI usage or migration scripts have a legitimate need. At library extraction time, if another project needs to mutate OpenEdge over HTTP, it can extend `DocumentsController` to add its verbs — the model layer is ready.

## Audience and prerequisites

This documentation assumes the reader:

- knows PHP 8.4 or higher (the systematic use of typed enums, readonly properties and first-class callable syntax is central in the code);
- understands PDO and ODBC basics in PHP — installing a `unixODBC` driver, configuring `odbcinst.ini`, instantiating a `new PDO('odbc:...')`;
- is comfortable with a PSR-11 container (PHP-DI used in the examples, but the code isn't coupled to it).

Knowledge of Slim isn't required: the `DocumentsController` integration is an independent module. You can use the PDO factory and the SQL helpers without touching the Slim controller.

## Positioning vs PHP alternatives

| Solution | Status | SQL builder | Progress specifics | DI / Slim integration |
|---|---|---|---|---|
| Raw `PDO::ODBC` | PHP native | no | no | no |
| Doctrine DBAL (generic ODBC driver) | maintained | yes (standard SQL) | no — no `outer join (+)`, no Progress `LockingHint` | partial |
| `oihana/openedge` | active | yes (composable) | yes (`NULLABLE_COLUMN`, `LockingHint`, `proArrayEscape`, …) | yes |

The table sums up the observation that motivated the project: no PHP alternative cleanly covers Progress specifics, and many teams end up writing their own layer.

## Further reading

- [Dependencies](dependencies.md) — required `oihana/php-*` packages.
- [Glossary](glossary.md) — framework terminology.
- [OpenEdge quickstart](quickstart.md) — first working example.
- [Official Progress OpenEdge SQL documentation](https://docs.progress.com/bundle/openedge-sql-reference/) — canonical reference.
