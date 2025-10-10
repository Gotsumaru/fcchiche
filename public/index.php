<?php
declare(strict_types=1);

/**
 * Page principale - FC Chiche
 * SPA (Single Page Application) avec navigation côté client
 */

// Déterminer les chemins de base
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$scriptDirectory = str_replace('\\', '/', dirname($scriptName));
$trimmedDirectory = rtrim($scriptDirectory, '/');

if ($trimmedDirectory === '.' || $trimmedDirectory === '/') {
    $trimmedDirectory = '';
}

$basePath = $trimmedDirectory === '' ? '' : '/' . ltrim($trimmedDirectory, '/');
$assetsBase = rtrim(($basePath === '' ? '' : $basePath) . '/assets', '/');
$apiBase = rtrim(($basePath === '' ? '' : $basePath) . '/api', '/');

// Valeurs par défaut si vides
$assetsBase = $assetsBase === '' ? '/assets' : $assetsBase;
$apiBase = $apiBase === '' ? '/api' : $apiBase;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="description" content="FC Chiche - Suivez les résultats, le calendrier et les classements de nos équipes de football">
    <meta name="theme-color" content="#006837">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    
    <title>FC Chiche - Club de Football</title>
    
    <!-- PWA -->
    <link rel="manifest" href="<?= htmlspecialchars($basePath . '/manifest.json', ENT_QUOTES) ?>">
    
    <!-- CSS -->
    <link rel="stylesheet" href="<?= htmlspecialchars($assetsBase . '/css/variables.css', ENT_QUOTES) ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($assetsBase . '/css/main.css', ENT_QUOTES) ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($assetsBase . '/css/components.css', ENT_QUOTES) ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?= htmlspecialchars($assetsBase . '/images/logo.svg', ENT_QUOTES) ?>">
    <link rel="apple-touch-icon" href="<?= htmlspecialchars($assetsBase . '/images/icon-192.png', ENT_QUOTES) ?>">
    
    <!-- Open Graph -->
    <meta property="og:title" content="FC Chiche - Club de Football">
    <meta property="og:description" content="Suivez les résultats et le calendrier du FC Chiche">
    <meta property="og:type" content="website">
    <meta property="og:image" content="<?= htmlspecialchars($assetsBase . '/images/og-image.jpg', ENT_QUOTES) ?>">
</head>
<body data-base-path="<?= htmlspecialchars($basePath, ENT_QUOTES) ?>" data-api-base="<?= htmlspecialchars($apiBase, ENT_QUOTES) ?>">
    
    <!-- Loader initial -->
    <div id="initialLoader" class="initial-loader">
        <div class="loader-content">
            <div class="loader-spinner"></div>
            <p class="loader-text">Chargement...</p>
        </div>
    </div>

    <!-- Header -->
    <header class="header">
        <div class="header-content container">
            <div class="header-logo" data-page="home">
                <span class="logo-text">FC Chiche</span>
            </div>
            <nav class="nav-desktop">
                <a class="nav-desktop-link active" data-page="home">Accueil</a>
                <a class="nav-desktop-link" data-page="results">Résultats</a>
                <a class="nav-desktop-link" data-page="calendar">Calendrier</a>
                <a class="nav-desktop-link" data-page="ranking">Classement</a>
            </nav>
        </div>
    </header>

    <!-- Pages -->
    <div id="app">
        
        <!-- Page Accueil -->
        <div class="page active" id="home">
            <div class="page-hero page-hero--with-image" style="background-image: url('<?= htmlspecialchars($assetsBase . '/images/hero-terrain.jpg', ENT_QUOTES) ?>');">
                <div class="page-hero-content container">
                    <div class="page-hero-badge">Saison 2024-2025</div>
                    <h1 class="page-hero-title">Bienvenue au FC Chiche</h1>
                    <p class="page-hero-text">
                        Suivez les performances de nos 4 équipes et vibrez avec nous.
                    </p>
                    <button class="hero-cta" data-page="results">
                        Voir les résultats
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <path d="M6 12L10 8L6 4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            <div class="page-content container">
                <div id="homeContent">
                    <div class="home-loading">Chargement des données...</div>
                </div>
            </div>
        </div>

        <!-- Page Résultats -->
        <div class="page" id="results">
            <div class="page-hero">
                <div class="page-hero-content container">
                    <div class="page-hero-badge">Derniers matchs</div>
                    <h2 class="page-hero-title">Résultats</h2>
                    <p class="page-hero-text">
                        Consultez les performances de chaque équipe
                    </p>
                </div>
            </div>
            
            <div class="page-content container">
                <div class="filters-section">
                    <div class="filters-group" id="resultsFilters">
                        <button class="filter-item active" data-team="all">Toutes les équipes</button>
                    </div>
                </div>
                <div id="resultsContent">
                    <div class="results-loading">Chargement des résultats...</div>
                </div>
            </div>
        </div>

        <!-- Page Calendrier -->
        <div class="page" id="calendar">
            <div class="page-hero">
                <div class="page-hero-content container">
                    <div class="page-hero-badge">Prochains matchs</div>
                    <h2 class="page-hero-title">Calendrier</h2>
                    <p class="page-hero-text">
                        Ne manquez aucun match de vos équipes favorites
                    </p>
                </div>
            </div>
            
            <div class="page-content container">
                <div class="filters-section">
                    <div class="filters-group" id="calendarFilters">
                        <button class="filter-item active" data-team="all">Toutes les équipes</button>
                    </div>
                </div>
                <div id="calendarContent">
                    <div class="calendar-loading">Chargement du calendrier...</div>
                </div>
            </div>
        </div>

        <!-- Page Classement -->
        <div class="page" id="ranking">
            <div class="page-hero">
                <div class="page-hero-content container">
                    <div class="page-hero-badge">Saison 2024-2025</div>
                    <h2 class="page-hero-title">Classement</h2>
                    <p class="page-hero-text">
                        Position de chaque équipe dans son championnat
                    </p>
                </div>
            </div>
            
            <div class="page-content container">
                <div class="filters-section">
                    <div class="filters-group" id="rankingFilters">
                        <!-- Chargé dynamiquement -->
                    </div>
                </div>
                <div id="rankingContent">
                    <div class="ranking-loading">Chargement des classements...</div>
                </div>
            </div>
        </div>

    </div>

    <!-- Navigation Mobile -->
    <nav class="nav-mobile">
        <ul class="nav-mobile-list">
            <li class="nav-mobile-item">
                <a class="nav-mobile-link active" data-page="home">
                    <span class="nav-mobile-icon">🏠</span>
                    <span class="nav-mobile-text">Accueil</span>
                </a>
            </li>
            <li class="nav-mobile-item">
                <a class="nav-mobile-link" data-page="results">
                    <span class="nav-mobile-icon">📊</span>
                    <span class="nav-mobile-text">Résultats</span>
                </a>
            </li>
            <li class="nav-mobile-item">
                <a class="nav-mobile-link" data-page="calendar">
                    <span class="nav-mobile-icon">📅</span>
                    <span class="nav-mobile-text">Calendrier</span>
                </a>
            </li>
            <li class="nav-mobile-item">
                <a class="nav-mobile-link" data-page="ranking">
                    <span class="nav-mobile-icon">🏆</span>
                    <span class="nav-mobile-text">Classement</span>
                </a>
            </li>
        </ul>
    </nav>

    <!-- Scripts -->
    <script src="<?= htmlspecialchars($assetsBase . '/js/app.js', ENT_QUOTES) ?>"></script>
</body>
</html>