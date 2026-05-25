# *Locking hints*

Un *locking hint* est une indication explicite passée à OpenEdge sur la stratégie de verrouillage à appliquer pour une requête. C'est un mot-clé qui s'ajoute après une référence de table dans le `FROM` ou en tant que sous-clause `WITH(...)`. Il sert principalement à débloquer une lecture sur une base de production qui prend beaucoup de verrous.

L'enum [`LockingHint`](../../../src/oihana/openedge/db/enums/LockingHint.php) liste les cinq hints supportés par le framework.

> **Référence canonique.** [Progress SQL — Locking hints (READPAST)](https://docs.progress.com/bundle/openedge-sql-development-117/page/The-READPAST-locking-hint.html).

## Pourquoi c'est important

Sur un ERP Progress en production, les transactions ABL longues sont fréquentes (clôture comptable mensuelle, calcul de stocks, génération de factures). Chacune prend des verrous sur les tables qu'elle touche. Une requête SQL externe qui essaie de lire **la même table** au même moment va se mettre en attente — voire échouer sur timeout.

Pour un usage *reporting* / catalogue HTTP, on accepte de lire des données un peu *stale* (deux secondes en retard) plutôt que de bloquer la production. C'est le cas d'usage premier des *locking hints*.

## Les cinq hints

```php
use oihana\openedge\db\enums\LockingHint ;
```

| Constante | SQL | Effet |
|---|---|---|
| `LockingHint::NOLOCK` | `NOLOCK` | Lit sans prendre de verrou ; voit les données non-commitées (*dirty read*). |
| `LockingHint::READPAST` | `READPAST` | Saute les lignes verrouillées par une autre transaction au lieu d'attendre. |
| `LockingHint::NOWAIT` | `NOWAIT` | Lève immédiatement une erreur si la requête doit attendre un verrou. |
| `LockingHint::WAIT` | `WAIT` | Attente standard (comportement par défaut, explicite). |
| `LockingHint::WITH_NOLOCK` | `WITH (NOLOCK)` | Variante syntaxique table-level de `NOLOCK`, style SQL Server. |

## `NOLOCK` — le plus utilisé

Lit sans poser de verrou, et **voit les données non-commitées** par les autres transactions. C'est le hint à utiliser pour du *reporting* qui doit ne **jamais** bloquer la production.

### Côté SQL brut

```sql
SELECT *
FROM   PUB.orders c WITH (NOLOCK)
WHERE  c.created_at > '2026-01-01'
```

### Côté framework

```php
use oihana\openedge\db\enums\LockingHint ;
use oihana\openedge\enums\OpenEdge as SQL ;

SQL::LOCKING_HINT => LockingHint::WITH_NOLOCK ,
```

Le builder injecte le hint après le `FROM`.

### Compromis

| Avantage | Inconvénient |
|---|---|
| Aucun verrou pris → jamais d'attente, jamais de *deadlock* | Lecture *dirty* — peut voir une ligne qui sera *rollback* plus tard |
| Performance maximale pour les requêtes catalogue | Inadapté aux requêtes critiques (calcul de solde comptable, etc.) |
| Pas d'impact sur la production | Sur une table qui se met à jour beaucoup, les statistiques d'agrégat peuvent être faussées |

**Règle de pouce :** `NOLOCK` est acceptable pour 95 % des lectures HTTP catalogue. Pour les 5 % restants (totaux comptables, état de stock à un instant T), il faut un `READ COMMITTED` standard ou un *snapshot* applicatif.

## `READPAST` — alternative plus sûre

Saute les lignes verrouillées **sans les voir**. Plus sûr que `NOLOCK` (pas de *dirty read*), mais le résultat est partiel : les lignes en cours d'écriture par une autre transaction sont absentes du résultat.

```php
SQL::LOCKING_HINT => LockingHint::READPAST ,
```

Cas d'usage : tableau de bord qui agrège des compteurs et accepte qu'il en manque quelques-uns plutôt que de voir des valeurs intermédiaires erronées.

## `NOWAIT` — fail-fast

Lève immédiatement une erreur SQL si la requête doit attendre un verrou. Utile dans un contexte CLI où on préfère échouer rapidement et réessayer plus tard, plutôt que bloquer une heure.

```php
SQL::LOCKING_HINT => LockingHint::NOWAIT ,
```

## Choix d'un *locking hint*

| Scénario | Hint recommandé |
|---|---|
| Lecture catalogue HTTP, *reporting*, dashboards | `WITH (NOLOCK)` |
| Compteurs agrégés acceptant une donnée partielle | `READPAST` |
| Synchronisation périodique (harvest) qui ne doit pas bloquer | `WITH (NOLOCK)` |
| Calcul critique qui doit voir des données cohérentes | aucun hint (`READ COMMITTED` standard) |
| Vérification ponctuelle CLI qui doit échouer si verrou | `NOWAIT` |

## Erreurs fréquentes

### Mettre `NOLOCK` partout par habitude

Tentation forte quand on a été mordu une fois par un timeout. Mais sur les lectures qui agrègent (`SUM`, `COUNT`), `NOLOCK` peut donner des résultats faux à cause d'écritures intermédiaires non-encore commitées. **À utiliser uniquement quand on accepte explicitement la lecture *dirty*.**

### Combiner `NOLOCK` et un `INSERT/UPDATE`

Sur un `INSERT ... SELECT ...` ou un `UPDATE ... FROM ... WHERE ...`, un `NOLOCK` côté `SELECT` peut amener à muter sur la base de données *dirty*. Source de bugs subtils impossibles à reproduire en test. **À éviter** sur les chemins d'écriture.

> Le contrôleur HTTP du framework étant en lecture seule par doctrine ([introduction.md](../introduction.md#une-doctrine--openedge-en-lecture-seule-depuis-http)), ce piège ne survient pas via HTTP. Il peut survenir côté commande CLI.

## Voir aussi

- [Outer join Progress](outer-join.md) — autre particularité de la clause `WHERE`.
- [Timeouts de connexion](timeouts.md) — `connectTimeout` et `serverTimeout` du modèle, autre levier pour gérer l'attente.
- [Clauses SQL](../sql/sql-clauses.md) — `LookingHintTrait` du *query builder*.
- [Progress SQL — READPAST](https://docs.progress.com/bundle/openedge-sql-development-117/page/The-READPAST-locking-hint.html) — référence canonique.
