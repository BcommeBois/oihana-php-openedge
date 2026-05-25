# Fonctions de dates

OpenEdge expose une famille de fonctions pour manipuler les dates, les heures et les timestamps. L'enum [`DateFunction`](../../../src/oihana/openedge/db/enums/functions/DateFunction.php) liste les 26 mots-clés ; le framework fournit des helpers pour les six fonctions les plus courantes (`CURDATE`, `CURTIME`, `NOW`, `SYSDATE`, `SYSTIME`, `SYSTIMESTAMP`) et un *composer* générique pour les autres.

> **Référence canonique.** [Progress SQL — Date and time functions](https://docs.progress.com/bundle/openedge-sql-reference/page/Date-and-time-functions.html).

## Enum `DateFunction`

| Constante | Valeur SQL | Rôle |
|---|---|---|
| `ADD_MONTHS` | `ADD_MONTHS` | Ajoute N mois à une date. |
| `CURDATE` | `CURDATE` | Date du jour côté serveur (ODBC standard). |
| `CURTIME` | `CURTIME` | Heure courante côté serveur. |
| `NOW` | `NOW` | Timestamp courant (ODBC standard). |
| `SYSDATE` | `SYSDATE` | Date courante (Oracle-style, équivalent `CURDATE`). |
| `SYSTIME` | `SYSTIME` | Heure courante (Oracle-style). |
| `SYSTIMESTAMP` | `SYSTIMESTAMP` | Timestamp avec fuseau horaire. |
| `DAYNAME` | `DAYNAME` | Nom du jour de la semaine (`'Monday'`, `'Tuesday'`, …). |
| `DAYOFMONTH` | `DAYOFMONTH` | Jour du mois (1-31). |
| `DAYOFWEEK` | `DAYOFWEEK` | Jour de la semaine (1=dimanche … 7=samedi). |
| `DAYOFYEAR` | `DAYOFYEAR` | Quantième de l'année (1-366). |
| `HOUR` | `HOUR` | Composante heure (0-23). |
| `ISOWEEK` | `ISOWEEK` | Semaine ISO 8601. |
| `ISOWEEKDAY` | `ISOWEEKDAY` | Jour de la semaine ISO (1=lundi … 7=dimanche). |
| `ISOYEAR` | `ISOYEAR` | Année ISO 8601. |
| `LAST_DAY` | `LAST_DAY` | Dernier jour du mois. |
| `MINUTE` | `MINUTE` | Composante minute. |
| `MONTHNAME` | `MONTHNAME` | Nom du mois (`'January'`, …). |
| `MONTHS_BETWEEN` | `MONTHS_BETWEEN` | Nombre de mois entre deux dates. |
| `NEXT_DAY` | `NEXT_DAY` | Prochain jour de la semaine après une date. |
| `QUARTER` | `QUARTER` | Trimestre (1-4). |
| `SECOND` | `SECOND` | Composante seconde. |
| `TIMESTAMPADD` | `TIMESTAMPADD` | Ajoute une durée à un timestamp. |
| `TIMESTAMPDIFF` | `TIMESTAMPDIFF` | Différence en unités entre deux timestamps. |
| `WEEK` | `WEEK` | Numéro de semaine. |
| `YEAR` | `YEAR` | Composante année. |

## Helpers natifs — dates et heures courantes

Six helpers PHP sont fournis pour les fonctions sans arguments, qui retournent l'heure ou la date côté serveur Progress.

### `curDate()` / `sysDate()`

Date du jour côté serveur. Les deux helpers retournent la même chose ; `CURDATE` est ODBC standard, `SYSDATE` est l'héritage Oracle.

```php
use function oihana\openedge\db\helpers\functions\dates\curDate ;
use function oihana\openedge\db\helpers\functions\dates\sysDate ;

echo curDate() ;  // CURDATE()
echo sysDate() ;  // SYSDATE
```

> **Attention syntaxique.** `CURDATE` s'appelle avec des parenthèses, `SYSDATE` sans. Le framework reflète cette particularité.

### `curTime()` / `sysTime()`

Heure courante.

```php
use function oihana\openedge\db\helpers\functions\dates\curTime ;
use function oihana\openedge\db\helpers\functions\dates\sysTime ;

echo curTime() ;  // CURTIME()
echo sysTime() ;  // SYSTIME
```

### `now()` / `sysTimestamp()`

Timestamp courant.

```php
use function oihana\openedge\db\helpers\functions\dates\now          ;
use function oihana\openedge\db\helpers\functions\dates\sysTimestamp ;

echo now()          ;  // NOW()
echo sysTimestamp() ;  // SYSTIMESTAMP
```

`SYSTIMESTAMP` inclut le fuseau horaire ; `NOW` est ODBC standard sans fuseau. Utiliser `SYSTIMESTAMP` si on veut conserver la zone, `NOW` sinon.

## Helper générique — `dateExpression()`

Pour les fonctions de date qui prennent des arguments (la grande majorité), le framework expose un *composer* générique [`dateExpression()`](../../../src/oihana/openedge/db/helpers/functions/dateExpression.php).

```php
use oihana\openedge\db\enums\functions\DateFunction ;
use function oihana\openedge\db\helpers\functions\dateExpression ;

// YEAR(created_at)
echo dateExpression( DateFunction::YEAR , 'created_at' ) ;

// ADD_MONTHS(created_at, 3)
echo dateExpression( DateFunction::ADD_MONTHS , [ 'created_at' , 3 ] ) ;

// MONTHS_BETWEEN(end_date, created_at)
echo dateExpression( DateFunction::MONTHS_BETWEEN , [ 'end_date' , 'created_at' ] ) ;
```

Le helper accepte soit un argument unique (chaîne), soit un tableau d'arguments.

## Composition typique

### Filtrer par année courante

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
            SQL::COLUMN   => 'created_at' ,
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
// → YEAR(clients.created_at) = YEAR(CURDATE())
```

### Différence entre deux dates en mois

```php
echo expression([
    SQL::COLUMN => 'end_date'                  ,
    SQL::TABLE  => 'contrats'                 ,
    SQL::ALTER  => DateFunction::MONTHS_BETWEEN ,
    SQL::VALUE  => 'contracts.created_at'         ,
]) ;
// → MONTHS_BETWEEN(contracts.end_date, contracts.created_at)
```

## Helpers de bas niveau

Pour produire un littéral de date côté SQL (entouré de quotes Progress), le framework expose trois helpers de plus bas niveau :

- [`timeExpression`](../helpers.md) — littéral de temps `{ t 'hh:mm:ss' }`.
- [`timestampExpression`](../helpers.md) — littéral de timestamp `{ ts 'yyyy-mm-dd hh:mm:ss' }`.
- `dateExpression` (sans argument unique) — couvre la majorité des cas.

Voir [helpers.md](../helpers.md) pour le détail.

## Voir aussi

- [Conversions](sql-functions-conversions.md) — `TO_DATE`, `TO_TIME`, `TO_TIMESTAMP` pour parser une chaîne.
- [Fonctions de chaînes](sql-functions-strings.md) — `TO_CHAR` côté formatage sortie.
- [`CAST`](sql-functions-casts.md) — conversion explicite vers `DATE`/`TIME`/`TIMESTAMP`.
- [Progress SQL — Date and time functions](https://docs.progress.com/bundle/openedge-sql-reference/page/Date-and-time-functions.html) — référence canonique.
