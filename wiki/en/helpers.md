# Helpers reference

The framework exposes about fifteen functional helpers under [`db/helpers/`](../../src/oihana/openedge/db/helpers/) (the folder root — excluding `predicates/`, `cases/`, `functions/` which are covered in dedicated pages). This page enumerates them and gives, for each, the signature, the effect and an example.

> Convention: 1 file = 1 function. All these helpers are Composer-autoloaded (`composer.json` `files` section). No manual `require` needed.

## Overview

| Helper | Role |
|---|---|
| [`expression`](#expression) | Polymorphic entry point, dispatches based on definition shape. |
| [`bindExpression`](#bindexpression) | Produces `:name` for a PDO placeholder. |
| [`valueExpression`](#valueexpression) | Produces a value fragment (literal or special expression). |
| [`literal`](#literal) | Produces an SQL literal `'…'` with quote escaping. |
| [`columnExpression`](#columnexpression) | Produces a qualified column with `CAST`, `ALTER`, *nullable*. |
| [`asAlias`](#asalias) | Suffixes `AS "alias"` or `AS alias`. |
| [`concatExpression`](#concatexpression) | Concatenation of multiple expressions via `\|\|`. |
| [`caseExpression`](#caseexpression) | Builds a `CASE WHEN … END`. |
| [`searchCondition`](#searchcondition) | Builds a `WHERE` or `ON` clause from structured conditions. |
| [`overrideExpression`](#overrideexpression) | Applies cascading `LITERAL` → `CAST` → `ALTER` → `ALTERS`. |
| [`validateContext`](#validatecontext) | Checks that an `OpenEdge::*` key is used in an allowed context. |
| [`limit`](#limit) | Produces `OFFSET x ROWS FETCH NEXT y ROWS ONLY`. |
| [`orderByExpression`](#orderbyexpression) | Parses a `?sort=` expression and validates it against a whitelist. |
| [`openEdgeType`](#openedgetype) | Validates a type is in `Type::*` and formats with `(length)` or `(precision, scale)`. |
| [`rowUrl`](#rowurl) | Builds a canonical URL for a row (URN-style). |

## `expression()` {#expression}

**The most-used entry point.** Accepts a polymorphic definition and dispatches to the right helper:

| Key present | Dispatch |
|---|---|
| `OpenEdge::BIND` | [`bindExpression`](#bindexpression) → `:name` |
| `OpenEdge::VALUE` | [`valueExpression`](#valueexpression) → literal or special expression |
| `OpenEdge::CASE` | [`caseExpression`](#caseexpression) → `CASE WHEN …` |
| `OpenEdge::CONCAT` / `OpenEdge::ARRAY` / `OpenEdge::LIST` | [`concatExpression`](#concatexpression) → `a \|\| b \|\| c` |
| Otherwise | [`columnExpression`](#columnexpression) → qualified column |

```php
use oihana\openedge\enums\OpenEdge as SQL ;
use function oihana\openedge\db\helpers\expression ;

// Column
expression([ SQL::COLUMN => 'name' , SQL::TABLE => 'clients' ]) ;
// → clients.name

// Bind
expression([ SQL::BIND => 'userId' ]) ;
// → :userId

// Concatenation
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

A scalar (non-array) passed to `expression()` is treated as a literal via [`literal()`](#literal).

## `bindExpression()` {#bindexpression}

Produces `:name` for a PDO placeholder. Use for **any dynamic value** (user input). See [tips.md](tips.md) on the absolute rule.

```php
use function oihana\openedge\db\helpers\bindExpression ;

bindExpression([ OpenEdge::BIND => 'userId' ]) ;
// → :userId

// With an alteration
bindExpression([
    OpenEdge::BIND  => 'price' ,
    OpenEdge::ALTER => 'ROUND' ,
]) ;
// → ROUND(:price)
```

## `valueExpression()` {#valueexpression}

Produces a value fragment **on the server side**. Recognises a few special values (common date/time constants), otherwise delegates to `literal()`.

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

Recognised special values: `DateFunction::CURDATE`, `CURTIME`, `NOW`, `SYSDATE`, `SYSTIME`, `SYSTIMESTAMP`, `NumericFunction::PI`.

> **Rule.** Use for **server-side constants** (today's date, π, etc.), never for values from user input — prefer `bindExpression` for that. See [tips.md](tips.md).

## `literal()` {#literal}

Produces a SQL literal `'…'`, escaping single quotes by doubling them (standard SQL style).

```php
use function oihana\openedge\db\helpers\literal ;

literal( 'hello' ) ;        // 'hello'
literal( "O'Hare" ) ;       // 'O''Hare'
literal( 42 ) ;             // 42
literal( true ) ;           // true
```

> **Warning.** `literal()` does not validate the type, only escapes quotes. For dates and times, prefer `literalExpression` with the right `Literal::*` which produces the `{ d '…' }`, `{ t '…' }`, `{ ts '…' }` syntax.

## `columnExpression()` {#columnexpression}

Produces a column qualified by its table, with optionally a `CAST`, an `ALTER`, and the Progress `(+)` suffix if the column is marked *nullable*.

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

The transformation order is: `LITERAL` → `CAST` → `ALTER` → `ALTERS`, handled by `overrideExpression()`.

## `asAlias()` {#asalias}

Produces `AS "alias"` (with quotes to preserve case) or `AS alias` (without quotes, Progress uppercases it).

```php
use function oihana\openedge\db\helpers\asAlias ;

asAlias( 'cd_client'  )                  ; // cd_client
asAlias( 'cd_client' , 'id' )            ; // cd_client AS "id"
asAlias( 'cd_client' , 'id' , false )    ; // cd_client AS id
```

> **Recommendation.** Keep `caseSensitive = true` (default) to preserve case on the API side. Without quotes, Progress uppercases everything — `id` becomes `ID` in results.

## `concatExpression()` {#concatexpression}

Concatenates multiple expressions with the `||` operator. Three accepted forms via the `OpenEdge::CONCAT`, `OpenEdge::ARRAY`, `OpenEdge::LIST` keys.

```php
use function oihana\openedge\db\helpers\concatExpression ;

// CONCAT — free separator (string or none)
concatExpression([
    OpenEdge::CONCAT =>
    [
        [ OpenEdge::COLUMN => 'first' ] ,
        ' '                                ,
        [ OpenEdge::COLUMN => 'last'  ] ,
    ]
]) ;
// → first || ' ' || last

// LIST — configurable separator, default ','
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

Builds a `CASE WHEN … END`. See the dedicated page [`CASE` expressions](sql/sql-functions-cases.md).

## `searchCondition()` {#searchcondition}

Builds a `WHERE` or `ON` clause from a recursive structure of conditions and predicates.

```php
use function oihana\openedge\db\helpers\searchCondition ;

searchCondition
([
    OpenEdge::OPERATOR   => 'AND' ,
    OpenEdge::CONDITIONS =>
    [
        [ OpenEdge::COLUMN => 'actif'   , OpenEdge::OPERATOR => '=' , OpenEdge::VALUE => 1     ] ,
        [ OpenEdge::COLUMN => 'cd_pays' , OpenEdge::OPERATOR => '=' , OpenEdge::BIND  => 'c'   ] ,
    ]
]) ;
// → actif = 1 AND cd_pays = :c
```

See [SQL predicates](sql/sql-predicates.md) for accepted condition forms.

## `overrideExpression()` {#overrideexpression}

Applies four cascading transformations to an expression: `LITERAL` (convert to typed literal), `CAST` (type conversion), `ALTER` (single transformation), `ALTERS` (transformation chain). It's the internal helper consumed by `columnExpression`, `bindExpression`, `valueExpression`.

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

The order is fixed: cast first, then alter. `ALTER` and `ALTERS` stack: `ALTER` is applied first (innermost), then `ALTERS` stacked on top.

## `validateContext()` {#validatecontext}

Checks that a key is used in an allowed context. Useful for helpers that behave differently in `WHERE` vs `HAVING` for example.

```php
use function oihana\openedge\db\helpers\validateContext ;

validateContext( 'WHERE' , [ 'WHERE' , 'HAVING' ] ) ;  // true
validateContext( 'GROUP' , [ 'WHERE' , 'HAVING' ] ) ;  // false
validateContext( null    , [ 'WHERE' ]            ) ;  // true (no context = no validation)
```

## `limit()` {#limit}

Produces the Progress pagination clause (`OFFSET x ROWS FETCH NEXT y ROWS ONLY` or `FETCH FIRST y ROWS ONLY`).

```php
use function oihana\openedge\db\helpers\limit ;

limit([ 'limit' => 10 ]) ;                       // FETCH FIRST 10 ROWS ONLY
limit([ 'limit' => 10 , 'offset' => 20 ]) ;      // OFFSET 20 ROWS FETCH NEXT 10 ROWS ONLY
limit([]) ;                                        // ''
```

Accepted keys are `Pagination::LIMIT` and `Pagination::OFFSET` (from `oihana/php-enums`). Synonyms of `OpenEdge::LIMIT` / `OpenEdge::OFFSET`.

## `orderByExpression()` {#orderbyexpression}

Parses an HTTP-side `?sort=` expression and validates it against a `SORTABLE` whitelist.

```php
use function oihana\openedge\db\helpers\orderByExpression ;

orderByExpression( '-name,city' ,
[
    'name' => 'user_name'  ,
    'city' => 'city_name'  ,
]) ;
// → [ 'user_name DESC' , 'city_name' ]
```

The `-` prefix means `DESC`. Keys missing from the `SORTABLE` are **silently ignored** — anti-injection guard.

## `openEdgeType()` {#openedgetype}

Validates that a SQL type is a `Type::*` constant and formats it with its optional parameters (`(length)`, `(precision, scale)`).

```php
use oihana\openedge\db\enums\Type ;
use function oihana\openedge\db\helpers\openEdgeType ;

openEdgeType( Type::VARCHAR , 50 ) ;        // VARCHAR(50)
openEdgeType( Type::DECIMAL , [10, 2] ) ;   // DECIMAL(10,2)
openEdgeType( Type::INTEGER ) ;             // INTEGER
openEdgeType( 'UNKNOWN' ) ;                 // ConstantException
```

## `rowUrl()` {#rowurl}

Builds a canonical URL for a row, typically for the `url` projection in an API response (URN-style or absolute path).

```php
use function oihana\openedge\db\helpers\rowUrl ;

rowUrl( '/customers' , 1274 ) ;
// → /customers/1274
```

## See also

- [Enums reference](enums.md) — constants consumed by these helpers.
- [Building a SQL query step by step](sql/sql-building-queries.md) — assembly example.
- [`Documents` model](models.md) — how the model consumes these helpers.
- [Tips and pitfalls](tips.md) — usage rules (`bind` vs `value` vs `literal`).
