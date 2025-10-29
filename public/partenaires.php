<?php
declare(strict_types=1);

$pageTitle = 'Partenaires | FC Chiché';

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/templates/header.php';
?>
      <section class="section">
        <div class="container">
          <div class="section__header">
            <span class="section__eyebrow">Soutiens</span>
            <h1 class="section__title">Nos partenaires</h1>
            <p class="section__subtitle">Merci à toutes les entreprises qui soutiennent le FC Chiché.</p>
          </div>

          <div class="partner-grid">
            <?php
            $partners = [
                'BCZ',
                'Boche Chaussure',
                'Cholet Traiteur',
                'CR7',
                'Clochard Dolor',
                'Chiché Automobile',
                'Maison Dubois',
                'Garage Martin',
                'Super U Bressuire',
              ];
            foreach ($partners as $partner): ?>
              <div class="partner-tile"><?= htmlspecialchars($partner, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endforeach; ?>
          </div>
        </div>
      </section>

      <section class="section section--alt">
        <div class="container">
          <div class="section__header">
            <span class="section__eyebrow">Rejoindre l'aventure</span>
            <h2 class="section__title">Devenir partenaire</h2>
            <p class="section__subtitle">Rejoignez l’aventure du FC Chiché et soutenez le sport local.</p>
            <div class="hero__actions" style="margin-top: 2rem;">
              <a class="btn btn--primary" href="<?= $basePath ?>/contact">Contactez-nous</a>
            </div>
          </div>
        </div>
      </section>

<?php
require_once __DIR__ . '/templates/footer.php';
