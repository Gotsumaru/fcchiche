<?php
declare(strict_types=1);

/**
 * API Engagements - Pivot équipes-compétitions
 * 
 * GET:
 * - /api/engagements - Tous les engagements
 * - /api/engagements?equipe_id=1 - Engagements d'une équipe
 * - /api/engagements?competition_id=123 - Équipes dans une compétition
 * - /api/engagements?category=SEM - Engagements par catégorie
 * - /api/engagements?type=CH - Championnats ou coupes (CH/CO)
 * - /api/engagements?championnats=true - Engagements championnats
 * - /api/engagements?coupes=true - Engagements coupes
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
    $engagementsModel = $models['engagements'];
    
    switch ($method) {
        case 'GET':
            handleGet($engagementsModel);
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
        'equipe_id' => null,
        'competition_id' => null,
        'category' => null,
        'type' => null,
        'championnats' => null,
        'coupes' => null,
        'season' => null
    ]);
    
    $season = $params['season'] !== null ? (int)$params['season'] : null;
    
    // Engagements d'une équipe
    if ($params['equipe_id'] !== null) {
        $engagements = $model->getEngagementsByEquipe((int)$params['equipe_id'], $season);
        ApiResponse::success($engagements, ['equipe_id' => (int)$params['equipe_id']]);
    }
    
    // Équipes dans une compétition
    if ($params['competition_id'] !== null) {
        $engagements = $model->getEquipesByCompetition((int)$params['competition_id'], $season);
        ApiResponse::success($engagements, ['competition_id' => (int)$params['competition_id']]);
    }
    
    // Par catégorie
    if ($params['category'] !== null) {
        $engagements = $model->getEngagementsByCategory($params['category'], $season);
        ApiResponse::success($engagements, ['category' => $params['category']]);
    }
    
    // Championnats uniquement
    if ($params['championnats'] !== null) {
        $engagements = $model->getChampionnatEngagements($season);
        ApiResponse::success($engagements, ['type' => 'championnats']);
    }
    
    // Coupes uniquement
    if ($params['coupes'] !== null) {
        $engagements = $model->getCoupeEngagements($season);
        ApiResponse::success($engagements, ['type' => 'coupes']);
    }
    
    // Tous les engagements (par défaut)
    $engagements = $model->getAllEngagements($season);
    ApiResponse::success($engagements);
}