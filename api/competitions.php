<?php
declare(strict_types=1);

/**
 * API Compétitions
 * 
 * GET:
 * - /api/competitions - Toutes les compétitions (saison actuelle)
 * - /api/competitions?season=2024 - Compétitions d'une saison
 * - /api/competitions?id=123 - Compétition par ID
 * - /api/competitions?cp_no=456 - Compétition par numéro API
 * - /api/competitions?type=CH - Par type (CH=Championnat, CO=Coupe)
 * - /api/competitions?championnats=true - Championnats uniquement
 * - /api/competitions?coupes=true - Coupes uniquement
 */

$basePath = dirname(__DIR__);
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
    $competitionsModel = $models['competitions'];
    
    switch ($method) {
        case 'GET':
            handleGet($competitionsModel);
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
        'cp_no' => null,
        'type' => null,
        'championnats' => null,
        'coupes' => null,
        'season' => null
    ]);
    
    $season = $params['season'] !== null ? (int)$params['season'] : null;
    
    // Compétition par ID
    if ($params['id'] !== null) {
        $competition = $model->getCompetitionById((int)$params['id']);
        ApiResponse::success($competition);
    }
    
    // Compétition par cp_no
    if ($params['cp_no'] !== null) {
        $competition = $model->getCompetitionByCpNo((int)$params['cp_no'], $season);
        ApiResponse::success($competition);
    }
    
    // Championnats uniquement
    if ($params['championnats'] !== null) {
        $competitions = $model->getChampionnats($season);
        ApiResponse::success($competitions, ['type' => 'championnats']);
    }
    
    // Coupes uniquement
    if ($params['coupes'] !== null) {
        $competitions = $model->getCoupes($season);
        ApiResponse::success($competitions, ['type' => 'coupes']);
    }
    
    // Par type
    if ($params['type'] !== null) {
        $competitions = $model->getCompetitionsByType($params['type'], $season);
        ApiResponse::success($competitions, ['type' => $params['type']]);
    }
    
    // Toutes les compétitions (par défaut)
    $competitions = $model->getAllCompetitions($season);
    ApiResponse::success($competitions);
}