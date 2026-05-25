# Quickstart OpenEdge

Cette page enchaîne les trois étapes minimales pour interroger une base Progress OpenEdge depuis PHP avec `oihana/openedge` :

1. Construire un PDO ODBC propre, avec les bons attributs.
2. Exécuter une requête SQL en lecture, à la main, pour valider la connexion.
3. Passer à un modèle `Documents` haut-niveau pour `list` / `get` / `count`.

Les étapes suivantes (multi-base, intégration Slim, *harvest*) sont couvertes dans les pages dédiées.

## Étape 1 — Construire un PDO

La classe [`OpenEdgePDOBuilder`](../../src/oihana/openedge/db/OpenEdgePDOBuilder.php) est une factory *callable* qui assemble un DSN ODBC puis instancie un `PDO` avec les attributs adaptés à Progress.

```php
use oihana\openedge\db\OpenEdgePDOBuilder ;

$pdo = ( new OpenEdgePDOBuilder
([
    // Bloc commun à toutes les bases du serveur
    'scheme'   => 'odbc'                        ,
    'driver'   => '/usr/dlc/odbc/lib/pgoe27.so' ,
    'hostName' => 'erp.example.com'             ,
    'charSet'  => 106                           , // IANAAppCodePage 106 = UTF-8
    'queryTimeout' => 300                       ,
    'logonID'  => 'reader'                      ,
    'password' => 'secret'                      ,
    // Bloc spécifique à la base ciblée
    'database'   => 'erp_database' ,
    'portNumber' => 20931      ,
]) )() ;
```

Trois choses se passent dans cette ligne :

1. **Construction du DSN.** `new OpenEdgePDOBuilder([...])` instancie en interne un [`OpenEdgeDSN`](dsn.md), qui mappe les clés camelCase (`hostName`, `arraySize`, `charSet`, …) sur la syntaxe DSN attendue par le driver Progress (`HostName=…;ArraySize=…;IANAAppCodePage=…`).
2. **Construction du PDO.** L'appel `()` (la factory est *invokable*) appelle `__invoke()` qui fait `new PDO( $dsn , $logonID , $password )`.
3. **Réglage des attributs PDO.** Six attributs sont fixés sans avoir à y penser :

   | Attribut | Valeur | Raison |
   |---|---|---|
   | `PDO::ATTR_DEFAULT_FETCH_MODE` | `PDO::FETCH_ASSOC` | Tableau associatif au lieu d'index numérique + assoc en double. |
   | `PDO::ATTR_ERRMODE` | `PDO::ERRMODE_EXCEPTION` | Toute erreur SQL lève une `PDOException`. |
   | `PDO::ATTR_CURSOR` | `PDO::CURSOR_FWDONLY` | Curseur unidirectionnel, le mode performant pour le *streaming*. |
   | `PDO::ATTR_PERSISTENT` | `true` | Connexion persistante entre requêtes (important sur Progress, le coût de connexion est non-négligeable). |
   | `PDO::ATTR_EMULATE_PREPARES` | `false` | Vrai `PREPARE` côté serveur, pas d'émulation client qui casse les types. |
   | `PDO::ATTR_STRINGIFY_FETCHES` | `false` | Les colonnes numériques reviennent en `int` / `float`, pas en `string`. |

Si l'un de ces attributs ne convient pas, on peut le surcharger après coup : `$pdo->setAttribute(...)`.

## Étape 2 — Première requête en SQL brut

À ce stade, on a un `PDO` standard. Tout ce qu'on sait faire avec PDO marche :

```php
$stmt = $pdo->prepare( <<<SQL
    SELECT customer_id , customer_name , created_at
    FROM   PUB.customers
    WHERE  country_code = :country
    ORDER  BY customer_name
    SQL
) ;

$stmt->execute([ 'country' => 'FR' ]) ;

while ( $row = $stmt->fetch() )
{
    echo $row[ 'customer_id' ] . ' — ' . $row[ 'customer_name' ] . PHP_EOL ;
}
```

C'est la couche de plus bas niveau. Elle marche, mais elle laisse plusieurs choses à la charge du développeur : composer dynamiquement le `SELECT`, ajouter des conditions optionnelles dans le `WHERE`, gérer la pagination, projeter en `array<Customer>` plutôt qu'en `array<array>`, gérer un cache, etc. C'est précisément ce que la couche modèle automatise.

## Étape 3 — Modèle `Documents` haut-niveau

Le modèle [`Documents`](models.md) encapsule un `PDO`, un `OpenEdgeQueryBuilder`, un schéma de sortie optionnel et un cache éventuel. Au constructeur on lui passe un conteneur DI et un tableau d'options.

```php
use DI\Container                          ;
use oihana\models\enums\ModelParam        ;
use oihana\openedge\enums\OpenEdge as SQL ;
use oihana\openedge\models\Documents      ;

$container = /* PHP-DI ou autre PSR-11 */ ;

$customers = new Documents( $container ,
[
    ModelParam::PDO    => $pdo                     ,
    ModelParam::SCHEMA => Customer::class          , // hydratation optionnelle
    ModelParam::QUERY_BUILDER =>
    [
        SQL::COLUMNS  => [ 'customer_id' , 'customer_name' , 'created_at' ] ,
        SQL::FROM     => 'PUB.customers' ,
        SQL::WHERE    => [ /* voir sql/sql-clauses.md */ ] ,
        SQL::ORDER_BY => 'customer_name' ,
        SQL::SORTABLE => // whitelist autorisée en tri HTTP
        [
            'id'   => 'customer_id'   ,
            'name' => 'customer_name'  ,
            'created' => 'created_at'  ,
        ],
    ],
]) ;
```

À partir de là, les 13 traits CRUD du modèle sont utilisables :

```php
// Liste paginée + tri
$list = $customers->list
([
    SQL::LIMIT    => 50          ,
    SQL::OFFSET   => 0           ,
    SQL::ORDER_BY => 'customer_name' ,
]) ;

// Récupération par clé
$one = $customers->get([ 'customer_id' => 1274 ]) ;

// Comptage
$total = $customers->count() ;

// Vérification d'existence
$exists = $customers->exist([ 'customer_id' => 1274 ]) ;

// Stream paresseux pour les gros volumes (harvest)
foreach ( $customers->stream([ SQL::LIMIT => 10000 ]) as $row )
{
    handle( $row ) ;
}
```

Le modèle expose aussi `insert`, `update`, `upsert`, `replace`, `delete`, `deleteAll`, `truncate`, `last` — utilisables côté CLI ou script de migration. Voir [models.md](models.md) pour le catalogue complet.

## Étape 4 — Conteneur DI (en production)

En production, on n'instancie presque jamais `OpenEdgePDOBuilder` à la main : on l'enregistre comme service dans le conteneur, paramétré par la configuration TOML. Convention recommandée, **un fichier de définition par base** sous `definitions/odbc/`.

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
        // Bloc commun [odbc]
        ...$container->get( Definition::CONFIG )[ DBConfig::ODBC ] ?? [] ,
        // Bloc spécifique [databases.erp]
        ...$container->get( Definition::CONFIG )[ DBConfig::DATABASES ][ DBConfig::ERP ] ?? [] ,
    ])() ,
] ;
```

Côté consommateur, le modèle reçoit l'identifiant de service (pas l'instance PDO) :

```php
new Documents( $container ,
[
    ModelParam::PDO => Databases::ODBC_ERP , // identifiant DI résolu par le modèle
    // ...
]) ;
```

Le modèle résout lui-même `$container->get(Databases::ODBC_ERP)` au premier accès, ce qui permet d'avoir une connexion PDO **paresseuse** : tant qu'aucune méthode du modèle n'est appelée, aucune connexion ODBC n'est ouverte. Pratique quand un conteneur déclare des dizaines de modèles dont seule une fraction est utilisée par requête HTTP.

Voir [Connexion ODBC et multi-base](connection.md) pour le détail du pattern multi-base et la configuration TOML.

## Et après

- [Connexion ODBC et multi-base](connection.md) — configuration TOML, factory par base, *troubleshooting* connexion.
- [DSN ODBC en détail](dsn.md) — mapping config → DSN, attributs PDO, valeurs spéciales du `queryTimeout`.
- [Construire une requête SQL pas à pas](sql/sql-building-queries.md) — assemblage d'un SELECT complet avec helpers.
- [Modèle `Documents`](models.md) — catalogue complet des clés `OpenEdge::*` et des 13 traits CRUD.
- [Contrôleurs Slim](controllers.md) — exposer le modèle en route HTTP lecture seule.
