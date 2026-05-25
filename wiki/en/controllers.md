# Slim controllers

[`DocumentsController`](../../src/oihana/openedge/controllers/DocumentsController.php) is the HTTP controller shipped by the framework to quickly expose a [`Documents`](models.md) model as Slim routes. It is **deliberately read-only** by doctrine (see [introduction.md](introduction.md#a-doctrine-openedge-is-read-only-over-http)): only `count`, `get` and `list` are exposed.

## Composition

```
DocumentsController
    ├── extends oihana\controllers\Controller (oihana/php-system)
    │
    ├── use DocumentsTrait                  (base helpers shared with arango/openedge)
    │
    ├── use DocumentsControllerCountTrait   → count() HTTP
    ├── use DocumentsControllerGetTrait     → get()   HTTP
    └── use DocumentsControllerListTrait    → list()  HTTP
```

No `POST`, `PATCH`, `PUT`, `DELETE`. The underlying model **has** these methods (see [models.md](models.md)) but the controller doesn't expose them.

## Construction

```php
use DI\Container ;
use app\enums\Controllers ;
use app\enums\Models ;
use oihana\controllers\enums\ControllerParam ;
use oihana\openedge\controllers\DocumentsController ;

Controllers::CUSTOMERS => fn( Container $container ) => new DocumentsController
(
    $container ,
    [
        ControllerParam::MODEL => Models::CUSTOMERS , // model DI identifier
    ]
)
```

The controller resolves its model via the DI container — it doesn't create its own.

## Constructor keys

Keys come from the [`ControllerParam`](https://github.com/BcommeBois/oihana-php-system/blob/main/src/oihana/controllers/enums/ControllerParam.php) enum.

| Key `ControllerParam::*` | Type | Role |
|---|---|---|
| `MODEL` | `string \| Documents` | Model to query. **Required**. |
| `HAS_TOTAL` | `bool` | If `true`, adds `count` to the `list` response (useful for UI pagination). |
| `LIMIT` | `int` | Default limit for `list`. Overridden by `?limit=` HTTP. |
| `FORCE_URL` | `bool` | If `true`, the controller adds the computed `url` field to each document in the response. |
| `OWNER` | `string \| Closure` | For scoped routes, ownership constraint (rarely used on the OpenEdge side). |
| `PARAMS` | `array` | Configuration of accepted HTTP parameters (`limit`, `offset`, `sort`, …) with bounds. |
| `SKINS` | `array` | List of accepted skins. **Unused** on the OpenEdge side — see note below. |
| `SORT_DEFAULT` | `string` | Default sort on the HTTP side. |

> **Note on skins.** The HTTP skin system is not implemented on the OpenEdge side (see [introduction.md](introduction.md)). The `SKINS` key is exposed for consistency with the twin ArangoDB controller, but currently has no effect.

## Exposed HTTP methods

### `list( Request, Response, array $args, array $init = [] )`

Paginated list. Reads standard parameters (`limit`, `offset`, `sort`) from the query string, applies the model, and returns a paginated JSON response.

```http
GET /customers?limit=50&offset=0&sort=-name
```

Code-side:

```php
$documents = $this->model->list
([
    OpenEdge::BINDS      => $bindVars ,
    OpenEdge::CACHEABLE  => $cacheable ,
    OpenEdge::CONDITIONS => $conditions ,
    OpenEdge::FACETS     => $facets ,
    OpenEdge::LIMIT      => $this->prepareLimit  ( $request , $init , $params ) ,
    OpenEdge::OFFSET     => $this->prepareOffset ( $request , $init , $params ) ,
    OpenEdge::SORT       => $this->prepareSort   ( $request , $init , $params ) ,
]) ;
```

If `HAS_TOTAL` is `true`, the controller also calls `$this->model->count(...)` to fill the total in the response.

### `get( Request, Response, array $args )`

Fetch by key.

```http
GET /customers/1274
```

The key is read from `$args` (Slim placeholder) and passed as `OpenEdge::VALUE` to the model.

### `count( Request, Response, array $args, array $init = [] )`

Filtered count.

```http
GET /customers/count?filter[country]=FR
```

> **HTTP filters.** The OpenEdge controller does not yet offer a `?filter={"key":"...","val":"..."}` URL-based system like `oihana/arango`'s. Accepted filters come from the model definition or from Slim `$args`, not from a generic URL filter parser.

## The `DocumentRoute` + `RouteFlag::READ_ONLY` pattern

To expose a model as a route, host applications uses `oihana\routes\DocumentRoute`:

```php
use oihana\routes\DocumentRoute ;
use oihana\routes\Route ;
use oihana\routes\enums\RouteFlag ;

Routes::CUSTOMERS => fn( Container $container ) => new DocumentRoute
(
    $container ,
    [
        Route::ROUTE         => '/customers'          ,
        Route::CONTROLLER_ID => Controllers::CUSTOMERS ,
        Route::FLAGS         => RouteFlag::READ_ONLY            , // ← important
    ],
)
```

`RouteFlag::READ_ONLY` tells the router to expose **only** `GET` (list + get + count). No `POST`, `PATCH`, `PUT`, `DELETE` route is created — that's the mechanism enforcing the "OpenEdge is read-only over HTTP" doctrine.

If a host project decides to expose writes (e.g. an internal admin tool), it can:

1. Remove `RouteFlag::READ_ONLY` from the flag — the router will then expose all CRUD routes…
2. …but the framework's `DocumentsController` doesn't have `post()` / `patch()` / `put()` / `delete()` methods. Either extend the controller on the host side, or wait for an upstream evolution.

This is deliberately left to the host project — the framework's read-only doctrine is explicit and not accidentally bypassable.

## Cross-cutting hooks

`DocumentsController` consumes several `oihana/php-system` traits that handle cross-cutting concerns:

| Trait | Role |
|---|---|
| `CheckOwnerArgumentsTrait` | Validates ownership arguments (`OWNER`) if the controller is scoped. |
| `ForceDocumentUrlTrait` | Adds the `url` field to each document if `FORCE_URL` is `true`. |
| `ModelTrait` | Helper to resolve `$this->model` from the DI container. |
| `OutputDocumentsTrait` | Serialises documents to JSON with the right HTTP headers, handles `?format=` (json, csv, xml…). |
| `ParamsTrait` | Reads and parses query-string parameters. |
| `PrepareBench` | Measures query execution time (for the `X-Bench-Time` header). |
| `PrepareMock` | Enables mock mode (debug only). |
| `PrepareParamTrait` | Bounds and defaults for `limit`, `offset`, `sort`. |
| `StatusTrait` | `$this->fail()` / `$this->success()` methods for error responses. |

## Extending `DocumentsController`

The only case where this controller is extended in a typical host application is to add **business-specific filters** not covered by the generic controller. Example: `StatsController` adds a time-range filter:

```php
namespace app\controllers ;

use oihana\openedge\controllers\DocumentsController ;

class StatsController extends DocumentsController
{
    // Overrides filter preparation to add a 'from' → 'to' range
    protected function prepareConditions( ?Request $request , array $init , array $params ) : array
    {
        $conditions = parent::prepareConditions( $request , $init , $params ) ;
        $from = $request?->getQueryParams()[ 'from' ] ?? null ;
        if ( $from )
        {
            $conditions[] = [ /* business condition */ ] ;
        }
        return $conditions ;
    }
}
```

This is the recommended approach for any HTTP logic beyond simple read CRUD.

## See also

- [`Documents` model](models.md) — model layer consumed by the controller.
- [Introduction — read-only doctrine](introduction.md#a-doctrine-openedge-is-read-only-over-http) — why this controller doesn't expose writes.
- [ODBC connection and multi-database](connection.md) — how the model gets instantiated with its PDO connection.
- [Tips and pitfalls](tips.md) — controller-side golden rules (sortable whitelist, etc.).
