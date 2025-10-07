<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="FC Chiche - Site officiel du club de football">
    <meta name="theme-color" content="#006600">
    
    <title>FC Chiche - Club de Football</title>
    
    <!-- Preconnect pour optimisation -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    
    <!-- CSS -->
    <link rel="stylesheet" href="/assets/css/variables.css">
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/components.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="/assets/images/logo.svg">
    
    <!-- Open Graph -->
    <meta property="og:title" content="FC Chiche - Club de Football">
    <meta property="og:description" content="Suivez les résultats et le calendrier du FC Chiche">
    <meta property="og:type" content="website">
    <meta property="og:image" content="/assets/images/logo.svg">
</head>
<body>
    <!-- Navigation Desktop -->
    <nav class="nav-desktop">
        <div class="container nav-desktop-container">
            <a href="/" class="nav-desktop-logo" data-link>
                <img src="/assets/images/logo.svg" alt="FC Chiche Logo">
                <span>FC Chiche</span>
            </a>
            <ul class="nav-desktop-list">
                <li><a href="/resultats" class="nav-desktop-link" data-link>Résultats</a></li>
                <li><a href="/calendrier" class="nav-desktop-link" data-link>Calendrier</a></li>
                <li><a href="/classement" class="nav-desktop-link" data-link>Classement</a></li>
                <li><a href="/club" class="nav-desktop-link" data-link>Le Club</a></li>
            </ul>
        </div>
    </nav>
    
    <!-- Contenu dynamique -->
    <div id="app">
        <div class="loading-container">
            <div class="spinner"></div>
        </div>
    </div>
    
    <!-- Navigation Mobile (fixe en bas) -->
    <nav class="nav-mobile">
        <ul class="nav-mobile-list">
            <li class="nav-mobile-item">
                <a href="/resultats" class="nav-mobile-link" data-link>
                    <span class="nav-mobile-icon">🏆</span>
                    <span class="nav-mobile-text">Résultats</span>
                </a>
            </li>
            <li class="nav-mobile-item">
                <a href="/calendrier" class="nav-mobile-link" data-link>
                    <span class="nav-mobile-icon">📅</span>
                    <span class="nav-mobile-text">Calendrier</span>
                </a>
            </li>
            <li class="nav-mobile-item">
                <a href="/classement" class="nav-mobile-link" data-link>
                    <span class="nav-mobile-icon">📊</span>
                    <span class="nav-mobile-text">Classement</span>
                </a>
            </li>
            <li class="nav-mobile-item">
                <a href="/club" class="nav-mobile-link" data-link>
                    <span class="nav-mobile-icon">⚽</span>
                    <span class="nav-mobile-text">Club</span>
                </a>
            </li>
        </ul>
    </nav>
    
    <!-- JavaScript -->
    <script src="/assets/js/router.js"></script>
    <script src="/assets/js/api.js"></script>
    <script src="/assets/js/components.js"></script>
    <script src="/assets/js/app.js"></script>
</body>
</html>