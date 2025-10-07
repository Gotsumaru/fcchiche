<?php
declare(strict_types=1);

// Forcer affichage erreurs
error_reporting(E_ALL);
ini_set('display_errors', '1');

/**
 * API Club - FC Chiche
 * Endpoint pour récupérer les informations du club
 */

$basePath = dirname(__DIR__, 2);
require_once $basePath . '/config/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

try {
    $pdo = Database::getInstance();
    
    // Récupérer infos du club
    $sql = "SELECT 
        cl_no,
        affiliation_number,
        name,
        short_name,
        location,
        colors,
        address1,
        address2,
        address3,
        postal_code,
        distributor_office,
        latitude,
        longitude,
        logo_url,
        district_name,
        district_cg_no
    FROM " . DB_PREFIX . "club
    WHERE cl_no = :cl_no
    LIMIT 1";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['cl_no' => API_FFF_CLUB_ID]);
    $club = $stmt->fetch();
    
    if (!$club) {
        throw new Exception('Club non trouvé');
    }
    
    // Réponse JSON
    echo json_encode([
        'success' => true,
        'club' => $club
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => DEBUG_MODE ? $e->getMessage() : 'Erreur serveur',
        'trace' => DEBUG_MODE ? $e->getTraceAsString() : null
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}