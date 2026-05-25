# SQL predicates

A **predicate** is a fragment of a `WHERE` or `HAVING` clause that evaluates to `TRUE`, `FALSE`, or `UNKNOWN` (because of `NULL`s). The framework exposes seven predicate forms, each with a dedicated helper under [`db/helpers/predicates/`](../../../src/oihana/openedge/db/helpers/predicates/).

The [`Predicate`](../../../src/oihana/openedge/db/enums/Predicate.php) enum lists the corresponding keywords:

| Constant | SQL value |
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

## Overview — the seven helpers

| Helper | SQL form produced | When to use |
|---|---|---|
| [`prepareBasicPredicate`](#prepare-basic) | `expr op expr` (=, <>, <, >, …) | Simple binary comparison. |
| [`prepareBetweenPredicate`](#prepare-between) | `expr BETWEEN x AND y` | Inclusive bounded interval. |
| [`prepareInPredicate`](#prepare-in) | `expr IN (a, b, c)` | List membership. |
| [`prepareLikePredicate`](#prepare-like) | `expr LIKE 'pattern%' ESCAPE '\'` | Pattern-based text search. |
| [`prepareNullPredicate`](#prepare-null) | `expr IS [NOT] NULL` | Null test. |
| [`prepareExistPredicate`](#prepare-exist) | `[NOT] EXISTS ( subquery )` | Subquery existence test. |
| [`prepareQuantifiedPredicate`](#prepare-quantified) | `expr op { ANY \| ALL \| SOME } ( subquery )` | Comparison against a set. |

The [`preparePredicate()`](#preparepredicate-facade) facade helper automatically dispatches to the right helper depending on the definition shape.

## `prepareBasicPredicate` {#prepare-basic}

Binary comparison between two expressions.

```php
use oihana\openedge\db\enums\RelationalOperator ;
use oihana\openedge\enums\OpenEdge as SQL ;

[
    SQL::COLUMN   => 'cd_pays'                  ,
    SQL::TABLE    => 'clients'                  ,
    SQL::OPERATOR => RelationalOperator::EQUAL  ,
    SQL::BIND     => 'country'                  ,
]
// → clients.cd_pays = :country
```

Accepted operators: `=`, `<>`, `<`, `>`, `<=`, `>=` (see [`RelationalOperator`](sql-operators.md#relationaloperator)).

## `prepareBetweenPredicate` {#prepare-between}

Inclusive bounded interval.

```php
[
    SQL::COLUMN    => 'dat_crt'             ,
    SQL::TABLE     => 'clients'             ,
    SQL::PREDICATE => Predicate::BETWEEN    ,
    SQL::VALUE     =>
    [
        [ SQL::BIND => 'dateMin' ] ,
        [ SQL::BIND => 'dateMax' ] ,
    ],
]
// → clients.dat_crt BETWEEN :dateMin AND :dateMax
```

`Predicate::NOT_BETWEEN` variant produces `NOT BETWEEN`. Bounds are inclusive in Progress.

## `prepareInPredicate` {#prepare-in}

List membership.

```php
[
    SQL::COLUMN    => 'cd_pays'         ,
    SQL::PREDICATE => Predicate::IN     ,
    SQL::VALUE     => [ 'FR' , 'BE' , 'LU' ] ,
]
// → cd_pays IN ('FR', 'BE', 'LU')
```

For a **parameterised** list (from user input), each value has to be bound — `IN (:c1, :c2, :c3)` — and the matching binds passed at execution. The framework doesn't automatically handle bound lists because the number of values changes per request (which invalidates Progress's query plan).

`Predicate::NOT_IN` variant produces `NOT IN`.

## `prepareLikePredicate` {#prepare-like}

Pattern-based text search. Standard SQL wildcards:

- `%` — zero or more characters
- `_` — exactly one character

```php
[
    SQL::COLUMN    => 'nom_client'        ,
    SQL::PREDICATE => Predicate::LIKE     ,
    SQL::BIND      => 'pattern'           ,
]
// → nom_client LIKE :pattern
```

At execution, build the pattern:

```php
$stmt->execute([ 'pattern' => 'Dur%' ]) ;
```

`Predicate::NOT_LIKE` variant produces `NOT LIKE`. For case-insensitive `LIKE`, combine with `LOWER()` through `SQL::ALTER` (see [helpers.md](../helpers.md#overrideexpression)).

> To escape literal `%` or `_` characters in the pattern, use `Predicate::ESCAPE`:
> ```php
> SQL::PREDICATE => Predicate::LIKE ,
> SQL::BIND      => 'pattern' ,
> SQL::ESCAPE    => '\\' ,
> // → nom_client LIKE :pattern ESCAPE '\'
> ```

## `prepareNullPredicate` {#prepare-null}

Explicit null test.

```php
[
    SQL::COLUMN    => 'cd_pays'           ,
    SQL::PREDICATE => Predicate::NULL     ,
]
// → cd_pays IS NULL

[
    SQL::COLUMN    => 'cd_pays'           ,
    SQL::PREDICATE => Predicate::NOT_NULL ,
]
// → cd_pays IS NOT NULL
```

> **Classic pitfall.** `col = NULL` never works in standard SQL (always evaluates to `UNKNOWN`). Always use `IS NULL` / `IS NOT NULL`.

## `prepareExistPredicate` {#prepare-exist}

Subquery existence test. The subquery content is passed as-is (the framework doesn't build it).

```php
[
    SQL::PREDICATE => Predicate::EXISTS ,
    SQL::QUERY     => 'SELECT 1 FROM PUB.commandes c WHERE c.cd_client = clients.cd_client' ,
]
// → EXISTS ( SELECT 1 FROM PUB.commandes c WHERE c.cd_client = clients.cd_client )
```

`Predicate::NOT_EXISTS` variant produces `NOT EXISTS`.

## `prepareQuantifiedPredicate` {#prepare-quantified}

Comparison against a set returned by a subquery.

```php
use oihana\openedge\db\enums\QuantifiedOperator ;

[
    SQL::COLUMN    => 'montant'                  ,
    SQL::OPERATOR  => RelationalOperator::GREATER_THAN ,
    SQL::QUANTIFIED => QuantifiedOperator::ALL   ,
    SQL::QUERY     => 'SELECT seuil FROM PUB.seuils_alerte' ,
]
// → montant > ALL ( SELECT seuil FROM PUB.seuils_alerte )
```

Three quantifiers available:

- `QuantifiedOperator::ANY` — at least one row of the subquery satisfies.
- `QuantifiedOperator::ALL` — all rows of the subquery satisfy.
- `QuantifiedOperator::SOME` — synonym of `ANY`.

## `preparePredicate()` facade {#preparepredicate-facade}

[`preparePredicate()`](../../../src/oihana/openedge/db/helpers/predicates/preparePredicate.php) dispatches automatically based on the keys present in the definition. It's the helper used internally by `WhereTrait` — you almost never call it directly.

Dispatch rule (priority order):

1. `SQL::QUERY` present + `SQL::QUANTIFIED` → `prepareQuantifiedPredicate`
2. `SQL::QUERY` present + `SQL::PREDICATE in [EXISTS, NOT_EXISTS]` → `prepareExistPredicate`
3. `SQL::PREDICATE` in `[NULL, NOT_NULL]` → `prepareNullPredicate`
4. `SQL::PREDICATE` in `[LIKE, NOT_LIKE]` → `prepareLikePredicate`
5. `SQL::PREDICATE` in `[IN, NOT_IN]` → `prepareInPredicate`
6. `SQL::PREDICATE` in `[BETWEEN, NOT_BETWEEN]` → `prepareBetweenPredicate`
7. Otherwise → `prepareBasicPredicate`

## See also

- [SQL clauses](sql-clauses.md) — `WhereTrait` orchestrating the assembly.
- [SQL operators](sql-operators.md) — relational, logical, quantified operators.
- [Building a SQL query step by step](sql-building-queries.md) — full assembly example.
- [`bindExpression` vs `valueExpression`](../tips.md) — absolute rule for dynamic values.
