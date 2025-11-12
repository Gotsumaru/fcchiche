# 🚀 Script de déploiement - OVH

Guide pour déployer automatiquement sur OVH via SFTP ou FTP.

---

## 📋 Avant de déployer

**Vérifications obligatoires:**

```bash
# 1. Code commité en git
git status
# Ne doit afficher aucune modification

# 2. Tests en local réussis
npm run test:complete
# ✅ Site fonctionne avec vraies données

# 3. Build généré
npm run build
# ✅ Pas d'erreurs

# 4. Tester le build
npm run preview
# ✅ Site fonctionne

# 5. Secrets OVH préparés
# .env.local créé localement (jamais committé)
# Nouveau password BD généré sur panel OVH
```

---

## 🔧 Option A : Script Python (Recommandé)

Automatise la copie via SFTP.

### Installation

```bash
# Installer dépendances Python
pip install paramiko  # Bibliothèque SFTP

# Ou sur Mac
pip3 install paramiko
```

### Créer deploy.py

```python
#!/usr/bin/env python3
"""
Script de déploiement automatisé pour OVH
Copie les fichiers nécessaires sur le serveur OVH via SFTP
"""

import os
import sys
import paramiko
from pathlib import Path

# ========================
# Configuration OVH
# ========================
OVH_HOST = 'fcchiche.fr'  # ou IP SFTP OVH
OVH_USER = 'fcchiche'     # Votre user FTP
OVH_PASS = 'YOUR_FTP_PASSWORD_HERE'  # Voir panel OVH
OVH_PORT = 22  # SFTP port

# Ou avec clé SSH (plus sûr):
# OVH_KEY = '~/.ssh/id_rsa'

# Répertoires à déployer
DEPLOY_DIRS = [
    ('public/dist', '/public/dist'),
    ('public/api', '/public/api'),
    ('public/assets', '/public/assets'),
    ('config', '/config'),
    ('cron', '/cron'),
]

# ========================
# Helpers
# ========================
def size_mb(path):
    """Taille dossier en MB"""
    total = 0
    for dirpath, dirnames, filenames in os.walk(path):
        for f in filenames:
            total += os.path.getsize(os.path.join(dirpath, f))
    return total / (1024 * 1024)

def upload_dir(sftp, local_dir, remote_dir, exclude=None):
    """Upload un répertoire via SFTP"""
    exclude = exclude or []

    for root, dirs, files in os.walk(local_dir):
        # Créer répertoires distants
        for dir_name in dirs:
            local_path = os.path.join(root, dir_name)
            remote_path = local_path.replace(local_dir, remote_dir).replace('\\', '/')

            try:
                sftp.stat(remote_path)
            except FileNotFoundError:
                print(f"📁 Créer: {remote_path}")
                sftp.mkdir(remote_path)

        # Upload fichiers
        for file_name in files:
            local_path = os.path.join(root, file_name)
            remote_path = local_path.replace(local_dir, remote_dir).replace('\\', '/')

            if any(exc in local_path for exc in exclude):
                print(f"⏭️  Ignorer: {file_name}")
                continue

            print(f"📤 Upload: {remote_path}")
            sftp.put(local_path, remote_path)

def main():
    """Déployer sur OVH"""

    print("\n" + "="*50)
    print("🚀 Déploiement FC Chichè → OVH")
    print("="*50 + "\n")

    # Vérifications
    print("✅ Vérifications...")

    if os.path.getsize('.env.local') > 0:
        print("   ⚠️  Attention: .env.local trouvé localement")
        print("       (Ne pas uploader ce fichier!)")

    for local_dir, _ in DEPLOY_DIRS:
        if not os.path.isdir(local_dir):
            print(f"   ❌ Erreur: {local_dir} n'existe pas")
            print(f"       Faire d'abord: npm run build")
            sys.exit(1)
        size = size_mb(local_dir)
        print(f"   ✓ {local_dir}: {size:.1f} MB")

    print("\n📝 Confirmation avant déploiement:\n")
    print("  - Code committé en git? (git status)")
    print("  - Tests réussis? (npm run test:complete)")
    print("  - Build généré? (npm run build)")
    print("  - .env.local créé sur OVH? (via FTP ou panel)")
    print("  - Nouveau password BD sur OVH?")

    response = input("\n   Continuer? (oui/non): ").strip().lower()
    if response not in ['oui', 'yes', 'y']:
        print("❌ Déploiement annulé")
        return

    print("\n🔗 Connexion SFTP...")

    try:
        # Connexion SFTP
        transport = paramiko.Transport((OVH_HOST, OVH_PORT))
        transport.connect(username=OVH_USER, password=OVH_PASS)
        sftp = paramiko.SFTPClient.from_transport(transport)

        print(f"✅ Connecté à {OVH_HOST}\n")

        # Upload chaque répertoire
        for local_dir, remote_dir in DEPLOY_DIRS:
            print(f"\n📁 Upload: {local_dir}/ → {remote_dir}/")
            upload_dir(sftp, local_dir, remote_dir)

        sftp.close()
        transport.close()

        print("\n" + "="*50)
        print("✅ Déploiement réussi!")
        print("="*50)

        print("\n📋 Prochaines étapes:")
        print("  1. Vérifier sur OVH que public/dist/ est à jour")
        print("  2. Accéder https://fcchiche.fr")
        print("  3. Tester les APIs: curl https://fcchiche.fr/api/matchs.php")
        print("  4. Vérifier les logs OVH s'il y a problèmes")

    except Exception as e:
        print(f"❌ Erreur SFTP: {e}")
        print("\n💡 Conseils:")
        print("  - Vérifier OVH_HOST, OVH_USER, OVH_PASS")
        print("  - Vérifier que SFTP est activé sur OVH")
        print("  - Tenter manuellement avec FileZilla")
        sys.exit(1)

if __name__ == '__main__':
    main()
```

### Utiliser le script

```bash
# 1. Créer le fichier deploy.py à la racine

# 2. Éditer avec vos infos OVH:
#    OVH_HOST = 'fcchiche.fr'
#    OVH_USER = 'votre_user_ftp'
#    OVH_PASS = 'votre_password_ftp'

# 3. Rendre exécutable
chmod +x deploy.py

# 4. Lancer
python3 deploy.py
```

---

## 🔧 Option B : Script Bash (Simple)

Pour Linux/Mac, utilise rsync/sftp.

```bash
#!/bin/bash
# deploy.sh - Déploiement simple

set -e

OVH_HOST="fcchiche.fr"
OVH_USER="fcchiche"
OVH_PATH="/home/fcchiche/www"

echo "🚀 Déploiement vers OVH..."

# Vérifications
echo "✅ Vérifications..."
[ -d "public/dist" ] || { echo "❌ public/dist n'existe pas. Faire npm run build"; exit 1; }
[ -f ".env.local" ] || { echo "⚠️  .env.local pas trouvé localement (OK si sur OVH)"; }

# Upload
echo "📤 Upload des fichiers..."

rsync -avz --delete public/dist/ ${OVH_USER}@${OVH_HOST}:${OVH_PATH}/public/dist/
rsync -avz public/api/ ${OVH_USER}@${OVH_HOST}:${OVH_PATH}/public/api/
rsync -avz config/ ${OVH_USER}@${OVH_HOST}:${OVH_PATH}/config/

echo "✅ Déploiement réussi!"
echo ""
echo "📋 Vérifier:"
echo "  1. https://fcchiche.fr"
echo "  2. curl https://fcchiche.fr/api/matchs.php"
```

### Utiliser le script

```bash
# Rendre exécutable
chmod +x deploy.sh

# Lancer
./deploy.sh
```

---

## 🖱️ Option C : Manuel FTP (Graphique)

Sans script, utiliser un client FTP graphique.

### FileZilla (gratuit)

```
1. Télécharger: https://filezilla-project.org/
2. Connexion:
   - Host: sftp://fcchiche.fr
   - Username: votre_user_ftp
   - Password: votre_password_ftp
   - Port: 22 (SFTP)

3. Naviguer:
   - Local: C:\Dev\fcchiche\public\dist
   - Remote: /public/dist

4. Drag & drop pour uploader
```

### Étapes manuelles

1. **Connecter FTP**
   - Host: `ftp://fcchiche.fr` ou SFTP
   - User: Votre user OVH
   - Password: Votre password FTP

2. **Naviguer à `/public/`**

3. **Uploader dossiers:**
   - `dist/` → remplace `/public/dist/`
   - `api/` → vérifier (ne pas déleter)
   - `assets/` → vérifier (ne pas déleter)

4. **Créer `/`.env.local`:**
   ```
   ENV=production
   DB_HOST=fcchice79.mysql.db
   DB_NAME=fcchice79
   DB_USER=fcchice79
   DB_PASS=YOUR_PASSWORD_HERE
   ```

5. **Vérifier permissions:**
   - `chmod 644` fichiers PHP
   - `chmod 755` répertoires

---

## ✅ Post-déploiement

Après avoir uploadé, vérifier:

```bash
# 1. Site accessible
curl https://fcchiche.fr -I
# Doit retourner 200 OK

# 2. API répondent
curl https://fcchiche.fr/api/config.php
# Doit retourner JSON

# 3. Matchs affichés
curl https://fcchiche.fr/api/matchs.php?upcoming=1
# Doit retourner matchs

# 4. Ouvrir dans navigateur
https://fcchiche.fr
# Vérifier que tout fonctionne
```

---

## 🔍 Dépannage

### Erreur 503 Service Unavailable

**Cause:** `.env.local` manquant ou incorrect
**Solution:**
1. Vérifier `.env.local` sur OVH
2. Vérifier le password BD
3. Vérifier que DB_HOST est accessible

### Erreur 404 sur `/api/`

**Cause:** Fichiers API manquants
**Solution:**
1. Vérifier que `/public/api/` est sur OVH
2. Vérifier permissions: `chmod 755 public/api`
3. Vérifier que `.htaccess` est intact

### JavaScript ne charge pas

**Cause:** `public/dist/` incomplet
**Solution:**
1. Re-générer localement: `npm run build`
2. Re-uploader `public/dist/`
3. Vérifier que `public/dist/assets/` existe

---

## 🎯 Script de vérification post-deploy

```bash
#!/bin/bash
# verify-deploy.sh

echo "🔍 Vérification déploiement OVH..."

DOMAIN="https://fcchiche.fr"

echo "1. Test domaine..."
curl -I $DOMAIN | head -1

echo "2. Test API config..."
curl -s $DOMAIN/api/config.php | head -1

echo "3. Test API matchs..."
curl -s $DOMAIN/api/matchs.php?upcoming=1 | head -1

echo "4. Test accès site..."
curl -I $DOMAIN/ | head -1

echo "✅ Vérifications terminées"
```

---

## 📊 Résumé déploiement

| Étape | Local | OVH |
|-------|-------|-----|
| Code source | Git (tout) | Pas besoin |
| public/dist/ | Généré (npm run build) | Upload |
| public/api/ | Existe | Existe |
| config/ | Existe | Existe |
| .env.local | Jamais en git | Créé manuellement |

---

## 🚀 Workflow final

```bash
# 1. Développer en local
npm run dev

# 2. Tester
npm run test:complete

# 3. Builder
npm run build

# 4. Commiter
git add src/ config/ *.md
git commit -m "feat: ..."
git push origin preprod

# 5. Déployer sur OVH
python3 deploy.py  # Ou script bash, ou manuel FTP

# 6. Vérifier
curl https://fcchiche.fr/api/matchs.php
```

---

Bon déploiement! 🚀
