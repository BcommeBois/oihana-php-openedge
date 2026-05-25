# Tableaux Progress

Progress OpenEdge propose un type natif `ARRAY` pour stocker plusieurs valeurs dans une seule colonne — héritage du langage ABL où c'est une primitive de premier ordre. Côté SQL, ces tableaux se présentent comme une chaîne sérialisée avec un séparateur spécifique. Trois fonctions SQL permettent de les manipuler, et le framework les expose en helpers PHP.

> **Référence canonique.** [Progress SQL — Array data type](https://docs.progress.com/bundle/openedge-sql-reference/page/Array-functions.html).

## Le type `ARRAY` Progress en deux phrases

Une colonne `ARRAY` Progress contient N valeurs du même type, où N est défini à la création (`extent` ABL). Quand on les lit via SQL, elles ressortent sous forme d'une chaîne sérialisée — les éléments sont séparés et chaque élément est éventuellement *escapé* si son contenu contient le caractère séparateur.

Cette représentation sérialisée est lisible mais incommode pour les traitements applicatifs. Les trois fonctions Progress ci-dessous facilitent la conversion.

## Les trois fonctions

### `PRO_ELEMENT(array_expr, start, [end])`

Extrait un ou plusieurs éléments d'un tableau Progress par leur position. Les positions sont **1-indexées**.

```php
use function oihana\openedge\db\helpers\functions\strings\proElement ;

echo proElement( 'col_phones' , 1 ) ;
// PRO_ELEMENT(col_phones, 1, 1)

echo proElement( 'col_phones' , 1 , 3 ) ;
// PRO_ELEMENT(col_phones, 1, 3)
```

> Le helper PHP normalise le deuxième argument : si `$endPosition` est `null`, il vaut `$startPosition` (donc on extrait un seul élément). Cohérent avec `LEFT(col, n)` qui prend un seul argument.

### `PRO_ARR_ESCAPE(elem)`

Échappe une chaîne pour qu'elle puisse être insérée comme un élément dans un tableau Progress, en protégeant le caractère séparateur natif.

```php
use function oihana\openedge\db\helpers\functions\strings\proArrayEscape ;

echo proArrayEscape( "'a;b'" ) ;
// PRO_ARR_ESCAPE('a;b')
// → résultat à l'exécution : 'a\;b' (le séparateur ; est échappé)
```

À utiliser quand on construit côté SQL une chaîne destinée à être insérée dans une colonne `ARRAY`.

### `PRO_ARR_DESCAPE(arr_expr)`

Opération inverse : désérialise un tableau sérialisé en récupérant ses éléments un par un.

```php
use function oihana\openedge\db\helpers\functions\strings\proArrayDescape ;

echo proArrayDescape( 'col_array' ) ;
// PRO_ARR_DESCAPE(col_array)
```

## Pattern d'usage typique

Cas concret de l.application hôte : exposer en API REST les téléphones d'un client, stockés en colonne `ARRAY` Progress.

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

Ce pattern explose un seul `ARRAY` en deux colonnes scalaires côté API.

## Alternative — exposer en JSON côté API

Quand on veut exposer **tous** les éléments d'un `ARRAY` sans connaître leur nombre à l'avance, on remonte la colonne entière côté PHP et on délègue le parsing au code applicatif :

```php
SQL::COLUMNS =>
[
    [ SQL::COLUMN => 'col_phones' , SQL::TABLE => 'clients' , SQL::ALIAS => 'phonesRaw' ] ,
]
```

Puis côté contrôleur ou côté schéma de sortie :

```php
$phones = explode( ';' , $row[ 'phonesRaw' ] ) ;
// Attention : ne gère pas l'escaping. Pour ça, garder PRO_ARR_DESCAPE.
```

## Pièges

### 1. Séparateur

Le séparateur natif Progress est typiquement `;`. Si une valeur contient ce caractère, elle doit être escapée (`PRO_ARR_ESCAPE`) pour ne pas casser la sérialisation. À l'inverse, un `explode(';', ...)` côté PHP sur une colonne `ARRAY` brute peut casser sur les valeurs avec séparateur — mieux vaut utiliser `PRO_ARR_DESCAPE` côté SQL.

### 2. Indexation 1-based

`PRO_ELEMENT(col, 1, 1)` extrait le **premier** élément, pas le deuxième. Cohérent avec le reste de Progress, mais inhabituel quand on vient de langages 0-indexés.

### 3. Tableaux vides

Si la colonne `ARRAY` est vide, `PRO_ELEMENT(col, 1, 1)` retourne `NULL`. Penser à un `COALESCE` ou un `NULLIF_EMPTY` pour avoir un défaut affichable.

### 4. Performance

Sur des tables avec des `ARRAY` de plusieurs centaines d'éléments, `PRO_ELEMENT` à chaque appel est coûteux. Pour des accès répétés au même élément, mieux vaut le projeter une fois en colonne séparée puis cacher le résultat côté `oihana/openedge` (`CacheableTrait` du modèle, voir [models.md](../models.md)).

## Voir aussi

- [Fonctions de chaînes](../sql/sql-functions-strings.md) — autres helpers de chaîne du framework.
- [Conditionnelles SQL](../sql/sql-functions-conditionals.md) — `NULLIF_EMPTY` pour gérer les tableaux vides.
- [Helpers](../helpers.md) — composition d'expressions complexes.
- [Progress SQL — Array data type](https://docs.progress.com/bundle/openedge-sql-reference/page/Array-functions.html) — référence canonique.
