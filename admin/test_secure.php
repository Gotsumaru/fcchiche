<?php
declare(strict_types=1);

/**
 * Debug - Analyse structure complète API FFF pour identifier champs logos
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/API/FFFApiClient.php';

header('Content-Type: text/plain; charset=utf-8');

echo str_repeat('=', 80) . "\n";
echo "DEBUG LOGOS API FFF\n";
echo str_repeat('=', 80) . "\n\n";

$api = new FFFApiClient();

// ============================================
// 1. INFOS CLUB - Recherche logo club
// ============================================
echo "1. INFOS CLUB\n";
echo str_repeat('-', 80) . "\n";

$club_data = $api->getClubInfo();

if ($club_data === null) {
    echo "❌ Impossible de récupérer les infos du club\n\n";
} else {
    echo "✓ Données récupérées\n\n";
    
    // Afficher structure complète
    echo "Structure complète des données club:\n";
    print_r($club_data);
    
    echo "\n\nAnalyse champs contenant 'logo' ou 'image':\n";
    $found_logo_fields = false;
    
    foreach ($club_data as $key => $value) {
        if (stripos($key, 'logo') !== false || stripos($key, 'image') !== false || stripos($key, 'photo') !== false) {
            echo "  - {$key} => " . (is_string($value) ? $value : json_encode($value)) . "\n";
            $found_logo_fields = true;
        }
    }
    
    if (!$found_logo_fields) {
        echo "  ⚠️ Aucun champ logo/image/photo trouvé dans les données club\n";
    }
}

// ============================================
// 2. ÉQUIPES - Recherche logos équipes
// ============================================
echo "\n\n";
echo "2. ÉQUIPES\n";
echo str_repeat('-', 80) . "\n";

$equipes_data = $api->getEquipes();

if ($equipes_data === null || !isset($equipes_data['hydra:member'])) {
    echo "❌ Impossible de récupérer les équipes\n\n";
} else {
    $equipes = $equipes_data['hydra:member'];
    echo "✓ " . count($equipes) . " équipes récupérées\n\n";
    
    if (!empty($equipes)) {
        echo "Analyse première équipe (structure complète):\n";
        print_r($equipes[0]);
        
        echo "\n\nChamps contenant 'logo' ou 'image' dans les équipes:\n";
        $found_team_logo = false;
        
        foreach ($equipes as $index => $equipe) {
            foreach ($equipe as $key => $value) {
                if (stripos($key, 'logo') !== false || stripos($key, 'image') !== false || stripos($key, 'photo') !== false) {
                    echo "  Équipe #{$index} - {$key} => " . (is_string($value) ? $value : json_encode($value)) . "\n";
                    $found_team_logo = true;
                }
            }
        }
        
        if (!$found_team_logo) {
            echo "  ⚠️ Aucun champ logo/image/photo trouvé dans les équipes\n";
        }
    }
}

// ============================================
// 3. CALENDRIER - Recherche logos adversaires
// ============================================
echo "\n\n";
echo "3. CALENDRIER (MATCHS)\n";
echo str_repeat('-', 80) . "\n";

$calendrier_data = $api->getCalendrier();

if ($calendrier_data === null || !isset($calendrier_data['hydra:member'])) {
    echo "❌ Impossible de récupérer le calendrier\n\n";
} else {
    $matchs = $calendrier_data['hydra:member'];
    echo "✓ " . count($matchs) . " matchs récupérés\n\n";
    
    if (!empty($matchs)) {
        echo "Analyse premier match (structure complète):\n";
        print_r($matchs[0]);
        
        echo "\n\nChamps contenant 'logo' dans les matchs:\n";
        $found_match_logo = false;
        
        foreach ($matchs as $index => $match) {
            // Recherche dans structure complète (y compris nested)
            $match_str = json_encode($match);
            if (stripos($match_str, 'logo') !== false) {
                echo "  Match #{$index} contient 'logo' quelque part:\n";
                
                // Analyser home et away
                if (isset($match['home']['club'])) {
                    foreach ($match['home']['club'] as $key => $value) {
                        if (stripos($key, 'logo') !== false) {
                            echo "    home.club.{$key} => " . (is_string($value) ? $value : json_encode($value)) . "\n";
                            $found_match_logo = true;
                        }
                    }
                }
                
                if (isset($match['away']['club'])) {
                    foreach ($match['away']['club'] as $key => $value) {
                        if (stripos($key, 'logo') !== false) {
                            echo "    away.club.{$key} => " . (is_string($value) ? $value : json_encode($value)) . "\n";
                            $found_match_logo = true;
                        }
                    }
                }
            }
        }
        
        if (!$found_match_logo) {
            echo "  ⚠️ Aucun champ logo trouvé dans les matchs\n";
        }
    }
}

// ============================================
// RÉSUMÉ & RECOMMANDATIONS
// ============================================
echo "\n\n";
echo str_repeat('=', 80) . "\n";
echo "RÉSUMÉ\n";
echo str_repeat('=', 80) . "\n";

echo "\nIdentifie dans la sortie ci-dessus:\n";
echo "1. Les noms EXACTS des champs contenant les URLs des logos\n";
echo "2. La structure des données (club direct, nested dans un objet, etc.)\n";
echo "3. Si les logos sont disponibles ou non dans l'API FFF\n\n";

echo "Une fois identifiés, je mettrai à jour:\n";
echo "- src/Database/Sync.php (mapping correct des champs)\n";
echo "- sql/database_schema.sql (si besoin d'ajouter des champs)\n";
echo "- README.md (documentation complète)\n\n";

echo str_repeat('=', 80) . "\n";