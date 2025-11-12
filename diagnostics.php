<?php
/**
 * Fichier de diagnostic - À SUPPRIMER après vérification
 * Accessible via: https://www.preprod.fcchiche.fr/diagnostics.php
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "=== DIAGNOSTIC COMPLET ===\n\n";

// 1. Chemins
echo "1. Chemins:\n";
$baseDir = dirname(__FILE__);
echo "   CWD: " . getcwd() . "\n";
echo "   FILE: " . __FILE__ . "\n";
echo "   BASE DIR: " . $baseDir . "\n\n";

// 2. Vérification des fichiers critiques (avec chemins absolus)
echo "2. Fichiers critiques:\n";
$files = [
    'config/loadenv.php' => $baseDir . '/config/loadenv.php',
    'config/config.php' => $baseDir . '/config/config.php',
    'config/database.php' => $baseDir . '/config/database.php',
    'public/api/config.php' => $baseDir . '/public/api/config.php',
    'public/dist/index.html' => $baseDir . '/public/dist/index.html',
    '.env.local' => $baseDir . '/.env.local',
];

foreach ($files as $name => $path) {
    echo "   $name: " . (file_exists($path) ? "✅" : "❌") . "\n";
}
echo "\n";

// 3. Variables d'environnement
echo "3. Chargement .env.local:\n";
$envPath = $baseDir . '/.env.local';
if (file_exists($envPath)) {
    echo "   Fichier trouvé: ✅\n";
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (is_array($lines)) {
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) continue;
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                putenv(trim($key) . '=' . trim($value));
                $_ENV[trim($key)] = trim($value);
            }
        }
        echo "   Variables chargées: ✅\n\n";
    } else {
        echo "   Erreur lecture fichier: ❌\n\n";
    }
} else {
    echo "   Fichier .env.local: ❌ NON TROUVÉ\n\n";
}

echo "4. Vérification variables d'environnement:\n";
echo "   DB_HOST: " . (getenv('DB_HOST') ?: "❌ NON DÉFINI") . "\n";
echo "   DB_NAME: " . (getenv('DB_NAME') ?: "❌ NON DÉFINI") . "\n";
echo "   DB_USER: " . (getenv('DB_USER') ?: "❌ NON DÉFINI") . "\n";
echo "   DB_PASS: " . (getenv('DB_PASS') ? "✅ DÉFINI" : "❌ NON DÉFINI") . "\n\n";

// 4. Test connexion BD (optionnel, avec gestion d'erreur)
echo "5. Test connexion base de données:\n";
$dbPath = $baseDir . '/config/database.php';
if (file_exists($dbPath)) {
    try {
        // Charger les variables d'env d'abord
        $loadenvPath = $baseDir . '/config/loadenv.php';
        if (file_exists($loadenvPath)) {
            require_once $loadenvPath;
        }

        // Charger config
        $configPath = $baseDir . '/config/config.php';
        if (file_exists($configPath)) {
            require_once $configPath;
        }

        // Charger database
        require_once $dbPath;

        if (class_exists('Database')) {
            $db = Database::getInstance();
            echo "   ✅ BD connectée\n";
            echo "   Version MySQL: " . $db->getAttribute(PDO::ATTR_SERVER_VERSION) . "\n\n";
        } else {
            echo "   ❌ Classe Database non trouvée\n\n";
        }
    } catch (Exception $e) {
        echo "   ❌ Erreur: " . $e->getMessage() . "\n\n";
    } catch (Throwable $t) {
        echo "   ❌ Erreur fatale: " . $t->getMessage() . "\n\n";
    }
} else {
    echo "   ❌ Fichier config/database.php non trouvé\n\n";
}

echo "6. Résumé:\n";
echo "   ✅ Diagnostic complet exécuté avec succès\n";
echo "   ⚠️  À SUPPRIMER après diagnostic (rm diagnostics.php)\n";
?>
