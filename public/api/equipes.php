<?php
declare(strict_types=1);

/**
 * API Équipes
 * 
 * GET:
 * - /api/equipes - Toutes les équipes
 * - /api/equipes?id=1 - Équipe par ID
 * - /api/equipes?category=SEM - Équipes par catégorie
 * - /api/equipes?short_name=SEM%201 - Équipe par nom court
 * - /api/equipes?seniors=true - Équipes seniors uniquement
 * - /api/equipes?jeunes=true - Équipes jeunes uniquement
 * - /api/equipes?categories=true - Liste des catégories
 * - /api/equipes?include_hidden=true - Inclure équipes non-diffusables
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
    $equipesModel = $models['equipes'];
    
    switch ($method) {
        case 'GET':
            handleGet($equipesModel);
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
        'category' => null,
        'short_name' => null,
        'seniors' => null,
        'jeunes' => null,
        'categories' => null,
        'include_hidden' => false,
        'season' => null
    ]);
    
    $season = $params['season'] !== null ? (int)$params['season'] : null;
    $includeHidden = filter_var($params['include_hidden'], FILTER_VALIDATE_BOOLEAN);
    
    // Équipe par ID
    if ($params['id'] !== null) {
        $equipe = $model->getEquipeById((int)$params['id']);
        ApiResponse::success($equipe);
    }
    
    // Équipes par catégorie
    if ($params['category'] !== null) {
        $equipes = $model->getEquipesByCategory($params['category'], $season, !$includeHidden);
        ApiResponse::success($equipes, ['category' => $params['category']]);
    }
    
    // Équipe par short_name
    if ($params['short_name'] !== null) {
        $equipe = $model->getEquipeByShortName($params['short_name'], $season);
        ApiResponse::success($equipe);
    }
    
    // Équipes seniors
    if ($params['seniors'] !== null) {
        $equipes = $model->getEquipesSeniors($season, !$includeHidden);
        ApiResponse::success($equipes, ['type' => 'seniors']);
    }
    
    // Équipes jeunes
    if ($params['jeunes'] !== null) {
        $equipes = $model->getEquipesJeunes($season, !$includeHidden);
        ApiResponse::success($equipes, ['type' => 'jeunes']);
    }
    
    // Liste des catégories
    if ($params['categories'] !== null) {
        $categories = $model->getCategories($season);
        ApiResponse::success($categories);
    }
    
    // Toutes les équipes (par défaut)
    $equipes = $model->getAllEquipes($season, !$includeHidden);
    ApiResponse::success($equipes);
}