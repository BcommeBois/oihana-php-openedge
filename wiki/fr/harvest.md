# Modèles `Harvest`

Un **modèle `Harvest`** est un modèle [`Documents`](models.md) spécifique, dédié à la **lecture massive** d'une table OpenEdge pour synchronisation vers un système cible (cache, base documentaire, fichier d'export). C'est un pattern, pas une classe à part entière — on instancie un `Documents` normal mais avec une configuration optimisée pour le *harvest*.

Cette page documente le pattern côté **modèle source OpenEdge**. La commande CLI qui consomme ce modèle (`HarvestDocumentsCommand`) vit dans un autre package (`oihana/arango`) — le lien sera cross-package après extraction de cette bibliothèque.

## Différences entre un modèle "API" et un modèle "Harvest"

| Aspect | Modèle API (lecture HTTP) | Modèle Harvest (sync CLI) |
|---|---|---|
| Colonnes projetées | Catalogue complet pour l'affichage | Subset minimal — juste ce que la cible a besoin |
| `Alters` | Riches — `URL`, `GET` cross-base, `CALL`, wrapping Schema.org | Pauvres — uniquement les casts de type (`INT`, `FLOAT`) |
| `arraySize` DSN | Modéré (~200) | Élevé (~1000-5000) |
| `queryTimeout` | Court (~300 s) | Long ou `-1` (pas de timeout) |
| Cache | Activé (PSR-16) | Désactivé — on lit en *streaming*, pas de cache |
| Méthode appelée | `list()` ou `get()` | `stream()` |
| `LIMIT` / `OFFSET` | Pagination utilisateur | Souvent absents — on lit tout |
| *Locking hint* | `WITH (NOLOCK)` recommandé | `WITH (NOLOCK)` quasi-obligatoire |

## Définition d'un modèle Harvest

Convention recommandée : un fichier `harvest.php` par entité, à côté du fichier principal. Exemple `customers/harvest.php` :

```php
use DI\Container ;

use app\enums\Caches    ;
use app\enums\Databases ;
use app\enums\Models    ;
use app\enums\Prop      ;
use app\schema\Customer ;

use oihana\models\enums\Alter      ;
use oihana\models\enums\ModelParam ;
use oihana\openedge\enums\OpenEdge as SQL ;
use oihana\openedge\models\Documents      ;

use function app\definitions\openedge\customers\customerAllColumns ;
use function app\definitions\openedge\customers\customerFrom       ;

return
[
    Models::CUSTOMERS_HARVEST => fn( Container $container ) => new Documents
    (
        $container ,
        [
            ModelParam::PDO           => Databases::ODBC_ERP ,
            ModelParam::SCHEMA        => Customer::class     ,
            ModelParam::CACHE         => Caches::CUSTOMERS ,
            ModelParam::ALTERS =>
            [
                // Alters minimaux : juste les casts de type
                Prop::AREA_SERVED        => Alter::INT ,
                Prop::PRICE_SEGMENTATION => Alter::INT ,
            ],
            ModelParam::QUERY_BUILDER =>
            [
                // Même colonnes que le modèle "API" + un champ "type" Schema.org injecté
                SQL::COLUMNS => customerAllColumns( extraColumns:
                [
                    Prop::ADDITIONAL_TYPE => [ SQL::VALUE => Customer::getSchemaType() ] ,
                ]) ,
                SQL::FROM    => customerFrom() ,
                // Pas de WHERE → on lit tout
                // Pas d'ORDER BY → on streame
            ]
        ]
    ) ,
] ;
```

## Différences clés ligne par ligne

### Pas de `WHERE` ni d'`ORDER_BY`

Sur un *harvest*, on lit **tout**. Un `WHERE` filtrerait, un `ORDER_BY` ralentirait la requête (Progress doit trier en mémoire avant de streamer). On laisse l'ordre naturel des lignes Progress.

### Pas de `SORTABLE`

Le modèle harvest n'est pas exposé en HTTP — pas besoin de *whitelist* de tri public.

### `Alters` minimaux

Pas de `Alter::GET` (lookup cross-base) sur un harvest : la dénormalisation se fait **côté cible** (ArangoDB qui reçoit) avec les modèles arango appropriés. Garder le modèle source rapide et indépendant.

### `extraColumns` pour le type Schema.org

Le application hôte pousse en cible des documents Schema.org. On ajoute donc une colonne `@type` côté SELECT, calculée à partir de la classe Schema.org : `Customer::getSchemaType()` retourne `'Customer'`. C'est le seul `extraColumns` utile sur la plupart des harvests.

### `ModelParam::CACHE` est souvent réutilisé du modèle API

Curieux à première vue : un *harvest* en *streaming* n'utilise pas le cache. Mais le partager avec le modèle API a un effet de bord utile : à la fin du harvest, le cache du modèle API peut être invalidé (`$model->clearCache()`) pour forcer les prochaines lectures HTTP à passer par la base mise à jour.

## Utilisation côté CLI

La commande qui consomme un modèle harvest est typiquement une extension de `HarvestDocumentsCommand` (qui vit dans `oihana/arango`, voir la doc de ce package). Pseudo-code :

```php
class HarvestCustomersCommand extends HarvestDocumentsCommand
{
    protected function execute( InputInterface $input , OutputInterface $output ) : int
    {
        $source = $this->container->get( Models::CUSTOMERS_HARVEST ) ;
        $target = $this->container->get( Models::CUSTOMERS )                  ; // modèle Arango

        foreach ( $source->stream() as $customer )
        {
            $target->upsert( $customer->toArray() ) ;
        }

        return Command::SUCCESS ;
    }
}
```

Le flux : `stream()` côté OpenEdge → upsert ligne par ligne dans la cible. Sur de gros volumes, on bufferise et on commit par batch (1000 lignes par transaction Arango).

## Configurations recommandées

### Pour un *harvest* nocturne sans contrainte de durée

```toml
[odbc]
queryTimeout = -1   # pas de timeout
arraySize    = 5000 # fetch lourd côté driver
```

```php
ModelParam::QUERY_BUILDER =>
[
    SQL::LOCKING_HINT => LockingHint::WITH_NOLOCK , // ne jamais bloquer la prod
    // pas de ORDER_BY
    // pas de WHERE
]
```

### Pour un *harvest* incrémental (delta sync)

```php
ModelParam::QUERY_BUILDER =>
[
    SQL::LOCKING_HINT => LockingHint::WITH_NOLOCK ,
    SQL::WHERE        =>
    [
        SQL::COLUMN   => 'updated_at'    ,
        SQL::OPERATOR => '>='         ,
        SQL::BIND     => 'since'      ,
    ],
]
```

Côté commande, on passe la date de dernier sync :

```php
$source->stream([ SQL::BINDS => [ 'since' => $lastSync ] ]) ;
```

## Pièges

### 1. `arraySize` trop faible

Sur un harvest qui ramène un million de lignes, un `arraySize = 1` (défaut driver) fait un million d'aller-retours réseau. Passer à `1000` ou `5000` divise par autant le temps total.

### 2. `WITH (NOLOCK)` manquant

Sur un *harvest* qui tourne en parallèle de la production, un lock pris par une transaction ABL longue peut bloquer la lecture pendant des minutes — voire faire échouer le harvest. `WITH (NOLOCK)` est quasi-obligatoire.

### 3. `Alter::GET` cross-base sur un harvest

Ça marche, mais c'est lent : pour chaque ligne, on déclenche un lookup vers la cible. Sur un million de lignes, c'est rédhibitoire. **Toujours faire la dénormalisation côté cible**, pas côté source.

### 4. `ORDER_BY` sur un *harvest* en streaming

Progress doit construire l'ensemble complet en mémoire avant de pouvoir trier. Sur une table à dix millions de lignes, c'est plusieurs gigaoctets côté serveur. Toujours laisser l'ordre naturel.

### 5. Cache PSR-16 plein

Si le modèle harvest partage son cache avec le modèle API et qu'on harvest sans configurer une stratégie d'éviction (TTL ou LRU), le cache Memcached peut saturer après le harvest. Soit on désactive le cache côté harvest (`ModelParam::CACHEABLE => false`), soit on configure une éviction.

## Voir aussi

- [Modèle `Documents`](models.md) — base commune au modèle API et au modèle Harvest.
- [`Alters` et dénormalisation](alters.md) — pourquoi on les garde minimaux côté harvest.
- [DSN ODBC en détail](dsn.md#arraysize) — comment régler `arraySize` pour un harvest.
- [*Locking hints*](progress/locking-hints.md) — choix d'un hint pour un harvest.
- [Tips et pièges](tips.md) — règles d'or transverses.
