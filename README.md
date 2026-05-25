# Oihana PHP OpenEdge

![Oihana PHP OpenEdge](https://raw.githubusercontent.com/BcommeBois/oihana-php-openedge/main/assets/images/oihana-php-openedge-logo-inline-512x160.png)

Composable PHP toolkit for the [Progress OpenEdge](https://www.progress.com/openedge) SQL database, accessed via ODBC. Part of the **Oihana PHP** ecosystem, this package bundles a functional query builder (predicates, casts, string/numeric/date functions, CASE expressions), document/edge models composed by traits, PSR-7 CRUD controllers, and Symfony Console commands — everything you need to build a read-heavy ERP integration end-to-end.

[![Latest Version](https://img.shields.io/packagist/v/oihana/php-openedge.svg?style=flat-square)](https://packagist.org/packages/oihana/php-openedge)
[![Total Downloads](https://img.shields.io/packagist/dt/oihana/php-openedge.svg?style=flat-square)](https://packagist.org/packages/oihana/php-openedge)
[![License](https://img.shields.io/packagist/l/oihana/php-openedge.svg?style=flat-square)](LICENSE)

## 📚 Documentation

Full API reference (generated with phpDocumentor): `https://bcommebois.github.io/oihana-php-openedge`

User guides (FR + EN) live under [`wiki/`](wiki/).

## 📦 Installation

Requires [PHP 8.4+](https://php.net/releases/) with the `ext-pdo` extension, and the [Progress DataDirect OpenEdge ODBC driver](https://www.progress.com/datadirect-connectors) installed on the host running the application. Install via [Composer](https://getcomposer.org/):

```bash
composer require oihana/php-openedge
```

## ✨ What you can do

- **Build OpenEdge SQL queries** with a fully functional API — column expressions, value/literal expressions, ORDER BY / LIMIT / GROUP BY, search conditions, predicates (basic, between, exists, in, like, null, quantified) — without ever concatenating strings.
- **Cover every OpenEdge SQL function** — string (`concat`, `substring`, `lpad`, `proArrayEscape`, ...), numeric (`abs`, `round`, `power`, `mod`, ...), date (`now`, `sysTimestamp`, ...), conditional (`coalesce`, `ifNull`, `nvl`, ...), conversion (`toChar`, `toDate`, `toNumber`, ...), and the full ODBC cast catalogue (`castBIGINT`, `castVARCHAR`, `castTIMESTAMP`, ...).
- **Compose CASE expressions** declaratively — `whenThenExpression(...)`, `elseExpression(...)`, no string templates.
- **Apply value mutators** through the `alters/` pipeline — `alterDate`, `alterKey`, `alterNumeric`, `alterString`, `alterConditional`, `alterConversion`.
- **Persist and load documents** through trait-composed models — `Documents`, `DocumentsList`, `DocumentsGet`, `DocumentsCount`, `DocumentsStream` etc. — with `bindExpression` placeholders and `SORTABLE` whitelisting baked in.
- **Plug controllers** into any [Slim](https://www.slimframework.com/)-compatible PSR-15 stack via `DocumentsController` (list, get, count) — read-only by default to match the typical ERP integration profile.

### Under the hood

- A consistent set of typed enums and constants — `OpenEdgeFunctionType`, `OpenEdgeOperator`, etc. — no magic strings anywhere.
- Pure functional helpers, registered through Composer `autoload.files` so they are always available without `use` boilerplate.
- PDO-based — uses the standard `ext-pdo` + ODBC driver path, so it works the same way on Linux (DataDirect driver) and Windows (Progress ODBC driver) hosts.
- Schema.org-friendly — models extend `org\schema\Thing` from [`oihana/php-schema`](https://github.com/BcommeBois/oihana-php-schema), so JSON-LD output is natural.

## ✅ Running tests

Run all tests:

```bash
composer test
```

Run a specific test file:

```bash
composer test ./tests/oihana/openedge/db/helpers/functions/casts/CastVarCharTest.php
```

The unit tests cover the pure functional helpers — they do **not** require an ODBC driver or a live OpenEdge server. End-to-end smoke tests against a real OpenEdge instance live in the consuming application.

## 🛠️ Generate the documentation

We use [phpDocumentor](https://phpdoc.org/) to generate documentation into the `./docs` folder.

```bash
composer doc
```

## 🧾 License

Licensed under the [Mozilla Public License 2.0 (MPL‑2.0)](https://www.mozilla.org/en-US/MPL/2.0/).

## 👤 About the author

- Author: Marc ALCARAZ (aka eKameleon)
- Email: `marc@ooop.fr`
- Website: `https://www.ooop.fr`

## 🔗 Related packages

| Package | Description |
| --- | --- |
| [oihana/php-arango](https://github.com/BcommeBois/oihana-php-arango) | Composable toolkit for ArangoDB — document/edge models, AQL helpers, controllers. |
| [oihana/php-auth](https://github.com/BcommeBois/oihana-php-auth) | Casbin RBAC + JWT/OIDC authorization toolkit. |
| [oihana/php-commands](https://github.com/BcommeBois/oihana-php-commands) | Symfony Console kernel and reusable command traits. |
| [oihana/php-core](https://github.com/BcommeBois/oihana-php-core) | Core helpers and utilities shared across the ecosystem. |
| [oihana/php-enums](https://github.com/BcommeBois/oihana-php-enums) | Typed constants and enums — no more magic strings. |
| [oihana/php-exceptions](https://github.com/BcommeBois/oihana-php-exceptions) | Framework exceptions with consistent semantics. |
| [oihana/php-http](https://github.com/BcommeBois/oihana-php-http) | HTTP helpers — client IP, cookies, route patterns. |
| [oihana/php-reflect](https://github.com/BcommeBois/oihana-php-reflect) | Reflection and object hydration utilities. |
| [oihana/php-schema](https://github.com/BcommeBois/oihana-php-schema) | Schema.org constants and vocabulary. |
| [oihana/php-system](https://github.com/BcommeBois/oihana-php-system) | Framework helpers — controllers, models, request handling. |
