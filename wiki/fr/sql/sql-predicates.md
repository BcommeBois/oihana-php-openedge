# Prédicats SQL

Un **prédicat** est un fragment d'une clause `WHERE` ou `HAVING` qui s'évalue à `TRUE`, `FALSE` ou `UNKNOWN` (à cause des `NULL`). Le framework expose sept formes de prédicat, chacune avec son helper dédié sous [`db/helpers/predicates/`](../../../src/oihana/openedge/db/helpers/predicates/).

L'enum [`Predicate`](../../../src/oihana/openedge/db/enums/Predicate.php) liste les mots-clés correspondants :

| Constante | Valeur SQL |
|---|---|
| `Predicate::ALL` | `ALL` |
| `Predicate::DISTINCT` | `DISTINCT` |
| `Predicate::EXISTS` | `EXISTS` |
| `Predicate::NOT_EXISTS` | `NOT EXISTS` |
| `Predicate::IN` | `IN` |
| `Predicate::NOT_IN` | `NOT IN` |
| `Predicate::LIKE` | `LIKE` |
| `Predicate::NOT_LIKE` | `NOT LIKE` |
| `Predicate::BETWEEN` | `BETWEEN` |
| `Predicate::NOT_BETWEEN` | `NOT BETWEEN` |
| `Predicate::NULL` | `IS NULL` |
| `Predicate::NOT_NULL` | `IS NOT NULL` |
| `Predicate::ESCAPE` | `ESCAPE` |

## Vue d'ensemble — sept helpers

| Helper | Forme SQL produite | Quand l'utiliser |
|---|---|---|
| [`prepareBasicPredicate`](#prepare-basic) | `expr op expr` (=, <>, <, >, …) | Comparaison binaire simple. |
| [`prepareBetweenPredicate`](#prepare-between) | `expr BETWEEN x AND y` | Intervalle borné inclus. |
| [`prepareInPredicate`](#prepare-in) | `expr IN (a, b, c)` | Appartenance à une liste. |
| [`prepareLikePredicate`](#prepare-like) | `expr LIKE 'pattern%' ESCAPE '\'` | Recherche textuelle par motif. |
| [`prepareNullPredicate`](#prepare-null) | `expr IS [NOT] NULL` | Test de nullité. |
| [`prepareExistPredicate`](#prepare-exist) | `[NOT] EXISTS ( subquery )` | Test d'existence d'une sous-requête. |
| [`prepareQuantifiedPredicate`](#prepare-quantified) | `expr op { ANY \| ALL \| SOME } ( subquery )` | Comparaison à un ensemble. |

Le helper *façade* [`preparePredicate()`](#preparepredicate-façade) dispatch automatiquement vers le bon helper selon la forme de la définition.

## `prepareBasicPredicate` {#prepare-basic}

Comparaison binaire entre deux expressions.

```php
use oihana\openedge\db\enums\RelationalOperator ;
use oihana\openedge\enums\OpenEdge as SQL ;

[
    SQL::COLUMN   => 'country_code'                  ,
    SQL::TABLE    => 'clients'                  ,
    SQL::OPERATOR => RelationalOperator::EQUAL  ,
    SQL::BIND     => 'country'                  ,
]
// → clients.country_code = :country
```

Opérateurs acceptés : `=`, `<>`, `<`, `>`, `<=`, `>=` (voir [`RelationalOperator`](sql-operators.md#relationaloperator)).

## `prepareBetweenPredicate` {#prepare-between}

Intervalle borné inclus.

```php
[
    SQL::COLUMN    => 'created_at'             ,
    SQL::TABLE     => 'clients'             ,
    SQL::PREDICATE => Predicate::BETWEEN    ,
    SQL::VALUE     =>
    [
        [ SQL::BIND => 'dateMin' ] ,
        [ SQL::BIND => 'dateMax' ] ,
    ],
]
// → clients.created_at BETWEEN :dateMin AND :dateMax
```

Variante `Predicate::NOT_BETWEEN` produit `NOT BETWEEN`. Les bornes sont inclusives côté Progress.

## `prepareInPredicate` {#prepare-in}

Appartenance à une liste.

```php
[
    SQL::COLUMN    => 'country_code'         ,
    SQL::PREDICATE => Predicate::IN     ,
    SQL::VALUE     => [ 'FR' , 'BE' , 'LU' ] ,
]
// → country_code IN ('FR', 'BE', 'LU')
```

Pour une liste **paramétrée** (issue d'un input utilisateur), il faut binder chaque valeur — `IN (:c1, :c2, :c3)` — et passer les *binds* correspondants à l'exécution. Le framework ne gère pas automatiquement les listes en *bind* parce que le nombre de valeurs change à chaque requête (ce qui invalide le plan de requête côté Progress).

Variante `Predicate::NOT_IN` produit `NOT IN`.

## `prepareLikePredicate` {#prepare-like}

Recherche textuelle par motif. Wildcards SQL standard :

- `%` — zéro ou plus caractères
- `_` — exactement un caractère

```php
[
    SQL::COLUMN    => 'customer_name'        ,
    SQL::PREDICATE => Predicate::LIKE     ,
    SQL::BIND      => 'pattern'           ,
]
// → customer_name LIKE :pattern
```

Côté exécution, on construit le pattern :

```php
$stmt->execute([ 'pattern' => 'Dur%' ]) ;
```

Variante `Predicate::NOT_LIKE` produit `NOT LIKE`. Pour un *LIKE* insensible à la casse, combiner avec `LOWER()` via `SQL::ALTER` (voir [helpers.md](../helpers.md#overrideexpression)).

> Pour échapper littéralement `%` ou `_` dans le motif, utiliser `Predicate::ESCAPE` :
> ```php
> SQL::PREDICATE => Predicate::LIKE ,
> SQL::BIND      => 'pattern' ,
> SQL::ESCAPE    => '\\' ,
> // → customer_name LIKE :pattern ESCAPE '\'
> ```

## `prepareNullPredicate` {#prepare-null}

Test de nullité explicite.

```php
[
    SQL::COLUMN    => 'country_code'           ,
    SQL::PREDICATE => Predicate::NULL     ,
]
// → country_code IS NULL

[
    SQL::COLUMN    => 'country_code'           ,
    SQL::PREDICATE => Predicate::NOT_NULL ,
]
// → country_code IS NOT NULL
```

> **Piège classique.** `col = NULL` ne marche jamais en SQL standard (s'évalue toujours à `UNKNOWN`). Toujours utiliser `IS NULL` / `IS NOT NULL`.

## `prepareExistPredicate` {#prepare-exist}

Test d'existence d'une sous-requête. Le contenu de la sous-requête est passé tel quel (le framework ne le construit pas).

```php
[
    SQL::PREDICATE => Predicate::EXISTS ,
    SQL::QUERY     => 'SELECT 1 FROM PUB.orders c WHERE c.customer_id = clients.customer_id' ,
]
// → EXISTS ( SELECT 1 FROM PUB.orders c WHERE c.customer_id = clients.customer_id )
```

Variante `Predicate::NOT_EXISTS` produit `NOT EXISTS`.

## `prepareQuantifiedPredicate` {#prepare-quantified}

Comparaison à un ensemble retourné par une sous-requête.

```php
use oihana\openedge\db\enums\QuantifiedOperator ;

[
    SQL::COLUMN    => 'amount'                  ,
    SQL::OPERATOR  => RelationalOperator::GREATER_THAN ,
    SQL::QUANTIFIED => QuantifiedOperator::ALL   ,
    SQL::QUERY     => 'SELECT threshold FROM PUB.alert_thresholds' ,
]
// → amount > ALL ( SELECT threshold FROM PUB.alert_thresholds )
```

Trois quantificateurs disponibles :

- `QuantifiedOperator::ANY` — au moins une ligne de la sous-requête vérifie.
- `QuantifiedOperator::ALL` — toutes les lignes de la sous-requête vérifient.
- `QuantifiedOperator::SOME` — synonyme d'`ANY`.

## `preparePredicate()` façade {#preparepredicate-façade}

[`preparePredicate()`](../../../src/oihana/openedge/db/helpers/predicates/preparePredicate.php) dispatch automatiquement vers le bon helper selon les clés présentes dans la définition. C'est le helper utilisé en interne par `WhereTrait` — on n'a presque jamais à l'appeler directement.

Règle de dispatch (ordre de priorité) :

1. `SQL::QUERY` présent + `SQL::QUANTIFIED` → `prepareQuantifiedPredicate`
2. `SQL::QUERY` présent + `SQL::PREDICATE in [EXISTS, NOT_EXISTS]` → `prepareExistPredicate`
3. `SQL::PREDICATE` dans `[NULL, NOT_NULL]` → `prepareNullPredicate`
4. `SQL::PREDICATE` dans `[LIKE, NOT_LIKE]` → `prepareLikePredicate`
5. `SQL::PREDICATE` dans `[IN, NOT_IN]` → `prepareInPredicate`
6. `SQL::PREDICATE` dans `[BETWEEN, NOT_BETWEEN]` → `prepareBetweenPredicate`
7. Sinon → `prepareBasicPredicate`

## Voir aussi

- [Clauses SQL](sql-clauses.md) — `WhereTrait` qui orchestre l'assemblage des prédicats.
- [Opérateurs SQL](sql-operators.md) — opérateurs relationnels, logiques, quantifiés.
- [Construire une requête SQL pas à pas](sql-building-queries.md) — exemple complet d'assemblage.
- [`bindExpression` vs `valueExpression`](../tips.md) — règle absolue pour les valeurs dynamiques.
