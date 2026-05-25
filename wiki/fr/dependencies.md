# Dépendances

`oihana/openedge` s'appuie sur un petit ensemble de packages `oihana/php-*` qui couvrent les briques de base (enums, exceptions, réflexion) et les couches transverses (models PDO, traits utilitaires). Il s'appuie également sur l'extension PHP `ext-odbc` et le driver SQL ODBC Progress fourni par Progress Software, qui doivent être installés au niveau du système.

> Toutes les dépendances `oihana/*` sont aujourd'hui versionnées en `dev-main`. La stabilisation viendra par cascade quand l'ensemble du graphe sera tagué. Voir la note finale.

## Pré-requis système

Avant même les dépendances Composer, `oihana/openedge` a besoin de deux choses au niveau du système :

| Pré-requis | Détail |
|---|---|
| **Extension PHP `ext-odbc`** | Active la fonction `odbc_*` et le driver PDO `PDO_ODBC` (`new PDO('odbc:...')`). Sur Debian/Ubuntu : `apt install php8.4-odbc`. |
| **Driver SQL ODBC Progress** | Fourni par Progress Software, payant ou inclus avec la licence ERP. Sur Linux 64 bits : binaire `pgoe27.so` typiquement déployé sous `/usr/dlc/odbc/lib/`. C'est le chemin qu'on indique dans `[odbc].driver` de la config. |
| **`unixODBC`** *(optionnel mais recommandé)* | Permet de tester la connexion via `isql` indépendamment de PHP. Pas requis par `oihana/openedge` directement. |

> **Contrainte de développement local :** le driver SQL ODBC Progress n'est livré que pour Linux x86_64 et Windows. Il **n'existe pas pour macOS**. En conséquence, le code haut-niveau d'`oihana/openedge` (factory PDO, modèle `Documents`, contrôleur) ne peut pas tourner en local sur Mac — seuls les helpers SQL purs (qui produisent des chaînes) sont testables sans connexion. Voir [tips.md](tips.md).

## Dépendances Composer

Le tableau ci-dessous liste les packages `oihana/*` consommés directement par `src/oihana/openedge/`.

| Package | Namespace racine | Rôle |
|---|---|---|
| `oihana/php-enums` | `oihana\enums\` | `Char`, `CharacterSet`, `ConstantsTrait` (introspection des constantes). Toutes les enums `OpenEdge`, `Clause`, `Predicate`, `Type`, `LockingHint`, … s'appuient sur ce trait. |
| `oihana/php-exceptions` | `oihana\exceptions\` | Famille d'exceptions standard du framework. |
| `oihana/php-reflect` | `oihana\reflect\` | `ConstantException`, helpers `useConstantsTrait`, validation des constantes utilisées dans les enums de fonctions. |
| `oihana/php-system` | `oihana\controllers\`, `oihana\models\`, `oihana\traits\`, `oihana\logging\` | Socle des contrôleurs HTTP, modèles transverses (`PDOModel`, `CacheableTrait`, `AlterBindVarsTrait`, `EnsureKeysTrait`), `LoggerTrait` PSR-3, `ToStringTrait`. |
| `oihana/php-core` | `oihana\core\` | Helpers fondamentaux : `oihana\core\strings\compile`, `oihana\core\strings\func`, `oihana\core\strings\key`, `oihana\core\strings\betweenDoubleQuotes`, `oihana\core\arrays\isAssociative`. Ce sont eux qui assemblent les fragments SQL. |
| `oihana/php-files` | `oihana\files\` | Lecture éventuelle de configuration sérialisée. |

> Le package `oihana/php-system` a un *autoload* large (`"oihana\\" => "src/oihana"`) qui couvre plusieurs namespaces racine. Dans `oihana/openedge`, on consomme principalement `oihana\models\pdo\PDOModel` (parent de `Documents`), `oihana\controllers\Controller` (parent de `DocumentsController`), et `oihana\traits\ToStringTrait`.

## Dépendances *cross-cutting* non `oihana/*`

`oihana/openedge` ne tire aucune dépendance lourde par lui-même, mais certains sous-modules s'intègrent à des frameworks tiers que le projet hôte doit fournir.

| Sous-module | Dépendance externe attendue | Notes |
|---|---|---|
| `openedge/controllers/` | `slim/slim` (Slim 4) + un conteneur PSR-11 | Le contrôleur consomme `Psr\Http\Message\ServerRequestInterface` et `Psr\Http\Message\ResponseInterface`. |
| `openedge/models/` | un conteneur PSR-11 (PHP-DI utilisé dans les exemples) | Le constructeur de `Documents` reçoit `DI\Container`. |

Aucun couplage à Symfony Console : à la différence d'`oihana/arango`, `oihana/openedge` ne livre pas de classe `Command` dédiée. Les commandes CLI qui consomment OpenEdge dans le projet hôte (typiquement les commandes `harvest:*`) héritent d'un parent fourni par une autre brique.

## Couplages côté application hôte

Le package ne dépend d'aucun namespace applicatif spécifique. Les seuls couplages avec l'application hôte sont contractuels :

- les `definitions/` de l'application hôte référencent `OpenEdgePDOBuilder` et `Documents` par leur FQCN ;
- les fonctions métier comme `customerAllColumns()`, `customerFrom()`, `customerWhere()` vivent côté hôte (`app\definitions\openedge\<entity>\`) et consomment les helpers du package — pas l'inverse.

## Snippet `composer require` minimal

Pour un usage **complet** d'`oihana/openedge` (helpers SQL + modèle `Documents` + contrôleur Slim) :

```bash
composer require \
    oihana/php-enums:dev-main      \
    oihana/php-exceptions:dev-main \
    oihana/php-reflect:dev-main    \
    oihana/php-system:dev-main     \
    oihana/php-core:dev-main       \
    oihana/php-files:dev-main
```

Et au niveau du système :

```bash
# Debian / Ubuntu — extension PHP ODBC
sudo apt install php8.4-odbc unixodbc

# Driver Progress (chemin à adapter au déploiement)
# Le binaire pgoe27.so est livré par Progress, pas par apt.
ls /usr/dlc/odbc/lib/pgoe27.so
```

Pour un usage **minimal** (couche SQL `db/helpers/` seule, sans modèle ni contrôleur) :

```bash
composer require \
    oihana/php-enums:dev-main      \
    oihana/php-reflect:dev-main    \
    oihana/php-core:dev-main
```

Pour le sous-module Slim :

```bash
composer require slim/slim:^4.0 php-di/php-di:^7.0
```

## Note sur les versions

L'ensemble des packages `oihana/*` est actuellement versionné en `dev-main`. Tant qu'une dépendance du graphe est en `dev-main`, le package qui la consomme reste lui aussi en `dev-main` — il serait incohérent de tager `1.0.0` un package qui pointe sur du `dev-main`. La stabilisation se fera par cascade quand l'ensemble du graphe sera prêt à recevoir un tag.

## Voir aussi

- [Introduction](introduction.md) — pourquoi cette bibliothèque existe.
- [Glossaire](glossary.md) — termes du framework.
- [Quickstart OpenEdge](quickstart.md) — premier exemple opérationnel.
