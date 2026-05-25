# Enums reference

This page recaps the enums consumed by the framework. For each, it gives the role, the main constants, and the link to the page that covers its usage in detail.

> All enums inherit from `ConstantsTrait` (from `oihana/php-reflect`), which gives them the static methods `keys()`, `values()`, `has($value)`, `hasKey($name)`. Useful to iterate or validate a value without reflection.

## `OpenEdge` — initialisation keys

The central enum, equivalent to `oihana/arango`'s `Arango`. Exposes ~60 configuration keys accepted by models, controllers, and helpers.

```php
use oihana\openedge\enums\OpenEdge ;
// or, shorter in definitions:
use oihana\openedge\enums\OpenEdge as SQL ;
```

### Constant groups

| Family | Constants |
|---|---|
| Expression structure | `EXPRESSION`, `COLUMN`, `COLUMNS`, `TABLE`, `ALIAS`, `BIND`, `VALUE`, `LITERAL` |
| Expression composition | `CONCAT`, `LIST`, `ARRAY`, `SEPARATOR`, `CASE`, `WHEN`, `THEN`, `ELSE` |
| Transformation | `CAST`, `ALTER`, `ALTERS` |
| SQL clauses | `FROM`, `WHERE`, `GROUP_BY`, `HAVING`, `ORDER_BY`, `JOIN`, `JOINS`, `ON` |
| Pagination | `LIMIT`, `OFFSET`, `TOP`, `COUNTER`, `DISTINCT` |
| Conditions | `CONDITIONS`, `PREDICATE`, `OPERATOR`, `LOGIC`, `PATTERN`, `QUERY` |
| Public sort | `SORT`, `SORTABLE` |
| Infrastructure | `CONTAINER`, `CONTEXT`, `CONTROLLER`, `PDO`, `SCHEMA`, `ROUTE`, `URL`, `PATH` |
| Cache | `CACHEABLE`, `CACHE_KEY` |
| Progress-specific | `NULLABLE`, `NULLABLE_COLUMN` (= `'(+)'`), `LOCKING_HINT`, `NOLOCK`, `NO_REORDER`, `HARVEST`, `WITH` |
| Metadata | `NAME`, `TYPE`, `TIMEZONE`, `MILLISECONDS`, `USE_PARENTHESES`, `CAPITALIZE`, `FACET`, `FACETS`, `FILTER`, `OPTIONS`, `PARAMS` |

### Bulk rename `as SQL`

The dominant pattern in host applications imports the central enum `OpenEdge` under the short alias `SQL` for readability:

```php
use oihana\openedge\enums\OpenEdge as SQL ;

SQL::COLUMN
SQL::TABLE
SQL::WHERE
SQL::CAST
```

Shorter than `OpenEdge::WHERE` and more meaningful — you read `SQL::WHERE`, you understand right away.

## SQL enums — `db/enums/`

### `Clause` — SQL keywords

`SELECT`, `FROM`, `WHERE`, `GROUP_BY`, `HAVING`, `ORDER_BY`, `OFFSET`, `FETCH`, `FIRST`, `NEXT`, `ONLY`, `ROW`, `ROWS`, `TOP`, `JOIN`, `INSERT`, `UPDATE`, `DELETE`, `SET`, `VALUES`, `AS`, `ON`, `WITH`, `FOR_UPDATE`, `TENANT`, `NO_REORDER`, `COUNT`.

See [SQL clauses](sql/sql-clauses.md).

### `Predicate` — SQL predicates

`ALL`, `BETWEEN`, `NOT_BETWEEN`, `DISTINCT`, `EXISTS`, `NOT_EXISTS`, `IN`, `NOT_IN`, `LIKE`, `NOT_LIKE`, `NULL` (`IS NULL`), `NOT_NULL` (`IS NOT NULL`), `ESCAPE`.

See [SQL predicates](sql/sql-predicates.md).

### `Logic` — logical connectors

`AND`, `OR`, `NOT`, `AND_NOT`, `OR_NOT`.

See [SQL operators](sql/sql-operators.md#logic).

### `RelationalOperator` — comparators

`EQUAL` (`=`), `NOT_EQUAL` (`<>`), `LESS_THAN` (`<`), `LESS_THAN_OR_EQUAL` (`<=`), `GREATER_THAN` (`>`), `GREATER_THAN_OR_EQUAL` (`>=`).

See [SQL operators](sql/sql-operators.md#relationaloperator).

### `QuantifiedOperator` — quantifiers

`ANY`, `ALL`, `SOME`.

See [SQL operators](sql/sql-operators.md#quantifiedoperator).

### `Operator` — cross-cutting operators

`ASSIGN` (`=` in `UPDATE SET`), `CONCAT` (`||`), `CONCAT_WITH_COMMA_SEPARATOR`.

### `ConcatOperator` — concatenation operators

`CONCAT` (`||`), `CONCAT_WITH_SPACE` (` || `), `CONCAT_WITH_COMMA_SEPARATOR` (` || ',' || `). Static method `concatSeparator($sep)` for customisation.

See [SQL operators](sql/sql-operators.md#concatoperator).

### `Type` — Progress SQL types

Numeric: `TINYINT`, `SMALLINT`, `INTEGER`, `BIGINT`, `DECIMAL` (alias `NUMERIC`, `NUMBER`), `REAL`, `FLOAT`, `DOUBLE_PRECISION`.
Character: `CHAR`, `VARCHAR`, `LVARCHAR`, `CHAR_VARYING`, `CLOB`.
Date: `DATE`, `TIME`, `TIMESTAMP`, `TIMESTAMP_WITH_TIME_ZONE`.
Binary: `BINARY`, `VARBINARY`, `LVARBINARY`, `BLOB`.
Special: `BIT`, `ARRAY`, `VARARRAY`, `NULL`.

See [`CAST` and SQL types](sql/sql-functions-casts.md).

### `Literal` — literal types

`STRING`, `NUMERIC`, `DATE`, `TIME`, `TIMESTAMP`. Used by [`literalExpression`](helpers.md).

### `Join` — join types

`INNER`, `LEFT`, `LEFT_OUTER`, `CROSS`.

See [Building a SQL query step by step](sql/sql-building-queries.md).

### `LockingHint` — Progress locking hints

`NOLOCK`, `NOWAIT`, `READPAST`, `WAIT`, `WITH_NOLOCK`.

See [Locking hints](progress/locking-hints.md).

### `Facet` — optional query components

`EXPRESSION`, `TYPE` (parameters), `EQUAL`, `IN` (facet types).

### `Conditions` — specialised conditions

Extends `RelationalOperator` with a few framework-specific constants.

## Function enums — `db/enums/functions/`

Eight enums that list the SQL functions accepted by Progress and by the framework helpers. Each enum corresponds to a function family, and most are consumed as the value of `OpenEdge::ALTER` in a column definition.

### `AggregateFunction`

`COUNT`, `SUM`, `AVG`, `MIN`, `MAX`.

See [Aggregates](sql/sql-functions-aggregates.md).

### `ConditionalFunction`

`CASE`, `COALESCE`, `IFNULL`, `NULLIF`, `NVL`, `DECODE`, plus two framework extensions: `NULLIF_EMPTY`, `NULLIF_ZERO`.

See [SQL conditionals](sql/sql-functions-conditionals.md).

### `ConversionFunction`

`CAST`, `CONVERT`, `DECODE`, `TO_CHAR`, `TO_DATE`, `TO_TIME`, `TO_TIMESTAMP`, `TO_NUMBER`.

See [Conversions](sql/sql-functions-conversions.md).

### `StringFunction`

30 functions: `ASCII`, `CHAR`, `CHR`, `CONCAT`, `DIFFERENCE`, `INITCAP`, `INSERT`, `INSTR`, `LCASE`, `LEFT`, `LENGTH`, `LOCATE`, `LOWER`, `LPAD`, `LTRIM`, `PREFIX`, `PRO_ARR_DESCAPE`, `PRO_ARR_ESCAPE`, `PRO_ELEMENT`, `REPEAT`, `REPLACE`, `RIGHT`, `RPAD`, `RTRIM`, `SUBSTR`, `SUBSTRING`, `SUFFIX`, `TRANSLATE`, `UCASE`, `UPPER`.

See [String functions](sql/sql-functions-strings.md).

### `DateFunction`

26 functions: `CURDATE`, `CURTIME`, `NOW`, `SYSDATE`, `SYSTIME`, `SYSTIMESTAMP`, `YEAR`, `MONTH`, `DAY`, `DAYNAME`, `DAYOFMONTH`, `DAYOFWEEK`, `DAYOFYEAR`, `HOUR`, `MINUTE`, `SECOND`, `QUARTER`, `WEEK`, `ISOWEEK`, `ISOWEEKDAY`, `ISOYEAR`, `MONTHNAME`, `ADD_MONTHS`, `LAST_DAY`, `MONTHS_BETWEEN`, `NEXT_DAY`, `TIMESTAMPADD`, `TIMESTAMPDIFF`.

See [Date functions](sql/sql-functions-dates.md).

### `NumericFunction`

23 functions: `ABS`, `ACOS`, `ASIN`, `ATAN`, `ATAN2`, `CEILING`, `COS`, `DEGREES`, `EXP`, `FLOOR`, `GREATEST`, `LEAST`, `LOG10`, `MOD`, `PI`, `POWER`, `RADIANS`, `RAND`, `ROUND`, `SIGN`, `SIN`, `SQRT`, `TAN`.

See [Numeric functions](sql/sql-functions-numerics.md).

### `SequenceFunction`

`NEXTVAL`, `CURRVAL` — Progress sequence functions (rarely used in reporting).

### `SystemFunction`

`USER`, `DATABASE`, `VERSION`, `SYSDATE` — Progress system metadata.

## See also

- [Helpers reference](helpers.md) — functions consuming these enums.
- [`Documents` model](models.md) — `OpenEdge::*` keys at the constructor.
- [`OpenEdgeQueryBuilder`](query-builder.md) — `OpenEdge::*` builder keys.
