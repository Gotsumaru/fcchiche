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
          style="background-image: url('https://images.unsplash.com/photo-1489515217757-5fd1be406fef?auto=format&fit=crop&w=1800&q=80');"
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
            <a class="btn btn--secondary" href="<?= $basePath ?>/matchs">Matchs à venir</a>
          </div>
        </div>
      </section>

      <section class="section">
        <div class="container">
          <div class="section__header">
            <span class="section__eyebrow">Le club</span>
            <h2 class="section__title">Un club, une famille, une histoire</h2>
            <p class="section__subtitle">
              Fondé en 1946, le FC Chiché fait vibrer le bocage depuis plusieurs générations. Le club regroupe aujourd'hui des
              équipes séniors, U17, U15 et U13, animées par la passion, le respect et la convivialité.
            </p>
            <div class="hero__actions" style="margin-top: 2.5rem;">
              <a class="btn btn--primary" href="<?= $basePath ?>/equipes">Découvrir nos équipes</a>
            </div>
          </div>
        </div>
      </section>

      <section class="section section--alt">
        <div class="container">
          <div class="section__header">
            <span class="section__eyebrow">Actualités</span>
            <h2 class="section__title">Actualités récentes</h2>
          </div>
          <div class="news-grid">
            <article class="news-card">
              <div class="news-card__image">
                <img src="https://images.unsplash.com/photo-1517927033932-b3d18e61fb3a?auto=format&fit=crop&w=1600&q=80" alt="Victoire des séniors" />
              </div>
              <div class="news-card__content">
                <span class="card-meta">Équipe Séniors</span>
                <h3 class="news-card__title">Victoire des séniors face à L'Absie — 2–0 !</h3>
                <p class="news-card__excerpt">Un match maîtrisé et un doublé décisif pour offrir trois points précieux au FC Chiché.</p>
              </div>
            </article>
            <article class="news-card">
              <div class="news-card__image">
                <img src="https://images.unsplash.com/photo-1505672678657-cc7037095e2c?auto=format&fit=crop&w=1600&q=80" alt="Tournoi jeunes" />
              </div>
              <div class="news-card__content">
                <span class="card-meta">Jeunes</span>
                <h3 class="news-card__title">Tournoi jeunes : une journée sous le soleil du bocage</h3>
                <p class="news-card__excerpt">Des sourires, des buts et une ambiance conviviale pour toutes les catégories U13 à U17.</p>
              </div>
            </article>
            <article class="news-card">
              <div class="news-card__image">
                <img src="https://images.unsplash.com/photo-1489515217757-5fd1be406fef?auto=format&fit=crop&w=1600&q=80" alt="Soirée partenaires" />
              </div>
              <div class="news-card__content">
                <span class="card-meta">Partenaires</span>
                <h3 class="news-card__title">Retour sur la soirée partenaires 2025</h3>
                <p class="news-card__excerpt">Le club a dévoilé sa feuille de route 2025 devant plus de 40 entreprises locales réunies.</p>
              </div>
            </article>
          </div>
        </div>
      </section>

      <section class="section">
        <div class="container">
          <div class="section__header">
            <span class="section__eyebrow">Soutiens</span>
            <h2 class="section__title">Nos partenaires</h2>
            <p class="section__subtitle">Merci à toutes les entreprises qui soutiennent le FC Chiché.</p>
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
