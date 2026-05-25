# Fonctions de chaînes

OpenEdge expose une trentaine de fonctions SQL pour manipuler les chaînes de caractères. L'enum [`StringFunction`](../../../src/oihana/openedge/db/enums/functions/StringFunction.php) les liste, et le framework fournit un helper PHP par fonction sous [`db/helpers/functions/strings/`](../../../src/oihana/openedge/db/helpers/functions/strings/) — un fichier par fonction, autoloadées via `composer.json`.

> **Référence canonique.** [Progress SQL — String functions](https://docs.progress.com/bundle/openedge-sql-reference/page/String-functions.html).

## Vue d'ensemble

| Famille | Fonctions |
|---|---|
| Casse | `LCASE`, `LOWER`, `UCASE`, `UPPER`, `INITCAP` |
| Longueur et codes | `LENGTH`, `ASCII`, `CHAR`, `CHR` |
| Position et recherche | `LOCATE`, `INSTR`, `DIFFERENCE`, `PREFIX`, `SUFFIX` |
| Découpe | `LEFT`, `RIGHT`, `SUBSTR`, `SUBSTRING` |
| Trim et padding | `LTRIM`, `RTRIM`, `LPAD`, `RPAD` |
| Remplacement | `REPLACE`, `TRANSLATE`, `INSERT`, `REPEAT` |
| Concaténation | `CONCAT` |
| Spécifique Progress (tableaux) | `PRO_ARR_ESCAPE`, `PRO_ARR_DESCAPE`, `PRO_ELEMENT` |

## Casse

### `LOWER` / `LCASE`

```php
use function oihana\openedge\db\helpers\functions\strings\lower ;

echo lower( 'nom_client' ) ;
// LOWER(nom_client)
```

`LOWER` et `LCASE` font la même chose ; `LCASE` est l'orthographe ODBC standard. Le framework expose les deux.

### `UPPER` / `UCASE`

```php
use function oihana\openedge\db\helpers\functions\strings\upper ;

echo upper( 'nom_client' ) ;
// UPPER(nom_client)
```

### `INITCAP`

Met la première lettre de chaque mot en majuscule, le reste en minuscule.

```php
use function oihana\openedge\db\helpers\functions\strings\initCap ;

echo initCap( 'nom_client' ) ;
// INITCAP(nom_client)
```

Pratique pour normaliser des noms saisis sans contrainte de casse.

## Longueur et codes ASCII

### `LENGTH`

```php
use function oihana\openedge\db\helpers\functions\strings\length ;

echo length( 'nom_client' ) ;
// LENGTH(nom_client)
```

### `ASCII` / `CHR` / `CHAR`

Conversion code ↔ caractère.

```php
use function oihana\openedge\db\helpers\functions\strings\ascii ;
use function oihana\openedge\db\helpers\functions\strings\chr   ;
use function oihana\openedge\db\helpers\functions\strings\char  ;

echo ascii( 'col' ) ;       // ASCII(col)
echo chr  ( 65 )    ;       // CHR(65)    → 'A'
echo char ( 65 )    ;       // CHAR(65)   → 'A'
```

## Recherche et position

### `LOCATE` {#concat}

Position d'une sous-chaîne dans une chaîne (1-indexed, retourne `0` si non trouvée).

```php
use function oihana\openedge\db\helpers\functions\strings\locate ;

echo locate( "'@'" , 'email' ) ;
// LOCATE('@', email)
```

### `INSTR`

Variante Progress de `LOCATE`, accepte un point de départ optionnel.

```php
use function oihana\openedge\db\helpers\functions\strings\inString ;

echo inString( 'email' , "'@'" ) ;
// INSTR(email, '@')
```

### `DIFFERENCE`

Distance phonétique Soundex entre deux chaînes (retourne `0` à `4`).

```php
use function oihana\openedge\db\helpers\functions\strings\difference ;

echo difference( "'Smith'" , "'Smyth'" ) ;
// DIFFERENCE('Smith', 'Smyth')
```

### `PREFIX` / `SUFFIX`

Vérifie si une chaîne commence ou finit par une autre (booléen).

```php
use function oihana\openedge\db\helpers\functions\strings\prefix ;
use function oihana\openedge\db\helpers\functions\strings\suffix ;

echo prefix( 'nom_client' , "'M.'" ) ;     // PREFIX(nom_client, 'M.')
echo suffix( 'nom_fichier' , "'.pdf'" ) ;  // SUFFIX(nom_fichier, '.pdf')
```

## Découpe

### `LEFT` / `RIGHT`

```php
use function oihana\openedge\db\helpers\functions\strings\left  ;
use function oihana\openedge\db\helpers\functions\strings\right ;

echo left ( 'cd_postal' , 2 ) ;  // LEFT(cd_postal, 2)
echo right( 'cd_postal' , 3 ) ;  // RIGHT(cd_postal, 3)
```

### `SUBSTR` / `SUBSTRING`

Sous-chaîne, 1-indexed côté Progress.

```php
use function oihana\openedge\db\helpers\functions\strings\substr    ;
use function oihana\openedge\db\helpers\functions\strings\substring ;

echo substr   ( 'nom' , 1 , 3 ) ;   // SUBSTR(nom, 1, 3)
echo substring( 'nom' , 1 , 3 ) ;   // SUBSTRING(nom, 1, 3)
```

Les deux sont synonymes côté Progress. Le framework expose les deux pour ne forcer aucun choix.

## Trim et padding

### `LTRIM` / `RTRIM`

```php
use function oihana\openedge\db\helpers\functions\strings\ltrim ;
use function oihana\openedge\db\helpers\functions\strings\rtrim ;

echo ltrim( 'nom_client' ) ;  // LTRIM(nom_client)
echo rtrim( 'nom_client' ) ;  // RTRIM(nom_client)
```

OpenEdge ne fournit pas de `TRIM` qui retire des deux côtés en un seul appel — il faut emboîter `LTRIM(RTRIM(...))`.

### `LPAD` / `RPAD`

Padding à gauche ou à droite.

```php
use function oihana\openedge\db\helpers\functions\strings\lpad ;

echo lpad( 'cd_client' , 8 , "'0'" ) ;
// LPAD(cd_client, 8, '0')
```

## Remplacement

### `REPLACE`

```php
use function oihana\openedge\db\helpers\functions\strings\replace ;

echo replace( 'nom_client' , "' '" , "'_'" ) ;
// REPLACE(nom_client, ' ', '_')
```

### `TRANSLATE`

Remplacement caractère par caractère selon une table.

```php
use function oihana\openedge\db\helpers\functions\strings\translate ;

echo translate( 'nom_client' , "'éèà'" , "'eea'" ) ;
// TRANSLATE(nom_client, 'éèà', 'eea')
```

### `INSERT`

Insère une chaîne à une position donnée, en remplaçant éventuellement N caractères.

```php
use function oihana\openedge\db\helpers\functions\strings\insertInString ;

echo insertInString( 'nom' , 5 , 0 , "'***'" ) ;
// INSERT(nom, 5, 0, '***')
```

> Le helper s'appelle `insertInString()` en PHP pour éviter le conflit avec le mot-clé PHP `insert` réservé en pratique.

### `REPEAT`

```php
use function oihana\openedge\db\helpers\functions\strings\repeat ;

echo repeat( "'-'" , 10 ) ;
// REPEAT('-', 10)
```

## Concaténation

### `CONCAT` {#concat-function}

```php
use function oihana\openedge\db\helpers\functions\strings\concat ;

echo concat( 'prenom_client' , 'nom_client' ) ;
// CONCAT(prenom_client, nom_client)
```

Préférable à l'opérateur `||` pour deux opérandes : plus lisible et explicite. Pour plus de deux opérandes, l'opérateur `||` reste plus pratique (voir [`ConcatOperator`](sql-operators.md#concatoperator)).

## Spécifique Progress — manipulation des tableaux

Progress OpenEdge a des colonnes natives de type `ARRAY`. Trois fonctions permettent de les manipuler. Voir [Tableaux Progress](../progress/arrays.md) pour le détail.

### `PRO_ARR_ESCAPE`

Sérialise une chaîne pour usage dans un tableau Progress.

```php
use function oihana\openedge\db\helpers\functions\strings\proArrayEscape ;

echo proArrayEscape( 'nom_client' ) ;
// PRO_ARR_ESCAPE(nom_client)
```

### `PRO_ARR_DESCAPE`

Désérialise.

```php
use function oihana\openedge\db\helpers\functions\strings\proArrayDescape ;

echo proArrayDescape( 'col_array_serialisee' ) ;
// PRO_ARR_DESCAPE(col_array_serialisee)
```

### `PRO_ELEMENT`

Accède à un élément d'un tableau Progress par index (1-indexed).

```php
use function oihana\openedge\db\helpers\functions\strings\proElement ;

echo proElement( 'col_array' , 1 ) ;
// PRO_ELEMENT(col_array, 1)
```

## Composition avec d'autres helpers

Les helpers de chaînes se composent naturellement avec [`columnExpression`](../helpers.md#columnexpression) via la clé `OpenEdge::ALTER` :

```php
use oihana\openedge\db\enums\functions\StringFunction ;
use oihana\openedge\enums\OpenEdge as SQL ;
use function oihana\openedge\db\helpers\expression ;

echo expression([
    SQL::COLUMN => 'nom_client'              ,
    SQL::TABLE  => 'clients'                 ,
    SQL::ALTER  => StringFunction::UPPER     ,
]) ;
// → UPPER(clients.nom_client)
```

Pour appliquer plusieurs transformations en chaîne, utiliser `SQL::ALTERS` :

```php
echo expression([
    SQL::COLUMN => 'nom_client'              ,
    SQL::TABLE  => 'clients'                 ,
    SQL::ALTERS =>
    [
        StringFunction::LTRIM ,
        StringFunction::RTRIM ,
        StringFunction::UPPER ,
    ],
]) ;
// → UPPER(RTRIM(LTRIM(clients.nom_client)))
```

L'ordre des `ALTERS` détermine l'ordre d'application : le **premier élément du tableau est appliqué en premier** (le plus interne dans le SQL).

## Voir aussi

- [Fonctions de dates](sql-functions-dates.md) — fonctions de date et heure.
- [Conversions](sql-functions-conversions.md) — `TO_CHAR` pour formater dates en chaîne.
- [`CAST`](sql-functions-casts.md) — convertir entre types.
- [Tableaux Progress](../progress/arrays.md) — détail sur les `ARRAY` Progress et leurs helpers.
- [Progress SQL — String functions](https://docs.progress.com/bundle/openedge-sql-reference/page/String-functions.html) — référence canonique.
