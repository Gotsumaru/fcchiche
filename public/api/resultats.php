<?php
declare(strict_types=1);

// Forcer affichage erreurs
error_reporting(E_ALL);
ini_set('display_errors', '1');

/**
 * API Résultats - FC Chiche
 * Endpoint pour récupérer les derniers résultats
 */

$basePath = dirname(__DIR__, 2);
require_once $basePath . '/config/bootstrap.php';
require_once $basePath . '/src/Models/MatchModel.php';
require_once $basePath . '/src/Models/Stats.php';



try {
    // Récupérer paramètres
    $equipe = $_GET['equipe'] ?? null;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    
    // Validation
    assert($limit > 0 && $limit <= 50, 'Limit must be between 1 and 50');
    
    $matchModel = new MatchModel();
    $statsModel = new Stats();
    
    // Parser l'équipe (format "SEM 1" => category="SEM", number=1)
    $category = null;
    $number = null;
    
    if ($equipe && $equipe !== 'Toutes les équipes') {
        $parts = explode(' ', $equipe);
        if (count($parts) === 2) {
            $category = $parts[0];
            $number = (int)$parts[1];
        }
    }
    
    // Récupérer résultats
    $resultats = $matchModel->getLastResults($limit, $category, $number);
    
    // Récupérer stats si équipe spécifique
    $stats = null;
    if ($category && $number) {
        $stats = $statsModel->getTeamStats($category, $number);
    }
    
    // Réponse JSON
    echo json_encode([
        'success' => true,
        'resultats' => $resultats,
        'stats' => $stats,
        'equipe' => $equipe,
        'count' => count($resultats)
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => DEBUG_MODE ? $e->getMessage() : 'Erreur serveur',
        'trace' => DEBUG_MODE ? $e->getTraceAsString() : null
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}