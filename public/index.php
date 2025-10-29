<?php
declare(strict_types=1);

/**
 * Page d'accueil - FC Chiché
 */

require_once __DIR__ . '/bootstrap.php';

require_once __DIR__ . '/templates/header.php';
?>
      <section class="landing-hero">
        <div class="landing-hero__inner">
          <div>
            <span class="landing-hero__badge">FC Chiché 1960</span>
            <h1 class="landing-hero__title">Le vert et blanc dans une nouvelle dynamique</h1>
            <p class="landing-hero__subtitle">
              FC Chiché modernise son expérience supporters : un suivi temps réel des équipes, une identité affirmée et des
              rendez-vous qui rassemblent toute la commune.
            </p>
            <div class="landing-hero__actions">
              <a class="btn btn--primary" href="<?= $basePath ?>/calendrier">Découvrir le calendrier</a>
              <a class="btn btn--ghost" href="<?= $basePath ?>/resultats">Derniers résultats</a>
            </div>
            <aside class="score-panel">
              <div class="score-panel__label">Prochain rendez-vous</div>
              <div class="score-panel__match">
                <div class="score-panel__team">
                  <span>FC Chiché</span>
                  <span class="score-panel__hint">Stade de La Broche</span>
                </div>
                <div class="score-panel__score">18h00</div>
                <div class="score-panel__team">
                  <span>US Parthenay</span>
                  <span class="score-panel__hint">Samedi 9 novembre</span>
                </div>
              </div>
              <div class="score-panel__details">
                <span>Championnat Départemental 1</span>
                <span>Ouverture des portes : 16h30 — Restauration locale</span>
              </div>
            </aside>
          </div>
          <div class="landing-hero__media">
            <img
              src="https://images.unsplash.com/photo-1489515217757-5fd1be406fef?q=80&amp;auto=format&amp;fit=crop&amp;w=1200"
              alt="Joueur du FC Chiché célébrant un but"
              loading="lazy"
            />
          </div>
        </div>
      </section>

      <section class="section">
        <div class="container">
          <div class="section__header">
            <span class="section__eyebrow">Identité club</span>
            <h2 class="section__title">Une vision ambitieuse ancrée dans les Deux-Sèvres</h2>
            <p class="section__subtitle">
              L'équipe dirigeante, les bénévoles et les supporters travaillent de concert pour faire rayonner FC Chiché des U6
              aux seniors. Chaque rencontre est pensée pour offrir un moment de partage.
            </p>
          </div>
          <div class="feature-grid">
            <article class="feature-card">
              <div class="feature-card__icon" aria-hidden="true">⚽</div>
              <h3>Formation exigeante</h3>
              <p>
                Éducateurs diplômés, suivi scolaire renforcé, séances vidéo : le parcours vert et blanc accompagne chaque joueur
                dans sa progression.
              </p>
              <a class="feature-card__link" href="<?= $basePath ?>/contact">Rejoindre l'école de foot</a>
            </article>
            <article class="feature-card">
              <div class="feature-card__icon" aria-hidden="true">🎟️</div>
              <h3>Expérience supporters</h3>
              <p>
                Billetterie en ligne, espaces familles et animations partenaires assurent une ambiance chaleureuse à La
                Broche.
              </p>
              <a class="feature-card__link" href="<?= $basePath ?>/calendrier">Préparer ma venue</a>
            </article>
            <article class="feature-card">
              <div class="feature-card__icon" aria-hidden="true">🤝</div>
              <h3>Communauté solidaire</h3>
              <p>
                70 bénévoles, un réseau de partenaires fidèles et des initiatives solidaires toute l'année au service du
                territoire.
              </p>
              <a class="feature-card__link" href="<?= $basePath ?>/contact">Devenir bénévole</a>
            </article>
          </div>
          <div class="stat-ribbon">
            <div class="stat-ribbon__item">
              <div class="stat-ribbon__value">180+</div>
              <div class="stat-ribbon__label">Licenciés engagés</div>
            </div>
            <div class="stat-ribbon__item">
              <div class="stat-ribbon__value">9 équipes</div>
              <div class="stat-ribbon__label">Des U6 aux seniors</div>
            </div>
            <div class="stat-ribbon__item">
              <div class="stat-ribbon__value">1 200</div>
              <div class="stat-ribbon__label">Supporters chaque saison</div>
            </div>
            <div class="stat-ribbon__item">
              <div class="stat-ribbon__value">60 ans</div>
              <div class="stat-ribbon__label">De passion partagée</div>
            </div>
          </div>
        </div>
      </section>

      <section class="section section--tint">
        <div class="container">
          <div class="story-split">
            <div class="story-split__media">
              <img
                src="https://images.unsplash.com/photo-1517927033932-b3d18e61fb3a?q=80&amp;auto=format&amp;fit=crop&amp;w=1200"
                alt="Coup d'envoi dans le stade de La Broche"
                loading="lazy"
              />
              <span class="story-split__badge">Matchday FC Chiché</span>
            </div>
            <div class="story-split__content">
              <span class="section__eyebrow">Au cœur du jeu</span>
              <h2 class="section__title">Suivez nos équipes sur toutes les compétitions</h2>
              <p class="section__subtitle">
                Calendrier interactif, fiches matchs, classements détaillés : les données officielles sont consolidées dans une
                interface fluide pensée pour le mobile comme pour le desktop.
              </p>
              <ul class="bullet-list">
                <li>Classements mis à jour automatiquement après validation fédérale</li>
                <li>Résumés de matchs avec buteurs et faits marquants</li>
                <li>Filtres multi-équipes pour naviguer rapidement</li>
              </ul>
              <div class="landing-hero__actions">
                <a class="btn btn--primary" href="<?= $basePath ?>/resultats">Consulter les résultats</a>
                <a class="btn btn--ghost" href="<?= $basePath ?>/classement">Voir les classements</a>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section class="section">
        <div class="container">
          <div class="highlight-grid">
            <article class="highlight-card">
              <h3 class="highlight-card__title">Projets infrastructures</h3>
              <p>
                Le terrain d'honneur sera équipé d'une nouvelle éclairage LED et d'une tribune couverte pour améliorer le
                confort supporters.
              </p>
              <p class="section__subtitle">Mise en service prévue pour mars 2025.</p>
            </article>
            <article class="highlight-card">
              <h3 class="highlight-card__title">Académie féminine</h3>
              <p>
                Un pôle dédié accompagne la section féminine du FC Chiché avec deux créneaux hebdomadaires et un suivi
                individualisé.
              </p>
              <div class="pill-list">
                <span>U13F</span>
                <span>U16F</span>
                <span>Senior F</span>
              </div>
            </article>
          </div>
          <div class="cta-panel">
            <div>
              <h2 class="section__title">Newsletter FC Chiché Inside</h2>
              <p>
                Matchs, événements partenaires, actions solidaires : recevez une fois par mois l'essentiel de l'actualité du
                club.
              </p>
            </div>
            <form class="cta-panel__form" method="post" action="#" novalidate>
              <label class="sr-only" for="newsletter-email">Adresse e-mail</label>
              <input
                id="newsletter-email"
                name="email"
                type="email"
                placeholder="prenom.nom@email.com"
                autocomplete="email"
                required
              />
              <button class="btn btn--accent" type="submit">Je m'inscris</button>
            </form>
          </div>
        </div>
      </section>

<?php
require_once __DIR__ . '/templates/footer.php';
