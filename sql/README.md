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
                     │                           │
                     │                           ├─────► pprod_matchs
                     │                           └─────► pprod_classements ⭐ NOUVEAU
                     │
                     └─────► pprod_matchs (liens via home/away_club_id)
                                   │
                                   ├─────► pprod_competitions
                                   ├─────► pprod_terrains (optionnel)
                                   └─────► pprod_clubs_cache (logos clubs adverses)
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

### 2. `pprod_clubs_cache`
**Cache des clubs adverses (logos et infos)**

```sql
-- Colonnes principales
id                INT UNSIGNED PRIMARY KEY
cl_no             INT UNSIGNED UNIQUE  -- ID club API FFF
name              VARCHAR(255)
short_name        VARCHAR(100)
logo_url          TEXT                 -- Logo du club adverse
created_at        TIMESTAMP
updated_at        TIMESTAMP

-- Index
INDEX idx_cl_no (cl_no)
```

**Usage :** 
- Stocke les informations des clubs adverses rencontrés
- Permet d'afficher les logos des équipes adverses sans appel API supplémentaire
- Mis à jour automatiquement lors de la synchronisation des matchs
- Exclut le club FC Chiche (cl_no = 5403)

**Synchronisation :** Alimentée par `Sync::updateClubsCache()` depuis les matchs calendrier/résultats

---

### 3. `pprod_matchs`
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

### 4. `pprod_classements` ⭐ NOUVEAU
**Classements par journée (historisation complète)**

```sql
-- Colonnes principales
id                      INT UNSIGNED PRIMARY KEY
competition_id          INT UNSIGNED (FK → pprod_competitions)
season                  INT UNSIGNED            -- 2025
date                    DATE NOT NULL           -- Date de la journée
cj_no                   TINYINT UNSIGNED        -- Numéro journée
type                    VARCHAR(5)              -- J (Journée)

-- Identification équipe
cl_no                   INT UNSIGNED            -- ID club
team_category           VARCHAR(10)             -- SEM, U17, U15, U13
team_number             TINYINT UNSIGNED
team_short_name         VARCHAR(255)

-- Classement et points
ranking                 TINYINT UNSIGNED        -- Position classement
point_count             TINYINT UNSIGNED        -- Points
penalty_point_count     TINYINT UNSIGNED        -- Pénalités

-- Statistiques matchs
total_games_count       TINYINT UNSIGNED        -- Matchs joués
won_games_count         TINYINT UNSIGNED        -- Victoires
draw_games_count        TINYINT UNSIGNED        -- Nuls
lost_games_count        TINYINT UNSIGNED        -- Défaites
forfeits_games_count    TINYINT UNSIGNED        -- Forfaits

-- Statistiques buts
goals_for_count         SMALLINT UNSIGNED       -- Buts pour
goals_against_count     SMALLINT UNSIGNED       -- Buts contre
goals_diff              SMALLINT                -- Différence buts

-- Poule
phase_number            TINYINT UNSIGNED
poule_stage_number      TINYINT UNSIGNED
poule_name              VARCHAR(255)            -- Ex: "POULE B"

-- Flags
is_forfait              TINYINT(1)              -- Forfait général

-- Métadonnées
external_updated_at     DATETIME
created_at              TIMESTAMP
updated_at              TIMESTAMP

-- Contraintes et index
FOREIGN KEY (competition_id) REFERENCES pprod_competitions(id)
UNIQUE KEY unique_classement (competition_id, cl_no, cj_no, season)
INDEX idx_competition (competition_id)
INDEX idx_cl_no (cl_no)
INDEX idx_season (season)
INDEX idx_date (date)
INDEX idx_ranking (ranking)
```

**Usage :**
- Stocke l'historique complet des classements à chaque journée
- Permet d'afficher l'évolution du classement dans le temps
- Clé unique : `(competition_id, cl_no, cj_no, season)` - une ligne par équipe par journée
- Synchronisé uniquement pour les championnats (type = 'CH')

**Synchronisation :** Alimentée par `Sync::syncClassements()` via `FFFApiClient::getAllClassements()`

---

### 5. `pprod_equipes`
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

### 6. `pprod_competitions`
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

## Requêtes SQL Essentielles

### 1. Dernier classement d'une compétition (affichage)

```sql
SELECT 
    c.ranking AS position,
    c.team_short_name AS equipe,
    cc.logo_url AS logo,
    c.point_count AS pts,
    c.total_games_count AS joues,
    c.won_games_count AS g,
    c.draw_games_count AS n,
    c.lost_games_count AS p,
    c.goals_for_count AS bp,
    c.goals_against_count AS bc,
    c.goals_diff AS diff,
    c.cl_no = 5403 AS is_chiche
FROM pprod_classements c
LEFT JOIN pprod_clubs_cache cc ON c.cl_no = cc.cl_no
WHERE c.competition_id = :competition_id
  AND c.cj_no = (
    SELECT MAX(cj_no) 
    FROM pprod_classements 
    WHERE competition_id = :competition_id
  )
ORDER BY c.ranking ASC;
```

### 2. Classement actuel équipe FC Chiche

```sql
SELECT 
    comp.name AS competition,
    c.ranking AS position,
    c.point_count AS points,
    c.total_games_count AS matchs_joues,
    c.goals_diff AS diff_buts,
    c.cj_no AS journee_actuelle
FROM pprod_classements c
JOIN pprod_competitions comp ON c.competition_id = comp.id
WHERE c.cl_no = 5403
  AND c.cj_no = (
    SELECT MAX(cj_no) 
    FROM pprod_classements c2 
    WHERE c2.competition_id = c.competition_id 
      AND c2.cl_no = 5403
  )
ORDER BY comp.name;
```

### 3. Évolution position dans classement

```sql
SELECT 
    c.date,
    c.cj_no AS journee,
    c.ranking AS position,
    c.point_count AS points,
    c.goals_diff AS diff
FROM pprod_classements c
WHERE c.competition_id = :competition_id
  AND c.cl_no = 5403
ORDER BY c.cj_no ASC;
```

### 4. Dernier résultat de chaque coupe (pour affichage)

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
    END AS statut,
    CASE 
        WHEN m.home_club_id = 5403 THEN cc_away.logo_url
        ELSE cc_home.logo_url
    END AS logo_adversaire
FROM pprod_matchs m
JOIN pprod_competitions c ON m.competition_id = c.id
LEFT JOIN pprod_clubs_cache cc_home ON m.home_club_id = cc_home.cl_no
LEFT JOIN pprod_clubs_cache cc_away ON m.away_club_id = cc_away.cl_no
WHERE m.is_result = 1
  AND c.type = 'CP'
  AND (m.home_club_id = 5403 OR m.away_club_id = 5403)
  AND m.id IN (
    SELECT MAX(m2.id)
    FROM pprod_matchs m2
    WHERE m2.competition_id = m.competition_id
      AND m2.is_result = 1
      AND (m2.home_club_id = 5403 OR m2.away_club_id = 5403)
    GROUP BY m2.competition_id
  )
ORDER BY m.date DESC;
```

---

## Points d'attention

### Format API Variable

- Pas toujours de structure `hydra:member`
- Possibles matchs éliminatoires uniques
- Garder uniquement : **dernier résultat + matchs à venir**
- Classements disponibles uniquement pour les championnats (type = 'CH')

### Identifiants Uniques

- `cl_no` = ID club API (5403 pour FC Chiche)
- `cp_no` = ID compétition API
- `ma_no` = ID match API (unique, même si reporté)
- `cj_no` = Numéro de journée (classement)

### Champs Nullable

- `terrain_id` : Parfois non défini au moment de la création du match
- `home_score` / `away_score` : NULL si match non joué
- `initial_date` : NULL si jamais reporté
- `logo_url` dans `pprod_clubs_cache` : NULL si non disponible dans API

### Index Critiques

Les index suivants sont essentiels pour les performances :
- `idx_date` sur `pprod_matchs(date)`
- `idx_is_result` sur `pprod_matchs(is_result)`
- `idx_home_club` / `idx_away_club` sur `pprod_matchs`
- `idx_cl_no` sur `pprod_clubs_cache`
- `idx_competition` sur `pprod_classements` ⭐ NOUVEAU
- `idx_ranking` sur `pprod_classements` ⭐ NOUVEAU

### Cache et Données Externes

- Table `pprod_clubs_cache` alimentée automatiquement par `Sync::updateClubsCache()`
- Contient uniquement les clubs adverses (exclut cl_no = 5403)
- Mise à jour lors de chaque synchronisation de matchs
- Permet l'affichage rapide des logos sans appel API supplémentaire

### Historisation Classements ⭐ NOUVEAU

- Table `pprod_classements` historise chaque journée
- Permet de tracer l'évolution du classement dans le temps
- Synchronisée uniquement pour les championnats (type = 'CH')
- Clé unique : `(competition_id, cl_no, cj_no, season)`

---

## Checklist Déploiement

- [x] Tables créées
- [x] Script synchronisation fonctionnel
- [x] CRON configuré (8h et 20h)
- [x] Table clubs_cache pour logos adversaires
- [x] Méthode getAllMatchs() via engagements
- [x] Table classements avec historisation ⭐ NOUVEAU
- [x] Synchronisation classements automatique ⭐ NOUVEAU
- [ ] Créer Models PHP pour requêtes (ajouter ClassementModel)
- [ ] Créer pages web (index, calendrier, résultats, classements)
- [ ] PWA (manifest.json, service worker)

---

**Version :** 1.2  
**Dernière mise à jour :** Octobre 2025  
**Changelog :**
- Ajout table `pprod_classements` pour historisation classements ⭐ NOUVEAU
- Ajout méthodes API `getClassement()` et `getAllClassements()` ⭐ NOUVEAU
- Ajout synchronisation automatique classements dans `Sync::syncClassements()` ⭐ NOUVEAU
- Ajout requêtes SQL pour affichage classements ⭐ NOUVEAU
- Ajout table `pprod_clubs_cache` pour cache logos clubs adverses
- Mise à jour requêtes SQL avec JOIN sur clubs_cache
- Documentation méthode `Sync::updateClubsCache()`

**Maintenu par :** FC Chiche Dev Team