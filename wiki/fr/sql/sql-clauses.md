# Clauses SQL

L'enum [`Clause`](../../../src/oihana/openedge/db/enums/Clause.php) liste les mots-clés SQL que le framework consomme pour assembler une requête. Cette page les énumère et associe chacun à son trait ou helper d'écriture.

## Enum `Clause`

```php
use oihana\openedge\db\enums\Clause ;
```

| Constante | Valeur SQL | Rôle |
|---|---|---|
| `Clause::SELECT` | `SELECT` | Début d'une requête de lecture. |
| `Clause::FROM` | `FROM` | Source de données (table, jointure, sous-requête). |
| `Clause::WHERE` | `WHERE` | Conditions de filtrage. |
| `Clause::GROUP_BY` | `GROUP BY` | Regroupement de lignes. |
| `Clause::HAVING` | `HAVING` | Conditions post-regroupement. |
| `Clause::ORDER_BY` | `ORDER BY` | Tri. |
| `Clause::OFFSET` | `OFFSET` | Pagination — lignes à sauter. |
| `Clause::FETCH` | `FETCH` | Pagination — nombre de lignes à retourner. |
| `Clause::FIRST` | `FIRST` | Synonyme de `NEXT` dans `FETCH FIRST x ROWS`. |
| `Clause::NEXT` | `NEXT` | Synonyme de `FIRST`. |
| `Clause::ONLY` | `ONLY` | Suffixe de `FETCH FIRST x ROWS ONLY`. |
| `Clause::ROW` | `ROW` | Singulier de `ROWS`. |
| `Clause::ROWS` | `ROWS` | Suffixe de `OFFSET x ROWS` et `FETCH FIRST x ROWS`. |
| `Clause::TOP` | `TOP` | Pagination style SQL Server : `SELECT TOP 50`. |
| `Clause::ON` | `ON` | Condition de jointure. |
| `Clause::AS` | `AS` | Alias d'une expression ou d'une table. |
| `Clause::SET` | `SET` | Affectations d'un `UPDATE`. |
| `Clause::VALUES` | `VALUES` | Valeurs d'un `INSERT`. |
| `Clause::COUNT` | `COUNT` | Mot-clé de comptage (utilisé dans le helper `count()` du builder). |
| `Clause::INSERT` | `INSERT INTO` | Début d'une requête d'insertion. |
| `Clause::UPDATE` | `UPDATE` | Début d'une requête de mise à jour. |
| `Clause::DELETE` | `DELETE` | Début d'une requête de suppression. |
| `Clause::WITH` | `WITH` | Préfixe d'un locking hint table-level : `WITH (NOLOCK)`. |
| `Clause::FOR_UPDATE` | `FOR UPDATE` | Verrou exclusif sur les lignes lues. |
| `Clause::TENANT` | `TENANT` | Multi-tenant Progress (rarement utilisé). |
| `Clause::NO_REORDER` | `{ NO REORDER }` | Désactive l'optimisation de l'ordre des jointures. |

## Traits du *query builder*

Le builder [`OpenEdgeQueryBuilder`](../query-builder.md) délègue chaque clause à un trait spécialisé. Cette section les liste avec leur rôle. Chaque trait initialise une propriété publique du même nom au constructeur via `OpenEdge::*`.

### `FromTrait`

Gère la chaîne `FROM` du builder, qui peut inclure les jointures inline.

```php
use oihana\openedge\enums\OpenEdge as SQL ;

new OpenEdgeQueryBuilder([
    SQL::FROM => 'PUB.clients_clients clients LEFT JOIN PUB.pays_pays pays ON clients.cd_pays = pays.cd_pays' ,
]) ;
```

`SQL::FROM` accepte une chaîne, pas un tableau. Le framework ne construit pas dynamiquement la jointure — c'est le développeur qui pré-compile la chaîne. C'est un choix volontaire : les jointures Progress contiennent souvent des particularités (`(+)`, alias case-sensitive, conditions de filtrage déguisées en conditions de jointure) qu'un assembleur générique aurait du mal à modéliser.

### `WhereTrait`

Gère la clause `WHERE`. Accepte une structure récursive (condition simple, groupe `AND`/`OR`, prédicats spéciaux).

```php
SQL::WHERE =>
[
    SQL::LOGIC      => Logic::AND ,
    SQL::CONDITIONS =>
    [
        [ SQL::COLUMN => 'actif'   , SQL::OPERATOR => '=' , SQL::VALUE => 1        ] ,
        [ SQL::COLUMN => 'cd_pays' , SQL::OPERATOR => '=' , SQL::BIND  => 'country' ] ,
    ]
]
```

Voir [Prédicats SQL](sql-predicates.md) pour le détail des formes acceptées.

### `GroupByTrait`

Gère `GROUP BY` et son optionnelle clause `HAVING`.

```php
SQL::GROUP_BY => [ 'cd_pays' , 'segment' ] ,
SQL::HAVING   =>
[
    SQL::COLUMN   => 'cd_pays' ,
    SQL::OPERATOR => '<>'      ,
    SQL::VALUE    => 'XX'      ,
]
```

`SQL::GROUP_BY` accepte une chaîne (une seule colonne) ou un tableau (plusieurs).

### `OrderByTrait`

Gère le tri par défaut **et** la *whitelist* `SORTABLE`.

```php
SQL::ORDER_BY => 'nom_client'         , // tri par défaut côté serveur
SQL::SORTABLE =>
[
    'id'   => 'cd_client'  ,             // ?sort=id → ORDER BY cd_client
    'name' => 'nom_client' ,             // ?sort=name → ORDER BY nom_client
]
```

Quand le contrôleur HTTP reçoit `?sort=name` ou `?sort=-name`, il vérifie que la clé est dans `SORTABLE`. Une clé absente est ignorée silencieusement — c'est la protection anti-injection.

### `ColumnTrait`

Gère la liste de colonnes du `SELECT`. Accepte un tableau de définitions d'expressions :

```php
SQL::COLUMNS =>
[
    [ SQL::COLUMN => 'cd_client'  , SQL::TABLE => 'clients' , SQL::ALIAS => 'id'   ] ,
    [ SQL::COLUMN => 'nom_client' , SQL::TABLE => 'clients' , SQL::ALIAS => 'name' ] ,
    [
        SQL::CONCAT =>
        [
            [ SQL::COLUMN => 'prenom_client' , SQL::TABLE => 'clients' ] ,
            ' '                                                            ,
            [ SQL::COLUMN => 'nom_client'    , SQL::TABLE => 'clients' ] ,
        ],
        SQL::ALIAS => 'fullName' ,
    ],
]
```

Chaque définition passe par le helper [`expression()`](../helpers.md#expression).

### `BindTrait`

Gère les variables bindées de la requête. Le trait expose la propriété `bindVars` (tableau associatif `[ nom => valeur ]`) que PDO reçoit à l'exécution.

```php
$builder->bindVars[ 'country' ] = 'FR' ;
```

En pratique, on n'écrit pas directement dans `bindVars` : on déclare `SQL::BIND => 'country'` dans une condition, et le framework injecte la valeur depuis le contexte d'appel.

### `LookingHintTrait`

Gère les *locking hints* Progress (`NOLOCK`, `READPAST`, …). Voir [*Locking hints*](../progress/locking-hints.md).

```php
use oihana\openedge\db\enums\LockingHint ;

SQL::LOCKING_HINT => LockingHint::WITH_NOLOCK ,
// → SELECT ... FROM table WITH (NOLOCK)
```

### `FacetsTrait`

Gère les éléments optionnels après `WHERE` : `GROUP BY`, `HAVING`, `ORDER BY`, `LIMIT`, `OFFSET`, `DISTINCT`. C'est un trait *meta* qui assemble les autres.

### `OpenEdgeQueryBuilderTrait`

Trait d'initialisation : logger PSR-3, conteneur DI, identifiant de requête (pour les logs).

## Voir aussi

- [Construire une requête SQL pas à pas](sql-building-queries.md) — assemblage d'un SELECT complet.
- [Prédicats SQL](sql-predicates.md) — les sept formes acceptées dans `WHERE` et `HAVING`.
- [Opérateurs SQL](sql-operators.md) — opérateurs autorisés dans une condition.
- [`OpenEdgeQueryBuilder`](../query-builder.md) — détail du *builder* qui compose ces traits.
