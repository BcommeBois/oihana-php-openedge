# Dependencies

`oihana/openedge` relies on a small set of `oihana/php-*` packages that cover the basics (enums, exceptions, reflection) and cross-cutting layers (PDO models, utility traits). It also relies on the PHP `ext-odbc` extension and the Progress SQL ODBC driver shipped by Progress Software, both of which must be installed at the system level.

> All `oihana/*` dependencies are currently versioned as `dev-main`. Stabilisation will happen by cascade when the full graph is ready to be tagged. See the final note.

## System prerequisites

Even before Composer dependencies, `oihana/openedge` needs two things at the system level:

| Prerequisite | Details |
|---|---|
| **PHP `ext-odbc` extension** | Enables `odbc_*` functions and the PDO `PDO_ODBC` driver (`new PDO('odbc:...')`). On Debian/Ubuntu: `apt install php8.4-odbc`. |
| **Progress SQL ODBC driver** | Shipped by Progress Software, paid or included with the ERP license. On 64-bit Linux: `pgoe27.so` binary typically deployed under `/usr/dlc/odbc/lib/`. This is the path you point `[odbc].driver` to in the config. |
| **`unixODBC`** *(optional but recommended)* | Lets you test the connection via `isql` independently from PHP. Not required by `oihana/openedge` directly. |

> **Local development constraint:** the Progress SQL ODBC driver is only shipped for Linux x86_64 and Windows. It **does not exist for macOS**. As a result, the high-level code in `oihana/openedge` (PDO factory, `Documents` model, controller) cannot run locally on a Mac — only the pure SQL helpers (which produce strings) are testable without a connection. See [tips.md](tips.md).

## Composer dependencies

The table below lists the `oihana/*` packages directly consumed by `src/oihana/openedge/`.

| Package | Root namespace | Role |
|---|---|---|
| `oihana/php-enums` | `oihana\enums\` | `Char`, `CharacterSet`, `ConstantsTrait` (constants introspection). All the `OpenEdge`, `Clause`, `Predicate`, `Type`, `LockingHint`, … enums depend on this trait. |
| `oihana/php-exceptions` | `oihana\exceptions\` | Standard framework exception family. |
| `oihana/php-reflect` | `oihana\reflect\` | `ConstantException`, `useConstantsTrait` helpers, validation of constants used in function enums. |
| `oihana/php-system` | `oihana\controllers\`, `oihana\models\`, `oihana\traits\`, `oihana\logging\` | Foundation for HTTP controllers, cross-cutting models (`PDOModel`, `CacheableTrait`, `AlterBindVarsTrait`, `EnsureKeysTrait`), PSR-3 `LoggerTrait`, `ToStringTrait`. |
| `oihana/php-core` | `oihana\core\` | Fundamental helpers: `oihana\core\strings\compile`, `oihana\core\strings\func`, `oihana\core\strings\key`, `oihana\core\strings\betweenDoubleQuotes`, `oihana\core\arrays\isAssociative`. These assemble SQL fragments. |
| `oihana/php-files` | `oihana\files\` | Optional serialised configuration reading. |

> The `oihana/php-system` package uses a wide autoload (`"oihana\\" => "src/oihana"`) that covers multiple root namespaces. In `oihana/openedge`, we mainly consume `oihana\models\pdo\PDOModel` (parent of `Documents`), `oihana\controllers\Controller` (parent of `DocumentsController`), and `oihana\traits\ToStringTrait`.

## Cross-cutting non-`oihana/*` dependencies

`oihana/openedge` doesn't pull in heavy dependencies by itself, but some sub-modules integrate with third-party frameworks the host project must provide.

| Sub-module | Expected external dependency | Notes |
|---|---|---|
| `openedge/controllers/` | `slim/slim` (Slim 4) + a PSR-11 container | The controller consumes `Psr\Http\Message\ServerRequestInterface` and `Psr\Http\Message\ResponseInterface`. |
| `openedge/models/` | a PSR-11 container (PHP-DI used in examples) | The `Documents` constructor receives a `DI\Container`. |

No coupling with Symfony Console: unlike `oihana/arango`, `oihana/openedge` does not ship a dedicated `Command` class. The CLI commands that consume OpenEdge in the host project (typically the `harvest:*` commands) inherit from a parent provided by another module.

## Host application couplings

The package does not depend on any application-specific namespace. The only couplings with the host application are contractual:

- the host application's `definitions/` reference `OpenEdgePDOBuilder` and `Documents` by FQCN;
- business functions like `customerAllColumns()`, `customerFrom()`, `customerWhere()` live on the host side (`app\definitions\openedge\<entity>\`) and consume helpers from the package — not the other way around.

## Minimal `composer require` snippet

For a **full** use of `oihana/openedge` (SQL helpers + `Documents` model + Slim controller):

```bash
composer require \
    oihana/php-enums:dev-main      \
    oihana/php-exceptions:dev-main \
    oihana/php-reflect:dev-main    \
    oihana/php-system:dev-main     \
    oihana/php-core:dev-main       \
    oihana/php-files:dev-main
```

And at the system level:

```bash
# Debian / Ubuntu — PHP ODBC extension
sudo apt install php8.4-odbc unixodbc

# Progress driver (path to adapt to your deployment)
# The pgoe27.so binary ships with Progress, not via apt.
ls /usr/dlc/odbc/lib/pgoe27.so
```

For a **minimal** use (SQL `db/helpers/` layer only, no model, no controller):

```bash
composer require \
    oihana/php-enums:dev-main      \
    oihana/php-reflect:dev-main    \
    oihana/php-core:dev-main
```

For the Slim sub-module:

```bash
composer require slim/slim:^4.0 php-di/php-di:^7.0
```

## Note on versions

All `oihana/*` packages are currently versioned as `dev-main`. As long as a dependency in the graph is on `dev-main`, the package consuming it stays on `dev-main` too — it would be inconsistent to tag `1.0.0` a package that points to `dev-main`. Stabilisation will happen by cascade when the full graph is ready for a tag.

## See also

- [Introduction](introduction.md) — why this library exists.
- [Glossary](glossary.md) — framework terminology.
- [OpenEdge quickstart](quickstart.md) — first working example.
