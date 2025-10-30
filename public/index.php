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

      <section class="section section--story">
        <div class="container">
          <div class="story-grid">
            <div class="story-grid__content">
              <span class="section__eyebrow">Le club</span>
              <h2 class="section__title">Un écrin pour les verts et blancs</h2>
              <p class="section__subtitle">
                De l'école de foot aux séniors, la passion se partage sur et en dehors du terrain. Notre identité mêle exigence,
                transmission et convivialité, avec des infrastructures rénovées pour accueillir licenciés et supporters.
              </p>
              <ul class="story-points">
                <li>
                  <span class="icon-placeholder" aria-hidden="true">i</span>
                  Vestiaires intégralement modernisés en 2024, éclairage LED dernière génération.
                </li>
                <li>
                  <span class="icon-placeholder" aria-hidden="true">i</span>
                  École de foot labellisée FFF, séances adaptées à chaque catégorie du lundi au samedi.
                </li>
                <li>
                  <span class="icon-placeholder" aria-hidden="true">i</span>
                  Club-house chaleureux avec espace restauration et terrasse panoramique sur le terrain honneur.
                </li>
              </ul>
            </div>
            <div class="story-grid__gallery" aria-hidden="true">
              <div class="image-stack">
                <div class="image-stack__item image-stack__item--primary">
                  <div class="image-frame">Image 1200x800</div>
                </div>
                <div class="image-stack__item image-stack__item--secondary">
                  <div class="image-frame">Image 720x900</div>
                </div>
                <div class="image-stack__item image-stack__item--accent">
                  <div class="image-frame">Image 640x640</div>
                </div>
              </div>
            </div>
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
          <div class="home-scroll">
            <div class="home-scroll__track" data-component="home-events-list" aria-live="polite"></div>
          </div>
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
          <div class="result-showcase">
            <div class="result-showcase__visual" aria-hidden="true">
              <div class="image-frame image-frame--tall">Image 1080x1440</div>
            </div>
            <div class="result-showcase__stream">
              <div class="home-scroll home-scroll--compact">
                <div class="home-scroll__track home-scroll__track--compact" data-component="home-results-list" aria-live="polite"></div>
              </div>
            </div>
          </div>
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
          <div class="sponsor-grid" role="list">
            <figure class="sponsor-card" role="listitem">
              <div class="image-frame image-frame--logo">Logo 320x180</div>
              <figcaption>BCZ Construction</figcaption>
            </figure>
            <figure class="sponsor-card" role="listitem">
              <div class="image-frame image-frame--logo">Logo 320x180</div>
              <figcaption>Boche Chaussure</figcaption>
            </figure>
            <figure class="sponsor-card" role="listitem">
              <div class="image-frame image-frame--logo">Logo 320x180</div>
              <figcaption>Cholet Traiteur</figcaption>
            </figure>
            <figure class="sponsor-card" role="listitem">
              <div class="image-frame image-frame--logo">Logo 320x180</div>
              <figcaption>Chiché Automobile</figcaption>
            </figure>
            <figure class="sponsor-card" role="listitem">
              <div class="image-frame image-frame--logo">Logo 320x180</div>
              <figcaption>Maison Dubois</figcaption>
            </figure>
            <figure class="sponsor-card" role="listitem">
              <div class="image-frame image-frame--logo">Logo 320x180</div>
              <figcaption>Crédit Bocage</figcaption>
            </figure>
          </div>
        </div>
      </section>

<?php
require_once __DIR__ . '/templates/footer.php';
