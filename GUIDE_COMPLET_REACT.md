# 📚 GUIDE COMPLET - REACT + BACKEND + DÉPLOIEMENT

**Pour les débutants React**
Date: 2025-11-08

---

## 🎯 TABLE DES MATIÈRES

1. [Comprendre React](#1-comprendre-react)
2. [Architecture du Projet](#2-architecture-du-projet)
3. [Comment fonctionne le Backend](#3-comment-fonctionne-le-backend)
4. [Guide d'Utilisation Quotidienne](#4-guide-dutilisation-quotidienne)
5. [Git et Versioning](#5-git-et-versioning)
6. [Déploiement sur OVH](#6-déploiement-sur-ovh)
7. [Troubleshooting](#7-troubleshooting)

---

## 1. COMPRENDRE REACT

### Qu'est-ce que React?

**React = Bibliothèque JavaScript pour créer des interfaces**

Au lieu d'écrire du HTML statique, vous écrivez du **code JavaScript qui génère du HTML dynamiquement**.

#### Exemple simple:

**Avant (Vanilla JS):**
```html
<!-- HTML statique -->
<div id="app">
  <h1>Bonjour</h1>
  <p>Mon nom est John</p>
</div>

<script>
  // Logique en JavaScript (séparé)
  document.getElementById('app').innerHTML = '...';
</script>
```

**Après (React):**
```jsx
// JavaScript ET HTML ensemble = JSX
function App() {
  const nom = "John";

  return (
    <div>
      <h1>Bonjour</h1>
      <p>Mon nom est {nom}</p>
    </div>
  );
}
```

### Concepts clés

#### 1. **Composants** (réutilisables)
```jsx
// Un composant = une fonction qui retourne du JSX
function MatchCard({ match }) {
  return (
    <div className="card">
      <h2>{match.home_team} vs {match.away_team}</h2>
      <p>Score: {match.score_home} - {match.score_away}</p>
    </div>
  );
}

// Utiliser le composant:
<MatchCard match={match1} />
<MatchCard match={match2} />  // Réutilisable!
```

#### 2. **Props** (paramètres)
```jsx
function Greeting({ name, age }) {
  return <p>{name} a {age} ans</p>;
}

// Utilisation:
<Greeting name="Alice" age={25} />
```

#### 3. **State** (état = données changeantes)
```jsx
import { useState } from 'react';

function Counter() {
  const [count, setCount] = useState(0);  // state = 0

  return (
    <div>
      <p>Compteur: {count}</p>
      <button onClick={() => setCount(count + 1)}>
        Incrémenter
      </button>
    </div>
  );
}
```

#### 4. **Hooks** (fonctions spéciales React)
```jsx
import { useState, useEffect } from 'react';

function UsersList() {
  const [users, setUsers] = useState([]);  // useState = state
  const [loading, setLoading] = useState(true);

  useEffect(() => {  // useEffect = quand charger les données
    fetch('/api/users')
      .then(r => r.json())
      .then(data => {
        setUsers(data);
        setLoading(false);
      });
  }, []);  // [] = s'exécute une seule fois (au démarrage)

  if (loading) return <p>Chargement...</p>;

  return (
    <ul>
      {users.map(u => <li key={u.id}>{u.name}</li>)}
    </ul>
  );
}
```

### Flux de données React

```
┌──────────────────────────────────────────┐
│  USER INTERACTION                        │
│  (Clic bouton, entrée form, etc)        │
└──────────────────────┬────────────────────┘
                       │
                       ▼
        ┌──────────────────────────┐
        │ Event Handler            │
        │ (onClick, onChange, etc) │
        └──────────────┬───────────┘
                       │
                       ▼
        ┌──────────────────────────┐
        │ Update State (setState)  │
        │ setCount(count + 1)      │
        └──────────────┬───────────┘
                       │
                       ▼
        ┌──────────────────────────┐
        │ Re-render Component      │
        │ (fonction appelée à new) │
        └──────────────┬───────────┘
                       │
                       ▼
        ┌──────────────────────────┐
        │ Return new JSX           │
        │ (avec nouveau HTML)      │
        └──────────────┬───────────┘
                       │
                       ▼
    ┌────────────────────────────────┐
    │ React met à jour le DOM        │
    │ (seulement les parties changées) │
    └────────────────────────────────┘
```

### Cycle de vie simplifié

```
┌─────────────────────────────────────────┐
│ COMPOSANT NAÎT                          │
│ (Component mounted)                     │
└────────────┬────────────────────────────┘
             │
             ▼
    ┌────────────────────┐
    │ useEffect() exécuté │ ← Charger données API
    │ (une seule fois)   │
    └────────┬───────────┘
             │
             ▼
    ┌────────────────────┐
    │ User Interacts     │ ← Clic bouton, saisie
    │ setState() appelé  │
    └────────┬───────────┘
             │
             ▼
    ┌────────────────────┐
    │ Re-render         │
    │ (nouveau HTML)     │
    └────────┬───────────┘
             │
             ▼
    ┌────────────────────┐
    │ DOM Updated        │
    │ (page met à jour)  │
    └────────────────────┘
```

---

## 2. ARCHITECTURE DU PROJET

### Structure complète

```
fcchiche-react/
│
├── src/                          ← Votre code source
│   │
│   ├── components/               ← Composants réutilisables
│   │   ├── Navigation.jsx        (barre de navigation)
│   │   ├── Footer.jsx            (pied de page)
│   │   ├── MatchCard.jsx         (carte match)
│   │   └── ProtectedRoute.jsx    (route protégée)
│   │
│   ├── pages/                    ← Pages (routes)
│   │   ├── Home.jsx              (accueil /)
│   │   ├── Matchs.jsx            (/matchs)
│   │   ├── Resultats.jsx         (/resultats)
│   │   ├── Classements.jsx       (/classements)
│   │   └── Admin/
│   │       ├── Login.jsx         (/admin/login)
│   │       └── Dashboard.jsx     (/admin/dashboard)
│   │
│   ├── hooks/                    ← Hooks personnalisés
│   │   ├── useApi.js             (Appels API)
│   │   └── useAuth.js            (Authentification)
│   │
│   ├── context/                  ← State Management global
│   │   ├── AuthContext.jsx       (Auth globale)
│   │   └── DataContext.jsx       (Données globales)
│   │
│   ├── services/                 ← Services (logique métier)
│   │   └── api.js                (Client API)
│   │
│   ├── styles/                   ← Fichiers CSS
│   │   ├── variables.css         (couleurs, espaces)
│   │   ├── common.css            (styles globaux)
│   │   └── index.css             (styles page)
│   │
│   ├── App.jsx                   ← Composant racine
│   ├── App.css
│   ├── main.jsx                  ← Point d'entrée
│   └── index.css
│
├── public/                       ← Assets statiques
│   └── vite.svg
│
├── package.json                  ← Dépendances npm
├── vite.config.js               ← Config build Vite
├── .env                         ← Variables environnement (local)
├── .env.preprod                 ← Variables preprod
├── .env.production              ← Variables production
└── README.md
```

### Flux de communication

```
┌─────────────────────────────────────────────────────────────┐
│                    NAVIGATEUR (Frontend)                     │
│                                                               │
│  ┌──────────────────────────────────────────────────────┐   │
│  │ React Application (SPA = Single Page App)           │   │
│  │                                                       │   │
│  │  App.jsx (racine)                                   │   │
│  │  ├── Navigation (composant)                         │   │
│  │  ├── Pages (Matchs, Resultats, Classements)       │   │
│  │  ├── AuthContext (état global auth)               │   │
│  │  └── DataContext (état global data)               │   │
│  │                                                       │   │
│  └──────────────────────────────────────────────────────┘   │
│                       │                                       │
│                       ▼ (Appels API)                          │
│  ┌──────────────────────────────────────────────────────┐   │
│  │ Services/api.js (Client API)                        │   │
│  │ - getMatchs()                                       │   │
│  │ - getClassements()                                  │   │
│  │ - login()                                           │   │
│  │ - etc...                                            │   │
│  └──────────────────────────────────────────────────────┘   │
│                       │                                       │
└───────────────────────┼───────────────────────────────────────┘
                        │
        ┌───────────────┴──────────────┐
        │                              │
        ▼ (requête HTTP GET/POST)      ▼ (réponse JSON)
┌──────────────────────────────────────────────────────────┐
│                    SERVEUR (Backend)                      │
│                                                           │
│  Backend PHP (public/api/*.php)                         │
│  ├── /api/matchs.php         (GET matchs)              │
│  ├── /api/classements.php    (GET classements)         │
│  ├── /api/equipes.php        (GET équipes)             │
│  ├── /api/auth.php           (POST login)              │
│  └── ... (11 autres endpoints)                         │
│                                                           │
│  Models PHP (src/Models/)                               │
│  ├── MatchsModel             (logique matchs)           │
│  ├── ClassementsModel        (logique classements)      │
│  └── ...                                                 │
│                                                           │
│  Database PHP (MySQL)                                   │
│  ├── pprod_matchs            (table matchs)             │
│  ├── pprod_classements       (table classements)        │
│  ├── pprod_equipes           (table équipes)            │
│  └── ... (11 autres tables)                            │
│                                                           │
└──────────────────────────────────────────────────────────┘
```

---

## 3. COMMENT FONCTIONNE LE BACKEND

### Architecture Backend existante

Votre backend PHP **EST DÉJÀ COMPLET** et ne change pas!

```
Backend PHP Structure:
├── public/                       ← Racine web
│   └── api/                     ← Endpoints API
│       ├── matchs.php           ← GET /api/matchs
│       ├── classements.php      ← GET /api/classements
│       ├── equipes.php          ← GET /api/equipes
│       ├── auth.php             ← POST /api/auth (login)
│       ├── competitions.php     ← GET /api/competitions
│       ├── club.php             ← GET /api/club
│       └── ... (8 autres)
│
├── src/                         ← Logique métier
│   ├── Models/                  ← Classes pour chaque table
│   │   ├── MatchsModel.php
│   │   ├── ClassementsModel.php
│   │   ├── EquipesModel.php
│   │   └── ... (11 modèles)
│   │
│   ├── API/                     ← Logique API
│   │   ├── ApiAuth.php          (authentification)
│   │   ├── ApiResponse.php      (réponses standardisées)
│   │   └── FFFApiClient.php     (client API FFF)
│   │
│   └── Database/                ← Accès base de données
│       ├── Connection.php       (connexion MySQL)
│       └── Sync.php             (synchronisation FFF)
│
├── config/                      ← Configuration
│   ├── database.php             (credentials MySQL)
│   └── environment.php
│
├── cron/                        ← Tâches planifiées
│   └── sync_data.php            (sync FFF 2x/jour)
│
└── sql/                         ← Schéma BDD
    └── schema.sql               (structure tables)
```

### Flux d'une requête API

Exemple: Récupérer les matchs

```
1. Frontend (React)
   ├─ useApi hook appelé
   ├─ api.getMatchs() exécuté
   └─ fetch('/api/matchs') envoyé

2. Serveur Web (OVH)
   ├─ Reçoit GET /api/matchs
   └─ Appelle public/api/matchs.php

3. Backend PHP
   ├─ ApiAuth vérifie JWT token (optionnel)
   ├─ MatchsModel::getMatchs() appelé
   └─ Requête SQL: SELECT * FROM pprod_matchs

4. Base de Données (MySQL)
   ├─ Exécute la requête SQL
   └─ Retourne les données

5. Backend PHP
   ├─ Formate les données en JSON
   └─ ApiResponse retourne réponse JSON

6. Frontend reçoit JSON
   ├─ data = { matchs: [...] }
   ├─ setState(data)
   └─ Component re-render avec les données
```

### Les 14 endpoints API

#### GET (Lecture - Publique)

```javascript
// Dans src/services/api.js

1. api.getMatchs()
   → GET /api/matchs
   ← { matchs: [...], success: true }

2. api.getClassements(competitionId)
   → GET /api/classements?competition_id=1
   ← { classements: [...] }

3. api.getEquipes()
   → GET /api/equipes
   ← { equipes: [...] }

4. api.getCompetitions()
   → GET /api/competitions
   ← { competitions: [...] }

5. api.getClub()
   → GET /api/club
   ← { club: {...} }

6. api.getEngagements()
   → GET /api/engagements
   ← { engagements: [...] }

7. api.getTerrains()
   → GET /api/terrains
   ← { terrains: [...] }

8. api.getMembres()
   → GET /api/membres
   ← { membres: [...] }

9. api.getConfig()
   → GET /api/config
   ← { config: {...} }

10. api.getSyncLogs()
    → GET /api/sync-logs
    ← { logs: [...] }
```

#### POST/PUT/DELETE (Écriture - Authentifiée)

```javascript
11. api.login(email, password)
    → POST /api/auth
       { email: "admin@fc.fr", password: "..." }
    ← { token: "eyJhbGc...", user: {...} }

12. api.createMatch(matchData, token)
    → POST /api/matchs
       { home_team: "Team A", away_team: "Team B", ... }
    ← { id: 123, success: true }

13. api.updateMatch(id, matchData, token)
    → PUT /api/matchs/123
       { score_home: 3, score_away: 2, ... }
    ← { success: true }

14. api.deleteMatch(id, token)
    → DELETE /api/matchs/123
    ← { success: true }
```

### Base de données (inchangée)

```sql
-- Structure existante (MySQL)

TABLE pprod_matchs
├── id (PK)
├── home_team
├── away_team
├── date
├── time
├── score_home
├── score_away
├── location
├── competition_id
└── ... (50+ colonnes)

TABLE pprod_classements
├── id (PK)
├── competition_id
├── team_code
├── position
├── points
├── played
├── wins
├── draws
├── losses
└── ...

TABLE pprod_equipes
├── id (PK)
├── code
├── name
├── category
└── ...

TABLE pprod_competitions
├── id (PK)
├── code
├── name
├── type
├── season
└── ...

-- Et 7 autres tables...
```

### Synchronisation FFF (CRON)

```
Tous les jours à 8h00 et 20h00:
│
├─ cron/sync_data.php s'exécute
├─ API FFF appelée (api-dofa.fff.fr)
├─ Données récupérées (matchs, classements, équipes)
├─ Données transformées et stockées en MySQL
├─ Logs créés dans pprod_sync_logs
└─ pprod_config mis à jour avec timestamp

Automatisé sur OVH (pas d'action manuelle)
```

### Authentification JWT

```
Flux Login:

1. User saisit email/password
   ├─ /api/auth POST { email, password }
   └─ Backend vérifie credentials MySQL

2. Backend génère JWT token
   ├─ Token = { user_id, email, exp: ... }
   ├─ Signé avec clé secrète
   └─ Token retourné au frontend

3. Frontend stocke token
   ├─ localStorage.setItem('auth_token', token)
   ├─ Utilisé pour requêtes protégées
   └─ Bearer Authorization header

4. Requête protégée
   ├─ fetch('/api/matchs', {
   │    headers: { Authorization: 'Bearer token...' }
   │  })
   ├─ Backend valide token
   └─ Si valide: requête exécutée
      Si invalide: 401 Unauthorized

5. Logout
   ├─ localStorage.removeItem('auth_token')
   └─ Token supprimé du client
```

---

## 4. GUIDE D'UTILISATION QUOTIDIENNE

### Démarrage du projet

#### Étape 1: Installer les dépendances
```bash
cd C:\Développement\fcchiche-react
npm install
# Installe tous les packages (react, react-router, etc)
# Crée node_modules/ (dossier lourd, ne pas versionner)
```

#### Étape 2: Démarrer le serveur de développement
```bash
npm run dev
# Lance le serveur Vite à http://localhost:5173
# Affiche: "Local: http://localhost:5173"

# Ouvert automatiquement dans le navigateur
# HMR activé (modifications = rechargement auto)
```

#### Étape 3: Développer!

Ouvrez votre éditeur (VS Code recommandé):
```bash
code .
# Ouvre le projet dans VS Code
```

### Structure d'un composant typique

```jsx
// src/pages/Matchs.jsx

import React, { useState } from 'react';
import { useApi } from '../hooks/useApi';      // Hook personnalisé
import api from '../services/api';              // Service API
import { MatchCard } from '../components/MatchCard';  // Composant réutilisable
import './Matchs.css';                          // Styles

// Composant = fonction qui retourne JSX
export const Matchs = () => {
  // STATES (données qui peuvent changer)
  const [activeTab, setActiveTab] = useState('upcoming');
  const [selectedEquipe, setSelectedEquipe] = useState(null);

  // HOOKS (useApi = appelle l'API et gère loading/error)
  const { data: matchsData, loading, error } = useApi(
    () => api.getMatchs({ limit: 50 })
  );

  const { data: competitionsData } = useApi(() => api.getCompetitions());

  // AFFICHAGE CONDITIONNEL
  if (loading) return <div>Chargement...</div>;
  if (error) return <div>Erreur: {error}</div>;

  // LOGIQUE (filtrer les données)
  const filteredMatchs = matchsData
    ?.filter(m => m.equipe_id === selectedEquipe)
    .filter(m => activeTab === 'upcoming' ? new Date(m.date) >= new Date() : true);

  // RETOUR JSX (HTML dynamique)
  return (
    <div className="matchs-page">
      <h1>Matchs</h1>

      {/* Filtre */}
      <select
        value={selectedEquipe || ''}
        onChange={(e) => setSelectedEquipe(e.target.value || null)}
      >
        <option value="">Toutes les équipes</option>
      </select>

      {/* Tabs */}
      <button
        className={activeTab === 'upcoming' ? 'active' : ''}
        onClick={() => setActiveTab('upcoming')}
      >
        À venir
      </button>

      {/* Liste des matchs */}
      <div className="matchs-list">
        {filteredMatchs?.map(match => (
          <MatchCard key={match.id} match={match} />
        ))}
      </div>
    </div>
  );
};
```

### Exemple: Appeler l'API

#### Dans un composant React:

```jsx
import { useApi } from '../hooks/useApi';
import api from '../services/api';

function MyComponent() {
  // Méthode 1: useApi hook (recommandé)
  const { data: matchs, loading, error } = useApi(() => api.getMatchs());

  if (loading) return <p>Chargement...</p>;
  if (error) return <p>Erreur: {error}</p>;

  return (
    <div>
      {matchs?.map(m => <div key={m.id}>{m.home_team} vs {m.away_team}</div>)}
    </div>
  );
}

// Méthode 2: Appel direct (pour actions utilisateur)
async function handleLogin(email, password) {
  try {
    const response = await api.login(email, password);
    console.log('Connecté!', response);
  } catch (error) {
    console.error('Erreur login:', error);
  }
}

// Méthode 3: Utiliser useAuth hook (pour authentification)
import { useAuth } from '../context/AuthContext';

function LoginComponent() {
  const { login, loading, error } = useAuth();

  const handleSubmit = async (email, password) => {
    const success = await login(email, password);
    if (success) {
      // Redirige vers dashboard
    }
  }
}
```

### Exemples de tâches courantes

#### Ajouter une nouvelle page

```jsx
// 1. Créer src/pages/Contact.jsx
import React from 'react';
import './Contact.css';

export const Contact = () => {
  return (
    <div className="contact-page">
      <h1>Contactez-nous</h1>
      <form>
        <input type="email" placeholder="Votre email" />
        <textarea placeholder="Votre message"></textarea>
        <button type="submit">Envoyer</button>
      </form>
    </div>
  );
};

// 2. Ajouter route dans App.jsx
import { Contact } from './pages/Contact';

function App() {
  return (
    <Routes>
      <Route path="/contact" element={<Contact />} />
      {/* autres routes */}
    </Routes>
  );
}

// 3. Ajouter lien dans Navigation.jsx
<Link to="/contact">Contact</Link>
```

#### Créer un composant réutilisable

```jsx
// src/components/Button.jsx
import './Button.css';

export const Button = ({ text, onClick, variant = 'primary' }) => {
  return (
    <button className={`btn btn-${variant}`} onClick={onClick}>
      {text}
    </button>
  );
};

// Utiliser:
<Button text="Cliquez-moi" onClick={handleClick} variant="success" />
<Button text="Supprimer" variant="danger" />
```

#### Utiliser le contexte global

```jsx
// Pour accéder aux données globales
import { useData } from '../context/DataContext';
import { useAuth } from '../context/AuthContext';

function MyComponent() {
  // Données globales
  const { club, equipes, competitions } = useData();

  // Authentification globale
  const { user, isAuthenticated, logout } = useAuth();

  return (
    <div>
      <h1>{club?.name}</h1>
      {isAuthenticated ? (
        <button onClick={logout}>Déconnexion</button>
      ) : (
        <p>Non authentifié</p>
      )}
    </div>
  );
}
```

---

## 5. GIT ET VERSIONING

### Configuration initiale

```bash
# Vérifier git configuré
git config --list | grep user
# Doit afficher: user.name=votre_nom, user.email=votre_email

# Si non configuré:
git config --global user.name "Votre Nom"
git config --global user.email "votre@email.com"
```

### Workflow Git (quotidien)

#### Avant de commencer
```bash
# 1. Créer une branche pour votre feature
git checkout -b feature/nouvelle-page

# 2. Vérifier la branche courante
git branch
# Output: * feature/nouvelle-page
#         main
#         preprod
```

#### Pendant le développement
```bash
# 1. Vérifier les fichiers modifiés
git status
# Output: modified: src/pages/Matchs.jsx
#         new file: src/components/NewComponent.jsx

# 2. Ajouter les fichiers
git add src/

# 3. Ou ajouter tout
git add .

# 4. Vérifier ce qui sera commité
git status
```

#### Faire un commit

```bash
# Commit = snapshot de votre travail
git commit -m "feat: Ajouter nouvelle feature

- Description détaillée
- Ou plusieurs lignes
- Ou plusieurs points"

# Exemple bon commit:
git commit -m "feat: Ajouter page Contact

- Formulaire de contact
- Validation des champs
- Email notification"

# Exemple mauvais commit:
git commit -m "fix stuff"  # ❌ Pas descriptif
```

#### Pousser sur Git

```bash
# 1. Créer branche distante (première fois)
git push -u origin feature/nouvelle-page

# 2. Pousser les changements (ensuite)
git push

# 3. Vérifier logs
git log --oneline
# Output: abc1234 feat: Ajouter page Contact
#         def5678 feat: Ajouter composant Button
#         ghi9012 fix: Corriger bug navigation
```

### Merging (fusionner branches)

```bash
# 1. Finir votre feature
git commit -m "feat: Feature complète"

# 2. Aller sur preprod
git checkout preprod

# 3. Mettre à jour preprod
git pull origin preprod

# 4. Merger votre branche
git merge feature/nouvelle-page

# 5. Pousser sur preprod
git push origin preprod

# 6. À ce stade, OVH webhook déploie automatiquement!
```

### Commits recommandés

```
Types de commits:
- feat: Nouvelle fonctionnalité
- fix: Correction de bug
- docs: Documentation
- style: Formatage code
- refactor: Restructuration
- perf: Performance
- test: Tests
- chore: Maintenance

Format:
git commit -m "type: Description courte

Détails si nécessaire
- Point 1
- Point 2"

Exemples:
git commit -m "feat: Ajouter page Contact"
git commit -m "fix: Corriger bug login"
git commit -m "docs: Documenter API"
git commit -m "refactor: Réorganiser composants"
```

### Branches recommandées

```
main
└─ Version de production (stable)

preprod
└─ Version de test (sur preprod.fcchiche.fr)

feature/...
└─ Votre branche de développement (temporaire)

Workflow:
1. Créer feature/mon-feature depuis preprod
2. Développer sur feature/mon-feature
3. Tester localement (npm run dev)
4. Commit et push
5. Merger dans preprod
6. OVH déploie automatiquement
7. Tester sur preprod.fcchiche.fr
8. Merger dans main pour production
```

---

## 6. DÉPLOIEMENT SUR OVH

### Architecture de déploiement

```
Local (Votre PC)
├─ npm run dev (tests locaux)
├─ npm run build (crée dist/)
└─ git push (pousse les commits)

        │
        ▼ (Git Webhook)

OVH Preprod (preprod.fcchiche.fr)
├─ Branche: preprod
├─ Auto-déploie via webhook
├─ Fichiers statiques dans dist/
└─ API backend existante

OVH Production (fcchiche.fr)
├─ Branche: main
├─ Auto-déploie via webhook
├─ Fichiers statiques dans dist/
└─ API backend existante
```

### Workflow de déploiement

#### Phase 1: Test local

```bash
# 1. Développer votre code
npm run dev

# 2. Tester sur http://localhost:5173
# - Vérifier toutes les pages
# - Tester navigation
# - Tester API calls
# - Vérifier console pour erreurs

# 3. Arrêter dev server
# Ctrl+C
```

#### Phase 2: Build production

```bash
# 1. Créer bundle optimisé
npm run build

# 2. Vérifier dist/ généré
ls -la dist/
# Output: index.html, assets/, etc.

# 3. Tester localement
npm run preview
# Accès: http://localhost:4173

# 4. Vérifier que tout fonctionne
# (test du build de production)
```

#### Phase 3: Git & Deployment

```bash
# 1. Vérifier changements
git status

# 2. Ajouter changements
git add .

# 3. Commit
git commit -m "feat: Ma nouvelle fonctionnalité"

# 4. Pousser sur preprod
git push origin preprod

# 5. OVH webhook détecte le push
# └─ Auto-déploie automatiquement!
# └─ Attendre ~30-60 secondes

# 6. Vérifier sur preprod.fcchiche.fr
# https://preprod.fcchiche.fr

# 7. Si OK, merger vers main
git checkout main
git merge preprod
git push origin main

# 8. Vérifier sur production
# https://fcchiche.fr
```

### Configuration OVH

#### Webhook Git (déjà configuré)

```
Quand vous faites: git push origin preprod

OVH reçoit le webhook:
├─ Récupère les nouveaux commits
├─ Exécute: npm install (si package.json changé)
├─ Exécute: npm run build (crée dist/)
├─ Copie dist/ → /public_html/preprod/
└─ Redéploie le site

Donc: Aucune action manuelle requise!
Il suffit de: git push
```

#### Vérifier le déploiement

```bash
# 1. Vérifier les logs OVH
# https://www.ovh.com/ → Console OVH → Logs

# 2. Vérifier avec curl
curl -I https://preprod.fcchiche.fr
# HTTP/1.1 200 OK

# 3. Vérifier le contenu
curl https://preprod.fcchiche.fr | head -20

# 4. Si erreur, vérifier:
# - Package.json valide?
# - Dépendances npm installées?
# - Build sans erreurs?
# - dist/ généré correctement?
```

#### Troubleshooting déploiement

```
Problème: Site 404 après push
Solution:
├─ Vérifier webhook déclenché (logs OVH)
├─ Vérifier npm run build réussit (local)
├─ Vérifier dist/ n'est pas dans .gitignore
└─ Refaire: git push origin preprod

Problème: API retourne 404
Solution:
├─ Vérifier API PHP backend toujours en place
├─ Vérifier URL API correcte (.env)
├─ Vérifier CORS configuré
└─ Tester avec curl: curl https://preprod.fcchiche.fr/api/club

Problème: Erreurs JavaScript
Solution:
├─ Ouvrir DevTools (F12)
├─ Onglet Console pour erreurs
├─ Vérifier imports chemins corrects
├─ Vérifier dépendances npm installées (node_modules/)
└─ Refaire: npm install && npm run build
```

---

## 7. TROUBLESHOOTING

### Problèmes courants

#### "Cannot find module 'react'"
```
Cause: node_modules/ manquant
Solution:
├─ npm install  ← Réinstalle tout
└─ Attendre 2-3 minutes
```

#### "Port 5173 déjà utilisé"
```
Cause: Autre processus utilise le port
Solution:
├─ npm run dev -- --port 5174  ← Utiliser autre port
└─ Ou: Fermer l'autre processus
```

#### "Module CSS non trouvé"
```
Cause: Import CSS incorrect
Solution:
├─ Vérifier le chemin: import './styles/Matchs.css'
├─ Vérifier le fichier existe
└─ Vérifier l'extension .css
```

#### "API retourne 404"
```
Cause: Backend PHP manquant ou URL incorrecte
Vérifier:
├─ URL dans .env correcte?
├─ Backend PHP déployé?
├─ CORS configuré?
└─ Tester: curl https://preprod.fcchiche.fr/api/club
```

#### "Authentification échoue"
```
Cause: Token JWT invalide ou API auth incorrecte
Vérifier:
├─ Credentials email/password corrects?
├─ API /api/auth répond?
├─ Réponse API contient "token"?
└─ Token sauvegardé dans localStorage?

Debug:
├─ DevTools → Application → localStorage
├─ Vérifier 'auth_token' présent
├─ Vérifier format JWT
```

#### "CORS error"
```
Erreur: "Access to XMLHttpRequest blocked by CORS policy"
Cause: Headers CORS manquants sur backend
Solution:
├─ Ajouter headers PHP dans /api/*.php
├─ header('Access-Control-Allow-Origin: *');
├─ header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
├─ header('Access-Control-Allow-Headers: Content-Type, Authorization');
└─ Gérer OPTIONS: if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;
```

### Commandes utiles

```bash
# Nettoyer et réinstaller
rm -rf node_modules/
npm install

# Vérifier les erreurs
npm run build  # Voir les erreurs de build

# Déboguer en local
npm run dev  # Chercher les erreurs console

# Vérifier le build
npm run preview  # Tester comme en production

# Logs Git
git log --oneline -10  # Voir les 10 derniers commits
git status  # Vérifier les changements

# Rechanger de branche
git checkout preprod  # Aller sur preprod
git pull origin preprod  # Mettre à jour

# Voir branche courante
git branch
```

---

## 📋 RÉSUMÉ RAPIDE

### Pour développer
```bash
cd C:\Développement\fcchiche-react
npm run dev
# Ouvrir http://localhost:5173
```

### Pour tester avant déployer
```bash
npm run build
npm run preview
```

### Pour déployer
```bash
git add .
git commit -m "feat: Ma feature"
git push origin preprod
# Webhook OVH déploie automatiquement
# Vérifier sur https://preprod.fcchiche.fr
```

### Architecture simple
```
Frontend (React) ←→ Backend PHP ←→ MySQL
  http://localhost:5173   /api/...    pprod_*
```

### React en 3 points
1. **Composants** = fonctions qui retournent JSX
2. **State** = données qui changent (useState)
3. **Props** = paramètres des composants

---

## 📚 RESSOURCES

- **React Docs:** https://react.dev
- **Vite Docs:** https://vitejs.dev
- **React Router:** https://reactrouter.com
- **Your API:** http://localhost:5173 (local)
- **Preprod:** https://preprod.fcchiche.fr

---

Vous êtes prêt! Commencez par `npm run dev` et explorez! 🚀
