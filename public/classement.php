<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/templates/header.php';
?>
      <section class="page-hero">
        <div class="page-hero__inner">
          <div class="page-hero__content">
            <span class="page-hero__eyebrow">Saison 2024&nbsp;/&nbsp;2025</span>
            <h1 class="page-hero__title">Classements officiels</h1>
            <p class="page-hero__subtitle">
              Comparez la progression de nos équipes, suivez les séries positives et identifiez les confrontations clés à venir.
              Les données sont consolidées depuis les compétitions fédérales.
            </p>
            <div class="chip-list">
              <span class="chip">Mises à jour automatiques</span>
              <span class="chip">Comparaisons adversaires</span>
              <span class="chip">Visualisation mobile</span>
            </div>
          </div>
          <div class="page-hero__media">
            <img
              src="https://images.unsplash.com/photo-1517649763962-0c623066013b?q=80&amp;auto=format&amp;fit=crop&amp;w=1200"
              alt="Analyse tactique sur tablette"
              loading="lazy"
            />
          </div>
        </div>
      </section>

      <section class="section">
        <div class="container">
          <article class="panel-card">
            <header class="panel-card__header panel-card__header--split">
              <div>
                <h2 class="panel-card__title">Classements par équipe</h2>
                <p class="panel-card__subtitle">
                  Sélectionnez l'équipe du FC Chiché et parcourez son classement, ses statistiques et la forme récente.
                </p>
              </div>
              <span class="status-pill" data-component="classement-meta">Chargement…</span>
            </header>
            <form class="panel-card__filters" data-component="classement-filters" novalidate>
              <label class="panel-card__field">
                <span>Équipe</span>
                <select
                  data-component="classement-team-select"
                  aria-label="Sélectionner une équipe"
                >
                  <option value="">Chargement…</option>
                </select>
              </label>
              <label class="panel-card__field panel-card__field--hidden" data-component="classement-competition-wrapper">
                <span>Compétition</span>
                <select
                  data-component="classement-competition-select"
                  aria-label="Sélectionner une compétition"
                ></select>
              </label>
            </form>
            <div class="panel-card__body" data-component="classement-table" aria-live="polite">
              <p class="panel-card__placeholder">Choisissez une équipe pour afficher son classement officiel.</p>
            </div>
            <noscript>
              <p class="panel-card__notice">Activez JavaScript pour consulter les classements dynamiques.</p>
            </noscript>
          </article>
        </div>
      </section>

      <section class="section section--tint">
        <div class="container">
          <div class="highlight-grid">
            <article class="highlight-card">
              <h3 class="highlight-card__title">Lecture simplifiée</h3>
              <p>
                Points, différence de buts, forme sur cinq matchs : les métriques essentielles sont regroupées pour préparer vos
                débriefs et séances vidéo.
              </p>
              <ul class="bullet-list">
                <li>Surlignage automatique des équipes FC Chiché</li>
                <li>Notation des séries (victoires/nuls/défaites)</li>
                <li>Export direct vers vos présentations staff</li>
              </ul>
            </article>
            <article class="highlight-card">
              <h3 class="highlight-card__title">Anticiper les déplacements</h3>
              <p>
                Les outils de planification vous indiquent le prochain adversaire, la distance à parcourir et l'horaire de
                convocation conseillé.
              </p>
              <div class="pill-list">
                <span>Distance estimée</span>
                <span>Point GPS</span>
                <span>Feuille de route</span>
              </div>
            </article>
          </div>
        </div>
      </section>

<?php
require_once __DIR__ . '/templates/footer.php';
