<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

/**
 * API Classement - FC Chiche
 * Endpoint pour récupérer le classement d'une équipe
 */

$basePath = dirname(__DIR__, 2);
require_once $basePath . '/config/bootstrap.php';
require_once $basePath . '/src/Models/Classement.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

try {
    // Récupérer paramètre équipe
    $equipe = $_GET['equipe'] ?? null;
    
    if (!$equipe) {
        throw new Exception('Paramètre équipe requis');
    }
    
    // Parser format "SEM 1", "U17 4", etc.
    $parts = explode(' ', $equipe);
    
    if (count($parts) < 2) {
        throw new Exception('Format équipe invalide. Attendu: "SEM 1", "U17 4", etc.');
    }
    
    $category = $parts[0];
    $number = (int)$parts[1];
    
    if ($number <= 0) {
        throw new Exception('Numéro d\'équipe invalide');
    }
    
    // Récupérer classement
    $classementModel = new Classement();
    $classement = $classementModel->getLatestClassement($category, $number);
    
    if (empty($classement)) {
        echo json_encode([
            'success' => false,
            'error' => 'Classement non disponible',
            'message' => 'Aucun classement trouvé pour cette équipe. Le classement est disponible uniquement pour les championnats.',
            'equipe' => $equipe,
            'classement' => []
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
    
    // Extraire infos de la poule
    $pouleInfo = [
        'journee' => $classement[0]['journee_num'] ?? null,
        'date' => $classement[0]['journee_date'] ?? null,
        'poule' => $classement[0]['poule_name'] ?? null
    ];
    
    // Réponse succès
    echo json_encode([
        'success' => true,
        'equipe' => $equipe,
        'classement' => $classement,
        'count' => count($classement),
        'info' => $pouleInfo
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => DEBUG_MODE ? $e->getMessage() : 'Erreur serveur',
        'trace' => DEBUG_MODE ? $e->getTraceAsString() : null
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}