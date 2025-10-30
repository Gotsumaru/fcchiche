<?php
declare(strict_types=1);

$pageTitle = 'Nos équipes | FC Chiché';

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/templates/header.php';
?>
      <section class="section">
        <div class="container">
          <div class="section__header">
            <span class="section__eyebrow">Effectifs</span>
            <h1 class="section__title">Nos équipes</h1>
            <p class="section__subtitle">Sélectionnez votre catégorie et découvrez la composition de chaque groupe.</p>
          </div>

          <div class="pill-group" role="tablist" aria-label="Filtrer les équipes">
            <button class="pill is-active" type="button">Toutes</button>
            <button class="pill" type="button">Séniors</button>
            <button class="pill" type="button">U17</button>
            <button class="pill" type="button">U15</button>
            <button class="pill" type="button">U13</button>
          </div>

          <div class="team-grid" style="margin-top: 2.5rem;">
            <a class="team-card" href="<?= $basePath ?>/equipes/fcchiche1.php">
              <div class="team-card__media">
                <img
                  src="<?= $assetsBase ?>/images/home.png"
                  width="4096"
                  height="4096"
                  alt="Équipe seniors du FC Chiché"
                  loading="lazy"
                  decoding="async"
                />
              </div>
              <div>
                <h3>FC Chiché 1</h3>
                <p>Seniors Départemental 3 – Phase 1 – Poule 1</p>
              </div>
            </a>
            <a class="team-card" href="<?= $basePath ?>/equipes/fcchiche2.php">
              <div class="team-card__media">
                <img
                  src="<?= $assetsBase ?>/images/Agenda.png"
                  width="4096"
                  height="4096"
                  alt="Équipe réserve du FC Chiché"
                  loading="lazy"
                  decoding="async"
                />
              </div>
              <div>
                <h3>FC Chiché 2</h3>
                <p>Seniors Départemental 4 – Phase 1 – Poule 2</p>
              </div>
            </a>
            <a class="team-card" href="<?= $basePath ?>/equipes/u17.php">
              <div class="team-card__media">
                <img
                  src="<?= $assetsBase ?>/images/resultat.png"
                  width="4096"
                  height="4096"
                  alt="Équipe U17 du FC Chiché"
                  loading="lazy"
                  decoding="async"
                />
              </div>
              <div>
                <h3>FC Chiché U17</h3>
                <p>U17 Régional 2 – Groupe C</p>
              </div>
            </a>
            <a class="team-card" href="<?= $basePath ?>/equipes/u15.php">
              <div class="team-card__media">
                <img
                  src="<?= $assetsBase ?>/images/Classement.png"
                  width="4096"
                  height="4096"
                  alt="Équipe U15 du FC Chiché"
                  loading="lazy"
                  decoding="async"
                />
              </div>
              <div>
                <h3>FC Chiché U15</h3>
                <p>U15 Départemental 1 – Poule Nord</p>
              </div>
            </a>
            <a class="team-card" href="<?= $basePath ?>/equipes/u13.php">
              <div class="team-card__media">
                <img
                  src="<?= $assetsBase ?>/images/Contact.png"
                  width="4096"
                  height="4096"
                  alt="Équipe U13 du FC Chiché"
                  loading="lazy"
                  decoding="async"
                />
              </div>
              <div>
                <h3>FC Chiché U13</h3>
                <p>U13 Départemental – Plateau Bocage</p>
              </div>
            </a>
          </div>
        </div>
      </section>

<?php
require_once __DIR__ . '/templates/footer.php';
