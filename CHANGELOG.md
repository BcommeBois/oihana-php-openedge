# Changelog

All notable changes to **oihana/php-openedge** are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- Initial scaffold: Composer manifest, PHPUnit 12 + phpDocumentor 3 configuration, MPL-2.0 license, README, CHANGELOG, sibling-aligned folder layout (`src/`, `tests/`, `wiki/`, `assets/`), 132 `autoload.files` entries for the functional helpers.
- Source code under `src/oihana/openedge/` (185 PHP files):
  - `db/` (165 files): a fully functional OpenEdge SQL query builder — column / value / literal / override / concat / search-condition / order-by / limit / cases (`whenThenExpression`, `elseExpression`), 7 predicates (basic, between, exists, in, like, null, quantified), 22 ODBC cast functions (`castBIGINT`, `castVARCHAR`, `castTIMESTAMP`, ...), 23 numeric functions (`abs`, `round`, `power`, `mod`, ...), 31 string functions (`concat`, `substring`, `lpad`, `proArrayEscape`, ...), 6 date functions (`now`, `sysTimestamp`, ...), 5 conversion functions (`toChar`, `toDate`, `toNumber`, `toTime`, `toTimestamp`), 6 conditional functions (`coalesce`, `ifNull`, `nullIf`, `nullIfEmpty`, `nullIfZero`, `nvl`), 6 alter helpers (`alterDate`, `alterKey`, `alterNumeric`, `alterString`, `alterConditional`, `alterConversion`), plus the `OpenEdgeType` enum and the `WhereTrait`.
  - `models/` (15 files): `Documents` model + trait-composed CRUD operations (`DocumentsListTrait`, `DocumentsGetTrait`, `DocumentsCountTrait`, `DocumentsStreamTrait`, `DocumentsUpdateTrait`, ...) wired on PDO + ODBC.
  - `controllers/` (4 files): `DocumentsController` extending the PSR-7 controller stack with list/get/count actions tailored for read-heavy OpenEdge integrations.
  - `enums/` (1 file): typed constants.
- Test suite under `tests/oihana/openedge/` (58 PHP files): unit coverage for the functional helpers (predicates, casts, string/numeric/date/conditional/conversion functions, expression builders). The suite runs without an ODBC driver and is fully green under PHPUnit 12 strict mode.
- Bilingual user guides under `wiki/{fr,en}/`: introduction, quickstart, dependencies, DSN, connection, glossary, helpers, query builder, models, controllers, harvest, tips, enums, alters, plus the `progress/` (driver quirks) and `sql/` (function catalogue) folders.
