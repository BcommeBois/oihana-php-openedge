# `CAST` et types SQL

Le `CAST` est l'opération SQL standard pour convertir une expression d'un type vers un autre. C'est l'outil le plus utilisé en pratique côté OpenEdge — les colonnes ERP sont souvent typées de façon inhabituelle (clients identifiés par `DECIMAL(15,0)` au lieu de `INTEGER`, codes pays en `CHAR(3)` avec padding d'espaces, dates encodées en `INTEGER` ou en `CHAR`…). On *cast* pour normaliser au format attendu côté API.

> **Référence canonique.** [Progress SQL — CAST](https://docs.progress.com/bundle/openedge-sql-reference/page/CAST.html).

## Le helper générique `cast()`

```php
use oihana\openedge\db\enums\Type ;
use function oihana\openedge\db\helpers\functions\cast ;

// Cast simple, sans paramètre
echo cast( 'prix_ht' , Type::INTEGER ) ;
// CAST(prix_ht AS INTEGER)

// Cast avec longueur
echo cast( 'nom_client' , Type::VARCHAR , 20 ) ;
// CAST(nom_client AS VARCHAR(20))

// Cast avec précision et échelle
echo cast( 'montant' , Type::DECIMAL , [ 10 , 2 ] ) ;
// CAST(montant AS DECIMAL(10, 2))
```

`cast()` valide que `$type` est bien une constante de `Type::*` (via `openEdgeType()`) et lève une `ConstantException` sinon. Pas de risque d'écrire `CAST(... AS UNKNOWN_TYPE)`.

## Helpers spécialisés — un par type

Pour les types les plus courants, le framework expose un helper dédié avec les arguments idiomatiques. Cela évite de retenir si `VARCHAR` prend `(length)` ou `(precision, scale)`.

### `castVARCHAR( expr , length = 1 )`

```php
use function oihana\openedge\db\helpers\functions\casts\castVARCHAR ;

echo castVARCHAR( 'cd_client' , 10 ) ;
// CAST(cd_client AS VARCHAR(10))
```

### `castCHAR( expr , length = 1 )`

Chaîne de longueur fixe, paddée d'espaces à droite.

### `castINTEGER( expr )`

```php
use function oihana\openedge\db\helpers\functions\casts\castINTEGER ;

echo castINTEGER( 'cd_client' ) ;
// CAST(cd_client AS INTEGER)
```

### `castBIGINT( expr )`

Entier sur 64 bits, utile quand on convertit un `DECIMAL(15,0)` ERP qui dépasse `2^31`.

### `castSMALLINT( expr )` / `castTINYINT( expr )`

Entiers petits formats — `SMALLINT` est sur 16 bits (-32 768 à 32 767), `TINYINT` sur 8 bits (-128 à 127).

### `castDECIMAL( expr , precision , scale )`

```php
use function oihana\openedge\db\helpers\functions\casts\castDECIMAL ;

echo castDECIMAL( 'prix_ht' , 10 , 2 ) ;
// CAST(prix_ht AS DECIMAL(10, 2))
```

### `castFLOAT( expr )` / `castREAL( expr )` / `castDOUBLE_PRECISION( expr )`

Trois précisions de virgule flottante. `REAL` est simple précision (32 bits), `DOUBLE PRECISION` est double précision (64 bits), `FLOAT` est paramétrable.

### `castDATE( expr )`

```php
use function oihana\openedge\db\helpers\functions\casts\castDATE ;

echo castDATE( 'dat_crt_str' ) ;
// CAST(dat_crt_str AS DATE)
```

### `castTIME( expr )` / `castTIMESTAMP( expr )`

```php
use function oihana\openedge\db\helpers\functions\casts\castTIMESTAMP ;

echo castTIMESTAMP( 'horodatage_str' ) ;
// CAST(horodatage_str AS TIMESTAMP)
```

### `castBIT( expr )`

Booléen sur 1 bit (0 ou 1).

### `castBINARY( expr , length )` / `castVARBINARY( expr , length )` / `castLVARBINARY( expr )`

Données binaires. `BINARY` est de longueur fixe, `VARBINARY` est variable, `LVARBINARY` est long (synonyme de `BLOB`).

### `castBLOB( expr )` / `castCLOB( expr )`

`BLOB` est binaire long ; `CLOB` est caractère long. Voir aussi `defaultLongDataBuffLen` côté DSN ([dsn.md](../dsn.md#defaultlongdatabufflen)).

## Catalogue des types OpenEdge

L'enum [`Type`](../../../src/oihana/openedge/db/enums/Type.php) liste les types Progress acceptés en `CAST` :

| Catégorie | Types |
|---|---|
| Entiers | `TINYINT`, `SMALLINT`, `INTEGER`, `BIGINT` |
| Décimaux | `DECIMAL` (alias `NUMERIC`, `NUMBER`), `REAL`, `FLOAT`, `DOUBLE_PRECISION` |
| Caractères | `CHAR`, `VARCHAR`, `LVARCHAR`, `CHAR_VARYING`, `CLOB` |
| Dates et heures | `DATE`, `TIME`, `TIMESTAMP`, `TIMESTAMP_WITH_TIME_ZONE` |
| Binaires | `BINARY`, `VARBINARY`, `LVARBINARY`, `BLOB` |
| Bit | `BIT` |
| Tableaux | `ARRAY`, `VARARRAY` |
| Spécial | `NULL` |

`DECIMAL` est l'orthographe ODBC standard ; `NUMERIC` et `NUMBER` sont des synonymes acceptés par Progress, exposés pour faciliter les copier-coller depuis des extraits Oracle.

## Pattern d'usage typique

Dans une définition de modèle, on cast côté `SQL::COLUMNS` pour normaliser :

```php
use oihana\openedge\db\enums\Type ;
use oihana\openedge\enums\OpenEdge as SQL ;

SQL::COLUMNS =>
[
    // L'ERP stocke cd_client en DECIMAL(15,0) ; on le veut en string côté API
    [
        SQL::COLUMN => 'cd_client'                ,
        SQL::TABLE  => 'clients'                  ,
        SQL::CAST   => [ Type::VARCHAR , 15 ]     ,
        SQL::ALIAS  => 'id'                       ,
    ],
    // L'ERP stocke prix_ht en DECIMAL(10,4) ; on tronque à 2 décimales pour la facturation
    [
        SQL::COLUMN => 'prix_ht'                  ,
        SQL::TABLE  => 'produits'                 ,
        SQL::CAST   => [ Type::DECIMAL , [ 10 , 2 ] ] ,
        SQL::ALIAS  => 'price'                    ,
    ],
]
```

> La clé `SQL::CAST` accepte trois formes : `string` (nom du type seul, sans paramètre), `[type, length]` (un paramètre), `[type, [p, s]]` (précision + échelle).

## Quand `CAST` n'est pas la bonne réponse

`CAST` lève une erreur si la valeur ne tient pas dans le type cible (par exemple `CAST('abc' AS INTEGER)`). Pour parser une chaîne en tolérant l'échec, préférer [`TO_NUMBER`](sql-functions-conversions.md#to_number), [`TO_DATE`](sql-functions-conversions.md#to_date), etc. Ces fonctions remontent un `NULL` au lieu de lever.

Voir [Conversions](sql-functions-conversions.md).

## Voir aussi

- [Conversions](sql-functions-conversions.md) — `TO_CHAR`, `TO_DATE`, `TO_NUMBER`, `TO_TIME`, `TO_TIMESTAMP`.
- [Helpers](../helpers.md#columnexpression) — `columnExpression()` qui intègre `SQL::CAST` au niveau colonne.
- [Progress SQL — CAST](https://docs.progress.com/bundle/openedge-sql-reference/page/CAST.html) — référence canonique.
