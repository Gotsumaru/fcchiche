<?php
declare(strict_types=1);

$pageTitle = 'Classements | FC Chiché';

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/templates/header.php';
?>
      <section class="section">
        <div class="container">
          <div class="section__header">
            <span class="section__eyebrow">Compétitions</span>
            <h1 class="section__title">Classements officiels</h1>
            <p class="section__subtitle">Suivez l'évolution des équipes engagées en championnat et en coupe.</p>
          </div>

          <div class="filter-bar" role="group" aria-label="Filtres du classement">
            <div class="filter-bar__field">
              <label class="label" for="classement-team">Équipe</label>
              <select class="select" id="classement-team" data-component="classement-team-select"></select>
            </div>
            <div
              class="filter-bar__field"
              data-component="classement-competition-wrapper"
            >
              <label class="label" for="classement-competition">Compétition</label>
              <select class="select" id="classement-competition" data-component="classement-competition-select"></select>
            </div>
          </div>

          <div class="classement-meta" aria-live="polite">
            <span class="classement-meta__badge" data-component="classement-meta">Chargement des équipes…</span>
          </div>

          <div class="table-shell" role="region" aria-label="Tableau des classements">
            <div class="classement-table" data-component="classement-table"></div>
          </div>
        </div>
      </section>

      <section class="section section--alt">
        <div class="container">
          <div class="classement-highlight">
            <div class="classement-highlight__content">
              <h2>Comprendre les indicateurs</h2>
              <p>
                Les classements affichent les informations officielles : points, matches joués, différence de buts et série de
                résultats. Les données sont issues des feuilles de match validées et ne tiennent pas compte des sanctions en
                attente d'homologation.
              </p>
              <ul>
                <li>Actualisation quotidienne via les API de la ligue</li>
                <li>Identification immédiate des équipes du FC Chiché</li>
                <li>Export possible au format CSV sur demande</li>
              </ul>
            </div>
            <div class="classement-highlight__visual" aria-hidden="true">
              <div class="image-placeholder" aria-hidden="true">VISUEL 720×520</div>
            </div>
          </div>
        </div>
      </section>

<?php
require_once __DIR__ . '/templates/footer.php';
