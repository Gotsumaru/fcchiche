<?php
/**
 * Template Header - FC Chiche
 * Charge dynamiquement les CSS et JS en fonction de la page actuelle
 */

// Déterminer la page actuelle (nom du fichier sans extension)
$currentPage = basename($_SERVER['SCRIPT_FILENAME'], '.php');
?>
<!DOCTYPE html>
<html class="dark" lang="fr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>FCChiche - Pride. Passion. Football.</title>
  <meta name="description" content="Le Football Club de Chiché - Plus de 60 ans de passion footballistique dans les Deux-Sèvres" />

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;700;900&display=swap" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />

  <!-- Configuration Tailwind -->
  <script>
    tailwind.config = {
      darkMode: "class",
      theme: {
        extend: {
          colors: {
            "primary": "#008a00",
            "background-light": "#f5f8f5",
            "background-dark": "#0f230f",
          },
          fontFamily: {
            "display": ["Public Sans", "sans-serif"]
          },
          borderRadius: {
            "DEFAULT": "0.5rem",
            "lg": "1rem",
            "xl": "1.5rem",
            "full": "9999px"
          },
        },
      },
    }
  </script>

  <!-- Common CSS -->
  <link rel="stylesheet" href="<?= $assetsBase ?>/css/common.css" />

  <!-- Page-specific CSS -->
  <?php
  $pageCSS = __DIR__ . '/../assets/css/' . $currentPage . '.css';
  if (file_exists($pageCSS)) {
    echo '<link rel="stylesheet" href="' . $assetsBase . '/css/' . $currentPage . '.css" />';
  }
  ?>
</head>

<body class="font-display bg-white text-gray-800">
  <!-- ====================================================================
       BACKGROUND PARALLAXE FIXE
       ==================================================================== -->
  <div class="parallax-bg-overlay"></div>

  <!-- ====================================================================
       CONTENEUR PRINCIPAL
       ==================================================================== -->
  <div class="relative flex w-full flex-col group/design-root overflow-x-hidden">

    <!-- ==================================================================
         HEADER - MENU DOCK LIQUID GLASS
         Desktop: Top center with max-width
         Mobile: Bottom full-width like iPhone
         ================================================================== -->
    <header class="fixed lg:top-3 bottom-0 lg:bottom-auto z-50 flex justify-center w-full lg:px-4 lg:px-6 lg:px-8">
      <div class="liquidGlass-wrapper dock w-full lg:max-w-6xl lg:rounded-xl rounded-none">
        <!-- Couches d'effet du menu -->
        <div class="liquidGlass-effect"></div>
        <div class="liquidGlass-tint"></div>
        <div class="liquidGlass-shine"></div>

        <!-- Contenu du menu -->
        <div class="liquidGlass-text w-full">
          <nav class="dock py-2 lg:py-0" aria-label="Navigation principale">
            <a href="<?= $basePath ?>/" class="nav-item" title="Accueil">
              <img src="<?= $assetsBase ?>/images/home.png" width="60" height="60" alt="Accueil" class="lg:w-24 lg:h-24" />
            </a>
            <a href="<?= $basePath ?>/classement" class="nav-item" title="Classement">
              <img src="<?= $assetsBase ?>/images/Classement.png" width="60" height="60" alt="Classement" class="lg:w-24 lg:h-24" />
            </a>
            <a href="<?= $basePath ?>/resultats" class="nav-item" title="Résultats">
              <img src="<?= $assetsBase ?>/images/resultat.png" width="60" height="60" alt="Résultats" class="lg:w-24 lg:h-24" />
            </a>
            <a href="<?= $basePath ?>/calendrier" class="nav-item" title="Calendrier">
              <img src="<?= $assetsBase ?>/images/Agenda.png" width="60" height="60" alt="Calendrier" class="lg:w-24 lg:h-24" />
            </a>
            <a href="<?= $basePath ?>/contact" class="nav-item" title="Contact">
              <img src="<?= $assetsBase ?>/images/Contact.png" width="60" height="60" alt="Contact" class="lg:w-24 lg:h-24" />
            </a>
          </nav>
        </div>
      </div>
    </header>

    <!-- ==================================================================
         SCROLL INDICATOR
         ================================================================== -->
    <div class="scroll-indicator" aria-hidden="true">
      <div class="mouse"></div>
    </div>
