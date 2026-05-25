# Référence des helpers

Le framework expose une quinzaine de helpers fonctionnels sous [`db/helpers/`](../../src/oihana/openedge/db/helpers/) (racine du dossier — hors `predicates/`, `cases/`, `functions/` qui sont couverts dans leurs pages dédiées). Cette page les énumère et donne pour chacun la signature, l'effet et un exemple.

> Convention : 1 fichier = 1 fonction. Tous ces helpers sont autoloadés par Composer (`composer.json` section `files`). Pas besoin de `require` manuel.

## Vue d'ensemble

| Helper | Rôle |
|---|---|
| [`expression`](#expression) | Point d'entrée polymorphe, dispatche selon la forme de la définition. |
| [`bindExpression`](#bindexpression) | Produit `:nom` pour un placeholder PDO. |
| [`valueExpression`](#valueexpression) | Produit un fragment de valeur (littéral ou expression spéciale). |
| [`literal`](#literal) | Produit un littéral SQL `'…'` avec échappement des quotes. |
| [`columnExpression`](#columnexpression) | Produit une colonne qualifiée avec `CAST`, `ALTER`, *nullable*. |
| [`asAlias`](#asalias) | Suffixe `AS "alias"` ou `AS alias`. |
| [`concatExpression`](#concatexpression) | Concaténation de plusieurs expressions via `\|\|`. |
| [`caseExpression`](#caseexpression) | Construit un `CASE WHEN … END`. |
| [`searchCondition`](#searchcondition) | Construit la clause `WHERE` ou `ON` à partir de conditions structurées. |
| [`overrideExpression`](#overrideexpression) | Applique en cascade `LITERAL` → `CAST` → `ALTER` → `ALTERS`. |
| [`validateContext`](#validatecontext) | Vérifie qu'une clé `OpenEdge::*` est utilisée dans un contexte autorisé. |
| [`limit`](#limit) | Produit `OFFSET x ROWS FETCH NEXT y ROWS ONLY`. |
| [`orderByExpression`](#orderbyexpression) | Parse une expression `?sort=` et la valide contre une *whitelist*. |
| [`openEdgeType`](#openedgetype) | Valide qu'un type est bien dans `Type::*` et formate avec `(length)` ou `(precision, scale)`. |
| [`rowUrl`](#rowurl) | Construit une URL canonique pour une ligne (URN-style). |

## `expression()` {#expression}

**Le point d'entrée le plus utilisé.** Accepte une définition polymorphe et dispatche vers le bon helper :

| Clé présente | Dispatch |
|---|---|
| `OpenEdge::BIND` | [`bindExpression`](#bindexpression) → `:nom` |
| `OpenEdge::VALUE` | [`valueExpression`](#valueexpression) → littéral ou expression spéciale |
| `OpenEdge::CASE` | [`caseExpression`](#caseexpression) → `CASE WHEN …` |
| `OpenEdge::CONCAT` / `OpenEdge::ARRAY` / `OpenEdge::LIST` | [`concatExpression`](#concatexpression) → `a \|\| b \|\| c` |
| Sinon | [`columnExpression`](#columnexpression) → colonne qualifiée |

```php
use oihana\openedge\enums\OpenEdge as SQL ;
use function oihana\openedge\db\helpers\expression ;

// Colonne
expression([ SQL::COLUMN => 'name' , SQL::TABLE => 'clients' ]) ;
// → clients.name

// Bind
expression([ SQL::BIND => 'userId' ]) ;
// → :userId

// Concaténation
expression([
    SQL::CONCAT =>
    [
        [ SQL::COLUMN => 'first' ] ,
        ' '                          ,
        [ SQL::COLUMN => 'last'  ] ,
    ]
]) ;
// → first || ' ' || last
```

Un scalaire (non-tableau) passé à `expression()` est traité comme littéral via [`literal()`](#literal).

## `bindExpression()` {#bindexpression}

Produit `:nom` pour un placeholder PDO. À utiliser pour **toute valeur dynamique** (input utilisateur). Voir [tips.md](tips.md) sur la règle absolue.

```php
use function oihana\openedge\db\helpers\bindExpression ;

bindExpression([ OpenEdge::BIND => 'userId' ]) ;
// → :userId

// Avec une alteration
bindExpression([
    OpenEdge::BIND  => 'price' ,
    OpenEdge::ALTER => 'ROUND' ,
]) ;
// → ROUND(:price)
```

## `valueExpression()` {#valueexpression}

Produit un fragment de valeur **côté serveur**. Reconnaît quelques valeurs spéciales (constantes de date/heure courantes), sinon délègue à `literal()`.

```php
use oihana\openedge\db\enums\functions\DateFunction ;
use function oihana\openedge\db\helpers\valueExpression ;

valueExpression([ OpenEdge::VALUE => DateFunction::NOW ]) ;
// → NOW()

valueExpression([ OpenEdge::VALUE => DateFunction::CURDATE ]) ;
// → CURDATE()

valueExpression([ OpenEdge::VALUE => 'admin' ]) ;
// → 'admin'
```

Valeurs spéciales reconnues : `DateFunction::CURDATE`, `CURTIME`, `NOW`, `SYSDATE`, `SYSTIME`, `SYSTIMESTAMP`, `NumericFunction::PI`.

> **Règle.** À utiliser pour des **constantes côté serveur** (date du jour, π, etc.), jamais pour des valeurs issues d'input utilisateur — préférer `bindExpression` dans ce cas. Voir [tips.md](tips.md).

## `literal()` {#literal}

Produit un littéral SQL `'…'`, en échappant les quotes simples par doublement (style SQL standard).

```php
use function oihana\openedge\db\helpers\literal ;

literal( 'hello' ) ;        // 'hello'
literal( "O'Hare" ) ;       // 'O''Hare'
literal( 42 ) ;             // 42
literal( true ) ;           // true
```

> **Attention.** `literal()` ne valide pas le type, il échappe seulement. Pour les dates et heures, utiliser plutôt `literalExpression` avec le bon `Literal::*` qui produit la syntaxe `{ d '…' }`, `{ t '…' }`, `{ ts '…' }`.

## `columnExpression()` {#columnexpression}

Produit une colonne qualifiée par sa table, avec optionnellement un `CAST`, un `ALTER`, et le suffixe Progress `(+)` si la colonne est marquée *nullable*.

```php
use function oihana\openedge\db\helpers\columnExpression ;

columnExpression([
    OpenEdge::COLUMN   => 'name'        ,
    OpenEdge::TABLE    => 'clients'     ,
]) ;
// → clients.name

columnExpression([
    OpenEdge::COLUMN   => 'name'              ,
    OpenEdge::TABLE    => 'clients'           ,
    OpenEdge::CAST     => [ 'VARCHAR' , 50 ]  ,
    OpenEdge::ALTER    => 'UPPER'             ,
    OpenEdge::NULLABLE => true                ,
]) ;
// → UPPER(CAST(clients.name AS VARCHAR(50)))(+)
```

L'ordre d'application des transformations est : `LITERAL` → `CAST` → `ALTER` → `ALTERS`, géré par `overrideExpression()`.

## `asAlias()` {#asalias}

Produit `AS "alias"` (avec quotes pour préserver la casse) ou `AS alias` (sans quotes, conversion en majuscule côté Progress).

```php
use function oihana\openedge\db\helpers\asAlias ;

asAlias( 'customer_id'  )                  ; // customer_id
asAlias( 'customer_id' , 'id' )            ; // customer_id AS "id"
asAlias( 'customer_id' , 'id' , false )    ; // customer_id AS id
```

> **Recommandation.** Garder `caseSensitive = true` (défaut) pour conserver la casse côté API. Sans quotes, Progress passe tout en majuscules — `id` devient `ID` côté résultat.

## `concatExpression()` {#concatexpression}

Concatène plusieurs expressions avec l'opérateur `||`. Trois formes acceptées via les clés `OpenEdge::CONCAT`, `OpenEdge::ARRAY`, `OpenEdge::LIST`.

```php
use function oihana\openedge\db\helpers\concatExpression ;

// CONCAT — séparateur libre (chaîne ou rien)
concatExpression([
    OpenEdge::CONCAT =>
    [
        [ OpenEdge::COLUMN => 'first' ] ,
        ' '                                ,
        [ OpenEdge::COLUMN => 'last'  ] ,
    ]
]) ;
// → first || ' ' || last

// LIST — séparateur configurable, défaut ','
concatExpression([
    OpenEdge::SEPARATOR => ';' ,
    OpenEdge::LIST      =>
    [
        [ OpenEdge::COLUMN => 'a' ] ,
        [ OpenEdge::COLUMN => 'b' ] ,
    ]
]) ;
// → a || ';' || b
```

## `caseExpression()` {#caseexpression}

Construit un `CASE WHEN … END`. Voir la page dédiée [Expressions `CASE`](sql/sql-functions-cases.md).

## `searchCondition()` {#searchcondition}

Construit la clause `WHERE` ou `ON` à partir d'une structure récursive de conditions et de prédicats.

```php
use function oihana\openedge\db\helpers\searchCondition ;

searchCondition
([
    OpenEdge::OPERATOR   => 'AND' ,
    OpenEdge::CONDITIONS =>
    [
        [ OpenEdge::COLUMN => 'active'   , OpenEdge::OPERATOR => '=' , OpenEdge::VALUE => 1     ] ,
        [ OpenEdge::COLUMN => 'country_code' , OpenEdge::OPERATOR => '=' , OpenEdge::BIND  => 'c'   ] ,
    ]
]) ;
// → active = 1 AND country_code = :c
```

Voir [Prédicats SQL](sql/sql-predicates.md) pour le détail des conditions acceptées.

## `overrideExpression()` {#overrideexpression}

Applique en cascade quatre transformations à une expression : `LITERAL` (conversion en littéral typé), `CAST` (conversion de type), `ALTER` (transformation unique), `ALTERS` (chaîne de transformations). C'est le helper interne consommé par `columnExpression`, `bindExpression`, `valueExpression`.

```php
use function oihana\openedge\db\helpers\overrideExpression ;

overrideExpression( 'user.age' ,
[
    OpenEdge::CAST   => [ 'INTEGER' ]                ,
    OpenEdge::ALTER  => 'RPAD'                       ,
    OpenEdge::ALTERS =>
    [
        [ 'RPAD' , 5 , "'-'" ] ,
        'LOWER'                  ,
    ],
]) ;
// → LOWER(RPAD(RPAD(CAST(user.age AS INTEGER), 5, '-')))
```

L'ordre est figé : on cast d'abord, puis on alter. `ALTER` et `ALTERS` se cumulent : `ALTER` est appliqué en premier (le plus interne), puis `ALTERS` empilés.

## `validateContext()` {#validatecontext}

Vérifie qu'une clé est utilisée dans un contexte autorisé. Utile pour les helpers qui se comportent différemment selon qu'ils sont dans `WHERE` ou dans `HAVING` par exemple.

```php
use function oihana\openedge\db\helpers\validateContext ;

validateContext( 'WHERE' , [ 'WHERE' , 'HAVING' ] ) ;  // true
validateContext( 'GROUP' , [ 'WHERE' , 'HAVING' ] ) ;  // false
validateContext( null    , [ 'WHERE' ]            ) ;  // true (pas de contexte = pas de validation)
```

## `limit()` {#limit}

Produit la clause de pagination Progress (`OFFSET x ROWS FETCH NEXT y ROWS ONLY` ou `FETCH FIRST y ROWS ONLY`).

```php
use function oihana\openedge\db\helpers\limit ;

limit([ 'limit' => 10 ]) ;                       // FETCH FIRST 10 ROWS ONLY
limit([ 'limit' => 10 , 'offset' => 20 ]) ;      // OFFSET 20 ROWS FETCH NEXT 10 ROWS ONLY
limit([]) ;                                        // ''
```

Les clés acceptées sont `Pagination::LIMIT` et `Pagination::OFFSET` (de `oihana/php-enums`). Synonymes des `OpenEdge::LIMIT` / `OpenEdge::OFFSET`.

## `orderByExpression()` {#orderbyexpression}

Parse une expression `?sort=` côté HTTP, et la valide contre une *whitelist* `SORTABLE`.

```php
use function oihana\openedge\db\helpers\orderByExpression ;

orderByExpression( '-name,city' ,
[
    'name' => 'user_name'  ,
    'city' => 'city_name'  ,
]) ;
// → [ 'user_name DESC' , 'city_name' ]
```

Le préfixe `-` indique `DESC`. Les clés absentes du `SORTABLE` sont **silencieusement ignorées** — protection anti-injection.

## `openEdgeType()` {#openedgetype}

Valide qu'un type SQL est bien une constante de `Type::*` et le formate avec ses paramètres optionnels (`(length)`, `(precision, scale)`).

```php
use oihana\openedge\db\enums\Type ;
use function oihana\openedge\db\helpers\openEdgeType ;

openEdgeType( Type::VARCHAR , 50 ) ;        // VARCHAR(50)
openEdgeType( Type::DECIMAL , [10, 2] ) ;   // DECIMAL(10,2)
openEdgeType( Type::INTEGER ) ;             // INTEGER
openEdgeType( 'UNKNOWN' ) ;                 // ConstantException
```

## `rowUrl()` {#rowurl}

Construit une URL canonique pour une ligne, typiquement pour la projection `url` dans une réponse API (style URN ou path absolu).

```php
use function oihana\openedge\db\helpers\rowUrl ;

rowUrl( '/customers' , 1274 ) ;
// → /customers/1274
```

## Voir aussi

- [Référence des enums](enums.md) — constantes consommées par ces helpers.
- [Construire une requête SQL pas à pas](sql/sql-building-queries.md) — exemple d'assemblage.
- [Modèle `Documents`](models.md) — comment le modèle consomme ces helpers.
- [Tips et pièges](tips.md) — règles d'utilisation (`bind` vs `value` vs `literal`).
