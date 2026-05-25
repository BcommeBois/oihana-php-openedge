# Construire une requête SQL pas à pas

Cette page enchaîne un cas concret pour montrer comment les helpers et les enums du framework s'assemblent pour produire un `SELECT` Progress complet, avec colonnes typées, jointures, conditions paramétrées, tri et pagination. Le cas pris en exemple : exposer une liste de clients depuis une table `PUB.customers`, jointe à un thésaurus de pays, avec filtre optionnel par nom et tri sécurisé.

## Le pipeline SELECT

Une requête `SELECT` OpenEdge enchaîne sept clauses dans cet ordre :

```
SELECT      ← colonnes projetées, distinct, top
FROM        ← table(s) et jointures
WHERE       ← conditions
GROUP BY    ← regroupement
HAVING      ← conditions post-regroupement
ORDER BY    ← tri
OFFSET/FETCH ← pagination
```

Le framework expose un helper ou un trait pour chacune de ces clauses, et un [`OpenEdgeQueryBuilder`](../query-builder.md) qui les agrège.

## Étape 1 — projeter des colonnes

Une colonne, dans le framework, n'est pas une chaîne brute : c'est une expression. La forme la plus simple :

```php
use function oihana\openedge\db\helpers\expression ;
use oihana\openedge\enums\OpenEdge as SQL ;

echo expression([
    SQL::COLUMN => 'customer_name'  ,
    SQL::TABLE  => 'clients'     ,
]) ;
// clients.customer_name
```

On peut ajouter un *cast*, un alias, marquer la colonne comme *nullable* (pour un outer join Progress) :

```php
use oihana\openedge\db\enums\Type ;

echo expression([
    SQL::COLUMN => 'country_code'                ,
    SQL::TABLE  => 'clients'                ,
    SQL::CAST   => [ Type::VARCHAR , 3 ]    ,
    SQL::NULLABLE => true                   ,
]) ;
// CAST(clients.country_code AS VARCHAR(3))(+)
```

> Le suffixe `(+)` est la **syntaxe outer join Progress** déclenchée par `SQL::NULLABLE => true`. Voir [Outer join Progress](../progress/outer-join.md).

### Concaténer plusieurs colonnes en une expression

```php
echo expression([
    SQL::CONCAT =>
    [
        [ SQL::COLUMN => 'first_name' , SQL::TABLE => 'clients' ] ,
        ' '                                                            ,
        [ SQL::COLUMN => 'customer_name'    , SQL::TABLE => 'clients' ] ,
    ]
]) ;
// clients.first_name || ' ' || clients.customer_name
```

### Lister plusieurs colonnes en chaîne séparée

```php
echo expression([
    SQL::SEPARATOR => ';' ,
    SQL::LIST      =>
    [
        [ SQL::COLUMN => 'first_name' , SQL::TABLE => 'clients' ] ,
        [ SQL::COLUMN => 'customer_name'    , SQL::TABLE => 'clients' ] ,
    ]
]) ;
// clients.first_name || ';' || clients.customer_name
```

## Étape 2 — le `FROM` et les jointures

```php
use oihana\openedge\db\enums\Clause ;
use oihana\openedge\db\enums\Join   ;

$from = 'PUB.customers clients'
      . ' '
      . Join::LEFT
      . ' PUB.countries pays '
      . Clause::ON
      . ' clients.country_code = pays.country_code' ;
```

Cette concaténation manuelle marche, mais en pratique on déclare le `FROM` directement dans la définition du *query builder* :

```php
SQL::FROM => 'PUB.customers clients LEFT JOIN PUB.countries pays ON clients.country_code = pays.country_code'
```

Le framework ne reconstruit pas le `FROM` à partir de morceaux — c'est une chaîne précompilée à l'initialisation.

> Astuce : externaliser le `FROM` dans une fonction nommée par entité (`customerFrom()`) sous `app\definitions\openedge\<entity>\` rend les definitions DI lisibles. Voir le pattern dans [models.md](../models.md).

## Étape 3 — la clause `WHERE` avec un *bind*

Le `WHERE` accepte des conditions exprimées sous forme structurée. La condition la plus simple : `colonne = :bind`.

```php
use oihana\openedge\db\enums\RelationalOperator ;
use function oihana\openedge\db\helpers\bindExpression ;

// Côté builder, on déclare le WHERE :
SQL::WHERE =>
[
    SQL::COLUMN    => 'country_code'                    ,
    SQL::TABLE     => 'clients'                    ,
    SQL::OPERATOR  => RelationalOperator::EQUAL    ,
    SQL::BIND      => 'country'                    , // produit :country côté SQL
]
// → clients.country_code = :country

// Côté exécution, on passe la valeur du bind à PDO :
$stmt->execute([ 'country' => 'FR' ]) ;
```

> **Règle absolue.** Toute valeur dynamique passe par `SQL::BIND`. Jamais d'`inline` avec `SQL::VALUE` ni de `literal()` pour une valeur utilisateur. Voir [tips.md](../tips.md) sur le risque d'injection.

Pour combiner plusieurs conditions, on emboîte avec un opérateur logique :

```php
use oihana\openedge\db\enums\Logic ;

SQL::WHERE =>
[
    SQL::LOGIC      => Logic::AND ,
    SQL::CONDITIONS =>
    [
        [ SQL::COLUMN => 'country_code' , SQL::TABLE => 'clients' , SQL::OPERATOR => '=' , SQL::BIND => 'country' ] ,
        [ SQL::COLUMN => 'active'   , SQL::TABLE => 'clients' , SQL::OPERATOR => '=' , SQL::VALUE => 1        ] ,
    ]
]
```

Le détail des sept formes de prédicat est couvert dans [Prédicats SQL](sql-predicates.md).

## Étape 4 — `GROUP BY` et `HAVING`

```php
SQL::GROUP_BY => 'country_code' ,
SQL::HAVING   =>
[
    SQL::COLUMN   => 'country_code'  ,
    SQL::OPERATOR => '<>'       ,
    SQL::VALUE    => 'XX'       ,
] ,
```

Les agrégats côté SELECT (`COUNT`, `SUM`, …) sont des helpers à part entière, voir [Agrégats](sql-functions-aggregates.md).

## Étape 5 — `ORDER BY` avec *whitelist*

Le tri est **toujours** validé contre une *whitelist* `SORTABLE` du builder. Cette *whitelist* mappe une clé publique vers un nom de colonne réel :

```php
SQL::ORDER_BY => 'name' ,            // valeur par défaut côté serveur
SQL::SORTABLE =>
[
    'id'      => 'customer_id'  ,      // ?sort=id → ORDER BY customer_id
    'name'    => 'customer_name' ,      // ?sort=name → ORDER BY customer_name
    'country' => 'country_code'    ,
]
```

Trois propriétés importantes :

- Une clé absente de `SORTABLE` est **silencieusement ignorée**. C'est la protection anti-injection sur le paramètre `?sort=`.
- La clé publique peut être différente du nom Progress (`name` ↔ `customer_name`). Permet d'exposer une API stable même si la table est renommée.
- La direction `?sort=-name` (préfixe `-` pour `DESC`) est gérée côté contrôleur, pas dans le builder.

## Étape 6 — pagination

Progress supporte les deux syntaxes :

```sql
-- Style SQL Server
SELECT TOP 50 * FROM PUB.customers

-- Style SQL standard
SELECT * FROM PUB.customers OFFSET 0 ROWS FETCH FIRST 50 ROWS ONLY
```

Côté builder, on passe `SQL::LIMIT` et `SQL::OFFSET` à la méthode `list()` du modèle, et le framework choisit la forme appropriée.

```php
$customers->list([ SQL::LIMIT => 50 , SQL::OFFSET => 100 ]) ;
```

## Tout assemblé — definition DI réelle

Voici à quoi ressemble une définition de modèle complète, telle qu'elle vit dans une application hôte typique :

```php
use app\enums\Databases ;
use app\enums\Models    ;
use app\enums\Prop      ;
use oihana\models\enums\ModelParam ;
use oihana\openedge\enums\OpenEdge as SQL ;
use oihana\openedge\models\Documents ;

use function app\definitions\openedge\customers\customerAllColumns ;
use function app\definitions\openedge\customers\customerFrom       ;
use function app\definitions\openedge\customers\customerWhere      ;

Models::CUSTOMERS => fn( Container $container ) => new Documents
(
    $container ,
    [
        ModelParam::PDO    => Databases::ODBC_ERP ,
        ModelParam::SCHEMA => Customer::class     ,
        ModelParam::QUERY_BUILDER =>
        [
            SQL::COLUMNS  => customerAllColumns()  , // fonction qui retourne le tableau de colonnes
            SQL::FROM     => customerFrom()        , // chaîne FROM + JOIN
            SQL::WHERE    => customerWhere()       , // tableau de conditions par défaut
            SQL::ORDER_BY => Prop::NAME            ,
            SQL::SORTABLE =>
            [
                Prop::ID               => Prop::ID  ,
                Prop::NAME             => Prop::NAME ,
                Prop::CREATED          => Prop::CREATED ,
                Prop::MODIFIED         => Prop::MODIFIED ,
                Prop::ADDRESS_LOCALITY => Prop::ADDRESS_LOCALITY ,
                Prop::ADDRESS_COUNTRY  => Prop::ADDRESS_COUNTRY  ,
            ],
        ]
    ]
)
```

> **Pattern à retenir.** Externaliser `COLUMNS`, `FROM` et `WHERE` dans des fonctions PHP nommées (`<entity>AllColumns()`, `<entity>From()`, `<entity>Where()`) plutôt que de tout écrire inline. Ça rend les definitions DI lisibles et le SQL réutilisable entre modèles.

## Voir aussi

- [Clauses SQL](sql-clauses.md) — détail des traits FROM / WHERE / GROUP BY / ORDER BY.
- [Prédicats SQL](sql-predicates.md) — sept formes de prédicat.
- [Opérateurs SQL](sql-operators.md) — relationnels, logiques, quantifiés, concat.
- [`OpenEdgeQueryBuilder`](../query-builder.md) — détail du *builder* sous-jacent.
- [Modèle `Documents`](../models.md) — comment le modèle consomme la définition.
