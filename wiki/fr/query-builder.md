# `OpenEdgeQueryBuilder`

La classe [`OpenEdgeQueryBuilder`](../../src/oihana/openedge/db/OpenEdgeQueryBuilder.php) est le *builder* SQL qui sous-tend le modèle [`Documents`](models.md). Elle compose 9 traits clauses pour produire les fragments d'une requête (`SELECT`, `FROM`, `WHERE`, …) à partir d'une configuration typée.

Côté usage, on n'instancie presque jamais ce *builder* directement — le modèle s'en charge à partir de la clé `ModelParam::QUERY_BUILDER`. Cette page documente le *builder* pour deux audiences :

- **Maintenance** — comprendre comment le modèle compose son SQL, savoir où regarder quand on debug une requête mal formée.
- **Usage avancé** — instancier un *builder* à part pour produire du SQL en dehors du modèle (par exemple pour un export ad-hoc, un script CLI sans modèle, ou pour pré-compiler une *subquery* à passer en `OpenEdge::QUERY`).

## Composition

```
OpenEdgeQueryBuilder
    ├── use BindTrait             ($bindVars + bind() + alterBindVars)
    ├── use ColumnTrait           (columns + columnList())
    ├── use FromTrait             ($from + from())
    ├── use GroupByTrait          ($groupBy + groupBy())
    ├── use OrderByTrait          ($orderBy + $sortable + orderBy())
    ├── use WhereTrait            ($where + where())
    ├── use LookingHintTrait      ($lockingHint + withLockingHint())
    ├── use LoggerTrait           (logger PSR-3)
    └── use ToStringTrait         (compatibilité Stringable)
```

Le constructeur initialise toutes les propriétés à partir d'un tableau de clés `OpenEdge::*` :

```php
use oihana\openedge\db\OpenEdgeQueryBuilder ;
use oihana\openedge\enums\OpenEdge as SQL  ;

$builder = new OpenEdgeQueryBuilder
([
    SQL::CONTAINER    => $container        ,
    SQL::COUNTER      => '*'               , // pour count(*)
    SQL::COLUMNS      => [ /* … */ ]       ,
    SQL::DISTINCT     => false             ,
    SQL::FROM         => 'PUB.clients clients' ,
    SQL::GROUP_BY     => null              ,
    SQL::JOINS        => null              ,
    SQL::LOCKING_HINT => 'WITH (NOLOCK)'   ,
    SQL::NO_REORDER   => false             ,
    SQL::ORDER_BY     => 'nom_client'      ,
    SQL::QUERY        => null              , // si défini, court-circuite tout le reste
    SQL::SORTABLE     => [ /* whitelist */ ] ,
    SQL::WHERE        => [ /* … */ ]       ,
]) ;
```

## Propriétés publiques

Les propriétés sont publiques (héritage du style "PHP fluent" du framework) — on peut donc les modifier après le constructeur.

| Propriété | Type | Rôle |
|---|---|---|
| `$container` | `?Container` | Conteneur DI. Utilisé par certains traits pour résoudre des services. |
| `$counter` | `string` | Argument de `COUNT()`. Défaut `'*'`. |
| `$columns` | `array` | Colonnes du `SELECT`. |
| `$distinct` | `bool` | Si `true`, ajoute `DISTINCT` après `SELECT`. |
| `$from` | `?string` | Chaîne `FROM ... [JOIN ...]`. |
| `$groupBy` | `?string\|array` | Colonne(s) de regroupement. |
| `$joins` | `?array` | Définitions de jointures (rarement utilisé — préférer `$from` en string). |
| `$lockingHint` | `?string` | *Locking hint* table-level. |
| `$noReorder` | `bool` | Si `true`, ajoute `{ NO REORDER }` au `FROM` pour désactiver l'optimisation des jointures. |
| `$orderBy` | `?string\|array` | Tri par défaut. |
| `$query` | `?string` | Si non-nul, court-circuite tout l'assemblage : la requête est utilisée telle quelle. Pratique pour des requêtes complexes pré-écrites à la main. |
| `$sortable` | `?array` | *Whitelist* des champs autorisés en tri public (clé publique → nom Progress). |
| `$where` | `?string\|array` | Conditions par défaut. |

## Méthodes publiques

### `select( array|string|null $init = null ): string`

Construit la clause `SELECT [DISTINCT] [TOP n]`.

```php
echo $builder->select() ;
// SELECT

echo $builder->select([ SQL::DISTINCT => true ]) ;
// SELECT DISTINCT

echo $builder->select([ SQL::TOP => 50 ]) ;
// SELECT TOP 50
```

Si on passe une `string`, elle est retournée telle quelle — court-circuit pour passer un fragment custom.

### `count( array $init = [] ): string`

Construit la clause `COUNT(...)`. L'argument est lu depuis `SQL::COUNTER` ou la propriété `$this->counter`.

```php
echo $builder->count() ;
// COUNT(*)

echo $builder->count([ SQL::COUNTER => 'cd_pays' ]) ;
// COUNT(cd_pays)
```

### `columnList( array $init = [] ): string`

Construit la liste des colonnes du `SELECT`. Délègue à [`columnExpression()`](helpers.md#columnexpression) et [`asAlias()`](helpers.md#asalias) pour chaque colonne.

### `from( array $init = [] ): string`

Construit la clause `FROM`. Retourne `'FROM ' . $this->from` (avec interpolation éventuelle).

### `where( array $init , array &$bindVars , string $context ): string`

Construit la clause `WHERE`. Modifie `$bindVars` par référence pour collecter les valeurs des binds rencontrés.

### `groupBy( array $init = [] ): string`

Construit la clause `GROUP BY`. Gère aussi le `HAVING` si présent.

### `orderBy( array $init = [] ): string`

Construit la clause `ORDER BY`. Si `OpenEdge::SORT` est passé en `$init`, on parse `orderByExpression()` contre `$this->sortable`. Sinon, on retombe sur `$this->orderBy` par défaut.

### `withLockingHint( array $init = [] ): string`

Construit le *locking hint* à coller après le `FROM`. Voir [*Locking hints*](progress/locking-hints.md).

### `bind( array $init = [] ): array`

Retourne le tableau de bind variables collectées par les autres clauses. Utilisé en interne par le modèle.

### `toString(): string`

Compile l'ensemble en une seule chaîne SQL. Hérité de `ToStringTrait`. Très utile pour debug : `echo (string) $builder ;`.

## Court-circuit via `SQL::QUERY`

Quand la propriété `$query` (ou `SQL::QUERY` au constructeur) est non-nulle, **tout l'assemblage est court-circuité** : la requête est utilisée telle quelle. Utile dans deux cas :

1. **Requête complexe pré-écrite à la main** — par exemple un `WITH ... AS (...) SELECT ... FROM ...` Progress avancé que les traits ne savent pas modéliser.
2. **Requête générée par une *subquery* externe** — par exemple le contenu d'une `EXISTS ( ... )` construit par un autre *builder* puis injecté.

```php
$builder = new OpenEdgeQueryBuilder([
    SQL::QUERY => 'SELECT cd_client FROM PUB.clients_clients WHERE actif = 1' ,
]) ;
```

Dans ce cas, les autres propriétés sont ignorées — sauf `$lockingHint` qui est toujours appliqué après. Et les bind variables (`SQL::BINDS`) sont toujours acceptées par le modèle.

## Pattern : instancier un *builder* hors modèle

Cas d'usage : un script CLI veut produire une requête personnalisée à *ad hoc* sans monter un modèle complet.

```php
use oihana\openedge\db\OpenEdgeQueryBuilder ;
use oihana\openedge\enums\OpenEdge as SQL  ;

$builder = new OpenEdgeQueryBuilder
([
    SQL::COLUMNS => [
        [ SQL::COLUMN => 'cd_pays' , SQL::ALIAS => 'country' ] ,
        [ SQL::COLUMN => 'COUNT(*)' , SQL::ALIAS => 'n' ] ,
    ],
    SQL::FROM     => 'PUB.clients_clients' ,
    SQL::GROUP_BY => 'cd_pays' ,
    SQL::ORDER_BY => 'cd_pays' ,
]) ;

$bindVars = [] ;
$query = compile([
    $builder->select() ,
    $builder->columnList() ,
    $builder->from() ,
    $builder->where([] , $bindVars , 'list') ,
    $builder->groupBy() ,
    $builder->orderBy() ,
]) ;

$stmt = $pdo->prepare( $query ) ;
$stmt->execute( $bindVars ) ;
```

> Pour un usage CLI répété, mieux vaut quand même monter un modèle `Documents` autour du *builder* — on profite du cache, des `ALTERS`, du logger et de la gestion des erreurs.

## Debug d'une requête mal formée

Le *builder* logge les requêtes en mode debug. Activer via `$init[OpenEdge::DEBUG] = true` dans l'appel au modèle :

```php
$customers->list([
    SQL::DEBUG => true ,
    SQL::SORT  => '-name' ,
]) ;
```

Sortie typique :

```
query    : SELECT clients.cd_client AS "id", clients.nom_client AS "name" FROM PUB.clients_clients clients ORDER BY nom_client DESC FETCH FIRST 50 ROWS ONLY
bindVars : {"country":"FR"}
```

Permet de vérifier que la requête générée est celle qu'on attend, et de la copier dans `isql` pour la tester en isolation.

## Voir aussi

- [Modèle `Documents`](models.md) — consommateur principal du *builder*.
- [Clauses SQL](sql/sql-clauses.md) — détail de chaque trait clause.
- [Référence des helpers](helpers.md) — `expression`, `columnExpression`, `asAlias`, etc.
- [Référence des enums](enums.md) — catalogue des clés `OpenEdge::*` acceptées.
