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
                ['file' => 'home.png', 'alt' => "Tribunes du stade du FC Chiché"],
                ['file' => 'Agenda.png', 'alt' => "Vestiaires préparés avant le match"],
                ['file' => 'resultat.png', 'alt' => "Victoire du FC Chiché célébrée en équipe"],
                ['file' => 'Classement.png', 'alt' => "Analyse des classements du FC Chiché"],
                ['file' => 'Contact.png', 'alt' => "Supporters réunis au club-house"],
                ['file' => 'Agenda.png', 'alt' => "Préparation tactique dans les vestiaires"],
            ];
            foreach ($photos as $photo):
              $src = $assetsBase . '/images/' . $photo['file'];
            ?>
              <div class="gallery-item">
                <img
                  src="<?= htmlspecialchars($src, ENT_QUOTES, 'UTF-8') ?>"
                  width="4096"
                  height="4096"
                  alt="<?= htmlspecialchars($photo['alt'], ENT_QUOTES, 'UTF-8') ?>"
                  loading="lazy"
                  decoding="async"
                />
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </section>

<?php
require_once __DIR__ . '/templates/footer.php';
