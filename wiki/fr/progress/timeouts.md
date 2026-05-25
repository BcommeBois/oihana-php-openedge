# Timeouts de connexion

Trois leviers permettent de contrôler combien de temps une requête OpenEdge peut prendre avant d'être abandonnée : un paramètre **DSN** au moment de la connexion, et deux méthodes côté **modèle** exposées par `OpenEdgeHelperTrait`.

| Levier | Niveau | Quand le régler |
|---|---|---|
| `queryTimeout` (DSN) | Toutes les requêtes de la connexion | Au boot, dans la config TOML. |
| `connectTimeout()` (modèle) | Toutes les requêtes du *handle* de connexion courant | À chaque ouverture de session OpenEdge, depuis le modèle. |
| `serverTimeout()` (modèle) | Toutes les requêtes du serveur pour cette connexion | À chaque ouverture, idem `connectTimeout()` mais portée serveur. |

## `queryTimeout` côté DSN

Le paramètre `queryTimeout` du DSN ([dsn.md](../dsn.md#querytimeout)) règle le timeout par défaut de **toutes les requêtes** qui passeront par ce PDO. Il vaut en secondes ; trois valeurs spéciales :

| Valeur | Comportement |
|---|---|
| `-1` | Pas de timeout. Le driver ignore aussi `SQL_ATTR_QUERY_TIMEOUT` côté ODBC. Pour les *harvests* longs en CLI. |
| `0` | Pas de timeout par défaut, mais le driver respecte un `SQL_ATTR_QUERY_TIMEOUT` réglé via un autre canal. |
| `x > 0` | Toutes les requêtes timeout après `x` secondes. |

Réglages recommandés :

```toml
[odbc]
# Exposition HTTP : 5 minutes max pour ne pas saturer l'API
queryTimeout = 300

# CLI / harvest : pas de timeout
# queryTimeout = -1
```

> Ce paramètre est figé à l'ouverture de la connexion PDO — pas modifiable au runtime. Pour changer le timeout en cours de session, utiliser `connectTimeout()` ou `serverTimeout()` ci-dessous.

## `connectTimeout()` et `serverTimeout()` côté modèle

`OpenEdgeHelperTrait` ([OpenEdgeHelperTrait.php](../../../src/oihana/openedge/models/traits/OpenEdgeHelperTrait.php)) — utilisé par le modèle [`Documents`](../models.md) — expose deux méthodes pour régler les timeouts **après** l'ouverture de la connexion.

### `connectTimeout(int $delay)`

Délai max d'exécution d'une requête, vue côté connexion (client).

```php
$customers->connectTimeout( 30 ) ; // 30 secondes
```

Côté SQL, c'est l'équivalent de :

```sql
SET PRO_CONNECT QUERY_TIMEOUT :delay
```

Le réglage vaut pour **toutes les requêtes ultérieures** sur la même connexion PDO. Persistance : tant que le PDO n'est pas refermé.

### `serverTimeout(int $delay)`

Délai max d'exécution d'une requête, **vue côté serveur** Progress. Sémantique proche de `connectTimeout` mais le calcul du dépassement se fait côté serveur, ce qui est plus précis quand le réseau est lent.

```php
$customers->serverTimeout( 60 ) ;
```

Côté SQL :

```sql
SET PRO_SERVER QUERY_TIMEOUT :delay
```

### `connectTimeout` vs `serverTimeout` — lequel choisir

| Cas | À privilégier |
|---|---|
| Limiter la durée totale vue par le client (PHP) | `connectTimeout` |
| Limiter la durée *réelle* d'exécution serveur (utile sur un réseau lent où l'aller-retour pèse) | `serverTimeout` |
| On veut les deux | Les deux ne sont pas exclusifs — combiner les deux est légitime. |

Dans 90 % des cas, le `queryTimeout` du DSN suffit. Les deux méthodes du trait sont utiles pour deux scénarios :

1. **CLI de harvest qui change de stratégie en cours d'exécution.** Au début du harvest on désactive le timeout (`-1`), puis on le rétablit à 60 s à la fin pour les requêtes de vérification.
2. **Session HTTP qui veut un timeout plus court** sur une route spécifique réputée rapide (200 ms attendus, max 5 s), sans toucher au timeout global.

## `updateStatistics(string $table)`

`OpenEdgeHelperTrait` expose aussi une méthode `updateStatistics()` qui demande au serveur Progress de **recalculer les statistiques** d'une table et de ses index. Ces statistiques sont consommées par l'optimiseur SQL Progress pour choisir le plan de requête.

```php
$customers->updateStatistics( 'PUB.customers' ) ;
```

Côté SQL :

```sql
UPDATE TABLE STATISTICS AND INDEX STATISTICS AND ALL COLUMN STATISTICS FOR PUB.customers
```

### Quand l'appeler

- **Après un harvest massif** (insertion de centaines de milliers de lignes) — les statistiques sont périmées et l'optimiseur peut choisir un plan catastrophique.
- **Après une création d'index** — Progress ne met pas à jour les statistiques automatiquement à la création.
- **Jamais en ligne sous traffic** — c'est une opération lourde qui pose des verrous le temps du calcul. Réserver à une fenêtre de maintenance.

> Cette méthode est documentée mais peu utilisée en pratique dans une application hôte typique. Elle est exposée par cohérence avec l'écosystème Progress, et utile dans les scripts de migration / *seed* manuels.

## Voir aussi

- [Connexion ODBC et multi-base](../connection.md) — configuration TOML `[odbc]` et `[databases.*]`.
- [DSN ODBC en détail](../dsn.md#querytimeout) — détail du paramètre `queryTimeout`.
- [Modèle `Documents`](../models.md) — comment le modèle consomme `OpenEdgeHelperTrait`.
- [Progress SQL — PRO_CONNECT and PRO_SERVER](https://docs.progress.com/bundle/openedge-sql-reference/page/SET-statement.html) — référence canonique.
