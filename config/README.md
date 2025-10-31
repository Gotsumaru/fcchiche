# Documentation du répertoire `config`

Ce dossier centralise toute la configuration applicative du site FC Chiche.

## Contenu

| Fichier | Rôle | Notes |
| --- | --- | --- |
| `config.php` | Paramètres globaux (BDD, API FFF, chemins, logs) | Doit être versionné sans secrets sensibles en production. | 
| `database.php` | Fabrique de connexion PDO sécurisée | Utilise `PDO::ATTR_ERRMODE => ERRMODE_EXCEPTION`. |
| `bootstrap.php` | Initialisation commune (autoload, timezone, constantes) | À inclure sur les points d'entrée publics. |
| `generate_password_hash.php` | Script CLI pour générer des mots de passe | Exécution manuelle : `php config/generate_password_hash.php "motdepasse"`. |

## Bonnes pratiques

- Toujours charger `bootstrap.php` avant toute autre dépendance pour garantir la configuration des constantes.
- Ne jamais commiter de crédentials définitifs : utiliser les variables d'environnement OVH pour surcharger `config.php` lors du déploiement.
- Tester la connexion PDO via `database.php` avant toute mise en production (`php -r "require 'config/database.php'; Database::getInstance(); echo 'OK';"`).
- Limiter les permissions à `640` sur ce dossier côté hébergement.

## Dépendances

- PHP 8.1+
- Extension PDO + PDO MySQL

## Points d'entrée recommandés

```php
<?php
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/database.php';

$pdo = Database::getInstance();
```
