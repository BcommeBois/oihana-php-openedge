# Fonctions numériques

OpenEdge expose 23 fonctions numériques (arithmétique, trigonométrie, arrondi, agrégats simples). L'enum [`NumericFunction`](../../../src/oihana/openedge/db/enums/functions/NumericFunction.php) les liste, et le framework fournit un helper PHP par fonction sous [`db/helpers/functions/numerics/`](../../../src/oihana/openedge/db/helpers/functions/numerics/).

> **Référence canonique.** [Progress SQL — Numeric functions](https://docs.progress.com/bundle/openedge-sql-reference/page/Numeric-functions.html).

## Vue d'ensemble par famille

| Famille | Fonctions |
|---|---|
| Valeur absolue et signe | `ABS`, `SIGN` |
| Arrondi | `CEILING`, `FLOOR`, `ROUND` |
| Modulo et puissance | `MOD`, `POWER`, `SQRT`, `EXP` |
| Logarithmes | `LOG10` |
| Trigonométrie | `SIN`, `COS`, `TAN`, `ASIN`, `ACOS`, `ATAN`, `ATAN2` |
| Conversion angles | `DEGREES`, `RADIANS` |
| Constantes et aléatoire | `PI`, `RAND` |
| Min / max scalaires | `GREATEST`, `LEAST` |

## Valeur absolue et signe

### `ABS`

```php
use function oihana\openedge\db\helpers\functions\numerics\abs ;

echo abs( 'amount' ) ;
// ABS(amount)
```

### `SIGN`

Retourne `-1`, `0` ou `+1` selon le signe de l'expression.

```php
use function oihana\openedge\db\helpers\functions\numerics\sign ;

echo sign( 'solde' ) ;
// SIGN(solde)
```

## Arrondi

### `CEILING`

Plus petit entier supérieur ou égal.

```php
use function oihana\openedge\db\helpers\functions\numerics\ceiling ;

echo ceiling( 'net_price * 1.20' ) ;
// CEILING(net_price * 1.20)
```

### `FLOOR`

Plus grand entier inférieur ou égal.

```php
use function oihana\openedge\db\helpers\functions\numerics\floor ;

echo floor( 'net_price * 1.20' ) ;
// FLOOR(net_price * 1.20)
```

### `ROUND`

Arrondi à N décimales.

```php
use function oihana\openedge\db\helpers\functions\numerics\round ;

echo round( 'net_price * 1.20' , 2 ) ;
// ROUND(net_price * 1.20, 2)
```

> Pour formater un nombre en chaîne avec un séparateur décimal et des milliers, préférer [`TO_CHAR`](sql-functions-conversions.md#to_char) avec un masque de format.

## Modulo et puissance

### `MOD`

Reste de la division entière.

```php
use function oihana\openedge\db\helpers\functions\numerics\mod ;

echo mod( 'customer_id' , 100 ) ;
// MOD(customer_id, 100)
```

### `POWER`

Élévation à une puissance.

```php
use function oihana\openedge\db\helpers\functions\numerics\power ;

echo power( 'base' , 'exposant' ) ;
// POWER(base, exposant)

echo power( 2 , 10 ) ;
// POWER(2, 10) → 1024
```

### `SQRT`

Racine carrée.

```php
use function oihana\openedge\db\helpers\functions\numerics\sqrt ;

echo sqrt( 'surface' ) ;
// SQRT(surface)
```

### `EXP`

Exponentielle (e^x).

```php
use function oihana\openedge\db\helpers\functions\numerics\exp ;

echo exp( 'x' ) ;
// EXP(x)
```

## Logarithmes

### `LOG10`

Logarithme décimal.

```php
use function oihana\openedge\db\helpers\functions\numerics\log10 ;

echo log10( 'revenue' ) ;
// LOG10(revenue)
```

OpenEdge n'expose pas `LN` (log naturel) — utiliser `LOG10` et convertir si besoin.

## Trigonométrie

Toutes les fonctions trigonométriques attendent ou retournent des radians.

```php
use function oihana\openedge\db\helpers\functions\numerics\sin   ;
use function oihana\openedge\db\helpers\functions\numerics\cos   ;
use function oihana\openedge\db\helpers\functions\numerics\tan   ;
use function oihana\openedge\db\helpers\functions\numerics\asin  ;
use function oihana\openedge\db\helpers\functions\numerics\acos  ;
use function oihana\openedge\db\helpers\functions\numerics\atan  ;
use function oihana\openedge\db\helpers\functions\numerics\atan2 ;

echo sin  ( 'theta'   ) ;             // SIN(theta)
echo cos  ( 'theta'   ) ;             // COS(theta)
echo tan  ( 'theta'   ) ;             // TAN(theta)
echo asin ( 'x'       ) ;             // ASIN(x)
echo acos ( 'x'       ) ;             // ACOS(x)
echo atan ( 'x'       ) ;             // ATAN(x)
echo atan2( 'y' , 'x' ) ;             // ATAN2(y, x)
```

`ATAN2(y, x)` est préférable à `ATAN(y / x)` parce qu'il gère le quadrant correct (et la division par zéro).

## Conversion d'angles

### `DEGREES` / `RADIANS`

```php
use function oihana\openedge\db\helpers\functions\numerics\degrees ;
use function oihana\openedge\db\helpers\functions\numerics\radians ;

echo degrees( 'angle_rad' ) ;  // DEGREES(angle_rad)
echo radians( 'angle_deg' ) ;  // RADIANS(angle_deg)
```

## Constantes et aléatoire

### `PI`

```php
use function oihana\openedge\db\helpers\functions\numerics\pi ;

echo pi() ;
// PI()
```

### `RAND`

Nombre aléatoire entre 0 (inclus) et 1 (exclu).

```php
use function oihana\openedge\db\helpers\functions\numerics\rand ;

echo rand() ;
// RAND()
```

> Pour générer un nombre dans `[a, b]`, calculer `a + (b - a) * RAND()`.

## Min / max scalaires

### `GREATEST` / `LEAST`

Plus grande / plus petite valeur parmi N arguments. Ce ne sont pas des agrégats (qui opèrent sur N lignes) — voir [`MAX`/`MIN`](sql-functions-aggregates.md) pour les agrégats.

```php
use function oihana\openedge\db\helpers\functions\numerics\greatest ;
use function oihana\openedge\db\helpers\functions\numerics\least    ;

echo greatest([ 'net_price' , 'min_price' ]) ;  // GREATEST(net_price, min_price)
echo least   ([ 'net_price' , 'max_price' ]) ;  // LEAST(net_price, max_price)
```

## Composition

Comme pour les chaînes, les fonctions numériques se composent avec [`columnExpression`](../helpers.md) via `SQL::ALTER` ou `SQL::ALTERS` :

```php
use oihana\openedge\db\enums\functions\NumericFunction ;
use oihana\openedge\enums\OpenEdge as SQL ;
use function oihana\openedge\db\helpers\expression ;

echo expression([
    SQL::COLUMN => 'net_price'                    ,
    SQL::TABLE  => 'produits'                   ,
    SQL::ALTERS =>
    [
        [ NumericFunction::POWER , 2 ]          , // POWER(x, 2)
        NumericFunction::ROUND                    , // ROUND(POWER(x, 2))
    ],
]) ;
// → ROUND(POWER(products.net_price, 2))
```

## Voir aussi

- [Agrégats](sql-functions-aggregates.md) — `COUNT`, `SUM`, `AVG`, `MIN`, `MAX` (à ne pas confondre avec `LEAST`/`GREATEST`).
- [`CAST`](sql-functions-casts.md) — pour borner explicitement la précision après calcul.
- [Conversions](sql-functions-conversions.md) — `TO_NUMBER` pour parser une chaîne en nombre.
- [Progress SQL — Numeric functions](https://docs.progress.com/bundle/openedge-sql-reference/page/Numeric-functions.html) — référence canonique.
