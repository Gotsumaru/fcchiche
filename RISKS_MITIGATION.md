# ANALYSE DES RISQUES & MITIGATION

---

## 📋 MATRICE RISQUES

```
┌─────────────────────────────────────────────────────────────┐
│ PROBABILITÉ vs IMPACT (Légende: 🟢=Faible 🟡=Moyen 🔴=Élevé) │
└─────────────────────────────────────────────────────────────┘

                    Impact
                  🟢  🟡  🔴
Probabilité 🟢   [3] [6] [8]
            🟡   [2] [5] [7]
            🔴   [1] [4] [9]

1=Très faible risque   5=Risque moyen    9=Très élevé risque
```

---

## 🔴 RISQUE #1: CORS Issues (Probabilité: 🟡 | Impact: 🔴)

### Risque Level: 🟡🔴 = **#7 - ÉLEVÉ**

### Description
Les requêtes depuis React SPA (preprod.fcchiche.fr) vers API PHP peuvent être bloquées par CORS si headers mal configurés.

### Scénario
```javascript
// React SPA
fetch('https://preprod.fcchiche.fr/api/matchs')
// ❌ CORS error
// Access to XMLHttpRequest at 'https://preprod.fcchiche.fr/api/matchs'
// from origin 'https://preprod.fcchiche.fr' has been blocked by CORS policy
```

### Probabilité
- 🟡 **Moyen** (60%) - Dépend config PHP existante

### Impact
- 🔴 **Élevé** - App inutilisable sans API

### ✅ MITIGATION

**Option 1: Vérifier CORS existant (IDÉAL)**
```bash
# Vérifier headers CORS dans public/api/* ou bootstrap.php
grep -r "Access-Control-Allow" ../fcchiche/public/

# Résultat attendu:
# Access-Control-Allow-Origin: *
# Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS
# Access-Control-Allow-Headers: Content-Type, Authorization
```

**Option 2: Ajouter CORS si manquant**
```php
// public/api/bootstrap.php ou chaque endpoint
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
```

**Option 3: Proxy Vite en développement**
```javascript
// vite.config.js
server: {
  proxy: {
    '/api': {
      target: 'http://localhost:8000',
      changeOrigin: true,
    }
  }
}
```

### 🧪 Test Phase 3
```javascript
// Phase 3: Tester dès que ApiClient créé
async function testCORS() {
  const response = await fetch('/api/club');
  console.log('Headers:', response.headers);
  console.log('Status:', response.status);
  // ✓ 200 = OK
  // ❌ 0 ou 403 = CORS issue
}
```

### ⏱️ Temps résolution: **10 minutes**

---

## 🟡 RISQUE #2: Performance Bundle (Probabilité: 🟡 | Impact: 🟡)

### Risque Level: 🟡🟡 = **#5 - MOYEN**

### Description
Bundle React + dépendances (react, react-router, zustand) peut être > 100KB non-gzipped, ralentissant chargement initial.

### Scénario
```
Vanilla JS:
- index.html: 50KB
- api.js: 5KB
- Total: 55KB
- Time to Interactive: 2.5s

React:
- React + ReactDOM: 42KB
- React Router: 15KB
- Zustand: 2KB
- Code app: 20KB
- Total: 79KB non-gzipped (45KB gzipped)
- Risque: TTI > 3s ❌
```

### Probabilité
- 🟡 **Moyen** (50%) - Dépend optimisations build

### Impact
- 🟡 **Moyen** - Users attendent 1-2s supplémentaires

### ✅ MITIGATION

**Option 1: Code splitting (Automatique Vite)**
```javascript
// vite.config.js
build: {
  rollupOptions: {
    output: {
      manualChunks: {
        'react-vendor': ['react', 'react-dom'],
        'router': ['react-router-dom'],
      }
    }
  },
  chunkSizeWarningLimit: 500,
}
```

**Option 2: Lazy load routes**
```javascript
// src/App.jsx
import { Suspense, lazy } from 'react';

const Home = lazy(() => import('./pages/Home'));
const Matchs = lazy(() => import('./pages/Matchs'));

export default function App() {
  return (
    <Suspense fallback={<div>Chargement...</div>}>
      <Routes>
        <Route path="/" element={<Home />} />
        <Route path="/matchs" element={<Matchs />} />
      </Routes>
    </Suspense>
  );
}
```

**Option 3: Lighthouse audit**
```bash
# Phase 8: Tester performance
npm run build
npm run preview

# Ouvrir Chrome DevTools → Lighthouse
# ✓ TTI < 2s (idéal)
# 🟡 TTI < 3s (acceptable)
# ❌ TTI > 3s (optimiser code splitting)
```

**Option 4: Remove unused deps**
```bash
# Avant install: Vérifier nécessité Zustand
# Context API suffisant? Oui → Skip Zustand
# → -2KB bundle

# Avant install: Vérifier nécessité Axios
# Fetch API suffisant? Oui → Skip Axios
# → -12KB bundle

# Avant install: Vérifier nécessité Zod
# Validation simple? Oui → Skip Zod
# → -8KB bundle
```

### 🧪 Test Phase 2
```bash
npm run build
# Vérifier tailles:
# ✓ main.js < 50KB
# ✓ react-vendor.js < 42KB
# ✓ router.js < 15KB
```

### ⏱️ Temps résolution: **2-3 heures**

---

## 🟡 RISQUE #3: Responsive Design Regression (Probabilité: 🟡 | Impact: 🟡)

### Risque Level: 🟡🟡 = **#5 - MOYEN**

### Description
En migreant vers composants React, risque d'oublier responsive sur certains composants → site cassé sur mobile.

### Scénario
```
Avant (vanilla):
  ✓ common.css a @media queries complets
  ✓ Tous les éléments responsive

Après (React):
  ❌ MatchCard.jsx oublie @media
  ❌ Navigation cassée sur mobile
  ❌ Tables débordent sur petit écran
```

### Probabilité
- 🟡 **Moyen** (45%) - Dépend rigueur développement

### Impact
- 🟡 **Moyen** - Utilisateurs mobile perdus

### ✅ MITIGATION

**Option 1: Copier CSS existant (Priorisé)**
```bash
# Phase 2: Copier common.css directement
cp ../fcchiche/templates/common.css src/styles/common.css
cp ../fcchiche/templates/index.css src/styles/index.css

# common.css a déjà:
# ✓ @media (max-width: 768px)
# ✓ @media (max-width: 480px)
# ✓ grid auto-fit
# ✓ flex responsive
```

**Option 2: CSS modules (scoped styles)**
```css
/* src/components/MatchCard.module.css */
.card {
  padding: 1rem;
  border: 1px solid #ddd;
}

/* Responsive intégré */
@media (max-width: 768px) {
  .card {
    padding: 0.5rem;
  }
}
```

**Option 3: Testing checklist**
```
Phase 8: Tester avant production
□ Desktop (1920x1080) - ✓ OK
□ Tablet (768x1024) - ✓ OK
□ Mobile (375x667) - ✓ OK
□ Landscape (667x375) - ✓ OK
□ Très petit (320x568) - ✓ OK

Outils:
• Chrome DevTools (F12 → Device Mode)
• BrowserStack pour devices réels
```

### 🧪 Test Phase 4
```bash
# Avant Phase 5: Tester chaque composant
npm run dev
# F12 → Toggle device mode
# Tester toutes résolutions
```

### ⏱️ Temps résolution: **1-2 heures (rares cas)**

---

## 🟡 RISQUE #4: Service Worker Conflict (Probabilité: 🟢 | Impact: 🟡)

### Risque Level: 🟢🟡 = **#6 - MOYEN-FAIBLE**

### Description
Service Worker vanilla existant peut entrer en conflit avec nouveau Service Worker React PWA.

### Scénario
```
Vanilla version:
  SW cache: CACHE_NAME = 'fcchiche-v1'

React version:
  SW cache: CACHE_NAME = 'fcchiche-v1' (identique)

Risque: Stale cache = old code servi
```

### Probabilité
- 🟢 **Faible** (30%) - Géré par versioning cache

### Impact
- 🟡 **Moyen** - Users voient anciennes versions

### ✅ MITIGATION

**Option 1: Cache versioning**
```javascript
// public/service-worker.js
const CACHE_NAME = 'fcchiche-react-v1'; // NOUVEAU format
const URLS_TO_CACHE = [
  '/',
  '/index.html',
  '/manifest.json',
  // Ajouter assets React
];

// Cleanup anciens caches
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames
          .filter(name => !name.includes('fcchiche-react'))
          .map(name => caches.delete(name))
      );
    })
  );
});
```

**Option 2: Force refresh sur update**
```javascript
// src/main.jsx
// Notify users of app updates
if (navigator.serviceWorker) {
  navigator.serviceWorker.ready.then((registration) => {
    registration.onupdatefound = () => {
      const newWorker = registration.installing;
      newWorker.onstatechange = () => {
        if (newWorker.state === 'installed') {
          if (navigator.serviceWorker.controller) {
            // New version available
            alert('Nouvelle version disponible. Rechargez la page.');
          }
        }
      };
    };
  });
}
```

**Option 3: Browser DevTools for testing**
```javascript
// Chrome DevTools → Application → Service Workers
// ✓ Unregister old SW
// ✓ Clear cache storage
// ✓ Test new SW

// Or programmatically:
if (navigator.serviceWorker) {
  navigator.serviceWorker.getRegistrations().then(registrations => {
    registrations.forEach(r => r.unregister());
  });
}
```

### 🧪 Test Phase 9
```bash
# Avant Phase 10:
npm run build
npm run preview

# Chrome DevTools → Application
# ✓ Old cache 'fcchiche-v1' absent
# ✓ New cache 'fcchiche-react-v1' présent
# ✓ Network requests cached correctly
```

### ⏱️ Temps résolution: **30 minutes**

---

## 🟢 RISQUE #5: JWT Token Management (Probabilité: 🟡 | Impact: 🟡)

### Risque Level: 🟡🟡 = **#5 - MOYEN**

### Description
JWT token stocké dans localStorage (vanilla) → React Context, risque de loss en refresh page.

### Scénario
```
Vanilla:
  ✓ localStorage.token persiste
  ✓ Refresh page = token toujours là

React:
  ❌ Context state reset en refresh
  ❌ User doit se reconnecter
  ❌ UX mauvaise
```

### Probabilité
- 🟡 **Moyen** (50%) - Problème classique React

### Impact
- 🟡 **Moyen** - Users frustés (re-login)

### ✅ MITIGATION

**Option 1: Hydrate from localStorage**
```javascript
// src/context/AuthContext.jsx
import { createContext, useState, useEffect } from 'react';

export const AuthContext = createContext();

export const AuthProvider = ({ children }) => {
  const [token, setToken] = useState(() => {
    // Hydrater depuis localStorage au mount
    return localStorage.getItem('token') || null;
  });

  const login = async (email, password) => {
    const response = await api.login(email, password);
    // Persister token
    localStorage.setItem('token', response.token);
    setToken(response.token);
  };

  const logout = () => {
    localStorage.removeItem('token');
    setToken(null);
  };

  return (
    <AuthContext.Provider value={{ token, login, logout }}>
      {children}
    </AuthContext.Provider>
  );
};
```

**Option 2: Validate token on mount**
```javascript
// src/hooks/useAuth.js
import { useEffect, useState } from 'react';
import { useAuth } from '../context/AuthContext';

export function useTokenValidation() {
  const { token } = useAuth();
  const [isValid, setIsValid] = useState(true);

  useEffect(() => {
    if (!token) return;

    // Vérifier token valide avec API
    fetch('/api/auth/verify', {
      headers: { 'Authorization': `Bearer ${token}` }
    })
      .then(r => {
        if (!r.ok) throw new Error('Token invalid');
        setIsValid(true);
      })
      .catch(() => setIsValid(false));
  }, [token]);

  return isValid;
}
```

**Option 3: Refresh token rotation**
```javascript
// Optionnel: Ajouter refresh token logic
// Si backend supporte /api/auth/refresh
api.interceptors.response.use(
  response => response,
  async error => {
    if (error.response?.status === 401) {
      const newToken = await api.refreshToken();
      localStorage.setItem('token', newToken);
      // Retry original request
    }
  }
);
```

### 🧪 Test Phase 7
```bash
# Avant Phase 8:
# 1. Ouvrir devtools → Application → localStorage
# 2. Login admin (token sauvegardé)
# 3. F5 refresh page
# ✓ Toujours authentifié (pas re-login)
# ✓ Token présent dans localStorage
```

### ⏱️ Temps résolution: **1 heure**

---

## 🟢 RISQUE #6: API Timeouts (Probabilité: 🟢 | Impact: 🟡)

### Risque Level: 🟢🟡 = **#6 - MOYEN-FAIBLE**

### Description
Requêtes API lentes ou perdues → React n'affiche que loading, pas d'erreur utilisateur.

### Scénario
```
API timeout:
  ❌ No error handling
  ❌ Loading indéfini
  ❌ User voit spinner éternellement
```

### Probabilité
- 🟢 **Faible** (25%) - OVH connection fiable

### Impact
- 🟡 **Moyen** - Mauvaise UX (user pense cassé)

### ✅ MITIGATION

**Option 1: Timeout global**
```javascript
// src/services/api.js
async request(endpoint, options = {}) {
  // Timeout 10 secondes
  const controller = new AbortController();
  const timeoutId = setTimeout(
    () => controller.abort(),
    10000
  );

  try {
    const response = await fetch(url, {
      ...options,
      signal: controller.signal,
    });
    clearTimeout(timeoutId);
    return response.json();
  } catch (error) {
    if (error.name === 'AbortError') {
      throw new Error('API timeout - Ressayer');
    }
    throw error;
  }
}
```

**Option 2: Retry logic**
```javascript
async function retryFetch(endpoint, maxRetries = 3) {
  for (let i = 0; i < maxRetries; i++) {
    try {
      return await api.request(endpoint);
    } catch (error) {
      if (i === maxRetries - 1) throw error;
      // Wait before retry (exponential backoff)
      await new Promise(r => setTimeout(r, 1000 * Math.pow(2, i)));
    }
  }
}
```

**Option 3: Error UI feedback**
```javascript
// src/hooks/useApi.js
export const useApi = (apiCall, deps = []) => {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetch = async () => {
      try {
        setLoading(true);
        const result = await apiCall();
        setData(result);
        setError(null);
      } catch (err) {
        setError(err.message || 'Erreur API');
      } finally {
        setLoading(false);
      }
    };

    fetch();
  }, deps);

  return { data, loading, error };
};

// Usage:
const { data, loading, error } = useApi(() => api.getMatchs());

if (error) return <div className="error">{error}</div>;
if (loading) return <div>Chargement...</div>;
return <MatchsList matchs={data} />;
```

### 🧪 Test Phase 8
```javascript
// Simulate timeout in DevTools
// Throttle network: Chrome DevTools → Network → Slow 3G
// Vérifier:
// ✓ Spinner après 10s
// ✓ Error message après timeout
// ✓ Retry button disponible
```

### ⏱️ Temps résolution: **1-2 heures**

---

## 🔴 RISQUE #7: Type Errors at Runtime (Probabilité: 🟡 | Impact: 🟡)

### Risque Level: 🟡🟡 = **#5 - MOYEN**

### Description
Vanilla JS = pas de vérification types → API response structure change, composants crashent.

### Scénario
```javascript
// API returns:
{
  matchs: [
    { id: 1, home_team: "Team A", ... } // ✓ Expected
  ]
}

// Nouveau endpoint return:
{
  matches: [  // ❌ TYPO: matchs → matches
    { id: 1, homeTeam: "Team A", ... } // ❌ TYPO: home_team → homeTeam
  ]
}

// React component:
matchs.map(m => m.home_team) // ❌ undefined → component breaks
```

### Probabilité
- 🟡 **Moyen** (40%) - Évitable avec validation

### Impact
- 🟡 **Moyen** - Composant cassé, app partiellement broken

### ✅ MITIGATION

**Option 1: Zod validation (Recommandé)**
```javascript
// src/utils/schemas.js
import { z } from 'zod';

export const MatchSchema = z.object({
  id: z.number(),
  home_team: z.string(),
  away_team: z.string(),
  date: z.string(),
  score_home: z.number().optional(),
  score_away: z.number().optional(),
});

export const MatchListSchema = z.array(MatchSchema);

// Usage:
async function getMatchs() {
  const response = await fetch('/api/matchs');
  const data = await response.json();

  // Valider structure
  const validated = MatchListSchema.parse(data.matchs);
  return validated;
}
```

**Option 2: Simple runtime checks**
```javascript
// Pas Zod? Vérifications manuelles
function validateMatch(match) {
  if (!match.id || !match.home_team) {
    throw new Error('Invalid match structure');
  }
  return match;
}

async function getMatchs() {
  const response = await fetch('/api/matchs');
  const data = await response.json();

  return data.matchs.map(validateMatch);
}
```

**Option 3: TypeScript (Future)**
```typescript
// src/types/api.ts
export interface Match {
  id: number;
  home_team: string;
  away_team: string;
  date: string;
  score_home?: number;
  score_away?: number;
}

// Usage:
const matchs: Match[] = await api.getMatchs();
// ✓ TS error si propriété manquante
```

### 🧪 Test Phase 3
```bash
# Phase 3: Tester chaque endpoint
npm run dev

# Ouvrir DevTools → Console
console.log(response) // Vérifier structure

// Ajouter validation Zod
// ✓ API changes caught early
```

### ⏱️ Temps résolution: **2-3 heures**

---

## 🟢 RISQUE #8: OVH Deployment Issues (Probabilité: 🟢 | Impact: 🟡)

### Risque Level: 🟢🟡 = **#6 - MOYEN-FAIBLE**

### Description
Deploiement OVH FTP/Git peut avoir issues (permissions, fichiers perdus, webhook cassé).

### Scénario
```
Push sur preprod:
  ✓ Code poushed
  ❌ Webhook ne trigger pas
  ❌ Server pas mis à jour
  ❌ Old code toujours en ligne
```

### Probabilité
- 🟢 **Faible** (20%) - OVH setup existant marche

### Impact
- 🟡 **Moyen** - Déploiement échoue

### ✅ MITIGATION

**Option 1: Test webhook AVANT migration**
```bash
# Avant Phase 1:
# 1. Pousser commit trivial sur preprod
git commit --allow-empty -m "Test webhook"
git push origin preprod

# 2. Vérifier site mis à jour
# ✓ preprod.fcchiche.fr reflète change
# ❌ Si pas updaté, contacter OVH
```

**Option 2: FTP upload fallback**
```bash
# Si webhook cassé:
npm run build

# Upload dist/ via FTP
# 1. Ouvrir Filezilla/WinSCP
# 2. Connecter OVH
# 3. Upload dist/* → /home/fcchiche/public_html/preprod/
# 4. F5 refresh site
```

**Option 3: Git deployment script**
```bash
# public_html/.git/hooks/post-receive
#!/bin/bash
cd /home/fcchiche/public_html/preprod
git fetch
git reset --hard origin/preprod
npm install
npm run build
# Copier dist/ → /public_html/preprod/
cp -r dist/* .
```

**Option 4: Permissions check**
```bash
# Phase 10: Avant déployer
# Vérifier permissions OVH FTP
# ✓ Can upload files (755 permissions)
# ✓ Can create directories
# ✓ node_modules writable (si install via FTP)
```

### 🧪 Test Phase 10
```bash
# Avant final production:
git push origin preprod
# Attendre 30s
curl https://preprod.fcchiche.fr/

# ✓ HTML React markup présent
# ✓ Assets chargent (js/css)
# ✓ Pas 404 errors
```

### ⏱️ Temps résolution: **30 min - 2 heures**

---

## 🟢 RISQUE #9: Data Loss During Migration (Probabilité: 🟢 | Impact: 🔴)

### Risque Level: 🟢🔴 = **#8 - CRITIQUE (mais faible proba)**

### Description
Migration React = Code frontend uniquement, mais risque théorique de perte données si backend sync cassé.

### Probabilité
- 🟢 **Faible** (5%) - Backend 100% inchangé

### Impact
- 🔴 **CRITIQUE** - Données perdues = désastre

### ✅ MITIGATION (Obligatoire)

**Option 1: Backup BDD AVANT migration (OBLIGATOIRE)**
```bash
# Phase 0: AVANT tout changement
mysqldump -u username -p dbname > /backup/fcchiche_$(date +%Y%m%d).sql

# Vérifier backup
ls -lh /backup/fcchiche_*.sql
# Résultat attendu: ~5-10MB
```

**Option 2: Verify backend intact**
```bash
# Phase 1: Après setup Vite
# Vérifier backend inchangé
git diff main public/api/
git diff main src/

# Résultat attendu:
# No output = backend untouched ✓
```

**Option 3: Test all API endpoints**
```bash
# Phase 3: Après créer ApiClient
# Tester chaque endpoint

curl -s 'https://preprod.fcchiche.fr/api/matchs' | jq '.matchs | length'
# ✓ Retourne nombre de matchs
# ❌ Error? = BDD cassée

curl -s 'https://preprod.fcchiche.fr/api/club' | jq '.club.name'
# ✓ Retourne nom club
```

**Option 4: Rollback procedure**
```bash
# Si données perdues (Dieu nous en préserve):
# 1. Arrêter app
# 2. Restaurer BDD depuis backup
mysql -u username -p dbname < /backup/fcchiche_YYYYMMDD.sql
# 3. Redémarrer
# 4. Vérifier données intactes
```

### 🧪 Test Phase 1
```bash
# Dès le départ:
mysqldump -u username -p dbname > /backup/preprod_backup.sql

# Verify backup valide
file /backup/preprod_backup.sql
# ✓ SQL text file
```

### ⏱️ Temps résolution: **5 minutes (prévention)**

---

## ⏱️ RÉSUMÉ TIMELINE RISQUES

| Phase | Risque | Test | Mitigation |
|-------|--------|------|-----------|
| 0 (Préparation) | Data loss 🔴 | Backup BDD | 5 min |
| 1 (Setup) | - | npm run dev | - |
| 2 (Styles) | Responsive 🟡 | Device mode | 1h |
| 3 (API) | CORS 🔴, Timeouts 🟡 | curl test | 2h |
| 4 (Composants) | Type errors 🟡 | Validation | 2h |
| 5 (State) | JWT 🟡 | localStorage test | 1h |
| 6 (Router) | - | Navigation test | - |
| 7 (Auth) | JWT tokens 🟡 | Login/logout | 1h |
| 8 (Testing) | Performance 🟡 | Lighthouse | 2h |
| 9 (PWA) | SW conflict 🟡 | Cache test | 1h |
| 10 (Deploy) | OVH deploy 🟡 | Webhook test | 1h |

**Total mitigation time: ~17 heures embedded dans phases**

---

## 🚨 CHECKLIST RISQUES PRÉ-MIGRATION

```
CRITIQUE (OBLIGATOIRE):
  ☑ BDD backup créé et vérifié
  ☑ Git main branch tagged (v1.0.0 ou similaire)
  ☑ Branche preprod propre (git status = clean)

IMPORTANT:
  ☑ Webhook OVH testé (commit trivial)
  ☑ CORS headers vérifiés dans PHP
  ☑ Tous endpoints API fonctionnels
  ☑ Node.js v18+ installé
  ☑ npm v9+ installé

OPTIONNEL:
  ☑ Team notifiée du timeline
  ☑ Monitoring scripts prêts
  ☑ Communication utilisateurs (si long test)
```

---

## 📞 ESCALATION PLAN

### Si CORS error (Risque #1)
```
Level 1: Ajouter headers PHP
Level 2: Contacter OVH si server config bloquer
Level 3: Proxy Vite en dev, curl test in production
```

### Si performance < 80 (Risque #2)
```
Level 1: Code splitting automatique Vite
Level 2: Lazy load routes
Level 3: Minify CSS/JS manuellement
```

### Si responsive cassé (Risque #3)
```
Level 1: Copier common.css complet
Level 2: Test tous breakpoints (320px, 768px, 1920px)
Level 3: CSS modules scoped
```

### Si JWT tokens perdus (Risque #5)
```
Level 1: Hydrater localStorage on mount
Level 2: Valider token avec /api/auth/verify
Level 3: Ajouter refresh token logic
```

### Si API timeout (Risque #6)
```
Level 1: Ajouter timeout 10s + error message
Level 2: Retry logic exponential backoff
Level 3: Ajouter service worker offline fallback
```

---

## 🎯 ACCEPTANCE CRITERIA

Migration réussie si:

```
Performance:
  ✓ Lighthouse score ≥ 85
  ✓ Time to Interactive < 2s
  ✓ Bundle size < 60KB gzipped

Fonctionnalité:
  ✓ Tous endpoints API répondent
  ✓ Navigation fluide
  ✓ Authentification fonctionne
  ✓ CRUD matchs fonctionne

Qualité:
  ✓ Zéro erreurs console
  ✓ Responsive (320px - 1920px)
  ✓ PWA installable
  ✓ Offline mode fonctionne

Test:
  ✓ 1 semaine test preprod sans critical bug
  ✓ Load test (100+ requests simultanés)
  ✓ Cross-browser (Chrome, Firefox, Safari, Edge)
  ✓ Mobile testing (iOS Safari, Android Chrome)
```

---

## 🎉 CONCLUSION

✅ Tous les risques ont une **mitigation claire**
✅ **Aucun risque non-adressable**
✅ **Préparation détaillée = sécurité**

Prêt pour déployer? ✨
