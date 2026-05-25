# Outer join Progress `(+)`

Progress OpenEdge accepte une **syntaxe historique** pour exprimer un *outer join* dans la clause `WHERE`, héritée d'Oracle pre-92 : on suffixe une colonne par `(+)`. Cette syntaxe est **non-standard SQL** mais reste largement utilisée dans les schémas ERP existants, et le framework la prend en charge via la constante `OpenEdge::NULLABLE_COLUMN`.

> **Référence canonique.** [Progress SQL — Outer join predicate](https://docs.progress.com/bundle/openedge-sql-reference/page/Outer-join-predicate.html).

## Le concept en deux phrases

Un *outer join* conserve les lignes du côté principal **même si** le côté joint n'a pas de correspondance. La syntaxe SQL standard l'exprime via `LEFT JOIN` (côté gauche conservé) ou `RIGHT JOIN` (côté droit). La syntaxe Progress historique l'exprime via un suffixe `(+)` dans la clause `WHERE`, sur la colonne du **côté qui peut manquer**.

## Comparaison des deux syntaxes

### Standard SQL — `LEFT JOIN`

```sql
SELECT e.name , d.label
FROM   PUB.employees e
LEFT JOIN PUB.departments d
       ON e.department_id = d.department_id
```

Lit : "tous les employés, et pour ceux qui ont un département associé, le libellé du département. Les employés sans département ressortent quand même, avec `d.label = NULL`."

### Progress historique — `(+)`

```sql
SELECT e.name , d.label
FROM   PUB.employees e , PUB.departments d
WHERE  e.department_id = d.department_id(+)
```

Sémantique strictement identique. Le `(+)` est posé sur la colonne du côté qui peut manquer (ici `d.department_id`).

## Côté framework — `OpenEdge::NULLABLE_COLUMN`

La constante `OpenEdge::NULLABLE_COLUMN` vaut littéralement `'(+)'`. Elle est appliquée par [`columnExpression()`](../helpers.md#columnexpression) quand la clé `OpenEdge::NULLABLE => true` est présente dans la définition.

```php
use oihana\openedge\enums\OpenEdge as SQL ;
use function oihana\openedge\db\helpers\expression ;

echo expression([
    SQL::COLUMN   => 'department_id'   ,
    SQL::TABLE    => 'd'         ,
    SQL::NULLABLE => true        ,
]) ;
// → d.department_id(+)
```

Dans une définition de condition `WHERE` :

```php
SQL::WHERE =>
[
    SQL::COLUMN   => 'department_id'                  ,
    SQL::TABLE    => 'e'                        ,
    SQL::OPERATOR => RelationalOperator::EQUAL  ,
    SQL::VALUE    => expression([
        SQL::COLUMN   => 'department_id' ,
        SQL::TABLE    => 'd'       ,
        SQL::NULLABLE => true      ,
    ]) ,
]
// → e.department_id = d.department_id(+)
```

## Quand utiliser quoi

| Cas | Recommandation |
|---|---|
| Nouveau code, base de toute évidence Progress | `LEFT JOIN ... ON ...` standard. Lisible et portable. |
| Maintenir un script ABL ou SQL legacy qui utilise `(+)` partout | Garder la syntaxe `(+)` pour la cohérence. `OpenEdge::NULLABLE_COLUMN` couvre le besoin. |
| Outer join *à droite* | `(+)` côté gauche, ou `RIGHT JOIN` standard. Préférer `LEFT JOIN` en inversant les tables — plus lisible. |
| Outer join *complet* (FULL) | Pas exprimable en `(+)`. Utiliser `FULL JOIN` standard. |

## Pièges du `(+)`

### 1. Position du `(+)` souvent inversée

Réflexe naturel : "je veux garder les employés sans département, donc je mets `(+)` sur les employés". **Faux.** Le `(+)` se met sur le côté qui **peut être absent**, donc sur `d.department_id`, pas sur `e.department_id`.

> Astuce mnémotechnique : `(+)` se lit "*plus quelque chose qui n'est pas vraiment là*", donc sur la colonne dont les valeurs peuvent manquer.

### 2. Un seul côté `(+)` à la fois

`a.x = b.y(+)` est un `LEFT JOIN` (côté `a` conservé). `a.x(+) = b.y` est un `RIGHT JOIN`. **`a.x(+) = b.y(+)` est invalide** — un `FULL JOIN` ne s'exprime pas comme ça.

### 3. Pas de `OR` dans une condition outer

```sql
WHERE  e.department_id = d.department_id(+)
   OR  e.other_id = d.other_id(+)         -- ERREUR
```

Quand on a besoin de plusieurs conditions de jointure, il faut passer à `LEFT JOIN ... ON ...` standard.

### 4. Le `WHERE` se mélange avec la jointure

Avec `(+)`, la jointure et le filtre vivent dans la même clause `WHERE`. C'est source d'erreur : un filtre `AND d.label = 'SALES'` ajouté après la condition de jointure **élimine** les lignes où `d.label IS NULL`, ce qui annule l'effet de l'outer join.

```sql
-- Inattendu : les employés sans département sont éliminés ici
WHERE  e.department_id = d.department_id(+)
  AND  d.label = 'SALES'

-- Correct : déplacer le filtre dans une condition compatible NULL
WHERE  e.department_id = d.department_id(+)
  AND  ( d.label = 'SALES' OR d.label IS NULL )
```

> Avec `LEFT JOIN ... ON ...`, on met `d.label = 'SALES'` **dans la clause `ON`** plutôt que dans `WHERE` — ce qui résout le problème de façon plus naturelle.

## Voir aussi

- [Construire une requête SQL pas à pas](../sql/sql-building-queries.md) — exemple de `FROM ... LEFT JOIN ... ON`.
- [Helpers](../helpers.md#columnexpression) — `columnExpression()` et la clé `NULLABLE`.
- [*Locking hints*](locking-hints.md) — autre particularité Progress dans la clause `WHERE`.
- [Progress SQL — Outer join predicate](https://docs.progress.com/bundle/openedge-sql-reference/page/Outer-join-predicate.html) — référence canonique.
