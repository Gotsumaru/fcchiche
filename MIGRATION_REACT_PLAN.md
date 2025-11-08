# PLAN DE MIGRATION REACT.JS - FC CHICHÉ

**Date:** 2025-11-08
**Branche:** preprod (fcchiche-preprod.fr)
**Risk Level:** ✅ FAIBLE (testing uniquement sur preprod)

---

## 📊 PHASE 0: AUDIT & PRÉPARATION

### État Actuel
- ✅ **Frontend:** HTML5 vanilla (1760 lignes) + CSS vanilla + JS vanilla (7 fichiers)
- ✅ **API:** 14 endpoints REST existants (GET/POST/PUT/DELETE)
- ✅ **Backend:** PHP 8.1 + MySQL (inchangé)
- ✅ **Déploiement:** Git OVH automatisé

### Points de Friction Identifiés
1. **Pas de state management** → Besoin Context API ou Zustand
2. **Pas de router** → Routes statiques PHP → React Router v6
3. **Styles CSS inline** → Variables CSS custom (réutilisables en React)
4. **Service Worker vanilla** → À adapter en React
5. **Validation manquante** → À ajouter en React (Zod/Yup)

---

## 🎯 PHASE 1: STRUCTURE REACT + BUILD

### 1.1 Initialisation Vite + React

**Pourquoi Vite?**
- Bundle ultra-rapide (200ms vs webpack 5-10s)
- HMR (Hot Module Replacement) performant
- Production tree-shaking excellent
- Compatible déploiement OVH

```bash
npm create vite@latest fcchiche-react -- --template react
cd fcchiche-react
npm install
```

### 1.2 Structure Répertoires

```
fcchiche-react/
├── src/
│   ├── components/          # Composants réutilisables
│   │   ├── Navigation.jsx
│   │   ├── Footer.jsx
│   │   ├── Card.jsx
│   │   └── ...
│   ├── pages/               # Pages principales (routes)
│   │   ├── Home.jsx
│   │   ├── Matchs.jsx
│   │   ├── Resultats.jsx
│   │   ├── Classements.jsx
│   │   ├── Contact.jsx
│   │   ├── Galerie.jsx
│   │   ├── Partenaires.jsx
│   │   ├── Equipes.jsx
│   │   └── Admin/
│   │       ├── Login.jsx
│   │       └── Dashboard.jsx
│   ├── hooks/               # Hooks React personnalisés
│   │   ├── useApi.js
│   │   ├── useAuth.js
│   │   ├── useWindowSize.js
│   │   └── ...
│   ├── context/             # State management
│   │   ├── AuthContext.jsx
│   │   ├── DataContext.jsx
│   │   └── ...
│   ├── services/            # Couche métier
│   │   ├── api.js           # Client API centralisé
│   │   ├── auth.js
│   │   └── storage.js
│   ├── styles/              # Styles CSS
│   │   ├── common.css       # Design system (copié)
│   │   ├── index.css
│   │   └── variables.css
│   ├── utils/               # Utilitaires
│   │   ├── validation.js
│   │   ├── format.js
│   │   └── ...
│   ├── App.jsx              # Component root
│   ├── App.css
│   └── main.jsx             # Entry point
├── public/
│   ├── manifest.json        # PWA manifest
│   ├── service-worker.js    # Service Worker React
│   └── images/
├── .env                      # Variables d'environnement
├── .env.production
├── vite.config.js           # Configuration Vite
└── package.json
```

### 1.3 Dependencies Core

```json
{
  "dependencies": {
    "react": "^18.2.0",
    "react-dom": "^18.2.0",
    "react-router-dom": "^6.x",
    "zustand": "^4.x",
    "zod": "^3.x",
    "axios": "^1.x"
  },
  "devDependencies": {
    "@vitejs/plugin-react": "^4.x",
    "vite": "^5.x"
  }
}
```

**Alternatives à considérer:**
- **State:** Context API (built-in) vs Zustand (minimaliste) vs Redux (complex)
- **Validation:** Zod vs Yup vs Joi
- **HTTP:** Fetch API vs Axios vs React Query

---

## 🎨 PHASE 2: STYLES & DESIGN SYSTEM

### 2.1 Migration CSS

**Actions:**
1. Copier `common.css` → `src/styles/common.css`
2. Adapter variables CSS (si nécessaire)
3. Ajouter CSS modules ou Tailwind (optionnel)

**Fichiers Source:**
- `templates/common.css` → Design system complet
- `templates/index.css` → Styles spécifiques

```css
/* src/styles/variables.css */
:root {
  --fc-green: #2d5016;
  --fc-light: #f5f5f5;
  --fc-accent: #d4af37;
  /* ... tous les tokens actuels ... */
}
```

### 2.2 Tailwind? CSS-in-JS? CSS Modules?

**Recommandation: Garder CSS vanilla + CSS Modules**
- Zéro migration CSS
- Performance identique
- Encapsulation composants

Alternative: Ajouter Tailwind progressivement post-migration

---

## 🔌 PHASE 3: COUCHE API CENTRALISÉE

### 3.1 ApiClient React réutilisable

**Fichier:** `src/services/api.js`

```javascript
// src/services/api.js
class ApiClient {
  constructor(baseURL = import.meta.env.VITE_API_BASE_URL || '/api') {
    this.baseURL = baseURL;
    this.timeout = 10000;
  }

  async request(endpoint, options = {}) {
    const url = `${this.baseURL}${endpoint}`;
    const signal = AbortSignal.timeout(this.timeout);

    const response = await fetch(url, {
      signal,
      headers: {
        'Content-Type': 'application/json',
        ...(this.token && { 'Authorization': `Bearer ${this.token}` }),
        ...options.headers,
      },
      ...options,
    });

    if (!response.ok) {
      throw new Error(`API Error: ${response.status}`);
    }

    return response.json();
  }

  // GET endpoints
  async getMatchs(params = {}) {
    const query = new URLSearchParams(params).toString();
    return this.request(`/matchs${query ? '?' + query : ''}`);
  }

  async getClassements(competitionId) {
    return this.request(`/classements?competition_id=${competitionId}`);
  }

  async getEquipes() {
    return this.request('/equipes');
  }

  async getCompetitions() {
    return this.request('/competitions');
  }

  async getClub() {
    return this.request('/club');
  }

  async getEngagements() {
    return this.request('/engagements');
  }

  async getTerrains() {
    return this.request('/terrains');
  }

  async getMembres() {
    return this.request('/membres');
  }

  async getConfig() {
    return this.request('/config');
  }

  // Auth endpoints
  async login(email, password) {
    return this.request('/auth', {
      method: 'POST',
      body: JSON.stringify({ email, password }),
    });
  }

  // CRUD Matchs (authentifiés)
  async createMatch(matchData, token) {
    this.token = token;
    return this.request('/matchs', {
      method: 'POST',
      body: JSON.stringify(matchData),
    });
  }

  async updateMatch(id, matchData, token) {
    this.token = token;
    return this.request(`/matchs/${id}`, {
      method: 'PUT',
      body: JSON.stringify(matchData),
    });
  }

  async deleteMatch(id, token) {
    this.token = token;
    return this.request(`/matchs/${id}`, {
      method: 'DELETE',
    });
  }

  setToken(token) {
    this.token = token;
  }

  clearToken() {
    this.token = null;
  }
}

export default new ApiClient();
```

### 3.2 Hook useApi personnalisé

**Fichier:** `src/hooks/useApi.js`

```javascript
import { useState, useEffect } from 'react';

export const useApi = (apiCall, deps = []) => {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    let isMounted = true;

    const fetchData = async () => {
      try {
        setLoading(true);
        const result = await apiCall();
        if (isMounted) {
          setData(result);
          setError(null);
        }
      } catch (err) {
        if (isMounted) {
          setError(err);
        }
      } finally {
        if (isMounted) {
          setLoading(false);
        }
      }
    };

    fetchData();

    return () => {
      isMounted = false;
    };
  }, deps);

  return { data, loading, error };
};
```

---

## 🏗️ PHASE 4: COMPOSANTS & PAGES

### 4.1 Composants de base

**Navigation.jsx**
```javascript
import React from 'react';
import { Link } from 'react-router-dom';
import './Navigation.css';

export const Navigation = () => {
  const [mobileMenuOpen, setMobileMenuOpen] = React.useState(false);

  return (
    <nav className="navbar">
      <div className="navbar-brand">
        <Link to="/">FC Chiché</Link>
      </div>

      <button
        className="navbar-toggle"
        onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
      >
        ☰
      </button>

      <ul className={`navbar-menu ${mobileMenuOpen ? 'active' : ''}`}>
        <li><Link to="/">Accueil</Link></li>
        <li><Link to="/matchs">Matchs</Link></li>
        <li><Link to="/resultats">Résultats</Link></li>
        <li><Link to="/classements">Classements</Link></li>
        <li><Link to="/contact">Contact</Link></li>
      </ul>
    </nav>
  );
};
```

### 4.2 Pages principales

**Pages/Home.jsx**
```javascript
import React from 'react';
import { useApi } from '../hooks/useApi';
import api from '../services/api';
import { MatchsList } from '../components/MatchsList';
import { ClassementsList } from '../components/ClassementsList';

export const Home = () => {
  const { data: club, loading: loadingClub } = useApi(() => api.getClub());
  const { data: nextMatchs } = useApi(() => api.getMatchs({ limit: 3 }));
  const { data: classements } = useApi(() => api.getClassements());

  if (loadingClub) return <div>Chargement...</div>;

  return (
    <main className="home">
      <section className="hero">
        <h1>{club?.name}</h1>
        <p>{club?.description}</p>
      </section>

      <section className="section-matchs">
        <h2>Prochains matchs</h2>
        <MatchsList matchs={nextMatchs} />
      </section>

      <section className="section-classements">
        <h2>Classements</h2>
        <ClassementsList classements={classements} />
      </section>
    </main>
  );
};
```

---

## 🔐 PHASE 5: GESTION D'ÉTAT & CONTEXTE

### 5.1 AuthContext (Context API)

**Fichier:** `src/context/AuthContext.jsx`

```javascript
import React, { createContext, useState, useCallback } from 'react';
import api from '../services/api';

export const AuthContext = createContext();

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null);
  const [token, setToken] = useState(localStorage.getItem('token'));
  const [loading, setLoading] = useState(false);

  const login = useCallback(async (email, password) => {
    setLoading(true);
    try {
      const response = await api.login(email, password);
      setToken(response.token);
      setUser(response.user);
      localStorage.setItem('token', response.token);
      api.setToken(response.token);
      return true;
    } catch (error) {
      console.error('Login failed:', error);
      return false;
    } finally {
      setLoading(false);
    }
  }, []);

  const logout = useCallback(() => {
    setToken(null);
    setUser(null);
    localStorage.removeItem('token');
    api.clearToken();
  }, []);

  const value = {
    user,
    token,
    loading,
    isAuthenticated: !!token,
    login,
    logout,
  };

  return (
    <AuthContext.Provider value={value}>
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = () => {
  const context = React.useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within AuthProvider');
  }
  return context;
};
```

### 5.2 DataContext (pour cache global)

```javascript
// src/context/DataContext.jsx
import React, { createContext, useCallback, useState } from 'react';

export const DataContext = createContext();

export const DataProvider = ({ children }) => {
  const [data, setData] = useState({
    club: null,
    equipes: null,
    competitions: null,
    matchs: null,
    classements: null,
  });

  const updateData = useCallback((key, value) => {
    setData(prev => ({
      ...prev,
      [key]: value,
    }));
  }, []);

  return (
    <DataContext.Provider value={{ data, updateData }}>
      {children}
    </DataContext.Provider>
  );
};
```

---

## 🧭 PHASE 6: ROUTAGE REACT ROUTER

### 6.1 Configuration routes

**Fichier:** `src/App.jsx`

```javascript
import React from 'react';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { AuthProvider } from './context/AuthContext';
import { DataProvider } from './context/DataContext';
import { Navigation } from './components/Navigation';
import { Footer } from './components/Footer';

// Pages
import { Home } from './pages/Home';
import { Matchs } from './pages/Matchs';
import { Resultats } from './pages/Resultats';
import { Classements } from './pages/Classements';
import { Contact } from './pages/Contact';
import { Galerie } from './pages/Galerie';
import { Partenaires } from './pages/Partenaires';
import { AdminLogin } from './pages/Admin/Login';
import { AdminDashboard } from './pages/Admin/Dashboard';
import { ProtectedRoute } from './components/ProtectedRoute';

import './App.css';

function App() {
  return (
    <BrowserRouter>
      <AuthProvider>
        <DataProvider>
          <Navigation />
          <Routes>
            <Route path="/" element={<Home />} />
            <Route path="/matchs" element={<Matchs />} />
            <Route path="/resultats" element={<Resultats />} />
            <Route path="/classements" element={<Classements />} />
            <Route path="/contact" element={<Contact />} />
            <Route path="/galerie" element={<Galerie />} />
            <Route path="/partenaires" element={<Partenaires />} />

            {/* Admin routes */}
            <Route path="/admin/login" element={<AdminLogin />} />
            <Route
              path="/admin/dashboard"
              element={
                <ProtectedRoute>
                  <AdminDashboard />
                </ProtectedRoute>
              }
            />

            {/* 404 */}
            <Route path="*" element={<Navigate to="/" />} />
          </Routes>
          <Footer />
        </DataProvider>
      </AuthProvider>
    </BrowserRouter>
  );
}

export default App;
```

### 6.2 ProtectedRoute

```javascript
// src/components/ProtectedRoute.jsx
import { Navigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

export const ProtectedRoute = ({ children }) => {
  const { isAuthenticated } = useAuth();

  if (!isAuthenticated) {
    return <Navigate to="/admin/login" />;
  }

  return children;
};
```

---

## 🔑 PHASE 7: AUTHENTIFICATION & ADMIN

### 7.1 Page Login

**Fichier:** `src/pages/Admin/Login.jsx`

```javascript
import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../../context/AuthContext';

export const AdminLogin = () => {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const navigate = useNavigate();
  const { login, loading } = useAuth();

  const handleSubmit = async (e) => {
    e.preventDefault();
    const success = await login(email, password);
    if (success) {
      navigate('/admin/dashboard');
    } else {
      setError('Email ou mot de passe incorrect');
    }
  };

  return (
    <div className="login-container">
      <form onSubmit={handleSubmit}>
        <h1>Connexion Admin</h1>

        {error && <div className="error">{error}</div>}

        <input
          type="email"
          placeholder="Email"
          value={email}
          onChange={(e) => setEmail(e.target.value)}
          required
        />

        <input
          type="password"
          placeholder="Mot de passe"
          value={password}
          onChange={(e) => setPassword(e.target.value)}
          required
        />

        <button type="submit" disabled={loading}>
          {loading ? 'Connexion...' : 'Se connecter'}
        </button>
      </form>
    </div>
  );
};
```

### 7.2 Dashboard Admin

```javascript
// src/pages/Admin/Dashboard.jsx
import React, { useState } from 'react';
import { useAuth } from '../../context/AuthContext';
import { MatchsCRUD } from '../../components/Admin/MatchsCRUD';

export const AdminDashboard = () => {
  const { user, logout } = useAuth();
  const [activeTab, setActiveTab] = useState('matchs');

  return (
    <div className="admin-dashboard">
      <header className="admin-header">
        <h1>Tableau de bord</h1>
        <div className="admin-user">
          <span>{user?.email}</span>
          <button onClick={logout}>Déconnexion</button>
        </div>
      </header>

      <nav className="admin-nav">
        <button
          className={activeTab === 'matchs' ? 'active' : ''}
          onClick={() => setActiveTab('matchs')}
        >
          Matchs
        </button>
        <button
          className={activeTab === 'config' ? 'active' : ''}
          onClick={() => setActiveTab('config')}
        >
          Configuration
        </button>
      </nav>

      <main className="admin-content">
        {activeTab === 'matchs' && <MatchsCRUD />}
        {activeTab === 'config' && <ConfigPanel />}
      </main>
    </div>
  );
};
```

---

## ✅ PHASE 8: TESTING & VALIDATION

### 8.1 Tests en preprod

```bash
# Checklist avant production
□ Tous les endpoints API testés
□ Authentification fonctionnelle
□ Navigation complète (toutes routes)
□ Responsive design (mobile/tablet/desktop)
□ Performance Lighthouse >80
□ CORS autorisé
□ PWA installable
□ Service Worker actif
□ Pas d'erreurs console
□ Images optimisées
□ Validation formulaires
□ Gestion erreurs réseau
```

### 8.2 Configuration .env

```env
# .env (local)
VITE_API_BASE_URL=http://localhost:8000/api

# .env.preprod
VITE_API_BASE_URL=https://fcchiche-preprod.fr/api

# .env.production
VITE_API_BASE_URL=https://fcchiche.fr/api
```

---

## 🔌 PHASE 9: PWA & SERVICE WORKER

### 9.1 Service Worker React

```javascript
// public/service-worker.js
const CACHE_NAME = 'fcchiche-v1';
const URLS_TO_CACHE = [
  '/',
  '/index.html',
  '/manifest.json',
  '/offline.html',
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(URLS_TO_CACHE);
    })
  );
});

self.addEventListener('fetch', (event) => {
  if (event.request.method !== 'GET') return;

  event.respondWith(
    caches.match(event.request).then((response) => {
      return response || fetch(event.request).then((response) => {
        if (!response.ok) return response;

        const clonedResponse = response.clone();
        caches.open(CACHE_NAME).then((cache) => {
          cache.put(event.request, clonedResponse);
        });
        return response;
      }).catch(() => {
        return new Response('Offline', { status: 503 });
      });
    })
  );
});
```

### 9.2 Manifest.json

```json
{
  "name": "FC Chiché",
  "short_name": "FC Chiché",
  "description": "Site officiel FC Chiché",
  "start_url": "/",
  "scope": "/",
  "display": "standalone",
  "background_color": "#ffffff",
  "theme_color": "#2d5016",
  "icons": [
    {
      "src": "/icon-192.png",
      "sizes": "192x192",
      "type": "image/png"
    },
    {
      "src": "/icon-512.png",
      "sizes": "512x512",
      "type": "image/png"
    }
  ]
}
```

---

## 🚀 PHASE 10: DÉPLOIEMENT

### 10.1 Build & Déploiement OVH

```bash
# Construire
npm run build

# Résultat: dist/
# Copier dist/ → preprod-react/

# Sur OVH:
git add dist/
git commit -m "Migration React: preprod ready"
git push origin preprod

# Webhook OVH déploie automatiquement
```

### 10.2 Stratégie Migration Progressive

**Option A: "Big Bang" (Plus rapide)**
1. Mettre en ligne React sur preprod
2. Tester 1 semaine
3. Migrer production

**Option B: Progressive (Plus sûr)**
1. Semaine 1: Home + Matchs en React (87% trafic)
2. Semaine 2: Classements + Resultats
3. Semaine 3: Admin + Pages statiques
4. Semaine 4: Production complète

### 10.3 Rollback Plan

```bash
# Si problème:
git revert <commit-hash>
git push origin preprod
# Webhook redéploie version PHP

# Garder branche `preprod-backup` avec version actuelle
git branch preprod-backup
git push origin preprod-backup
```

---

## 📊 TIMELINE ESTIMÉE

| Phase | Temps | Effort |
|-------|-------|--------|
| 1. Setup Vite + React | 2h | 🟢 Minimal |
| 2. Styles CSS | 3h | 🟢 Minimal (copie) |
| 3. API Client | 4h | 🟡 Moyen |
| 4. Composants base | 8h | 🟡 Moyen |
| 5. State management | 4h | 🟡 Moyen |
| 6. React Router | 3h | 🟡 Moyen |
| 7. Auth + Admin | 6h | 🟠 Complexe |
| 8. Testing | 4h | 🟡 Moyen |
| 9. PWA | 2h | 🟢 Minimal |
| 10. Déploiement | 1h | 🟢 Minimal |
| **TOTAL** | **~37h** | **4-5 jours** |

---

## 🔄 COMPATIBILITÉ BACKEND

**✅ AUCUNE MODIFICATION BACKEND REQUISE**

- API REST existante reste 100% identique
- JWT Bearer token inchangé
- CORS existant suffisant
- MySQL inchangé
- CRON/Sync inchangés

Les seules modifications optionnelles:
- Ajouter CORS headers si absent (recommandé)
- Compression Gzip (optionnel)
- Rate limiting (optionnel)

---

## ⚠️ RISQUES & MITIGATION

| Risque | Impact | Mitigation |
|--------|--------|-----------|
| Performance | 🟡 Moyen | Lazy loading, code splitting, Vite optimisation |
| CORS issues | 🟠 Élevé | Tester CORS immédiatement phase 3 |
| Responsive design | 🟡 Moyen | Garder CSS vanilla, tester mobile |
| Service Worker | 🟡 Moyen | Tester offline, cache invalidation |
| Authentification JWT | 🟡 Moyen | Vérifier token refresh, localStorage |
| API timeouts | 🟢 Faible | Retry logic déjà dans ApiClient |

---

## ✨ AMÉLIORATIONS FUTURES (Post-React)

Une fois React stable:

1. **Tailwind CSS** (remplacer CSS vanilla progressivement)
2. **React Query** ou **SWR** (caching avancé)
3. **TypeScript** (typage fort)
4. **Testing** (Jest, React Testing Library)
5. **Analytics** (Sentry, Plausible)
6. **Optimisations Images** (Sharp, WebP)
7. **Dark Mode** (CSS variables + Context)

---

## 📋 CHECKLIST PRÉ-MIGRATION

- [ ] Branche `preprod` propre (git status = clean)
- [ ] Backup BDD MySQL (mysqldump)
- [ ] Node.js v18+ installé (`node --version`)
- [ ] npm v9+ installé (`npm --version`)
- [ ] Git OVH webhook vérifié
- [ ] Accès OVH FTP confirmé
- [ ] Liste endpoints API documentée
- [ ] Design system CSS copié en local

---

## 🎯 OBJECTIFS DE RÉUSSITE

✅ **Succès = migration complète en 5 jours MAX**
- [ ] Tous les endpoints API fonctionnels
- [ ] Navigation fluide (React Router)
- [ ] Pas d'erreurs console
- [ ] Performance Lighthouse >85
- [ ] Tests complets sur preprod
- [ ] Zéro downtime (déploiement OVH)
- [ ] Rollback possible en <5 min

---

**Prêt à commencer? Je lance la Phase 1! 🚀**
