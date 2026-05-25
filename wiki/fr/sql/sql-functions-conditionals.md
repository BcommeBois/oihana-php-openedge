# Conditionnelles SQL

Les **conditionnelles SQL** sont les fonctions qui retournent une valeur **différente selon une condition** — typiquement pour remplacer un `NULL` par une valeur par défaut, ou marquer comme `NULL` certaines valeurs sentinelle (chaîne vide, zéro). L'enum [`ConditionalFunction`](../../../src/oihana/openedge/db/enums/functions/ConditionalFunction.php) liste les fonctions Progress disponibles, et le framework fournit six helpers PHP sous [`db/helpers/functions/conditionals/`](../../../src/oihana/openedge/db/helpers/functions/conditionals/).

> **Référence canonique.** [Progress SQL — Conditional expressions](https://docs.progress.com/bundle/openedge-sql-reference/page/CASE.html).

## Vue d'ensemble

| Helper | SQL produit | Quand l'utiliser |
|---|---|---|
| [`coalesce`](#coalesce) | `COALESCE(a, b, c, …)` | Premier non-`NULL`. Standard SQL. |
| [`ifNull`](#ifnull) | `IFNULL(expr, fallback)` | Standard ODBC. Strictement deux arguments. |
| [`nvl`](#nvl) | `NVL(expr, fallback)` | Oracle. Deux arguments. **Non ODBC compatible**. |
| [`nullIf`](#nullif) | `NULLIF(a, b)` | Retourne `NULL` si `a = b`, sinon `a`. |
| [`nullIfEmpty`](#nullifempty) | `NULLIF(expr, '')` | Helper raccourci pour `''` ↔ `NULL`. |
| [`nullIfZero`](#nullifzero) | `NULLIF(expr, 0)` | Helper raccourci pour `0` ↔ `NULL`. |

## `coalesce()` {#coalesce}

Retourne la première expression non-`NULL` parmi la liste fournie.

```php
use function oihana\openedge\db\helpers\functions\conditionals\coalesce ;

echo coalesce([ 'promo_price' , 'net_price' , 0 ]) ;
// COALESCE(promo_price, net_price, 0)
```

Trois cas typiques :

- **Cascade de valeurs** : prix promo si défini, sinon prix HT, sinon zéro.
- **Valeur par défaut affichable** : `COALESCE(customer_name, '(client supprimé)')`.
- **Force un type non-NULL côté API** : très utile quand le contrat API ne tolère pas `null` sur un champ.

Le second argument optionnel est un *callback* qui transforme chaque expression avant insertion :

```php
echo coalesce([ 'name' , 'city' ] , fn( $v ) => "'" . $v . "'" ) ;
// COALESCE('name', 'city')
```

## `ifNull()` {#ifnull}

Retourne `fallback` si `expr` est `NULL`, sinon `expr`. Strictement deux arguments.

```php
use function oihana\openedge\db\helpers\functions\conditionals\ifNull ;

echo ifNull( 'net_price' , 0 ) ;
// IFNULL(net_price, 0)
```

`IFNULL` est ODBC standard ; c'est l'équivalent à deux opérandes de `COALESCE`. Pour plus de deux valeurs, utiliser `COALESCE` directement.

## `nvl()` {#nvl}

Synonyme Oracle de `IFNULL`. À éviter quand la portabilité ODBC compte — la documentation Progress note explicitement que `NVL` n'est pas ODBC compatible.

```php
use function oihana\openedge\db\helpers\functions\conditionals\nvl ;

echo nvl( 'net_price' , 0 ) ;
// NVL(net_price, 0)
```

> Le helper est exposé pour les bases qui consomment `oihana/openedge` via un client non-ODBC ou pour rester compatible avec du SQL ancien écrit en style Oracle. Préférer `IFNULL` ou `COALESCE` autrement.

## `nullIf()` {#nullif}

Retourne `NULL` si `a = b`, sinon `a`. C'est l'opération inverse de `IFNULL`.

```php
use function oihana\openedge\db\helpers\functions\conditionals\nullIf ;

echo nullIf( 'country_code' , "'XX'" ) ;
// NULLIF(country_code, 'XX')
```

Cas typique : une colonne ERP utilise une valeur sentinelle (`'XX'`, `0`, `'N/A'`) pour signifier l'absence — on la remplace par `NULL` au moment de la projection.

## `nullIfEmpty()` {#nullifempty}

Raccourci : `NULLIF(expr, '')`. Très utilisé sur les colonnes `CHAR` Progress, qui sont paddées d'espaces — souvent une valeur stockée comme `'    '` (4 espaces) devrait être traitée comme `NULL` côté API.

```php
use function oihana\openedge\db\helpers\functions\conditionals\nullIfEmpty ;

echo nullIfEmpty( 'description' ) ;
// NULLIF(description, '')
```

> **Attention.** `NULLIF(x, '')` ne traite pas les chaînes de seulement-espaces. Pour ça, combiner avec `LTRIM(RTRIM(x))` côté `ALTERS`.

## `nullIfZero()` {#nullifzero}

Raccourci : `NULLIF(expr, 0)`. Utile pour les colonnes numériques où `0` signifie "non renseigné" côté ERP.

```php
use function oihana\openedge\db\helpers\functions\conditionals\nullIfZero ;

echo nullIfZero( 'industry_code' ) ;
// NULLIF(industry_code, 0)
```

## Composition typique

Le pattern récurrent dans une application hôte typique : `NULLIF` pour normaliser, puis `COALESCE` pour un fallback affichable.

```php
// "Nom du client tronqué et valeur par défaut si vide"
echo coalesce
([
    nullIfEmpty( 'LTRIM(RTRIM(customer_name))' ) ,
    "'(sans nom)'" ,
]) ;
// COALESCE(NULLIF(LTRIM(RTRIM(customer_name)), ''), '(sans nom)')
```

Côté définition de modèle :

```php
use oihana\openedge\db\enums\functions\ConditionalFunction ;
use oihana\openedge\enums\OpenEdge as SQL ;

SQL::COLUMNS =>
[
    [
        SQL::COLUMN => 'description'                      ,
        SQL::TABLE  => 'produits'                         ,
        SQL::ALTER  => ConditionalFunction::NULLIF_EMPTY  , // → NULLIF(products.description, '')
        SQL::ALIAS  => 'description'                      ,
    ],
]
```

> `NULLIF_EMPTY` et `NULLIF_ZERO` sont des constantes **propres au framework** (pas natives Progress). Elles sont reconnues par `overrideExpression()` qui les déplie en `NULLIF(..., '')` ou `NULLIF(..., 0)`. Pratique pour rester dans le système des constantes typées.

## Voir aussi

- [Expressions `CASE`](sql-functions-cases.md) — généralisation de la logique conditionnelle.
- [Conversions](sql-functions-conversions.md) — `TO_*` qui retournent `NULL` sur valeur non-parsable, à combiner avec `COALESCE`.
- [`CAST`](sql-functions-casts.md) — la version stricte (lève au lieu de retourner `NULL`).
- [Progress SQL — Conditional functions](https://docs.progress.com/bundle/openedge-sql-reference/page/CASE.html) — référence canonique.
