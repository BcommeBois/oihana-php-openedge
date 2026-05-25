# SQL operators

This page lists the four operator families exposed by the framework — relational (comparison), logical (condition composition), quantified (subqueries), and concatenation (strings).

## `RelationalOperator` {#relationaloperator}

Binary comparators usable in a basic predicate.

```php
use oihana\openedge\db\enums\RelationalOperator ;
```

| Constant | SQL value | Meaning |
|---|---|---|
| `RelationalOperator::EQUAL` | `=` | Equality. |
| `RelationalOperator::NOT_EQUAL` | `<>` | Inequality. Prefer `<>` to `!=` (both standard SQL and Progress accept the two, but `<>` is more portable). |
| `RelationalOperator::LESS_THAN` | `<` | Strict less than. |
| `RelationalOperator::LESS_THAN_OR_EQUAL` | `<=` | Less than or equal. |
| `RelationalOperator::GREATER_THAN` | `>` | Strict greater than. |
| `RelationalOperator::GREATER_THAN_OR_EQUAL` | `>=` | Greater than or equal. |

Typical use in a condition:

```php
use oihana\openedge\enums\OpenEdge as SQL ;

[
    SQL::COLUMN   => 'amount'                              ,
    SQL::OPERATOR => RelationalOperator::GREATER_THAN_OR_EQUAL ,
    SQL::BIND     => 'minAmount'                            ,
]
// → amount >= :minAmount
```

## `Logic`

Logical connectors to compose nested conditions.

```php
use oihana\openedge\db\enums\Logic ;
```

| Constant | SQL value |
|---|---|
| `Logic::AND` | `AND` |
| `Logic::OR` | `OR` |
| `Logic::NOT` | `NOT` |
| `Logic::AND_NOT` | `AND NOT` |
| `Logic::OR_NOT` | `OR NOT` |

Composition of a condition group:

```php
SQL::WHERE =>
[
    SQL::LOGIC      => Logic::AND ,
    SQL::CONDITIONS =>
    [
        [ SQL::COLUMN => 'active'   , SQL::OPERATOR => '=' , SQL::VALUE => 1 ] ,
        [
            SQL::LOGIC => Logic::OR ,
            SQL::CONDITIONS =>
            [
                [ SQL::COLUMN => 'country_code' , SQL::OPERATOR => '=' , SQL::VALUE => 'FR' ] ,
                [ SQL::COLUMN => 'country_code' , SQL::OPERATOR => '=' , SQL::VALUE => 'BE' ] ,
            ],
        ],
    ],
]
// → active = 1 AND ( country_code = 'FR' OR country_code = 'BE' )
```

Nesting can go to N levels. The framework automatically parenthesises each group.

## `QuantifiedOperator`

Quantifiers to compare a value against a set returned by a subquery.

```php
use oihana\openedge\db\enums\QuantifiedOperator ;
```

| Constant | SQL value | Meaning |
|---|---|---|
| `QuantifiedOperator::ANY` | `ANY` | The condition is true if **at least one** row of the subquery matches. |
| `QuantifiedOperator::ALL` | `ALL` | The condition is true if **all** rows match. |
| `QuantifiedOperator::SOME` | `SOME` | Synonym of `ANY` (standard SQL, rarely used in practice). |

```php
[
    SQL::COLUMN     => 'amount'                              ,
    SQL::OPERATOR   => RelationalOperator::GREATER_THAN       ,
    SQL::QUANTIFIED => QuantifiedOperator::ALL                ,
    SQL::QUERY      => 'SELECT threshold FROM PUB.alert_thresholds'  ,
]
// → amount > ALL ( SELECT threshold FROM PUB.alert_thresholds )
```

Typical use case: "is this amount greater than all existing alert thresholds?". On large volumes, prefer `MAX()` + a direct comparison: `amount > ( SELECT MAX(threshold) FROM PUB.alert_thresholds )`.

## `ConcatOperator`

Standard SQL string concatenation operator (`||`). Covered by the [`ConcatOperator`](../../../src/oihana/openedge/db/enums/ConcatOperator.php) enum **and** by the [`Operator`](../../../src/oihana/openedge/db/enums/Operator.php) enum (which re-exposes it for backward compatibility).

```php
use oihana\openedge\db\enums\ConcatOperator ;
```

| Constant | SQL value | Meaning |
|---|---|---|
| `ConcatOperator::CONCAT` | `\|\|` | Plain concatenation (no padding). |
| `ConcatOperator::CONCAT_WITH_SPACE` | `␣\|\|␣` | Concatenation with a space on either side of `\|\|` (for readability of generated SQL). |
| `ConcatOperator::CONCAT_WITH_COMMA_SEPARATOR` | `␣\|\|␣','␣\|\|␣` | Concatenation with a literal comma between the two operands. |

In practice, you rarely use these constants directly: the [`concatExpression()`](../helpers.md#concatexpression) helper and the `SQL::CONCAT` or `SQL::LIST` keys in an expression definition use them internally.

```php
echo expression([
    SQL::CONCAT =>
    [
        [ SQL::COLUMN => 'first_name' ] ,
        ' '                                  ,
        [ SQL::COLUMN => 'customer_name'    ] ,
    ]
]) ;
// → first_name || ' ' || customer_name
```

### Custom separator

`ConcatOperator::concatSeparator(';')` returns `␣||␣';'␣||␣` and lets you build CSV-like strings in SQL:

```php
echo expression([
    SQL::SEPARATOR => ';' ,
    SQL::LIST      =>
    [
        [ SQL::COLUMN => 'first_name' ] ,
        [ SQL::COLUMN => 'customer_name'    ] ,
    ]
]) ;
// → first_name || ';' || customer_name
```

> For cleaner concatenation, especially on Progress, prefer the `CONCAT(a, b)` function over the `||` operator when you only have two operands. See [`concat()`](sql-functions-strings.md#concat).

## `Operator`

The [`Operator`](../../../src/oihana/openedge/db/enums/Operator.php) enum groups "cross-cutting" operators that don't fit the categories above.

| Constant | SQL value | Meaning |
|---|---|---|
| `Operator::ASSIGN` | `=` | Assignment in an `UPDATE ... SET col = expr`. Same character as `EQUAL`, but the context differs (the framework keeps them separate for rename safety). |
| `Operator::CONCAT` | `\|\|` | Synonym of `ConcatOperator::CONCAT` (re-exposed for historical imports). |
| `Operator::CONCAT_WITH_COMMA_SEPARATOR` | `␣\|\|␣,␣\|\|␣` | Synonym of `ConcatOperator::CONCAT_WITH_COMMA_SEPARATOR`. |

## See also

- [SQL predicates](sql-predicates.md) — seven predicate forms that consume these operators.
- [Building a SQL query step by step](sql-building-queries.md) — full `AND`/`OR` assembly example.
- [String functions](sql-functions-strings.md) — `CONCAT()` function as an alternative to `||`.
