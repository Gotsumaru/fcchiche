<?php
declare(strict_types=1);

/**
 * API Terrains
 * 
 * GET:
 * - /api/terrains - Tous les terrains
 * - /api/terrains?id=1 - Terrain par ID
 * - /api/terrains?te_no=12345 - Terrain par numéro API
 * - /api/terrains?gps=true - Terrains avec coordonnées GPS
 */

$basePath = dirname(__DIR__, 2);
require_once $basePath . '/config/bootstrap.php';
require_once $basePath . '/src/Models/ModelsLoader.php';
require_once $basePath . '/src/Utils/ApiResponse.php';
require_once $basePath . '/src/Utils/ApiAuth.php';

ApiResponse::setCorsHeaders();

$method = $_SERVER['REQUEST_METHOD'];
ApiAuth::protectWrite($method);

try {
    $pdo = Database::getInstance();
    $models = ModelsLoader::loadAll($pdo);
    $terrainsModel = $models['terrains'];
    
    switch ($method) {
        case 'GET':
            handleGet($terrainsModel);
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
        'id' => null,
        'te_no' => null,
        'gps' => null
    ]);
    
    // Terrain par ID
    if ($params['id'] !== null) {
        $terrain = $model->getTerrainById((int)$params['id']);
        ApiResponse::success($terrain);
    }
    
    // Terrain par te_no
    if ($params['te_no'] !== null) {
        $terrain = $model->getTerrainByTeNo((int)$params['te_no']);
        ApiResponse::success($terrain);
    }
    
    // Terrains avec GPS
    if ($params['gps'] !== null) {
        $terrains = $model->getTerrainsWithGPS();
        ApiResponse::success($terrains, ['filter' => 'with_gps']);
    }
    
    // Tous les terrains (par défaut)
    $terrains = $model->getAllTerrains();
    ApiResponse::success($terrains);
}