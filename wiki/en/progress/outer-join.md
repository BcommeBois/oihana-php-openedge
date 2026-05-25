# Progress outer join `(+)`

Progress OpenEdge accepts a **historical syntax** to express an outer join in the `WHERE` clause, inherited from pre-92 Oracle: you suffix a column with `(+)`. This syntax is **non-standard SQL** but is still widely used in existing ERP schemas, and the framework supports it through the `OpenEdge::NULLABLE_COLUMN` constant.

> **Canonical reference.** [Progress SQL — Outer join predicate](https://docs.progress.com/bundle/openedge-sql-reference/page/Outer-join-predicate.html).

## The concept in two sentences

An *outer join* keeps the rows on the main side **even if** the joined side has no matching row. Standard SQL expresses this with `LEFT JOIN` (left side kept) or `RIGHT JOIN` (right side kept). Progress historical syntax expresses it with a `(+)` suffix in the `WHERE` clause, on the column of the **side that may be missing**.

## Comparing the two syntaxes

### Standard SQL — `LEFT JOIN`

```sql
SELECT e.name , d.label
FROM   PUB.employees e
LEFT JOIN PUB.departments d
       ON e.department_id = d.department_id
```

Reads as: "all employees, and for those with an associated department, the department label. Employees without a department still come out, with `d.label = NULL`."

### Progress historical — `(+)`

```sql
SELECT e.name , d.label
FROM   PUB.employees e , PUB.departments d
WHERE  e.department_id = d.department_id(+)
```

Strictly identical semantics. The `(+)` is placed on the column of the side that may be missing (here `d.department_id`).

## In the framework — `OpenEdge::NULLABLE_COLUMN`

The `OpenEdge::NULLABLE_COLUMN` constant is literally `'(+)'`. It's applied by [`columnExpression()`](../helpers.md#columnexpression) when the `OpenEdge::NULLABLE => true` key is present in the definition.

```php
use oihana\openedge\enums\OpenEdge as SQL ;
use function oihana\openedge\db\helpers\expression ;

echo expression([
    SQL::COLUMN   => 'department_id'   ,
    SQL::TABLE    => 'd'         ,
    SQL::NULLABLE => true        ,
]) ;
// → d.department_id(+)
```

In a `WHERE` condition definition:

```php
SQL::WHERE =>
[
    SQL::COLUMN   => 'department_id'                  ,
    SQL::TABLE    => 'e'                        ,
    SQL::OPERATOR => RelationalOperator::EQUAL  ,
    SQL::VALUE    => expression([
        SQL::COLUMN   => 'department_id' ,
        SQL::TABLE    => 'd'       ,
        SQL::NULLABLE => true      ,
    ]) ,
]
// → e.department_id = d.department_id(+)
```

## When to use which

| Case | Recommendation |
|---|---|
| New code, evidently Progress base | Standard `LEFT JOIN ... ON ...`. Readable and portable. |
| Maintaining a legacy ABL or SQL script using `(+)` everywhere | Keep the `(+)` syntax for consistency. `OpenEdge::NULLABLE_COLUMN` covers it. |
| Outer join *on the right* | `(+)` on the left, or standard `RIGHT JOIN`. Prefer `LEFT JOIN` by swapping tables — more readable. |
| *Full* outer join (FULL) | Not expressible with `(+)`. Use standard `FULL JOIN`. |

## Pitfalls of `(+)`

### 1. The `(+)` position is often inverted

Natural reflex: "I want to keep employees without a department, so I put `(+)` on the employees." **Wrong.** The `(+)` goes on the side that **may be missing**, so on `d.department_id`, not on `e.department_id`.

> Mnemonic: `(+)` reads as "*plus something that isn't really there*", thus on the column whose values may be missing.

### 2. Only one side `(+)` at a time

`a.x = b.y(+)` is a `LEFT JOIN` (side `a` kept). `a.x(+) = b.y` is a `RIGHT JOIN`. **`a.x(+) = b.y(+)` is invalid** — a `FULL JOIN` can't be expressed that way.

### 3. No `OR` in an outer condition

```sql
WHERE  e.department_id = d.department_id(+)
   OR  e.other_id = d.other_id(+)         -- ERROR
```

When you need several join conditions, switch to standard `LEFT JOIN ... ON ...`.

### 4. `WHERE` mixes with the join

With `(+)`, the join and the filter live in the same `WHERE` clause. This is error-prone: an `AND d.label = 'SALES'` filter added after the join condition **eliminates** rows where `d.label IS NULL`, which cancels the outer-join effect.

```sql
-- Unexpected: employees without a department are dropped here
WHERE  e.department_id = d.department_id(+)
  AND  d.label = 'SALES'

-- Correct: move the filter into a NULL-compatible condition
WHERE  e.department_id = d.department_id(+)
  AND  ( d.label = 'SALES' OR d.label IS NULL )
```

> With `LEFT JOIN ... ON ...`, you put `d.label = 'SALES'` **in the `ON` clause** rather than in `WHERE` — which solves the problem more naturally.

## See also

- [Building a SQL query step by step](../sql/sql-building-queries.md) — `FROM ... LEFT JOIN ... ON` example.
- [Helpers](../helpers.md#columnexpression) — `columnExpression()` and the `NULLABLE` key.
- [Locking hints](locking-hints.md) — another Progress specificity in the `WHERE` clause.
- [Progress SQL — Outer join predicate](https://docs.progress.com/bundle/openedge-sql-reference/page/Outer-join-predicate.html) — canonical reference.
