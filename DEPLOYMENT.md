# 🚀 Guide de Déploiement - FC Chiché React

## Architecture

Le projet combine **PHP Backend + React Frontend**:
- **Frontend:** React 18 buildé en `/public/dist/` par Vite
- **Backend:** APIs PHP dans `/public/api/`
- **Assets:** Fichiers statiques dans `/public/assets/`

## Développement Local

### 1. Démarrer le serveur de développement

```bash
cd C:\Développement\fcchiche
npm run dev
```

Accédez à: **http://localhost:5174/**

Les changements de code reloadent automatiquement (HMR).

### 2. Builder pour production

```bash
npm run build
```

Cela génère `/public/dist/` avec le HTML et assets optimisés.

## Déploiement sur OVH

### Préparation

1. **Build en local d'abord:**
   ```bash
   npm run build
   ```

2. **Vérifier que `/public/dist/` existe** avec:
   - `dist/index.html`
   - `dist/assets/` (CSS, JS)

3. **Vérifier que `.htaccess` existe** dans `/public/`

### Processus de déploiement

1. **Commit les changements:**
   ```bash
   git add .
   git commit -m "Description du changement"
   ```

2. **Pousser vers Git (OVH auto-déploie):**
   ```bash
   git push origin preprod
   ```
   ou
   ```bash
   git push origin main
   ```

3. **Attendre le webhook OVH** (30 secondes environ)

4. **Vérifier sur preprod.fcchiche.fr**

### Comment ça fonctionne sur OVH

1. **OVH reçoit le push Git**
2. **Webhook lance le déploiement**
3. **Apache lit `.htaccess`** qui:
   - Ignore `/api/` → les APIs PHP fonctionnent normalement
   - Redirige tout vers `/public/dist/index.html` → React prend le contrôle
4. **React Router** gère la navigation (pas de rechargement page)
5. **Les APIs** répondent sur `/api/matchs`, `/api/classements`, etc.

## Checklist avant déploiement

- [ ] `npm run build` réussi (pas d'erreurs)
- [ ] `/public/dist/index.html` existe
- [ ] `/public/dist/assets/` contient des fichiers
- [ ] `/public/.htaccess` existe
- [ ] `/public/api/` contient les fichiers PHP des APIs
- [ ] Git committed tous les changements
- [ ] Test local sur http://localhost:5174/

## Fichiers clés

```
fcchiche/
├── src/                    # Code source React
│   ├── components/
│   ├── pages/
│   ├── services/api.js     # Client API
│   ├── styles/
│   ├── App.jsx
│   └── main.jsx
├── public/
│   ├── dist/              # Build React (généré par Vite)
│   │   ├── index.html
│   │   └── assets/
│   ├── api/               # APIs PHP originales (intactes)
│   │   ├── matchs.php
│   │   ├── classements.php
│   │   └── ...
│   ├── assets/            # Images, fonts (si présentes)
│   └── .htaccess          # URL rewriting pour React Router
├── index.html             # Template HTML pour build
├── vite.config.js         # Config Vite
├── package.json           # Dépendances
└── DEPLOYMENT.md          # Ce fichier
```

## Dépannage

### Les APIs ne répondent pas
```bash
curl http://localhost:8000/api/matchs
# Vérifie que l'API PHP répond
```

### Les fichiers CSS/JS ne chargent pas
- Vérifier que `/public/dist/assets/` existent
- Vérifier les URLs dans le `.htaccess`
- Relancer: `npm run build`

### React affiche une page blanche
- Ouvrir F12 → Console dans le navigateur
- Chercher les erreurs JavaScript
- Vérifier que les API répondent sur `/api/`

### Revert à une ancienne version
```bash
git log --oneline          # Voir les commits
git revert <commit-id>     # Revenir à un commit
git push origin preprod
```

## Performance

- **Bundle:** ~200KB (minifié)
- **Build time:** <2 secondes
- **HMR:** <100ms
- **Gzipped:** ~65KB

## Questions?

Consultez:
- `/GUIDE_COMPLET_REACT.md` - Guide React complet
- `/BACKEND_EXPLANATION.md` - Explication backend PHP
- `/TESTING_VERIFICATION_GUIDE.md` - Tests
