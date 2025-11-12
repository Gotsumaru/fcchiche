# 🚀 Guide de Déploiement - Git + OVH

Ce guide explique comment déployer votre site FC Chichè sur OVH via Git.

---

## ⚠️ ÉTAPE CRITIQUE : Sécurité avant tout

### 1️⃣ CHANGER LE MOT DE PASSE BD OVH (URGENT!)

**ATTENTION:** Le mot de passe BD a été exposé en git. Il FAUT le changer immédiatement.

**Sur le panel OVH:**
1. Aller sur https://www.ovh.com/manager/web/
2. Sélectionner votre hébergement mutualisé
3. Aller dans `Bases de données` → `fcchice79`
4. Cliquer sur `Changer le mot de passe`
5. Noter le nouveau mot de passe (vous le mettrez dans `.env.local` en production)

---

## 📋 Étapes de déploiement

### Étape 1️⃣ : Préparation locale (sur votre ordinateur)

#### 1.1 - Créer `.env.local` avec les vrais secrets

```bash
# Copier le template
cp .env.local.example .env.local

# Éditer .env.local avec vos infos
# ENV=production
# DB_HOST=fcchice79.mysql.db
# DB_NAME=fcchice79
# DB_USER=fcchice79
# DB_PASS=VOTRE_NOUVEAU_MOT_DE_PASSE_OVH
```

**⚠️ IMPORTANT:** Ne JAMAIS commiter `.env.local` en git

#### 1.2 - Vérifier que les secrets ne sont plus dans le code

```bash
# Vérifier que config.php.example existe (template sans secrets)
ls config/config.php.example

# Vérifier .gitignore ignore les secrets
cat .gitignore | grep "config/config.php"  # Doit être dans la liste

# Tester en local que tout fonctionne
npm install
npm run test:ui  # Mock data
# ou
npm run test:complete  # Avec Docker + BD OVH
```

#### 1.3 - Nettoyer et commiter

```bash
# Stage les fichiers modifiés (config, .gitignore, package.json, etc.)
git status  # Voir ce qui doit être stagé

# Ajouter les fichiers de sécurité
git add .gitignore
git add config/config.php.example
git add config/loadenv.php
git add .env.local.example
git add .env.example
git add DEPLOYMENT_GUIDE.md
git add package.json
git add vite.config.js
git add src/

# NE PAS ajouter:
# - .env.local
# - Dockerfile, docker-compose.yml (untracked)
# - config/config.php (pas de modif, reste en git)
# - DOCKER_TESTING.md (dev only)

# Commit
git commit -m "fix: Sécurité - Externaliser secrets BD vers .env.local

- Créer config/loadenv.php pour charger secrets depuis .env.local
- Créer config/config.php.example (template sans secrets)
- Ajouter .env.local.example pour template
- Mettre à jour .gitignore pour ignorer secrets
- Modifier config.php pour charger depuis getenv()

IMPORTANT: Mot de passe BD DOIT être changé sur OVH car il était exposé."

# Voir les commits
git log --oneline | head -5
```

#### 1.4 - Pousser vers la branche de déploiement

```bash
# Vérifier votre branche actuelle
git branch

# Vous êtes probablement sur 'preprod'
# Vérifier s'il existe une relation avec origin
git branch -vv

# Pousser vers preprod
git push origin preprod

# Ou si vous voulez pousser sur main:
git push origin main
```

---

### Étape 2️⃣ : Configuration OVH (dans le panel web)

#### 2.1 - Variables d'environnement OVH

OVH permet de définir des variables d'environnement directement dans le panel.

**Options:**

**Option A - Via le panel OVH (Recommandé pour OVH):**

1. Aller sur https://www.ovh.com/manager/web/
2. Sélectionner votre hébergement
3. Aller dans `Domaines` → votre domaine
4. Rechercher la section `Variables PHP` ou `Variables d'environnement`
5. Ajouter :
   ```
   ENV = production
   DB_HOST = fcchice79.mysql.db
   DB_NAME = fcchice79
   DB_USER = fcchice79
   DB_PASS = [VOTRE_NOUVEAU_MOT_DE_PASSE]
   ```

**Option B - Via fichier `.env.local` sur le serveur (Plus simple):**

1. Déployer le code (voir Étape 3)
2. Créer un fichier `/public/.env.local` sur le serveur OVH via FTP/SFTP
   ```
   ENV=production
   DB_HOST=fcchice79.mysql.db
   DB_NAME=fcchice79
   DB_USER=fcchice79
   DB_PASS=VOTRE_NOUVEAU_MOT_DE_PASSE_OVH
   ```
3. Donner les permissions correctes : `chmod 600 .env.local`

> **OVH recommande l'Option A** (variables du panel), car `.env.local` ne devrait jamais être dans l'arborescence publique.

#### 2.2 - Vérifier les autorisations FTP

Vous aurez besoin d'accès FTP pour:
- Créer `.env.local` (ou configurer variables via panel)
- Vérifier que le déploiement s'est bien fait
- Voir les logs si besoin

---

### Étape 3️⃣ : Déploiement du code (Git sur OVH)

OVH supporte le déploiement via Git. Voici les étapes:

#### 3.1 - Vérifier que OVH a un dépôt Git configuré

OVH crée généralement un dépôt Git automatiquement pour les hébergements mutualisés.

**Vérifier l'accès:**

```bash
# Sur votre machine locale
git remote -v

# Vous devriez voir:
# origin    https://github.com/Gotsumaru/fcchiche.git (fetch)
# origin    https://github.com/Gotsumaru/fcchiche.git (push)

# Si vous avez un remote OVH:
# git remote add ovh https://git.ovh.com/...
# Mais généralement c'est déjà configuré
```

#### 3.2 - Pousser le code vers votre dépôt principal (GitHub)

```bash
git push origin preprod
# ou
git push origin main
```

#### 3.3 - OVH récupère le code (auto-déploiement)

OVH a normalement un hook Git qui:
1. Détecte les push sur `main` ou `preprod`
2. Télécharge le code sur le serveur
3. Installe les dépendances npm
4. Build le site

**Vérifier le statut du déploiement:**

Sur le panel OVH, vous pouvez voir:
- Logs de déploiement
- Erreurs build
- État du site

---

### Étape 4️⃣ : Build React en production

Le build React (`public/dist/`) doit être généré.

#### Option A - Build localement et pusher

```bash
# Sur votre machine locale
npm run build

# Commit le build
git add public/dist/
git commit -m "build: Générer bundle React pour production"
git push origin preprod
```

#### Option B - Build sur OVH après déploiement (via hook)

OVH peut exécuter automatiquement :
```bash
npm install && npm run build
```

Vérifier dans le panel OVH si cette option est activée.

---

### Étape 5️⃣ : Vérification post-déploiement

#### 5.1 - Vérifier que le site est accessible

```bash
# Tester les APIs
curl https://fcchiche.fr/api/matchs.php
curl https://fcchiche.fr/api/equipes.php
curl https://fcchiche.fr/api/config.php

# Vous devriez avoir des réponses JSON
```

#### 5.2 - Vérifier les logs OVH

Si une erreur, consulter:
- Panel OVH → Logs
- Panel OVH → Erreurs PHP
- FTP → `/logs/` répertoire

#### 5.3 - Tester les pages

Accéder à https://fcchiche.fr et vérifier:
- ✅ La page charge
- ✅ Les matchs s'affichent
- ✅ Les équipes chargent
- ✅ Les classements apparaissent

---

## 📊 Résumé du processus

```
Local Machine
    ↓
1. Ajouter secrets dans .env.local
2. Commit code + sécurité
3. Push vers GitHub/origin
    ↓
GitHub
    ↓
4. OVH détecte le push (hook Git)
5. OVH récupère le code
6. OVH exécute npm install && npm run build
7. OVH publie dans /public/
    ↓
Production OVH
    ↓
8. Variables d'env configurées (panel OVH ou .env.local)
9. APIs accèdent la BD
10. Site accessible via HTTPS
```

---

## 🔄 Mises à jour ultérieures

Une fois le site déployé, pour faire des mises à jour:

```bash
# Sur votre machine locale
git checkout preprod
git pull

# Faire vos modifications
# ...

# Tester en local
npm run test:ui      # Mock
npm run test:complete  # Avec Docker + BD

# Si OK, commit et push
git add .
git commit -m "feat: Description de la modification"
git push origin preprod

# ✅ OVH redéploie automatiquement
```

---

## ❌ Troubleshooting

### Erreur: "Cannot connect to database"

**Vérifier:**
1. Mot de passe BD changé? (il l'était en git avant)
2. Variables d'env configurées sur OVH? (panel ou .env.local)
3. IP whitelistée chez OVH?

```bash
# Via FTP, créer test.php:
<?php
require 'config/config.php';
require 'config/database.php';
try {
    $db = Database::getInstance();
    echo "✅ Connexion OK";
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage();
}
?>
```

### Erreur: "Cannot find config/config.php"

**Vérifier:**
- Le fichier existe en production? (voir via FTP)
- Les permissions sont correctes? (`chmod 644`)

### Erreur: "Cannot read .env.local"

**Vérifier:**
- Le fichier existe? (créé via FTP ou variables panel)
- Les permissions? (`chmod 600`)

### Le site affiche une page blanche

**Vérifier les logs PHP:**
- Panel OVH → Logs
- FTP → `/logs/php.log` (si disponible)

---

## 📝 Checklist avant déploiement

- ☑️ Mot de passe BD changé sur OVH
- ☑️ `.env.local` créé localement (jamais en git)
- ☑️ Code testé en local (`npm run test:complete`)
- ☑️ Build généré (`npm run build`)
- ☑️ `.gitignore` met à jour (ignore secrets)
- ☑️ Commit et push vers GitHub
- ☑️ Variables d'env configurées sur OVH (panel ou `.env.local`)
- ☑️ Site accessible et APIs répondent

---

## 🎯 Structure Git finale

```
Repository (GitHub)
├── .gitignore           ✓ Ignore .env.local, Dockerfile, etc.
├── config/
│   ├── config.php       ✓ Sans secrets (utilise getenv)
│   ├── config.php.example ✓ Template
│   └── loadenv.php      ✓ Charge depuis .env.local
├── .env.local.example   ✓ Template (jamais le vrai .env.local)
├── .env.example         ✓ Template pour Vite
├── src/                 ✓ Tous les sources React
├── public/api/          ✓ APIs PHP
├── Dockerfile           ✗ NE PAS en git
├── docker-compose.yml   ✗ NE PAS en git
├── DOCKER_TESTING.md    ✗ NE PAS en git (dev only)
└── DEPLOYMENT_GUIDE.md  ✓ Ce guide
```

---

## 💡 Bonnes pratiques

1. **Jamais commiter `.env.local`** - Config secrète locale
2. **Jamais commiter `Dockerfile`** - Dev local seulement
3. **Toujours commiter `.example`** - Pour que chacun sache quoi créer
4. **Toujours tester en local avant de pusher** - `npm run test:complete`
5. **Utiliser des commits atomiques** - Un commit = une fonction/fix
6. **Écrire des messages clairs** - `git commit -m "feat: Description claire"`

---

## 📞 Besoin d'aide ?

Consultez:
- **Local:** `DOCKER_TESTING.md` - Comment tester avec Docker
- **Sécurité:** Vérifier `.gitignore` et `config/loadenv.php`
- **OVH:** Panel d'administration OVH (Logs, Variables, FTP)
- **Git:** `git status` et `git log` pour voir l'historique

Bon déploiement! 🚀
