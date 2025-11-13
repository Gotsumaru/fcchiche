<?php
declare(strict_types=1);

/**
 * API Classements - Historisés par journée
 * 
 * GET:
 * - /api/classements?competition_id=123 - Classement actuel
 * - /api/classements?competition_id=123&journee=10 - Journée spécifique
 * - /api/classements?competitions=true - Liste des compétitions avec classement
 * - /api/classements?competition_id=123&position=true - Position FC Chiche
 * - /api/classements?competition_id=123&history=true - Évolution position
 * - /api/classements?competition_id=123&stats=true - Statistiques club
 * - /api/classements?competition_id=123&compare=5,10 - Comparer journées
 * - /api/classements?competition_id=123&journees_count=true - Nombre de journées
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
    $classementsModel = $models['classements'];
    
    switch ($method) {
        case 'GET':
            handleGet($classementsModel);
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
        'competition_id' => null,
        'journee' => null,
        'competitions' => null,
        'position' => null,
        'history' => null,
        'stats' => null,
        'compare' => null,
        'journees_count' => null,
        'season' => null
    ]);
    
    $season = $params['season'] !== null ? (int)$params['season'] : null;
    
    // Liste des compétitions avec classement
    if ($params['competitions'] !== null) {
        $competitions = $model->getCompetitionsWithClassement($season);
        ApiResponse::success($competitions);
    }
    
    // Vérifier competition_id pour les autres requêtes
    if ($params['competition_id'] === null) {
        ApiResponse::error('Missing competition_id parameter', 400);
    }
    
    $competitionId = (int)$params['competition_id'];
    
    // Nombre de journées
    if ($params['journees_count'] !== null) {
        $count = $model->getJourneesCount($competitionId, $season);
        ApiResponse::success(['count' => $count], ['competition_id' => $competitionId]);
    }
    
    // Position FC Chiche
    if ($params['position'] !== null) {
        $position = $model->getClubPosition($competitionId, $season);
        ApiResponse::success($position, ['competition_id' => $competitionId]);
    }
    
    // Évolution de position
    if ($params['history'] !== null) {
        $history = $model->getPositionHistory($competitionId, $season);
        ApiResponse::success($history, ['competition_id' => $competitionId]);
    }
    
    // Statistiques du club
    if ($params['stats'] !== null) {
        $stats = $model->getClubStats($competitionId, $season);
        ApiResponse::success($stats, ['competition_id' => $competitionId]);
    }
    
    // Comparer deux journées
    if ($params['compare'] !== null) {
        $journees = explode(',', $params['compare']);
        if (count($journees) !== 2) {
            ApiResponse::error('Compare parameter must contain exactly 2 journees (e.g., compare=5,10)', 400);
        }
        $evolution = $model->compareJournees($competitionId, (int)$journees[0], (int)$journees[1], $season);
        ApiResponse::success($evolution, ['competition_id' => $competitionId, 'journees' => $journees]);
    }
    
    // Classement d'une journée spécifique
    if ($params['journee'] !== null) {
        $classement = $model->getClassementByJournee($competitionId, (int)$params['journee'], $season);
        ApiResponse::success($classement, ['competition_id' => $competitionId, 'journee' => (int)$params['journee']]);
    }
    
    // Classement actuel (par défaut)
    $classement = $model->getCurrentClassement($competitionId, $season);
    ApiResponse::success($classement, ['competition_id' => $competitionId, 'type' => 'current']);
}