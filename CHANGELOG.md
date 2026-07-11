# Changelog

All notable changes to **oihana/php-openedge** are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- `db/helpers/parseExtent()` helper: extracts one element (0-based index) from a Progress extent serialized as a `';'`-joined string (e.g. `"5;0;0;0"`), trimmed, returning an empty string when the value or the requested index is absent. Registered in `autoload.files` and covered by `ParseExtentTest` (10 tests).
- Initial scaffold: Composer manifest, PHPUnit 12 + phpDocumentor 3 configuration, MPL-2.0 license, README, CHANGELOG, sibling-aligned folder layout (`src/`, `tests/`, `wiki/`, `assets/`), 132 `autoload.files` entries for the functional helpers.
- Source code under `src/oihana/openedge/` (185 PHP files):
  - `db/` (165 files): a fully functional OpenEdge SQL query builder — column / value / literal / override / concat / search-condition / order-by / limit / cases (`whenThenExpression`, `elseExpression`), 7 predicates (basic, between, exists, in, like, null, quantified), 22 ODBC cast functions (`castBIGINT`, `castVARCHAR`, `castTIMESTAMP`, ...), 23 numeric functions (`abs`, `round`, `power`, `mod`, ...), 31 string functions (`concat`, `substring`, `lpad`, `proArrayEscape`, ...), 6 date functions (`now`, `sysTimestamp`, ...), 5 conversion functions (`toChar`, `toDate`, `toNumber`, `toTime`, `toTimestamp`), 6 conditional functions (`coalesce`, `ifNull`, `nullIf`, `nullIfEmpty`, `nullIfZero`, `nvl`), 6 alter helpers (`alterDate`, `alterKey`, `alterNumeric`, `alterString`, `alterConditional`, `alterConversion`), plus the `OpenEdgeType` enum and the `WhereTrait`.
  - `models/` (15 files): `Documents` model + trait-composed CRUD operations (`DocumentsListTrait`, `DocumentsGetTrait`, `DocumentsCountTrait`, `DocumentsStreamTrait`, `DocumentsUpdateTrait`, ...) wired on PDO + ODBC.
  - `controllers/` (4 files): `DocumentsController` extending the PSR-7 controller stack with list/get/count actions tailored for read-heavy OpenEdge integrations.
  - `enums/` (1 file): typed constants.
- Test suite under `tests/oihana/openedge/` (58 PHP files): unit coverage for the functional helpers (predicates, casts, string/numeric/date/conditional/conversion functions, expression builders). The suite runs without an ODBC driver and is fully green under PHPUnit 12 strict mode.
- Bilingual user guides under `wiki/{fr,en}/`: introduction, quickstart, dependencies, DSN, connection, glossary, helpers, query builder, models, controllers, harvest, tips, enums, alters, plus the `progress/` (driver quirks) and `sql/` (function catalogue) folders.

### Tests
- Coverage campaign — Lot 6 (controllers): added `DocumentsControllerTest`, building the controller on a real lightweight Slim app (`AppFactory` + route parser) with a stubbed `DocumentsModel`. Covers `count` (raw int without response, success `Response` with one, and the failure path forwarding the exception code), `get` and `list`. `controllers` 0% → 100%, `controllers/traits` 0% → 93.0%, overall line coverage 84.95% → 93.02%. This completes the locally-achievable scope; the remaining gaps are the ODBC-only `OpenEdgePDOBuilder`, cache-hit branches and unreachable code.
- Coverage campaign — Lot 5b (Documents read-only guards + stream): extended `DocumentsTest` with a data-provided check that every mutation (`insert`, `update`, `updateDate`, `replace`, `upsert`, `delete`, `deleteAll`, `last`, `truncate`) raises `UnsupportedOperationException` — this is a read-only library — and a `stream()` test driving a generator-backed read through a stubbed `PDO`. `models/traits/documents` 64.2% → 77.5%, overall line coverage 83.50% → 84.95%.
- Coverage campaign — Lot 5 (Documents model, mocked PDO): added `DocumentsTest` covering `count`/`exist` through the debug+mock seam (generated SQL asserted via a capturing logger), `get`/`list` through a stubbed `PDO`/`PDOStatement`, and the `OpenEdgeHelperTrait` statements (`connectTimeout`, `serverTimeout`, `updateStatistics`) via mock expectations. `Documents` and `OpenEdgeHelperTrait` reach 100%; `models/traits/documents` 0% → 64.2%; overall line coverage 74.25% → 83.50%. The write traits (insert/update/replace/upsert/delete/deleteAll/last/truncate/stream) remain for a follow-up.
- Coverage campaign — Lot 4 (DSN): added `OpenEdgeDSNTest` covering the config→DSN string translation — component order, always-on charset, string/int port, int-only numeric guards, `QueryTimeout=0`, property mutation, and the `TypeError` thrown when `scheme` is omitted. Overall line coverage 71.17% → 74.25%; `db` directory 37.5% → 84.7%. Completes Tier A (pure SQL building) — the only remaining 0% in `db` is `OpenEdgePDOBuilder` (needs the ODBC driver).
- Coverage campaign — Lot 3 (query builder): added `OpenEdgeQueryBuilderTest` (select/count building, DISTINCT/TOP, composed-trait state) and `OpenEdgeQueryBuilderTraitTest` (default creation, instance reuse, array config, container resolution by name, init fallback). `db/traits` coverage 93.1% → 98.3%, overall line coverage 67.91% → 71.17%.
- Coverage campaign — Lot 2 (SQL clause traits): added `OrderByTraitTest`, `LookingHintTraitTest`, `BindTraitTest`, `ColumnTraitTest`, `FromTraitTest` and `FacetsTraitTest`; extended `WhereTraitTest` (null init, facets merged into WHERE) and `GroupByTest` (non-array and no-usable-part definitions). `db/traits` coverage 18.3% → 93.1%, overall line coverage 56.03% → 67.91%.
- Coverage campaign — Lot 1 (helpers): added `OrderByExpressionTest` (11 tests) and `RowUrlTest` (5 tests), covering `db/helpers/orderByExpression()` and `db/helpers/rowUrl()` end to end. Line coverage 53.85% → 56.03%.

### Changed
- Dev dependency: bumped `phpunit/phpunit` from `^12` to `^13` (installed 13.2.4). Full suite green — 329 tests, 414 assertions.
- DocumentsGetTrait: use the oihana/logging/DebugTrait trait
- Dependencies: replaced `oihana/php-system` with the focused packages actually consumed — `oihana/php-controllers`, `oihana/php-logging`, `oihana/php-models` and `oihana/php-traits` (namespaces `oihana\controllers`, `oihana\logging`, `oihana\models`, `oihana\traits`). Drops the heavy Slim/Twig/Symfony stack that `php-system` pulled in. No code or public-API change.