# PHASE 1: SETUP VITE + REACT

## Temps estimé: 2 heures

---

## ✅ PRÉ-REQUIS

Avant de commencer, vérifier:

```bash
# Node.js v18+
node --version
# Résultat attendu: v18.x.x ou supérieur

# npm v9+
npm --version
# Résultat attendu: 9.x.x ou supérieur

# Git configuré
git config --list | grep user
# Doit afficher user.name et user.email
```

Si Node.js < 18:
1. Télécharger depuis https://nodejs.org (LTS)
2. Installer
3. Redémarrer terminal

---

## 🚀 ÉTAPE 1: CRÉER PROJET VITE + REACT

### 1.1 Initialiser le projet

```bash
# Dans le répertoire parent de fcchiche
cd /c/Développement

# Créer nouveau projet React avec Vite
npm create vite@latest fcchiche-react -- --template react

# Attendu:
# ✔ project name: fcchiche-react
# ✔ framework: react
# ✔ variant: react

# Entrer dans le dossier
cd fcchiche-react
```

### 1.2 Vérifier structure

```bash
# Afficher structure créée
ls -la

# Résultat attendu:
# ├── src/
# │   ├── App.jsx
# │   ├── App.css
# │   ├── main.jsx
# │   ├── index.css
# │   └── assets/
# ├── public/
# ├── package.json
# ├── vite.config.js
# ├── index.html
# └── README.md
```

---

## 📦 ÉTAPE 2: INSTALLER DÉPENDANCES

```bash
# Installer les dépendances core
npm install

# Ajouter React Router (navigation)
npm install react-router-dom@6

# Ajouter Zustand (state management optionnel)
npm install zustand

# Ajouter Zod (validation optionnel)
npm install zod

# Ajouter axios (HTTP client optionnel)
npm install axios
```

### Vérifier installation

```bash
cat package.json

# Vérifier section "dependencies":
# {
#   "react": "^18.2.0",
#   "react-dom": "^18.2.0",
#   "react-router-dom": "^6.x.x",
#   "zustand": "^4.x.x"
# }
```

---

## 🏗️ ÉTAPE 3: STRUCTURE RÉPERTOIRES

```bash
# Créer structure organisée
mkdir -p src/{components,pages,hooks,context,services,styles,utils}

# Vérifier
tree src/

# Résultat attendu:
# src/
# ├── components/     (composants réutilisables)
# ├── pages/          (pages/routes)
# ├── hooks/          (hooks React)
# ├── context/        (Context API)
# ├── services/       (API, storage, etc)
# ├── styles/         (CSS)
# ├── utils/          (utilitaires)
# ├── App.jsx
# ├── App.css
# ├── main.jsx
# └── index.css
```

---

## ⚙️ ÉTAPE 4: CONFIGURATION VITE

### 4.1 vite.config.js

```javascript
// vite.config.js
import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

export default defineConfig({
  plugins: [react()],

  // Optimisations build
  build: {
    // Code splitting
    rollupOptions: {
      output: {
        manualChunks: {
          'react-vendor': ['react', 'react-dom'],
          'router': ['react-router-dom'],
        }
      }
    },
    // Taille chunk limite
    chunkSizeWarningLimit: 500,
    // Source maps production
    sourcemap: false,
  },

  // Dev server
  server: {
    port: 5173,
    open: true,
    // Proxy API en local (optionnel)
    proxy: {
      '/api': {
        target: 'http://localhost:8000',
        changeOrigin: true,
        rewrite: (path) => path.replace(/^\/api/, '/api'),
      }
    }
  }
})
```

### 4.2 Variables environnement

```bash
# Créer .env
cat > .env << 'EOF'
VITE_API_BASE_URL=http://localhost:8000/api
VITE_APP_NAME=FC Chiché
VITE_APP_VERSION=1.0.0
EOF

# Créer .env.preprod
cat > .env.preprod << 'EOF'
VITE_API_BASE_URL=https://preprod.fcchiche.fr/api
VITE_APP_NAME=FC Chiché
VITE_APP_VERSION=1.0.0
EOF

# Créer .env.production
cat > .env.production << 'EOF'
VITE_API_BASE_URL=https://fcchiche.fr/api
VITE_APP_NAME=FC Chiché
VITE_APP_VERSION=1.0.0
EOF
```

### 4.3 .gitignore

```bash
# Vérifier .gitignore existe
cat > .gitignore << 'EOF'
node_modules/
dist/
.env.local
.env.*.local
*.log
.DS_Store
.idea/
.vscode/
*.swp
EOF
```

---

## 🎨 ÉTAPE 5: COPIER CSS DESIGN SYSTEM

### 5.1 Récupérer CSS existant

```bash
# Copier common.css (design system)
cp ../fcchiche/templates/common.css src/styles/common.css
cp ../fcchiche/templates/index.css src/styles/index.css

# Vérifier
ls -la src/styles/

# Résultat attendu:
# ├── common.css  (~2000 lignes)
# ├── index.css   (~500 lignes)
```

### 5.2 Mettre à jour main.jsx

```javascript
// src/main.jsx
import React from 'react'
import ReactDOM from 'react-dom/client'
import App from './App.jsx'

// Importer styles
import './styles/common.css'
import './styles/index.css'
import './index.css'

ReactDOM.createRoot(document.getElementById('root')).render(
  <React.StrictMode>
    <App />
  </React.StrictMode>,
)
```

---

## ✅ ÉTAPE 6: VÉRIFIER LE BUILD

### 6.1 Dev server

```bash
# Lancer le serveur développement
npm run dev

# Résultat attendu:
# ➜  Local:   http://localhost:5173/
# ➜  press h to show help

# Ouvrir http://localhost:5173 dans le navigateur
# ✓ Page Vite par défaut
# ✓ Console sans erreurs
```

### 6.2 Production build

```bash
# Construire pour production
npm run build

# Résultat attendu:
# ✓ dist/index.html       0.42 kB
# ✓ dist/assets/main.xxx.js   120.45 kB
# ✓ dist/assets/main.xxx.css   45.23 kB

# Vérifier dossier dist/
ls -la dist/

# Résultat attendu:
# ├── index.html
# ├── assets/
# │   ├── main.xxx.js
# │   ├── main.xxx.css
# │   └── react.xxx.js
```

### 6.3 Preview production

```bash
# Prévisualiser le build
npm run preview

# Résultat attendu:
# ➜  Local:   http://localhost:4173/
# ✓ Page correcte (sans HMR)
```

---

## 📝 ÉTAPE 7: INITIALISER GIT

### 7.1 Créer branche migration

```bash
# Vérifier statut git
git status

# Créer branche migration
git checkout -b feat/react-migration

# Vérifier branche active
git branch

# Résultat attendu:
# * feat/react-migration
#   main
#   preprod
```

### 7.2 Commit initial

```bash
# Ajouter tous les fichiers
git add .

# Commit initial
git commit -m "feat: initialize React + Vite project

- Setup Vite with React 18
- Configure build for preprod/production
- Install core dependencies (react-router, zustand)
- Copy CSS design system from vanilla
- Environment files configured

Phase 1 complete: Ready for API client implementation"

# Vérifier
git log --oneline -5
```

---

## 🔍 ÉTAPE 8: VALIDER PHASE 1

### Checklist:

```
✅ Phase 1 Complete si:
  ☑ Node.js v18+ installed
  ☑ npm v9+ installed
  ☑ Vite + React initialized
  ☑ Dependencies installed
  ☑ Directories created
  ☑ vite.config.js configured
  ☑ .env files created
  ☑ CSS design system copied
  ☑ npm run dev works (localhost:5173)
  ☑ npm run build works (dist/)
  ☑ npm run preview works
  ☑ Git branch created (feat/react-migration)
  ☑ Initial commit done
```

### Tests rapides:

```bash
# Vérifier Node
node -e "console.log('Node ' + process.version)"
# Attendu: Node vX.X.X

# Vérifier npm
npm -v
# Attendu: 9.x.x

# Vérifier Vite
npm list vite
# Attendu: vite@5.x.x

# Vérifier React
npm list react
# Attendu: react@18.x.x

# Vérifier React Router
npm list react-router-dom
# Attendu: react-router-dom@6.x.x
```

---

## 📊 RÉSUMÉ PHASE 1

✅ **FAIT:**
- Projet Vite + React 18 créé
- Structure répertoires organisée
- Dépendances core installées
- Configuration build optimisée
- CSS design system migré
- Environment variables configurées
- Git branch créée

🎯 **PROCHAINE ÉTAPE:**
- **Phase 2:** Migration CSS complémentaire
- **Phase 3:** Créer couche API centralisée

⏱️ **TEMPS ÉCOULÉ: ~2 heures**

---

## 🆘 TROUBLESHOOTING

### Erreur: "npm: command not found"
```bash
# Node.js n'est pas installé
# Solution: https://nodejs.org → installer LTS
```

### Erreur: "vite.config.js not found"
```bash
# Vérifier dans le bon dossier
pwd
# Doit être: /c/Développement/fcchiche-react

# Vérifier structure
ls vite.config.js
```

### Erreur: "Cannot find module '@vitejs/plugin-react'"
```bash
# node_modules corruptu
rm -rf node_modules package-lock.json
npm install
```

### Erreur: Port 5173 déjà utilisé
```bash
# Utiliser port différent
npm run dev -- --port 5174
```

### Erreur: "CSS import failed"
```bash
# Vérifier fichiers CSS existent
ls -la src/styles/
# Doit afficher: common.css, index.css

# Vérifier import dans main.jsx correct
grep "import.*css" src/main.jsx
```

---

## ✨ PROCHAINES PHASES

Une fois Phase 1 validée:

**Phase 2:** Migration CSS avancée (3h)
- Adapter variables CSS
- Intégrer dans composants
- Responsive design

**Phase 3:** Couche API (4h)
- Créer ApiClient réutilisable
- Hook useApi
- Tests endpoints

**Phases 4-10:** Voir MIGRATION_REACT_PLAN.md

---

**FIN PHASE 1** 🎉

Prêt pour Phase 2? Signaler quand ready!
