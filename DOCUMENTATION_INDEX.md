# Index de Documentation - FC Chiché

## Documents d'Analyse

### 1. **QUICK_SUMMARY.txt** ⚡
**Résumé rapide et concis** (lecture: 5 minutes)
- Structure globale
- Frontend/CSS/JS
- API endpoints
- Dépendances
- Points forts/à améliorer
- **Meilleur pour**: Avoir une vue d'ensemble rapide

### 2. **ARCHITECTURE_ANALYSIS.md** 📐
**Analyse détaillée complète** (lecture: 20 minutes)
- Exploration fichier par fichier
- Détails API endpoints
- Structure backend PHP
- Models et synchronisation
- Base de données complète
- Déploiement et opérations
- **Meilleur pour**: Comprendre l'architecture en profondeur

## Documentation Existante du Projet

### 3. **README.md** 📖
**Guide d'installation et déploiement**
- Prérequis
- Installation BDD
- Configuration application
- Permissions fichiers
- Configuration CRON
- Tests manuels
- Déploiement OVH
- **À consulter pour**: Mettre en place l'environnement

### 4. **REVIEW.md** 🔍
**Audit conformité au code style (NASA Power of 10)**
- Points critiques identifiés
- Assertions insuffisantes
- Fonctions trop longues
- Requêtes SQL non préparées
- Validations manquantes
- **À consulter pour**: Comprendre les améliorations nécessaires

### 5. **sql/README.md**
Documentation du schéma BDD

### 6. **src/README.md**
Documentation structure src/

### 7. **public/README.md**
Documentation structure public/

### 8. **public/api/README.md**
Documentation détaillée API endpoints

### 9. **public/api/openapi.yaml** 📋
Specification OpenAPI 3.0 complète (machine-readable)

## Fichiers de Configuration

- **config/config.php** : Configuration application (BDD, API FFF, env)
- **.htaccess** : Rewrite rules (racine et /api)
- **public/manifest.json** : PWA manifest

## Structure des Répertoires

```
fcchiche-refonte/
├── DOCUMENTATION_INDEX.md      ← Vous êtes ici
├── QUICK_SUMMARY.txt           ⚡ Résumé 5 min
├── ARCHITECTURE_ANALYSIS.md    📐 Analyse 20 min
├── README.md                   📖 Installation
├── REVIEW.md                   🔍 Audit code
├── config/                     Configuration
├── src/                        Code métier
├── public/                     Web + API
├── cron/                       Synchronisation CRON
├── sql/                        Schéma BDD
└── templates/                  Templates HTML
```

## Flux de Lecture Recommandé

### Pour Nouveaux Développeurs
1. **QUICK_SUMMARY.txt** - Comprendre la structure
2. **ARCHITECTURE_ANALYSIS.md** - Détails techniques
3. **README.md** - Mettre en place localement
4. **public/api/README.md** - Endpoints API

### Pour Code Review / Maintenance
1. **REVIEW.md** - Points d'amélioration
2. **src/README.md** - Structure code
3. **Fichiers PHP** - Inspection détaillée

### Pour Opérations / DevOps
1. **README.md** - Déploiement
2. **config/config.php** - Configuration
3. **.htaccess** - Rewrite rules

### Pour Intégration Frontend
1. **public/api/README.md** - Endpoints disponibles
2. **public/api/openapi.yaml** - Spec complète
3. **public/api/docs.html** - Documentation manuelle

## Clés Techniques

### Stack Tech
- Backend: PHP 8.1 + PDO + MySQL
- Frontend: Vanilla JS + CSS (zéro frameworks)
- Sync: CRON automatisé 2x/jour
- Déploiement: OVH mutualisé + Git webhook

### API Principales
- 11 endpoints lecture (public)
- 3 endpoints écriture (authentifiés)
- Format: JSON + CORS
- Authentification: JWT Bearer

### Base de Données
- 11 tables normalisées (3NF)
- Prefix: pprod_
- Charset: utf8mb4
- Connection: PDO with prepared statements

## Contacts et Support

- Endpoint API: https://api-dofa.fff.fr/api
- OVH: Auto-déploiement sur push main
- CRON: 0 8,20 * * * (2x/jour)
- Logs: logs/ directory (rotation 10MB)

---

**Généré**: Analyse automatisée du projet
**Date**: 8 novembre 2025
**Version**: 1.0.0 - Phase 1 Backend
