<?php
/**
 * Debug API - Vérification chemins et config
 */


header('Content-Type: text/plain; charset=utf-8');

echo "=== DEBUG API FC CHICHE ===\n\n";

// 1. Chemin actuel
echo "1. Chemin actuel:\n";
echo "   __DIR__ = " . __DIR__ . "\n";
echo "   __FILE__ = " . __FILE__ . "\n\n";

// 2. Vérifier existence des fichiers
echo "2. Vérification fichiers:\n";

$files_to_check = [
    'config/bootstrap.php' => __DIR__ . '/../../config/bootstrap.php',
    'config/config.php' => __DIR__ . '/../../config/config.php',
    'config/database.php' => __DIR__ . '/../../config/database.php',
    'src/Models/MatchModel.php' => __DIR__ . '/../../src/Models/MatchModel.php',
    'src/Models/Stats.php' => __DIR__ . '/../../src/Models/Stats.php',
    'src/Models/Equipe.php' => __DIR__ . '/../../src/Models/Equipe.php',
    'src/Utils/Logger.php' => __DIR__ . '/../../src/Utils/Logger.php',
];

foreach ($files_to_check as $name => $path) {
    $exists = file_exists($path);
    $status = $exists ? '✓' : '✗';
    echo "   [{$status}] {$name}\n";
    echo "       Path: {$path}\n";
    if (!$exists) {
        echo "       ERREUR: Fichier introuvable!\n";
    }
}

echo "\n3. Test chargement config:\n";
try {
    require_once __DIR__ . '/../../config/bootstrap.php';
    echo "   ✓ config.php chargé via bootstrap\n";
    echo "   - DB_HOST: " . DB_HOST . "\n";
    echo "   - DB_NAME: " . DB_NAME . "\n";
    echo "   - API_FFF_CLUB_ID: " . API_FFF_CLUB_ID . "\n";
    echo "   - DEBUG_MODE: " . (DEBUG_MODE ? 'true' : 'false') . "\n";
} catch (Exception $e) {
    echo "   ✗ ERREUR: " . $e->getMessage() . "\n";
}

echo "\n4. Test connexion PDO:\n";
try {
    require_once __DIR__ . '/../../config/bootstrap.php';
    $pdo = Database::getInstance();
    echo "   ✓ PDO connecté\n";
    
    // Test requête simple
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM " . DB_PREFIX . "club");
    $result = $stmt->fetch();
    echo "   ✓ Requête test OK (club count: " . $result['count'] . ")\n";
} catch (Exception $e) {
    echo "   ✗ ERREUR: " . $e->getMessage() . "\n";
}



echo "\n6. PHP Info:\n";
echo "   - Version PHP: " . PHP_VERSION . "\n";
echo "   - Extensions chargées:\n";
$required_extensions = ['pdo', 'pdo_mysql', 'json', 'curl'];
foreach ($required_extensions as $ext) {
    $loaded = extension_loaded($ext);
    $status = $loaded ? '✓' : '✗';
    echo "     [{$status}] {$ext}\n";
}

echo "\n=== FIN DEBUG ===\n";