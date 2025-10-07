<?php
/**
 * API Calendrier - FC Chiche
 * Endpoint pour récupérer les prochains matchs
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Models/Match.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

try {
    // Récupérer paramètres
    $equipe = $_GET['equipe'] ?? null;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    
    // Validation
    assert($limit > 0 && $limit <= 50, 'Limit must be between 1 and 50');
    
    $matchModel = new Match();
    
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
    
    // Récupérer matchs à venir
    $matchs = $matchModel->getUpcomingMatches($limit, $category, $number);
    
    // Réponse JSON
    echo json_encode([
        'success' => true,
        'matchs' => $matchs,
        'equipe' => $equipe,
        'count' => count($matchs)
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => DEBUG_MODE ? $e->getMessage() : 'Erreur serveur',
        'trace' => DEBUG_MODE ? $e->getTraceAsString() : null
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}