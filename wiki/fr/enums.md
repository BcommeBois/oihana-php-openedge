# Référence des enums

Cette page récapitule les enums consommées par le framework. Pour chacune, elle donne le rôle, les constantes principales et le lien vers la page qui couvre son usage en détail.

> Toutes les enums héritent de `ConstantsTrait` (de `oihana/php-reflect`) qui leur donne les méthodes statiques `keys()`, `values()`, `has($value)`, `hasKey($name)`. Permet d'itérer ou de valider une valeur sans réflexion.

## `OpenEdge` — clés d'initialisation

L'enum centrale, équivalent du `Arango` de `oihana/arango`. Elle expose les ~60 clés de configuration acceptées par les modèles, contrôleurs et helpers.

```php
use oihana\openedge\enums\OpenEdge ;
// ou, plus court dans les definitions :
use oihana\openedge\enums\OpenEdge as SQL ;
```

### Groupes de constantes

| Famille | Constantes |
|---|---|
| Structure d'expression | `EXPRESSION`, `COLUMN`, `COLUMNS`, `TABLE`, `ALIAS`, `BIND`, `VALUE`, `LITERAL` |
| Composition d'expression | `CONCAT`, `LIST`, `ARRAY`, `SEPARATOR`, `CASE`, `WHEN`, `THEN`, `ELSE` |
| Transformation | `CAST`, `ALTER`, `ALTERS` |
| Clauses SQL | `FROM`, `WHERE`, `GROUP_BY`, `HAVING`, `ORDER_BY`, `JOIN`, `JOINS`, `ON` |
| Pagination | `LIMIT`, `OFFSET`, `TOP`, `COUNTER`, `DISTINCT` |
| Conditions | `CONDITIONS`, `PREDICATE`, `OPERATOR`, `LOGIC`, `PATTERN`, `QUERY` |
| Tri public | `SORT`, `SORTABLE` |
| Infrastructure | `CONTAINER`, `CONTEXT`, `CONTROLLER`, `PDO`, `SCHEMA`, `ROUTE`, `URL`, `PATH` |
| Cache | `CACHEABLE`, `CACHE_KEY` |
| Spécifique Progress | `NULLABLE`, `NULLABLE_COLUMN` (= `'(+)'`), `LOCKING_HINT`, `NOLOCK`, `NO_REORDER`, `HARVEST`, `WITH` |
| Métadonnées | `NAME`, `TYPE`, `TIMEZONE`, `MILLISECONDS`, `USE_PARENTHESES`, `CAPITALIZE`, `FACET`, `FACETS`, `FILTER`, `OPTIONS`, `PARAMS` |

### Renaming massif `as SQL`

Le pattern dominant dans les applications consommatrices importe `OpenEdge` sous l'alias `SQL` pour la lisibilité :

```php
use oihana\openedge\enums\OpenEdge as SQL ;

SQL::COLUMN
SQL::TABLE
SQL::WHERE
SQL::CAST
```

Plus court que `OpenEdge::WHERE` et plus parlant — on lit "SQL::WHERE", on comprend immédiatement.

## Enums SQL — `db/enums/`

### `Clause` — mots-clés SQL

`SELECT`, `FROM`, `WHERE`, `GROUP_BY`, `HAVING`, `ORDER_BY`, `OFFSET`, `FETCH`, `FIRST`, `NEXT`, `ONLY`, `ROW`, `ROWS`, `TOP`, `JOIN`, `INSERT`, `UPDATE`, `DELETE`, `SET`, `VALUES`, `AS`, `ON`, `WITH`, `FOR_UPDATE`, `TENANT`, `NO_REORDER`, `COUNT`.

Voir [Clauses SQL](sql/sql-clauses.md).

### `Predicate` — prédicats SQL

`ALL`, `BETWEEN`, `NOT_BETWEEN`, `DISTINCT`, `EXISTS`, `NOT_EXISTS`, `IN`, `NOT_IN`, `LIKE`, `NOT_LIKE`, `NULL` (`IS NULL`), `NOT_NULL` (`IS NOT NULL`), `ESCAPE`.

Voir [Prédicats SQL](sql/sql-predicates.md).

### `Logic` — connecteurs logiques

`AND`, `OR`, `NOT`, `AND_NOT`, `OR_NOT`.

Voir [Opérateurs SQL](sql/sql-operators.md#logic).

### `RelationalOperator` — comparateurs

`EQUAL` (`=`), `NOT_EQUAL` (`<>`), `LESS_THAN` (`<`), `LESS_THAN_OR_EQUAL` (`<=`), `GREATER_THAN` (`>`), `GREATER_THAN_OR_EQUAL` (`>=`).

Voir [Opérateurs SQL](sql/sql-operators.md#relationaloperator).

### `QuantifiedOperator` — quantificateurs

`ANY`, `ALL`, `SOME`.

Voir [Opérateurs SQL](sql/sql-operators.md#quantifiedoperator).

### `Operator` — opérateurs transverses

`ASSIGN` (`=` dans un `UPDATE SET`), `CONCAT` (`||`), `CONCAT_WITH_COMMA_SEPARATOR`.

### `ConcatOperator` — opérateurs de concaténation

`CONCAT` (`||`), `CONCAT_WITH_SPACE` (` || `), `CONCAT_WITH_COMMA_SEPARATOR` (` || ',' || `). Méthode statique `concatSeparator($sep)` pour personnaliser.

Voir [Opérateurs SQL](sql/sql-operators.md#concatoperator).

### `Type` — types SQL Progress

Numériques : `TINYINT`, `SMALLINT`, `INTEGER`, `BIGINT`, `DECIMAL` (alias `NUMERIC`, `NUMBER`), `REAL`, `FLOAT`, `DOUBLE_PRECISION`.
Caractères : `CHAR`, `VARCHAR`, `LVARCHAR`, `CHAR_VARYING`, `CLOB`.
Dates : `DATE`, `TIME`, `TIMESTAMP`, `TIMESTAMP_WITH_TIME_ZONE`.
Binaires : `BINARY`, `VARBINARY`, `LVARBINARY`, `BLOB`.
Spécial : `BIT`, `ARRAY`, `VARARRAY`, `NULL`.

Voir [`CAST` et types SQL](sql/sql-functions-casts.md).

### `Literal` — types de littéraux

`STRING`, `NUMERIC`, `DATE`, `TIME`, `TIMESTAMP`. Utilisé par [`literalExpression`](helpers.md).

### `Join` — types de jointures

`INNER`, `LEFT`, `LEFT_OUTER`, `CROSS`.

Voir [Construire une requête SQL pas à pas](sql/sql-building-queries.md).

### `LockingHint` — *locking hints* Progress

`NOLOCK`, `NOWAIT`, `READPAST`, `WAIT`, `WITH_NOLOCK`.

Voir [*Locking hints*](progress/locking-hints.md).

### `Facet` — composantes facultatives d'une requête

`EXPRESSION`, `TYPE` (paramètres), `EQUAL`, `IN` (types de facettes).

### `Conditions` — conditions spécialisées

Étend `RelationalOperator` avec quelques constantes spécifiques au framework.

## Enums de fonctions — `db/enums/functions/`

Huit enums qui listent les fonctions SQL acceptées par Progress et par les helpers du framework. Chaque enum correspond à une famille de fonctions, et la plupart sont consommées comme valeur de `OpenEdge::ALTER` dans une définition de colonne.

### `AggregateFunction`

`COUNT`, `SUM`, `AVG`, `MIN`, `MAX`.

Voir [Agrégats](sql/sql-functions-aggregates.md).

### `ConditionalFunction`

`CASE`, `COALESCE`, `IFNULL`, `NULLIF`, `NVL`, `DECODE`, plus deux extensions du framework : `NULLIF_EMPTY`, `NULLIF_ZERO`.

Voir [Conditionnelles SQL](sql/sql-functions-conditionals.md).

### `ConversionFunction`

`CAST`, `CONVERT`, `DECODE`, `TO_CHAR`, `TO_DATE`, `TO_TIME`, `TO_TIMESTAMP`, `TO_NUMBER`.

Voir [Conversions](sql/sql-functions-conversions.md).

### `StringFunction`

30 fonctions : `ASCII`, `CHAR`, `CHR`, `CONCAT`, `DIFFERENCE`, `INITCAP`, `INSERT`, `INSTR`, `LCASE`, `LEFT`, `LENGTH`, `LOCATE`, `LOWER`, `LPAD`, `LTRIM`, `PREFIX`, `PRO_ARR_DESCAPE`, `PRO_ARR_ESCAPE`, `PRO_ELEMENT`, `REPEAT`, `REPLACE`, `RIGHT`, `RPAD`, `RTRIM`, `SUBSTR`, `SUBSTRING`, `SUFFIX`, `TRANSLATE`, `UCASE`, `UPPER`.

Voir [Fonctions de chaînes](sql/sql-functions-strings.md).

### `DateFunction`

26 fonctions : `CURDATE`, `CURTIME`, `NOW`, `SYSDATE`, `SYSTIME`, `SYSTIMESTAMP`, `YEAR`, `MONTH`, `DAY`, `DAYNAME`, `DAYOFMONTH`, `DAYOFWEEK`, `DAYOFYEAR`, `HOUR`, `MINUTE`, `SECOND`, `QUARTER`, `WEEK`, `ISOWEEK`, `ISOWEEKDAY`, `ISOYEAR`, `MONTHNAME`, `ADD_MONTHS`, `LAST_DAY`, `MONTHS_BETWEEN`, `NEXT_DAY`, `TIMESTAMPADD`, `TIMESTAMPDIFF`.

Voir [Fonctions de dates](sql/sql-functions-dates.md).

### `NumericFunction`

23 fonctions : `ABS`, `ACOS`, `ASIN`, `ATAN`, `ATAN2`, `CEILING`, `COS`, `DEGREES`, `EXP`, `FLOOR`, `GREATEST`, `LEAST`, `LOG10`, `MOD`, `PI`, `POWER`, `RADIANS`, `RAND`, `ROUND`, `SIGN`, `SIN`, `SQRT`, `TAN`.

Voir [Fonctions numériques](sql/sql-functions-numerics.md).

### `SequenceFunction`

`NEXTVAL`, `CURRVAL` — fonctions de séquence Progress (peu utilisées en *reporting*).

### `SystemFunction`

`USER`, `DATABASE`, `VERSION`, `SYSDATE` — métadonnées système Progress.

## Voir aussi

- [Référence des helpers](helpers.md) — fonctions qui consomment ces enums.
- [Modèle `Documents`](models.md) — clés `OpenEdge::*` au constructeur.
- [`OpenEdgeQueryBuilder`](query-builder.md) — clés `OpenEdge::*` du *builder*.
