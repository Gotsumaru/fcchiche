<?php
declare(strict_types=1);

/**
 * Script CRON - Synchronisation données API FFF
 * Compatible hébergement mutualisé OVH
 * 
 * Configuration CRON OVH : /cron/sync_data.php (sans paramètres)
 * Fréquence : 2 fois par jour (8h et 20h)
 */

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../src/Database/Sync.php';
require_once __DIR__ . '/../src/Utils/Logger.php';

// Headers pour éviter timeout navigateur si appelé manuellement
header('Content-Type: text/plain; charset=utf-8');

// Timeout maximum
set_time_limit(SYNC_TIMEOUT);

$logger = new Logger('cron.log');
$start_time = microtime(true);

echo "[" . date('Y-m-d H:i:s') . "] Début synchronisation\n";

try {
    // Vérifier si synchronisation activée
    if (!SYNC_ENABLED) {
        $logger->warning('Synchronisation désactivée dans configuration');
        echo "Synchronisation désactivée\n";
        exit(0);
    }
    
    $logger->info('Début synchronisation CRON');
    
    // Créer instance synchronisation
    $sync = new Sync();
    
    // Exécuter synchronisation
    $stats = $sync->syncAll();
    
    // Calculer temps exécution
    $execution_time = round((microtime(true) - $start_time) * 1000);
    
    // Logger résultats
    if ($stats['success']) {
        $logger->info('Synchronisation CRON réussie', [
            'club' => $stats['club'] ? 'OK' : 'SKIP',
            'equipes' => $stats['equipes'],
            'calendrier' => $stats['calendrier'],
            'resultats' => $stats['resultats'],
            'execution_time_ms' => $execution_time
        ]);
        
        echo "✓ Synchronisation réussie\n";
        echo "  - Club: " . ($stats['club'] ? 'OK' : 'SKIP') . "\n";
        echo "  - Équipes: {$stats['equipes']}\n";
        echo "  - Calendrier: {$stats['calendrier']}\n";
        echo "  - Résultats: {$stats['resultats']}\n";
        echo "  - Temps: {$execution_time}ms\n";
        
        exit(0);
    } else {
        $logger->error('Synchronisation CRON échouée', [
            'errors' => $stats['errors'],
            'execution_time_ms' => $execution_time
        ]);
        
        echo "✗ Synchronisation échouée\n";
        echo "Erreurs: " . implode(', ', $stats['errors']) . "\n";
        exit(1);
    }
    
} catch (Exception $e) {
    $execution_time = round((microtime(true) - $start_time) * 1000);
    
    $logger->error('Erreur critique synchronisation CRON', [
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'execution_time_ms' => $execution_time
    ]);
    
    echo "✗ Erreur critique: " . $e->getMessage() . "\n";
    exit(1);
}

echo "[" . date('Y-m-d H:i:s') . "] Fin synchronisation\n";