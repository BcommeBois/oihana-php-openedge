# Glossaire

Cette page définit les termes clés rencontrés dans la documentation du framework. Elle ne remplace pas la [documentation officielle Progress OpenEdge](https://docs.progress.com/bundle/openedge-sql-reference/) — elle fixe un vocabulaire commun pour cette doc.

## Vocabulaire SQL / ODBC / Progress

### ABL

*Advanced Business Language*, historiquement appelé Progress 4GL. Langage applicatif et de manipulation de données natif de Progress OpenEdge. `oihana/openedge` **ne touche pas à l'ABL** : il passe par l'interface SQL standard que Progress expose en parallèle.

### `(+)` (outer join Progress)

Suffixe non-standard utilisé dans la clause `WHERE` de Progress OpenEdge pour exprimer un *outer join* à la manière d'Oracle historique. Exposé dans le framework via la constante `OpenEdge::NULLABLE_COLUMN = '(+)'`. Voir [Outer join Progress](progress/outer-join.md).

### `ArraySize`

Paramètre DSN ODBC Progress qui contrôle le nombre de lignes ramenées par le driver à chaque aller-retour serveur. Une valeur trop basse multiplie les appels réseau ; une valeur trop haute alourdit la RAM client. Côté framework, exposé via la clé camelCase `arraySize`.

### Bind variable

Variable injectée dans une requête SQL sous la forme `:nom` plutôt qu'inlinée dans la chaîne. Sa valeur est fournie séparément à PDO via `bindValue` / `bindParam`. Garantit l'absence d'injection SQL et permet la réutilisation du plan de requête côté serveur. Voir le helper [`bindExpression`](helpers.md#bindexpression).

### CAST

Conversion explicite d'un type SQL vers un autre, syntaxe `CAST(expression AS type[(length[,scale])])`. OpenEdge a son catalogue de types (`VARCHAR`, `INTEGER`, `DECIMAL`, `TIMESTAMP`, `BLOB`, …). Voir [`CAST` et types SQL](sql/sql-functions-casts.md).

### DSN

*Data Source Name*. Chaîne de connexion ODBC qui décrit où et comment se connecter à une base. Côté Progress : `Driver=...;HostName=...;PortNumber=...;Database=...;IANAAppCodePage=...`. Construit par la classe [`OpenEdgeDSN`](dsn.md).

### `IANAAppCodePage`

Paramètre DSN ODBC Progress qui indique le *codepage* IANA à utiliser pour les conversions de chaînes côté client. Valeur courante : `106` (UTF-8). Côté framework, exposé via la clé camelCase `charSet`.

### Locking hint

Indication explicite passée à OpenEdge sur la stratégie de verrouillage d'une requête. Exemples : `NOLOCK` (lecture sans verrou, *dirty read*), `READ COMMITTED`. Sur un ERP de *reporting*, `NOLOCK` est souvent indispensable pour ne pas geler la production. Voir [*Locking hints*](progress/locking-hints.md).

### ODBC

*Open Database Connectivity*. Standard d'accès à une base SQL via un driver tiers. PHP l'expose via l'extension `ext-odbc` et le driver PDO `PDO_ODBC`. Progress fournit son driver SQL ODBC propriétaire (`pgoe27.so` sous Linux).

### Outer join

Jointure qui conserve les lignes d'un des deux côtés même quand le côté opposé n'a pas de correspondance. Le SQL standard utilise `LEFT JOIN` / `RIGHT JOIN` / `FULL JOIN` ; Progress accepte également la syntaxe historique `(+)` dans la clause `WHERE` (voir ci-dessus).

### PDO

*PHP Data Objects*. Abstraction PHP standard pour accéder à une base via différents drivers (`mysql`, `pgsql`, `sqlite`, `odbc`, `sqlsrv`, …). `oihana/openedge` produit ses instances PDO via la factory `OpenEdgePDOBuilder`.

### Predicate

Fragment d'une clause `WHERE` qui s'évalue à `TRUE`, `FALSE` ou `UNKNOWN`. Les sept formes supportées par le framework :

- *basic* — comparaison binaire (`=`, `<>`, `<`, `>`, …)
- *between* — `expr BETWEEN x AND y`
- *in* — `expr IN (a, b, c)`
- *like* — `expr LIKE 'pattern%'`
- *exists* — `EXISTS ( subquery )`
- *null* — `IS NULL`, `IS NOT NULL`
- *quantified* — `expr op { ANY | ALL | SOME } ( subquery )`

Voir [Prédicats SQL](sql/sql-predicates.md).

### Query timeout

Durée maximale (en secondes) qu'une requête peut prendre côté serveur avant d'être annulée. Côté Progress, exposé via le paramètre DSN `QueryTimeout`. Trois valeurs spéciales : `-1` (pas de timeout, driver ignore le SQL_ATTR_QUERY_TIMEOUT), `0` (pas de timeout mais driver respecte le SQL_ATTR_QUERY_TIMEOUT), `x > 0` (timeout effectif).

## Vocabulaire `oihana/openedge`

### Alter / Alters

Fonction de transformation appliquée à une expression au moment où on la sérialise en SQL. Exemples : ajouter un `LOWER(...)`, un `CAST(...)`, une normalisation post-fetch via `Alter::GET` qui lookup un thésaurus. Exposé via les clés `OpenEdge::ALTER` (transformation unique) et `OpenEdge::ALTERS` (chaîne de transformations). Voir [`Alters` et dénormalisation](alters.md).

### `bindExpression` vs `valueExpression` vs `literal`

Trois helpers qui produisent un fragment SQL pour une valeur, à choisir selon la nature de la valeur :

- **`bindExpression(['bind' => 'userId'])`** → produit `:userId` (placeholder PDO). À utiliser pour toute valeur **issue d'un input utilisateur**.
- **`valueExpression(['value' => 'admin'])`** → produit l'expression inline (typiquement via `literal`). À utiliser pour des **constantes côté serveur**.
- **`literal('admin')`** → produit `'admin'` (chaîne échappée). Plus bas niveau, utilisé par `valueExpression`.

La règle absolue : **toute valeur dynamique passe par `bindExpression`**, jamais par `literal` ni `valueExpression`. Voir [tips.md](tips.md).

### Capability *(absent côté openedge)*

Notion présente dans `oihana/arango` (permission fine sur la valeur d'un paramètre URL). Le contrôleur OpenEdge actuel est en lecture seule sans système de *skins* ni de capabilities — la projection est gérée par le `ALTERS` du modèle. Mentionné pour éviter la confusion avec `oihana/arango`.

### Composition de traits

Pattern d'architecture central du framework : la classe `Documents` ne contient presque pas de code propre — elle agrège 13 traits CRUD à responsabilité unique (`DocumentsGetTrait`, `DocumentsListTrait`, `DocumentsInsertTrait`, …) plus 3 traits transverses et 1 trait Progress-specific. Idem pour `OpenEdgeQueryBuilder` qui agrège 9 traits clauses.

### Conteneur (DI)

Conteneur d'injection de dépendances conforme à PSR-11 (`Psr\Container\ContainerInterface`). Le framework accepte un conteneur au constructeur des modèles et contrôleurs, et résout ses dépendances (connexion PDO, schémas, cache, logger) par identifiant de service. PHP-DI est utilisé dans les exemples mais le code n'y est pas couplé.

### Definition

Fichier PHP qui retourne un tableau de définitions DI consommé par le conteneur. Dans les applications consommatrices, les *definitions* `oihana/openedge` vivent sous `definitions/...` (modèles HTTP) et `definitions/odbc/` (connexions PDO).

### Expression

Unité de base produite par les helpers du framework : une chaîne de caractères qui représente un fragment SQL valide. Une expression peut être un littéral (`'42'`), un *bind* (`:userId`), une colonne qualifiée (`clients.cd_client`), une fonction (`CAST(price AS INTEGER)`), un `CASE WHEN`, une concaténation (`a || b`), etc. La fonction `expression()` est le point d'entrée polymorphe qui dispatche selon la forme du tableau d'entrée.

### Facet

Élément optionnel d'une requête SELECT qui s'ajoute après le `SELECT … FROM … WHERE`. Couvert par l'enum `Facet` (`HAVING`, `GROUP_BY`, `ORDER_BY`, `LIMIT`, `OFFSET`, `DISTINCT`) et le trait `FacetsTrait` du *query builder*.

### Harvest

Synchronisation lecture massive d'une table OpenEdge vers un système cible (cache, base documentaire, fichier). Dans les applications consommatrices, les commandes `harvest:*` lisent OpenEdge avec un modèle dédié (typiquement `Models::<X>_HARVEST`) qui projette les colonnes utiles, puis écrivent en cible. Voir [Modèles `Harvest`](harvest.md).

### Modèle (`Documents`)

Classe haut-niveau qui représente une table OpenEdge et expose les opérations CRUD + listage + count + exist + stream. Configurée par un tableau de clés `OpenEdge::*` au constructeur. La projection HTTP est en lecture seule mais le modèle expose toutes les opérations d'écriture (utilisables par script CLI, par exemple).

### `OpenEdge::NULLABLE_COLUMN`

Constante valant `'(+)'`. Sert à suffixer une colonne dans la clause `WHERE` pour exprimer un *outer join* Progress historique. Voir [Outer join Progress](progress/outer-join.md).

### Sortable

*Whitelist* explicite des champs autorisés en tri HTTP, déclarée sous `OpenEdge::SORTABLE` dans la définition du *query builder*. Sans cette *whitelist*, l'API HTTP ne propose aucun tri — c'est une protection contre l'injection SQL par le paramètre `?sort=`.

### Validate context

Helper `validateContext()` qui introspecte un tableau de définition d'expression et lève une `ConstantException` si une clé inattendue est présente. Permet d'attraper les fautes de frappe et les noms de constantes obsolètes au moment de la construction de la requête, plutôt qu'au runtime SQL.

## Voir aussi

- [Introduction](introduction.md) — vue d'ensemble du framework.
- [Dépendances](dependencies.md) — packages requis.
- [Documentation officielle Progress OpenEdge SQL](https://docs.progress.com/bundle/openedge-sql-reference/) — référence canonique pour la syntaxe et les types.
