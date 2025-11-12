# ✅ Checklist Git - Avant de déployer

## 📊 État actuel du repo

**Branch:** preprod
**Fichiers modifiés:** 14
**Fichiers non tracés:** 11

---

## 🎯 Ce qu'il faut faire

### Phase 1️⃣ : Staging des fichiers importants

#### ✅ À COMMITER (Fichiers de sécurité et config)

```bash
git add .gitignore
git add config/config.php
git add config/config.php.example
git add config/loadenv.php
git add .env.example
git add .env.local.example
git add DEPLOYMENT_GUIDE.md
git add package.json
git add vite.config.js
git add src/api.js
```

#### ✅ À COMMITER (Fonctionnalités React)

```bash
git add src/components/MatchCard.jsx
git add src/components/ResultCard.jsx
git add src/hooks/
git add src/reveal.js
git add src/
```

#### ✅ À COMMITER (Modifications existantes)

```bash
# Tous les autres fichiers modifiés:
git add index.html
git add package-lock.json
git add public/
git add src/index.css
git add src/main.jsx
git add src/mockData.js
git add src/pages/HomePage.jsx
```

#### ❌ À IGNORER (Ne pas commiter)

- `.env.local` - Jamais! (secrets)
- `Dockerfile`, `docker-compose.yml`, `docker/` - Déjà ignorés
- `DOCKER_TESTING.md` - Dev local
- `.claude/settings.local.json` - Déjà ignoré
- `src/debug.js` - À décider (voir ci-dessous)

---

### Phase 2️⃣ : Commit unique de sécurité

```bash
git add .gitignore config/config.php config/config.php.example config/loadenv.php .env.example .env.local.example DEPLOYMENT_GUIDE.md

git commit -m "security: Externaliser secrets BD vers .env.local

- Créer config/loadenv.php pour charger secrets depuis .env.local ou variables système
- Créer config/config.php.example (template sans password)
- Ajouter .env.local.example pour développeurs
- Mettre à jour .gitignore pour ignorer .env.local et fichiers dev
- Modifier config.php pour charger credentials via getenv()

⚠️ CRITIQUE: Mot de passe BD doit être changé sur OVH (était exposé en git)
Voir: DEPLOYMENT_GUIDE.md pour détails"
```

### Phase 3️⃣ : Commit des nouvelles fonctionnalités React

```bash
git add src/components/ src/hooks/ src/reveal.js package.json vite.config.js src/api.js

git commit -m "feat: Ajouter composants React et support Docker testing

- Ajouter MatchCard et ResultCard components
- Ajouter hooks personnalisés
- Ajouter reveal.js pour animations scroll
- Ajouter support variables d'env pour mode mock data
- Mettre à jour vite.config.js pour proxy Docker en dev"
```

### Phase 4️⃣ : Commit des autres modifications

```bash
git add index.html package-lock.json public/ src/index.css src/main.jsx src/mockData.js src/pages/

git commit -m "build: Mettre à jour assets et styles

- Mettre à jour index.html
- Actualiser styles CSS
- Mettre à jour mockData
- Organiser assets statiques"
```

### ❓ Décision : src/debug.js

**Question:** Gardez-vous ce fichier?

**Options:**
- ❌ **Ne pas commiter:** `git clean -fd src/` pour le supprimer
- ✅ **Commiter:** `git add src/debug.js` si c'est un utilitaire de test

**Recommandation:** Supprimer si ce n'est pas utilisé, ou ajouter un `.gitignore` pour `**/debug.js`

---

## 🚀 Commandes complètes (copier-coller)

### Option A : Committer en une seule fois

```bash
# Stage TOUT ce qui doit être commité
git add .gitignore config/ .env.example .env.local.example DEPLOYMENT_GUIDE.md package.json package-lock.json vite.config.js index.html public/ src/

# MAIS: Cela peut inclure src/debug.js, donc plutôt faire:
git add .gitignore config/ .env.example .env.local.example DEPLOYMENT_GUIDE.md package.json package-lock.json vite.config.js index.html public/ src/components/ src/hooks/ src/pages/ src/api.js src/index.css src/main.jsx src/mockData.js src/reveal.js

# Vérifier ce qui sera commité
git status

# Commit
git commit -m "feat: Sécurité, composants React, et Docker testing"

# Push
git push origin preprod
```

### Option B : Committer en phases (recommandé)

**Phase 1 - Sécurité:**
```bash
git add .gitignore config/config.php config/config.php.example config/loadenv.php .env.example .env.local.example DEPLOYMENT_GUIDE.md
git commit -m "security: Externaliser secrets vers .env.local

- Config/loadenv.php charge depuis .env.local
- Template sans password en config.php.example
- Mise à jour .gitignore pour ignorer secrets
- ⚠️ CHANGER MOT DE PASSE BD SUR OVH"
```

**Phase 2 - React:**
```bash
git add src/components/ src/hooks/ src/reveal.js vite.config.js src/api.js package.json
git commit -m "feat: Ajouter composants React et support Docker testing"
```

**Phase 3 - Autres:**
```bash
git add index.html package-lock.json public/ src/index.css src/main.jsx src/mockData.js src/pages/
git commit -m "build: Actualiser styles et assets"
```

**Phase 4 - Push:**
```bash
git push origin preprod
```

---

## 📋 Vérification avant push

```bash
# Voir les commits qui seront pushés
git log origin/preprod..preprod --oneline

# Vérifier qu'il n'y a pas de secrets dans les fichiers
git diff --staged | grep -i "password\|secret\|key" || echo "✅ Aucun secret trouvé"

# Voir ce qui sera dans le push
git diff --cached
```

---

## 🔐 Vérification sécurité CRITIQUE

Avant de push, vérifier:

```bash
# ✅ config/config.php n'a plus le password en dur
grep -n "DB_PASS" config/config.php
# Doit afficher: define('DB_PASS', getenv('DB_PASS') ?: '');

# ✅ .gitignore ignore les secrets
grep "\.env.local" .gitignore
# Doit afficher la ligne

# ✅ config.php.example existe
ls -la config/config.php.example

# ✅ .env.local.example existe
ls -la .env.local.example
```

---

## 📝 Post-commit : Après push

Une fois `git push` fait:

1. ✅ Vérifier le push sur GitHub
   ```bash
   git log --oneline -5
   ```

2. ✅ Attendre que OVH détecte et déploie (2-5 minutes)

3. ✅ Créer `.env.local` sur OVH (via FTP ou variables panel)
   ```
   ENV=production
   DB_HOST=fcchice79.mysql.db
   DB_NAME=fcchice79
   DB_USER=fcchice79
   DB_PASS=VOTRE_NOUVEAU_MOT_DE_PASSE
   ```

4. ✅ Tester le site: https://fcchiche.fr

---

## ❌ Fichiers à NE PAS commiter

- `.env.local` ← Jamais!
- `Dockerfile`, `docker-compose.yml` ← Dev local
- `DOCKER_TESTING.md` ← Dev local
- `node_modules/` ← Déjà ignoré
- `public/dist/` ← Build output (optionnel de commiter)
- `.env.development` ← Dev local
- `.claude/settings.local.json` ← Déjà ignoré

---

## 🎯 Résumé rapide

**Si vous êtes pressé:**

```bash
# 1. Stage et commit tout d'un coup
git add .
git commit -m "feat: Sécurité BD, composants React, Docker testing"

# 2. Vérifier avant push
git log --oneline -3
git diff HEAD^ config/config.php | head -20

# 3. Push
git push origin preprod

# 4. Vérifier que secrets ne sont pas en git
git show HEAD:config/config.php | grep DB_PASS
# Doit montrer: getenv('DB_PASS'), pas le password en dur
```

---

## 🆘 Erreurs courantes

### ❌ "I don't know how to merge .env.local"

Solution: `.env.local` doit être dans `.gitignore`, pas en git.

```bash
git rm --cached .env.local
```

### ❌ "Je viens de commiter le password!"

Solution: Utiliser BFG pour nettoyer (voir guide déploiement).

```bash
# Instant fix (dernière commit):
git reset HEAD~1
git rm --cached config/config.php
# Corriger config.php
git add config/config.php
git commit -m "security: Enlever password du commit précédent"
```

### ❌ "Les fichiers ne pushent pas"

Vérifier l'accès:

```bash
git remote -v
git push -u origin preprod  # -u pour upstream
```

---

## ✨ Prochaines étapes

1. ✅ `git add` des fichiers listés ci-dessus
2. ✅ `git commit` avec message clair
3. ✅ `git push origin preprod`
4. ✅ Attendre déploiement OVH
5. ✅ Configurer `.env.local` sur OVH
6. ✅ Tester https://fcchiche.fr

Bonne chance! 🚀
