<?php
declare(strict_types=1);

/**
 * API Membres - Bureau du club
 * 
 * GET:
 * - /api/membres - Tous les membres
 * - /api/membres?id=1 - Membre par ID
 * - /api/membres?titre=Président - Membres par titre/fonction
 * - /api/membres?search=Martin - Recherche par nom
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
    $membresModel = $models['membres'];
    
    switch ($method) {
        case 'GET':
            handleGet($membresModel);
            break;
            
        case 'POST':
        case 'PUT':
        case 'DELETE':
            ApiResponse::error('Not implemented', 501);
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
        'titre' => null,
        'search' => null
    ]);
    
    // Membre par ID
    if ($params['id'] !== null) {
        $membre = $model->getMembreById((int)$params['id']);
        ApiResponse::success($membre);
    }
    
    // Membres par titre
    if ($params['titre'] !== null) {
        $membres = $model->getMembresByTitre($params['titre']);
        ApiResponse::success($membres, ['titre' => $params['titre']]);
    }
    
    // Recherche
    if ($params['search'] !== null) {
        $membres = $model->searchMembres($params['search']);
        ApiResponse::success($membres, ['search' => $params['search']]);
    }
    
    // Tous les membres (par défaut)
    $membres = $model->getAllMembres();
    ApiResponse::success($membres);
}