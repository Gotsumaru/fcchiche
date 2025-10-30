<?php
declare(strict_types=1);

/**
 * Page d'accueil — FC Chiché
 */

$pageTitle = 'Accueil | FC Chiché';

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/templates/header.php';
?>
      <section class="hero">
        <div
          class="hero__background"
          style="background-image: url('https://via.placeholder.com/1800x1000?text=Visuel+principal+1800x1000');"
          aria-hidden="true"
        ></div>
        <div class="hero__overlay" aria-hidden="true"></div>
        <div class="hero__content">
          <img
            class="hero__logo"
            src="<?= $assetsBase ?>/images/logo.svg"
            width="120"
            height="120"
            alt="Emblème FC Chiché"
          />
          <h1 class="hero__title">La passion du football depuis 1946</h1>
          <p class="hero__subtitle">Club de football amateur du bocage bressuirais.</p>
          <div class="hero__actions">
            <a class="btn btn--primary" href="<?= $basePath ?>/resultats">Résultats</a>
            <a class="btn btn--secondary" href="<?= $basePath ?>/matchs">Calendrier</a>
          </div>
        </div>
      </section>

      <section class="section section--alt">
        <div class="container">
          <div class="section__header">
            <span class="section__eyebrow">Le club</span>
            <h2 class="section__title">Un club familial, exigeant et ouvert</h2>
            <p class="section__subtitle">
              De l'école de foot aux séniors, plus de 250 licenciés font vivre le FC Chiché chaque semaine. Notre projet sportif
              repose sur la formation, l'inclusion et le partage d'une passion commune.
            </p>
          </div>
          <div class="info-grid">
            <article class="info-card">
              <span class="icon-placeholder" aria-hidden="true">i</span>
              <h3>Équipes engagées</h3>
              <p>4 collectifs séniors et 6 équipes de jeunes. Toutes évoluent dans les championnats départementaux.</p>
            </article>
            <article class="info-card">
              <span class="icon-placeholder" aria-hidden="true">i</span>
              <h3>Équipements</h3>
              <p>Deux terrains en herbe, un terrain synthétique et un club-house modernisé en 2024.</p>
            </article>
            <article class="info-card">
              <span class="icon-placeholder" aria-hidden="true">i</span>
              <h3>Vie associative</h3>
              <p>Des événements toute l'année : tournois, stages vacances, soirées partenaires et actions solidaires.</p>
            </article>
          </div>
        </div>
      </section>

      <section class="section">
        <div class="container">
          <div class="section__header">
            <span class="section__eyebrow">Évènements</span>
            <h2 class="section__title">Les prochains rendez-vous</h2>
            <p class="section__subtitle">Matches, plateaux jeunes et animations organisés par le club.</p>
          </div>
          <div class="events-grid" data-component="home-events-list" aria-live="polite"></div>
        </div>
      </section>

      <section class="section section--alt">
        <div class="container">
          <div class="section__header">
            <span class="section__eyebrow">Performances</span>
            <h2 class="section__title">Derniers résultats du club</h2>
            <p class="section__subtitle" data-component="home-results-header">
              Chargement des dernières rencontres…
            </p>
          </div>
          <div class="results-grid" data-component="home-results-list" aria-live="polite"></div>
        </div>
      </section>

      <section class="section">
        <div class="container">
          <div class="section__header">
            <span class="section__eyebrow">Notre stade</span>
            <h2 class="section__title">Retrouvez-nous à Chiché</h2>
            <p class="section__subtitle">
              Venez encourager les verts et blancs au complexe sportif. Buvette, tribunes couvertes et grand parking sont à votre
              disposition.
            </p>
          </div>
          <div class="map-shell map-shell--wide">
            <iframe
              title="Localisation du FC Chiché"
              src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2706.655193924687!2d-0.543!3d46.783!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x480799b0517d8623%3A0x5d6587b9e90b8e0!2sChich%C3%A9!5e0!3m2!1sfr!2sfr!4v1700000000000"
              loading="lazy"
              referrerpolicy="no-referrer-when-downgrade"
              allowfullscreen
            ></iframe>
          </div>
        </div>
      </section>

      <section class="section section--alt">
        <div class="container">
          <div class="section__header">
            <span class="section__eyebrow">Soutiens</span>
            <h2 class="section__title">Nos partenaires</h2>
            <p class="section__subtitle">Un grand merci aux entreprises locales qui accompagnent le FC Chiché.</p>
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
          <div class="hero__actions" style="margin-top: 2.5rem;">
            <a class="btn btn--primary" href="<?= $basePath ?>/partenaires">Devenir partenaire</a>
          </div>
        </div>
      </section>

<?php
require_once __DIR__ . '/templates/footer.php';
