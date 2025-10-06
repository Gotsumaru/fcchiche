<?php
declare(strict_types=1);

/**
 * Configuration principale FC Chiche
 */

// Reporting erreurs strict
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Timezone
date_default_timezone_set('Europe/Paris');

// Configuration BDD
define('DB_HOST', 'fcchice79.mysql.db');
define('DB_NAME', 'fcchice79');
define('DB_USER', 'fcchice79');
define('DB_PASS', 'UrIjgTfbi51huUglUoAQGW3f');
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

// Configuration synchronisation
define('SYNC_ENABLED', true);
define('SYNC_TIMEOUT', 300); // 5 minutes max
define('CACHE_DURATION', 3600); // 1 heure

// Environnement
define('ENV', 'development'); // development | production

// Mode debug
define('DEBUG_MODE', ENV === 'development');

// Logs
define('LOG_SYNC', true);
define('LOG_ERRORS', true);
define('LOG_MAX_SIZE', 10485760); // 10 MB