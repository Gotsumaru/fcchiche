# Configuration du serveur Preprod

## 📋 Configuration requise après déploiement

### 1. Créer le fichier `config/config.php`

Le fichier `config/config.php` contient les credentials de base de données et **n'est PAS versionné** (pour la sécurité).

Connectez-vous en SSH sur votre serveur preprod et créez le fichier :

```bash
cd /home/votreuser/preprod.fcchiche.fr
nano config/config.php
```

Copiez ce contenu (en adaptant les valeurs pour la preprod) :

```php
<?php
declare(strict_types=1);

/**
 * Configuration applicative - PREPROD
 * ⚠️ Ne JAMAIS commiter ce fichier (contient credentials)
 */

// Base de données PREPROD
define('DB_HOST', 'votre-host-preprod.mysql.db');
define('DB_NAME', 'votre_base_preprod');
define('DB_USER', 'votre_user_preprod');
define('DB_PASS', 'votre_password_preprod');
define('DB_CHARSET', 'utf8mb4');

// API FFF
define('API_FFF_BASE_URL', 'https://api-dofa.fff.fr');
define('API_FFF_CLIENT_ID', 'votre_client_id');
define('API_FFF_CLIENT_SECRET', 'votre_client_secret');

// Admin credentials
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD_HASH', 'votre_hash_bcrypt');

// Environment
define('ENVIRONMENT', 'preprod');
define('DEBUG_MODE', true);
```

### 2. Générer le hash du mot de passe admin

```bash
php config/generate_password_hash.php
```

Copiez le hash généré dans `ADMIN_PASSWORD_HASH` dans votre `config.php`.

### 3. Vérifier les permissions

```bash
chmod 600 config/config.php
```

### 4. Tester l'API

Visitez : `https://preprod.fcchiche.fr/diagnostics.php`

Cela vous montrera si la configuration est correcte.

## 🔄 Déploiement automatique

Chaque push sur la branche `preprod` déclenche automatiquement :
1. Build du frontend React
2. Copie des fichiers PHP backend
3. Copie des assets statiques (images)
4. Déploiement sur la branche `preprod-deploy`

Le serveur OVH tire automatiquement depuis `preprod-deploy`.

## 📁 Structure déployée

```
preprod.fcchiche.fr/
├── index.html          ← React app
├── assets/             ← JS, CSS, images du build + images statiques
├── api/                ← Endpoints PHP
├── config/             ← Configuration (VOUS DEVEZ CRÉER config.php)
├── src/                ← Classes PHP (Models, API, Database, Utils)
├── cron/               ← Scripts de synchronisation
└── .htaccess           ← Règles Apache
```

## 🐛 Debugging

Si l'API ne fonctionne pas :
1. Vérifiez que `config/config.php` existe
2. Vérifiez les credentials de BDD
3. Vérifiez les logs Apache : `tail -f logs/error.log`
4. Testez la connexion BDD : visitez `/diagnostics.php`
