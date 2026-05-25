# Agrégats

OpenEdge expose les cinq agrégats SQL standards. L'enum [`AggregateFunction`](../../../src/oihana/openedge/db/enums/functions/AggregateFunction.php) les liste.

> **Référence canonique.** [Progress SQL — Aggregate functions](https://docs.progress.com/bundle/openedge-sql-reference/page/Aggregate-functions_2.html).

## Liste des agrégats

| Constante | SQL | Sens |
|---|---|---|
| `AggregateFunction::COUNT` | `COUNT(expr)` | Nombre de lignes (ou de valeurs non-`NULL` si l'argument est une colonne). |
| `AggregateFunction::SUM` | `SUM(expr)` | Somme. `NULL` si toutes les valeurs sont `NULL`. |
| `AggregateFunction::AVG` | `AVG(expr)` | Moyenne. `NULL` si toutes les valeurs sont `NULL`. |
| `AggregateFunction::MIN` | `MIN(expr)` | Valeur minimale. |
| `AggregateFunction::MAX` | `MAX(expr)` | Valeur maximale. |

## `COUNT(*)` vs `COUNT(col)`

C'est la distinction la plus subtile et la plus utilisée :

- `COUNT(*)` — compte **toutes les lignes** du résultat, y compris celles dont toutes les colonnes sont `NULL`.
- `COUNT(col)` — compte les lignes pour lesquelles `col IS NOT NULL`.
- `COUNT(DISTINCT col)` — compte les valeurs **distinctes non-`NULL`** de `col`.

```sql
SELECT
    COUNT(*)                AS total ,           -- toutes les lignes
    COUNT(country_code)          AS withCountry ,     -- lignes avec un country_code renseigné
    COUNT(DISTINCT country_code) AS distinctCountries -- nombre de pays différents
FROM PUB.customers
```

## Helper `count()` du *query builder*

Le `OpenEdgeQueryBuilder` expose une méthode `count()` qui produit la clause `COUNT(...)` :

```php
use oihana\openedge\db\OpenEdgeQueryBuilder ;
use oihana\openedge\enums\OpenEdge as SQL ;

$builder = new OpenEdgeQueryBuilder([
    SQL::FROM    => 'PUB.customers' ,
    SQL::COUNTER => '*' ,                              // par défaut = '*'
]) ;

echo $builder->count() ;
// COUNT(*)

echo $builder->count([ SQL::COUNTER => 'country_code' ]) ;
// COUNT(country_code)
```

`SQL::COUNTER` reçoit la chaîne à mettre entre les parenthèses (un nom de colonne, `DISTINCT col`, ou `*`).

Côté modèle, la méthode `count()` du modèle [`Documents`](../models.md) consomme ce builder et renvoie un entier PHP :

```php
$total = $customers->count() ;                                 // SELECT COUNT(*) FROM ...
$withCountry = $customers->count([ SQL::COUNTER => 'country_code' ]) ;
```

## `SUM`, `AVG`, `MIN`, `MAX`

Pas de helper PHP dédié — on les écrit directement dans une définition de colonne via `ALTER` :

```php
use oihana\openedge\db\enums\functions\AggregateFunction ;
use oihana\openedge\enums\OpenEdge as SQL ;

SQL::COLUMNS =>
[
    [ SQL::COLUMN => 'country_code' , SQL::ALIAS => 'country' ] ,
    [
        SQL::COLUMN => 'customer_id'                    ,
        SQL::ALTER  => AggregateFunction::COUNT       ,
        SQL::ALIAS  => 'count'                        ,
    ],
    [
        SQL::COLUMN => 'revenue'             ,
        SQL::ALTER  => AggregateFunction::SUM         ,
        SQL::ALIAS  => 'totalRevenue'                 ,
    ],
],
SQL::GROUP_BY => 'country_code' ,
```

Produit l'équivalent SQL :

```sql
SELECT
    country_code            AS "country" ,
    COUNT(customer_id)   AS "count"   ,
    SUM(revenue) AS "totalRevenue"
FROM ...
GROUP BY country_code
```

## Agrégats et `GROUP BY`

Un `SELECT` qui mélange colonnes simples et agrégats **doit** déclarer toutes les colonnes simples dans `GROUP BY`. Sinon Progress retourne une erreur.

```php
SQL::COLUMNS  => [ 'country_code' , 'segment' , [ 'COUNT(*)' , SQL::ALIAS => 'n' ] ] ,
SQL::GROUP_BY => [ 'country_code' , 'segment' ] ,
```

Le framework ne **vérifie pas** automatiquement cette cohérence — c'est au développeur de garantir que `GROUP BY` couvre toutes les colonnes non-agrégées. Une erreur courante : modifier `COLUMNS` sans mettre à jour `GROUP_BY`.

## `HAVING` — filtrer après agrégation

`WHERE` filtre avant l'agrégation, `HAVING` filtre après. La distinction importe : on ne peut pas mettre un agrégat dans un `WHERE`.

```sql
-- Mauvais : WHERE ne peut pas voir SUM(x)
SELECT country_code, SUM(revenue) AS total
FROM PUB.customers
WHERE SUM(revenue) > 100000           -- ERREUR
GROUP BY country_code

-- Bon : HAVING filtre après agrégation
SELECT country_code, SUM(revenue) AS total
FROM PUB.customers
GROUP BY country_code
HAVING SUM(revenue) > 100000          -- OK
```

Côté framework :

```php
SQL::GROUP_BY => 'country_code' ,
SQL::HAVING   =>
[
    SQL::COLUMN   => 'revenue'      ,
    SQL::ALTER    => AggregateFunction::SUM  ,
    SQL::OPERATOR => '>'                     ,
    SQL::VALUE    => 100000                  ,
]
```

## Agrégats sur expressions

Les agrégats acceptent une expression, pas seulement une colonne :

```sql
SUM(net_price * quantite)        -- somme du CA ligne par ligne
AVG(CASE WHEN segment = 'A' THEN net_price ELSE 0 END)
COUNT(CASE WHEN active = 1 THEN 1 END)   -- compte les lignes actives
```

C'est le pattern "agréger conditionnellement" — utile quand on veut plusieurs métriques dans un seul `GROUP BY` sans plusieurs sous-requêtes.

## Voir aussi

- [Construire une requête SQL pas à pas](sql-building-queries.md) — exemple complet avec `GROUP BY` et `HAVING`.
- [Expressions `CASE`](sql-functions-cases.md) — souvent utilisé dans un agrégat conditionnel.
- [Modèle `Documents`](../models.md) — méthode `count()` du modèle.
- [Progress SQL — Aggregate functions](https://docs.progress.com/bundle/openedge-sql-reference/page/Aggregate-functions_2.html) — référence canonique.
