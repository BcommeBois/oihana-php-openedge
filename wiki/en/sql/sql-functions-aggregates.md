# Aggregates

OpenEdge exposes the five standard SQL aggregates. The [`AggregateFunction`](../../../src/oihana/openedge/db/enums/functions/AggregateFunction.php) enum lists them.

> **Canonical reference.** [Progress SQL — Aggregate functions](https://docs.progress.com/bundle/openedge-sql-reference/page/Aggregate-functions_2.html).

## List of aggregates

| Constant | SQL | Meaning |
|---|---|---|
| `AggregateFunction::COUNT` | `COUNT(expr)` | Number of rows (or non-`NULL` values if the argument is a column). |
| `AggregateFunction::SUM` | `SUM(expr)` | Sum. `NULL` if all values are `NULL`. |
| `AggregateFunction::AVG` | `AVG(expr)` | Average. `NULL` if all values are `NULL`. |
| `AggregateFunction::MIN` | `MIN(expr)` | Minimum value. |
| `AggregateFunction::MAX` | `MAX(expr)` | Maximum value. |

## `COUNT(*)` vs `COUNT(col)`

The subtlest and most-used distinction:

- `COUNT(*)` — counts **all rows** in the result, including those where every column is `NULL`.
- `COUNT(col)` — counts rows where `col IS NOT NULL`.
- `COUNT(DISTINCT col)` — counts the **distinct non-`NULL` values** of `col`.

```sql
SELECT
    COUNT(*)                AS total ,           -- all rows
    COUNT(country_code)          AS withCountry ,     -- rows with country_code set
    COUNT(DISTINCT country_code) AS distinctCountries -- number of distinct countries
FROM PUB.customers
```

## The builder's `count()` helper

The `OpenEdgeQueryBuilder` exposes a `count()` method that produces the `COUNT(...)` clause:

```php
use oihana\openedge\db\OpenEdgeQueryBuilder ;
use oihana\openedge\enums\OpenEdge as SQL ;

$builder = new OpenEdgeQueryBuilder([
    SQL::FROM    => 'PUB.customers' ,
    SQL::COUNTER => '*' ,                              // default = '*'
]) ;

echo $builder->count() ;
// COUNT(*)

echo $builder->count([ SQL::COUNTER => 'country_code' ]) ;
// COUNT(country_code)
```

`SQL::COUNTER` receives the string to put between the parentheses (a column name, `DISTINCT col`, or `*`).

On the model side, the [`Documents`](../models.md) model's `count()` method consumes this builder and returns a PHP integer:

```php
$total = $customers->count() ;                                 // SELECT COUNT(*) FROM ...
$withCountry = $customers->count([ SQL::COUNTER => 'country_code' ]) ;
```

## `SUM`, `AVG`, `MIN`, `MAX`

No dedicated PHP helpers — you write them directly in a column definition through `ALTER`:

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

Produces the SQL equivalent:

```sql
SELECT
    country_code            AS "country" ,
    COUNT(customer_id)   AS "count"   ,
    SUM(revenue) AS "totalRevenue"
FROM ...
GROUP BY country_code
```

## Aggregates and `GROUP BY`

A `SELECT` mixing scalar columns and aggregates **must** declare all the scalar columns in `GROUP BY`. Otherwise Progress returns an error.

```php
SQL::COLUMNS  => [ 'country_code' , 'segment' , [ 'COUNT(*)' , SQL::ALIAS => 'n' ] ] ,
SQL::GROUP_BY => [ 'country_code' , 'segment' ] ,
```

The framework **doesn't automatically verify** this consistency — it's up to the developer to ensure `GROUP BY` covers all non-aggregated columns. A common mistake: modifying `COLUMNS` without updating `GROUP_BY`.

## `HAVING` — filter after aggregation

`WHERE` filters before aggregation, `HAVING` filters after. The distinction matters: you can't put an aggregate in a `WHERE`.

```sql
-- Wrong: WHERE can't see SUM(x)
SELECT country_code, SUM(revenue) AS total
FROM PUB.customers
WHERE SUM(revenue) > 100000           -- ERROR
GROUP BY country_code

-- Right: HAVING filters after aggregation
SELECT country_code, SUM(revenue) AS total
FROM PUB.customers
GROUP BY country_code
HAVING SUM(revenue) > 100000          -- OK
```

In the framework:

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

## Aggregates on expressions

Aggregates accept an expression, not just a column:

```sql
SUM(net_price * quantite)        -- row-by-row revenue sum
AVG(CASE WHEN segment = 'A' THEN net_price ELSE 0 END)
COUNT(CASE WHEN active = 1 THEN 1 END)   -- counts active rows
```

That's the "conditional aggregation" pattern — useful when you want several metrics in a single `GROUP BY` without multiple subqueries.

## See also

- [Building a SQL query step by step](sql-building-queries.md) — full example with `GROUP BY` and `HAVING`.
- [`CASE` expressions](sql-functions-cases.md) — often used inside a conditional aggregate.
- [`Documents` model](../models.md) — model's `count()` method.
- [Progress SQL — Aggregate functions](https://docs.progress.com/bundle/openedge-sql-reference/page/Aggregate-functions_2.html) — canonical reference.
