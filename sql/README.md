# Modèle Base de Données FC Chiche - Documentation Complète

## Vue d'ensemble

```
┌─────────────┐
│ pprod_club  │ ← Club FC Chiche (1 seul)
└──────┬──────┘
       │
       ├─────► pprod_terrains     (N terrains)
       ├─────► pprod_membres      (N membres bureau)
       └─────► pprod_equipes      (6 équipes actuelles)
                     │
                     ├─────► pprod_engagements (pivot équipe-compétition)
                     │             │
                     │             └─────► pprod_competitions (11 compétitions)
                     │
                     └─────► pprod_matchs (liens via home/away_club_id)
                                   │
                                   └─────► pprod_competitions
                                   └─────► pprod_terrains (optionnel)
```

---

## Tables Principales

### 1. `pprod_club`
**Informations du club FC Chiche**

```sql
-- Colonnes principales
id                   INT UNSIGNED PRIMARY KEY
cl_no                INT UNSIGNED UNIQUE (5403)
affiliation_number   INT UNSIGNED
name                 VARCHAR(255)
short_name           VARCHAR(100)
logo_url             TEXT
address1/2/3         VARCHAR(255)
postal_code          VARCHAR(10)
latitude/longitude   DECIMAL
district_name        VARCHAR(255)
```

**Usage :** Une seule ligne, référence pour toutes les autres tables.

---

### 2. `pprod_equipes`
**Les 6 équipes du club**

```sql
-- Colonnes principales
id                INT UNSIGNED PRIMARY KEY
club_id           INT UNSIGNED (FK → pprod_club)
category_code     VARCHAR(10)  -- SEM, U17, U15, U13
number            TINYINT      -- 1, 2, 3, 4, 5, 6
code              TINYINT      -- Code interne FFF
short_name        VARCHAR(255) -- "CHICHE FC"
type              VARCHAR(5)   -- "C" (Club)
season            INT          -- 2025
category_label    VARCHAR(100) -- "Senior Libre", "U17 - U16 Libre"
category_gender   CHAR(1)      -- "M" (Masculin)
diffusable        TINYINT(1)   -- 0/1

-- Index
UNIQUE KEY unique_team (club_id, category_code, number, season)
```

**Équipes actuelles :**
- SEM 1 : Seniors 1 (Départemental 3)
- SEM 3 : Seniors 2 (Départemental 5)
- SEM 6 : Seniors 3 (Départemental 5)
- U17 4 : U17
- U15 2 : U15
- U13 5 : U13

---

### 3. `pprod_competitions`
**Les 11 compétitions (championnats + coupes)**

```sql
-- Colonnes principales
id                    INT UNSIGNED PRIMARY KEY
cp_no                 INT UNSIGNED UNIQUE  -- ID API FFF
season                INT UNSIGNED         -- 2025
type                  VARCHAR(10)          -- CH (Championnat) / CP (Coupe)
name                  VARCHAR(255)
level                 VARCHAR(10)          -- F/L/D (Fédération/Ligue/District)
cdg_cg_no             INT UNSIGNED
cdg_name              VARCHAR(255)
external_updated_at   DATETIME

-- Index
INDEX idx_cp_no (cp_no)
INDEX idx_season (season)
INDEX idx_type (type)
```

**Compétitions actuelles :**

| cp_no  | Type | Nom                                    | Level |
|--------|------|----------------------------------------|-------|
| 435164 | CP   | COUPE DE FRANCE CRÉDIT AGRICOLE        | F     |
| 435202 | CP   | Coupe de Nouvelle-Aquitaine            | L     |
| 436839 | CP   | Coupe des Deux-Sèvres                  | D     |
| 436838 | CP   | Coupe Saboureau                        | D     |
| 436831 | CH   | Seniors Départemental 3                | D     |
| 436833 | CH   | Seniors Départemental 5 -1ère phase-   | D     |
| 442762 | CH   | U17 -2ème Division-                    | D     |
| 442766 | CH   | U15 A7                                 | D     |
| 442777 | CH   | U13 -4ème Division-                    | D     |
| 442789 | CP   | U17 Coupe Départementale               | D     |

---

### 4. `pprod_matchs`
**Tous les matchs (résultats + calendrier)**

```sql
-- Colonnes principales
id                      INT UNSIGNED PRIMARY KEY
ma_no                   BIGINT UNSIGNED UNIQUE  -- ID match API FFF
competition_id          INT UNSIGNED (FK → pprod_competitions)
terrain_id              INT UNSIGNED (FK → pprod_terrains, nullable)
season                  INT UNSIGNED            -- 2025

-- Date et heure
date                    DATE NOT NULL
time                    VARCHAR(10)             -- "15H00"
initial_date            DATE                    -- Si reporté

-- Structure compétition
phase_number            TINYINT UNSIGNED
phase_type              VARCHAR(10)
phase_name              VARCHAR(255)
poule_stage_number      TINYINT UNSIGNED
poule_name              VARCHAR(255)
poule_journee_number    TINYINT UNSIGNED

-- Équipe domicile
home_club_id            INT UNSIGNED            -- cl_no (5403 si Chiche à domicile)
home_team_category      VARCHAR(10)             -- SEM, U17, U15, U13
home_team_number        TINYINT UNSIGNED
home_team_name          VARCHAR(255)
home_score              TINYINT UNSIGNED        -- NULL si match à venir
home_is_forfeit         CHAR(1)                 -- N/O

-- Équipe extérieur
away_club_id            INT UNSIGNED
away_team_category      VARCHAR(10)
away_team_number        TINYINT UNSIGNED
away_team_name          VARCHAR(255)
away_score              TINYINT UNSIGNED
away_is_forfeit         CHAR(1)

-- Statut
status                  VARCHAR(5)              -- A (Arbitré), ...
status_label            VARCHAR(50)
is_overtime             CHAR(1)                 -- N/O
seems_postponed         VARCHAR(10)
is_result               TINYINT(1)              -- 0 = calendrier, 1 = résultat

external_updated_at     DATETIME

-- Index
INDEX idx_ma_no (ma_no)
INDEX idx_date (date)
INDEX idx_season (season)
INDEX idx_competition (competition_id)
INDEX idx_home_club (home_club_id)
INDEX idx_away_club (away_club_id)
INDEX idx_is_result (is_result)
```

**Distinction résultat vs calendrier :**
- `is_result = 1` : Match terminé avec score
- `is_result = 0` : Match à venir

---

## Requêtes SQL Essentielles

### 1. Récupérer les 5 derniers résultats

```sql
SELECT 
    DATE_FORMAT(m.date, '%d/%m/%Y') AS date,
    m.time,
    CASE 
        WHEN m.home_club_id = 5403 THEN 'DOM'
        ELSE 'EXT'
    END AS lieu,
    m.home_team_name AS domicile,
    m.home_score,
    m.away_score,
    m.away_team_name AS exterieur,
    CASE 
        WHEN m.home_club_id = 5403 THEN
            CASE 
                WHEN m.home_score > m.away_score THEN 'V'
                WHEN m.home_score < m.away_score THEN 'D'
                ELSE 'N'
            END
        ELSE
            CASE 
                WHEN m.away_score > m.home_score THEN 'V'
                WHEN m.away_score < m.home_score THEN 'D'
                ELSE 'N'
            END
    END AS resultat,
    c.name AS competition,
    c.type AS competition_type,
    CONCAT(e.category_code, ' ', e.number) AS equipe_chiche
FROM pprod_matchs m
JOIN pprod_competitions c ON m.competition_id = c.id
LEFT JOIN pprod_equipes e ON (
    (m.home_club_id = 5403 AND e.category_code = m.home_team_category AND e.number = m.home_team_number)
    OR (m.away_club_id = 5403 AND e.category_code = m.away_team_category AND e.number = m.away_team_number)
)
WHERE m.is_result = 1
  AND (m.home_club_id = 5403 OR m.away_club_id = 5403)
ORDER BY m.date DESC, m.time DESC
LIMIT 5;
```

### 2. Récupérer les 5 prochains matchs

```sql
SELECT 
    DATE_FORMAT(m.date, '%d/%m/%Y') AS date,
    m.time,
    CASE 
        WHEN m.home_club_id = 5403 THEN 'DOM'
        ELSE 'EXT'
    END AS lieu,
    m.home_team_name AS domicile,
    m.away_team_name AS exterieur,
    c.name AS competition,
    c.type AS competition_type,
    t.name AS terrain,
    t.city AS ville_terrain,
    CONCAT(e.category_code, ' ', e.number) AS equipe_chiche
FROM pprod_matchs m
JOIN pprod_competitions c ON m.competition_id = c.id
LEFT JOIN pprod_terrains t ON m.terrain_id = t.id
LEFT JOIN pprod_equipes e ON (
    (m.home_club_id = 5403 AND e.category_code = m.home_team_category AND e.number = m.home_team_number)
    OR (m.away_club_id = 5403 AND e.category_code = m.away_team_category AND e.number = m.away_team_number)
)
WHERE m.is_result = 0
  AND (m.home_club_id = 5403 OR m.away_club_id = 5403)
  AND m.date >= CURDATE()
ORDER BY m.date ASC, m.time ASC
LIMIT 5;
```

### 3. Calendrier complet d'une équipe

```sql
-- Exemple pour Seniors 1 (SEM 1)
SELECT 
    DATE_FORMAT(m.date, '%d/%m/%Y') AS date,
    m.time,
    CASE WHEN m.home_club_id = 5403 THEN 'DOM' ELSE 'EXT' END AS lieu,
    m.home_team_name AS domicile,
    m.away_team_name AS exterieur,
    m.home_score,
    m.away_score,
    c.name AS competition,
    m.poule_journee_number AS journee,
    m.is_result
FROM pprod_matchs m
JOIN pprod_competitions c ON m.competition_id = c.id
WHERE (
    (m.home_club_id = 5403 AND m.home_team_category = 'SEM' AND m.home_team_number = 1)
    OR (m.away_club_id = 5403 AND m.away_team_category = 'SEM' AND m.away_team_number = 1)
)
ORDER BY m.date ASC, m.time ASC;
```

### 4. Statistiques d'une équipe (bilan saison)

```sql
SELECT 
    COUNT(*) AS matchs_joues,
    SUM(CASE 
        WHEN m.home_club_id = 5403 THEN
            CASE WHEN m.home_score > m.away_score THEN 1 ELSE 0 END
        ELSE
            CASE WHEN m.away_score > m.home_score THEN 1 ELSE 0 END
    END) AS victoires,
    SUM(CASE 
        WHEN m.home_score = m.away_score THEN 1 ELSE 0 
    END) AS nuls,
    SUM(CASE 
        WHEN m.home_club_id = 5403 THEN
            CASE WHEN m.home_score < m.away_score THEN 1 ELSE 0 END
        ELSE
            CASE WHEN m.away_score < m.home_score THEN 1 ELSE 0 END
    END) AS defaites,
    SUM(CASE WHEN m.home_club_id = 5403 THEN m.home_score ELSE m.away_score END) AS buts_pour,
    SUM(CASE WHEN m.home_club_id = 5403 THEN m.away_score ELSE m.home_score END) AS buts_contre
FROM pprod_matchs m
WHERE m.is_result = 1
  AND (
    (m.home_club_id = 5403 AND m.home_team_category = 'SEM' AND m.home_team_number = 1)
    OR (m.away_club_id = 5403 AND m.away_team_category = 'SEM' AND m.away_team_number = 1)
  );
```

### 5. Dernier résultat de chaque coupe (pour affichage)

```sql
SELECT 
    c.name AS coupe,
    DATE_FORMAT(m.date, '%d/%m/%Y') AS date,
    m.home_team_name AS domicile,
    m.home_score,
    m.away_score,
    m.away_team_name AS exterieur,
    CASE 
        WHEN m.home_club_id = 5403 THEN 'DOM' 
        ELSE 'EXT' 
    END AS lieu,
    CASE 
        WHEN m.home_club_id = 5403 THEN
            CASE 
                WHEN m.home_score > m.away_score THEN 'Qualifié'
                ELSE 'Éliminé'
            END
        ELSE
            CASE 
                WHEN m.away_score > m.home_score THEN 'Qualifié'
                ELSE 'Éliminé'
            END
    END AS statut
FROM pprod_matchs m
JOIN pprod_competitions c ON m.competition_id = c.id
WHERE m.is_result = 1
  AND c.type = 'CP'  -- CP = Coupe
  AND (m.home_club_id = 5403 OR m.away_club_id = 5403)
  AND m.id IN (
    -- Dernier match de chaque coupe
    SELECT MAX(m2.id)
    FROM pprod_matchs m2
    WHERE m2.competition_id = m.competition_id
      AND m2.is_result = 1
      AND (m2.home_club_id = 5403 OR m2.away_club_id = 5403)
    GROUP BY m2.competition_id
  )
ORDER BY m.date DESC;
```

### 6. Planning de la semaine (tous matchs dans 7 jours)

```sql
SELECT 
    DATE_FORMAT(m.date, '%a %d/%m') AS jour,
    m.time,
    CONCAT(e.category_code, ' ', e.number) AS equipe,
    CASE WHEN m.home_club_id = 5403 THEN 'DOM' ELSE 'EXT' END AS lieu,
    CASE 
        WHEN m.home_club_id = 5403 THEN m.away_team_name
        ELSE m.home_team_name
    END AS adversaire,
    c.name AS competition,
    t.name AS terrain
FROM pprod_matchs m
JOIN pprod_competitions c ON m.competition_id = c.id
LEFT JOIN pprod_terrains t ON m.terrain_id = t.id
LEFT JOIN pprod_equipes e ON (
    (m.home_club_id = 5403 AND e.category_code = m.home_team_category AND e.number = m.home_team_number)
    OR (m.away_club_id = 5403 AND e.category_code = m.away_team_category AND e.number = m.away_team_number)
)
WHERE m.is_result = 0
  AND (m.home_club_id = 5403 OR m.away_club_id = 5403)
  AND m.date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
ORDER BY m.date ASC, m.time ASC;
```

---

## Classes PHP Recommandées

### Structure suggérée

```php
src/
├── Models/
│   ├── Club.php           // Infos club
│   ├── Equipe.php         // Gestion équipes
│   ├── Competition.php    // Gestion compétitions
│   ├── Match.php          // Requêtes matchs
│   └── Stats.php          // Statistiques
├── API/
│   └── FFFApiClient.php   // (existant)
├── Database/
│   ├── Sync.php           // (existant)
│   └── Database.php       // (existant)
└── Utils/
    └── Logger.php         // (existant)
```

### Exemple Model Match.php

```php
class Match
{
    private PDO $pdo;
    
    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }
    
    /**
     * Récupérer derniers résultats
     */
    public function getLastResults(int $limit = 5): array
    {
        $sql = "SELECT ... (voir requête SQL ci-dessus)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Récupérer prochains matchs
     */
    public function getUpcomingMatches(int $limit = 5): array
    {
        // ...
    }
    
    /**
     * Calendrier d'une équipe
     */
    public function getTeamSchedule(string $category, int $number): array
    {
        // ...
    }
}
```

---

## Notes Importantes

### Gestion des Coupes

Les coupes ont un format API différent :
- Pas toujours de structure `hydra:member`
- Possibles matchs éliminatoires uniques
- Garder uniquement : **dernier résultat + matchs à venir**

### Identifiants Uniques

- `cl_no` = ID club API (5403)
- `cp_no` = ID compétition API
- `ma_no` = ID match API (unique, même si reporté)

### Champs Nullable

- `terrain_id` : Parfois non défini au moment de la création du match
- `home_score` / `away_score` : NULL si match non joué
- `initial_date` : NULL si jamais reporté

### Index Critiques

Les index suivants sont essentiels pour les performances :
- `idx_date` sur `pprod_matchs(date)`
- `idx_is_result` sur `pprod_matchs(is_result)`
- `idx_home_club` / `idx_away_club` sur `pprod_matchs`

---

## Checklist Déploiement

- [x] Tables créées
- [x] Script synchronisation fonctionnel
- [x] CRON configuré (8h et 20h)
- [ ] Adapter format coupes dans FFFApiClient.php
- [ ] Créer Models PHP pour requêtes
- [ ] Créer pages web (index, calendrier, résultats)
- [ ] PWA (manifest.json, service worker)

---

**Version :** 1.0  
**Dernière mise à jour :** Octobre 2025  
**Maintenu par :** FC Chiche Dev Team