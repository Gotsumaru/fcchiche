<?php
declare(strict_types=1);

/**
 * API Config - Configuration système
 * 
 * ⚠️ ADMIN UNIQUEMENT - Authentification requise pour toutes les méthodes
 * 
 * GET:
 * - /api/config - Toutes les configurations
 * - /api/config?key=current_season - Config spécifique
 * - /api/config?keys=current_season,last_sync_club - Plusieurs configs
 * - /api/config?prefix=last_sync_ - Configs par préfixe
 * - /api/config?current_season=true - Saison actuelle
 * - /api/config?last_sync=club - Dernière sync d'un endpoint
 * - /api/config?all_last_sync=true - Toutes les dernières sync
 * 
 * PUT:
 * - /api/config (body: {key, value}) - Mettre à jour une config
 */

$basePath = dirname(__DIR__, 2);
require_once $basePath . '/config/bootstrap.php';
require_once $basePath . '/src/Models/ModelsLoader.php';
require_once $basePath . '/src/Utils/ApiResponse.php';
require_once $basePath . '/src/Utils/ApiAuth.php';

ApiResponse::setCorsHeaders();

// ⚠️ PROTECTION ADMIN OBLIGATOIRE
ApiAuth::requireAuth();

$method = $_SERVER['REQUEST_METHOD'];

try {
    $pdo = Database::getInstance();
    $models = ModelsLoader::loadAll($pdo);
    $configModel = $models['config'];
    
    switch ($method) {
        case 'GET':
            handleGet($configModel);
            break;
            
        case 'PUT':
            handlePut($configModel);
            break;
            
        default:
            ApiResponse::error('Method not allowed', 405);
    }
    
} catch (Exception $e) {
    ApiResponse::error(
        DEBUG_MODE ? $e->getMessage() : 'Internal server error',
        500,
        DEBUG_MODE ? ['trace' => $e->getTraceAsString()] : []
    );
}

/**
 * Gérer requêtes GET
 */
function handleGet($model): void
{
    $params = ApiResponse::getParams([
        'key' => null,
        'keys' => null,
        'prefix' => null,
        'current_season' => null,
        'last_sync' => null,
        'all_last_sync' => null
    ]);
    
    // Config spécifique
    if ($params['key'] !== null) {
        $value = $model->get($params['key']);
        ApiResponse::success(['key' => $params['key'], 'value' => $value]);
    }
    
    // Plusieurs configs
    if ($params['keys'] !== null) {
        $keys = explode(',', $params['keys']);
        $configs = $model->getMultiple($keys);
        ApiResponse::success($configs);
    }
    
    // Par préfixe
    if ($params['prefix'] !== null) {
        $configs = $model->getByPrefix($params['prefix']);
        ApiResponse::success($configs, ['prefix' => $params['prefix']]);
    }
    
    // Saison actuelle
    if ($params['current_season'] !== null) {
        $season = $model->getCurrentSeason();
        ApiResponse::success(['current_season' => $season]);
    }
    
    // Dernière sync d'un endpoint
    if ($params['last_sync'] !== null) {
        $lastSync = $model->getLastSync($params['last_sync']);
        ApiResponse::success(['last_sync' => $lastSync], ['endpoint' => $params['last_sync']]);
    }
    
    // Toutes les dernières sync
    if ($params['all_last_sync'] !== null) {
        $allSyncs = $model->getAllLastSync();
        ApiResponse::success($allSyncs);
    }
    
    // Toutes les configs (par défaut)
    $configs = $model->getAll();
    ApiResponse::success($configs);
}

/**
 * Gérer requêtes PUT
 */
function handlePut($model): void
{
    $data = ApiResponse::getBody();
    
    if ($data === null || !isset($data['key']) || !isset($data['value'])) {
        ApiResponse::error('Missing key or value in request body', 400);
    }
    
    // TODO: Implémenter mise à jour config
    ApiResponse::error('Not implemented', 501);
}