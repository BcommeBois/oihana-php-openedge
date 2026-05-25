# Tips et pièges

Recueil des règles d'or à respecter quand on travaille avec `oihana/openedge`. Toute violation découverte (avec son incident associé) doit venir grossir cette page plutôt que rester dans la mémoire d'une session.

## Table des matières

### Sécurité SQL

- [`bindExpression` est obligatoire pour toute valeur dynamique](#bindexpression-est-obligatoire-pour-toute-valeur-dynamique)
- [`SORTABLE` est obligatoire pour autoriser un tri public](#sortable-est-obligatoire-pour-autoriser-un-tri-public)
- [Pas de SELECT en lecture seule signifie pas d'écriture HTTP](#pas-de-select-en-lecture-seule-signifie-pas-décriture-http)

### Performance et verrous

- [`WITH (NOLOCK)` pour les lectures de reporting](#with-nolock-pour-les-lectures-de-reporting)
- [`arraySize` à régler par cas d'usage](#arraysize-à-régler-par-cas-dusage)
- [`Alter::GET` sans cache crée un N+1](#altergetsans-cache-crée-un-n1)

### Particularités Progress

- [Outer join `(+)` se met sur le bon côté](#outer-join--se-met-sur-le-bon-côté)
- [`charSet = 106` pour UTF-8, presque toujours](#charset--106-pour-utf-8-presque-toujours)
- [`(+) AND filtre` annule l'outer join](#-and-filtre-annule-louter-join)

### Test et debug

- [Pas de driver Progress sur macOS — tests d'intégration en preprod](#pas-de-driver-progress-sur-macos--tests-dintégration-en-preprod)
- [`SQL::DEBUG => true` pour voir la requête générée](#sqldebug--true-pour-voir-la-requête-générée)
- [Trou de tests sur les classes haut-niveau](#trou-de-tests-sur-les-classes-haut-niveau)

---

## Sécurité SQL

### `bindExpression` est obligatoire pour toute valeur dynamique

**Règle.** Toute valeur qui vient d'un *input* utilisateur ou d'un contexte non-contrôlé passe par `SQL::BIND`, jamais par `SQL::VALUE`, jamais par `literal()` direct.

```php
// ❌ Risque d'injection — la valeur est inlinée dans le SQL
SQL::WHERE => [
    SQL::COLUMN   => 'customer_name'      ,
    SQL::OPERATOR => '='               ,
    SQL::VALUE    => $_GET['search']   , // jamais ça
]

// ✅ La valeur est bindée à l'exécution, PDO l'échappe correctement
SQL::WHERE => [
    SQL::COLUMN   => 'customer_name' ,
    SQL::OPERATOR => '='          ,
    SQL::BIND     => 'search'     ,
]
$model->list([ SQL::BINDS => [ 'search' => $_GET['search'] ] ]) ;
```

### Pourquoi

`SQL::VALUE` passe par `valueExpression()` qui appelle `literal()` pour les chaînes. `literal()` échappe les quotes simples par doublement — mais ça ne protège **pas** contre une injection sur d'autres caractères (commentaires `--`, bytes nuls, codes de contrôle Progress). C'est explicitement conçu pour les **constantes côté serveur** (date du jour, code statut, etc.), pas pour des inputs utilisateur.

`SQL::BIND` produit un placeholder `:nom` que PDO échappe correctement à l'exécution, et active aussi le plan-cache côté Progress.

### Symptômes d'un oubli

- Un caractère `'` dans une valeur cherchée provoque une erreur SQL côté Progress (parsing cassé).
- Un audit code montre du `literal( $input )` ou du `SQL::VALUE => $input` avec une variable.

---

### `SORTABLE` est obligatoire pour autoriser un tri public

**Règle.** La clé `SQL::SORTABLE` est une *whitelist* — toute clé absente est ignorée silencieusement. Sans `SORTABLE`, aucun tri public ne fonctionne. **Ne jamais** construire un `ORDER BY` à partir d'un paramètre HTTP directement.

```php
// ❌ Injection garantie sur ?sort=
SQL::ORDER_BY => $_GET['sort']

// ✅ Whitelist explicite ; ?sort=name → ORDER BY customer_name
SQL::ORDER_BY => 'customer_name' ,
SQL::SORTABLE =>
[
    'id'   => 'customer_id'  ,
    'name' => 'customer_name' ,
]
```

Le contrôleur lit `?sort=name` (ou `?sort=-name` pour `DESC`), parse via `orderByExpression()`, vérifie la clé dans `SORTABLE` et utilise la valeur mappée. Les clés inconnues tombent silencieusement.

### Symptômes d'un oubli

- Un `?sort=field; DROP TABLE x` n'est pas filtré → catastrophe.
- Un client signale "j'ai ajouté `?sort=foo` mais rien ne change" → la clé n'est pas dans `SORTABLE` (comportement attendu, pas un bug).

---

### Pas de SELECT en lecture seule signifie pas d'écriture HTTP

**Règle.** Le contrôleur `DocumentsController` du framework expose uniquement `count`, `get`, `list`. Le routeur applique `RouteFlag::READ_ONLY`. **Ne pas contourner** ce flag sans une raison documentée.

Trois raisons pour cette doctrine (voir [introduction.md](introduction.md#une-doctrine--openedge-en-lecture-seule-depuis-http)) :

1. **Source de vérité ailleurs.** L'ERP a son propre client pour les mutations métier.
2. **Synchronisation, pas double écriture.** Les harvests CLI lisent OpenEdge et écrivent dans la cible documentaire — c'est cette cible qui sert d'écriture publique.
3. **Verrouillage Progress.** Les transactions ABL longues de la production prennent des verrous ; muter en parallèle expose au *deadlock*.

Le modèle (`Documents`) expose `insert`, `update`, `upsert`, `delete` — utilisables côté CLI ou script de migration. C'est intentionnel. **Le contrôleur HTTP ne doit pas les ré-exposer sans audit.**

---

## Performance et verrous

### `WITH (NOLOCK)` pour les lectures de reporting

**Règle.** Toute lecture en *reporting* / catalogue HTTP / dashboard met `LockingHint::WITH_NOLOCK`. La production Progress a des transactions ABL longues qui prennent des verrous — sans `NOLOCK`, l'API peut se bloquer pendant des minutes.

```php
SQL::LOCKING_HINT => LockingHint::WITH_NOLOCK ,
```

Compromis : `NOLOCK` voit les données non-commitées (*dirty read*). Acceptable pour 95 % des cas d'usage catalogue ; à éviter pour les requêtes de cohérence comptable. Voir [Locking hints](progress/locking-hints.md).

---

### `arraySize` à régler par cas d'usage

**Règle.** Le paramètre DSN `arraySize` contrôle combien de lignes le driver Progress ramène par aller-retour serveur. Défaut driver = 1 → catastrophique sur n'importe quel volume.

| Cas | `arraySize` |
|---|---|
| Lecture HTTP catalogue (limit ~50-200) | `200` |
| *Harvest* CLI massif | `1000` à `5000` |
| *Streaming* document par document | `100` à `200` |

Sur un *harvest* qui ramène un million de lignes avec `arraySize = 1`, c'est un million d'aller-retours réseau. Le passage à `5000` divise par autant le temps total.

---

### `Alter::GET` sans cache crée un N+1

**Règle.** Quand un modèle déclare `Alter::GET` pour dénormaliser, le modèle **cible** doit avoir `ModelParam::CACHE` configuré. Sans cache, une liste de 1000 lignes avec 5 références dénormalisées déclenche 5000 lookups.

```php
// Modèle cible : OBLIGATOIRE de configurer un cache
new Documents( $container ,
[
    ModelParam::PDO    => /* ... */ ,
    ModelParam::CACHE  => Caches::THESAURUS_CATEGORIES , // ← essentiel
    ModelParam::QUERY_BUILDER => /* ... */ ,
]) ;
```

Voir [Alters](alters.md#pièges).

---

## Particularités Progress

### Outer join `(+)` se met sur le bon côté

**Règle.** Le `(+)` se met sur la colonne **du côté qui peut être absent**, pas du côté qu'on veut "garder".

```sql
-- Garder les employés sans département : (+) sur d, pas sur e
WHERE e.department_id = d.department_id(+)
```

Mnémotechnique : "*plus quelque chose qui n'est pas vraiment là*". Voir [Outer join Progress](progress/outer-join.md).

---

### `charSet = 106` pour UTF-8, presque toujours

**Règle.** Le paramètre DSN `charSet` Progress utilise les *codepages* IANA, pas les noms PHP. `106` = UTF-8. À mettre par défaut.

Si on voit des caractères accentués cassés (`é` au lieu de `é`), c'est ce paramètre. Voir [DSN](dsn.md#charset).

---

### `(+) AND filtre` annule l'outer join

**Règle.** Avec la syntaxe `(+)`, la jointure et le filtre vivent dans le même `WHERE`. Un filtre `AND d.label = 'X'` ajouté **élimine** les lignes où `d.label IS NULL`, ce qui annule l'effet de l'outer join.

```sql
-- ❌ Les employés sans département sont éliminés ici
WHERE  e.department_id = d.department_id(+)
  AND  d.label = 'SALES'

-- ✅ Filtre compatible NULL
WHERE  e.department_id = d.department_id(+)
  AND  ( d.label = 'SALES' OR d.label IS NULL )

-- ✅✅ Mieux : LEFT JOIN avec ON
FROM   PUB.employees e
LEFT JOIN PUB.departments d ON e.department_id = d.department_id AND d.label = 'SALES'
```

Voir [Outer join Progress](progress/outer-join.md#pièges-du-).

---

## Test et debug

### Pas de driver Progress sur macOS — tests d'intégration en preprod

**Contrainte factuelle.** Le driver SQL ODBC Progress n'est livré que pour Linux x86_64 et Windows. Il n'existe pas pour macOS. En conséquence, sur un poste de développement Mac :

- Les helpers SQL purs (qui produisent des chaînes) sont testables unitairement — pas besoin de connexion.
- Les classes haut-niveau qui ouvrent une connexion (`OpenEdgePDOBuilder`, `Documents`, `DocumentsController`) **ne peuvent pas être testées en unit local sur Mac**.

Stratégie de test :

1. **Unitaire local Mac** — couvre les helpers SQL (`db/helpers/**`), les enums, les fonctions de production de chaînes. C'est ce qui est couvert par les 58 tests existants du package.
2. **Intégration en preprod Debian** — couvre le boot du modèle, l'exécution PDO, le contrôleur HTTP. À lancer avant un déploiement, pas en local.

> Conséquence : les couches haut-niveau (`OpenEdgeQueryBuilder` lui-même, `OpenEdgePDOBuilder`, `OpenEdgeDSN`, `Documents`, `DocumentsController`) n'ont **pas de tests unitaires dédiés** dans le package. Voir aussi [Trou de tests sur les classes haut-niveau](#trou-de-tests-sur-les-classes-haut-niveau).

### Workarounds possibles (non implémentés)

- Mocker `PDO` derrière `PDOTrait` : faisable mais lourd, et le mock ne reproduit pas les quirks Progress (cast bizarre, padding `CHAR`, etc.).
- Utiliser une base SQLite ou MySQL en backend de test : ne couvre pas les particularités Progress (outer join `(+)`, types `ARRAY`).
- Tourner un container Docker Progress de test : Progress ne distribue pas d'image Docker officielle.

Le compromis actuel est d'assumer la contrainte et de privilégier les tests d'intégration sur l'environnement preprod réel.

---

### `SQL::DEBUG => true` pour voir la requête générée

**Astuce.** Quand une requête échoue ou retourne un résultat inattendu, activer le mode debug pour voir le SQL généré et les bind variables :

```php
$customers->list([
    SQL::DEBUG => true ,
    SQL::SORT  => '-name' ,
]) ;
```

Sortie typique dans les logs :

```
query    : SELECT clients.customer_id AS "id", clients.customer_name AS "name" FROM PUB.customers clients ORDER BY customer_name DESC FETCH FIRST 50 ROWS ONLY
bindVars : {"country":"FR"}
```

Copie le SQL dans `isql` pour le tester en isolation et localiser l'erreur.

---

### Trou de tests sur les classes haut-niveau

**État factuel** (mai 2026). Le package a 58 tests qui couvrent bien :

- les helpers SQL (`db/helpers/**`),
- les fonctions de production de chaîne (`functions/**`),
- les prédicats (`predicates/**`),
- le trait `WhereTrait`.

Mais il **n'y a pas** de tests unitaires sur :

- `OpenEdgeQueryBuilder` (la classe centrale du *builder*),
- `OpenEdgePDOBuilder` (la factory PDO),
- `OpenEdgeDSN` (le builder DSN),
- `Documents` (le modèle haut-niveau),
- `DocumentsController` (le contrôleur HTTP).

Raison : ces classes nécessitent une connexion PDO ODBC Progress, donc un driver indisponible sur le poste de développement standard (Mac). Voir [Pas de driver Progress sur macOS](#pas-de-driver-progress-sur-macos--tests-dintégration-en-preprod).

**Implications pratiques :**

- Les modifications de ces classes nécessitent une validation en **preprod** réelle avant déploiement.
- Une régression introduite côté builder ou modèle ne sera **pas** détectée par la CI locale — uniquement par les tests d'intégration en preprod.
- Toute extension du framework (héritage, ajout de méthode) devrait s'accompagner d'un test d'intégration côté projet hôte.

À combler : pourrait être attaqué via Docker Progress de test (si une image officielle apparaît) ou via un harness PDO mocké minimal. Pas prioritaire tant que la stratégie preprod fonctionne.

---

## Conventions de code

### Importer `OpenEdge as SQL`

Le pattern dominant dans une application hôte typique importe l'enum centrale `OpenEdge` sous l'alias court `SQL` pour la lisibilité :

```php
use oihana\openedge\enums\OpenEdge as SQL ;

SQL::COLUMN
SQL::TABLE
SQL::WHERE
```

Plus lisible que `OpenEdge::WHERE` répété 30 fois dans une définition de modèle.

### Externaliser `COLUMNS` / `FROM` / `WHERE` en fonctions

Pour les modèles complexes, on externalise les blocs dans des fonctions PHP nommées sous `app\definitions\openedge\<entity>\` :

```php
SQL::COLUMNS  => customerAllColumns() ,
SQL::FROM     => customerFrom()       ,
SQL::WHERE    => customerWhere()      ,
```

Bénéfices : DI plus lisible, code SQL réutilisable entre modèles (API + harvest), tests unitaires possibles sur ces fonctions. Voir [models.md](models.md#le-pattern-dexternalisation-des-colonnes--from--where).

### `Schema::MODIFIED`, pas `updatedAt`

Convention oihana alignée sur Schema.org : le champ de date de modification s'appelle **`modified`** (pas `updatedAt`, pas `updated_at`). Idem `created` pas `createdAt`.

```php
Prop::MODIFIED ,  // ← convention
Prop::CREATED  ,
```

---

## Voir aussi

- [Introduction](introduction.md) — doctrine du framework.
- [Modèle `Documents`](models.md) — couche métier complète.
- [Construire une requête SQL pas à pas](sql/sql-building-queries.md) — assemblage d'un SELECT.
- [Outer join Progress](progress/outer-join.md) — détail du `(+)`.
- [Locking hints](progress/locking-hints.md) — quand utiliser `NOLOCK`.
