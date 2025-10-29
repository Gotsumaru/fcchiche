<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/templates/header.php';
?>
      <section class="page-hero">
        <div class="page-hero__inner">
          <div class="page-hero__content">
            <span class="page-hero__eyebrow">Performances en temps réel</span>
            <h1 class="page-hero__title">Tous les résultats du FC Chiché</h1>
            <p class="page-hero__subtitle">
              Découvrez les feuilles de match complètes, les buteurs et la dynamique de chaque équipe. Les données sont mises à
              jour après chaque validation officielle.
            </p>
            <div class="chip-list">
              <span class="chip">Scores live</span>
              <span class="chip">Analyse par équipe</span>
              <span class="chip">Historique complet</span>
            </div>
          </div>
          <div class="page-hero__media">
            <img
              src="https://images.unsplash.com/photo-1508098682722-e99c43a406b2?q=80&amp;auto=format&amp;fit=crop&amp;w=1200"
              alt="Joueurs au duel"
              loading="lazy"
            />
          </div>
        </div>
      </section>

      <section class="section">
        <div class="container">
          <div class="panel-stack">
            <form class="panel-card" data-component="results-filters" novalidate>
              <header class="panel-card__header">
                <div>
                  <h2 class="panel-card__title">Filtrer les rencontres</h2>
                  <p class="panel-card__subtitle">
                    Choisissez votre équipe, affinez par compétition puis explorez les feuilles de match détaillées.
                  </p>
                </div>
              </header>
              <div class="panel-card__filters panel-card__filters--inline">
                <label class="panel-card__field">
                  <span>Équipe</span>
                  <select
                    data-component="results-team-select"
                    aria-label="Filtrer les résultats par équipe"
                  >
                    <option value="">Chargement…</option>
                  </select>
                </label>
                <label class="panel-card__field">
                  <span>Compétition</span>
                  <select
                    data-component="results-competition-select"
                    aria-label="Filtrer les résultats par compétition"
                  >
                    <option value="">Toutes compétitions</option>
                    <option value="CH">Championnat</option>
                    <option value="CP">Coupe</option>
                  </select>
                </label>
              </div>
            </form>

            <section class="panel-card panel-card--listing" aria-live="polite">
              <header class="panel-card__header">
                <div>
                  <h2 class="panel-card__title">Derniers matchs</h2>
                  <p class="panel-card__subtitle">Résumés officiels avec buteurs, cartons et statistiques clés.</p>
                </div>
              </header>
              <div class="panel-card__body panel-card__body--grid" data-component="results-list">
                <p class="panel-card__placeholder">Choisissez une équipe pour afficher ses dernières rencontres.</p>
              </div>
              <noscript>
                <p class="panel-card__notice">Activez JavaScript pour consulter les résultats dynamiques du club.</p>
              </noscript>
            </section>
          </div>
        </div>
      </section>

      <section class="section section--tint">
        <div class="container">
          <div class="highlight-grid">
            <article class="highlight-card">
              <h3 class="highlight-card__title">Statistiques clés</h3>
              <p>
                Chaque feuille de match propose les buteurs, passes décisives et la dynamique des cinq derniers matchs pour mieux
                préparer les rencontres.
              </p>
              <ul class="bullet-list">
                <li>Séries de victoires et forme du moment</li>
                <li>Comparaison directe avec l'adversaire</li>
                <li>Accès rapide aux feuilles PDF officielles</li>
              </ul>
            </article>
            <article class="highlight-card">
              <h3 class="highlight-card__title">Focus équipe fanion</h3>
              <p>
                Les seniors A restent invaincus à domicile depuis le début de saison. Retrouvez les temps forts et interviews
                d'après-match chaque semaine.
              </p>
              <div class="pill-list">
                <span>Interviews vidéo</span>
                <span>Analyse tactique</span>
                <span>Moments forts</span>
              </div>
            </article>
          </div>
        </div>
      </section>

<?php
require_once __DIR__ . '/templates/footer.php';
