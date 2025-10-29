<?php
declare(strict_types=1);

$pageTitle = 'FC Chiché U15 | FC Chiché';

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../templates/header.php';
?>
      <section class="section">
        <div class="container">
          <div class="section__header">
            <span class="section__eyebrow">Jeunes</span>
            <h1 class="section__title">FC Chiché U15</h1>
            <p class="section__subtitle">U15 Départemental 1 – Poule Nord</p>
          </div>

          <div class="glass-card">
            <h2 class="glass-card__title">Composition type</h2>
            <p class="glass-card__excerpt">Gardien : T. Piveteau · Défense : A. Billaud, L. Grolleau, E. Poirier, F. Davy · Milieu : J. Faure, R. Viala, B. Texier · Attaque : A. Girard, S. Martineau, D. Renaud.</p>
            <h3>Entraîneur</h3>
            <p>Maxime Boisson</p>
            <h3>Résultats récents</h3>
            <ul>
              <li>FC Chiché U15 1 – 1 Cerizay</li>
              <li>Bressuire 0 – 3 FC Chiché U15</li>
              <li>FC Chiché U15 2 – 0 Airvault</li>
            </ul>
            <a class="btn btn--primary" href="<?= $basePath ?>/resultats">Voir tous les résultats</a>
          </div>
        </div>
      </section>

<?php
require_once __DIR__ . '/../templates/footer.php';
