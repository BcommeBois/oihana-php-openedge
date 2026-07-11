# Testing

![Language](https://img.shields.io/badge/language-English-blue)

The project has **two complementary layers of tests**:

| Layer | Tool | OpenEdge server / ODBC driver? | Role |
|---|---|---|---|
| **Unit tests** | PHPUnit | No | Validate pure logic: the SQL builder, predicates, casts, string/numeric/date functions, `CASE` expressions and helpers. Fast, run on every commit and in CI. |
| **Live integration tests** | PHPUnit (`#[Group('integration')]`) | Yes (reachable Progress OpenEdge over ODBC) | Validate the driver-facing layer end-to-end: PDO/ODBC factory, `Documents` model, controllers. Opt-in; skipped green when no driver/server is present. |

## Unit tests (PHPUnit)

The suite lives in [`tests/`](../../tests) and runs with:

```shell
composer test                                       # = ./vendor/bin/phpunit
./vendor/bin/phpunit --filter ParseExtentTest       # a single case
```

Configuration: [phpunit.xml](../../phpunit.xml). Key points:

- **Coverage scope**: `./src` only (the `<source>` element).
- **Strict mode**: `failOnWarning`, `failOnRisky`, `failOnSkipped`, `beStrictAboutOutputDuringTests`… A "risky" test (no assertion, produces output, etc.) **fails** the suite on purpose — a test that checks nothing protects nothing.
- **`integration` group excluded** by default: tests that need a real server/driver are tagged `#[Group('integration')]` and do not run with `composer test`.

### What we test, and how

| Tier | Target | Technique |
|---|---|---|
| 1 | Pure SQL builders (`db/helpers/**`, `db/enums/**`) | Input → expected SQL string. No mocks. |
| 2 | SQL clause traits (`db/traits/**`, e.g. `WhereTrait`) | Assert the produced fragment for a given definition array. |
| 3 | Models / controllers | Require a live ODBC connection → covered by the **integration** suite, not the unit suite. |

> **Characterization testing.** When covering existing code, we write tests that describe what the code **actually does**, branch by branch (`if` / `else` / `match`). If a surprising behaviour could be relied upon downstream, we **freeze it in a test** and flag it — we never change a public API without explicit validation.

## Code coverage

PHPUnit measures which lines of `./src` the suite executes. You must **enable Xdebug's coverage mode** (or PCOV); otherwise PHPUnit prints `No tests executed!` and a `XDEBUG_MODE=coverage … has to be set` warning. The `composer` scripts below set the environment variable for you:

```shell
composer coverage       # suite + coverage: text in the terminal, Clover + HTML under build/coverage/
composer coverage:md    # regenerate build/coverage/COVERAGE.md (Markdown summary, red zones first)
```

Output goes to `build/coverage/` — **gitignored, never committed**: a numbers snapshot goes stale at the next commit and pollutes diffs. Regenerate on demand. The Clover → Markdown converter lives in [`tools/clover-to-markdown.php`](../../tools/clover-to-markdown.php) (portable across the `oihana/php-*` libraries — it auto-detects the common source prefix).

### Evolution between runs

Each generation timestamps the report (`Generated at YYYY-MM-DD HH:MM:SS`) and appends a snapshot to `build/coverage/history.json` (also gitignored). On the next run the summary compares against the **previous recorded run** and shows a per-metric delta: `▲ +0.14 pts (+12 lines)` / `▼ -0.30 pts (-5 methods)` / `= ±0.00 pts (+0 lines)`. `history.json` is capped at the last 50 runs and is **local only**: for a shared trend, publish the report from a CI job rather than committing it.

### Reading the report

- **Lines** = the reference metric (% of executed lines).
- An empty bar = code **never tested** → an undetected potential bug.
- ⚠️ **100% ≠ zero bugs.** A line "walked through" without a solid assertion is *covered* but not really *verified*. We aim for tests that **assert a precise result**, not ones that merely pass through the code.

### The 100% ceiling: why it cannot be reached locally

A large part of the library talks to Progress OpenEdge through **PDO + ODBC**, and the Progress ODBC driver **cannot run on a developer's macOS box**. That layer is therefore **structurally uncoverable by the unit suite**, and the coverage report splits cleanly in two:

| Zone | Typical coverage | Reason |
|---|---|---|
| `db/helpers/**` (functional SQL builder) | 80–100% | Pure functions — no driver needed. This is where we push toward ~100%. |
| `db/traits/**` (clause traits) | partial | Some paths assume a live connection. |
| `controllers/**`, `models/**`, `models/traits/documents/**` | 0% locally | PDO/ODBC I/O — only the **integration** suite reaches them. |

So the target is **not** a global 100%: it is ~100% on the pure builder layer, with the driver-facing layer covered by the integration suite wherever a real OpenEdge is available (CI with a driver, staging, the ERP environment).

## Live integration tests (real OpenEdge)

The opt-in suite runs the tests tagged `#[Group('integration')]` against a reachable Progress OpenEdge database over ODBC:

```shell
composer test:integration           # = ./vendor/bin/phpunit -c phpunit-integration.xml
```

Configuration: [phpunit-integration.xml](../../phpunit-integration.xml). It sets `failOnSkipped="false"` so the suite **stays green on a machine without the driver** — the integration tests simply skip. Write these tests so they detect the absence of a reachable server (or the ODBC driver) and `markTestSkipped()` early, rather than failing.

### Connecting to a server

Connection settings live in a TOML file, `configs/config.toml`, **copied from the committed template** [`configs/config.example.toml`](../../configs/config.example.toml). The real `config.toml` is gitignored — no credentials are committed. It uses the same `[odbc]` / `[databases.<name>]` split documented in [connection.md](connection.md):

```toml
[odbc]
scheme       = "odbc"
driver       = "/usr/dlc/odbc/lib/pgoe27.so"
hostName     = "erp.example.com"
charSet      = 106
queryTimeout = 300
logonID      = "reader"
password     = "secret"

[databases.erp]
database   = "erp_database"
portNumber = 20931

# optional smoke query for OpenEdgeConnectionTest
[test]
query = "SELECT COUNT(*) FROM PUB.customer"
```

The shared guard [`RequiresOpenEdgeTrait`](../../tests/oihana/openedge/integration/RequiresOpenEdgeTrait.php) reads it through `oihana\init\initConfig()` (the `oihana/php-init` dev dependency), merges `[odbc]` with the first `[databases.*]` block, builds a real PDO via `OpenEdgePDOBuilder`, and `markTestSkipped()`s when `pdo_odbc` is missing, the file is absent, or the connection throws. Then just:

```shell
cp configs/config.example.toml configs/config.toml   # first time only, then edit it
composer test:integration
```

The reference case [`OpenEdgeConnectionTest`](../../tests/oihana/openedge/integration/OpenEdgeConnectionTest.php) opens a live connection and runs the optional `[test]` smoke query; use it as a template for model/controller integration tests.

## Continuous integration

Two GitHub Actions workflows:

- [`.github/workflows/ci.yml`](../../.github/workflows/ci.yml) — runs `composer validate --strict` and the unit suite on PHP 8.4 for every push to `main` and every pull request. Coverage is off (the driver is not present on the runner).
- [`.github/workflows/docs.yml`](../../.github/workflows/docs.yml) — builds the phpDocumentor site (`composer doc`) and deploys it to GitHub Pages on push to `main`.

## See also

- [Tips and pitfalls](tips.md) — including the local test constraint without an ODBC driver.
- [ODBC connection and multi-database](connection.md) — the `OpenEdgePDOBuilder` factory the integration suite would connect through.
- [ODBC DSN in detail](dsn.md) — `OpenEdgeDSN`, config → DSN mapping, connection troubleshooting.
