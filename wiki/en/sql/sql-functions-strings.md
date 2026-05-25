# String functions

OpenEdge exposes around thirty SQL functions for string manipulation. The [`StringFunction`](../../../src/oihana/openedge/db/enums/functions/StringFunction.php) enum lists them, and the framework provides a PHP helper per function under [`db/helpers/functions/strings/`](../../../src/oihana/openedge/db/helpers/functions/strings/) — one file per function, autoloaded via `composer.json`.

> **Canonical reference.** [Progress SQL — String functions](https://docs.progress.com/bundle/openedge-sql-reference/page/String-functions.html).

## Overview

| Family | Functions |
|---|---|
| Case | `LCASE`, `LOWER`, `UCASE`, `UPPER`, `INITCAP` |
| Length and codes | `LENGTH`, `ASCII`, `CHAR`, `CHR` |
| Position and search | `LOCATE`, `INSTR`, `DIFFERENCE`, `PREFIX`, `SUFFIX` |
| Cutting | `LEFT`, `RIGHT`, `SUBSTR`, `SUBSTRING` |
| Trim and padding | `LTRIM`, `RTRIM`, `LPAD`, `RPAD` |
| Replacement | `REPLACE`, `TRANSLATE`, `INSERT`, `REPEAT` |
| Concatenation | `CONCAT` |
| Progress-specific (arrays) | `PRO_ARR_ESCAPE`, `PRO_ARR_DESCAPE`, `PRO_ELEMENT` |

## Case

### `LOWER` / `LCASE`

```php
use function oihana\openedge\db\helpers\functions\strings\lower ;

echo lower( 'customer_name' ) ;
// LOWER(customer_name)
```

`LOWER` and `LCASE` do the same thing; `LCASE` is the standard ODBC spelling. The framework exposes both.

### `UPPER` / `UCASE`

```php
use function oihana\openedge\db\helpers\functions\strings\upper ;

echo upper( 'customer_name' ) ;
// UPPER(customer_name)
```

### `INITCAP`

Capitalises the first letter of each word, lowercases the rest.

```php
use function oihana\openedge\db\helpers\functions\strings\initCap ;

echo initCap( 'customer_name' ) ;
// INITCAP(customer_name)
```

Useful to normalise user-entered names without strict casing rules.

## Length and ASCII codes

### `LENGTH`

```php
use function oihana\openedge\db\helpers\functions\strings\length ;

echo length( 'customer_name' ) ;
// LENGTH(customer_name)
```

### `ASCII` / `CHR` / `CHAR`

Code ↔ character conversion.

```php
use function oihana\openedge\db\helpers\functions\strings\ascii ;
use function oihana\openedge\db\helpers\functions\strings\chr   ;
use function oihana\openedge\db\helpers\functions\strings\char  ;

echo ascii( 'col' ) ;       // ASCII(col)
echo chr  ( 65 )    ;       // CHR(65)    → 'A'
echo char ( 65 )    ;       // CHAR(65)   → 'A'
```

## Search and position

### `LOCATE` {#concat}

Position of a substring within a string (1-indexed, returns `0` if not found).

```php
use function oihana\openedge\db\helpers\functions\strings\locate ;

echo locate( "'@'" , 'email' ) ;
// LOCATE('@', email)
```

### `INSTR`

Progress variant of `LOCATE`, accepts an optional start position.

```php
use function oihana\openedge\db\helpers\functions\strings\inString ;

echo inString( 'email' , "'@'" ) ;
// INSTR(email, '@')
```

### `DIFFERENCE`

Soundex phonetic distance between two strings (returns `0` to `4`).

```php
use function oihana\openedge\db\helpers\functions\strings\difference ;

echo difference( "'Smith'" , "'Smyth'" ) ;
// DIFFERENCE('Smith', 'Smyth')
```

### `PREFIX` / `SUFFIX`

Check whether a string starts or ends with another (boolean).

```php
use function oihana\openedge\db\helpers\functions\strings\prefix ;
use function oihana\openedge\db\helpers\functions\strings\suffix ;

echo prefix( 'customer_name' , "'M.'" ) ;     // PREFIX(customer_name, 'M.')
echo suffix( 'file_name' , "'.pdf'" ) ;  // SUFFIX(file_name, '.pdf')
```

## Cutting

### `LEFT` / `RIGHT`

```php
use function oihana\openedge\db\helpers\functions\strings\left  ;
use function oihana\openedge\db\helpers\functions\strings\right ;

echo left ( 'postal_code' , 2 ) ;  // LEFT(postal_code, 2)
echo right( 'postal_code' , 3 ) ;  // RIGHT(postal_code, 3)
```

### `SUBSTR` / `SUBSTRING`

Substring, 1-indexed on the Progress side.

```php
use function oihana\openedge\db\helpers\functions\strings\substr    ;
use function oihana\openedge\db\helpers\functions\strings\substring ;

echo substr   ( 'nom' , 1 , 3 ) ;   // SUBSTR(nom, 1, 3)
echo substring( 'nom' , 1 , 3 ) ;   // SUBSTRING(nom, 1, 3)
```

Both are synonyms in Progress. The framework exposes both to avoid forcing a choice.

## Trim and padding

### `LTRIM` / `RTRIM`

```php
use function oihana\openedge\db\helpers\functions\strings\ltrim ;
use function oihana\openedge\db\helpers\functions\strings\rtrim ;

echo ltrim( 'customer_name' ) ;  // LTRIM(customer_name)
echo rtrim( 'customer_name' ) ;  // RTRIM(customer_name)
```

OpenEdge doesn't ship a `TRIM` that strips both sides in a single call — you have to nest `LTRIM(RTRIM(...))`.

### `LPAD` / `RPAD`

Left or right padding.

```php
use function oihana\openedge\db\helpers\functions\strings\lpad ;

echo lpad( 'customer_id' , 8 , "'0'" ) ;
// LPAD(customer_id, 8, '0')
```

## Replacement

### `REPLACE`

```php
use function oihana\openedge\db\helpers\functions\strings\replace ;

echo replace( 'customer_name' , "' '" , "'_'" ) ;
// REPLACE(customer_name, ' ', '_')
```

### `TRANSLATE`

Character-by-character substitution from a translation table.

```php
use function oihana\openedge\db\helpers\functions\strings\translate ;

echo translate( 'customer_name' , "'éèà'" , "'eea'" ) ;
// TRANSLATE(customer_name, 'éèà', 'eea')
```

### `INSERT`

Insert a string at a given position, optionally replacing N characters.

```php
use function oihana\openedge\db\helpers\functions\strings\insertInString ;

echo insertInString( 'nom' , 5 , 0 , "'***'" ) ;
// INSERT(nom, 5, 0, '***')
```

> The helper is named `insertInString()` in PHP to avoid the conflict with the PHP `insert` keyword used in practice.

### `REPEAT`

```php
use function oihana\openedge\db\helpers\functions\strings\repeat ;

echo repeat( "'-'" , 10 ) ;
// REPEAT('-', 10)
```

## Concatenation

### `CONCAT` {#concat-function}

```php
use function oihana\openedge\db\helpers\functions\strings\concat ;

echo concat( 'first_name' , 'customer_name' ) ;
// CONCAT(first_name, customer_name)
```

Preferable to the `||` operator for two operands: more readable and explicit. For more than two operands, the `||` operator is more practical (see [`ConcatOperator`](sql-operators.md#concatoperator)).

## Progress-specific — array manipulation

Progress OpenEdge has native `ARRAY`-type columns. Three functions let you manipulate them. See [Progress arrays](../progress/arrays.md) for details.

### `PRO_ARR_ESCAPE`

Serialises a string for use as an element inside a Progress array.

```php
use function oihana\openedge\db\helpers\functions\strings\proArrayEscape ;

echo proArrayEscape( 'customer_name' ) ;
// PRO_ARR_ESCAPE(customer_name)
```

### `PRO_ARR_DESCAPE`

Deserialises.

```php
use function oihana\openedge\db\helpers\functions\strings\proArrayDescape ;

echo proArrayDescape( 'col_array_serialisee' ) ;
// PRO_ARR_DESCAPE(col_array_serialisee)
```

### `PRO_ELEMENT`

Access an element of a Progress array by index (1-indexed).

```php
use function oihana\openedge\db\helpers\functions\strings\proElement ;

echo proElement( 'col_array' , 1 ) ;
// PRO_ELEMENT(col_array, 1)
```

## Composition with other helpers

String helpers naturally compose with [`columnExpression`](../helpers.md#columnexpression) through the `OpenEdge::ALTER` key:

```php
use oihana\openedge\db\enums\functions\StringFunction ;
use oihana\openedge\enums\OpenEdge as SQL ;
use function oihana\openedge\db\helpers\expression ;

echo expression([
    SQL::COLUMN => 'customer_name'              ,
    SQL::TABLE  => 'clients'                 ,
    SQL::ALTER  => StringFunction::UPPER     ,
]) ;
// → UPPER(clients.customer_name)
```

To chain multiple transformations, use `SQL::ALTERS`:

```php
echo expression([
    SQL::COLUMN => 'customer_name'              ,
    SQL::TABLE  => 'clients'                 ,
    SQL::ALTERS =>
    [
        StringFunction::LTRIM ,
        StringFunction::RTRIM ,
        StringFunction::UPPER ,
    ],
]) ;
// → UPPER(RTRIM(LTRIM(clients.customer_name)))
```

The order of `ALTERS` determines the application order: the **first element is applied first** (innermost in the SQL).

## See also

- [Date functions](sql-functions-dates.md) — date and time functions.
- [Conversions](sql-functions-conversions.md) — `TO_CHAR` for formatting dates as strings.
- [`CAST`](sql-functions-casts.md) — convert between types.
- [Progress arrays](../progress/arrays.md) — detail on Progress `ARRAY`s and their helpers.
- [Progress SQL — String functions](https://docs.progress.com/bundle/openedge-sql-reference/page/String-functions.html) — canonical reference.
