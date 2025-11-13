<?php
declare(strict_types=1);

/**
 * API Clubs Cache - Clubs adverses
 * 
 * GET:
 * - /api/clubs-cache - Tous les clubs en cache
 * - /api/clubs-cache?cl_no=12345 - Club par cl_no
 * - /api/clubs-cache?logo=12345 - Logo d'un club
 * - /api/clubs-cache?search=Saint - Recherche par nom
 * - /api/clubs-cache?recent=10 - Clubs récents
 * - /api/clubs-cache?exists=12345 - Vérifier existence
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
    $clubsCacheModel = $models['clubs_cache'];
    
    switch ($method) {
        case 'GET':
            handleGet($clubsCacheModel);
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
        'cl_no' => null,
        'logo' => null,
        'search' => null,
        'recent' => null,
        'exists' => null,
        'limit' => 50
    ]);
    
    $limit = (int)$params['limit'];
    assert($limit > 0 && $limit <= 100, 'Limit must be between 1 and 100');
    
    // Club par cl_no
    if ($params['cl_no'] !== null) {
        $club = $model->getClubByClNo((int)$params['cl_no']);
        ApiResponse::success($club);
    }
    
    // Logo uniquement
    if ($params['logo'] !== null) {
        $logo = $model->getClubLogo((int)$params['logo']);
        ApiResponse::success(['logo_url' => $logo]);
    }
    
    // Vérifier existence
    if ($params['exists'] !== null) {
        $exists = $model->exists((int)$params['exists']);
        ApiResponse::success(['exists' => $exists]);
    }
    
    // Recherche
    if ($params['search'] !== null) {
        $clubs = $model->searchClubs($params['search'], $limit);
        ApiResponse::success($clubs, ['search' => $params['search']]);
    }
    
    // Clubs récents
    if ($params['recent'] !== null) {
        $limit = (int)$params['recent'];
        $clubs = $model->getRecentClubs($limit);
        ApiResponse::success($clubs, ['type' => 'recent']);
    }
    
    // Tous les clubs (par défaut)
    $clubs = $model->getAllClubs($limit);
    ApiResponse::success($clubs);
}