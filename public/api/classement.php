<?php

// Forcer affichage erreurs
error_reporting(E_ALL);
ini_set('display_errors', '1');
declare(strict_types=1);

/**
 * API Classement - FC Chiche
 * Endpoint pour récupérer le classement d'une équipe
 * 
 * Note: L'API FFF ne fournit pas directement les classements
 * Ce endpoint retourne un message temporaire en attendant
 * l'intégration avec une source de classement
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

try {
    // Récupérer paramètres
    $equipe = $_GET['equipe'] ?? null;
    
    if (!$equipe) {
        throw new Exception('Paramètre équipe requis');
    }
    
    // TODO: Intégrer source de classement (scraping FFF ou calcul depuis matchs)
    // Pour l'instant, retour message temporaire
    
    echo json_encode([
        'success' => false,
        'error' => 'Classement non disponible',
        'message' => 'Le classement sera disponible prochainement. L\'API FFF ne fournit pas cette donnée directement.',
        'equipe' => $equipe,
        'classement' => []
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => DEBUG_MODE ? $e->getMessage() : 'Erreur serveur',
        'trace' => DEBUG_MODE ? $e->getTraceAsString() : null
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}