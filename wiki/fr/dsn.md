# DSN ODBC en détail

La classe [`OpenEdgeDSN`](../../src/oihana/openedge/db/OpenEdgeDSN.php) assemble une chaîne de connexion ODBC Progress à partir d'un tableau de configuration. Elle n'a aucune logique métier — elle traduit une configuration PHP idiomatique (clés camelCase) vers la syntaxe attendue par le driver Progress (clés PascalCase). Cette page documente ce mapping et les valeurs spéciales à connaître.

## Pourquoi cette classe existe

Le driver SQL ODBC Progress attend un DSN sous la forme :

```
odbc:Driver=/usr/dlc/odbc/lib/pgoe27.so;HostName=erp.example.com;PortNumber=20931;Database=gcow0501;IANAAppCodePage=106;ArraySize=200;QueryTimeout=300
```

Trois inconvénients à manipuler cette chaîne directement :

1. La casse compte côté ODBC (`HostName`, pas `hostname`), mais elle est anti-idiomatique côté PHP (où on préfère `hostName`).
2. Le nom des paramètres Progress (`IANAAppCodePage`, `DefaultLongDataBuffLen`) est peu mémorable.
3. Construire la chaîne par concaténation revient à faire du `sprintf` et perd la typage.

`OpenEdgeDSN` traduit les clés camelCase d'entrée vers le DSN Progress et produit une `string` valide en sortie via `__toString()`.

## Le mapping en un tableau

| Clé d'entrée (camelCase) | Clé DSN Progress | Type PHP | Description |
|---|---|---|---|
| `scheme` | *(préfixe `:` avant le DSN)* | `string` | Préfixe du PDO. **Toujours `'odbc'`** pour Progress. |
| `driver` | `Driver` | `string` | Chemin absolu du binaire driver (`pgoe27.so` sous Linux). |
| `hostName` | `HostName` | `string` | Nom DNS ou IP du serveur Progress. |
| `portNumber` | `PortNumber` | `string\|int` | Port d'écoute du *broker* SQL pour cette base. Différent par base. |
| `database` | `Database` | `string` | Nom de la base Progress (typiquement `gcow0501`, pas un chemin). |
| `charSet` | `IANAAppCodePage` | `int` | *Codepage* IANA pour la conversion de chaînes côté client. **Toujours `106` (UTF-8)** sauf cas particulier. |
| `arraySize` | `ArraySize` | `?int` | Nombre de lignes fetch par aller-retour serveur. Défaut driver = 1. Recommandé en lecture massive : `200` à `5000`. |
| `defaultLongDataBuffLen` | `DefaultLongDataBuffLen` | `?int` | Taille (en multiples de 1024) du tampon pour les colonnes longues (`CLOB`, `BLOB`, `LVARBINARY`). Défaut driver = 1024. |
| `queryTimeout` | `QueryTimeout` | `?int` | Timeout en secondes par requête. Trois valeurs spéciales : `-1`, `0`, `> 0`. Voir ci-dessous. |

Les clés d'entrée sont définies comme constantes `OpenEdgeDSN::CONFIG_*` (par exemple `OpenEdgeDSN::CONFIG_HOST_NAME = 'hostName'`) ; les clés DSN comme constantes `OpenEdgeDSN::*` (par exemple `OpenEdgeDSN::HOST_NAME = 'HostName'`). En pratique, on n'a presque jamais à les manipuler directement — `OpenEdgePDOBuilder` les passe à `OpenEdgeDSN` qui produit la chaîne finale.

## Valeurs spéciales

### `charSet`

Le driver Progress utilise les *codepages* IANA, pas les noms PHP. Valeur recommandée :

| `charSet` | Codepage | Quand |
|---|---|---|
| `106` | UTF-8 | **Par défaut, presque toujours**. |
| `4` | ISO-8859-1 (Latin-1) | Base très ancienne stockée en Latin-1 sans conversion serveur. |

Si on voit des caractères accentués cassés dans les réponses (par exemple `é` au lieu de `é`), c'est un problème de `charSet` : la base stocke probablement en UTF-8 mais le client demande du Latin-1, ou l'inverse.

### `queryTimeout`

Trois valeurs spéciales documentées par Progress :

| Valeur | Comportement |
|---|---|
| `-1` | Pas de timeout. Le driver ignore aussi l'attribut `SQL_ATTR_QUERY_TIMEOUT` côté ODBC. Recommandé pour les *harvests* longs en CLI. |
| `0` | Pas de timeout par défaut, mais le driver respecte un éventuel `SQL_ATTR_QUERY_TIMEOUT` réglé via un autre canal. |
| `x > 0` | Toutes les requêtes timeout après `x` secondes. Recommandé en exposition HTTP (par exemple `300` pour limiter les requêtes ad-hoc à 5 min). |

### `arraySize`

Paramètre de performance le plus sensible.

- Une valeur trop basse multiplie les aller-retours réseau. Sur une *list* qui retourne 10 000 lignes avec `arraySize = 1`, on fait 10 000 aller-retours.
- Une valeur trop haute alloue trop de RAM côté client : `arraySize × largeur_ligne × N_connexions` peut dépasser plusieurs gigaoctets.

Réglages recommandés :

| Type d'usage | `arraySize` |
|---|---|
| Lecture catalogue HTTP (limit 50–200) | `200` |
| *Harvest* CLI massif (millions de lignes) | `1000` à `5000` |
| Lecture *streaming* document par document | `100` à `200` (PHP-DI réutilise la connexion persistante) |

### `defaultLongDataBuffLen`

Pertinent uniquement si on projette des colonnes `CLOB`, `BLOB` ou `LVARBINARY` (par exemple un champ "description longue" ou un PDF stocké en base). Le paramètre est en **multiples de 1024** (donc `defaultLongDataBuffLen = 64` = 64 KB par champ).

Si une colonne `CLOB` revient tronquée, c'est ce paramètre qu'il faut augmenter — pas un attribut PDO côté PHP.

## Reconstitution du DSN

Pour déboguer un DSN, on peut simplement caster `OpenEdgeDSN` en string :

```php
use oihana\openedge\db\OpenEdgeDSN ;

$dsn = new OpenEdgeDSN
([
    'scheme'   => 'odbc'                        ,
    'driver'   => '/usr/dlc/odbc/lib/pgoe27.so' ,
    'hostName' => 'erp.example.com'             ,
    'portNumber' => 20931                       ,
    'database' => 'gcow0501'                    ,
    'charSet'  => 106                           ,
    'arraySize' => 200                          ,
    'queryTimeout' => 300                       ,
]) ;

echo (string) $dsn ;
// odbc:Driver=/usr/dlc/odbc/lib/pgoe27.so;HostName=erp.example.com;PortNumber=20931;Database=gcow0501;IANAAppCodePage=106;ArraySize=200;QueryTimeout=300
```

L'ordre des paramètres dans la chaîne est fixé par `__toString()` :

1. `Driver`
2. `HostName`
3. `PortNumber`
4. `Database`
5. `IANAAppCodePage`
6. `ArraySize`
7. `DefaultLongDataBuffLen`
8. `QueryTimeout`

Le `scheme` est ajouté en préfixe avec un `:` (`odbc:Driver=…`).

## Attributs PDO réglés par `OpenEdgePDOBuilder`

Une fois le DSN construit, [`OpenEdgePDOBuilder::__invoke()`](../../src/oihana/openedge/db/OpenEdgePDOBuilder.php) crée le PDO et règle six attributs :

```php
$pdo = new PDO( (string) $this->dsn , $this->logonID , $this->password ) ;

$pdo->setAttribute( PDO::ATTR_DEFAULT_FETCH_MODE , PDO::FETCH_ASSOC ) ;
$pdo->setAttribute( PDO::ATTR_ERRMODE            , PDO::ERRMODE_EXCEPTION ) ;
$pdo->setAttribute( PDO::ATTR_CURSOR             , PDO::CURSOR_FWDONLY ) ;
$pdo->setAttribute( PDO::ATTR_PERSISTENT         , true ) ;
$pdo->setAttribute( PDO::ATTR_EMULATE_PREPARES   , false ) ;
$pdo->setAttribute( PDO::ATTR_STRINGIFY_FETCHES  , false ) ;
```

Détail dans le [Quickstart](quickstart.md#étape-1--construire-un-pdo).

## Que faire en cas d'échec d'ouverture

Si `OpenEdgePDOBuilder::__invoke()` lève une `PDOException`, l'ordre de vérification recommandé :

1. **Driver présent ?** `ls -la <driver>`. Souvent un chemin incorrect ou des droits manquants pour l'utilisateur PHP (typiquement `www-data`).
2. **Réseau ?** `nc -vz <hostName> <portNumber>` pour vérifier que le *broker* SQL écoute.
3. **Credentials ?** Tester via `isql` (voir [connection.md](connection.md#vérifier-quune-connexion-fonctionne-sans-php)).
4. **Base existe ?** Le nom de base est sensible à la casse côté Progress.
5. **`ext-odbc` chargée ?** `php -m | grep -i odbc`. Doit afficher `odbc` et `PDO_ODBC`.

## Voir aussi

- [Quickstart OpenEdge](quickstart.md) — assemblage complet config → PDO.
- [Connexion ODBC et multi-base](connection.md) — pattern config TOML par base.
- [Tips et pièges](tips.md) — limitation du test local (driver Progress indisponible sur Mac).
- [Documentation officielle Progress — Connection parameters](https://docs.progress.com/bundle/openedge-data-management-sql-development/page/Connection-parameters-keywords.html) — référence canonique du DSN.
