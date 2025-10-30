<?php
declare(strict_types=1);

$pageTitle = 'Résultats | FC Chiché';

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/templates/header.php';
?>
      <section class="section">
        <div class="container">
          <div class="section__header">
            <span class="section__eyebrow">Performances</span>
            <h1 class="section__title">Résultats des équipes</h1>
            <p class="section__subtitle">Retrouvez les scores officiels de toutes les équipes du FC Chiché.</p>
          </div>
          <div class="filter-bar" role="group" aria-label="Filtres des résultats">
            <div class="filter-bar__field">
              <label class="label" for="results-team">Équipe</label>
              <select class="select" id="results-team" data-component="results-team-select"></select>
            </div>
            <div class="filter-bar__field">
              <label class="label" for="results-competition">Compétition</label>
              <select class="select" id="results-competition" data-component="results-competition-select">
                <option value="">Toutes les compétitions</option>
                <option value="CH">Championnat</option>
                <option value="CP">Coupe de France / Coupes</option>
              </select>
            </div>
          </div>

          <div class="results-grid" data-component="results-list" aria-live="polite"></div>
        </div>
      </section>

      <section class="section section--alt">
        <div class="container">
          <div class="result-info">
            <div class="result-info__visual" aria-hidden="true">
              <img
                src="https://images.unsplash.com/photo-1547955922-999ecf17f9b1?auto=format&fit=crop&w=960&q=80"
                width="960"
                height="640"
                alt="Feuilles de match officielles signées"
                loading="lazy"
              />
            </div>
            <div class="result-info__content">
              <h2>Lecture des codes compétition</h2>
              <p>
                Les résultats sont synchronisés automatiquement depuis les bases officielles de la FFF. Les codes CH et CP
                correspondent respectivement aux matches de championnat et aux rencontres de coupe. Les scores sont mis à jour
                dans les minutes qui suivent la validation des feuilles de match.
              </p>
              <div class="result-info__actions">
                <a class="btn btn--secondary" href="<?= $basePath ?>/matchs">Voir le calendrier</a>
                <a class="btn btn--ghost" href="<?= $basePath ?>/classements">Consulter les classements</a>
              </div>
            </div>
          </div>
        </div>
      </section>

<?php
require_once __DIR__ . '/templates/footer.php';
