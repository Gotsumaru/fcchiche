<?php
declare(strict_types=1);

/**
 * Template Header - FC Chiché
 * Déclare l'en-tête commun et le système de navigation principal
 */

// Déterminer la page actuelle (nom du fichier sans extension)
$currentPage = basename($_SERVER['SCRIPT_FILENAME'], '.php');

// Autoriser la surcharge du titre de page avant l'inclusion du header
$pageTitle = $pageTitle ?? 'FC Chiché';

$navItems = [
    [
        'id' => 'index',
        'label' => 'Accueil',
        'href' => $basePath . '/',
    ],
    [
        'id' => 'resultats',
        'label' => 'Résultats',
        'href' => $basePath . '/resultats',
    ],
    [
        'id' => 'matchs',
        'label' => 'Calendrier',
        'href' => $basePath . '/matchs',
    ],
    [
        'id' => 'classements',
        'label' => 'Classements',
        'href' => $basePath . '/classements',
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
  <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
  <meta
    name="description"
    content="FC Chiché — Club de football amateur du bocage bressuirais, fondé en 1946. Retrouvez résultats, matchs, équipes et actualités."
  />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap"
    rel="stylesheet"
  />
  <link rel="stylesheet" href="<?= $assetsBase ?>/css/common.css" />
  <?php
  $pageCSS = __DIR__ . '/../assets/css/' . $currentPage . '.css';
  if (file_exists($pageCSS)) {
      echo '<link rel="stylesheet" href="' . $assetsBase . '/css/' . $currentPage . '.css" />';
  }
  ?>
</head>

<body
  class="page-body page-<?= htmlspecialchars($currentPage === '' ? 'index' : $currentPage, ENT_QUOTES, 'UTF-8') ?>"
  data-api-base="<?= htmlspecialchars($apiBase, ENT_QUOTES, 'UTF-8') ?>"
  data-base-path="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>"
  data-assets-base="<?= htmlspecialchars($assetsBase, ENT_QUOTES, 'UTF-8') ?>"
>
  <div class="page-shell">
    <header class="app-header">
      <div class="app-header__inner">
        <a class="app-brand" href="<?= $basePath ?>/">
          <span class="app-brand__mark">
            <img
              src="<?= $assetsBase ?>/images/logo.svg"
              width="56"
              height="56"
              alt="Logo du FC Chiché"
              loading="lazy"
            />
          </span>
          <span class="app-brand__text">
            <span class="app-brand__title">FC Chiché</span>
            <span class="app-brand__baseline">Depuis 1946</span>
          </span>
        </a>

        <nav id="main-navigation" class="app-nav" data-nav-menu>
          <ul class="app-nav__list">
            <?php foreach ($navItems as $item): ?>
              <?php
              $isIndex = $item['id'] === 'index';
              $isActive = $currentPage === $item['id'] || ($isIndex && ($currentPage === '' || $currentPage === 'index'));
              ?>
              <li>
                <a
                  class="app-nav__link<?= $isActive ? ' is-active' : '' ?>"
                  href="<?= htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8') ?>"
                  <?php if ($isActive): ?>aria-current="page"<?php endif; ?>
                >
                  <?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        </nav>

        <button
          type="button"
          class="app-header__toggle"
          data-nav-toggle
          aria-controls="main-navigation"
          aria-expanded="false"
        >
          <span class="app-header__toggle-line" aria-hidden="true"></span>
          <span class="app-header__toggle-line" aria-hidden="true"></span>
          <span class="app-header__toggle-line" aria-hidden="true"></span>
          <span class="sr-only">Ouvrir la navigation</span>
        </button>
      </div>
    </header>

    <main class="page-shell__content" id="page-content">
