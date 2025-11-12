# ⚽ FC Chichè - Architecture & Déploiement

Site officiel du FC Chichè avec gestion des matchs, équipes, classements et résultats.

---

## 📋 Vue d'ensemble

| Aspect | Detail |
|--------|--------|
| **Frontend** | React 19 + Vite 7 |
| **Backend** | PHP 8.2 + PDO MySQL |
| **Base de données** | MySQL/MariaDB sur OVH |
| **APIs** | REST PHP (~13 endpoints actifs) |
| **Hébergement** | OVH mutualisé |
| **Dépôt** | GitHub (preprod branch) |

---

## 🗂️ Structure du projet

```
fcchiche/
│
├── 📁 public/                    # Répertoire public (servi par Apache)
│   ├── dist/                     # Build Vite React (NE PAS ÉDITER)
│   │   ├── index.html            # Entry point compilé
│   │   └── assets/               # JS/CSS minifiés
│   ├── api/                      # APIs PHP (13 endpoints actifs)
│   │   ├── matchs.php            # 🔴 CRITIQUE: Matchs + résultats
│   │   ├── equipes.php           # Équipes du club
│   │   ├── classements.php       # Classements FFF
│   │   ├── club.php              # Infos club
│   │   ├── auth.php              # Authentification
│   │   └── [10 autres endpoints] # Autres APIs
│   ├── assets/                   # Images + CSS (~200 MB)
│   │   ├── images/               # 190+ images webp optimisées
│   │   ├── css/                  # Styles (common.css, index.css)
│   │   └── docs/                 # PDFs et ressources
│   └── .htaccess                 # Rewrite rules Apache
│
├── 📁 src/                       # Code source React & utils
│   ├── components/               # Composants React
│   │   ├── Header.jsx            # En-tête
│   │   ├── Footer.jsx            # Pied de page
│   │   ├── MatchCard.jsx         # Affichage matchs à venir
│   │   └── ResultCard.jsx        # Affichage résultats
│   ├── pages/                    # Pages React
│   │   └── HomePage.jsx          # Page d'accueil principale
│   ├── hooks/                    # Custom hooks React
│   │   └── useHorizontalScroll.js # Scroll horizontal
│   ├── api.js                    # Client API (appels REST)
│   ├── mockData.js               # Données mock (development)
│   ├── reveal.js                 # Animations au scroll
│   ├── App.jsx                   # Root component
│   ├── main.jsx                  # Entry point React
│   ├── index.css, pages.css      # Styles spécifiques
│   │
│   └── [Legacy PHP sources]      # Sources PHP historiques (ref)
│       ├── API/                  # Client API FFF
│       ├── Models/               # 14 modèles métier
│       ├── Database/             # Synchronisation
│       └── Utils/                # Utilitaires
│
├── 📁 config/                    # Configuration (CRITIQUE)
│   ├── config.php                # Configuration app (charge secrets via getenv())
│   ├── config.php.example        # Template sans secrets
│   ├── loadenv.php               # ⭐ IMPORTANT: Charge .env.local
│   ├── database.php              # Configuration base de données
│   ├── bootstrap.php             # Initialisation app
│   └── generate_password_hash.php # Utilitaire
│
├── 📁 cron/                      # Tâches planifiées
│   ├── sync-fff.php              # Synchronisation quotidienne FFF
│   └── [autres tâches]
│
├── 📄 index.html                 # Template Vite React
├── 📄 vite.config.js             # Configuration Vite (proxy, build, etc.)
├── 📄 package.json               # Dépendances npm + scripts
│
├── 📄 .gitignore                 # Fichiers à ignorer en git
├── 📄 .env.example               # Template vars publiques
├── 📄 .env.local.example         # Template secrets (jamais le vrai)
│
└── 📚 Documentation
    ├── README.md                 # Ce fichier
    ├── DEPLOYMENT_GUIDE.md       # Guide complet OVH
    ├── DEPLOY_SCRIPT.md          # Script de déploiement
    ├── BUILD_STRATEGY.md         # Stratégie build (local vs OVH)
    └── [Guides développement]
```

---

## 🔍 Explication architecture

### Frontend (React)
- **Sources:** `src/` → `public/dist/` (généré par Vite)
- **Bundling:** Vite 7 (ultra-rapide)
- **Optimisation:** Terser (minification JS), PostCSS (CSS)
- **Déploiement:** Apache sert `public/dist/index.html`
- **APIs:** Client JS (`src/api.js`) appelle `/api/matchs.php` etc.

**Flux React:**
```
HomePage.jsx
  → useEffect() appelle apiClient.getMatchs()
    → fetch('/api/matchs.php?upcoming=6')
      → Vite proxy vers localhost:8080 (en dev)
      → ou vers https://fcchiche.fr/api/matchs.php (prod)
        → PHP exécute matchs.php
          → Database::getInstance()
            → MatchsModel::getUpcomingMatches()
              → SELECT FROM pprod_matchs
                → JSON retourné
  → setState + re-render
```

### Backend (PHP)
- **Sources:** `config/` + `src/` + `public/api/`
- **Base de données:** MySQL/MariaDB (OVH) avec PDO
- **APIs:** 13 endpoints PHP retournant JSON
- **Sécurité:**
  - PDO prepared statements (SQL injection prevention)
  - GET = public, POST/PUT/DELETE = authentifiée
  - CORS headers automatiques
  - Secrets chargés via `config/loadenv.php` depuis `.env.local`

**Flux API PHP:**
```
Requête → /api/matchs.php?upcoming=6
         → ApiAuth::checkCors()
         → handleGet()
         → MatchsModel::getUpcomingMatches(6)
         → Database::getInstance()->prepare()
         → MySQL sur OVH
         → enrichMatchData() (ajoute noms clubs, etc.)
         → ApiResponse::success($data)
         → JSON
```

### Configuration (CRITIQUE)
- **`config/config.php`** : Charge secrets via `getenv()`
- **`config/loadenv.php`** : Charge `.env.local` en dev
- **`.env.local`** : Secrets JAMAIS en git
  ```
  ENV=production
  DB_HOST=fcchice79.mysql.db
  DB_NAME=fcchice79
  DB_USER=fcchice79
  DB_PASS=UR_REAL_PASSWORD_HERE
  ```

---

## 🛠️ Développement local

### Installation

```bash
# 1. Cloner le dépôt
git clone https://github.com/Gotsumaru/fcchiche.git
cd fcchiche

# 2. Installer dépendances
npm install

# 3. Créer configuration locale
cp .env.local.example .env.local
# Éditer .env.local avec vos infos OVH

# 4. Démarrer en développement
npm run dev
# Visite http://localhost:5174
```

### Modes de développement

#### Mode 1: UI rapide (avec mock data)
```bash
npm run test:ui
# Données fictives, pas besoin de BD
# Idéal pour développer l'UI rapidement
```

#### Mode 2: Test complet (avec vraies données OVH)
Nécessite Docker installé:

```bash
npm run test:complete
# Démarre Docker (serveur PHP local)
# Se connecte à la BD OVH
# Données réelles
```

**Détails:** Voir `DOCKER_TESTING.md`

### Build pour production

```bash
# Générer le build optimisé
npm run build

# Résultat dans public/dist/ (197 MB)
# Contient: HTML, JS, CSS minifiés

# Tester le build en local
npm run preview
# Visite http://localhost:4173
```

---

## 🚀 Déploiement sur OVH

### Architecture déploiement

```
Git (GitHub)              Développement & Versioning
  ↓ (code source uniquement)

OVH Serveur              Production
  ├── public/dist/       ← Généré localement, copié via FTP
  ├── public/api/        ← Reste identique (APIs PHP)
  ├── public/assets/     ← Reste identique (images)
  ├── config/            ← Reste identique (config)
  ├── cron/              ← Reste identique (jobs)
  └── .env.local         ← Créé manuellement (secrets)
```

### Processus déploiement

#### Étape 1️⃣ : Préparation locale

```bash
# 1. Développer, tester
npm run test:complete

# 2. Builder quand ready
npm run build

# 3. Tester le build
npm run preview

# 4. Commiter les sources (PAS public/dist/)
git add src/ config/ package.json vite.config.js .gitignore *.md
git commit -m "feat: Description du changement"
git push origin preprod
```

#### Étape 2️⃣ : Copier sur OVH

Deux options :

**Option A - Script Python (Recommandé)**
```bash
python3 deploy.py
# Copie automatiquement:
# - public/dist/ (React build)
# - public/api/ (APIs)
# - config/ (configuration)
# Sur OVH via SFTP
```

**Option B - Manuel FTP**
```
1. Connecter FTP: ftp://fcchiche.fr
2. Copier public/dist/ → /public/dist/ (remplacer)
3. Vérifier que public/api/ est intact
```

#### Étape 3️⃣ : Configurer secrets OVH

Via FTP, créer `/`.env.local`:
```
ENV=production
DB_HOST=fcchice79.mysql.db
DB_NAME=fcchice79
DB_USER=fcchice79
DB_PASS=YOUR_NEW_OVH_PASSWORD
```

**⚠️ IMPORTANT:** Mot de passe OVH DOIT être changé (il était exposé en git)

#### Étape 4️⃣ : Vérifier

```bash
# Tester les APIs
curl https://fcchiche.fr/api/matchs.php
# Doit retourner JSON avec matchs

# Tester le site
https://fcchiche.fr
# Doit afficher la page avec données réelles
```

---

## 📄 Scripts npm

```bash
# Développement
npm run dev              # Démarre Vite dev server (port 5174)

# Tests
npm run test:ui          # Test UI rapide (mock data)
npm run test:complete    # Test complet (Docker + BD OVH)

# Production
npm run build            # Builder React (public/dist/)
npm run preview          # Tester le build local

# Docker (développement)
npm run docker:build     # Construire image PHP
npm run docker:up        # Démarrer conteneur
npm run docker:down      # Arrêter conteneur
npm run docker:logs      # Voir logs PHP
```

---

## 🔐 Sécurité

### Secrets management

**❌ Jamais en git:**
- `.env.local` (credentials BD)
- `Dockerfile`, `docker-compose.yml` (dev local)
- `.env.development` (config dev)

**✅ Toujours en git:**
- `.env.example` (template public)
- `.env.local.example` (template secrets)
- `config/config.php.example` (template sans password)
- `config/loadenv.php` (code qui charge les secrets)

### Stratégie secrets

1. **Développement local:**
   - Créer `.env.local` avec infos OVH
   - Jamais commiter
   - `.gitignore` le protège

2. **Production OVH:**
   - Créer `.env.local` sur serveur (FTP)
   - Ou utiliser variables panel OVH
   - Changer mot de passe de la BD (ancien était en git)

---

## 📊 Tailles & Performance

| Élément | Taille | Notes |
|---------|--------|-------|
| **Sources git** | ~450 MB | Incluant node_modules* |
| **Build React** | 197 MB | public/dist/ (généré localement) |
| **Assets images** | 150 MB | WebP optimisées |
| **API PHP** | 116 KB | 13 endpoints actifs |
| **OVH final** | ~250 MB | Sans node_modules, sources React |

*node_modules ignorés par git

### Optimisations

- ✅ Images WebP (compression automatique)
- ✅ CSS minifié (Terser)
- ✅ JS minifié + split chunks
- ✅ HTML minifié
- ✅ Gzip enabled (Apache)

---

## 🐛 Troubleshooting

### Erreur: "Cannot connect to database"

**Vérifier:**
1. `.env.local` existe avec bon password?
2. Mot de passe OVH a été changé? (ancien était en git)
3. IP en whitelist chez OVH?

```bash
# Tester connexion
npm run test:complete
```

### Erreur: "Build succeeded but site is blank"

**Vérifier:**
1. `public/dist/index.html` existe?
2. Apache `.htaccess` réécrire les routes?
3. Logs OVH pour erreurs PHP

```bash
# Tester localement
npm run preview
```

### Site fonctionne mais APIs ne répondent pas

**Vérifier:**
1. `/public/api/matchs.php` existe sur OVH?
2. `config/config.php` peut charger `.env.local`?
3. Permissions fichiers: `chmod 644 config/*.php`

```bash
# Tester l'API
curl https://fcchiche.fr/api/config.php
```

---

## 📚 Guides & Documentation

Tous les guides sont à la racine du projet:

| Fichier | Contenu |
|---------|---------|
| **DEPLOYMENT_GUIDE.md** | Guide complet OVH (credentials, variables, déploiement) |
| **BUILD_STRATEGY.md** | Pourquoi builder localement vs OVH |
| **DOCKER_TESTING.md** | Comment tester avec Docker en local |
| **GIT_DEPLOYMENT_CHECKLIST.md** | Checklist avant de pousser en git |
| **DEPLOY_SCRIPT.md** | Script automatisé de déploiement |
| **CLEANUP_FILES.md** | Quoi supprimer/ignorer du repo |

---

## 🎯 Quick Start (5 min)

```bash
# 1. Clone et install
git clone https://github.com/Gotsumaru/fcchiche.git && cd fcchiche && npm install

# 2. Config locale
cp .env.local.example .env.local
# Éditer avec vos infos OVH

# 3. Tester
npm run test:ui        # UI rapide
# ou
npm run test:complete  # Avec vraies données

# 4. Développer
npm run dev

# 5. Quand ready, builder
npm run build

# 6. Commiter
git add src/ config/ package.json *.md
git commit -m "feat: ..."
git push origin preprod

# 7. Déployer sur OVH (voir DEPLOYMENT_GUIDE.md)
```

---

## 👥 Equipe

- **Développement:** React + PHP vanilla
- **Infrastructure:** OVH mutualisé + GitHub
- **APIs:** REST PHP + MySQL

---

## 📄 License

Propriétaire FC Chichè 2025

---

## 📞 Support

- Erreurs build: Voir `npm run build` output
- Erreurs APIs: Voir `DEPLOYMENT_GUIDE.md`
- Erreurs git: Voir `GIT_DEPLOYMENT_CHECKLIST.md`
- Tests locaux: Voir `DOCKER_TESTING.md`

---

**Projet prêt pour production!** 🚀
