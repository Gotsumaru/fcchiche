<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/templates/header.php';
?>
      <section class="page-hero">
        <div class="page-hero__inner">
          <div class="page-hero__content">
            <span class="page-hero__eyebrow">Tous les rendez-vous officiels</span>
            <h1 class="page-hero__title">Calendrier 2024&nbsp;/&nbsp;2025</h1>
            <p class="page-hero__subtitle">
              Visualisez chaque journée, les déplacements à venir et les événements spéciaux du FC Chiché. Les informations
              sont synchronisées automatiquement avec la fédération.
            </p>
            <div class="chip-list">
              <span class="chip">Mise à jour en direct</span>
              <span class="chip">Synchronisé FFF</span>
              <span class="chip">Mobile first</span>
            </div>
            <div class="landing-hero__actions">
              <a class="btn btn--primary" href="<?= $basePath ?>/resultats">Voir les derniers résultats</a>
              <a class="btn btn--ghost" href="<?= $basePath ?>/classement">Consulter les classements</a>
            </div>
          </div>
          <div class="page-hero__media">
            <img
              src="https://images.unsplash.com/photo-1509023464722-18d996393ca8?q=80&amp;auto=format&amp;fit=crop&amp;w=1200"
              alt="Stade éclairé de nuit"
              loading="lazy"
            />
          </div>
        </div>
      </section>

      <section class="section">
        <div class="container">
          <div class="panel-grid">
            <article class="panel-card">
              <header class="panel-card__header">
                <div>
                  <h2 class="panel-card__title">Matchs à venir</h2>
                  <p class="panel-card__subtitle">
                    Filtrez par équipe et compétition pour organiser vos week-ends football en un clin d'œil.
                  </p>
                </div>
              </header>
              <form class="panel-card__filters" data-component="calendar-team-form" novalidate>
                <label class="panel-card__field">
                  <span>Équipe</span>
                  <select
                    data-component="calendar-team-select"
                    aria-label="Filtrer le calendrier par équipe"
                  >
                    <option value="">Chargement…</option>
                  </select>
                </label>
                <label class="panel-card__field">
                  <span>Compétition</span>
                  <select
                    data-component="calendar-competition-select"
                    aria-label="Filtrer le calendrier par type de compétition"
                  >
                    <option value="">Toutes compétitions</option>
                    <option value="CH">Championnat</option>
                    <option value="CP">Coupe</option>
                  </select>
                </label>
              </form>
              <div class="panel-card__body" data-component="calendar-list" aria-live="polite">
                <p class="panel-card__placeholder">
                  Sélectionnez une équipe pour afficher son calendrier des prochains matchs.
                </p>
              </div>
              <noscript>
                <p class="panel-card__notice">Activez JavaScript pour consulter le calendrier interactif.</p>
              </noscript>
            </article>

            <article class="panel-card">
              <header class="panel-card__header">
                <div>
                  <h2 class="panel-card__title">Focus prochain match à domicile</h2>
                  <p class="panel-card__subtitle">
                    Les supporters se retrouvent 90 minutes avant le coup d'envoi autour de la fan zone et de la boutique
                    officielle.
                  </p>
                </div>
              </header>
              <div class="match-list">
                <article class="match-item">
                  <div class="match-item__header">
                    <span>Samedi 9 novembre</span>
                    <span>Championnat D1</span>
                  </div>
                  <div class="match-item__teams">
                    <span class="match-item__team">FC Chiché</span>
                    <span class="match-item__score">vs</span>
                    <span class="match-item__team">US Parthenay</span>
                  </div>
                  <p>Entrée gratuite pour les moins de 12 ans — Coup d'envoi 18h00 — Animations partenaires dès 16h30.</p>
                </article>
                <article class="match-item">
                  <div class="match-item__header">
                    <span>Dimanche 17 novembre</span>
                    <span>Coupe des Deux-Sèvres</span>
                  </div>
                  <div class="match-item__teams">
                    <span class="match-item__team">FC Chiché B</span>
                    <span class="match-item__score">vs</span>
                    <span class="match-item__team">US Airvault</span>
                  </div>
                  <p>Buvette solidaire au profit de l'école de foot — Tirage au sort tombola à la mi-temps.</p>
                </article>
              </div>
            </article>
          </div>
        </div>
      </section>

      <section class="section section--tint">
        <div class="container">
          <div class="highlight-grid">
            <article class="highlight-card">
              <h3 class="highlight-card__title">Matchday Experience</h3>
              <p>
                Parkings fléchés, point billetterie express et restauration locale : tout est pensé pour fluidifier votre arrivée
                au stade.
              </p>
              <ul class="bullet-list">
                <li>Ouverture des portes 90 minutes avant match</li>
                <li>Application web pour vos billets dématérialisés</li>
                <li>Espace kids avec animations encadrées</li>
              </ul>
            </article>
            <article class="highlight-card">
              <h3 class="highlight-card__title">Notifications calendrier</h3>
              <p>
                Ajoutez les matchs à votre agenda Google ou Apple en un clic et recevez une alerte la veille de chaque
                rencontre.
              </p>
              <div class="pill-list">
                <span>Rappel J-1</span>
                <span>Itinéraire intégré</span>
                <span>Météo du stade</span>
              </div>
            </article>
          </div>
        </div>
      </section>

<?php
require_once __DIR__ . '/templates/footer.php';
