# ANALYSE COMPLÈTE DE L'ARCHITECTURE FC CHICHÉ

## 1. STRUCTURE GLOBALE
Le projet est organisé en 6 répertoires principaux:
- config/ : Configuration application (BDD, API FFF, environnement)
- src/ : Code métier (Models, API client, Database sync, Utils)
- public/ : Répertoire web racine (pages, API endpoints, assets)
- cron/ : Script synchronisation CRON (2x/jour)
- sql/ : Schéma BDD SQL
- templates/ : Templates HTML partagées

## 2. FICHIERS HTML/CSS/JS (NON-REACT)

### HTML (2 fichiers)
- templates/index.html: Page accueil SPA (1760 lignes, 100% CSS-in-HTML, vanilla JS)
- public/api/docs.html: Documentation API manuelle

### CSS (2 fichiers)
- common.css: Design system (couleurs --fc-green, typographie, animations)
- index.css: Styles page accueil (surtout vide, hérités de common.css)

Design réactif, variables CSS, animations reveal.js, grille auto-fit.

### JavaScript (7 fichiers, ~2500 lignes)
- api.js: Classe ApiClient (fetch + retry 3x, timeout 10s)
- common.js: Utilitaires (nav mobile, reveal animations, SW registration)
- index.js: Bootstrap page accueil (filtres équipes/résultats/classements)
- matchs.js: Calendrier (fetch API, filtrage équipe/compétition)
- resultats.js: Résultats (fetch, filtrage, rendu cartes)
- classements.js: Classements (par compétition, tables dynamiques)
- service-worker.js: PWA (caching strategies, offline fallback)

PATTERNS: Vanilla JS, assertions systématiques console.assert(), pas de frameworks.

## 3. API ENDPOINTS (14 endpoints)

### Lecture (Publics - GET)
- /api/matchs.php: Matchs avec 15+ paramètres (id, upcoming, last_results, competition_id, etc.)
- /api/classements.php: Classements par compétition
- /api/equipes.php: Équipes du club
- /api/competitions.php: Toutes compétitions
- /api/club.php: Infos club
- /api/engagements.php: Pivot équipes-compétitions
- /api/terrains.php: Lieux matchs
- /api/membres.php: Bureau du club
- /api/clubs-cache.php: Cache clubs adversaires
- /api/sync-logs.php: Logs synchronisation
- /api/config.php: Configuration système

### Écriture (Authentifiés - POST/PUT/DELETE)
- POST /api/auth.php: Authentification admin
- POST/PUT/DELETE /api/matchs.php: CRUD matchs

Authentification: JWT Bearer token, protégée par ApiAuth::protectWrite()
CORS: Headers Access-Control-Allow-* dans ApiResponse::setCorsHeaders()

## 4. DÉPENDANCES ACTUELLES

### Backend
- PHP 8.1+ (vanilla, sans frameworks)
- PDO + PDO_MySQL (native)
- Extensions: JSON, cURL (implicite)
- BDD: MySQL (fcchice79.mysql.db, charset utf8mb4)

### Frontend
- ZÉRO dépendances npm (package.json inexistant)
- Fetch API native, LocalStorage, Service Worker API
- Aucun framework CSS/JS

### Outils externes
- API FFF: https://api-dofa.fff.fr/api (synchronisation)
- Google Fonts: Police Manrope
- OVH CRON: 2x/jour (8h, 20h)

## 5. PAGES/ROUTES PRINCIPALES

Routes statiques PHP:
- / (index.php): Accueil SPA
- /matchs: Calendrier
- /resultats: Résultats
- /classements: Classements
- /contact: Contact
- /galerie: Galerie photos
- /partenaires: Sponsors
- /equipes/{name}: Pages équipes (legacy)

Système templates:
- bootstrap.php: Initialise $basePath, $assetsBase, $apiBase
- templates/header.php: <head>, <header>, navigation
- templates/footer.php: </body>, scripts, footer

Architecture hybride: PHP rend HTML structurel + JS fait fetch() pour contenu dynamique.

## 6. STRUCTURE BACKEND PHP

### Modèles (src/Models/)
- MatchsModel: Calendrier/résultats CRUD
- ClassementsModel: Classements officiels
- EquipesModel: Équipes du club
- CompetitionsModel: Compétitions
- EngagementsModel: Pivot équipes-compétitions
- ClubModel: Infos club
- TerrainsModel: Lieux matchs
- MembresModel: Bureau
- ConfigModel: Clés/valeurs système
- SyncLogsModel: Logs synchronisation
- ClubsCacheModel: Cache clubs adversaires

### Synchronisation (src/Database/Sync.php)
Exécuté par CRON 2x/jour:
- syncClub(): Infos club
- syncEquipes(): Équipes
- syncCompetitions(): Compétitions
- syncEngagements(): Engagements équipes
- syncClassements(): Classements (~190 lignes)
- syncMatchs(): Matchs/résultats (~90 lignes)
- Puis updateConfigValue() pour timestamps

Flux: FFFApiClient → JSON → Transform → Models → INSERT/UPDATE/DELETE via PDO

### API Client FFF (src/API/FFFApiClient.php)
Wrapper pour api-dofa.fff.fr:
- getClubInfo(): GET /clubs/{id}
- getEquipes(): GET /clubs/{id}/equipes
- getEngagements(): GET /engagements?club.cl_no={id}
- getAllClassements(): GET /classements + loop
- getMatchs(): GET /matchs?club={id}&season={year}

Base URL: https://api-dofa.fff.fr/api
Timeout: 30s par requête
Format: JSON Hydra (hydra:member, hydra:totalItems)

### Base de Données
Schema normalisé (prefix pprod_):
- pprod_club: Infos club
- pprod_equipes: Équipes (id, code, name, category)
- pprod_competitions: Compétitions (id, code, name, type, season)
- pprod_engagements: Pivot (equipe_id, competition_id, phase, poule)
- pprod_matchs: 50+ colonnes (dates, scores, équipes, terrains, phases, journées)
- pprod_classements: Classements (competition_id, team_code, pos, pts, stats)
- pprod_terrains: Terrains (id, name, location)
- pprod_membres: Bureau (id, role, name, phone, email)
- pprod_sync_logs: Logs (created_at, action, status, message)
- pprod_config: Config (key, value, updated_at)
- pprod_clubs_cache: Cache clubs (club_id, name, code)

Normalisation: 3NF complète, indexes sur clés primaires et colonnes fréquentes.

## 7. RÉSUMÉ ARCHITECTURE

Stack technique:
- Frontend: HTML5 vanilla + CSS no-framework + JS vanilla (fetch API, Service Worker)
- Backend: PHP 8.1 vanilla + PDO + MySQL
- Sync: CRON automatisé via sync_data.php
- Déploiement: OVH mutualisé, Git auto-deploy

Responsabilités:
- Frontend JS: Filtrer/paginer, animer, affichage
- API HTTP: Endpoints JSON, authentification, CORS
- Models: Requêtes DB, enrichissement données
- MySQL: Stockage normalisé
- CRON: Synchronisation FFF → MySQL

## 8. POINTS FORTS
✅ Architecture propre (séparation models/API/sync)
✅ Zéro dépendances (maintenabilité)
✅ Requêtes PDO préparées (sécurité)
✅ Logging complet (traçabilité)
✅ Frontend moderne (design system, animations)
✅ PWA-ready (Service Worker, manifest)
✅ API REST claire (OpenAPI docs)
✅ DB 3NF normalisée

## 9. POINTS À AMÉLIORER (Audit REVIEW.md)
⚠️ Assertions insuffisantes (NASA Power of 10 exige 2+ assertions/fonction)
⚠️ Fonctions trop longues (syncClassements 190 lignes, syncMatchs 90 lignes)
⚠️ Une requête SQL non préparée (syncMembres utilise exec() avec interpolation)
⚠️ Validations manquantes (structures réponses API non vérifiées)
⚠️ Wrappers sans assertions (Logger::info() ne valide pas entrées)

## 10. DÉPLOIEMENT
CRON: 0 8,20 * * * /usr/bin/php .../cron/sync_data.php
Déploiement: git push ovh main → webhook auto-déploiement
Logs: logs/sync.log, logs/cron.log, logs/api.log, logs/error.log (rotation 10MB)

## CONCLUSION
Application PHP vanilla moderne, performante, maintenable. Architecture claire, zéro dépendances, synchronisation FFF automatisée, API REST simple. Prête pour production ou migration progressive vers Vue/React si besoin.
