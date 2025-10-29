<?php
declare(strict_types=1);

$pageTitle = 'FC Chiché 2 | FC Chiché';

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../templates/header.php';
?>
      <section class="section">
        <div class="container">
          <div class="section__header">
            <span class="section__eyebrow">Seniors</span>
            <h1 class="section__title">FC Chiché 2</h1>
            <p class="section__subtitle">Seniors Départemental 4 – Phase 1 – Poule 2</p>
          </div>

          <div class="glass-card">
            <h2 class="glass-card__title">Composition type</h2>
            <p class="glass-card__excerpt">Gardien : B. Lucas · Défense : P. Boissinot, F. Perraut, G. Baron, R. Vivien · Milieu : T. Houmeau, J. Gatard, A. Reveau · Attaque : M. Texier, A. Brossard, L. Galopin.</p>
            <h3>Entraîneur</h3>
            <p>Julien Piveteau</p>
            <h3>Résultats récents</h3>
            <ul>
              <li>FC Chiché 2 1 – 0 Pompaire</li>
              <li>Combrand 2 – 2 FC Chiché 2</li>
              <li>FC Chiché 2 4 – 1 Boismé</li>
            </ul>
            <a class="btn btn--primary" href="<?= $basePath ?>/resultats">Voir tous les résultats</a>
          </div>
        </div>
      </section>

<?php
require_once __DIR__ . '/../templates/footer.php';
