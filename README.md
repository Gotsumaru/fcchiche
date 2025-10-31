# FC Chiche - Refonte Site Web

## Architecture

### Stack Technique
- **Backend** : PHP 8.1 (vanilla)
- **Base de données** : MySQL/MariaDB avec PDO
- **API** : FFF API (api-dofa.fff.fr)
- **Hébergement** : OVH (infrastructure mutualisée)
- **Déploiement** : Dépôt Git OVH avec auto-déploiement continu (push = publication)

### Fonctionnalités
- Synchronisation automatique données API FFF (2x/jour : 8h et 20h)
- Stockage BDD avec relations normalisées
- Logging complet (API, sync, erreurs)
- Gestion historique/archivage automatique

## Installation

### 1. Prérequis
- PHP 8.1+
- MySQL 5.7+ ou MariaDB 10.3+
- Accès CRON
- Extension PHP : PDO, PDO_MySQL, JSON

### 2. Configuration Base de Données

```bash
# 1. Importer le schéma
mysql -u votre_user -p votre_base < sql/database_schema.sql

# 2. Vérifier les tables créées
mysql -u votre_user -p votre_base -e "SHOW TABLES LIKE 'pprod_%'"
```

### 3. Configuration Application

Éditer `config/config.php` :

```php
// Base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'votre_base');
define('DB_USER', 'votre_user');
define('DB_PASS', 'votre_password');

// API FFF
define('API_FFF_CLUB_ID', 5403); // ID club Chiche

// Environnement
define('ENV', 'production'); // Passer en production après tests
```

### 4. Permissions Fichiers

```bash
# Permissions répertoires
chmod 755 config/ src/ cron/ public/
chmod 775 logs/

# Permissions fichiers
chmod 644 config/*.php src/**/*.php cron/*.php
chmod 755 cron/sync_data.php
```

### 5. Configuration CRON

Ajouter dans crontab (`crontab -e`) :

```bash
# Synchronisation FC Chiche - 8h et 20h
0 8,20 * * * /usr/bin/php /chemin/complet/fcchiche-refonte/cron/sync_data.php >> /chemin/complet/fcchiche-refonte/logs/cron.log 2>&1
```

Vérifier chemin PHP : `which php`

### 6. Test Manuel Synchronisation

```bash
# Test script synchronisation
php cron/sync_data.php

# Vérifier logs
tail -f logs/sync.log
tail -f logs/cron.log
```

### 7. Vérification Données

```sql
-- Vérifier club synchronisé
SELECT * FROM pprod_club;

-- Vérifier équipes
SELECT * FROM pprod_equipes;

-- Vérifier matchs à venir
SELECT * FROM pprod_matchs WHERE is_result = 0 ORDER BY date ASC LIMIT 5;

-- Vérifier derniers résultats
SELECT * FROM pprod_matchs WHERE is_result = 1 ORDER BY date DESC LIMIT 5;

-- Vérifier dernière synchronisation
SELECT * FROM pprod_config WHERE config_key LIKE 'last_sync_%';

-- Logs synchronisation
SELECT * FROM pprod_sync_logs ORDER BY created_at DESC LIMIT 10;
```

## Déploiement OVH via Git Auto

- **Workflow** : chaque push sur la branche `main` du dépôt OVH déclenche l'auto-déploiement côté hébergement mutualisé.
- **Préparation locale** : `git pull` pour rester aligné, développement en feature branch, puis merge propre sur `main`.
- **Déclenchement** : `git push ovh main` (ou remote `production`) publie immédiatement l'application.
- **Post-déploiement** : vérifier `public/` en HTTPS, consulter `logs/cron.log` et `logs/sync.log`.
- **Fallback** : en cas de rollback, re-pusher un tag précédent (`git push ovh v1.2.3:main`).

## Structure Projet

```
fcchiche-refonte/
├── config/
│   ├── config.php          # Configuration principale
│   └── database.php        # Connexion PDO
├── src/
│   ├── API/
│   │   └── FFFApiClient.php    # Client API FFF
│   ├── Database/
│   │   └── Sync.php            # Synchronisation
│   └── Utils/
│       └── Logger.php          # Gestion logs
├── cron/
│   └── sync_data.php       # Script CRON
├── public/
│   └── index.php           # Page accueil (à venir)
├── sql/
│   └── database_schema.sql # Schéma BDD
├── logs/                   # Logs (générés auto)
└── README.md
```

## Tables BDD (prefix pprod_)

- `pprod_club` : Informations club
- `pprod_terrains` : Terrains du club
- `pprod_membres` : Membres du bureau
- `pprod_competitions` : Toutes les compétitions
- `pprod_equipes` : Équipes du club
- `pprod_engagements` : Pivot équipes-compétitions
- `pprod_matchs` : Calendrier + résultats
- `pprod_sync_logs` : Logs synchronisation
- `pprod_config` : Configuration système

## Maintenance

### Monitoring

```bash
# Surveiller logs temps réel
tail -f logs/sync.log

# Vérifier taille logs
du -h logs/

# Logs cron
tail -f logs/cron.log
```

### Rotation Logs

Les logs sont automatiquement rotationnés à 10 MB (5 fichiers conservés).

### Troubleshooting

**Synchronisation échoue**
```bash
# Vérifier connectivité API
curl -I https://api-dofa.fff.fr/api/clubs/5403

# Tester manuellement
php cron/sync_data.php

# Consulter logs erreurs
grep -i error logs/sync.log
```

**CRON ne s'exécute pas**
```bash
# Vérifier cron actif
service cron status

# Vérifier logs système
grep CRON /var/log/syslog

# Tester chemin PHP
which php
```

## Prochaines Étapes

1. ✅ Phase 1 : Backend/BDD/Synchronisation (TERMINÉ)
2. ⏳ Phase 2 : Page d'accueil
3. ⏳ Phase 3 : Pages calendrier/résultats
4. ⏳ Phase 4 : PWA + Design responsive
5. ⏳ Phase 5 : Backoffice administration

## Support

- Logs détaillés : `logs/`
- Configuration API : `config/config.php`
- Documentation API FFF : https://api-dofa.fff.fr

---

**Version** : 1.0.0 - Phase 1 Backend  
**Dernière mise à jour** : Octobre 2025
