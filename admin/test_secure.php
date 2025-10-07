<?php
declare(strict_types=1);

/**
 * Debug format des coupes pour identifier la structure de réponse
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/../config/config.php';

$cups = [
    ['name' => 'Coupe de France', 'cp_no' => 435164, 'phase' => 1, 'poule' => 1],
    ['name' => 'Coupe des Deux-Sèvres', 'cp_no' => 436839, 'phase' => 1, 'poule' => 1],
    ['name' => 'Coupe Saboureau', 'cp_no' => 436838, 'phase' => 1, 'poule' => 1],
];

echo "================================================================================\n";
echo "ANALYSE FORMAT API DES COUPES\n";
echo "================================================================================\n\n";

foreach ($cups as $cup) {
    echo str_repeat("=", 80) . "\n";
    echo "COUPE: {$cup['name']}\n";
    echo str_repeat("=", 80) . "\n\n";
    
    $url = sprintf(
        'https://api-dofa.fff.fr/api/compets/%d/phases/%d/poules/%d/matchs?clNo=5403',
        $cup['cp_no'],
        $cup['phase'],
        $cup['poule']
    );
    
    echo "URL: {$url}\n\n";
    
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'User-Agent: Mozilla/5.0'
        ]
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "HTTP Code: {$http_code}\n";
    
    if ($response === false || $http_code !== 200) {
        echo "ERREUR: Impossible de récupérer les données\n\n";
        continue;
    }
    
    $data = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "ERREUR JSON: " . json_last_error_msg() . "\n";
        echo "Réponse brute (200 premiers caractères):\n";
        echo substr($response, 0, 200) . "\n\n";
        continue;
    }
    
    echo "Structure de la réponse:\n";
    echo "  Type: " . gettype($data) . "\n";
    
    if (is_array($data)) {
        if (isset($data['hydra:member'])) {
            echo "  Format: Hydra Collection\n";
            echo "  Nombre de matchs: " . count($data['hydra:member']) . "\n";
        } elseif (isset($data[0])) {
            echo "  Format: Tableau direct\n";
            echo "  Nombre de matchs: " . count($data) . "\n";
        } elseif (empty($data)) {
            echo "  Format: Tableau vide []\n";
            echo "  Nombre de matchs: 0\n";
        } else {
            echo "  Format: Objet avec clés: " . implode(', ', array_keys($data)) . "\n";
        }
    }
    
    echo "\nPremières clés du JSON:\n";
    if (is_array($data) && !empty($data)) {
        foreach (array_slice(array_keys($data), 0, 10) as $key) {
            $value_preview = is_array($data[$key]) ? '[array]' : (is_string($data[$key]) ? substr($data[$key], 0, 50) : $data[$key]);
            echo "  - {$key}: {$value_preview}\n";
        }
    } else {
        echo "  (vide ou non-array)\n";
    }
    
    echo "\nJSON complet (formaté):\n";
    echo str_repeat("-", 80) . "\n";
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    echo str_repeat("-", 80) . "\n\n";
}

echo "\n================================================================================\n";
echo "TEST ALTERNATIFS (différentes combinaisons phase/poule)\n";
echo "================================================================================\n\n";

// Test Coupe de France avec différentes combinaisons
$test_cases = [
    ['phase' => 0, 'poule' => 0],
    ['phase' => 0, 'poule' => 1],
    ['phase' => 1, 'poule' => 0],
    ['phase' => 2, 'poule' => 1],
];

echo "COUPE DE FRANCE (cp_no: 435164)\n";
echo str_repeat("-", 80) . "\n";

foreach ($test_cases as $test) {
    $url = sprintf(
        'https://api-dofa.fff.fr/api/compets/435164/phases/%d/poules/%d/matchs?clNo=5403',
        $test['phase'],
        $test['poule']
    );
    
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_HTTPHEADER => ['Accept: application/json']
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $data = $http_code === 200 ? json_decode($response, true) : null;
    $count = 0;
    
    if (is_array($data)) {
        if (isset($data['hydra:member'])) {
            $count = count($data['hydra:member']);
        } elseif (isset($data[0])) {
            $count = count($data);
        }
    }
    
    $status = $http_code === 200 ? ($count > 0 ? "✓ {$count} matchs" : "⊘ 0 match") : "✗ HTTP {$http_code}";
    
    echo sprintf(
        "  phase=%d, poule=%d : %s\n",
        $test['phase'],
        $test['poule'],
        $status
    );
    
    if ($count > 0) {
        echo "    → URL fonctionnelle: {$url}\n";
    }
}

echo "\n================================================================================\n";