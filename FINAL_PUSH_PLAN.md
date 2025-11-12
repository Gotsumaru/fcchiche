# 🚀 Plan Final - Pousser sur OVH

Résumé complet pour déployer proprement votre site sur OVH.

---

## ✅ Vérifications avant de commencer

### 1️⃣ Tester localement que tout fonctionne

```bash
# Mode développement avec mock data
npm run test:ui
# Visiter http://localhost:5174
# ✅ Vérifier que ça affiche quelque chose

# Mode développement avec vraies données (Docker)
npm run test:complete
# Visiter http://localhost:5174
# ✅ Vérifier que ça affiche les vraies données OVH

# Build de production
npm run build
# ✅ Vérifier qu'il n'y a pas d'erreurs

# Tester le build en local
npm run preview
# Visiter http://localhost:4173
# ✅ Vérifier que le site fonctionne avec le build
```

---

## 📋 Plan d'action (7 étapes)

### Étape 1️⃣ : Créer et tester .env.local local

**Objectif:** Vous assurer que la BD OVH est accessible

```bash
# Créer le fichier local
cat > .env.local << 'EOF'
ENV=development
DB_HOST=fcchice79.mysql.db
DB_NAME=fcchice79
DB_USER=fcchice79
DB_PASS=VOTRE_NOUVEAU_PASSWORD_OVH
EOF

# Tester que ça fonctionne
npm run test:complete

# ✅ Si ça affiche les matchs: OK
# ❌ Si erreur BD: Vérifier le password OVH
```

---

### Étape 2️⃣ : Builder en local

```bash
# Générer le build React pour production
npm run build

# ✅ Devrait afficher: "✓ built in X.XXs"
# ❌ S'il y a erreurs: Les fixer avant de continuer
```

---

### Étape 3️⃣ : Tester le build

```bash
# Lancer le serveur de preview
npm run preview

# Ouvrir: http://localhost:4173
# ✅ Vérifier que le site fonctionne
# ❌ S'il y a problèmes: Fixer et re-builder (npm run build)
```

---

### Étape 4️⃣ : Stage les fichiers pour git

**⚠️ IMPORTANT:** Ne pas commiter `.env.local` !

```bash
# Voir le status
git status

# Stage les fichiers importants
git add .gitignore                           # Mise à jour build strategy
git add config/config.php                    # Sécurité
git add config/config.php.example            # Template
git add config/loadenv.php                   # Charge secrets
git add .env.example                         # Template public
git add .env.local.example                   # Template secrets
git add DEPLOYMENT_GUIDE.md                  # Guide OVH
git add GIT_DEPLOYMENT_CHECKLIST.md          # Checklist
git add CLEANUP_FILES.md                     # Cleanup
git add BUILD_STRATEGY.md                    # Build strategy
git add FINAL_PUSH_PLAN.md                   # Ce fichier
git add package.json                         # Scripts mise à jour
git add vite.config.js                       # Proxy Docker
git add index.html                           # Mise à jour
git add src/                                 # Tous les sources
git add public/dist/                         # ⭐ BUILD REACT (NOUVEAU!)
git add public/api/                          # APIs PHP
git add public/assets/                       # Assets statiques

# NE PAS commiter:
# .env.local                (fichier local, jamais en git)
# Dockerfile, docker-compose.yml (dev local)
# .env.development          (dev local)
# .claude/settings.local.json (local)
# node_modules/             (déjà ignoré)

# Vérifier
git status
```

---

### Étape 5️⃣ : Commit

```bash
# Commit avec message descriptif
git commit -m "feat: Déploiement production - Build React + Sécurité

- Ajouter npm run build pour optimisation production
- Commiter public/dist/ (build React optimisé)
- Sécurité: Externaliser secrets vers .env.local (config/loadenv.php)
- Ajouter templates sans secrets (config.php.example, .env.local.example)
- Mettre à jour .gitignore pour ignorer vraie config et dev files
- Ajouter scripts npm pour Docker testing en local
- Ajouter guides de déploiement (DEPLOYMENT_GUIDE.md, BUILD_STRATEGY.md)

⚠️ CRITIQUE:
- Mot de passe BD DOIT être changé sur OVH (était exposé)
- Créer .env.local sur serveur OVH avec nouvelles credentials

Voir: DEPLOYMENT_GUIDE.md pour post-déploiement"
```

---

### Étape 6️⃣ : Vérifier avant push

```bash
# Voir les commits qui seront pushés
git log origin/preprod..preprod --oneline

# Vérifier qu'il n'y a pas de secrets dans le push
git diff origin/preprod..preprod | grep -i "password\|secret" || echo "✅ OK"

# Vérifier que config.php.example n'a pas de password en dur
grep "DB_PASS" config/config.php.example
# Doit afficher: define('DB_PASS', 'YOUR_PASSWORD_HERE');

# Vérifier que .env.local n'est pas en git
git ls-files | grep ".env.local" || echo "✅ .env.local ignoré"
```

---

### Étape 7️⃣ : Push

```bash
# Push vers la branche de déploiement
git push origin preprod

# Ou si vous voulez pousser sur main:
git push origin main

# ✅ Voir les push sur GitHub
# Attendre 2-5 minutes que OVH détecte et déploie
```

---

## 🔧 Post-déploiement (sur OVH)

Après que votre code soit sur OVH, vous devez configurer les secrets:

### Option A - Panel OVH (Recommandé)

```
1. https://www.ovh.com/manager/web/
2. Sélectionner votre hébergement
3. Domaines → Votre domaine
4. Variables PHP ou Variables d'environnement
5. Ajouter:
   ENV = production
   DB_HOST = fcchice79.mysql.db
   DB_NAME = fcchice79
   DB_USER = fcchice79
   DB_PASS = VOTRE_NOUVEAU_MOT_DE_PASSE_OVH
```

### Option B - Via FTP (.env.local)

```bash
# Via FTP, créer un fichier: .env.local
# À la racine du projet (même niveau que index.html)

Contenu:
ENV=production
DB_HOST=fcchice79.mysql.db
DB_NAME=fcchice79
DB_USER=fcchice79
DB_PASS=VOTRE_NOUVEAU_MOT_DE_PASSE_OVH
```

---

## ✅ Tester que le déploiement fonctionne

```bash
# 1. Vérifier que les APIs répondent
curl https://fcchiche.fr/api/matchs.php
# ✅ Doit retourner du JSON avec matchs

# 2. Vérifier que le site affiche
curl https://fcchiche.fr/
# ✅ Doit retourner du HTML avec React

# 3. Ouvrir dans le navigateur
https://fcchiche.fr

# 4. DevTools (F12) → Network
# Vérifier que les requêtes API répondent avec les vraies données

# 5. Vérifier les logs OVH s'il y a problèmes
# Panel OVH → Logs → PHP
```

---

## 🎯 Résumé des commandes (copier-coller)

```bash
# ===== VÉRIFICATIONS =====
npm run test:ui           # Test avec mock data
npm run test:complete     # Test avec vraies données
npm run build             # Builder pour prod
npm run preview           # Tester le build

# ===== GIT =====
git add .gitignore config/ .env*.example *.md package.json vite.config.js index.html src/ public/
git commit -m "feat: Production - Build React + Sécurité BD"
git push origin preprod

# ===== VÉRIFICATION =====
curl https://fcchiche.fr/api/matchs.php
# ✅ Si JSON retourné: OK!
```

---

## 📊 Structure après déploiement

### En git (commité)
```
config/
├── config.php              ✓ Charge depuis getenv()
├── config.php.example      ✓ Template sans password
└── loadenv.php             ✓ Charge .env.local

public/dist/
├── index.html              ✓ Build React
├── assets/                 ✓ JS/CSS optimisés
├── api/                    ✓ APIs PHP
└── .htaccess               ✓ Rewrite rules

.env.local.example          ✓ Template (jamais le vrai)
```

### Sur OVH (pas en git, créé manuellement)
```
.env.local                  ✗ Secrets uniquement OVH
                            (via FTP ou variables panel)
```

---

## 🔐 Sécurité - Checklist finale

Avant de pousser:

- ☑️ `.env.local` local créé (pour tester)
- ☑️ Testé avec `npm run test:complete` ✅
- ☑️ `npm run build` sans erreurs ✅
- ☑️ `npm run preview` fonctionne ✅
- ☑️ Mot de passe BD changé sur OVH ✅
- ☑️ `.gitignore` mis à jour (ignore secrets) ✅
- ☑️ `config.php.example` existe (sans secrets) ✅
- ☑️ `.env.local` n'est pas commité ✅
- ☑️ `public/dist/` va être commité ✅
- ☑️ Logs git vérifiés (pas de secrets) ✅

---

## ❓ FAQ

**Q: Je viens de pusher et le site est blanc?**
A: Vérifier les logs OVH. Probablement que `.env.local` n'est pas créé sur le serveur.

**Q: public/dist/ est trop gros?**
A: Normal, c'est ~2-5 MB compressé. Les gros projets font ça.

**Q: Je dois rebuild à chaque changement?**
A: Oui, si vous modifiez du React, faire `npm run build` puis `git add public/dist/` avant de commiter.

**Q: Et si je veux tester une modif avant de rebuild?**
A: Faire `npm run test:ui` ou `npm run test:complete` pour tester en dev, puis builder quand c'est OK.

---

## 🎬 Prochaines étapes

1. ✅ Faire `npm run build`
2. ✅ Faire `npm run preview` et vérifier
3. ✅ Faire `git add` des fichiers listés
4. ✅ Faire `git commit` avec le message fourni
5. ✅ Faire `git push origin preprod`
6. ✅ Attendre 2-5 minutes
7. ✅ Vérifier https://fcchiche.fr

Bon déploiement! 🚀
