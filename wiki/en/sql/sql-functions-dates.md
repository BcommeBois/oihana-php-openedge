# Date functions

OpenEdge exposes a family of functions to manipulate dates, times and timestamps. The [`DateFunction`](../../../src/oihana/openedge/db/enums/functions/DateFunction.php) enum lists the 26 keywords; the framework provides helpers for the six most common (`CURDATE`, `CURTIME`, `NOW`, `SYSDATE`, `SYSTIME`, `SYSTIMESTAMP`) and a generic composer for the rest.

> **Canonical reference.** [Progress SQL — Date and time functions](https://docs.progress.com/bundle/openedge-sql-reference/page/Date-and-time-functions.html).

## `DateFunction` enum

| Constant | SQL value | Role |
|---|---|---|
| `ADD_MONTHS` | `ADD_MONTHS` | Add N months to a date. |
| `CURDATE` | `CURDATE` | Server-side current date (standard ODBC). |
| `CURTIME` | `CURTIME` | Server-side current time. |
| `NOW` | `NOW` | Current timestamp (standard ODBC). |
| `SYSDATE` | `SYSDATE` | Current date (Oracle-style, equivalent to `CURDATE`). |
| `SYSTIME` | `SYSTIME` | Current time (Oracle-style). |
| `SYSTIMESTAMP` | `SYSTIMESTAMP` | Timestamp with timezone. |
| `DAYNAME` | `DAYNAME` | Day name (`'Monday'`, `'Tuesday'`, …). |
| `DAYOFMONTH` | `DAYOFMONTH` | Day of month (1-31). |
| `DAYOFWEEK` | `DAYOFWEEK` | Day of week (1=Sunday … 7=Saturday). |
| `DAYOFYEAR` | `DAYOFYEAR` | Day of year (1-366). |
| `HOUR` | `HOUR` | Hour component (0-23). |
| `ISOWEEK` | `ISOWEEK` | ISO 8601 week. |
| `ISOWEEKDAY` | `ISOWEEKDAY` | ISO day of week (1=Monday … 7=Sunday). |
| `ISOYEAR` | `ISOYEAR` | ISO 8601 year. |
| `LAST_DAY` | `LAST_DAY` | Last day of the month. |
| `MINUTE` | `MINUTE` | Minute component. |
| `MONTHNAME` | `MONTHNAME` | Month name (`'January'`, …). |
| `MONTHS_BETWEEN` | `MONTHS_BETWEEN` | Number of months between two dates. |
| `NEXT_DAY` | `NEXT_DAY` | Next given weekday after a date. |
| `QUARTER` | `QUARTER` | Quarter (1-4). |
| `SECOND` | `SECOND` | Second component. |
| `TIMESTAMPADD` | `TIMESTAMPADD` | Add a duration to a timestamp. |
| `TIMESTAMPDIFF` | `TIMESTAMPDIFF` | Difference in units between two timestamps. |
| `WEEK` | `WEEK` | Week number. |
| `YEAR` | `YEAR` | Year component. |

## Native helpers — current dates and times

Six PHP helpers are provided for the no-argument functions that return the current date or time on the Progress server.

### `curDate()` / `sysDate()`

Current date on the server. Both helpers return the same thing; `CURDATE` is standard ODBC, `SYSDATE` is Oracle legacy.

```php
use function oihana\openedge\db\helpers\functions\dates\curDate ;
use function oihana\openedge\db\helpers\functions\dates\sysDate ;

echo curDate() ;  // CURDATE()
echo sysDate() ;  // SYSDATE
```

> **Syntax gotcha.** `CURDATE` is called with parentheses, `SYSDATE` without. The framework reflects this quirk.

### `curTime()` / `sysTime()`

Current time.

```php
use function oihana\openedge\db\helpers\functions\dates\curTime ;
use function oihana\openedge\db\helpers\functions\dates\sysTime ;

echo curTime() ;  // CURTIME()
echo sysTime() ;  // SYSTIME
```

### `now()` / `sysTimestamp()`

Current timestamp.

```php
use function oihana\openedge\db\helpers\functions\dates\now          ;
use function oihana\openedge\db\helpers\functions\dates\sysTimestamp ;

echo now()          ;  // NOW()
echo sysTimestamp() ;  // SYSTIMESTAMP
```

`SYSTIMESTAMP` includes the timezone; `NOW` is standard ODBC without timezone. Use `SYSTIMESTAMP` if you want to preserve the zone, `NOW` otherwise.

## Generic helper — `dateExpression()`

For date functions taking arguments (the vast majority), the framework exposes a generic composer [`dateExpression()`](../../../src/oihana/openedge/db/helpers/functions/dateExpression.php).

```php
use oihana\openedge\db\enums\functions\DateFunction ;
use function oihana\openedge\db\helpers\functions\dateExpression ;

// YEAR(dat_crt)
echo dateExpression( DateFunction::YEAR , 'dat_crt' ) ;

// ADD_MONTHS(dat_crt, 3)
echo dateExpression( DateFunction::ADD_MONTHS , [ 'dat_crt' , 3 ] ) ;

// MONTHS_BETWEEN(dat_fin, dat_crt)
echo dateExpression( DateFunction::MONTHS_BETWEEN , [ 'dat_fin' , 'dat_crt' ] ) ;
```

The helper accepts either a single string argument or an array of arguments.

## Typical compositions

### Filter by current year

```php
use oihana\openedge\db\enums\functions\DateFunction ;
use oihana\openedge\enums\OpenEdge as SQL ;
use function oihana\openedge\db\helpers\expression ;
use function oihana\openedge\db\helpers\functions\dates\curDate ;

SQL::WHERE =>
[
    SQL::LOGIC => Logic::AND ,
    SQL::CONDITIONS =>
    [
        [
            SQL::COLUMN   => 'dat_crt' ,
            SQL::TABLE    => 'clients' ,
            SQL::ALTER    => DateFunction::YEAR ,
            SQL::OPERATOR => '=' ,
            SQL::VALUE    => expression([
                SQL::COLUMN => curDate() ,
                SQL::ALTER  => DateFunction::YEAR ,
            ]) ,
        ],
    ],
]
// → YEAR(clients.dat_crt) = YEAR(CURDATE())
```

### Months difference between two dates

```php
echo expression([
    SQL::COLUMN => 'dat_fin'                  ,
    SQL::TABLE  => 'contrats'                 ,
    SQL::ALTER  => DateFunction::MONTHS_BETWEEN ,
    SQL::VALUE  => 'contrats.dat_crt'         ,
]) ;
// → MONTHS_BETWEEN(contrats.dat_fin, contrats.dat_crt)
```

## Low-level helpers

To produce a date literal on the SQL side (wrapped in Progress quotes), the framework exposes three lower-level helpers:

- [`timeExpression`](../helpers.md) — time literal `{ t 'hh:mm:ss' }`.
- [`timestampExpression`](../helpers.md) — timestamp literal `{ ts 'yyyy-mm-dd hh:mm:ss' }`.
- `dateExpression` (no single argument) — covers most cases.

See [helpers.md](../helpers.md) for details.

## See also

- [Conversions](sql-functions-conversions.md) — `TO_DATE`, `TO_TIME`, `TO_TIMESTAMP` for parsing strings.
- [String functions](sql-functions-strings.md) — `TO_CHAR` for output formatting.
- [`CAST`](sql-functions-casts.md) — explicit conversion to `DATE`/`TIME`/`TIMESTAMP`.
- [Progress SQL — Date and time functions](https://docs.progress.com/bundle/openedge-sql-reference/page/Date-and-time-functions.html) — canonical reference.
