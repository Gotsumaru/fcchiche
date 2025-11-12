# 🧹 Nettoyage des fichiers inutiles

Liste des fichiers qui devraient être supprimés du projet ou ignorés.

---

## 📋 Fichiers à décider

### 1. `src/debug.js`

**Statut:** Untracked (pas en git)
**Utilité:** À déterminer

**Décision:**
- ❌ Si **pas utilisé**: Ne pas commiter, supprimer du disque
- ✅ Si **utile pour testing**: Commiter

**Commande (si vous ne l'utilisez pas):**
```bash
rm src/debug.js
```

---

### 2. `Dockerfile`, `docker-compose.yml`, `docker/`

**Statut:** Untracked (pas en git)
**Utilité:** Développement local Docker uniquement
**Décision:** ❌ Ne PAS commiter

**Raison:**
- Chaque développeur peut avoir une config Docker différente
- À garder en local pour `npm run test:complete`
- `.gitignore` les ignore déjà

**Commande (garder en local, ne pas commiter):**
```bash
# Les fichiers restent en local, juste pas en git
# Rien à faire, ils sont déjà ignorés par .gitignore
```

---

### 3. `DOCKER_TESTING.md`

**Statut:** Créé pour ce projet
**Utilité:** Documentation développement local

**Décision:** ❌ Ne PAS commiter (optionnel)

**Raison:**
- Seulement utile pour développeurs
- Pas nécessaire en production OVH

**Commande (si vous ne voulez pas le versionner):**
```bash
# Ignorer dans .gitignore (déjà fait)
# Ou supprimer si vous ne voulez pas le garder
rm DOCKER_TESTING.md
```

---

### 4. `.env.development`

**Statut:** Créé pour ce projet
**Utilité:** Configuration Vite pour mode Docker

**Décision:** ❌ À IGNORER (pas en git)

**Raison:**
- Configuration développement local uniquement
- Chaque dev crée la sienne si besoin
- `.gitignore` l'ignore déjà

**Commande:**
```bash
# Le fichier peut rester en local pour votre usage dev
# Mais ne pas le commiter (il est ignoré par .gitignore)
```

---

## 📁 Structure recommandée post-cleanup

```
fcchiche/
├── .gitignore                    ✓ À commiter
├── .env.example                 ✓ À commiter (template public)
├── .env.local.example           ✓ À commiter (template, pas les vrais secrets)
│
├── config/
│   ├── config.php               ✓ À commiter (sans secrets)
│   ├── config.php.example       ✓ À commiter (template)
│   ├── loadenv.php              ✓ À commiter (charge secrets)
│   ├── database.php             ✓ À commiter
│   └── bootstrap.php            ✓ À commiter
│
├── src/
│   ├── API/                     ✓ À commiter
│   ├── Models/                  ✓ À commiter
│   ├── Utils/                   ✓ À commiter
│   ├── Database/                ✓ À commiter
│   ├── components/              ✓ À commiter
│   ├── pages/                   ✓ À commiter
│   ├── hooks/                   ✓ À commiter
│   ├── api.js                   ✓ À commiter
│   ├── main.jsx                 ✓ À commiter
│   └── reveal.js                ✓ À commiter
│
├── public/
│   ├── api/                     ✓ À commiter
│   ├── assets/                  ✓ À commiter
│   └── .htaccess                ✓ À commiter
│
├── package.json                 ✓ À commiter
├── package-lock.json            ✓ À commiter
├── vite.config.js               ✓ À commiter
├── index.html                   ✓ À commiter
│
├── DEPLOYMENT_GUIDE.md          ✓ À commiter (guide)
├── GIT_DEPLOYMENT_CHECKLIST.md  ✓ À commiter (aide)
├── DOCKER_TESTING.md            ❌ Optionnel (dev only)
│
├── Dockerfile                   ❌ À ignorer (.gitignore)
├── docker-compose.yml           ❌ À ignorer (.gitignore)
├── docker/                      ❌ À ignorer (.gitignore)
├── .env.local                   ❌ À ignorer (.gitignore)
├── .env.development             ❌ À ignorer (.gitignore)
├── node_modules/                ❌ À ignorer (.gitignore)
├── public/dist/                 ❌ À ignorer (.gitignore)
│
└── .claude/settings.local.json  ❌ À ignorer (.gitignore)
```

---

## 🚀 Plan de nettoyage

### Step 1: Décider sur src/debug.js

```bash
# Vérifier s'il est utilisé
grep -r "debug" src/ | grep -i "import\|require" || echo "Pas utilisé"

# Si pas utilisé, supprimer:
rm src/debug.js
```

### Step 2: Garder Docker files localement, pas en git

```bash
# Vérifier qu'ils sont ignorés par .gitignore
git status | grep "Dockerfile\|docker-compose"

# Devrait afficher: (rien)
# Si affichage: ils ne sont pas ignorés, vérifier .gitignore
```

### Step 3: Décider sur DOCKER_TESTING.md

**Option A - Garder en git pour documentation:**
```bash
git add DOCKER_TESTING.md
git commit -m "docs: Guide Docker testing pour développeurs"
```

**Option B - Garder localement, pas en git:**
```bash
# Ignorer dans .gitignore (optionnel, c'est une doc utile)
# Laisser en local seulement
```

Nous recommandons: **Garder en git** (c'est une bonne doc de dev)

---

## 📊 Résumé décisions

| Fichier | Commiter? | Raison |
|---------|-----------|--------|
| `src/debug.js` | ❓ À décider | Utile? |
| `Dockerfile` | ❌ Non | Dev local |
| `docker-compose.yml` | ❌ Non | Dev local |
| `docker/` | ❌ Non | Dev local |
| `DOCKER_TESTING.md` | ✅ Oui | Doc utile |
| `.env.development` | ❌ Non | Config dev |
| `.env.local.example` | ✅ Oui | Template |
| `config/config.php.example` | ✅ Oui | Template |
| `config/loadenv.php` | ✅ Oui | Code |

---

## ✨ Actions finales

```bash
# 1. Décider sur debug.js
# rm src/debug.js  # Si pas utilisé

# 2. Vérifier .gitignore est complet
cat .gitignore | grep -E "Dockerfile|docker-compose|\.env\."

# 3. Faire le cleanup
git status --short | grep "^??"

# 4. Stage et commit les fichiers à garder
git add .gitignore config/ .env.local.example .env.example DEPLOYMENT_GUIDE.md GIT_DEPLOYMENT_CHECKLIST.md DOCKER_TESTING.md package.json src/

# 5. Vérifier avant commit
git status

# 6. Commit
git commit -m "chore: Nettoyer et organiser le repo avant déploiement"

# 7. Push
git push origin preprod
```

---

**Voilà!** Votre repo est maintenant propre et sécurisé. 🎯
