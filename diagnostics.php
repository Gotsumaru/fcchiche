<?php
/**
 * Fichier de diagnostic - À SUPPRIMER après vérification
 * Accessible via: https://www.preprod.fcchiche.fr/diagnostics.php
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "=== DIAGNOSTIC COMPLET ===\n\n";

echo "1. Chemins:\n";
echo "   CWD: " . getcwd() . "\n";
echo "   FILE: " . __FILE__ . "\n\n";

echo "2. Fichiers critiques:\n";
echo "   config/loadenv.php: " . (file_exists('config/loadenv.php') ? "✅" : "❌") . "\n";
echo "   config/config.php: " . (file_exists('config/config.php') ? "✅" : "❌") . "\n";
echo "   public/api/config.php: " . (file_exists('public/api/config.php') ? "✅" : "❌") . "\n";
echo "   public/dist/index.html: " . (file_exists('public/dist/index.html') ? "✅" : "❌") . "\n";
echo "   .env.local: " . (file_exists('.env.local') ? "✅" : "❌") . "\n\n";

echo "3. Variables d'environnement:\n";
require 'config/loadenv.php';
echo "   DB_HOST: " . (getenv('DB_HOST') ?: "❌") . "\n";
echo "   DB_NAME: " . (getenv('DB_NAME') ?: "❌") . "\n";
echo "   DB_USER: " . (getenv('DB_USER') ?: "❌") . "\n";
echo "   DB_PASS: " . (getenv('DB_PASS') ? "✅ DÉFINI" : "❌") . "\n\n";

echo "4. Test connexion BD:\n";
try {
    require 'config/config.php';
    require 'config/database.php';
    $db = Database::getInstance();
    echo "   ✅ BD connectée\n";
    echo "   Version MySQL: " . $db->getAttribute(PDO::ATTR_SERVER_VERSION) . "\n\n";
} catch (Exception $e) {
    echo "   ❌ Erreur: " . $e->getMessage() . "\n\n";
}

echo "5. Test API:\n";
echo "   GET /api/config.php: ";
$response = @file_get_contents('http://localhost/api/config.php');
if ($response) {
    echo "✅ Accessible\n";
} else {
    echo "❌ Non accessible (essayer via le navigateur)\n";
}

echo "\n✅ Diagnostic complet\n";
echo "\n⚠️ À SUPPRIMER après diagnostic (rm diagnostics.php)\n";
?>
