<?php
declare(strict_types=1);

$pageTitle = 'FC Chiché U17 | FC Chiché';

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../templates/header.php';
?>
      <section class="section">
        <div class="container">
          <div class="section__header">
            <span class="section__eyebrow">Jeunes</span>
            <h1 class="section__title">FC Chiché U17</h1>
            <p class="section__subtitle">U17 Régional 2 – Groupe C</p>
          </div>

          <div class="glass-card">
            <h2 class="glass-card__title">Composition type</h2>
            <p class="glass-card__excerpt">Gardien : T. Chevalier · Défense : L. Martin, M. Drapeau, T. Dupont, M. Texier · Milieu : J. Guiet, N. Roy, A. Lamy · Attaque : R. Moulin, E. Dubreuil, S. Brossard.</p>
            <h3>Entraîneur</h3>
            <p>Anthony Drapeau</p>
            <h3>Résultats récents</h3>
            <ul>
              <li>FC Chiché U17 3 – 1 Parthenay</li>
              <li>Niort 1 – 2 FC Chiché U17</li>
              <li>FC Chiché U17 2 – 2 Thouars</li>
            </ul>
            <a class="btn btn--primary" href="<?= $basePath ?>/resultats">Voir tous les résultats</a>
          </div>
        </div>
      </section>

<?php
require_once __DIR__ . '/../templates/footer.php';
