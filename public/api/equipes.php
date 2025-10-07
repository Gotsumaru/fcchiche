<?php
// Forcer affichage erreurs
error_reporting(E_ALL);
ini_set('display_errors', '1');
declare(strict_types=1);

/**
 * API Équipes - FC Chiche
 * Endpoint pour récupérer la liste des équipes
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Models/Equipe.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

try {
    $equipeModel = new Equipe();
    
    // Récupérer toutes les équipes
    $equipes = $equipeModel->getAllEquipes();
    
    // Réponse JSON
    echo json_encode([
        'success' => true,
        'equipes' => $equipes,
        'count' => count($equipes)
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => DEBUG_MODE ? $e->getMessage() : 'Erreur serveur',
        'trace' => DEBUG_MODE ? $e->getTraceAsString() : null
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}