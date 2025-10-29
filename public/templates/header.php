<?php
/**
 * Template Header - FC Chiche
 * Met en place l'en-tête commun et charge les ressources
 */

// Déterminer la page actuelle (nom du fichier sans extension)
$currentPage = basename($_SERVER['SCRIPT_FILENAME'], '.php');

$navItems = [
    [
        'id' => 'index',
        'label' => 'Accueil',
        'href' => $basePath . '/',
    ],
    [
        'id' => 'calendrier',
        'label' => 'Calendrier',
        'href' => $basePath . '/calendrier',
    ],
    [
        'id' => 'resultats',
        'label' => 'Résultats',
        'href' => $basePath . '/resultats',
    ],
    [
        'id' => 'classement',
        'label' => 'Classements',
        'href' => $basePath . '/classement',
    ],
    [
        'id' => 'contact',
        'label' => 'Contact',
        'href' => $basePath . '/contact',
    ],
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>FC Chiché - Club officiel</title>
  <meta
    name="description"
    content="Le Football Club de Chiché - 60 ans de passion et de partage autour du football dans les Deux-Sèvres"
  />

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
      darkMode: 'class',
      theme: {
        extend: {
          colors: {
            primary: '#008a00',
            'primary-dark': '#0a3c1d',
            'primary-light': '#12b012',
            'surface': '#f7faf5',
            'ink': '#102016',
          },
          fontFamily: {
            display: ['Public Sans', 'sans-serif'],
          },
          borderRadius: {
            DEFAULT: '0.75rem',
            lg: '1.5rem',
            xl: '2rem',
            full: '9999px',
          },
        },
      },
    };
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

<body
  class="font-display bg-surface text-ink"
  data-api-base="<?= htmlspecialchars($apiBase, ENT_QUOTES, 'UTF-8') ?>"
  data-base-path="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>"
  data-assets-base="<?= htmlspecialchars($assetsBase, ENT_QUOTES, 'UTF-8') ?>"
>
  <div class="site-background" aria-hidden="true"></div>
  <div class="page-shell relative flex min-h-dvh flex-col">
    <header class="site-header">
      <div class="site-header__inner">
        <a class="site-logo" href="<?= $basePath ?>/">
          <span class="site-logo__mark">
            <img
              src="<?= $assetsBase ?>/images/logo.svg"
              width="52"
              height="52"
              alt="Logo du FC Chiché"
              loading="lazy"
            />
          </span>
          <span class="site-logo__text">
            <span class="site-logo__club">FC Chiché</span>
            <span class="site-logo__tagline">Pour l'amour du maillot</span>
          </span>
        </a>

        <nav id="site-navigation" class="site-nav" data-nav-menu>
          <div class="site-nav__links">
            <?php foreach ($navItems as $item): ?>
              <?php
              $isActive = $currentPage === $item['id'] || ($item['id'] === 'index' && $currentPage === '');
              $linkClasses = 'site-nav__link' . ($isActive ? ' is-active' : '');
              ?>
              <a
                class="<?= $linkClasses ?>"
                href="<?= htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8') ?>"
                <?php if ($isActive): ?>aria-current="page"<?php endif; ?>
              >
                <?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?>
              </a>
            <?php endforeach; ?>
          </div>
          <a class="site-nav__cta" href="<?= $basePath ?>/calendrier">Billetterie</a>
        </nav>

        <button
          type="button"
          class="site-header__toggle"
          data-nav-toggle
          aria-controls="site-navigation"
          aria-expanded="false"
          aria-label="Ouvrir la navigation"
        >
          <span class="sr-only">Ouvrir la navigation</span>
          <span class="material-symbols-outlined" aria-hidden="true">menu</span>
        </button>
      </div>
    </header>

    <main class="page-shell__content flex-1" id="page-content">
