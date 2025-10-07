<?php
declare(strict_types=1);

/**
 * Debug détaillé des engagements et leurs matchs
 * 
 * Usage: php debug_engagements.php
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/API/FFFApiClient.php';
require_once __DIR__ . '/../src/Utils/Logger.php';

$logger = new Logger('debug.log');
$api = new FFFApiClient(5403, $logger);

echo "================================================================================\n";
echo "DEBUG DÉTAILLÉ DES ENGAGEMENTS\n";
echo "================================================================================\n\n";

$engagements = $api->getEngagements();

if ($engagements === null || !isset($engagements['hydra:member'])) {
    echo "ERREUR : Impossible de récupérer les engagements\n";
    exit(1);
}

$total_engagements = count($engagements['hydra:member']);
echo "Total engagements : {$total_engagements}\n\n";

$summary = [];
$problematic = [];

foreach ($engagements['hydra:member'] as $idx => $engagement) {
    $equipe = $engagement['equipe'] ?? [];
    $competition = $engagement['competition'] ?? [];
    $phase = $engagement['phase'] ?? [];
    $poule = $engagement['poule'] ?? [];
    
    $category = $equipe['category_code'] ?? 'N/A';
    $number = $equipe['number'] ?? 0;
    $cp_no = $competition['cp_no'] ?? 0;
    $cp_name = $competition['name'] ?? 'Unknown';
    $phase_no = $phase['number'] ?? 0;
    $poule_no = $poule['stage_number'] ?? 0;
    
    echo str_repeat("-", 80) . "\n";
    echo sprintf("[%d/%d] %s %d - %s\n", $idx + 1, $total_engagements, $category, $number, $cp_name);
    echo str_repeat("-", 80) . "\n";
    echo "  cp_no: {$cp_no}\n";
    echo "  phase: {$phase_no}\n";
    echo "  poule: {$poule_no}\n";
    
    // Construire l'URL exacte qui sera appelée
    $test_url = sprintf(
        'https://api-dofa.fff.fr/api/compets/%d/phases/%d/poules/%d/matchs?clNo=5403',
        $cp_no,
        $phase_no,
        $poule_no
    );
    
    echo "  URL: {$test_url}\n\n";
    
    // Tester la récupération des matchs
    echo "  Test récupération matchs...\n";
    $matchs = $api->getMatchsByCompetition($cp_no, $phase_no, $poule_no);
    
    if ($matchs === null) {
        echo "  ✗ ERREUR : Impossible de récupérer les matchs\n";
        $problematic[] = [
            'engagement' => "{$category} {$number} - {$cp_name}",
            'url' => $test_url,
            'error' => 'API returned null'
        ];
    } elseif (!isset($matchs['hydra:member'])) {
        echo "  ✗ ERREUR : Format de réponse invalide\n";
        $problematic[] = [
            'engagement' => "{$category} {$number} - {$cp_name}",
            'url' => $test_url,
            'error' => 'Invalid response format'
        ];
    } else {
        $match_count = count($matchs['hydra:member']);
        
        if ($match_count === 0) {
            echo "  ⚠ ATTENTION : 0 matchs retournés\n";
            $problematic[] = [
                'engagement' => "{$category} {$number} - {$cp_name}",
                'url' => $test_url,
                'error' => 'No matches returned (0 matchs)'
            ];
        } else {
            echo "  ✓ OK : {$match_count} matchs récupérés\n";
            
            // Compter résultats vs calendrier
            $results = 0;
            $calendar = 0;
            
            foreach ($matchs['hydra:member'] as $match) {
                $has_score = isset($match['home_score']) && $match['home_score'] !== null;
                if ($has_score) {
                    $results++;
                } else {
                    $calendar++;
                }
            }
            
            echo "    - Résultats : {$results}\n";
            echo "    - Calendrier: {$calendar}\n";
            
            // Afficher quelques détails des matchs
            if ($match_count > 0 && $match_count <= 3) {
                echo "    Détails:\n";
                foreach ($matchs['hydra:member'] as $match) {
                    $date = date('Y-m-d', strtotime($match['date']));
                    $home = $match['home']['short_name'] ?? 'N/A';
                    $away = $match['away']['short_name'] ?? 'N/A';
                    $score = isset($match['home_score']) 
                        ? "{$match['home_score']}-{$match['away_score']}"
                        : "à venir";
                    echo "      {$date}: {$home} vs {$away} ({$score})\n";
                }
            }
        }
        
        $summary[] = [
            'engagement' => "{$category} {$number} - {$cp_name}",
            'matchs' => $match_count,
            'resultats' => $results ?? 0,
            'calendrier' => $calendar ?? 0
        ];
    }
    
    echo "\n";
}

echo "================================================================================\n";
echo "RÉSUMÉ\n";
echo "================================================================================\n\n";

echo "Engagements avec matchs:\n";
echo str_repeat("-", 80) . "\n";
foreach ($summary as $item) {
    echo sprintf(
        "%-50s : %2d matchs (%d résultats, %d calendrier)\n",
        $item['engagement'],
        $item['matchs'],
        $item['resultats'],
        $item['calendrier']
    );
}

if (!empty($problematic)) {
    echo "\n================================================================================\n";
    echo "PROBLÈMES DÉTECTÉS\n";
    echo "================================================================================\n\n";
    
    foreach ($problematic as $problem) {
        echo "Engagement: {$problem['engagement']}\n";
        echo "  Erreur: {$problem['error']}\n";
        echo "  URL à tester manuellement:\n";
        echo "  {$problem['url']}\n\n";
    }
    
    echo "INSTRUCTIONS POUR TEST MANUEL:\n";
    echo str_repeat("-", 80) . "\n";
    echo "1. Ouvrir chaque URL ci-dessus dans votre navigateur\n";
    echo "2. Vérifier si l'API retourne des données\n";
    echo "3. Si vide, essayer de changer phase/poule dans l'URL:\n";
    echo "   - Essayer poule=0 ou poule=2\n";
    echo "   - Essayer phase=0 ou phase=2\n";
    echo "4. Partager les URLs qui fonctionnent\n";
}

echo "\n================================================================================\n";
echo "TOTAL: " . array_sum(array_column($summary, 'matchs')) . " matchs récupérés\n";
echo "================================================================================\n";