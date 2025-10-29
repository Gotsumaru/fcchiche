<?php
declare(strict_types=1);

$pageTitle = 'Photos | FC Chiché';

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/templates/header.php';
?>
      <section class="section">
        <div class="container">
          <div class="section__header">
            <span class="section__eyebrow">Souvenirs</span>
            <h1 class="section__title">Galerie photos</h1>
            <p class="section__subtitle">Revivez les plus beaux moments du FC Chiché en images.</p>
          </div>

          <div class="pill-group" role="tablist" aria-label="Filtrer la galerie">
            <button class="pill is-active" type="button">Tous</button>
            <button class="pill" type="button">Séniors</button>
            <button class="pill" type="button">Jeunes</button>
            <button class="pill" type="button">Événements</button>
            <button class="pill" type="button">Vie du club</button>
          </div>

          <div class="gallery-grid" style="margin-top: 2.5rem;">
            <?php
            $photos = [
                'https://images.unsplash.com/photo-1499028344343-cd173ffc68a9?auto=format&fit=crop&w=1600&q=80',
                'https://images.unsplash.com/photo-1508609349937-5ec4ae374ebf?auto=format&fit=crop&w=1600&q=80',
                'https://images.unsplash.com/photo-1522778119026-d647f0596c20?auto=format&fit=crop&w=1600&q=80',
                'https://images.unsplash.com/photo-1543353071-873f17a7a088?auto=format&fit=crop&w=1600&q=80',
                'https://images.unsplash.com/photo-1518091043644-c1d4457512c6?auto=format&fit=crop&w=1600&q=80',
                'https://images.unsplash.com/photo-1508098682722-e99c43a406b2?auto=format&fit=crop&w=1600&q=80',
              ];
            foreach ($photos as $photo): ?>
              <div class="gallery-item">
                <img src="<?= htmlspecialchars($photo, ENT_QUOTES, 'UTF-8') ?>" alt="Moment FC Chiché" loading="lazy" />
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </section>

<?php
require_once __DIR__ . '/templates/footer.php';
