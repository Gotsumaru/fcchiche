<?php
declare(strict_types=1);

/**
 * API Club - Informations FC Chiche
 * 
 * GET:
 * - /api/club - Toutes les infos du club
 * - /api/club?essentials=true - Infos essentielles uniquement
 * - /api/club?logo=true - Logo uniquement
 * - /api/club?exists=true - Vérifier existence
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
    $clubModel = $models['club'];
    
    switch ($method) {
        case 'GET':
            handleGet($clubModel);
            break;
            
        case 'PUT':
            handlePut($clubModel);
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
        'essentials' => null,
        'logo' => null,
        'exists' => null
    ]);
    
    // Logo uniquement
    if ($params['logo'] !== null) {
        $logo = $model->getClubLogo();
        ApiResponse::success(['logo_url' => $logo]);
    }
    
    // Vérifier existence
    if ($params['exists'] !== null) {
        $exists = $model->exists();
        ApiResponse::success(['exists' => $exists]);
    }
    
    // Infos essentielles
    if ($params['essentials'] !== null) {
        $club = $model->getClubEssentials();
        ApiResponse::success($club);
    }
    
    // Toutes les infos (par défaut)
    $club = $model->getClub();
    ApiResponse::success($club);
}

/**
 * Gérer requêtes PUT (mise à jour)
 */
function handlePut($model): void
{
    $data = ApiResponse::getBody();
    
    if ($data === null) {
        ApiResponse::error('Invalid JSON body', 400);
    }
    
    // TODO: Implémenter mise à jour infos club (si nécessaire)
    ApiResponse::error('Not implemented', 501);
}