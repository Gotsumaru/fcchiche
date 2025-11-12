# 🔨 Stratégie de Build - npm run build

## 📊 Décision à prendre

Vous avez deux stratégies pour la production OVH:

### Option A: Builder en local + Commiter le build ✅ RECOMMANDÉ

**Commandes:**
```bash
# 1. Builder localement
npm run build

# 2. Vérifier le build en preview
npm run preview
# Puis accéder: http://localhost:4173

# 3. Si tout est OK, commiter le build
git add public/dist/
git commit -m "build: Générer bundle React optimisé pour prod"

# 4. Push
git push origin preprod
```

**Structure git après:**
```
public/
├── api/              (PHP APIs)
├── assets/           (images, CSS)
├── dist/             ✅ COMMITÉ (build React)
├── index.html
└── .htaccess
```

**Avantages:**
- ✅ Garantit un build valide à chaque déploiement
- ✅ OVH n'a pas besoin de Node.js
- ✅ Déploiement plus rapide (pas de build sur le serveur)
- ✅ Plus contrôlable

**Inconvénients:**
- ❌ Repo devient plus lourd (~3-5 MB pour `public/dist/`)
- ❌ `git diff` affichera beaucoup de fichiers minifiés
- ❌ Historique git moins lisible

---

### Option B: Ignorer le build, OVH builder automatiquement

**Commandes:**
```bash
# 1. Laisser public/dist/ ignoré par .gitignore (c'est déjà le cas)
cat .gitignore | grep "public/dist"

# 2. Push le code source seulement
git add .
git commit -m "feat: Ajouter nouvelles fonctionnalités"
git push origin preprod

# 3. OVH exécute (via hook):
# npm install
# npm run build
# chmod -R 755 public/
```

**Structure git après:**
```
public/
├── api/              (PHP APIs)
├── assets/           (images, CSS)
├── dist/             ✗ IGNORÉ (pas en git)
├── index.html
└── .htaccess
```

**Avantages:**
- ✅ Repo plus léger
- ✅ Historique git plus lisible
- ✅ Un seul artifact: le code source

**Inconvénients:**
- ❌ OVH doit avoir Node.js installé
- ❌ OVH doit exécuter `npm run build` après deploy
- ❌ Déploiement plus lent
- ❌ Si le build échoue sur OVH, le site ne fonctionne pas

---

## 🔍 Vérifier la configuration OVH

### Étape 1: Voir si OVH a un hook de build

**Sur le panel OVH:**
1. Aller sur https://www.ovh.com/manager/web/
2. Sélectionner votre hébergement
3. Aller dans: `Domaines` → Votre domaine
4. Chercher: `Git`, `Déploiement automatique`, ou `Hooks`
5. Vérifier si un script s'exécute après `git push`

**Chercher:**
- Y a-t-il un script qui exécute `npm run build`?
- Node.js est-il disponible?

### Étape 2: Tester manuellement (via FTP)

```bash
# Via FTP, créer un fichier test-node.php:
<?php
echo "Node.js path: " . shell_exec('which node');
echo "npm version: " . shell_exec('npm -v');
?>

# Visiter: https://fcchiche.fr/test-node.php
# Si vous voyez des chemins: Node.js est disponible ✅
# Si erreur: Node.js n'est pas disponible ❌
```

---

## 🎯 Recommandation selon votre config

### Si Node.js est dispo sur OVH: **Option B**
```bash
# Ignorer public/dist/ (déjà fait)
git add .
git commit -m "feat: ..."
git push origin preprod
# OVH builder automatiquement
```

### Si Node.js N'est PAS dispo sur OVH: **Option A (OBLIGATOIRE)**
```bash
# Builder en local
npm run build
# Commiter public/dist/
git add public/dist/
git commit -m "build: ..."
git push origin preprod
```

---

## 📝 Passer d'une stratégie à l'autre

### Si vous étiez en Option B et voulez Option A:

```bash
# 1. Retirer public/dist/ de .gitignore
sed -i '/public\/dist\//d' .gitignore

# 2. Builder
npm run build

# 3. Ajouter à git
git add public/dist/
git add .gitignore
git commit -m "build: Commiter bundle React optimisé (changement de stratégie)"
git push origin preprod
```

### Si vous étiez en Option A et voulez Option B:

```bash
# 1. Supprimer du git (mais garder en local)
git rm --cached -r public/dist/

# 2. Ajouter à .gitignore
echo "public/dist/" >> .gitignore

# 3. Commit
git commit -m "build: Ignorer bundle React (construire sur OVH)"
git push origin preprod
```

---

## ✅ Checklist avant de décider

- [ ] Vérifier si OVH a Node.js: `test-node.php`
- [ ] Vérifier si OVH exécute `npm run build`: panel OVH ou git logs
- [ ] Tester localement: `npm run build && npm run preview`
- [ ] Décider: Option A (commiter build) ou B (OVH builder)
- [ ] Mettre à jour .gitignore si necessaire
- [ ] Commiter et pusher

---

## 🚀 Prochaine étape

**Vous devez tester localement d'abord:**

```bash
# Générer le build React
npm run build

# Vérifier qu'il n'y a pas d'erreurs
# Devrait afficher:
# ✓ 123 modules transformed
# dist/index-XXXX.js     456.45 kB

# Tester que le build fonctionne
npm run preview

# Visiter: http://localhost:4173
# Vérifier que le site fonctionne
```

---

## 💡 Conseil

**En production, généralement:**
- Les petits projets: **Option A** (commiter build) - plus simple
- Les gros projets: **Option B** (OVH builder) - plus flexibilité

**Pour FC Chichè:** Je recommande **Option A** car:
- ✅ Hébergement mutualisé (moins de contrôle OVH)
- ✅ Garantit que la prod fonctionne
- ✅ Pas besoin de dépendre de la config OVH
- ✅ Déploiement plus rapide
