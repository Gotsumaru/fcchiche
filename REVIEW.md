# Audit conformité aux consignes de développement

Cette revue dresse la liste des écarts constatés par rapport aux règles définies (NASA Power of 10 adaptée, assertions systématiques, requêtes préparées, etc.). Chaque section indique les points à corriger avec références précises.

## 1. `src/Database/Sync.php`
- **Assertions insuffisantes** : plusieurs fonctions ne respectent pas l'exigence de deux assertions minimum. Par exemple `syncTerrains()` ne comporte qu'une seule vérification (`assert($club_id > 0)`), et `syncMembres()`, `syncEquipes()`, `syncEquipe()`, `syncEngagements()`, `getOrCreateCompetition()`, `getTerrainId()`, `parseDate()`, `parseDateTime()` ou `updateConfigValue()` n'en contiennent aucune ou une seule. 【F:src/Database/Sync.php†L154-L344】【F:src/Database/Sync.php†L712-L845】
- **Fonctions trop longues (>60 lignes)** : `syncClassements()` (~190 lignes) et `syncMatchs()` (~90 lignes) dépassent largement la limite fixée et devraient être découpées en sous-fonctions cohérentes. 【F:src/Database/Sync.php†L410-L704】
- **Requête non préparée** : `syncMembres()` exécute une suppression via `exec()` en interpolant directement l'identifiant (`DELETE FROM ... WHERE club_id = " . $club_id`), ce qui enfreint la règle « PDO + requêtes préparées systématiques » et complique la validation des entrées. 【F:src/Database/Sync.php†L211-L247】
- **Manque de validations additionnelles** : `syncAll()` et `syncClub()` n'introduisent pas de garde-fous supplémentaires (ex : contrôle que les sous-fonctions retournent bien un booléen/entier attendu, ou que les jeux de données API possèdent toutes les clés critiques avant insertion). 【F:src/Database/Sync.php†L34-L146】

## 2. `src/API/FFFApiClient.php`
- **Assertions manquantes** : `getClubInfo()`, `getEquipes()` et `getEngagements()` ne réalisent aucune vérification d'entrée/sortie alors que la règle impose au moins deux assertions par fonction (ex : contrôle de la structure de réponse avant retour). 【F:src/API/FFFApiClient.php†L38-L82】
- **Fonctions utilitaires à durcir** : `getAllClassements()` devrait valider la structure des éléments retournés (ex : assertions sur la présence de `competition`, `phase`, `poule`) avant de les insérer dans `$all_classements`. 【F:src/API/FFFApiClient.php†L128-L175】

## 3. `src/Models/MatchsModel.php`
- **Manque d'assertions** : `enrichMatchData()` et `enrichMatchsData()` ne comportent aucune assertion ; `getAllMatchs()` ne vérifie qu'une seule condition (`limit <= 1000`). Chaque méthode doit valider explicitement les entrées (structure `$match`) et les retours. 【F:src/Models/MatchsModel.php†L24-L91】
- **Absence de contrôles de sortie** : `getMatchById()` ne garantit pas que le tableau renvoyé est complet (pas d'assertions sur les clés essentielles). 【F:src/Models/MatchsModel.php†L92-L160】

## 4. `src/Utils/Logger.php`
- **Assertions insuffisantes dans les wrappers** : Les méthodes `info()`, `warning()` et `error()` délèguent directement à `log()` sans effectuer les deux assertions réglementaires (ex : message non vide, structure du contexte). 【F:src/Utils/Logger.php†L55-L84】
- **Nettoyage des sauvegardes** : `cleanOldBackups()` ne vérifie pas que `unlink()` réussit (absence d'assertion) et ne protège pas contre un nombre de fichiers massifs (absence de borne explicite sur la boucle). 【F:src/Utils/Logger.php†L85-L125】

## 5. Autres modèles (`src/Models/*.php`)
- **Règle des assertions** : La majorité des méthodes d'accès (`ClubModel`, `EquipesModel`, `ConfigModel`, etc.) ne contiennent aucune assertion explicite ni contrôle des retours PDO, contrevenant à l'exigence « ≥2 assertions par fonction » et à la vérification systématique des valeurs de retour. Une passe complète est nécessaire pour instrumenter ces vérifications. 【F:src/Models/ClubModel.php†L1-L160】【F:src/Models/ConfigModel.php†L1-L200】

## 6. Points transverses
- **Validation des retours** : De nombreuses exécutions de requêtes (`$stmt->execute()`) ne testent pas la valeur retournée avant de continuer, ce qui viole la règle « Chaque appelant vérifie la valeur de retour ». Exemple dans `syncEngagements()` ou `logSync()`. 【F:src/Database/Sync.php†L353-L873】
- **Gestion des erreurs API** : Les méthodes `FFFApiClient::makeRequest()` (et consorts) ne semblent pas remonter systématiquement les erreurs réseau/HTTP via exceptions ou assertions, ce qui va à l'encontre de l'approche défensive attendue.

### Conclusion
La base de code nécessite une refonte systématique pour aligner toutes les fonctions sur les contraintes (assertions, découpage, vérification des retours, requêtes préparées). Les priorités immédiates sont :
1. Ajouter des assertions d'entrées/sorties et des vérifications de résultats partout.
2. Segmenter les méthodes volumineuses (`syncClassements`, `syncMatchs`).
3. Corriger les accès PDO non préparés et centraliser la validation des données API.
