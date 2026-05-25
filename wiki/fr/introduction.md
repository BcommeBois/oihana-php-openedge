# Introduction

## Qu'est-ce que Progress OpenEdge ?

[Progress OpenEdge](https://www.progress.com/openedge) est une plateforme applicative historique éditée par Progress Software, utilisée par de nombreux progiciels métier (ERP, gestion commerciale, comptabilité). Son cœur regroupe :

- un **moteur de base de données** transactionnel et propriétaire (extension `.db`, tables organisées par schémas type `PUB.<table>`) ;
- le langage **ABL** (*Advanced Business Language*, historiquement « Progress 4GL »), à la fois langage applicatif et langage de manipulation de données ;
- une **interface SQL** standard exposée par-dessus le moteur, accessible via le driver **SQL ODBC officiel** (`pgoe27.so` sur Linux, équivalent Windows).

Du point de vue d'une application PHP moderne, Progress OpenEdge se présente donc comme **une base de données SQL accessible en ODBC**, avec quelques particularités hérités du moteur Progress :

- syntaxe **`(+)` pour les outer joins** (style Oracle historique), non-standard SQL ;
- *locking hints* explicites (`NOLOCK`, `READ COMMITTED`, …) ;
- types ARRAY natifs ;
- besoin de spécifier le **charset codepage** au moment de la connexion (`IANAAppCodePage`) ;
- besoin de gérer explicitement la taille des tampons de récupération (`ArraySize`, `DefaultLongDataBuffLen`) pour les performances.

## Pourquoi cette bibliothèque

Quand on doit interfacer un PHP moderne avec un ERP OpenEdge — typiquement pour exposer un catalogue produits, faire du *reporting* ou synchroniser des données vers un système plus moderne — on bute presque toujours sur le même trio de difficultés :

1. **Aucun ORM PHP ne supporte OpenEdge sérieusement.** Doctrine, Eloquent et compagnie ciblent MySQL/PostgreSQL/SQLite/SQL Server. OpenEdge n'a pas de *grammar* dédiée dans ces ORM, et les particularités Progress (outer join `(+)`, *locking hints*) ne se modélisent pas en JPQL/DQL.
2. **Le SQL Progress se construit vite par `sprintf`.** Une fois sorti de l'ORM, on retombe sur du `'SELECT ' . implode(',', $cols) . ' FROM ' . $table . ' WHERE ...'` et toutes les fragilités que ça implique (injection, échappement des littéraux, conditions optionnelles, gestion des `NULL`, alias case-sensitive).
3. **La configuration ODBC est tatillonne.** Le DSN ODBC Progress demande une douzaine de paramètres précis (driver, host, port, charset codepage, taille des buffers, timeouts). Les attributs PDO/ODBC à régler côté client (`PDO::ATTR_EMULATE_PREPARES = false`, `PDO::ATTR_STRINGIFY_FETCHES = false`, etc.) sont rarement documentés ensemble.

`oihana/openedge` répond aux trois points :

- une **factory PDO** prête à l'emploi (`OpenEdgePDOBuilder`) qui produit une instance PDO avec les bons attributs ;
- un **DSN builder** typé (`OpenEdgeDSN`) qui mappe une configuration camelCase (`hostName`, `arraySize`, …) sur la syntaxe DSN attendue par le driver Progress (`HostName=…;ArraySize=…`) ;
- un **catalogue de helpers fonctionnels** pour construire les expressions SQL Progress (`cast`, `concat`, `coalesce`, `nvl`, `toChar`, …) sans `sprintf` ;
- un **modèle `Documents`** par composition de traits qui expose `list`/`get`/`count`/`exist`/`stream` et l'écriture (`insert`/`update`/`upsert`/`delete`) — l'écriture est dispo côté modèle mais n'est pas exposée par le contrôleur HTTP, voir plus bas ;
- un **contrôleur Slim en lecture seule** (`DocumentsController`) pour exposer rapidement des routes catalogue.

## La philosophie d'`oihana/openedge`

Le framework suit cinq principes qui se retrouvent dans tout le code et qu'on partage avec [`oihana/arango`](../arango/README.md) :

1. **Fonctions standalone composables, pas un ORM lourd.** La couche SQL est faite d'une centaine de petites fonctions PHP autoloadées (`expression`, `bindExpression`, `cast`, `coalesce`, `concat`, …) qu'on assemble par composition. Pas d'objet géant `QueryBuilder` à apprendre — on lit le SQL produit en regardant son code PHP.
2. **Zéro *magic string*.** Chaque clé d'option, chaque opérateur SQL, chaque type ou prédicat est exposé comme constante d'un enum typé (`OpenEdge::COLUMN`, `Predicate::BETWEEN`, `Type::VARCHAR`, `LockingHint::NOLOCK`). Les chaînes brutes (`'IS NULL'`, `'NOLOCK'`, `'VARCHAR'`) sont systématiquement remplacées par des constantes, ce qui rend les renommages refactorables et la recherche IDE fiable.
3. **Composition de traits fins.** Le modèle `Documents` n'est pas une classe géante : il agrège 13 traits CRUD à responsabilité unique (`DocumentsListTrait`, `DocumentsGetTrait`, `DocumentsInsertTrait`, …) plus quelques traits transverses (`AlterBindVarsTrait`, `CacheableTrait`, `EnsureKeysTrait`, `OpenEdgeHelperTrait`).
4. ***Container-friendly*.** Tout est conçu pour vivre derrière un conteneur PSR-11 (PHP-DI, Symfony DI, …). La factory PDO, le modèle `Documents` et le contrôleur Slim acceptent un `ContainerInterface` au constructeur et résolvent leurs dépendances par identifiant de service.
5. **Intégration Slim *out of the box*, en lecture seule.** `DocumentsController` produit en quelques lignes une route GET/COUNT/LIST avec pagination et tri (*whitelist* explicite via `OpenEdge::SORTABLE`). Volontairement pas de POST/PATCH/PUT/DELETE par défaut côté contrôleur : voir la doctrine ci-dessous.

## Une doctrine : OpenEdge en lecture seule depuis HTTP

Dans les applications consommatrices d'`oihana/openedge` — une API qui expose des données issues d'un ERP — **OpenEdge n'est jamais muté par une requête HTTP**. Le contrôleur Slim livré (`DocumentsController`) n'expose donc que `count`, `get` et `list` ; les routes utilisent `RouteFlag::READ_ONLY`.

Trois raisons à cette doctrine :

1. **Source de vérité ailleurs.** L'ERP a son propre client et son propre langage ABL pour les mutations métier ; l'API web ne fait pas concurrence à ce client.
2. **Synchronisation, pas double écriture.** Les commandes CLI `harvest:*` lisent OpenEdge et écrivent dans une base documentaire moderne (ArangoDB dans les applications consommatrices) ; c'est cette base documentaire qui sert d'écriture publique, jamais OpenEdge.
3. **Verrouillage Progress.** Un ERP OpenEdge en production a des transactions ABL longues qui prennent des verrous ; ouvrir l'écriture SQL en parallèle expose au *deadlock*.

Cette doctrine est portée par le contrôleur, **pas par le modèle** : le modèle `Documents` expose toujours `insert` / `update` / `upsert` / `replace` / `delete` / `truncate`, parce qu'un usage CLI ou un script de migration en a un besoin légitime. À l'extraction de la bibliothèque, si un autre projet a besoin de muter OpenEdge en HTTP, il pourra étendre `DocumentsController` pour ajouter ses verbes — la couche modèle est prête.

## Public et prérequis

Cette documentation suppose que le lecteur :

- maîtrise PHP 8.4 ou supérieur (l'utilisation systématique d'enums typés, de *readonly properties* et de la *first-class callable syntax* est centrale dans le code) ;
- comprend les bases de PDO et d'ODBC en PHP — installation d'un driver `unixODBC`, configuration `odbcinst.ini`, instanciation d'un `new PDO('odbc:...')` ;
- est à l'aise avec un conteneur PSR-11 (PHP-DI utilisé dans les exemples, mais le code n'y est pas couplé).

La connaissance de Slim n'est pas requise : l'intégration `DocumentsController` est un module indépendant. On peut consommer la factory PDO et les helpers SQL sans toucher au contrôleur Slim.

## Positionnement vis-à-vis des alternatives PHP

| Solution | Statut | SQL builder | Particularités Progress | Intégration DI / Slim |
|---|---|---|---|---|
| `PDO::ODBC` brut | natif PHP | non | non | non |
| Doctrine DBAL (driver ODBC générique) | maintenu | oui (mais SQL standard) | non — pas d'`outer join (+)`, pas de `LockingHint` Progress | partiel |
| `oihana/openedge` | actif | oui (composable) | oui (`NULLABLE_COLUMN`, `LockingHint`, `proArrayEscape`, …) | oui |

Le tableau résume le constat qui motive le projet : aucune alternative PHP ne couvre proprement les particularités Progress, et beaucoup d'équipes finissent par écrire leur propre couche.

## Aller plus loin

- [Dépendances](dependencies.md) — packages `oihana/php-*` requis.
- [Glossaire](glossary.md) — termes clés du framework.
- [Quickstart OpenEdge](quickstart.md) — premier exemple opérationnel.
- [Documentation officielle Progress OpenEdge SQL](https://docs.progress.com/bundle/openedge-sql-reference/) — référence canonique.
