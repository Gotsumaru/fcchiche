<?php
declare(strict_types=1);

/**
 * Page d'accueil — FC Chiché
 */

$pageTitle = 'Accueil | FC Chiché';

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/templates/header.php';
?>
      <section class="hero" aria-labelledby="hero-title">
        <div class="container hero__container">
          <header class="hero__intro">
            <h1 class="hero__title" id="hero-title">F.C. Chiché</h1>
            <span class="hero__tagline" role="text">
              <img
                class="hero__tagline-logo"
                src="<?= $assetsBase ?>/images/logo.svg"
                width="40"
                height="40"
                alt=""
                aria-hidden="true"
                loading="lazy"
                decoding="async"
              />
              <span class="hero__tagline-text">Pour l'amour du maillot vert</span>
            </span>
          </header>
          <figure class="hero__visual">
            <div class="hero__media">
              <picture>
                <source
                  srcset="<?= $assetsBase ?>/images/home-low.webp"
                  type="image/webp"
                  media="(max-width: 30rem)"
                />
                <source
                  srcset="
                    <?= $assetsBase ?>/images/home-480.webp 480w,
                    <?= $assetsBase ?>/images/home-800.webp 800w,
                    <?= $assetsBase ?>/images/home-1200.webp 1200w
                  "
                  type="image/webp"
                  sizes="(min-width: 70rem) 960px, (min-width: 48rem) 88vw, 100vw"
                />
                <source
                  srcset="<?= $assetsBase ?>/images/home.jpg 2048w"
                  type="image/jpeg"
                  sizes="(min-width: 70rem) 960px, (min-width: 48rem) 88vw, 100vw"
                />
                <img
                  src="<?= $assetsBase ?>/images/home.jpg"
                  width="2048"
                  height="1152"
                  alt="Les joueurs du FC Chiché rentrent sur la pelouse du stade municipal"
                  loading="eager"
                  fetchpriority="high"
                  decoding="async"
                />
              </picture>
            </div>
          </figure>
          <div class="hero__content">
            <p class="hero__subtitle">
              Suivez nos équipes, préparez vos déplacements et vivez le club en temps réel avec des infos fiables et mises à
              jour.
            </p>
            <div class="hero__actions" role="group" aria-label="Actions principales">
              <a class="btn btn--primary" href="<?= $basePath ?>/matchs">Voir le calendrier</a>
              <a class="btn btn--primary" href="<?= $basePath ?>/resultats">Derniers résultats</a>
            </div>
          </div>
        </div>
      </section>

      <section class="section section--story" aria-labelledby="story-title">
        <div class="container">
          <div class="story__intro">
            <span class="section__eyebrow">Le club</span>
            <h2 class="section__title" id="story-title">Un écrin pour les verts et blancs</h2>
            <p class="section__subtitle">
              Des vestiaires rénovés aux animations du club-house, nous mettons l'expérience de nos licenciés et supporters au
              centre. Découvrez l'envers du décor d'un club familial qui cultive la performance.
            </p>
          </div>
          <div class="story__layout">
            <div class="story__content">
              <div class="story-points" role="list">
                <article class="story-block" role="listitem">
                  <span class="story-block__icon" aria-hidden="true">🏟</span>
                  <div>
                    <h3 class="story-block__title">Vestiaires premium</h3>
                    <p class="story-block__text">
                      Espaces remis à neuf avec zone de récupération, éclairage LED et sonorisation d'avant-match.
                    </p>
                  </div>
                </article>
                <article class="story-block" role="listitem">
                  <span class="story-block__icon" aria-hidden="true">🎓</span>
                  <div>
                    <h3 class="story-block__title">École de foot labellisée</h3>
                    <p class="story-block__text">
                      Séances adaptées à chaque catégorie, éducateurs certifiés et suivi des progrès sur toute la saison.
                    </p>
                  </div>
                </article>
                <article class="story-block" role="listitem">
                  <span class="story-block__icon" aria-hidden="true">🍻</span>
                  <div>
                    <h3 class="story-block__title">Club-house vivant</h3>
                    <p class="story-block__text">
                      Espace restauration, retransmissions et terrasse panoramique pour vivre les matches autrement.
                    </p>
                  </div>
                </article>
              </div>
              <div class="story-stats" role="list">
                <div class="story-stat" role="listitem">
                  <span class="story-stat__value">220+</span>
                  <span class="story-stat__label">licenciés accompagnés chaque saison</span>
                </div>
                <div class="story-stat" role="listitem">
                  <span class="story-stat__value">32</span>
                  <span class="story-stat__label">séances hebdomadaires encadrées</span>
                </div>
                <div class="story-stat" role="listitem">
                  <span class="story-stat__value">18 bénévoles</span>
                  <span class="story-stat__label">mobilisés sur les jours de match</span>
                </div>
              </div>
            </div>
            <div class="story__media">
              <figure class="story__visual story__visual--primary">
                <div class="story__visual-media">
                  <picture>
                    <source
                      srcset="
                        <?= $assetsBase ?>/images/premiere-480.webp 480w,
                        <?= $assetsBase ?>/images/premiere-800.webp 800w,
                        <?= $assetsBase ?>/images/premiere-1200.webp 1200w
                    "
                    type="image/webp"
                    sizes="(min-width: 72rem) 420px, (min-width: 48rem) 60vw, 90vw"
                  />
                  <source
                    srcset="<?= $assetsBase ?>/images/premiere.jpg 1200w"
                    type="image/jpeg"
                    sizes="(min-width: 72rem) 420px, (min-width: 48rem) 60vw, 90vw"
                  />
                  <img
                    src="<?= $assetsBase ?>/images/premiere.jpg"
                    width="1300"
                    height="866"
                    alt="Ambiance nocturne au Pas des Biches"
                    loading="lazy"
                    decoding="async"
                  />
                </picture>
                </div>
                <figcaption class="story__visual-caption">Ambiance nocturne au Pas des Biches</figcaption>
              </figure>
              <figure class="story__visual story__visual--secondary">
                <div class="story__visual-media">
                  <picture>
                    <source srcset="<?= $assetsBase ?>/images/buvette.webp" type="image/webp" />
                    <source srcset="<?= $assetsBase ?>/images/buvette.jpg" type="image/jpeg" />
                    <img
                      src="<?= $assetsBase ?>/images/buvette.jpg"
                      width="1792"
                      height="1024"
                      alt="Préparation du club-house avant le match"
                      loading="lazy"
                      decoding="async"
                    />
                  </picture>
                </div>
                <figcaption class="story__visual-caption">450 repas servis au club-house cette saison</figcaption>
              </figure>
              <figure class="story__visual story__visual--tertiary">
                <div class="story__visual-media">
                  <picture>
                    <source
                      srcset="
                        <?= $assetsBase ?>/images/Reserve-480.webp 480w,
                        <?= $assetsBase ?>/images/Reserve-800.webp 800w,
                        <?= $assetsBase ?>/images/Reserve-1200.webp 1200w
                      "
                      type="image/webp"
                      sizes="(min-width: 72rem) 320px, (min-width: 48rem) 40vw, 80vw"
                    />
                    <source
                      srcset="<?= $assetsBase ?>/images/Reserve.jpg 1200w"
                      type="image/jpeg"
                      sizes="(min-width: 72rem) 320px, (min-width: 48rem) 40vw, 80vw"
                    />
                    <img
                      src="<?= $assetsBase ?>/images/Reserve.jpg"
                      width="1200"
                      height="800"
                      alt="Échauffement collectif de l'équipe réserve"
                      loading="lazy"
                      decoding="async"
                    />
                  </picture>
                </div>
                <figcaption class="story__visual-caption">Échauffement collectif de l'équipe réserve</figcaption>
              </figure>
            </div>
          </div>
        </div>
      </section>

      <section class="section" aria-labelledby="events-title">
        <div class="container">
          <div class="section__header">
            <span class="section__eyebrow">Évènements</span>
            <h2 class="section__title" id="events-title">Les prochains rendez-vous</h2>
            <p class="section__subtitle">Matches, plateaux jeunes et animations organisés par le club.</p>
          </div>
          <div class="home-scroll" data-component="home-events">
            <button class="home-scroll__control" type="button" data-action="scroll-prev" aria-label="Voir les évènements précédents"></button>
            <div class="home-scroll__track" data-component="home-events-list" aria-live="polite"></div>
            <button class="home-scroll__control" type="button" data-action="scroll-next" aria-label="Voir les évènements suivants"></button>
          </div>
        </div>
      </section>

      <section class="section section--alt" aria-labelledby="results-title">
        <div class="container">
          <div class="section__header">
            <span class="section__eyebrow">Performances</span>
            <h2 class="section__title" id="results-title">Derniers résultats du club</h2>
            <p class="section__subtitle" data-component="home-results-header">
              Chargement des dernières rencontres…
            </p>
          </div>
          <div class="result-showcase" data-component="home-results">
            <div class="result-showcase__gallery">
              <picture>
                <source
                  srcset="<?= $assetsBase ?>/images/galeries/441321599_943464427577563_1527836518105961020_n.jpg 2048w"
                  type="image/jpeg"
                  sizes="(min-width: 75rem) 420px, (min-width: 48rem) 60vw, 100vw"
                />
                <img
                  src="<?= $assetsBase ?>/images/galeries/441321599_943464427577563_1527836518105961020_n.jpg"
                  width="2048"
                  height="1670"
                  alt="Scène de match du FC Chiché capturée depuis la tribune principale"
                  loading="lazy"
                  decoding="async"
                />
              </picture>
            </div>
            <div class="result-showcase__stream">
              <div class="home-scroll home-scroll--compact">
                <div class="home-scroll__track home-scroll__track--compact" data-component="home-results-list" aria-live="polite"></div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section class="section" aria-labelledby="location-title">
        <div class="container">
          <div class="section__header">
            <span class="section__eyebrow">Notre stade</span>
            <h2 class="section__title" id="location-title">Retrouvez-nous à Chiché</h2>
            <p class="section__subtitle">
              Venez encourager les verts et blancs au complexe sportif. Buvette, tribunes couvertes et grand parking sont à votre disposition.
            </p>
          </div>
          <div class="location">
            <div class="location__map">
              <iframe
                title="Localisation du FC Chiché"
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2706.655193924687!2d-0.543!3d46.783!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x480799b0517d8623%3A0x5d6587b9e90b8e0!2sChich%C3%A9!5e0!3m2!1sfr!2sfr!4v1700000000000"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
                allowfullscreen
              ></iframe>
            </div>
            <div class="location__details">
              <h3>Complexe sportif de la Brelandière</h3>
              <address>
                Rue du Stade<br />
                79350 Chiché, France
              </address>
              <p class="location__schedule">
                Accueil du secrétariat&nbsp;: lundi au vendredi 18h-20h • Buvette ouverte les jours de match dès 1h avant le coup d'envoi.
              </p>
              <a class="btn btn--primary" href="https://maps.google.com/?daddr=Chich%C3%A9" target="_blank" rel="noopener noreferrer">
                Comment venir
              </a>
            </div>
          </div>
        </div>
      </section>

      <section class="section section--alt" aria-labelledby="partners-title">
        <div class="container">
          <div class="section__header">
            <span class="section__eyebrow">Soutiens</span>
            <h2 class="section__title" id="partners-title">Nos partenaires</h2>
            <p class="section__subtitle">Un grand merci aux entreprises locales qui accompagnent le FC Chiché.</p>
          </div>
          <div class="partner-carousel" data-component="partner-carousel">
            <button
              type="button"
              class="partner-carousel__control partner-carousel__control--prev"
              data-action="scroll-prev"
              aria-label="Voir le partenaire précédent"
            ></button>
            <div class="partner-carousel__viewport" role="list">
              <a class="partner-card" role="listitem" href="https://example.com" target="_blank" rel="noopener noreferrer">
                <img
                  src="<?= $assetsBase ?>/images/logo.svg"
                  width="160"
                  height="160"
                  alt="Logo de BCZ Construction"
                  loading="lazy"
                  decoding="async"
                  data-variant="emerald"
                />
                <span>BCZ Construction</span>
              </a>
              <a class="partner-card" role="listitem" href="https://example.com" target="_blank" rel="noopener noreferrer">
                <img
                  src="<?= $assetsBase ?>/images/placeholder-logo.svg"
                  width="160"
                  height="160"
                  alt="Logo de Boche Chaussure"
                  loading="lazy"
                  decoding="async"
                  data-variant="slate"
                />
                <span>Boche Chaussure</span>
              </a>
              <a class="partner-card" role="listitem" href="https://example.com" target="_blank" rel="noopener noreferrer">
                <img
                  src="<?= $assetsBase ?>/images/placeholder-logo.svg"
                  width="160"
                  height="160"
                  alt="Logo de Cholet Traiteur"
                  loading="lazy"
                  decoding="async"
                  data-variant="forest"
                />
                <span>Cholet Traiteur</span>
              </a>
              <a class="partner-card" role="listitem" href="https://example.com" target="_blank" rel="noopener noreferrer">
                <img
                  src="<?= $assetsBase ?>/images/placeholder-logo.svg"
                  width="160"
                  height="160"
                  alt="Logo de Chiché Automobile"
                  loading="lazy"
                  decoding="async"
                  data-variant="charcoal"
                />
                <span>Chiché Automobile</span>
              </a>
              <a class="partner-card" role="listitem" href="https://example.com" target="_blank" rel="noopener noreferrer">
                <img
                  src="<?= $assetsBase ?>/images/placeholder-logo.svg"
                  width="160"
                  height="160"
                  alt="Logo de Maison Dubois"
                  loading="lazy"
                  decoding="async"
                  data-variant="amber"
                />
                <span>Maison Dubois</span>
              </a>
              <a class="partner-card" role="listitem" href="https://example.com" target="_blank" rel="noopener noreferrer">
                <img
                  src="<?= $assetsBase ?>/images/placeholder-logo.svg"
                  width="160"
                  height="160"
                  alt="Logo de Crédit Bocage"
                  loading="lazy"
                  decoding="async"
                  data-variant="silver"
                />
                <span>Crédit Bocage</span>
              </a>
            </div>
            <button
              type="button"
              class="partner-carousel__control partner-carousel__control--next"
              data-action="scroll-next"
              aria-label="Voir le partenaire suivant"
            ></button>
          </div>
        </div>
      </section>
<?php
require_once __DIR__ . '/templates/footer.php';
