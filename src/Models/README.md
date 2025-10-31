# Documentation des Modèles - FC Chiche

## Vue d'ensemble

Tous les modèles sont situés dans `src/Models/` et suivent les conventions:
- **Nommage**: `NomModel.php` (PascalCase)
- **Méthodes**: Utilisent `snake_case` pour les paramètres
- **Sécurité**: Requêtes préparées PDO obligatoires
- **Assertions**: Validation systématique des paramètres
- **Retour**: `array|null` pour un élément, `array` pour une liste

---

## Installation et Chargement

### Méthode 1 : Chargement automatique (recommandé)

```php
require_once 'config/database.php';
require_once 'src/Models/ModelsLoader.php';

$pdo = Database::getInstance();
$models = ModelsLoader::loadAll($pdo);

// Utilisation
$club = $models['club']->getClub();
```

### Méthode 2 : Chargement manuel

```php
require_once 'config/database.php';
require_once 'src/Models/ClubModel.php';

$pdo = Database::getInstance();
$clubModel = new ClubModel($pdo);
$club = $clubModel->getClub();
```

---

## 1. ClubModel

**Table**: `pprod_club`  
**Description**: Gestion des informations du club FC Chiche

### Méthodes principales

```php
// Toutes les infos du club
$club = $models['club']->getClub();

// Infos essentielles uniquement
$essentials = $models['club']->getClubEssentials();

// ID interne du club
$clubId = $models['club']->getClubId();

// Logo du club
$logo = $models['club']->getClubLogo();

// Vérifier existence
$exists = $models['club']->exists();
```

### Exemple de données retournées

```php
[
    'id' => 1,
    'cl_no' => 5403,
    'name' => 'FC Chiche',
    'short_name' => 'FC CHICHE',
    'logo_url' => 'https://...',
    'address1' => '...',
    'postal_code' => '85200',
    'latitude' => 46.1234,
    'longitude' => -1.5678
]
```

---

## 2. TerrainsModel

**Table**: `pprod_terrains`  
**Description**: Gestion des terrains du club

### Méthodes principales

```php
// Tous les terrains
$terrains = $models['terrains']->getAllTerrains();

// Un terrain par ID
$terrain = $models['terrains']->getTerrainById(1);

// Terrain par numéro API
$terrain = $models['terrains']->getTerrainByTeNo(12345);

// Terrains avec coordonnées GPS
$terrainsGPS = $models['terrains']->getTerrainsWithGPS();

// Nombre de terrains
$count = $models['terrains']->countTerrains();
```

---

## 3. MembresModel

**Table**: `pprod_membres`  
**Description**: Gestion des membres du bureau

### Méthodes principales

```php
// Tous les membres
$membres = $models['membres']->getAllMembres();

// Un membre par ID
$membre = $models['membres']->getMembreById(1);

// Membres par titre/fonction
$presidents = $models['membres']->getMembresByTitre('Président');

// Recherche
$results = $models['membres']->searchMembres('Martin');

// Nombre de membres
$count = $models['membres']->countMembres();
```

---

## 4. CompetitionsModel

**Table**: `pprod_competitions`  
**Description**: Gestion des compétitions

### Méthodes principales

```php
// Toutes les compétitions (saison actuelle)
$competitions = $models['competitions']->getAllCompetitions();

// Compétitions d'une saison spécifique
$comps2024 = $models['competitions']->getAllCompetitions(2024);

// Une compétition par ID
$comp = $models['competitions']->getCompetitionById(123);

// Compétition par numéro API
$comp = $models['competitions']->getCompetitionByCpNo(456);

// Par type
$championnats = $models['competitions']->getChampionnats();
$coupes = $models['competitions']->getCoupes();

// Nombre de compétitions
$count = $models['competitions']->countCompetitions();
```

---

## 5. EquipesModel

**Table**: `pprod_equipes`  
**Description**: Gestion des équipes du club

### Méthodes principales

```php
// Toutes les équipes (diffusables uniquement par défaut)
$equipes = $models['equipes']->getAllEquipes();

// Toutes les équipes (incluant non-diffusables)
$toutesEquipes = $models['equipes']->getAllEquipes(null, false);

// Une équipe par ID
$equipe = $models['equipes']->getEquipeById(1);

// Équipes par catégorie
$seniorsEquipes = $models['equipes']->getEquipesByCategory('SEM');

// Par short_name
$equipe = $models['equipes']->getEquipeByShortName('SEM 1');

// Toutes les catégories
$categories = $models['equipes']->getCategories();

// Équipes seniors
$seniors = $models['equipes']->getEquipesSeniors();

// Équipes jeunes
$jeunes = $models['equipes']->getEquipesJeunes();

// Nombre d'équipes
$count = $models['equipes']->countEquipes();
```

---

## 6. MatchsModel ⭐ MODÈLE LE PLUS UTILISÉ

**Table**: `pprod_matchs`  
**Description**: Gestion calendrier et résultats avec jointures automatiques

### Méthodes principales

```php
// CALENDRIER (matchs à venir)
$prochains = $models['matchs']->getUpcomingMatchs(10);
$calendrier = $models['matchs']->getAllMatchs(false, 20);

// RÉSULTATS (matchs terminés)
$derniers = $models['matchs']->getLastResults(10);
$resultats = $models['matchs']->getAllMatchs(true, 20);

// Par ID ou numéro API
$match = $models['matchs']->getMatchById(1);
$match = $models['matchs']->getMatchByMaNo(123456);

// Par compétition
$matchsCoupe = $models['matchs']->getMatchsByCompetition(123, false); // Calendrier
$resultatsCoupe = $models['matchs']->getMatchsByCompetition(123, true); // Résultats
$tousMatchs = $models['matchs']->getMatchsByCompetition(123); // Tous

// Par équipe/catégorie
$matchsSEM = $models['matchs']->getMatchsByTeamCategory('SEM', false);
$resultatsSEM = $models['matchs']->getMatchsByTeamCategory('SEM', true);

// Domicile / Extérieur
$matchsDomicile = $models['matchs']->getHomeMatchs(false, 10);
$matchsExterieur = $models['matchs']->getAwayMatchs(false, 10);
$resultatsDomicile = $models['matchs']->getHomeMatchs(true, 10);
$resultatsExterieur = $models['matchs']->getAwayMatchs(true, 10);

// Par journée
$matchsJ10 = $models['matchs']->getMatchsByJournee(10, 123);

// Par période de dates
$matchsNovembre = $models['matchs']->getMatchsByDateRange('2025-11-01', '2025-11-30', false);

// Widgets rapides
$prochainDomicile = $models['matchs']->getNextHomeMatch();
$dernierDomicile = $models['matchs']->getLastHomeResult();

// Nombre de matchs
$nbCalendrier = $models['matchs']->countMatchs(false);
$nbResultats = $models['matchs']->countMatchs(true);
```

### Données retournées (avec jointures)

Toutes les méthodes retournent des matchs enrichis avec:
- Infos compétition (`competition_name`, `competition_type`, `competition_level`)
- Infos terrain (`terrain_name`, `terrain_address`, `terrain_city`)
- **Logo club adverse** (`opponent_logo`, `opponent_name`, `opponent_short_name`)

```php
[
    'id' => 1,
    'date' => '2025-11-15',
    'time' => '15H00',
    'home_team_name' => 'FC CHICHE SEM 1',
    'away_team_name' => 'AS EXEMPLE',
    'home_score' => 2,
    'away_score' => 1,
    'is_result' => 1,
    'competition_name' => 'Championnat District',
    'competition_type' => 'CH',
    'terrain_name' => 'Stade Municipal',
    'opponent_name' => 'AS EXEMPLE',
    'opponent_logo' => 'https://...' // 🆕 Logo automatique
]
```

---

## 7. ClubsCacheModel 🆕

**Table**: `pprod_clubs_cache`  
**Description**: Cache des clubs adverses (logos et infos)

### Méthodes principales

```php
// Tous les clubs en cache
$clubs = $models['clubs_cache']->getAllClubs();

// Un club par cl_no
$club = $models['clubs_cache']->getClubByClNo(12345);

// Logo d'un club adverse
$logo = $models['clubs_cache']->getClubLogo(12345);

// Recherche
$results = $models['clubs_cache']->searchClubs('Saint');

// Vérifier existence
$exists = $models['clubs_cache']->exists(12345);

// Clubs récents
$recents = $models['clubs_cache']->getRecentClubs(10);
```

---

## 8. ClassementsModel 🆕

**Table**: `pprod_classements`  
**Description**: Historisation des classements (championnats uniquement)

### Méthodes principales

```php
// Classement actuel (dernière journée)
$classement = $models['classements']->getCurrentClassement(123);

// Classement d'une journée spécifique
$classementJ10 = $models['classements']->getClassementByJournee(123, 10);

// Position FC Chiche
$position = $models['classements']->getClubPosition(123);

// Évolution de position
$evolution = $models['classements']->getPositionHistory(123);

// Statistiques du club
$stats = $models['classements']->getClubStats(123);

// Toutes les compétitions avec classement
$comps = $models['classements']->getCompetitionsWithClassement();

// Nombre de journées
$nbJournees = $models['classements']->getJourneesCount(123);

// Comparer deux journées
$evolution = $models['classements']->compareJournees(123, 5, 10);
```

### Exemple de données

```php
// Position actuelle
[
    'ranking' => 3,
    'points' => 18,
    'games_played' => 10,
    'wins' => 6,
    'draws' => 0,
    'losses' => 4,
    'goals_for' => 25,
    'goals_against' => 18,
    'goal_difference' => 7,
    'club_name' => 'FC CHICHE',
    'club_logo' => 'https://...'
]
```

---

## 9. EngagementsModel

**Table**: `pprod_engagements`  
**Description**: Pivot équipes-compétitions

### Méthodes principales

```php
// Tous les engagements
$engagements = $models['engagements']->getAllEngagements();

// Engagements d'une équipe
$compsEquipe = $models['engagements']->getEngagementsByEquipe(1);

// Équipes dans une compétition
$equipesComp = $models['engagements']->getEquipesByCompetition(123);

// Engagement spécifique
$engagement = $models['engagements']->getEngagementByEquipeAndCompetition(1, 123);

// Par catégorie
$engagementsSEM = $models['engagements']->getEngagementsByCategory('SEM');

// Championnats / Coupes uniquement
$championnats = $models['engagements']->getChampionnatEngagements();
$coupes = $models['engagements']->getCoupeEngagements();

// Vérifier engagement
$isEngaged = $models['engagements']->isEngaged(1, 123);

// Nombre d'engagements
$count = $models['engagements']->countEngagements();
$countEquipe = $models['engagements']->countEngagementsByEquipe(1);
```

---

## 10. ConfigModel

**Table**: `pprod_config`  
**Description**: Configuration système

### Méthodes principales

```php
// Une valeur de config
$value = $models['config']->get('current_season');

// Toutes les configs
$allConfigs = $models['config']->getAll();

// Plusieurs valeurs
$configs = $models['config']->getMultiple(['current_season', 'last_sync_club']);

// Saison actuelle
$season = $models['config']->getCurrentSeason();

// Dernière synchronisation
$lastSync = $models['config']->getLastSync('club');
$allSyncs = $models['config']->getAllLastSync();

// Vérifier existence
$exists = $models['config']->exists('current_season');

// Par préfixe
$syncs = $models['config']->getByPrefix('last_sync_');

// Avec timestamp
$configData = $models['config']->getWithTimestamp('current_season');
```

---

## 11. SyncLogsModel

**Table**: `pprod_sync_logs`  
**Description**: Logs de synchronisation API

### Méthodes principales

```php
// Tous les logs
$logs = $models['sync_logs']->getAllLogs(100);

// Par endpoint
$logsClub = $models['sync_logs']->getLogsByEndpoint('club', 50);

// Par statut
$errors = $models['sync_logs']->getErrors(20);
$successes = $models['sync_logs']->getSuccesses(20);
$logs = $models['sync_logs']->getLogsByStatus('warning', 20);

// Par période
$logsNovembre = $models['sync_logs']->getLogsByDateRange('2025-11-01', '2025-11-30');
$logsToday = $models['sync_logs']->getTodayLogs();

// Combinaisons
$logsClubErrors = $models['sync_logs']->getLogsByEndpointAndStatus('club', 'error');

// Statistiques
$stats = $models['sync_logs']->getStats();
$statsEndpoint = $models['sync_logs']->getStatsByEndpoint('club');
$allStats = $models['sync_logs']->getAllEndpointsStats();

// Dernier log
$lastLog = $models['sync_logs']->getLastLog();
$lastLogClub = $models['sync_logs']->getLastLog('club');

// Recherche
$results = $models['sync_logs']->searchLogs('erreur');

// Performance
$slowest = $models['sync_logs']->getSlowestLogs(10);
$fastest = $models['sync_logs']->getFastestLogs(10);

// Nombre de logs
$count = $models['sync_logs']->countLogs();
$countErrors = $models['sync_logs']->countLogs('error');
```

---

## Cas d'Usage Typiques

### Page d'Accueil

```php
$club = $models['club']->getClubEssentials();
$nextMatch = $models['matchs']->getNextHomeMatch();
$lastResults = $models['matchs']->getLastResults(5);
$nbEquipes = $models['equipes']->countEquipes();
```

### Page Calendrier

```php
$upcomingMatchs = $models['matchs']->getUpcomingMatchs(20);
$matchsByTeam = $models['matchs']->getMatchsByTeamCategory('SEM', false);
```

### Page Résultats

```php
$results = $models['matchs']->getLastResults(30);
$homeResults = $models['matchs']->getHomeMatchs(true, 15);
$awayResults = $models['matchs']->getAwayMatchs(true, 15);
```

### Page Classements

```php
$competitions = $models['classements']->getCompetitionsWithClassement();
foreach ($competitions as $comp) {
    $classement = $models['classements']->getCurrentClassement($comp['id']);
    $position = $models['classements']->getClubPosition($comp['id']);
}
```

### Détail Match

```php
$match = $models['matchs']->getMatchById($id);
// Contient automatiquement: competition_name, terrain_name, opponent_logo
```

---

## Conventions et Bonnes Pratiques

### Paramètres par défaut
- `$season`: `null` = saison actuelle
- `$limit`: Valeur raisonnable par défaut (10-50)
- `$isResult`: `null` = tous, `true` = résultats, `false` = calendrier

### Assertions
Toutes les méthodes valident leurs paramètres:
```php
assert($id > 0, 'ID must be positive');
assert(!empty($search), 'Search term cannot be empty');
```

### Gestion NULL
- Méthode `getXxxById()` → retourne `array|null`
- Méthode `getAllXxx()` → retourne toujours `array` (vide si aucun)

### Compteurs
Limite maximale dans les boucles pour éviter timeout:
```php
$counter = 0;
$maxIterations = 1000;
while ($condition && $counter++ < $maxIterations) {
    // Code
}
```

---

## Fichiers Créés

1. `ClubModel.php` - Informations du club
2. `TerrainsModel.php` - Terrains
3. `MembresModel.php` - Membres du bureau
4. `CompetitionsModel.php` - Compétitions
5. `EquipesModel.php` - Équipes
6. `MatchsModel.php` - Calendrier et résultats ⭐
7. `ClubsCacheModel.php` - Cache clubs adverses 🆕
8. `ClassementsModel.php` - Classements historisés 🆕
9. `EngagementsModel.php` - Pivot équipes-compétitions
10. `ConfigModel.php` - Configuration système
11. `SyncLogsModel.php` - Logs de synchronisation
12. `ModelsLoader.php` - Chargeur automatique
13. `EXAMPLES_USAGE.php` - Exemples d'utilisation

---

**🎯 Tout est prêt pour le développement frontend!**

Les modèles gèrent:
- ✅ Sécurité (requêtes préparées)
- ✅ Validation (assertions)
- ✅ Jointures automatiques
- ✅ Cache logos adversaires
- ✅ Historisation classements
- ✅ Performance (limites, index)