# Contrôleurs Slim

[`DocumentsController`](../../src/oihana/openedge/controllers/DocumentsController.php) est le contrôleur HTTP livré par le framework pour exposer rapidement un modèle [`Documents`](models.md) sous forme de routes Slim. Il est **volontairement en lecture seule** par doctrine (voir [introduction.md](introduction.md#une-doctrine--openedge-en-lecture-seule-depuis-http)) : seules les méthodes `count`, `get` et `list` sont exposées.

## Composition

```
DocumentsController
    ├── extends oihana\controllers\Controller (oihana/php-system)
    │
    ├── use DocumentsTrait                  (helpers de base partagés avec arango/openedge)
    │
    ├── use DocumentsControllerCountTrait   → count() HTTP
    ├── use DocumentsControllerGetTrait     → get()   HTTP
    └── use DocumentsControllerListTrait    → list()  HTTP
```

Pas de `POST`, `PATCH`, `PUT`, `DELETE`. Le modèle sous-jacent **a** ces méthodes (voir [models.md](models.md)) mais le contrôleur ne les expose pas.

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
        ControllerParam::MODEL => Models::CUSTOMERS , // identifiant DI du modèle
    ]
)
```

Le contrôleur résout son modèle via le conteneur DI — il ne crée pas son propre modèle.

## Clés acceptées au constructeur

Les clés viennent de l'enum [`ControllerParam`](https://github.com/BcommeBois/oihana-php-system/blob/main/src/oihana/controllers/enums/ControllerParam.php).

| Clé `ControllerParam::*` | Type | Rôle |
|---|---|---|
| `MODEL` | `string \| Documents` | Modèle à interroger. **Obligatoire**. |
| `HAS_TOTAL` | `bool` | Si `true`, ajoute `count` dans la réponse `list` (utile pour la pagination UI). |
| `LIMIT` | `int` | Limite par défaut pour `list`. Surchargée par `?limit=` HTTP. |
| `FORCE_URL` | `bool` | Si `true`, le contrôleur ajoute le champ `url` calculé sur chaque document de la réponse. |
| `OWNER` | `string \| Closure` | Pour les routes scoped, contrainte de propriété (rarement utilisé côté OpenEdge). |
| `PARAMS` | `array` | Configuration des paramètres HTTP acceptés (`limit`, `offset`, `sort`, …) avec bornes. |
| `SKINS` | `array` | Liste des *skins* acceptés. **Inutilisé** côté OpenEdge — voir note ci-dessous. |
| `SORT_DEFAULT` | `string` | Tri par défaut côté HTTP. |

> **Note sur les skins.** Le système de *skins* HTTP n'est pas implémenté côté OpenEdge (voir [introduction.md](introduction.md)). La clé `SKINS` est exposée pour cohérence avec le contrôleur ArangoDB jumeau, mais elle reste sans effet pour le moment.

## Méthodes HTTP exposées

### `list( Request, Response, array $args, array $init = [] )`

Liste paginée. Lit les paramètres standards (`limit`, `offset`, `sort`) depuis la query string, applique le modèle, et retourne une réponse JSON paginée.

```http
GET /customers?limit=50&offset=0&sort=-name
```

Côté code :

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

Si `HAS_TOTAL` est `true`, le contrôleur appelle aussi `$this->model->count(...)` pour remplir le total dans la réponse.

### `get( Request, Response, array $args )`

Récupération par clé.

```http
GET /customers/1274
```

La clé est lue dans `$args` (placeholder Slim) et passée comme `OpenEdge::VALUE` au modèle.

### `count( Request, Response, array $args, array $init = [] )`

Comptage filtré.

```http
GET /customers/count?filter[country]=FR
```

> **Filtres HTTP.** Le contrôleur OpenEdge ne propose pas (encore) de système `?filter={"key":"...","val":"..."}` à la `oihana/arango`. Les filtres acceptés viennent de la définition du modèle ou des `$args` Slim, pas d'un parser de filtre URL générique.

## Pattern `DocumentRoute` + `RouteFlag::READ_ONLY`

Pour exposer un modèle en route, l.application hôte utilise `oihana\routes\DocumentRoute` :

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

`RouteFlag::READ_ONLY` indique au routeur d'exposer **uniquement** `GET` (list + get + count). Aucune route `POST`, `PATCH`, `PUT`, `DELETE` n'est créée — c'est le mécanisme qui matérialise la doctrine "OpenEdge en lecture seule depuis HTTP".

Si un projet hôte décide d'exposer l'écriture (par exemple un outil admin interne), il peut :

1. Retirer `RouteFlag::READ_ONLY` du flag — le routeur exposera alors toutes les routes CRUD…
2. …mais le `DocumentsController` du framework n'a pas les méthodes `post()` / `patch()` / `put()` / `delete()`. Il faut soit étendre le contrôleur côté projet hôte, soit attendre une évolution upstream.

C'est volontairement laissé à la charge du projet hôte — la doctrine de lecture seule du framework est explicite et non-contournable par accident.

## Hooks transverses

`DocumentsController` consomme plusieurs traits de `oihana/php-system` qui gèrent les aspects transverses :

| Trait | Rôle |
|---|---|
| `CheckOwnerArgumentsTrait` | Valide les arguments de propriété (`OWNER`) si le contrôleur est *scoped*. |
| `ForceDocumentUrlTrait` | Ajoute le champ `url` sur chaque document si `FORCE_URL` est `true`. |
| `ModelTrait` | Helper pour résoudre `$this->model` depuis le conteneur DI. |
| `OutputDocumentsTrait` | Sérialise les documents en JSON avec les bons headers HTTP, gère `?format=` (json, csv, xml…). |
| `ParamsTrait` | Lit et parse les paramètres de query string. |
| `PrepareBench` | Mesure le temps d'exécution de la requête (pour le header `X-Bench-Time`). |
| `PrepareMock` | Active le mode mock (debug uniquement). |
| `PrepareParamTrait` | Bornes et défauts pour `limit`, `offset`, `sort`. |
| `StatusTrait` | Méthodes `$this->fail()` / `$this->success()` pour les réponses d'erreur. |

## Étendre `DocumentsController`

Le seul cas où l'on étend ce contrôleur dans les applications consommatrices est pour ajouter des **filtres métier spécifiques** non couverts par le contrôleur générique. Exemple : `StatsController` ajoute un filtre par plage temporelle :

```php
namespace app\controllers ;

use oihana\openedge\controllers\DocumentsController ;

class StatsController extends DocumentsController
{
    // Surcharge la préparation des filtres pour ajouter une plage 'from' → 'to'
    protected function prepareConditions( ?Request $request , array $init , array $params ) : array
    {
        $conditions = parent::prepareConditions( $request , $init , $params ) ;
        $from = $request?->getQueryParams()[ 'from' ] ?? null ;
        if ( $from )
        {
            $conditions[] = [ /* condition métier */ ] ;
        }
        return $conditions ;
    }
}
```

C'est l'approche recommandée pour toute logique HTTP qui dépasse le simple CRUD lecture.

## Voir aussi

- [Modèle `Documents`](models.md) — couche modèle consommée par le contrôleur.
- [Introduction — doctrine lecture seule](introduction.md#une-doctrine--openedge-en-lecture-seule-depuis-http) — pourquoi ce contrôleur n'expose pas l'écriture.
- [Connexion ODBC et multi-base](connection.md) — comment le modèle est instancié avec sa connexion PDO.
- [Tips et pièges](tips.md) — règles d'or côté contrôleur (whitelist sortable, etc.).
