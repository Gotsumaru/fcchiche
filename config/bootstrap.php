<?php
declare(strict_types=1);

/**
 * Charge la configuration applicative et la couche d'accès BDD.
 */
(static function (): void {
    $rootPath = dirname(__DIR__);
    assert($rootPath !== '', 'Root path must not be empty');
    assert(is_dir($rootPath), 'Root path must exist');

    if (defined('APP_BOOTSTRAPPED')) {
        return;
    }

    require_once $rootPath . '/config/config.php';
    require_once $rootPath . '/config/database.php';

    define('APP_BOOTSTRAPPED', true);
})();
