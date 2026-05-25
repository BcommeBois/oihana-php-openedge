# oihana/openedge — Framework Progress OpenEdge pour PHP

![Langue](https://img.shields.io/badge/langue-Français-blue)

`oihana/openedge` est un framework PHP qui industrialise le travail avec [Progress OpenEdge](https://www.progress.com/openedge) via SQL/ODBC : factory PDO, *builder* SQL composable, modèle `Documents` haut-niveau par composition de traits, contrôleurs Slim en lecture seule, et catalogue de helpers pour les fonctions SQL Progress.

> Cette documentation est **en construction active**. Le sommaire ci-dessous reflète l'avancement réel.

## À qui s'adresse cette documentation

Aux développeurs PHP qui exposent ou synchronisent des données Progress OpenEdge depuis une API PHP et qui veulent :

- éviter d'écrire du SQL Progress à la main via du `sprintf` — helpers fonctionnels composables, zéro *magic string* ;
- s'appuyer sur une factory PDO/ODBC déjà câblée avec les attributs Progress qui vont bien ;
- exposer rapidement des routes HTTP **en lecture seule** (catalogue, *reporting*, *thésaurus*) au-dessus d'une base ERP OpenEdge sans réinventer la couche modèle pour chaque table ;
- intégrer OpenEdge dans un conteneur PHP-DI et une application Slim avec une API cohérente avec le reste de l'écosystème `oihana`.

## Démarrage rapide

```php
use oihana\openedge\db\OpenEdgePDOBuilder ;
use oihana\openedge\enums\OpenEdge as SQL  ;
use oihana\openedge\models\Documents       ;

// 1. Factory PDO depuis une config ODBC
$pdo = ( new OpenEdgePDOBuilder
([
    'scheme'   => 'odbc'                          ,
    'driver'   => '/usr/dlc/odbc/lib/pgoe27.so'   ,
    'hostName' => 'erp.example.com'               ,
    'portNumber' => 20931                         ,
    'database' => 'erp_database'                      ,
    'logonID'  => 'reader'                        ,
    'password' => 'secret'                        ,
]) )() ;

// 2. Modèle haut-niveau
$customers = new Documents( $container ,
[
    SQL::PDO   => $pdo                  ,
    SQL::TABLE => 'PUB.customers' ,
]) ;

$list  = $customers->list ([ SQL::LIMIT => 50 ]) ;
$first = $customers->get  ([ 'customer_id' => 1274 ]) ;
```

Pour le détail (DSN ODBC, attributs PDO, *builder* SQL composable, modèles, contrôleurs Slim), voir le sommaire ci-dessous.

## Sommaire

### Fondations

- [Introduction](introduction.md) — qu'est-ce que Progress OpenEdge, philosophie `oihana`, pourquoi cette bibliothèque existe.
- [Dépendances](dependencies.md) — packages `oihana/php-*` requis, snippet `composer require` minimal.
- [Glossaire](glossary.md) — termes clés : ODBC, DSN, *bind*, *literal*, *predicate*, *alter*, *cast*, *outer join (+)*, *locking hint*, *harvest*.

### Démarrer

- [Quickstart OpenEdge](quickstart.md) — première connexion PDO, première requête `SELECT`, premier modèle `Documents`.
- [Connexion ODBC et multi-base](connection.md) — configuration TOML, factory `OpenEdgePDOBuilder`, intégration PHP-DI, multi-base (accounting, common, erp, stats…).
- [DSN ODBC en détail](dsn.md) — classe `OpenEdgeDSN`, mapping config → DSN, attributs PDO réglés par défaut, *troubleshooting* connexion.

### Construire des requêtes SQL

- [Construire une requête SQL pas à pas](sql/sql-building-queries.md) — enchaînement `SELECT → FROM → JOIN → WHERE → GROUP BY → ORDER BY → LIMIT`, exemples.
- [Clauses SQL](sql/sql-clauses.md) — enum `Clause` et traits `FromTrait`, `WhereTrait`, `GroupByTrait`, `OrderByTrait`, `ColumnTrait`, `BindTrait`.
- [Prédicats SQL](sql/sql-predicates.md) — les 7 prédicats (`BETWEEN`, `IN`, `LIKE`, `EXISTS`, `IS NULL`, *quantified*, *basic*).
- [Opérateurs SQL](sql/sql-operators.md) — `RelationalOperator`, `Logic`, `QuantifiedOperator`, `ConcatOperator`.
- [Fonctions de chaînes](sql/sql-functions-strings.md) — 30 helpers (`CONCAT`, `LPAD`, `SUBSTR`, `UPPER`, `proArrayEscape`, …).
- [Fonctions de dates](sql/sql-functions-dates.md) — `CURDATE`, `SYSDATE`, `NOW`, helpers de composition.
- [Fonctions numériques](sql/sql-functions-numerics.md) — 23 helpers (`ABS`, `ROUND`, `MOD`, `POWER`, `GREATEST`, `LEAST`, …).
- [`CAST` et types SQL](sql/sql-functions-casts.md) — `castVARCHAR`, `castINTEGER`, `castTIMESTAMP`, `castDECIMAL`, … (19 cibles).
- [Conversions explicites](sql/sql-functions-conversions.md) — `TO_CHAR`, `TO_DATE`, `TO_NUMBER`, `TO_TIME`, `TO_TIMESTAMP`.
- [Conditionnelles SQL](sql/sql-functions-conditionals.md) — `COALESCE`, `IFNULL`, `NULLIF`, `NVL`, `NULLIF_EMPTY`, `NULLIF_ZERO`.
- [Expressions `CASE`](sql/sql-functions-cases.md) — `whenExpression`, `thenExpression`, `elseExpression`, composition.
- [Agrégats](sql/sql-functions-aggregates.md) — `COUNT`, `SUM`, `AVG`, `MIN`, `MAX`.

### Spécificités Progress

- [Outer join Progress `(+)`](progress/outer-join.md) — syntaxe non-standard, constante `NULLABLE_COLUMN`, exemples.
- [*Locking hints*](progress/locking-hints.md) — `NOLOCK`, `READ COMMITTED`, quand et pourquoi sur un ERP de *reporting*.
- [Timeouts de connexion](progress/timeouts.md) — `connectTimeout`, `serverTimeout`, `updateStatistics` via `OpenEdgeHelperTrait`.
- [Tableaux Progress](progress/arrays.md) — helpers `proArrayEscape`, `proArrayDescape`, `proElement` pour les colonnes `ARRAY` natives Progress.

### Options et configuration

- [Référence des enums](enums.md) — `OpenEdge` (clés d'init), `Clause`, `Predicate`, `Logic`, `Type`, `Facet`, `LockingHint`, 8 catégories de fonctions.
- [Référence des helpers](helpers.md) — `expression`, `bindExpression`, `valueExpression`, `columnExpression`, `literal`, `asAlias`, `searchCondition`, `validateContext`, `overrideExpression`.

### Couche métier

- [Modèle `Documents`](models.md) — architecture par composition de traits, catalogue des clés `OpenEdge::*`, méthodes CRUD, intégration PDO.
- [`OpenEdgeQueryBuilder`](query-builder.md) — *builder* fluent SELECT/COUNT, ses 9 traits, exemples concrets.
- [Contrôleurs Slim](controllers.md) — `DocumentsController` en lecture seule, traits HTTP `Count` / `Get` / `List`, `DocumentRoute` avec `RouteFlag::READ_ONLY`.
- [`Alters` et dénormalisation](alters.md) — pattern `Alter::NORMALIZE` + `Alter::GET` pour résoudre des références cross-base.
- [Modèles `Harvest`](harvest.md) — pattern de modèle source pour la synchronisation OpenEdge → cible (cache, base documentaire).

### Transverse

- [Tips et pièges](tips.md) — règles d'or : `bindExpression` vs `valueExpression` vs `literal`, lecture seule HTTP, *whitelist* `sortable`, contrainte de test local sans driver ODBC.

## Statut du chantier

| Phase | Description | État |
|---|---|---|
| 0 | Fondations — introduction, dépendances, glossaire | *disponible* |
| 1 | Démarrer — quickstart, connection, dsn | *disponible* |
| 2 | Cœur SQL — clauses, prédicats, fonctions | *disponible* |
| 3 | Spécificités Progress — outer join, locking, timeouts, tableaux | *disponible* |
| 4 | Options et enums | *disponible* |
| 5 | Couche métier — modèles, *builder*, contrôleurs, *alters*, *harvest* | *disponible* |
| 6 | Tips et pièges | *disponible* |

## Code source

Le code du framework vit sous [`src/oihana/openedge/`](../../src/oihana/openedge/).

## Voir aussi

- [Documentation officielle Progress OpenEdge SQL](https://docs.progress.com/bundle/openedge-sql-reference/) — référence canonique.
