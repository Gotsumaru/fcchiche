<?php
declare(strict_types=1);

$pageTitle = 'FC Chiché 1 | FC Chiché';

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../templates/header.php';
?>
      <section class="section">
        <div class="container">
          <div class="section__header">
            <span class="section__eyebrow">Seniors</span>
            <h1 class="section__title">FC Chiché 1</h1>
            <p class="section__subtitle">Seniors Départemental 3 – Phase 1 – Poule 1</p>
          </div>

          <div class="glass-card">
            <h2 class="glass-card__title">Composition type</h2>
            <p class="glass-card__excerpt">Gardien : L. Moreau · Défense : D. Martin, A. Baron, G. Ribéreau, J. Loret · Milieu : T. Billaud, M. Richard, L. Bouchet · Attaque : N. Blanchard, E. Girard, P. Guignard.</p>
            <h3>Entraîneur</h3>
            <p>Christophe Bernard</p>
            <h3>Résultats récents</h3>
            <ul>
              <li>FC Chiché 2 – 0 L'Absie</li>
              <li>Bressuire 1 – 1 FC Chiché (4–5 tab)</li>
              <li>FC Chiché 3 – 1 Thouars</li>
            </ul>
            <a class="btn btn--primary" href="<?= $basePath ?>/resultats">Voir tous les résultats</a>
          </div>
        </div>
      </section>

<?php
require_once __DIR__ . '/../templates/footer.php';
