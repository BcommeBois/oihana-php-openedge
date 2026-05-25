# `Alters` et dénormalisation

Le système d'`alters` du modèle [`Documents`](models.md) permet d'appliquer des **transformations post-fetch** aux données ramenées par PDO, avant que le modèle ne les retourne à l'appelant. C'est l'outil qui sert à normaliser les types (`'1'` → `1`), à hydrater des références (`cd_pays = 'FR'` → `{ id: 'FR', name: 'France' }`), à enrichir avec une URL canonique, ou à appliquer une logique métier (`Alter::CALL`).

C'est complémentaire de [`CAST`](sql/sql-functions-casts.md) côté SQL : `CAST` transforme **côté serveur**, `Alters` transforme **côté PHP** après réception.

> Les `Alters` vivent dans l'écosystème `oihana/php-system` (enum `Alter`, trait `AlterBindVarsTrait`) et sont utilisables identiquement par les modèles d'autres bases (ArangoDB). Cette page documente le pattern côté OpenEdge.

## Déclaration

Les `Alters` se déclarent au constructeur du modèle, sous la clé `ModelParam::ALTERS` :

```php
use app\enums\Models ;
use app\enums\Prop   ;
use oihana\models\enums\Alter      ;
use oihana\models\enums\ModelParam ;
use oihana\openedge\enums\OpenEdge as SQL ;
use oihana\openedge\models\Documents ;

new Documents( $container ,
[
    ModelParam::PDO    => Databases::ODBC_ERP ,
    ModelParam::SCHEMA => Customer::class     ,
    ModelParam::ALTERS =>
    [
        Prop::URL              => [ Alter::URL  , '/customers' ]                                ,
        Prop::AREA_SERVED      => [ Alter::NORMALIZE , [ Alter::GET  , Models::THESAURUS_SHIPPING_AREA_SERVED ] ] ,
        Prop::CATEGORY         => [ Alter::NORMALIZE , [ Alter::GET  , Models::THESAURUS_CUSTOMERS_CATEGORIES ] ] ,
        Prop::WEBSITE          => [ Alter::CALL      , fn( ?string $value ) => !empty( $value ) ? new WebSite([ 'url' => $value ]) : null ] ,
    ],
    ModelParam::QUERY_BUILDER => [ /* … */ ] ,
])
```

La clé est le **nom du champ** dans le document de sortie (souvent issu de l'alias d'une colonne SQL ou du nom de la propriété du schéma Schema.org). La valeur est une liste `[ Alter::TYPE , ...args ]`.

## Catalogue des transformations

### `Alter::URL` — URL canonique

Construit une URL pour le document à partir d'un préfixe et de la valeur d'un autre champ (typiquement l'identifiant).

```php
Prop::URL => [ Alter::URL , '/customers' ]
// → injecte { url: '/customers/1274' } sur chaque ligne
```

Le préfixe + `/{id}` produit l'URL. Utile pour les API qui doivent émettre du HATEOAS ou simplement faciliter le clic depuis une UI admin.

### `Alter::GET` — lookup vers un autre modèle

Remplace une valeur scalaire par le document complet récupéré depuis un autre modèle. C'est le cas d'usage le plus puissant — il permet la **dénormalisation cross-base** : on lit une référence côté OpenEdge (un code pays `'FR'`) et on remonte le document complet depuis ArangoDB (ou une autre base) qui contient le thésaurus enrichi (`{ id: 'FR', name: 'France', flag: '🇫🇷' }`).

```php
Prop::CATEGORY => [ Alter::GET , Models::THESAURUS_CUSTOMERS_CATEGORIES ]
// → la valeur "12" du champ CATEGORY est remplacée par le document du thésaurus avec id=12
```

Le modèle cible doit avoir une méthode `get` qui accepte `[ id => valeur ]`. C'est le cas de tous les modèles `Documents` du framework (OpenEdge et ArangoDB).

> **Cross-base.** Le pattern est particulièrement utile pour dénormaliser depuis OpenEdge (où les références sont des codes courts) vers ArangoDB (où les thésaurus complets et i18n vivent). Le modèle source (OpenEdge) ne fait pas la jointure SQL — il délègue à un modèle cible qui peut être n'importe où.

### `Alter::NORMALIZE` — wrapper sur une autre transformation

Applique la transformation enfant **uniquement si la valeur n'est pas vide** (différente de `null` et de chaîne vide). Pratique pour les references optionnelles.

```php
Prop::CATEGORY => [ Alter::NORMALIZE , [ Alter::GET , Models::THESAURUS_CATEGORIES ] ]
// Si CATEGORY est null/vide → reste null
// Sinon → lookup et remplace
```

Sans `NORMALIZE`, un `Alter::GET` sur une valeur vide tenterait quand même un `get(['id' => null])` ce qui est inutile et bruyant en log.

### `Alter::CALL` — appel à un callable

Permet d'appliquer une transformation arbitraire via une fonction PHP. Le callable reçoit la valeur actuelle et retourne la nouvelle valeur.

```php
Prop::WEBSITE => [ Alter::CALL , fn( ?string $value ) => !empty( $value )
    ? new WebSite([ 'url' => $value ])
    : null
]
```

Utilisé typiquement pour wrapper une valeur scalaire dans un objet Schema.org (un `WebSite` autour d'une URL, un `PostalAddress` autour d'un code), ou pour appliquer une logique métier complexe qu'on ne veut pas faire en SQL.

### `Alter::INT` — cast en entier

Force la valeur en `int`. Court-circuit du `(int)` PHP.

```php
Prop::AREA_SERVED => Alter::INT
```

Utile quand le `CAST` SQL ne suffit pas — par exemple `CAST(... AS INTEGER)` Progress qui retourne une chaîne `'42'` au lieu d'un `int 42` selon les versions du driver ODBC.

### `Alter::FLOAT` / `Alter::STRING` / `Alter::BOOL`

Variantes de `Alter::INT` pour les autres types scalaires. Mêmes considérations.

## Ordre d'application

Les `Alters` sont appliqués **après** le fetch PDO et **avant** la sérialisation finale (JSON, hydratation Schema.org). L'ordre :

1. PDO fetch → array associatif brut.
2. Hydratation Schema.org si `ModelParam::SCHEMA` est défini → objet typé.
3. Pour chaque clé déclarée dans `ALTERS`, application de la transformation dans l'ordre du tableau.
4. `EnsureKeysTrait` complète les clés manquantes à `null`.
5. Retour au caller.

Si plusieurs `Alters` sont déclarés sur la même clé, **seul le dernier** est conservé (les clés du tableau écrasent).

## Pattern composé — exemple complet

Extrait simplifié d'une définition de modèle `Customer` de l.application hôte :

```php
ModelParam::ALTERS =>
[
    // URL canonique pour HATEOAS
    Prop::URL => [ Alter::URL , Paths::CUSTOMERS ] ,

    // Références cross-base : OpenEdge stocke un code, on remonte le document Arango
    Prop::AREA_SERVED         => [ Alter::NORMALIZE , [ Alter::GET , Models::THESAURUS_SHIPPING_AREA_SERVED      ] ] ,
    Prop::ASSIGNED_POS        => [ Alter::NORMALIZE , [ Alter::GET , Models::WAREHOUSES_PLAIN                    ] ] ,
    Prop::ASSIGNED_SELLER     => [ Alter::NORMALIZE , [ Alter::GET , Models::SUBSIDIARIES_SELLERS_PLAIN          ] ] ,
    Prop::CATEGORY            => [ Alter::NORMALIZE , [ Alter::GET , Models::THESAURUS_CUSTOMERS_CATEGORIES      ] ] ,
    Prop::CREDIT_STATUS       => [ Alter::NORMALIZE , [ Alter::GET , Models::THESAURUS_CUSTOMERS_CREDIT_STATUS   ] ] ,
    Prop::DELIVERY_METHOD     => [ Alter::NORMALIZE , [ Alter::GET , Models::THESAURUS_SHIPPING_DELIVERY_METHODS ] ] ,
    Prop::INDUSTRY            => [ Alter::NORMALIZE , [ Alter::GET , Models::THESAURUS_CUSTOMERS_INDUSTRIES      ] ] ,
    Prop::INVOICE_TYPE        => [ Alter::NORMALIZE , [ Alter::GET , Models::THESAURUS_CUSTOMERS_INVOICES_TYPES  ] ] ,
    Prop::VAT                 => [ Alter::NORMALIZE , [ Alter::GET , Models::VATS                                ] ] ,

    // Wrapping Schema.org
    Prop::WEBSITE             => [ Alter::CALL , fn( ?string $value ) =>
                                                  !empty( $value ) ? new WebSite([ 'url' => $value ]) : null ] ,
]
```

Une seule ligne JSON sortante peut contenir ainsi une dizaine de références dénormalisées, chacune lookup à un modèle cible. Avec le cache PSR-16 sur chaque modèle cible (`CacheableTrait`), le coût en runtime est négligeable après la première requête.

## Pièges

### 1. Boucle de lookup

Si le modèle cible d'un `Alter::GET` a lui-même un `Alters` qui pointe vers le modèle source, on entre en boucle infinie. En pratique : ne jamais déclarer un `Alter::GET` croisé entre deux modèles. Préférer un modèle "Plain" (`Models::*_PLAIN`) qui n'a pas d'`Alters` du tout, comme cible des lookups — pattern visible dans les applications consommatrices (`WAREHOUSES_PLAIN`, `SUBSIDIARIES_SELLERS_PLAIN`).

### 2. N+1 caché

Un `Alter::GET` déclenche un appel au modèle cible **par document fetch**. Sur une liste de 1000 lignes, c'est 1000 lookups. Le cache PSR-16 les évite après la première fois, mais sans cache la performance s'effondre. Toujours configurer `ModelParam::CACHE` sur le modèle cible quand on utilise `Alter::GET`.

### 3. Alter sur un champ absent du SELECT

Si on déclare `ALTERS[Prop::CATEGORY]` mais que `Prop::CATEGORY` n'est pas dans le `SELECT` du builder, l'alter est ignoré silencieusement. Pas d'erreur, pas de warning — juste rien. À vérifier en cas de souci.

### 4. Alter::CALL et la mutation

Le callable de `Alter::CALL` reçoit la valeur courante et **doit retourner la nouvelle valeur**. Modifier l'objet en place sans retourner ne fonctionne pas — la propriété du document n'est pas réassignée.

## Voir aussi

- [Modèle `Documents`](models.md) — comment le modèle consomme `ALTERS`.
- [Modèles `Harvest`](harvest.md) — souvent utilisés avec un `Alters` simplifié (juste les casts de type), pas avec des `Alter::GET` (le harvest fait la dénormalisation côté cible).
- [`CAST` et types SQL](sql/sql-functions-casts.md) — complément côté serveur.
- [`AlterBindVarsTrait`](models.md) — transformations symétriques côté bind variables (avant l'exécution).
