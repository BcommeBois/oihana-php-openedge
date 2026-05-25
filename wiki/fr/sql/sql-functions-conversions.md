# Conversions explicites

OpenEdge expose une famille de fonctions `TO_*` complémentaires de `CAST` : elles convertissent entre types **tolérante aux erreurs** (retour `NULL` si la valeur n'est pas parsable, là où `CAST` lèverait) et acceptent un **masque de format** pour les dates et nombres.

L'enum [`ConversionFunction`](../../../src/oihana/openedge/db/enums/functions/ConversionFunction.php) liste les sept conversions disponibles, et le framework fournit un helper par fonction sous [`db/helpers/functions/conversions/`](../../../src/oihana/openedge/db/helpers/functions/conversions/).

> **Référence canonique.** [Progress SQL — Conversion functions](https://docs.progress.com/bundle/openedge-sql-reference/page/Numeric-functions.html).

## `CAST` vs `TO_*` — quand utiliser quoi

| Critère | `CAST` | `TO_*` |
|---|---|---|
| Erreur si la valeur ne tient pas dans le type | **lève** | retourne `NULL` |
| Masque de format (dates, nombres) | non | **oui** |
| Standard SQL | oui (SQL-92) | non (Oracle-style) |
| ODBC compatible | oui | partiellement (`NVL` ne l'est pas, par exemple) |

Règle pratique :

- Quand la valeur **doit** être convertible (ex: `customer_id DECIMAL → VARCHAR` pour normalisation API), utiliser `CAST`. Un échec révèle un bug de donnée à corriger.
- Quand la valeur **peut** ne pas être convertible (ex: une chaîne saisie utilisateur convertie en date), utiliser `TO_*`. Le `NULL` produit en sortie peut être géré par `COALESCE`/`NVL`.

## `TO_CHAR()` {#to_char}

Convertit une expression vers `CHAR`, avec un masque de format optionnel. C'est la fonction la plus utile, parce qu'elle permet de **formater** une date ou un nombre côté serveur sans avoir à le faire côté PHP.

```php
use function oihana\openedge\db\helpers\functions\conversions\toChar ;

echo toChar( 'created_at' ) ;
// TO_CHAR(created_at)

echo toChar( 'created_at' , "'YYYY-MM-DD'" ) ;
// TO_CHAR(created_at, 'YYYY-MM-DD')

echo toChar( 'net_price' , "'999G990D00'" ) ;
// TO_CHAR(net_price, '999G990D00')  → "  1 234,56" (séparateur locale)
```

### Masques de format de date courants

| Masque | Exemple |
|---|---|
| `'YYYY-MM-DD'` | `2026-05-19` |
| `'DD/MM/YYYY'` | `19/05/2026` |
| `'YYYY-MM-DD HH24:MI:SS'` | `2026-05-19 14:30:00` |
| `'Day, DD Month YYYY'` | `Tuesday, 19 May 2026` |
| `'YYYY-WW'` | `2026-21` (numéro de semaine) |

### Masques de format de nombre courants

| Masque | Exemple |
|---|---|
| `'9999'` | nombre entier, 4 chiffres |
| `'9990D00'` | décimal avec 2 décimales |
| `'999G990D00'` | décimal avec séparateur de milliers et 2 décimales |
| `'9999PR'` | nombre négatif entre parenthèses |
| `'X9'` | hexadécimal |

> Le séparateur de milliers (`G`) et le séparateur décimal (`D`) sont **localisés** — `,` vs `.` selon les paramètres serveur.

## `TO_DATE()` {#to_date}

Convertit une chaîne en `DATE` selon un masque de format.

```php
use function oihana\openedge\db\helpers\functions\conversions\toDate ;

echo toDate( "'2026-05-19'" , "'YYYY-MM-DD'" ) ;
// TO_DATE('2026-05-19', 'YYYY-MM-DD')

echo toDate( 'date_str' , "'DD/MM/YYYY'" ) ;
// TO_DATE(date_str, 'DD/MM/YYYY')
```

Retourne `NULL` si la chaîne ne matche pas le format.

## `TO_TIME()` {#to_time}

Convertit une chaîne en `TIME`.

```php
use function oihana\openedge\db\helpers\functions\conversions\toTime ;

echo toTime( "'14:30:00'" , "'HH24:MI:SS'" ) ;
// TO_TIME('14:30:00', 'HH24:MI:SS')
```

## `TO_TIMESTAMP()` {#to_timestamp}

Convertit une chaîne en `TIMESTAMP`.

```php
use function oihana\openedge\db\helpers\functions\conversions\toTimestamp ;

echo toTimestamp( "'2026-05-19 14:30:00'" , "'YYYY-MM-DD HH24:MI:SS'" ) ;
// TO_TIMESTAMP('2026-05-19 14:30:00', 'YYYY-MM-DD HH24:MI:SS')
```

## `TO_NUMBER()` {#to_number}

Convertit une chaîne en nombre.

```php
use function oihana\openedge\db\helpers\functions\conversions\toNumber ;

echo toNumber( "'1234.56'" ) ;
// TO_NUMBER('1234.56')

echo toNumber( "'1 234,56'" , "'999G990D00'" ) ;
// TO_NUMBER('1 234,56', '999G990D00')
```

Le masque est optionnel mais nécessaire quand le séparateur n'est pas le `.` standard.

## Pattern d'usage typique

Dans une application hôte typique, `TO_CHAR` est très utilisé pour exposer une date ISO 8601 côté API à partir d'une colonne `DATE` Progress :

```php
use oihana\openedge\db\enums\functions\ConversionFunction ;
use oihana\openedge\enums\OpenEdge as SQL ;

SQL::COLUMNS =>
[
    [
        SQL::COLUMN => 'created_at'                      ,
        SQL::TABLE  => 'clients'                      ,
        SQL::ALTER  => [
            ConversionFunction::TO_CHAR ,
            "'YYYY-MM-DD'" ,
        ],
        SQL::ALIAS  => 'created' ,
    ],
]
// → TO_CHAR(clients.created_at, 'YYYY-MM-DD') AS "created"
```

## `CONVERT` et `DECODE` — non exposés en helpers

Deux constantes de [`ConversionFunction`](../../../src/oihana/openedge/db/enums/functions/ConversionFunction.php) n'ont pas de helper PHP dédié :

- **`CONVERT`** — variante ODBC du `CAST`, très peu utilisée en pratique. Préférer `CAST`.
- **`DECODE`** — équivalent de `CASE` Oracle. Préférer une expression `CASE WHEN` explicite ([`whenExpression`](sql-functions-cases.md)).

## Voir aussi

- [`CAST` et types SQL](sql-functions-casts.md) — conversion stricte avec erreur en cas d'échec.
- [Conditionnelles SQL](sql-functions-conditionals.md) — `COALESCE` / `NVL` pour gérer les `NULL` issus d'un `TO_*`.
- [Expressions `CASE`](sql-functions-cases.md) — alternative typée à `DECODE`.
- [Progress SQL — Conversion functions](https://docs.progress.com/bundle/openedge-sql-reference/page/Numeric-functions.html) — référence canonique.
