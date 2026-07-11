# Tests

![Langue](https://img.shields.io/badge/langue-Français-blue)

Le projet comporte **deux couches de tests complémentaires** :

| Couche | Outil | Serveur OpenEdge / driver ODBC ? | Rôle |
|---|---|---|---|
| **Tests unitaires** | PHPUnit | Non | Valident la logique pure : le constructeur SQL, les prédicats, les casts, les fonctions chaîne/numériques/dates, les expressions `CASE` et les helpers. Rapides, joués à chaque commit et en CI. |
| **Tests d'intégration live** | PHPUnit (`#[Group('integration')]`) | Oui (OpenEdge joignable via ODBC) | Valident la couche driver de bout en bout : factory PDO/ODBC, modèle `Documents`, contrôleurs. Opt-in ; ignorés au vert quand aucun driver/serveur n'est disponible. |

## Tests unitaires (PHPUnit)

La suite vit dans [`tests/`](../../tests) et se lance avec :

```shell
composer test                                       # = ./vendor/bin/phpunit
./vendor/bin/phpunit --filter ParseExtentTest       # un seul cas
```

Configuration : [phpunit.xml](../../phpunit.xml). Points clés :

- **Périmètre de couverture** : `./src` uniquement (l'élément `<source>`).
- **Mode strict** : `failOnWarning`, `failOnRisky`, `failOnSkipped`, `beStrictAboutOutputDuringTests`… Un test « risky » (aucune assertion, produit une sortie, etc.) **fait échouer** la suite volontairement — un test qui ne vérifie rien ne protège rien.
- **Groupe `integration` exclu** par défaut : les tests qui exigent un vrai serveur/driver sont tagués `#[Group('integration')]` et ne tournent pas avec `composer test`.

### Ce que l'on teste, et comment

| Palier | Cible | Technique |
|---|---|---|
| 1 | Constructeurs SQL purs (`db/helpers/**`, `db/enums/**`) | Entrée → chaîne SQL attendue. Aucun mock. |
| 2 | Traits de clause SQL (`db/traits/**`, ex. `WhereTrait`) | On assert le fragment produit pour un tableau de définition donné. |
| 3 | Modèles / contrôleurs | Nécessitent une connexion ODBC vivante → couverts par la suite **integration**, pas par la suite unitaire. |

> **Tests de caractérisation.** Pour couvrir du code existant, on écrit des tests qui décrivent ce que le code **fait réellement**, branche par branche (`if` / `else` / `match`). Si un comportement surprenant peut être exploité en aval, on le **fige dans un test** et on le signale — on ne change jamais une API publique sans validation explicite.

## Couverture de code

PHPUnit mesure quelles lignes de `./src` la suite exécute. Il faut **activer le mode couverture de Xdebug** (ou PCOV) ; sinon PHPUnit affiche `No tests executed!` et un avertissement `XDEBUG_MODE=coverage … has to be set`. Les scripts `composer` ci-dessous posent la variable d'environnement pour vous :

```shell
composer coverage       # suite + couverture : texte dans le terminal, Clover + HTML sous build/coverage/
composer coverage:md    # régénère build/coverage/COVERAGE.md (résumé Markdown, zones rouges en premier)
```

La sortie va dans `build/coverage/` — **gitignoré, jamais commité** : un instantané de chiffres devient obsolète dès le commit suivant et pollue les diffs. À régénérer à la demande. Le convertisseur Clover → Markdown vit dans [`tools/clover-to-markdown.php`](../../tools/clover-to-markdown.php) (portable entre les libs `oihana/php-*` — il auto-détecte le préfixe de chemin source commun).

### Évolution entre deux exécutions

Chaque génération horodate le rapport (`Generated at YYYY-MM-DD HH:MM:SS`) et ajoute un instantané à `build/coverage/history.json` (gitignoré lui aussi). À l'exécution suivante, le résumé se compare à la **précédente exécution enregistrée** et affiche un delta par métrique : `▲ +0.14 pts (+12 lines)` / `▼ -0.30 pts (-5 methods)` / `= ±0.00 pts (+0 lines)`. `history.json` est borné aux 50 dernières exécutions et reste **local uniquement** : pour une tendance partagée, publiez le rapport depuis un job CI plutôt que de le commiter.

### Lire le rapport

- **Lines** = la métrique de référence (% de lignes exécutées).
- Une barre vide = code **jamais testé** → bug potentiel non détecté.
- ⚠️ **100 % ≠ zéro bug.** Une ligne « traversée » sans assertion solide est *couverte* mais pas vraiment *vérifiée*. On vise des tests qui **assertent un résultat précis**, pas qui se contentent de passer dans le code.

### Le plafond des 100 % : pourquoi il est inatteignable en local

Une large part de la librairie dialogue avec Progress OpenEdge via **PDO + ODBC**, et le driver ODBC Progress **ne tourne pas sur un poste macOS de développement**. Cette couche est donc **structurellement non couvrable par la suite unitaire**, et le rapport de couverture se scinde nettement en deux :

| Zone | Couverture typique | Raison |
|---|---|---|
| `db/helpers/**` (constructeur SQL fonctionnel) | 80–100 % | Fonctions pures — aucun driver requis. C'est là qu'on pousse vers ~100 %. |
| `db/traits/**` (traits de clause) | partielle | Certains chemins supposent une connexion vivante. |
| `controllers/**`, `models/**`, `models/traits/documents/**` | 0 % en local | I/O PDO/ODBC — seule la suite **integration** les atteint. |

L'objectif n'est donc **pas** un 100 % global : c'est ~100 % sur la couche de construction pure, la couche driver étant couverte par la suite d'intégration partout où un vrai OpenEdge est disponible (CI avec driver, staging, environnement ERP).

## Tests d'intégration live (vrai OpenEdge)

La suite opt-in joue les tests tagués `#[Group('integration')]` contre une base Progress OpenEdge joignable via ODBC :

```shell
composer test:integration           # = ./vendor/bin/phpunit -c phpunit-integration.xml
```

Configuration : [phpunit-integration.xml](../../phpunit-integration.xml). Elle pose `failOnSkipped="false"` pour que la suite **reste verte sur une machine sans driver** — les tests d'intégration se contentent d'être ignorés. Écrivez ces tests de façon à détecter tôt l'absence de serveur joignable (ou du driver ODBC) et à appeler `markTestSkipped()`, plutôt qu'à échouer.

## Intégration continue

Deux workflows GitHub Actions :

- [`.github/workflows/ci.yml`](../../.github/workflows/ci.yml) — joue `composer validate --strict` et la suite unitaire sur PHP 8.4 pour chaque push sur `main` et chaque pull request. La couverture est désactivée (le driver n'est pas présent sur le runner).
- [`.github/workflows/docs.yml`](../../.github/workflows/docs.yml) — construit le site phpDocumentor (`composer doc`) et le déploie sur GitHub Pages au push sur `main`.

## Voir aussi

- [Astuces et pièges](tips.md) — dont la contrainte de test local sans driver ODBC.
- [Connexion ODBC et multi-base](connection.md) — la factory `OpenEdgePDOBuilder` par laquelle la suite d'intégration se connecterait.
- [Le DSN ODBC en détail](dsn.md) — `OpenEdgeDSN`, mapping config → DSN, dépannage de connexion.
