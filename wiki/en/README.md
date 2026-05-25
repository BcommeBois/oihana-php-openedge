# oihana/openedge — Progress OpenEdge framework for PHP

![Language](https://img.shields.io/badge/language-English-blue)

`oihana/openedge` is a PHP framework that industrialises working with [Progress OpenEdge](https://www.progress.com/openedge) via SQL/ODBC: PDO factory, composable SQL builder, high-level `Documents` model built from trait composition, read-only Slim controllers, and a catalog of helpers for Progress SQL functions.

> This documentation is **actively under construction**. The table of contents below reflects actual progress.

## Who this documentation is for

PHP developers who need to expose or synchronise Progress OpenEdge data from a PHP API and want to:

- avoid hand-writing Progress SQL with `sprintf` — composable functional helpers, zero magic strings;
- rely on a pre-wired PDO/ODBC factory with the right Progress attributes set out of the box;
- expose **read-only** HTTP routes quickly (catalog, reporting, thesauri) on top of an OpenEdge ERP without reinventing the model layer for every table;
- integrate OpenEdge into a PHP-DI container and a Slim application with an API consistent with the rest of the `oihana` ecosystem.

## Quickstart

```php
use oihana\openedge\db\OpenEdgePDOBuilder ;
use oihana\openedge\enums\OpenEdge as SQL  ;
use oihana\openedge\models\Documents       ;

// 1. PDO factory from an ODBC config
$pdo = ( new OpenEdgePDOBuilder
([
    'scheme'   => 'odbc'                          ,
    'driver'   => '/usr/dlc/odbc/lib/pgoe27.so'   ,
    'hostName' => 'erp.example.com'               ,
    'portNumber' => 20931                         ,
    'database' => 'erp_database'                      ,
    'logonID'  => 'reader'                        ,
    'password' => 'secret'                        ,
]) )() ;

// 2. High-level model
$customers = new Documents( $container ,
[
    SQL::PDO   => $pdo                  ,
    SQL::TABLE => 'PUB.customers' ,
]) ;

$list  = $customers->list ([ SQL::LIMIT => 50 ]) ;
$first = $customers->get  ([ 'customer_id' => 1274 ]) ;
```

For details (ODBC DSN, PDO attributes, composable SQL builder, models, Slim controllers), see the table of contents below.

## Table of contents

### Foundations

- [Introduction](introduction.md) — what Progress OpenEdge is, `oihana` philosophy, why this library exists.
- [Dependencies](dependencies.md) — required `oihana/php-*` packages, minimal `composer require` snippet.
- [Glossary](glossary.md) — key terms: ODBC, DSN, *bind*, *literal*, *predicate*, *alter*, *cast*, *outer join (+)*, *locking hint*, *harvest*.

### Getting started

- [OpenEdge quickstart](quickstart.md) — first PDO connection, first `SELECT`, first `Documents` model.
- [ODBC connection and multi-database](connection.md) — TOML configuration, `OpenEdgePDOBuilder` factory, PHP-DI integration, multi-database (accounting, common, erp, stats, …).
- [ODBC DSN in detail](dsn.md) — `OpenEdgeDSN` class, config → DSN mapping, PDO attributes set by default, connection troubleshooting.

### Building SQL queries

- [Building a SQL query step by step](sql/sql-building-queries.md) — chaining `SELECT → FROM → JOIN → WHERE → GROUP BY → ORDER BY → LIMIT`, examples.
- [SQL clauses](sql/sql-clauses.md) — `Clause` enum and `FromTrait`, `WhereTrait`, `GroupByTrait`, `OrderByTrait`, `ColumnTrait`, `BindTrait`.
- [SQL predicates](sql/sql-predicates.md) — the seven predicate forms (`BETWEEN`, `IN`, `LIKE`, `EXISTS`, `IS NULL`, *quantified*, *basic*).
- [SQL operators](sql/sql-operators.md) — `RelationalOperator`, `Logic`, `QuantifiedOperator`, `ConcatOperator`.
- [String functions](sql/sql-functions-strings.md) — 30 helpers (`CONCAT`, `LPAD`, `SUBSTR`, `UPPER`, `proArrayEscape`, …).
- [Date functions](sql/sql-functions-dates.md) — `CURDATE`, `SYSDATE`, `NOW`, composition helpers.
- [Numeric functions](sql/sql-functions-numerics.md) — 23 helpers (`ABS`, `ROUND`, `MOD`, `POWER`, `GREATEST`, `LEAST`, …).
- [`CAST` and SQL types](sql/sql-functions-casts.md) — `castVARCHAR`, `castINTEGER`, `castTIMESTAMP`, `castDECIMAL`, … (19 targets).
- [Explicit conversions](sql/sql-functions-conversions.md) — `TO_CHAR`, `TO_DATE`, `TO_NUMBER`, `TO_TIME`, `TO_TIMESTAMP`.
- [SQL conditionals](sql/sql-functions-conditionals.md) — `COALESCE`, `IFNULL`, `NULLIF`, `NVL`, `NULLIF_EMPTY`, `NULLIF_ZERO`.
- [`CASE` expressions](sql/sql-functions-cases.md) — `whenExpression`, `thenExpression`, `elseExpression`, composition.
- [Aggregates](sql/sql-functions-aggregates.md) — `COUNT`, `SUM`, `AVG`, `MIN`, `MAX`.

### Progress specifics

- [Progress outer join `(+)`](progress/outer-join.md) — non-standard syntax, `NULLABLE_COLUMN` constant, examples.
- [Locking hints](progress/locking-hints.md) — `NOLOCK`, `READ COMMITTED`, when and why on a reporting ERP.
- [Connection timeouts](progress/timeouts.md) — `connectTimeout`, `serverTimeout`, `updateStatistics` via `OpenEdgeHelperTrait`.
- [Progress arrays](progress/arrays.md) — `proArrayEscape`, `proArrayDescape`, `proElement` helpers for native Progress `ARRAY` columns.

### Options and configuration

- [Enums reference](enums.md) — `OpenEdge` (init keys), `Clause`, `Predicate`, `Logic`, `Type`, `Facet`, `LockingHint`, 8 function categories.
- [Helpers reference](helpers.md) — `expression`, `bindExpression`, `valueExpression`, `columnExpression`, `literal`, `asAlias`, `searchCondition`, `validateContext`, `overrideExpression`.

### Domain layer

- [`Documents` model](models.md) — architecture by trait composition, `OpenEdge::*` keys catalog, CRUD methods, PDO integration.
- [`OpenEdgeQueryBuilder`](query-builder.md) — fluent SELECT/COUNT builder, its 9 traits, concrete examples.
- [Slim controllers](controllers.md) — read-only `DocumentsController`, `Count` / `Get` / `List` HTTP traits, `DocumentRoute` with `RouteFlag::READ_ONLY`.
- [Alters and denormalisation](alters.md) — `Alter::NORMALIZE` + `Alter::GET` pattern for resolving cross-database references.
- [`Harvest` models](harvest.md) — source model pattern for OpenEdge → target synchronisation (cache, document database).

### Cross-cutting

- [Tips and pitfalls](tips.md) — golden rules: `bindExpression` vs `valueExpression` vs `literal`, read-only HTTP, `sortable` whitelist, local test constraint without ODBC driver.

## Project status

| Phase | Description | State |
|---|---|---|
| 0 | Foundations — introduction, dependencies, glossary | *available* |
| 1 | Getting started — quickstart, connection, dsn | *available* |
| 2 | SQL core — clauses, predicates, functions | *available* |
| 3 | Progress specifics — outer join, locking, timeouts, arrays | *available* |
| 4 | Options and enums | *available* |
| 5 | Domain layer — models, builder, controllers, alters, harvest | *available* |
| 6 | Tips and pitfalls | *available* |

## Source code

The framework code lives under [`src/oihana/openedge/`](../../src/oihana/openedge/).

## See also

- [Official Progress OpenEdge SQL documentation](https://docs.progress.com/bundle/openedge-sql-reference/) — canonical reference.
