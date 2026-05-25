# Opérateurs SQL

Cette page liste les quatre familles d'opérateurs exposées par le framework — relationnels (comparaison), logiques (composition de conditions), quantifiés (sous-requêtes), et de concaténation (chaînes).

## `RelationalOperator` {#relationaloperator}

Comparateurs binaires utilisables dans un prédicat *basic*.

```php
use oihana\openedge\db\enums\RelationalOperator ;
```

| Constante | Valeur SQL | Sens |
|---|---|---|
| `RelationalOperator::EQUAL` | `=` | Égalité. |
| `RelationalOperator::NOT_EQUAL` | `<>` | Différence. Préférer `<>` à `!=` (le SQL standard et Progress acceptent les deux, mais `<>` est plus portable). |
| `RelationalOperator::LESS_THAN` | `<` | Strict inférieur. |
| `RelationalOperator::LESS_THAN_OR_EQUAL` | `<=` | Inférieur ou égal. |
| `RelationalOperator::GREATER_THAN` | `>` | Strict supérieur. |
| `RelationalOperator::GREATER_THAN_OR_EQUAL` | `>=` | Supérieur ou égal. |

Utilisation typique dans une condition :

```php
use oihana\openedge\enums\OpenEdge as SQL ;

[
    SQL::COLUMN   => 'montant'                              ,
    SQL::OPERATOR => RelationalOperator::GREATER_THAN_OR_EQUAL ,
    SQL::BIND     => 'minAmount'                            ,
]
// → montant >= :minAmount
```

## `Logic`

Connecteurs logiques pour composer des conditions imbriquées.

```php
use oihana\openedge\db\enums\Logic ;
```

| Constante | Valeur SQL |
|---|---|
| `Logic::AND` | `AND` |
| `Logic::OR` | `OR` |
| `Logic::NOT` | `NOT` |
| `Logic::AND_NOT` | `AND NOT` |
| `Logic::OR_NOT` | `OR NOT` |

Composition d'un groupe de conditions :

```php
SQL::WHERE =>
[
    SQL::LOGIC      => Logic::AND ,
    SQL::CONDITIONS =>
    [
        [ SQL::COLUMN => 'actif'   , SQL::OPERATOR => '=' , SQL::VALUE => 1 ] ,
        [
            SQL::LOGIC => Logic::OR ,
            SQL::CONDITIONS =>
            [
                [ SQL::COLUMN => 'cd_pays' , SQL::OPERATOR => '=' , SQL::VALUE => 'FR' ] ,
                [ SQL::COLUMN => 'cd_pays' , SQL::OPERATOR => '=' , SQL::VALUE => 'BE' ] ,
            ],
        ],
    ],
]
// → actif = 1 AND ( cd_pays = 'FR' OR cd_pays = 'BE' )
```

L'imbrication peut aller à n niveaux. Le framework parenthèse automatiquement chaque groupe.

## `QuantifiedOperator`

Quantificateurs pour comparer une valeur à un ensemble retourné par une sous-requête.

```php
use oihana\openedge\db\enums\QuantifiedOperator ;
```

| Constante | Valeur SQL | Sens |
|---|---|---|
| `QuantifiedOperator::ANY` | `ANY` | La condition est vraie si **au moins une** ligne de la sous-requête vérifie. |
| `QuantifiedOperator::ALL` | `ALL` | La condition est vraie si **toutes** les lignes vérifient. |
| `QuantifiedOperator::SOME` | `SOME` | Synonyme de `ANY` (SQL standard, peu utilisé en pratique). |

```php
[
    SQL::COLUMN     => 'montant'                              ,
    SQL::OPERATOR   => RelationalOperator::GREATER_THAN       ,
    SQL::QUANTIFIED => QuantifiedOperator::ALL                ,
    SQL::QUERY      => 'SELECT seuil FROM PUB.seuils_alerte'  ,
]
// → montant > ALL ( SELECT seuil FROM PUB.seuils_alerte )
```

Cas d'usage typique : "ce montant est-il supérieur à tous les seuils d'alerte existants ?". Sur de gros volumes, préférer `MAX()` + une comparaison directe : `montant > ( SELECT MAX(seuil) FROM PUB.seuils_alerte )`.

## `ConcatOperator`

Opérateur de concaténation de chaînes SQL standard (`||`). Couvert par l'enum [`ConcatOperator`](../../../src/oihana/openedge/db/enums/ConcatOperator.php) **et** par l'enum [`Operator`](../../../src/oihana/openedge/db/enums/Operator.php) (qui le ré-expose pour rester *backward-compatible*).

```php
use oihana\openedge\db\enums\ConcatOperator ;
```

| Constante | Valeur SQL | Sens |
|---|---|---|
| `ConcatOperator::CONCAT` | `\|\|` | Concaténation simple (pas d'espace autour). |
| `ConcatOperator::CONCAT_WITH_SPACE` | `␣\|\|␣` | Concaténation avec un espace de chaque côté de `\|\|` (pour la lisibilité du SQL généré). |
| `ConcatOperator::CONCAT_WITH_COMMA_SEPARATOR` | `␣\|\|␣','␣\|\|␣` | Concaténation avec une virgule littérale entre les deux opérandes. |

En pratique, on n'utilise presque jamais ces constantes directement : le helper [`concatExpression()`](../helpers.md#concatexpression) et la clé `SQL::CONCAT` ou `SQL::LIST` dans une définition d'expression les utilisent en interne.

```php
echo expression([
    SQL::CONCAT =>
    [
        [ SQL::COLUMN => 'prenom_client' ] ,
        ' '                                  ,
        [ SQL::COLUMN => 'nom_client'    ] ,
    ]
]) ;
// → prenom_client || ' ' || nom_client
```

### Séparateur personnalisé

`ConcatOperator::concatSeparator(';')` retourne `␣||␣';'␣||␣` et permet de construire des chaînes type CSV en SQL :

```php
echo expression([
    SQL::SEPARATOR => ';' ,
    SQL::LIST      =>
    [
        [ SQL::COLUMN => 'prenom_client' ] ,
        [ SQL::COLUMN => 'nom_client'    ] ,
    ]
]) ;
// → prenom_client || ';' || nom_client
```

> Pour concaténer plus proprement, surtout côté Progress, préférer la fonction `CONCAT(a, b)` plutôt que l'opérateur `||` quand on n'a que deux opérandes. Voir [`concat()`](sql-functions-strings.md#concat).

## `Operator`

L'enum [`Operator`](../../../src/oihana/openedge/db/enums/Operator.php) regroupe des opérateurs "transverses" qui ne rentrent pas dans les catégories ci-dessus.

| Constante | Valeur SQL | Sens |
|---|---|---|
| `Operator::ASSIGN` | `=` | Affectation dans un `UPDATE ... SET col = expr`. C'est le même caractère que `EQUAL`, mais le contexte est différent (le framework distingue les deux pour les renommages). |
| `Operator::CONCAT` | `\|\|` | Synonyme de `ConcatOperator::CONCAT` (ré-exposé pour les imports historiques). |
| `Operator::CONCAT_WITH_COMMA_SEPARATOR` | `␣\|\|␣,␣\|\|␣` | Synonyme de `ConcatOperator::CONCAT_WITH_COMMA_SEPARATOR`. |

## Voir aussi

- [Prédicats SQL](sql-predicates.md) — sept formes de prédicat qui consomment ces opérateurs.
- [Construire une requête SQL pas à pas](sql-building-queries.md) — exemple complet d'assemblage `AND`/`OR`.
- [Fonctions de chaînes](sql-functions-strings.md) — fonction `CONCAT()` alternative à `||`.
