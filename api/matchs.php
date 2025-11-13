<?php
declare(strict_types=1);

/**
 * API Matchs - Calendrier et résultats
 * 
 * GET:
 * - /api/matchs - Liste des matchs
 * - /api/matchs?id=123 - Match par ID
 * - /api/matchs?ma_no=456 - Match par numéro API
 * - /api/matchs?upcoming=10 - Prochains matchs
 * - /api/matchs?last_results=10 - Derniers résultats
 * - /api/matchs?competition_id=123&is_result=true
 * - /api/matchs?category=SEM&is_result=false
 * - /api/matchs?home=true&is_result=false
 * - /api/matchs?journee=10&competition_id=123
 * - /api/matchs?date_from=2025-01-01&date_to=2025-12-31
 * 
 * POST/PUT/DELETE: Protégés authentification admin
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
    $matchsModel = $models['matchs'];
    
    switch ($method) {
        case 'GET':
            handleGet($matchsModel);
            break;
            
        case 'POST':
            handlePost($matchsModel);
            break;
            
        case 'PUT':
            handlePut($matchsModel);
            break;
            
        case 'DELETE':
            handleDelete($matchsModel);
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
        'ma_no' => null,
        'upcoming' => null,
        'last_results' => null,
        'competition_id' => null,
        'category' => null,
        'is_result' => null,
        'home' => null,
        'away' => null,
        'journee' => null,
        'date_from' => null,
        'date_to' => null,
        'limit' => 20,
        'equipe_id' => null,
        'competition_type' => null
    ]);

    $limit = (int)$params['limit'];
    assert($limit > 0 && $limit <= 100, 'Limit must be between 1 and 100');

    $competitionType = null;
    if ($params['competition_type'] !== null) {
        $candidate = strtoupper(trim((string)$params['competition_type']));
        assert(strlen($candidate) <= 2, 'Competition type length invalid');

        if ($candidate === '') {
            $competitionType = null;
        } elseif (!in_array($candidate, ['CH', 'CP'], true)) {
            ApiResponse::error('Invalid competition type', 400);
        } else {
            $competitionType = $candidate;
        }
    }

    assert($competitionType === null || strlen($competitionType) === 2, 'Competition type must be two characters');
    assert($competitionType === null || in_array($competitionType, ['CH', 'CP'], true), 'Competition type must be normalized');

    // Par équipe spécifique
    if ($params['equipe_id'] !== null) {
        $equipeId = (int)$params['equipe_id'];
        assert($equipeId > 0, 'Equipe ID must be positive');
        $isResult = $params['is_result'] !== null ? filter_var($params['is_result'], FILTER_VALIDATE_BOOLEAN) : null;
        $matchs = $model->getMatchsByEquipeId($equipeId, $isResult, $limit, $competitionType);
        ApiResponse::success($matchs, [
            'equipe_id' => $equipeId,
            'competition_type' => $competitionType
        ]);
    }

    // Match par ID
    if ($params['id'] !== null) {
        $match = $model->getMatchById((int)$params['id']);
        ApiResponse::success($match);
    }
    
    // Match par ma_no
    if ($params['ma_no'] !== null) {
        $match = $model->getMatchByMaNo((int)$params['ma_no']);
        ApiResponse::success($match);
    }
    
    // Prochains matchs
    if ($params['upcoming'] !== null) {
        $limit = (int)$params['upcoming'];
        $matchs = $model->getUpcomingMatchs($limit);
        ApiResponse::success($matchs, ['type' => 'upcoming']);
    }
    
    // Derniers résultats
    if ($params['last_results'] !== null) {
        $limit = (int)$params['last_results'];
        $matchs = $model->getLastResults($limit);
        ApiResponse::success($matchs, ['type' => 'results']);
    }
    
    // Par compétition
    if ($params['competition_id'] !== null) {
        $competitionId = (int)$params['competition_id'];
        $isResult = $params['is_result'] !== null ? filter_var($params['is_result'], FILTER_VALIDATE_BOOLEAN) : null;
        $matchs = $model->getMatchsByCompetition($competitionId, $isResult, $limit);
        ApiResponse::success($matchs, ['competition_id' => $competitionId]);
    }
    
    // Par catégorie d'équipe
    if ($params['category'] !== null) {
        $isResult = $params['is_result'] !== null ? filter_var($params['is_result'], FILTER_VALIDATE_BOOLEAN) : null;
        $matchs = $model->getMatchsByTeamCategory($params['category'], $isResult, $limit);
        ApiResponse::success($matchs, ['category' => $params['category']]);
    }
    
    // Domicile/Extérieur
    if ($params['home'] !== null) {
        $isResult = $params['is_result'] !== null ? filter_var($params['is_result'], FILTER_VALIDATE_BOOLEAN) : false;
        $matchs = $model->getHomeMatchs($isResult, $limit);
        ApiResponse::success($matchs, ['location' => 'home']);
    }
    
    if ($params['away'] !== null) {
        $isResult = $params['is_result'] !== null ? filter_var($params['is_result'], FILTER_VALIDATE_BOOLEAN) : false;
        $matchs = $model->getAwayMatchs($isResult, $limit);
        ApiResponse::success($matchs, ['location' => 'away']);
    }
    
    // Par journée
    if ($params['journee'] !== null && $params['competition_id'] !== null) {
        $matchs = $model->getMatchsByJournee((int)$params['journee'], (int)$params['competition_id']);
        ApiResponse::success($matchs, ['journee' => (int)$params['journee']]);
    }
    
    // Par période de dates
    if ($params['date_from'] !== null && $params['date_to'] !== null) {
        $isResult = $params['is_result'] !== null ? filter_var($params['is_result'], FILTER_VALIDATE_BOOLEAN) : null;
        $matchs = $model->getMatchsByDateRange($params['date_from'], $params['date_to'], $isResult, $limit);
        ApiResponse::success($matchs, ['date_range' => [$params['date_from'], $params['date_to']]]);
    }
    
    // Liste par défaut
    $isResult = $params['is_result'] !== null ? filter_var($params['is_result'], FILTER_VALIDATE_BOOLEAN) : null;
    $matchs = $model->getAllMatchs($isResult, $limit);
    ApiResponse::success($matchs);
}

/**
 * Gérer requêtes POST (création)
 */
function handlePost($model): void
{
    $data = ApiResponse::getBody();
    
    if ($data === null) {
        ApiResponse::error('Invalid JSON body', 400);
    }
    
    // TODO: Implémenter création de match (si nécessaire)
    ApiResponse::error('Not implemented', 501);
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
    
    // TODO: Implémenter mise à jour de match (si nécessaire)
    ApiResponse::error('Not implemented', 501);
}

/**
 * Gérer requêtes DELETE (suppression)
 */
function handleDelete($model): void
{
    $params = ApiResponse::getParams(['id' => null]);
    
    if ($params['id'] === null) {
        ApiResponse::error('Missing id parameter', 400);
    }
    
    // TODO: Implémenter suppression de match (si nécessaire)
    ApiResponse::error('Not implemented', 501);
}