# Modèle `Documents`

La classe [`Documents`](../../src/oihana/openedge/models/Documents.php) est la couche haut-niveau du framework côté lecture/écriture d'une table OpenEdge. Elle hérite de `oihana\models\pdo\PDOModel` et compose 17 traits (13 traits CRUD + 3 traits transverses + 1 trait Progress-specific) pour exposer une API uniforme `list / get / count / exist / stream / insert / update / upsert / replace / delete / deleteAll / truncate / last`.

## Architecture en couches

```
Documents (la classe finale)
    ├── extends PDOModel (oihana/php-system)
    │     └── connaît le PDO, le schéma de sortie, le logger
    │
    ├── use AlterBindVarsTrait       (binds normalisés / typés au runtime)
    ├── use CacheableTrait           (cache PSR-16 sur GET/LIST)
    ├── use EnsureKeysTrait          (forcer la présence de clés sur les retours)
    │
    ├── use OpenEdgeQueryBuilderTrait   (initialise $this->openEdge)
    │
    ├── use DocumentsCountTrait      → count()
    ├── use DocumentsListTrait       → list()
    ├── use DocumentsGetTrait        → get()
    ├── use DocumentsLastTrait       → last()
    ├── use DocumentsExistTrait      → exist()
    ├── use DocumentsInsertTrait     → insert()
    ├── use DocumentsUpsertTrait     → upsert()
    ├── use DocumentsUpdateTrait     → update()
    ├── use DocumentsReplaceTrait    → replace()
    ├── use DocumentsDeleteTrait     → delete()
    ├── use DocumentsDeleteAllTrait  → deleteAll()
    ├── use DocumentsTruncateTrait   → truncate()
    ├── use DocumentsStreamTrait     → stream()
    │
    └── use OpenEdgeHelperTrait      → connectTimeout(), serverTimeout(), updateStatistics()
```

Chaque trait CRUD est **autonome** et **isolé** — on peut en lire un seul pour comprendre l'opération. Aucun trait ne dépend d'un autre trait CRUD au runtime ; tous dépendent en revanche du `OpenEdgeQueryBuilderTrait` (qui leur fournit `$this->openEdge`), de `PDOTrait` (qui fournit `$this->pdo` et la méthode `fetch` / `fetchAll`), et de `CacheableTrait` (sur les opérations en lecture).

## Construction

```php
use DI\Container ;
use oihana\models\enums\ModelParam ;
use oihana\openedge\enums\OpenEdge as SQL ;
use oihana\openedge\models\Documents ;

$customers = new Documents( $container ,
[
    ModelParam::PDO           => Databases::ODBC_ERP ,
    ModelParam::SCHEMA        => Customer::class     ,
    ModelParam::CACHE         => Caches::CUSTOMERS , // optionnel
    ModelParam::ALTERS        => [ /* voir alters.md */ ]   , // optionnel
    ModelParam::QUERY_BUILDER =>
    [
        SQL::COLUMNS  => [ /* … */ ] ,
        SQL::FROM     => 'PUB.clients_clients clients' ,
        SQL::WHERE    => [ /* … */ ] ,
        SQL::ORDER_BY => 'nom_client'                  ,
        SQL::SORTABLE => [ /* whitelist */ ]           ,
    ],
]) ;
```

## Clés acceptées au constructeur

Les clés sont définies dans l'enum [`ModelParam`](https://github.com/BcommeBois/oihana-php-system/blob/main/src/oihana/models/enums/ModelParam.php) (de `oihana/php-system`). Les principales :

| Clé `ModelParam::*` | Type | Rôle |
|---|---|---|
| `PDO` | `string \| \PDO` | Connexion PDO ou identifiant DI à résoudre. **Obligatoire**. |
| `SCHEMA` | `string \| Closure \| null` | Classe Schema.org à hydrater pour les retours (ex: `Customer::class`). Optionnel. |
| `CACHE` | `string \| Cache` | Cache PSR-16 ou identifiant DI. Active le cache sur `get` et `list`. |
| `QUERY_BUILDER` | `array` | Configuration du `OpenEdgeQueryBuilder` (clés `OpenEdge::*`). |
| `ALTERS` | `array` | Map de transformations à appliquer après fetch sur les champs du document. Voir [alters.md](alters.md). |
| `BIND_VARS` | `array` | Bind variables par défaut, fusionnées avec celles passées à chaque appel. |
| `THROWABLE` | `bool` | Si `true`, propage les exceptions PDO. Défaut : `false` (logger + null). |

## Les 13 opérations CRUD

### Lecture

#### `list(array $init = []): array`

Liste paginée. Pagination via `OpenEdge::LIMIT` + `OpenEdge::OFFSET`. Tri via `OpenEdge::SORT` (clé publique mappée par `SORTABLE`).

```php
$customers->list([
    SQL::LIMIT  => 50              ,
    SQL::OFFSET => 100             ,
    SQL::SORT   => '-name'         ,        // tri descendant sur 'name'
    SQL::BINDS  => [ 'country' => 'FR' ] ,  // valeurs de bind
]) ;
```

Cache : si `ModelParam::CACHE` est défini, les objets `Thing` ayant un `id` sont automatiquement mis en cache par leur `id` après la liste.

#### `get(array $init = []): mixed`

Récupère **une** ligne. La clé de lookup par défaut est `OpenEdge::ID` (mappée sur la colonne primaire dans le `WHERE` du builder). Peut être surchargée via `OpenEdge::KEY`.

```php
$customer = $customers->get([ SQL::VALUE => 1274 ]) ;
// → fetch sur cd_client = 1274 (selon le WHERE déclaré dans le builder)

// Avec une clé différente
$customer = $customers->get([
    SQL::KEY   => 'email'   ,
    SQL::VALUE => 'a@b.com' ,
]) ;
```

Cache : *cache key* calculée à partir de la classe et des binds via `uniqueKey()`. Personnalisable via `OpenEdge::CACHE_KEY`.

#### `count(array $init = []): int`

Compte les lignes. Accepte les mêmes filtres que `list` :

```php
$count = $customers->count([ SQL::BINDS => [ 'country' => 'FR' ] ]) ;
```

#### `exist(array $init = []): bool`

Vérifie l'existence d'une ligne. Équivalent à `count > 0` mais optimisé (ne ramène pas la valeur).

```php
$exists = $customers->exist([ SQL::VALUE => 1274 ]) ;
```

#### `last(array $init = []): mixed`

Récupère la **dernière** ligne selon le tri par défaut (`ORDER BY ... DESC LIMIT 1`).

```php
$mostRecent = $customers->last() ;
```

#### `stream(array $init = []): Generator`

Itère paresseusement sur le résultat — à utiliser pour les *harvests* qui traitent des millions de lignes sans tout charger en RAM.

```php
foreach ( $customers->stream([ SQL::LIMIT => 100000 ]) as $row )
{
    handle( $row ) ;
}
```

### Écriture

> Toutes les opérations d'écriture sont disponibles côté modèle. Elles ne sont **pas** exposées par le contrôleur HTTP par doctrine (voir [introduction.md](introduction.md#une-doctrine--openedge-en-lecture-seule-depuis-http)) mais utilisables en CLI ou script de migration.

#### `insert(array $document): mixed`

```php
$customers->insert([
    'cd_client'   => 99999       ,
    'nom_client'  => 'NEW CLIENT' ,
    'cd_pays'     => 'FR'        ,
]) ;
```

#### `update(array $init = []): mixed`

```php
$customers->update([
    SQL::VALUE => 1274                          ,
    'data'     => [ 'nom_client' => 'RENAMED' ] ,
]) ;
```

#### `upsert(array $document): mixed`

Insert ou update selon que la clé primaire existe déjà.

#### `replace(array $document): mixed`

Remplace intégralement la ligne (équivalent `DELETE` + `INSERT` atomique côté Progress).

#### `delete(array $init = []): mixed`

```php
$customers->delete([ SQL::VALUE => 99999 ]) ;
```

#### `deleteAll(array $init = []): mixed`

Suppression bulk filtrée par `WHERE`.

#### `truncate(): mixed`

Vide la table (équivalent `TRUNCATE TABLE`).

## Hooks transverses

### `AlterBindVarsTrait`

Permet de **transformer les valeurs des binds** avant l'exécution SQL — typiquement pour normaliser un type ou appliquer une conversion.

```php
ModelParam::BIND_VARS_ALTERS =>
[
    'country' => fn( $v ) => strtoupper( (string) $v ) , // valeurs en uppercase
    'date'    => fn( $v ) => $v?->format( 'Y-m-d' )    , // DateTime → string
]
```

Appliqué **par méthode** (`list` / `get` / `count` / etc.) : le contexte est passé pour différencier les transformations selon l'opération.

### `CacheableTrait`

Active le cache PSR-16 sur `get` et `list`. Quatre méthodes exposées : `hasCache`, `getCache`, `setCache`, `clearCache`. Le cache est invalidé manuellement par le développeur — pas d'invalidation automatique sur écriture.

### `EnsureKeysTrait`

Garantit que les clés attendues sont présentes dans les objets retournés. Utile quand la sortie est forcée à un schéma Schema.org dont certains champs ne sont pas dans le `SELECT` — `EnsureKeysTrait` les initialise à `null` après hydratation.

```php
ModelParam::ENSURE_KEYS =>
[
    Prop::AREA_SERVED ,
    Prop::WEBSITE     ,
]
```

### `OpenEdgeHelperTrait`

Exposé sur le modèle pour donner accès aux trois méthodes Progress-specific :

- `connectTimeout(int $delay)` — règle le timeout client de la connexion en cours.
- `serverTimeout(int $delay)` — règle le timeout serveur.
- `updateStatistics(string $table)` — recalcule les statistiques d'une table.

Voir [Timeouts de connexion](progress/timeouts.md).

## Le pattern d'externalisation des colonnes / FROM / WHERE

En pratique, on n'écrit pas le `QUERY_BUILDER` inline dans le DI : on externalise dans des fonctions PHP nommées par entité.

Convention recommandée : sous `app\definitions\openedge\<entity>\`, on trouve un fichier par bloc :

```
app/definitions/openedge/customers/
├── customerAllColumns.php  ← fonction qui retourne le tableau de colonnes
├── customerFrom.php        ← fonction qui retourne la chaîne FROM
├── customerWhere.php       ← fonction qui retourne le WHERE par défaut
```

```php
use function app\definitions\openedge\customers\customerAllColumns ;
use function app\definitions\openedge\customers\customerFrom       ;
use function app\definitions\openedge\customers\customerWhere      ;

new Documents( $container ,
[
    ModelParam::PDO    => Databases::ODBC_ERP ,
    ModelParam::SCHEMA => Customer::class     ,
    ModelParam::QUERY_BUILDER =>
    [
        SQL::COLUMNS  => customerAllColumns() ,
        SQL::FROM     => customerFrom()       ,
        SQL::WHERE    => customerWhere()      ,
        SQL::ORDER_BY => 'name'               ,
        SQL::SORTABLE => [ /* … */ ]          ,
    ],
])
```

Bénéfices :

- Réutilisation entre modèles (le modèle "Customer normal" et le modèle "Customer harvest" partagent leur `FROM` et leur `WHERE`).
- Lisibilité du DI (la définition tient sur dix lignes).
- Tests unitaires possibles sur les fonctions de génération de colonnes — sans monter de modèle PDO.
- Surcharge facile via les paramètres : `customerAllColumns( extraColumns: [...] )` ajoute des colonnes spécifiques au modèle harvest.

## Voir aussi

- [`OpenEdgeQueryBuilder`](query-builder.md) — détail du *builder* sous-jacent et de ses 9 traits.
- [`Alters` et dénormalisation](alters.md) — système de transformations post-fetch.
- [Modèles `Harvest`](harvest.md) — pattern de modèle source pour la synchronisation.
- [Contrôleurs Slim](controllers.md) — comment exposer un modèle en route HTTP lecture seule.
- [Référence des enums](enums.md) — catalogue des clés `OpenEdge::*` et `ModelParam::*`.
