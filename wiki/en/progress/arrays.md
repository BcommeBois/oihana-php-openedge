# Progress arrays

Progress OpenEdge offers a native `ARRAY` type that stores multiple values in a single column — a legacy of the ABL language where it's a first-class primitive. On the SQL side, these arrays show up as a serialised string with a specific separator. Three SQL functions let you manipulate them, and the framework exposes them as PHP helpers.

> **Canonical reference.** [Progress SQL — Array data type](https://docs.progress.com/bundle/openedge-sql-reference/page/Array-functions.html).

## The Progress `ARRAY` type in two sentences

A Progress `ARRAY` column holds N values of the same type, where N is defined at creation (ABL `extent`). When read through SQL, they come out as a serialised string — elements are separated and each element is escaped if its content contains the separator character.

This serialised representation is readable but inconvenient for application processing. The three Progress functions below make conversion easier.

## The three functions

### `PRO_ELEMENT(array_expr, start, [end])`

Extracts one or several elements from a Progress array by position. Positions are **1-indexed**.

```php
use function oihana\openedge\db\helpers\functions\strings\proElement ;

echo proElement( 'col_phones' , 1 ) ;
// PRO_ELEMENT(col_phones, 1, 1)

echo proElement( 'col_phones' , 1 , 3 ) ;
// PRO_ELEMENT(col_phones, 1, 3)
```

> The PHP helper normalises the second argument: if `$endPosition` is `null`, it equals `$startPosition` (so you extract a single element). Consistent with `LEFT(col, n)` which takes a single argument.

### `PRO_ARR_ESCAPE(elem)`

Escapes a string so it can be inserted as an element in a Progress array, protecting the native separator character.

```php
use function oihana\openedge\db\helpers\functions\strings\proArrayEscape ;

echo proArrayEscape( "'a;b'" ) ;
// PRO_ARR_ESCAPE('a;b')
// → result at execution: 'a\;b' (the ; separator is escaped)
```

Use when you build a SQL-side string destined to be inserted into an `ARRAY` column.

### `PRO_ARR_DESCAPE(arr_expr)`

Inverse operation: deserialises a serialised array, recovering its elements one by one.

```php
use function oihana\openedge\db\helpers\functions\strings\proArrayDescape ;

echo proArrayDescape( 'col_array' ) ;
// PRO_ARR_DESCAPE(col_array)
```

## Typical usage pattern

Concrete case from host applications: expose a customer's phone numbers through the REST API, stored in a Progress `ARRAY` column.

```php
use oihana\openedge\db\enums\functions\StringFunction ;
use oihana\openedge\enums\OpenEdge as SQL ;
use function oihana\openedge\db\helpers\expression ;

SQL::COLUMNS =>
[
    [
        SQL::ALIAS => 'phonePrimary' ,
        SQL::CONCAT =>
        [
            expression([
                SQL::COLUMN => 'col_phones' ,
                SQL::TABLE  => 'clients'    ,
                SQL::ALTER  => [ StringFunction::PRO_ELEMENT , 1 , 1 ] ,
            ])
        ],
    ],
    [
        SQL::ALIAS => 'phoneSecondary' ,
        SQL::CONCAT =>
        [
            expression([
                SQL::COLUMN => 'col_phones' ,
                SQL::TABLE  => 'clients'    ,
                SQL::ALTER  => [ StringFunction::PRO_ELEMENT , 2 , 2 ] ,
            ])
        ],
    ],
]
```

This pattern explodes a single `ARRAY` into two scalar columns on the API side.

## Alternative — expose as JSON on the API side

When you want to expose **all** elements of an `ARRAY` without knowing the count in advance, you fetch the full column on the PHP side and delegate parsing to application code:

```php
SQL::COLUMNS =>
[
    [ SQL::COLUMN => 'col_phones' , SQL::TABLE => 'clients' , SQL::ALIAS => 'phonesRaw' ] ,
]
```

Then in the controller or output schema:

```php
$phones = explode( ';' , $row[ 'phonesRaw' ] ) ;
// Caution: doesn't handle escaping. For that, keep PRO_ARR_DESCAPE.
```

## Pitfalls

### 1. Separator

The native Progress separator is typically `;`. If a value contains this character, it must be escaped (`PRO_ARR_ESCAPE`) so the serialisation doesn't break. Conversely, an `explode(';', ...)` on a raw `ARRAY` column may break on values containing the separator — better use `PRO_ARR_DESCAPE` on the SQL side.

### 2. 1-based indexing

`PRO_ELEMENT(col, 1, 1)` extracts the **first** element, not the second. Consistent with the rest of Progress, but unusual when you come from 0-indexed languages.

### 3. Empty arrays

If the `ARRAY` column is empty, `PRO_ELEMENT(col, 1, 1)` returns `NULL`. Think of a `COALESCE` or `NULLIF_EMPTY` to get a displayable default.

### 4. Performance

On tables with `ARRAY`s of hundreds of elements, calling `PRO_ELEMENT` on every call is costly. For repeated accesses to the same element, project once into a separate column then cache the result on the `oihana/openedge` side (`CacheableTrait` on the model, see [models.md](../models.md)).

## See also

- [String functions](../sql/sql-functions-strings.md) — other string helpers in the framework.
- [SQL conditionals](../sql/sql-functions-conditionals.md) — `NULLIF_EMPTY` to handle empty arrays.
- [Helpers](../helpers.md) — composing complex expressions.
- [Progress SQL — Array data type](https://docs.progress.com/bundle/openedge-sql-reference/page/Array-functions.html) — canonical reference.
