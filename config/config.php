<?php
declare(strict_types=1);

/**
 * Configuration principale FC Chiche
 *
 * IMPORTANT: Les secrets (DB_PASS, etc) doivent être configurés en dehors du versionage Git.
 * Voir: config/loadenv.php et .env.local.example
 */

// Charger d'abord les variables d'environnement (depuis .env.local ou variables système)
require_once __DIR__ . '/loadenv.php';

// Reporting erreurs selon l'environnement
error_reporting(E_ALL);
ini_set('display_errors', ENV === 'development' ? '1' : '0');

// Timezone
date_default_timezone_set('Europe/Paris');

// ===========================
// Configuration BDD
// ===========================
// Les credentials sont chargés depuis .env.local ou variables système
define('DB_HOST', getenv('DB_HOST') ?: 'fcchice79.mysql.db');
define('DB_NAME', getenv('DB_NAME') ?: 'fcchice79');
define('DB_USER', getenv('DB_USER') ?: 'fcchice79');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');
define('DB_PREFIX', 'pprod_');

// Configuration API FFF
define('API_FFF_BASE_URL', 'https://api-dofa.fff.fr/api');
define('API_FFF_CLUB_ID', 5403);
define('API_FFF_TIMEOUT', 30);

// Chemins
define('ROOT_PATH', dirname(__DIR__));
define('CONFIG_PATH', ROOT_PATH . '/config');
define('SRC_PATH', ROOT_PATH . '/src');
define('LOG_PATH', ROOT_PATH . '/logs');
define('PUBLIC_PATH', ROOT_PATH . '/public');

// Saison actuelle
define('CURRENT_SEASON', 2025);

// ===========================
// Configuration synchronisation
// ===========================
define('SYNC_ENABLED', true);
define('SYNC_TIMEOUT', 300); // 5 minutes max
define('CACHE_DURATION', 3600); // 1 heure

// ===========================
// Mode debug (basé sur ENV)
// ===========================
define('DEBUG_MODE', ENV === 'development');

// ===========================
// Logs
// ===========================
define('LOG_SYNC', true);
define('LOG_ERRORS', true);
define('LOG_MAX_SIZE', 10485760); // 10 MB