<?php
/**
 * Charge les variables d'environnement depuis .env.local
 *
 * IMPORTANT: .env.local n'est JAMAIS versionné en git
 * Il doit être créé manuellement à chaque déploiement
 *
 * Ce fichier est appelé au début de config.php pour charger les secrets
 */

declare(strict_types=1);

// Détecter l'environnement
$env = $_ENV['ENV'] ?? $_SERVER['ENV'] ?? 'production';
define('ENV', $env);

// Définir le chemin du fichier .env.local
$envPath = dirname(__DIR__) . '/.env.local';

// Charger .env.local s'il existe (dev local uniquement)
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        // Ignorer les commentaires
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // Parser les lignes KEY=VALUE
        if (strpos($line, '=') !== false) {
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            // Supprimer les guillemets si présents
            if ((strpos($value, '"') === 0 && strrpos($value, '"') === strlen($value) - 1) ||
                (strpos($value, "'") === 0 && strrpos($value, "'") === strlen($value) - 1)) {
                $value = substr($value, 1, -1);
            }

            // Ajouter aux variables d'environnement
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

// Alternative: charger depuis variables d'environnement système (recommandé en production OVH)
// Si DB_PASS n'est pas encore défini, essayer depuis $_ENV ou getenv()
if (!getenv('DB_PASS')) {
    // Sur OVH, définir les variables depuis le panel d'administration
    // Ou depuis les variables d'environnement du serveur web

    // En local dev, si .env.local n'existe pas, utiliser des defaults
    if (ENV === 'development') {
        // Defaults locaux (à adapter)
        putenv('DB_HOST=localhost');
        putenv('DB_NAME=fcchiche');
        putenv('DB_USER=root');
        putenv('DB_PASS=');  // Laissé vide en dev local sans config
    }
}
