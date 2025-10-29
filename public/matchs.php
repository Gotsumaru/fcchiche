<?php
declare(strict_types=1);

$pageTitle = 'Matchs | FC Chiché';

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/templates/header.php';
?>
      <section class="section">
        <div class="container">
          <div class="section__header">
            <span class="section__eyebrow">Agenda</span>
            <h1 class="section__title">Prochains matchs</h1>
            <p class="section__subtitle">Tous les rendez-vous pour soutenir les verts et blancs.</p>
          </div>

          <div class="match-list">
            <article class="match-card">
              <div class="match-card__teams">
                <span>FC Chiché 1</span>
                <span>vs</span>
                <span>Beaulieu Breuil ES</span>
              </div>
              <div class="match-card__meta">
                <span class="badge">Championnat</span>
                <span>25 mai 2025</span>
                <span>15h00</span>
                <span>Stade de Chiché</span>
              </div>
            </article>
            <article class="match-card">
              <div class="match-card__teams">
                <span>FC Chiché U17</span>
                <span>vs</span>
                <span>US Parthenay</span>
              </div>
              <div class="match-card__meta">
                <span class="badge">Championnat</span>
                <span>31 mai 2025</span>
                <span>18h30</span>
                <span>Complexe La Broche</span>
              </div>
            </article>
            <article class="match-card">
              <div class="match-card__teams">
                <span>FC Chiché U15</span>
                <span>vs</span>
                <span>Airvault</span>
              </div>
              <div class="match-card__meta">
                <span class="badge">Coupe</span>
                <span>2 juin 2025</span>
                <span>14h00</span>
                <span>Stade municipal</span>
              </div>
            </article>
          </div>
        </div>
      </section>

      <section class="section section--alt">
        <div class="container">
          <div class="section__header">
            <span class="section__eyebrow">Saison</span>
            <h2 class="section__title">Calendrier de la saison</h2>
            <p class="section__subtitle">Téléchargez la version complète à afficher dans votre vestiaire.</p>
            <div class="hero__actions" style="margin-top: 2rem;">
              <a class="btn btn--primary" href="<?= $assetsBase ?>/docs/calendrier-fcchiche.pdf" download>Télécharger le calendrier PDF</a>
            </div>
          </div>
        </div>
      </section>

      <section class="section">
        <div class="container">
          <div class="section__header">
            <span class="section__eyebrow">Soutiens</span>
            <h2 class="section__title">Nos sponsors</h2>
          </div>
          <div class="partner-marquee" role="list">
            <span class="partner-marquee__item" role="listitem">BCZ</span>
            <span class="partner-marquee__item" role="listitem">Boche Chaussure</span>
            <span class="partner-marquee__item" role="listitem">Cholet Traiteur</span>
            <span class="partner-marquee__item" role="listitem">CR7</span>
            <span class="partner-marquee__item" role="listitem">Clochard Dolor</span>
            <span class="partner-marquee__item" role="listitem">Chiché Automobile</span>
            <span class="partner-marquee__item" role="listitem">Maison Dubois</span>
          </div>
        </div>
      </section>

<?php
require_once __DIR__ . '/templates/footer.php';
