<?php
declare(strict_types=1);

$pageTitle = 'FC Chiché U13 | FC Chiché';

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../templates/header.php';
?>
      <section class="section">
        <div class="container">
          <div class="section__header">
            <span class="section__eyebrow">Jeunes</span>
            <h1 class="section__title">FC Chiché U13</h1>
            <p class="section__subtitle">U13 Départemental – Plateau Bocage</p>
          </div>

          <div class="glass-card">
            <h2 class="glass-card__title">Composition type</h2>
            <p class="glass-card__excerpt">Gardien : H. Blanchet · Défense : L. Billaud, M. Besson, R. Faivre · Milieu : A. Nadeau, T. Brard, S. Lory · Attaque : J. Manceau, P. Bounaud, N. Berson.</p>
            <h3>Entraîneur</h3>
            <p>Clara Renaud</p>
            <h3>Résultats récents</h3>
            <ul>
              <li>FC Chiché U13 5 – 3 Bressuire</li>
              <li>Parthenay 2 – 2 FC Chiché U13</li>
              <li>FC Chiché U13 4 – 1 Châtillon</li>
            </ul>
            <a class="btn btn--primary" href="<?= $basePath ?>/resultats">Voir tous les résultats</a>
          </div>
        </div>
      </section>

<?php
require_once __DIR__ . '/../templates/footer.php';
