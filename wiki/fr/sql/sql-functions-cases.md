# Expressions `CASE`

L'expression `CASE` SQL est la généralisation des conditionnelles : elle retourne une valeur **parmi N** selon une cascade de conditions. Quand `COALESCE` et `NULLIF` ne suffisent pas (typiquement quand la condition n'est pas un simple test de nullité), c'est l'outil à utiliser.

Le framework expose quatre helpers sous [`db/helpers/cases/`](../../../src/oihana/openedge/db/helpers/cases/) plus un composer central [`caseExpression()`](../../../src/oihana/openedge/db/helpers/caseExpression.php).

> **Référence canonique.** [Progress SQL — CASE](https://docs.progress.com/bundle/openedge-sql-reference/page/CASE.html).

## Les deux formes de `CASE`

### Forme simple

```sql
CASE primary_expr
    WHEN value1 THEN result1
    WHEN value2 THEN result2
    ELSE result_default
END
```

Compare `primary_expr` à chaque `valueN`. Plus concis quand la condition est toujours une égalité.

### Forme à conditions explicites (*searched*)

```sql
CASE
    WHEN condition1 THEN result1
    WHEN condition2 THEN result2
    ELSE result_default
END
```

Chaque `condition` peut être n'importe quel prédicat (`x > 100`, `y IS NULL`, `name LIKE '%-%'`). Plus puissant, plus verbeux.

## Helpers `db/helpers/cases/`

### `whenExpression( condition )`

```php
use function oihana\openedge\db\helpers\cases\whenExpression ;

echo whenExpression( "prix_ht > 100" ) ;
// WHEN prix_ht > 100
```

### `thenExpression( value )`

```php
use function oihana\openedge\db\helpers\cases\thenExpression ;

echo thenExpression( "'cher'" ) ;
// THEN 'cher'
```

### `elseExpression( value )`

```php
use function oihana\openedge\db\helpers\cases\elseExpression ;

echo elseExpression( "'pas cher'" ) ;
// ELSE 'pas cher'
```

### `whenThenExpression( condition , value )`

Compose `WHEN ... THEN ...` en un seul appel, plus pratique.

```php
use function oihana\openedge\db\helpers\cases\whenThenExpression ;

echo whenThenExpression( "prix_ht > 100" , "'cher'" ) ;
// WHEN prix_ht > 100 THEN 'cher'
```

## `caseExpression()` — composer global

Le helper [`caseExpression()`](../../../src/oihana/openedge/db/helpers/caseExpression.php) construit l'expression complète à partir d'un tableau structuré.

```php
use oihana\openedge\enums\OpenEdge as SQL ;
use function oihana\openedge\db\helpers\expression ;

echo expression([
    SQL::CASE =>
    [
        SQL::WHEN =>
        [
            [ "prix_ht > 100" , "'cher'"   ] ,
            [ "prix_ht > 50"  , "'moyen'"  ] ,
        ],
        SQL::ELSE => "'pas cher'" ,
    ]
]) ;
// CASE
//     WHEN prix_ht > 100 THEN 'cher'
//     WHEN prix_ht > 50 THEN 'moyen'
//     ELSE 'pas cher'
// END
```

### Forme simple

Quand toutes les conditions sont des égalités sur la même expression, on peut utiliser la forme simple :

```php
echo expression([
    SQL::CASE =>
    [
        SQL::EXPRESSION => 'segment' ,
        SQL::WHEN =>
        [
            [ "'A'" , "'gold'"   ] ,
            [ "'B'" , "'silver'" ] ,
            [ "'C'" , "'bronze'" ] ,
        ],
        SQL::ELSE => "'standard'" ,
    ]
]) ;
// CASE segment
//     WHEN 'A' THEN 'gold'
//     WHEN 'B' THEN 'silver'
//     WHEN 'C' THEN 'bronze'
//     ELSE 'standard'
// END
```

## Pattern d'usage — calcul de catégorie tarifaire

Dans les applications consommatrices, ce pattern est utilisé pour calculer une catégorie côté SQL (plus rapide que de remonter le prix et catégoriser côté PHP) :

```php
use oihana\openedge\db\enums\Type ;
use oihana\openedge\enums\OpenEdge as SQL ;

SQL::COLUMNS =>
[
    [
        SQL::ALIAS => 'priceCategory' ,
        SQL::CASE  =>
        [
            SQL::WHEN =>
            [
                [ "produits.prix_ht >= 1000" , "'premium'"  ] ,
                [ "produits.prix_ht >= 100"  , "'standard'" ] ,
            ],
            SQL::ELSE => "'budget'" ,
        ],
    ],
]
```

## Pattern alternatif — `DECODE` ou `IFNULL`

Quand la `CASE` est seulement un mapping fixe (`A → X`, `B → Y`, `C → Z`), on peut utiliser :

- **`DECODE(...)`** — syntaxe Oracle native exposée par `ConditionalFunction::DECODE`, mais **non ODBC compatible**. Pas de helper PHP dédié dans le framework — préférer `CASE`.
- **`COALESCE` + `NULLIF`** — sympa quand la logique se réduit à "garder la valeur sauf si elle vaut X, alors mettre Y".

Pour une logique réellement conditionnelle (plusieurs branches non-équivalentes), `CASE` reste l'option lisible et standard.

## Imbrication

`CASE` peut être imbriqué dans n'importe quelle expression — y compris un autre `CASE`. C'est puissant mais ça devient vite illisible. À partir de trois niveaux, mieux vaut déplacer la logique côté code PHP.

```sql
CASE
    WHEN segment = 'A' THEN
        CASE
            WHEN region = 'EU' THEN 'gold-eu'
            ELSE 'gold-other'
        END
    ELSE 'standard'
END
```

## Voir aussi

- [Conditionnelles SQL](sql-functions-conditionals.md) — `COALESCE`, `IFNULL`, `NULLIF` pour les cas simples.
- [Construire une requête SQL pas à pas](sql-building-queries.md) — exemple complet.
- [Helpers](../helpers.md#expression) — le helper `expression()` qui dispatche vers `caseExpression()`.
- [Progress SQL — CASE](https://docs.progress.com/bundle/openedge-sql-reference/page/CASE.html) — référence canonique.
