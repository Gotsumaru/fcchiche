# ⚽ FC Chichè - Site officiel

Gestion des matchs, équipes, classements et résultats en temps réel.

---

## 📋 Vue d'ensemble

| Aspect | Detail |
|--------|--------|
| **Frontend** | React 19 + Vite 7 |
| **Backend** | PHP 8.2 + MySQL/PDO |
| **Base de données** | OVH mutualisé |
| **APIs** | 13 endpoints REST PHP |
| **Dépôt** | GitHub (branch preprod) |
| **Déploiement** | Automatique OVH |

---

## 🚀 Démarrage rapide

### Installation

```bash
# 1. Cloner et installer
git clone https://github.com/Gotsumaru/fcchiche.git
cd fcchiche && npm install

# 2. Créer configuration locale
cp .env.local.example .env.local
# Éditer .env.local avec infos OVH:
#   DB_HOST=fcchice79.mysql.db
#   DB_NAME=fcchice79
#   DB_USER=fcchice79
#   DB_PASS=votre_password_ovh
```

### Développement

```bash
# Mode UI rapide (données mock)
npm run dev

# Mode complet (vraies données OVH + Docker)
npm run test:complete

# Builder pour production
npm run build

# Tester le build
npm run preview
```

---

## 🏗️ Architecture

### Frontend (React)
```
src/ → Vite build → public/dist/
  ├── components/      React components
  ├── pages/           Pages
  ├── hooks/           Custom hooks
  ├── api.js           Client API REST
  └── mockData.js      Données mock (dev)
```

### Backend (PHP)
```
public/api/           13 endpoints REST
  ├── matchs.php       🔴 Calendrier + résultats
  ├── equipes.php      Équipes du club
  ├── classements.php  Classements FFF
  └── [10 autres]      Config, auth, etc.

config/               Configuration
  ├── config.php       Charge secrets via .env.local
  ├── loadenv.php      Parse .env.local
  └── database.php     PDO MySQL
```

### Base de données
- **Host:** fcchice79.mysql.db (OVH)
- **Driver:** PDO MySQL
- **Tables:** pprod_matchs, pprod_equipes, pprod_classements, etc.
- **Sync:** API FFF (quotidienne)

---

## 🔐 Sécurité

### Secrets management

**Jamais en git:**
- `.env.local` (credentials BD)
- Fichiers Dockerfile (dev local)

**En git:**
- `.env.example` (template public)
- `.env.local.example` (template secrets)
- `config/config.php.example` (template)
- `config/loadenv.php` (code qui charge secrets)

### Configuration

1. **Localement:** Créer `.env.local`
   ```bash
   cp .env.local.example .env.local
   # Remplir avec infos OVH
   ```

2. **Sur OVH:** Créer `.env.local` via FTP/panel
   ```
   ENV=production
   DB_HOST=fcchice79.mysql.db
   DB_NAME=fcchice79
   DB_USER=fcchice79
   DB_PASS=votre_password
   ```

⚠️ **Important:** Mot de passe BD doit être changé sur panel OVH (ancien était en git)

---

## 📦 Déploiement OVH

### Configuration automatique (déjà faite)

OVH déploie automatiquement quand vous pushez sur GitHub:

```bash
# Développer en local
npm run dev

# Tester
npm run test:complete

# Builder
npm run build

# Pousser sur GitHub
git add src/ config/ *.md
git commit -m "feat: Description"
git push origin preprod
```

**OVH exécute automatiquement:**
1. `git pull`
2. `npm install`
3. `npm run build`
4. Publie sur https://fcchiche.fr

### Configuration manuelle `.env.local` sur OVH

```bash
# Via FTP ou panel OVH, créer .env.local avec:
ENV=production
DB_HOST=fcchice79.mysql.db
DB_NAME=fcchice79
DB_USER=fcchice79
DB_PASS=VOTRE_PASSWORD_OVH
```

### Vérifier le déploiement

```bash
# Tester les APIs
curl https://fcchiche.fr/api/matchs.php
# Doit retourner JSON avec matchs

# Ouvrir dans navigateur
https://fcchiche.fr
# Doit afficher le site avec données réelles
```

---

## 🛠️ Scripts npm

```bash
# Développement
npm run dev              # Dev server (port 5174)
npm run test:ui          # UI rapide (mock data)
npm run test:complete    # Test complet (Docker + BD OVH)

# Production
npm run build            # Build React optimisé
npm run preview          # Tester le build

# Docker (local)
npm run docker:build     # Construire image
npm run docker:up        # Démarrer conteneur
npm run docker:down      # Arrêter conteneur
npm run docker:logs      # Voir logs PHP
```

---

## 🐳 Développement local avec Docker

Tester complètement en local avec BD OVH:

```bash
# 1. Créer .env.local
cp .env.local.example .env.local
# Éditer avec infos OVH

# 2. Lancer test complet
npm run test:complete
# Lance Docker PHP + Vite React

# 3. Ouvrir navigateur
http://localhost:5174
# Vérifier que matchs apparaissent
```

**Détails complets:** Voir `DOCKER_TESTING.md`

---

## 🐛 Troubleshooting

### Erreur: "Cannot connect to database"

```bash
# Vérifier .env.local existe
ls -la .env.local

# Vérifier credentials
cat .env.local | grep DB_

# Tester localement
npm run test:complete
```

Si erreur BD:
- Vérifier mot de passe OVH (changé depuis ancien exposé en git?)
- Vérifier IP whitelistée chez OVH
- Tester connexion OVH: `nc -zv fcchice79.mysql.db 3306`

### Erreur: "Build failed"

```bash
# Voir le détail
npm run build

# Si erreurs compilation React:
# Vérifier src/*.jsx
# Vérifier imports

# Rebuilder
npm run build
```

### Site blank sur OVH

```bash
# Vérifier que public/dist/ existe sur OVH
# Vérifier .htaccess (rewrite rules)
# Vérifier logs OVH (panel)
```

### APIs ne répondent pas

```bash
# Vérifier .env.local sur OVH
# Vérifier permissions: chmod 644 config/*.php
# Vérifier logs PHP: Panel OVH
```

---

## 📂 Structure fichiers

```
fcchiche/
├── README.md                 ← Vous êtes ici
├── DOCKER_TESTING.md         ← Guide Docker local
├── DEPLOYMENT_GUIDE.md       ← Guide OVH (si déploiement manuel)
│
├── src/                      React sources
├── config/                   Configuration PHP
├── public/api/               APIs REST PHP
├── public/assets/            Images + CSS
│
├── package.json              Dépendances npm
├── vite.config.js            Config Vite
├── index.html                Template React
│
├── .env.example              Template vars publiques
├── .env.local.example        Template secrets
├── .gitignore                Fichiers ignorés git
│
├── Dockerfile                PHP-Apache (dev local)
├── docker-compose.yml        Orchestration Docker
└── docker/                   Config Docker
```

**NE PAS en git:**
- `.env.local` (secrets)
- `public/dist/` (build généré)
- `node_modules/` (dépendances)

---

## 📊 Performance

| Métrique | Taille |
|----------|--------|
| Build React | ~50 KB (gzipped) |
| Images | 150 MB (WebP) |
| APIs | <1 KB par requête |
| DB queries | <100ms (OVH) |

**Optimisations:**
- ✅ Images WebP (compression)
- ✅ JavaScript minifié + split chunks
- ✅ CSS minifié
- ✅ Gzip enabled (Apache)

---

## 🔄 Workflow développement

```
1. Développer
   npm run dev  (ou npm run test:complete)

2. Tester
   npm run preview
   F12 → Network → vérifier APIs

3. Commiter
   git add src/ config/ package.json *.md
   git commit -m "feat: ..."
   git push origin preprod

4. OVH déploie automatiquement (2-5 min)

5. Vérifier
   curl https://fcchiche.fr/api/matchs.php
   https://fcchiche.fr
```

---

## 🎯 Points clés

✅ **Secrets jamais en git** → Chargés via `.env.local`
✅ **Build local uniquement** → `npm run build`
✅ **OVH déploie auto** → Push sur GitHub = deployment
✅ **Test local complet** → Docker + BD OVH
✅ **13 APIs PHP actives** → JSON REST
✅ **React 19 + Vite** → Dev rapide

---

## 📚 Documentation

| Fichier | Contenu | Durée |
|---------|---------|-------|
| **README.md** | Vue d'ensemble (vous êtes ici) | 10 min |
| **DOCKER_TESTING.md** | Tester en local avec Docker | 10 min |
| **DEPLOYMENT_GUIDE.md** | Guide OVH (optionnel) | 5 min |

---

## 🆘 Besoin d'aide?

- **Dev local:** Voir `DOCKER_TESTING.md`
- **OVH déploiement:** Voir `DEPLOYMENT_GUIDE.md`
- **Code:** Voir structure ci-dessus
- **Erreurs:** Voir Troubleshooting

---

## 📈 Prochaines étapes

```bash
# 1. Installer
npm install

# 2. Créer .env.local
cp .env.local.example .env.local

# 3. Tester en local
npm run test:complete

# 4. Développer
npm run dev

# 5. Push → OVH déploie automatiquement
git push origin preprod
```

---

**Projet prêt pour production!** 🚀

Créé: 2025-11-12 | Version: 1.0
