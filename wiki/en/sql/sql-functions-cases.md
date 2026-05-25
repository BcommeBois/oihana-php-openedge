# `CASE` expressions

A `CASE` SQL expression generalises conditionals: it returns a value **among N** based on a cascade of conditions. When `COALESCE` and `NULLIF` aren't enough (typically when the condition isn't a simple null test), this is the tool to reach for.

The framework provides four helpers under [`db/helpers/cases/`](../../../src/oihana/openedge/db/helpers/cases/) plus a central composer [`caseExpression()`](../../../src/oihana/openedge/db/helpers/caseExpression.php).

> **Canonical reference.** [Progress SQL — CASE](https://docs.progress.com/bundle/openedge-sql-reference/page/CASE.html).

## The two `CASE` forms

### Simple form

```sql
CASE primary_expr
    WHEN value1 THEN result1
    WHEN value2 THEN result2
    ELSE result_default
END
```

Compares `primary_expr` against each `valueN`. More concise when the condition is always equality.

### Searched form (explicit conditions)

```sql
CASE
    WHEN condition1 THEN result1
    WHEN condition2 THEN result2
    ELSE result_default
END
```

Each `condition` can be any predicate (`x > 100`, `y IS NULL`, `name LIKE '%-%'`). More powerful, more verbose.

## `db/helpers/cases/` helpers

### `whenExpression( condition )`

```php
use function oihana\openedge\db\helpers\cases\whenExpression ;

echo whenExpression( "net_price > 100" ) ;
// WHEN net_price > 100
```

### `thenExpression( value )`

```php
use function oihana\openedge\db\helpers\cases\thenExpression ;

echo thenExpression( "'expensive'" ) ;
// THEN 'expensive'
```

### `elseExpression( value )`

```php
use function oihana\openedge\db\helpers\cases\elseExpression ;

echo elseExpression( "'cheap'" ) ;
// ELSE 'cheap'
```

### `whenThenExpression( condition , value )`

Composes `WHEN ... THEN ...` in a single call, more convenient.

```php
use function oihana\openedge\db\helpers\cases\whenThenExpression ;

echo whenThenExpression( "net_price > 100" , "'expensive'" ) ;
// WHEN net_price > 100 THEN 'expensive'
```

## `caseExpression()` — global composer

The [`caseExpression()`](../../../src/oihana/openedge/db/helpers/caseExpression.php) helper builds the complete expression from a structured array.

```php
use oihana\openedge\enums\OpenEdge as SQL ;
use function oihana\openedge\db\helpers\expression ;

echo expression([
    SQL::CASE =>
    [
        SQL::WHEN =>
        [
            [ "net_price > 100" , "'expensive'" ] ,
            [ "net_price > 50"  , "'mid-range'" ] ,
        ],
        SQL::ELSE => "'cheap'" ,
    ]
]) ;
// CASE
//     WHEN net_price > 100 THEN 'expensive'
//     WHEN net_price > 50 THEN 'mid-range'
//     ELSE 'cheap'
// END
```

### Simple form

When all conditions are equalities on the same expression, you can use the simple form:

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

## Usage pattern — price category computation

In a typical host application, this pattern is used to compute a category on the SQL side (faster than fetching the price and categorising in PHP):

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
                [ "products.net_price >= 1000" , "'premium'"  ] ,
                [ "products.net_price >= 100"  , "'standard'" ] ,
            ],
            SQL::ELSE => "'budget'" ,
        ],
    ],
]
```

## Alternative pattern — `DECODE` or `IFNULL`

When `CASE` is only a fixed mapping (`A → X`, `B → Y`, `C → Z`), you can use:

- **`DECODE(...)`** — native Oracle syntax exposed by `ConditionalFunction::DECODE`, but **not ODBC compatible**. No dedicated PHP helper in the framework — prefer `CASE`.
- **`COALESCE` + `NULLIF`** — fine when the logic reduces to "keep the value unless it equals X, then put Y".

For genuinely conditional logic (multiple non-equivalent branches), `CASE` remains the readable, standard option.

## Nesting

`CASE` can be nested inside any expression — including another `CASE`. Powerful but quickly unreadable. Past three levels, better move the logic to PHP code.

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

## See also

- [SQL conditionals](sql-functions-conditionals.md) — `COALESCE`, `IFNULL`, `NULLIF` for simple cases.
- [Building a SQL query step by step](sql-building-queries.md) — full example.
- [Helpers](../helpers.md#expression) — the `expression()` helper that dispatches to `caseExpression()`.
- [Progress SQL — CASE](https://docs.progress.com/bundle/openedge-sql-reference/page/CASE.html) — canonical reference.
