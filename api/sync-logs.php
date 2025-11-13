<?php
declare(strict_types=1);

/**
 * API Sync Logs - Logs de synchronisation
 * 
 * ⚠️ ADMIN UNIQUEMENT - Authentification requise pour toutes les méthodes
 * 
 * GET:
 * - /api/sync-logs - Tous les logs
 * - /api/sync-logs?endpoint=club - Logs d'un endpoint
 * - /api/sync-logs?status=error - Logs par statut (success/error/warning)
 * - /api/sync-logs?errors=true - Erreurs uniquement
 * - /api/sync-logs?date_from=2025-01-01&date_to=2025-12-31
 * - /api/sync-logs?today=true - Logs du jour
 * - /api/sync-logs?endpoint=club&status=error
 * - /api/sync-logs?stats=true - Statistiques globales
 * - /api/sync-logs?stats=club - Statistiques d'un endpoint
 * - /api/sync-logs?all_stats=true - Stats de tous les endpoints
 * - /api/sync-logs?last=true - Dernier log
 * - /api/sync-logs?last=club - Dernier log d'un endpoint
 * - /api/sync-logs?search=erreur - Recherche dans les logs
 * - /api/sync-logs?slowest=10 - Logs les plus lents
 * - /api/sync-logs?fastest=10 - Logs les plus rapides
 * - /api/sync-logs?limit=50 - Limiter le nombre de résultats
 */

$basePath = dirname(__DIR__);
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
    $syncLogsModel = $models['sync_logs'];
    
    switch ($method) {
        case 'GET':
            handleGet($syncLogsModel);
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
        'endpoint' => null,
        'status' => null,
        'errors' => null,
        'date_from' => null,
        'date_to' => null,
        'today' => null,
        'stats' => null,
        'all_stats' => null,
        'last' => null,
        'search' => null,
        'slowest' => null,
        'fastest' => null,
        'limit' => 100
    ]);
    
    $limit = (int)$params['limit'];
    assert($limit > 0 && $limit <= 500, 'Limit must be between 1 and 500');
    
    // Statistiques globales
    if ($params['stats'] === 'true') {
        $stats = $model->getStats();
        ApiResponse::success($stats);
    }
    
    // Stats d'un endpoint
    if ($params['stats'] !== null && $params['stats'] !== 'true') {
        $stats = $model->getStatsByEndpoint($params['stats']);
        ApiResponse::success($stats, ['endpoint' => $params['stats']]);
    }
    
    // Stats de tous les endpoints
    if ($params['all_stats'] !== null) {
        $stats = $model->getAllEndpointsStats();
        ApiResponse::success($stats);
    }
    
    // Dernier log
    if ($params['last'] === 'true') {
        $log = $model->getLastLog();
        ApiResponse::success($log);
    }
    
    // Dernier log d'un endpoint
    if ($params['last'] !== null && $params['last'] !== 'true') {
        $log = $model->getLastLog($params['last']);
        ApiResponse::success($log, ['endpoint' => $params['last']]);
    }
    
    // Recherche
    if ($params['search'] !== null) {
        $logs = $model->searchLogs($params['search'], $limit);
        ApiResponse::success($logs, ['search' => $params['search']]);
    }
    
    // Logs les plus lents
    if ($params['slowest'] !== null) {
        $limit = (int)$params['slowest'];
        $logs = $model->getSlowestLogs($limit);
        ApiResponse::success($logs, ['type' => 'slowest']);
    }
    
    // Logs les plus rapides
    if ($params['fastest'] !== null) {
        $limit = (int)$params['fastest'];
        $logs = $model->getFastestLogs($limit);
        ApiResponse::success($logs, ['type' => 'fastest']);
    }
    
    // Erreurs uniquement
    if ($params['errors'] !== null) {
        $logs = $model->getErrors($limit);
        ApiResponse::success($logs, ['filter' => 'errors_only']);
    }
    
    // Logs du jour
    if ($params['today'] !== null) {
        $logs = $model->getTodayLogs($limit);
        ApiResponse::success($logs, ['filter' => 'today']);
    }
    
    // Par période de dates
    if ($params['date_from'] !== null && $params['date_to'] !== null) {
        $logs = $model->getLogsByDateRange($params['date_from'], $params['date_to'], $limit);
        ApiResponse::success($logs, ['date_range' => [$params['date_from'], $params['date_to']]]);
    }
    
    // Par endpoint et statut
    if ($params['endpoint'] !== null && $params['status'] !== null) {
        $logs = $model->getLogsByEndpointAndStatus($params['endpoint'], $params['status'], $limit);
        ApiResponse::success($logs, ['endpoint' => $params['endpoint'], 'status' => $params['status']]);
    }
    
    // Par endpoint
    if ($params['endpoint'] !== null) {
        $logs = $model->getLogsByEndpoint($params['endpoint'], $limit);
        ApiResponse::success($logs, ['endpoint' => $params['endpoint']]);
    }
    
    // Par statut
    if ($params['status'] !== null) {
        $logs = $model->getLogsByStatus($params['status'], $limit);
        ApiResponse::success($logs, ['status' => $params['status']]);
    }
    
    // Tous les logs (par défaut)
    $logs = $model->getAllLogs($limit);
    ApiResponse::success($logs);
}