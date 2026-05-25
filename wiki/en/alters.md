# Alters and denormalisation

The [`Documents`](models.md) model's `alters` system lets you apply **post-fetch transformations** to data brought back by PDO before the model returns it to the caller. It's the tool used to normalise types (`'1'` → `1`), to hydrate references (`cd_pays = 'FR'` → `{ id: 'FR', name: 'France' }`), to enrich with a canonical URL, or to apply business logic (`Alter::CALL`).

This complements [`CAST`](sql/sql-functions-casts.md) on the SQL side: `CAST` transforms **server-side**, `Alters` transforms **PHP-side** after reception.

> `Alters` live in the `oihana/php-system` ecosystem (the `Alter` enum, the `AlterBindVarsTrait`) and are usable identically by models against other databases (ArangoDB). This page documents the pattern on the OpenEdge side.

## Declaration

`Alters` are declared at the model constructor, under the `ModelParam::ALTERS` key:

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

The key is the **field name** in the output document (typically from a SQL column alias or a Schema.org property name). The value is a list `[ Alter::TYPE , ...args ]`.

## Transformations catalog

### `Alter::URL` — canonical URL

Builds a URL for the document from a prefix and the value of another field (typically the identifier).

```php
Prop::URL => [ Alter::URL , '/customers' ]
// → injects { url: '/customers/1274' } on each row
```

Prefix + `/{id}` produces the URL. Useful for APIs that emit HATEOAS or just to ease clicking from an admin UI.

### `Alter::GET` — lookup against another model

Replaces a scalar value with the full document fetched from another model. This is the most powerful use case — it allows **cross-database denormalisation**: read a reference on the OpenEdge side (a country code `'FR'`) and pull the full document from ArangoDB (or another database) holding the enriched thesaurus (`{ id: 'FR', name: 'France', flag: '🇫🇷' }`).

```php
Prop::CATEGORY => [ Alter::GET , Models::THESAURUS_CUSTOMERS_CATEGORIES ]
// → the value "12" of the CATEGORY field is replaced by the thesaurus document with id=12
```

The target model must have a `get` method accepting `[ id => value ]`. All framework `Documents` models (OpenEdge and ArangoDB) qualify.

> **Cross-database.** The pattern is especially useful to denormalise from OpenEdge (where references are short codes) to ArangoDB (where full i18n thesauri live). The source model (OpenEdge) doesn't do the SQL join — it delegates to a target model that can be anywhere.

### `Alter::NORMALIZE` — wrapper around another transformation

Applies the inner transformation **only if the value isn't empty** (different from `null` and empty string). Useful for optional references.

```php
Prop::CATEGORY => [ Alter::NORMALIZE , [ Alter::GET , Models::THESAURUS_CATEGORIES ] ]
// If CATEGORY is null/empty → stays null
// Otherwise → lookup and replace
```

Without `NORMALIZE`, an `Alter::GET` on an empty value would still try a `get(['id' => null])` which is useless and noisy in logs.

### `Alter::CALL` — callable invocation

Lets you apply an arbitrary transformation via a PHP function. The callable receives the current value and returns the new one.

```php
Prop::WEBSITE => [ Alter::CALL , fn( ?string $value ) => !empty( $value )
    ? new WebSite([ 'url' => $value ])
    : null
]
```

Typically used to wrap a scalar value in a Schema.org object (a `WebSite` around a URL, a `PostalAddress` around a code), or to apply complex business logic you don't want to do in SQL.

### `Alter::INT` — cast to integer

Forces the value to `int`. Shortcut for PHP's `(int)`.

```php
Prop::AREA_SERVED => Alter::INT
```

Useful when the SQL `CAST` isn't enough — e.g. a Progress `CAST(... AS INTEGER)` returning the string `'42'` instead of an `int 42` depending on the ODBC driver version.

### `Alter::FLOAT` / `Alter::STRING` / `Alter::BOOL`

Variants of `Alter::INT` for other scalar types. Same considerations.

## Application order

`Alters` are applied **after** the PDO fetch and **before** the final serialisation (JSON, Schema.org hydration). The order:

1. PDO fetch → raw associative array.
2. Schema.org hydration if `ModelParam::SCHEMA` is set → typed object.
3. For each key declared in `ALTERS`, apply the transformation in the array's order.
4. `EnsureKeysTrait` fills missing keys with `null`.
5. Return to the caller.

If multiple `Alters` are declared on the same key, **only the last** is kept (array keys overwrite).

## Composed pattern — full example

Simplified excerpt from a `Customer` model definition in host applications:

```php
ModelParam::ALTERS =>
[
    // Canonical URL for HATEOAS
    Prop::URL => [ Alter::URL , Paths::CUSTOMERS ] ,

    // Cross-database references: OpenEdge stores a code, we pull the Arango document
    Prop::AREA_SERVED         => [ Alter::NORMALIZE , [ Alter::GET , Models::THESAURUS_SHIPPING_AREA_SERVED      ] ] ,
    Prop::ASSIGNED_POS        => [ Alter::NORMALIZE , [ Alter::GET , Models::WAREHOUSES_PLAIN                    ] ] ,
    Prop::ASSIGNED_SELLER     => [ Alter::NORMALIZE , [ Alter::GET , Models::SUBSIDIARIES_SELLERS_PLAIN          ] ] ,
    Prop::CATEGORY            => [ Alter::NORMALIZE , [ Alter::GET , Models::THESAURUS_CUSTOMERS_CATEGORIES      ] ] ,
    Prop::CREDIT_STATUS       => [ Alter::NORMALIZE , [ Alter::GET , Models::THESAURUS_CUSTOMERS_CREDIT_STATUS   ] ] ,
    Prop::DELIVERY_METHOD     => [ Alter::NORMALIZE , [ Alter::GET , Models::THESAURUS_SHIPPING_DELIVERY_METHODS ] ] ,
    Prop::INDUSTRY            => [ Alter::NORMALIZE , [ Alter::GET , Models::THESAURUS_CUSTOMERS_INDUSTRIES      ] ] ,
    Prop::INVOICE_TYPE        => [ Alter::NORMALIZE , [ Alter::GET , Models::THESAURUS_CUSTOMERS_INVOICES_TYPES  ] ] ,
    Prop::VAT                 => [ Alter::NORMALIZE , [ Alter::GET , Models::VATS                                ] ] ,

    // Schema.org wrapping
    Prop::WEBSITE             => [ Alter::CALL , fn( ?string $value ) =>
                                                  !empty( $value ) ? new WebSite([ 'url' => $value ]) : null ] ,
]
```

A single outgoing JSON row can thus contain a dozen denormalised references, each looked up against a target model. With PSR-16 cache on each target model (`CacheableTrait`), runtime cost is negligible after the first request.

## Pitfalls

### 1. Lookup loop

If the target model of an `Alter::GET` itself has an `Alters` pointing back to the source model, you get into an infinite loop. In practice: never declare a crossed `Alter::GET` between two models. Prefer a "Plain" model (`Models::*_PLAIN`) that has no `Alters` at all as the lookup target — a pattern visible in host applications (`WAREHOUSES_PLAIN`, `SUBSIDIARIES_SELLERS_PLAIN`).

### 2. Hidden N+1

An `Alter::GET` triggers a target-model call **per fetched document**. On a 1000-row list, that's 1000 lookups. The PSR-16 cache avoids them after the first time, but without cache the performance collapses. Always configure `ModelParam::CACHE` on the target model when using `Alter::GET`.

### 3. Alter on a field not in the SELECT

If you declare `ALTERS[Prop::CATEGORY]` but `Prop::CATEGORY` is not in the builder's `SELECT`, the alter is silently ignored. No error, no warning — just nothing. To check when something seems wrong.

### 4. Alter::CALL and mutation

The `Alter::CALL` callable receives the current value and **must return the new value**. Mutating the object in place without returning doesn't work — the document property isn't reassigned.

## See also

- [`Documents` model](models.md) — how the model consumes `ALTERS`.
- [`Harvest` models](harvest.md) — often used with simplified `Alters` (only type casts), not with `Alter::GET` (the harvest does denormalisation on the target side).
- [`CAST` and SQL types](sql/sql-functions-casts.md) — server-side complement.
- [`AlterBindVarsTrait`](models.md) — symmetric transformations on bind variables (before execution).
