# Connexion ODBC et multi-base

Cette page décrit comment configurer une (ou plusieurs) connexion ODBC Progress depuis un fichier TOML, l'enregistrer comme service dans un conteneur PHP-DI, et la consommer depuis les modèles. C'est le pattern recommandé quand l'application hôte adresse plusieurs bases Progress hébergées sur le même serveur (par exemple une base ERP et une base analytique).

## Vocabulaire

Avant d'attaquer la config, deux mots à clarifier :

- **Bloc commun** — les paramètres ODBC qui ne changent pas entre les bases d'un même serveur : `driver`, `hostName`, `charSet`, `queryTimeout`, `logonID`, `password`. Stockés dans la section `[odbc]` de la configuration.
- **Bloc base** — les paramètres qui changent par base : `database` (nom de la base Progress) et `portNumber` (port d'écoute du *broker* SQL). Stockés dans `[databases.<nom>]`.

Le DSN final est la fusion des deux blocs. Cette séparation simplifie la rotation des credentials et la déclaration de nouvelles bases.

## Configuration TOML

Exemple typique d'une configuration multi-base — un `[odbc]` partagé, puis une section `[databases.<nom>]` par base Progress :

```toml
[odbc]
scheme       = "odbc"
driver       = "/usr/dlc/odbc/lib/pgoe27.so"
hostName     = "erp.example.com"
charSet      = 106         # IANAAppCodePage 106 = UTF-8
queryTimeout = 300         # secondes ; -1 pour pas de timeout
logonID      = "reader"
password     = "secret"

[databases.erp]
database   = "erp_database"
portNumber = 20931

[databases.analytics]
database   = "analytics_database"
portNumber = 20932
```

Les deux bases pointent vers le même `[odbc]` (même hôte, mêmes credentials, même driver) et ne se distinguent que par `database` et `portNumber`. Pour ajouter une troisième base, il suffit d'ajouter une section `[databases.<nom>]` supplémentaire.

> **Conseil sécurité.** `logonID` et `password` sont des secrets : ne pas commit le `config.toml` réel, garder un `config.example.toml` avec des placeholders, et résoudre les valeurs sensibles via un coffre (env vars, fichier `secrets/`, Vault, etc.) au boot.

## Service PDO par base

Convention recommandée : chaque base a son propre fichier de définition DI sous `definitions/odbc/<base>.php`. Tous ces fichiers ont la même structure :

```php
// definitions/odbc/erp.php
use app\enums\Databases ;
use app\enums\DBConfig  ;
use app\enums\Definition ;
use oihana\openedge\db\OpenEdgePDOBuilder ;
use Psr\Container\ContainerInterface ;

return
[
    Databases::ODBC_ERP => fn( ContainerInterface $container ) => new OpenEdgePDOBuilder
    ([
        ...$container->get( Definition::CONFIG )[ DBConfig::ODBC ] ?? [] ,
        ...$container->get( Definition::CONFIG )[ DBConfig::DATABASES ][ DBConfig::ERP ] ?? [] ,
    ])() ,
] ;
```

Trois choses à remarquer :

1. **Spread operator pour fusionner les blocs.** `[ ...$common , ...$base ]` donne un seul tableau plat consommé par `OpenEdgePDOBuilder`. Les clés du bloc base écrasent celles du bloc commun, ce qui permet de surcharger ponctuellement un paramètre (par exemple un `queryTimeout` spécifique pour la base de stats).
2. **`OpenEdgePDOBuilder` est *invokable*.** Le `()` final appelle `__invoke()` qui retourne l'instance PDO. Le service enregistré est donc directement le `PDO`, pas la factory.
3. **Le service est paresseux par défaut.** Tant qu'aucun modèle ne demande la base ERP, la closure n'est pas appelée et aucune connexion ODBC n'est ouverte. C'est important quand on déclare plusieurs bases mais qu'une requête HTTP donnée n'en touche qu'une.

### Pourquoi un fichier par base

On pourrait tout déclarer dans un seul fichier. La séparation par fichier facilite :

- la lisibilité (un fichier ≈ une connexion) ;
- la suppression d'une base devenue inutile (un `unlink`) ;
- la *code-review* d'un ajout de base (un commit qui ajoute un seul fichier) ;
- le découplage des conventions de nommage (`Databases::ODBC_<X>`) par base.

C'est purement organisationnel — le conteneur DI fusionne le tout au *boot*.

## Consommation depuis un modèle

Côté modèle, on ne reçoit pas l'instance PDO directement : on reçoit l'identifiant de service et le modèle se charge de la résolution paresseuse.

```php
use oihana\models\enums\ModelParam        ;
use oihana\openedge\enums\OpenEdge as SQL ;
use oihana\openedge\models\Documents      ;

new Documents( $container ,
[
    ModelParam::PDO    => Databases::ODBC_ERP , // ← identifiant DI, pas l'instance PDO
    ModelParam::SCHEMA => Customer::class     ,
    ModelParam::QUERY_BUILDER =>
    [
        SQL::FROM    => 'PUB.customers' ,
        SQL::COLUMNS => [ 'customer_id' , 'customer_name' ] ,
    ],
]) ;
```

Le modèle `Documents` accepte `ModelParam::PDO` sous deux formes :

| Forme | Comportement |
|---|---|
| `string` (identifiant DI) | Résolu par `$container->get($id)` au premier accès. **Forme recommandée**. |
| `PDO` (instance) | Utilisé tel quel. Utile en test unitaire avec un mock SQLite par exemple. |

## Multi-base dans une même requête

Un modèle est attaché à **une seule** base PDO. Si une requête HTTP a besoin de croiser deux bases, on instancie deux modèles (un par base) et on agrège côté contrôleur ou côté service métier.

```php
$customers = new Documents( $container , [
    ModelParam::PDO    => Databases::ODBC_ERP       ,
    ModelParam::SCHEMA => Customer::class           ,
    ModelParam::QUERY_BUILDER => [ /* ... */ ]      ,
]) ;

$reports = new Documents( $container , [
    ModelParam::PDO    => Databases::ODBC_ANALYTICS ,
    ModelParam::SCHEMA => Report::class             ,
    ModelParam::QUERY_BUILDER => [ /* ... */ ]      ,
]) ;
```

OpenEdge ne supporte pas les jointures cross-base SQL standards via le driver ODBC — c'est une limitation de la plateforme, pas du framework.

## Vérifier qu'une connexion fonctionne (sans PHP)

Avant de soupçonner le code PHP, il est utile de prouver que le DSN et les credentials sont bons via `unixODBC` :

```bash
# Le driver doit exister
ls /usr/dlc/odbc/lib/pgoe27.so

# Test connexion via isql (paquet unixodbc-bin)
echo "SELECT TOP 1 customer_id FROM PUB.customers" | \
isql -v "DRIVER=/usr/dlc/odbc/lib/pgoe27.so;HostName=erp.example.com;PortNumber=20931;Database=erp_database;IANAAppCodePage=106" \
     "reader" "secret"
```

Si `isql` se connecte mais que PHP ne se connecte pas, le problème est PHP (extension `ext-odbc` manquante, attribut PDO mal réglé). Si `isql` ne se connecte pas, c'est l'infrastructure (driver, réseau, credentials).

## Erreurs fréquentes

| Symptôme | Cause probable |
|---|---|
| `SQLSTATE[IM002] Data source name not found and no default driver specified` | Le chemin `/usr/dlc/odbc/lib/pgoe27.so` est faux ou le binaire n'est pas accessible par l'utilisateur PHP. |
| Caractères latin mal décodés (é → é) | `charSet` mal réglé. Forcer `charSet = 106` (UTF-8). |
| Timeout à 60 s sur une requête qui devrait prendre 90 s | `queryTimeout` trop bas. Augmenter ou passer à `-1`. |
| Toutes les requêtes prennent ~500 ms en plus | `PDO::ATTR_PERSISTENT` désactivé après coup par erreur, ou Apache redémarre en pré-fork. Vérifier l'attribut. |
| Colonnes `INTEGER` reviennent en `string` PHP | `PDO::ATTR_STRINGIFY_FETCHES` activé. Vérifier que la factory n'a pas été altérée. |

## Voir aussi

- [DSN ODBC en détail](dsn.md) — mapping config → DSN, valeurs spéciales.
- [Quickstart OpenEdge](quickstart.md) — premier exemple opérationnel.
- [Modèle `Documents`](models.md) — comment le modèle résout `ModelParam::PDO`.
- [Tips et pièges](tips.md) — contrainte de test local (driver indisponible sur Mac).
