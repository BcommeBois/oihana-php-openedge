# Building a SQL query step by step

This page walks through a concrete case to show how the framework's helpers and enums fit together to produce a full Progress `SELECT`, with typed columns, joins, parameterised conditions, sorting and pagination. The example: exposing a customer list from a `PUB.clients_clients` table, joined to a country thesaurus, with optional name filtering and safe sorting.

## The SELECT pipeline

A Progress `SELECT` query chains seven clauses in this order:

```
SELECT      ← projected columns, distinct, top
FROM        ← table(s) and joins
WHERE       ← conditions
GROUP BY    ← grouping
HAVING      ← post-grouping conditions
ORDER BY    ← sorting
OFFSET/FETCH ← pagination
```

The framework exposes a helper or a trait for each of these clauses, and an [`OpenEdgeQueryBuilder`](../query-builder.md) that aggregates them.

## Step 1 — projecting columns

A column, in the framework, isn't a raw string — it's an expression. The simplest form:

```php
use function oihana\openedge\db\helpers\expression ;
use oihana\openedge\enums\OpenEdge as SQL ;

echo expression([
    SQL::COLUMN => 'nom_client'  ,
    SQL::TABLE  => 'clients'     ,
]) ;
// clients.nom_client
```

You can add a cast, an alias, mark the column as nullable (for a Progress outer join):

```php
use oihana\openedge\db\enums\Type ;

echo expression([
    SQL::COLUMN => 'cd_pays'                ,
    SQL::TABLE  => 'clients'                ,
    SQL::CAST   => [ Type::VARCHAR , 3 ]    ,
    SQL::NULLABLE => true                   ,
]) ;
// CAST(clients.cd_pays AS VARCHAR(3))(+)
```

> The `(+)` suffix is the **Progress outer join syntax** triggered by `SQL::NULLABLE => true`. See [Progress outer join](../progress/outer-join.md).

### Concatenating several columns into one expression

```php
echo expression([
    SQL::CONCAT =>
    [
        [ SQL::COLUMN => 'prenom_client' , SQL::TABLE => 'clients' ] ,
        ' '                                                            ,
        [ SQL::COLUMN => 'nom_client'    , SQL::TABLE => 'clients' ] ,
    ]
]) ;
// clients.prenom_client || ' ' || clients.nom_client
```

### Listing several columns into a separator-joined string

```php
echo expression([
    SQL::SEPARATOR => ';' ,
    SQL::LIST      =>
    [
        [ SQL::COLUMN => 'prenom_client' , SQL::TABLE => 'clients' ] ,
        [ SQL::COLUMN => 'nom_client'    , SQL::TABLE => 'clients' ] ,
    ]
]) ;
// clients.prenom_client || ';' || clients.nom_client
```

## Step 2 — `FROM` and joins

```php
use oihana\openedge\db\enums\Clause ;
use oihana\openedge\db\enums\Join   ;

$from = 'PUB.clients_clients clients'
      . ' '
      . Join::LEFT
      . ' PUB.pays_pays pays '
      . Clause::ON
      . ' clients.cd_pays = pays.cd_pays' ;
```

This manual concatenation works, but in practice you declare the `FROM` directly in the query-builder definition:

```php
SQL::FROM => 'PUB.clients_clients clients LEFT JOIN PUB.pays_pays pays ON clients.cd_pays = pays.cd_pays'
```

The framework doesn't rebuild `FROM` from parts — it's a pre-compiled string at initialisation.

> Tip: externalising the `FROM` into a function named by entity (`customerFrom()`) under `app\definitions\openedge\<entity>\` keeps DI definitions readable. See the pattern in [models.md](../models.md).

## Step 3 — the `WHERE` clause with a bind

`WHERE` accepts conditions expressed in a structured form. The simplest condition: `column = :bind`.

```php
use oihana\openedge\db\enums\RelationalOperator ;
use function oihana\openedge\db\helpers\bindExpression ;

// In the builder, you declare the WHERE:
SQL::WHERE =>
[
    SQL::COLUMN    => 'cd_pays'                    ,
    SQL::TABLE     => 'clients'                    ,
    SQL::OPERATOR  => RelationalOperator::EQUAL    ,
    SQL::BIND      => 'country'                    , // produces :country on the SQL side
]
// → clients.cd_pays = :country

// At execution time, pass the bind value to PDO:
$stmt->execute([ 'country' => 'FR' ]) ;
```

> **Absolute rule.** Any dynamic value goes through `SQL::BIND`. Never inline with `SQL::VALUE` or `literal()` for a user-provided value. See [tips.md](../tips.md) on the injection risk.

To combine several conditions, nest them with a logical operator:

```php
use oihana\openedge\db\enums\Logic ;

SQL::WHERE =>
[
    SQL::LOGIC      => Logic::AND ,
    SQL::CONDITIONS =>
    [
        [ SQL::COLUMN => 'cd_pays' , SQL::TABLE => 'clients' , SQL::OPERATOR => '=' , SQL::BIND => 'country' ] ,
        [ SQL::COLUMN => 'actif'   , SQL::TABLE => 'clients' , SQL::OPERATOR => '=' , SQL::VALUE => 1        ] ,
    ]
]
```

The seven predicate forms are detailed in [SQL predicates](sql-predicates.md).

## Step 4 — `GROUP BY` and `HAVING`

```php
SQL::GROUP_BY => 'cd_pays' ,
SQL::HAVING   =>
[
    SQL::COLUMN   => 'cd_pays'  ,
    SQL::OPERATOR => '<>'       ,
    SQL::VALUE    => 'XX'       ,
] ,
```

Aggregates on the SELECT side (`COUNT`, `SUM`, …) are first-class helpers, see [Aggregates](sql-functions-aggregates.md).

## Step 5 — `ORDER BY` with a whitelist

Sorting is **always** validated against a `SORTABLE` whitelist in the builder. This whitelist maps a public key to a real column name:

```php
SQL::ORDER_BY => 'name' ,            // default value server-side
SQL::SORTABLE =>
[
    'id'      => 'cd_client'  ,      // ?sort=id → ORDER BY cd_client
    'name'    => 'nom_client' ,      // ?sort=name → ORDER BY nom_client
    'country' => 'cd_pays'    ,
]
```

Three important properties:

- A key missing from `SORTABLE` is **silently ignored**. That's the anti-injection guard on the `?sort=` parameter.
- The public key can differ from the Progress name (`name` ↔ `nom_client`). Lets you expose a stable API even if the table is renamed.
- Direction `?sort=-name` (`-` prefix for `DESC`) is handled at the controller, not in the builder.

## Step 6 — pagination

Progress supports both syntaxes:

```sql
-- SQL Server style
SELECT TOP 50 * FROM PUB.clients_clients

-- Standard SQL style
SELECT * FROM PUB.clients_clients OFFSET 0 ROWS FETCH FIRST 50 ROWS ONLY
```

In the builder, you pass `SQL::LIMIT` and `SQL::OFFSET` to the model's `list()` method, and the framework picks the right form.

```php
$customers->list([ SQL::LIMIT => 50 , SQL::OFFSET => 100 ]) ;
```

## All assembled — real DI definition

Here's what a complete model definition looks like, as it lives in host applications:

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
            SQL::COLUMNS  => customerAllColumns()  , // function returning the column array
            SQL::FROM     => customerFrom()        , // FROM + JOIN string
            SQL::WHERE    => customerWhere()       , // default conditions array
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

> **Pattern to remember.** Externalise `COLUMNS`, `FROM` and `WHERE` into named PHP functions (`<entity>AllColumns()`, `<entity>From()`, `<entity>Where()`) rather than writing everything inline. Makes DI definitions readable and the SQL reusable across models.

## See also

- [SQL clauses](sql-clauses.md) — detail of the FROM / WHERE / GROUP BY / ORDER BY traits.
- [SQL predicates](sql-predicates.md) — the seven predicate forms.
- [SQL operators](sql-operators.md) — relational, logical, quantified, concat.
- [`OpenEdgeQueryBuilder`](../query-builder.md) — detail of the underlying builder.
- [`Documents` model](../models.md) — how the model consumes the definition.
