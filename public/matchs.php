<?php
declare(strict_types=1);

$pageTitle = 'Matchs | FC Chiché';

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/templates/header.php';
?>
      <section class="section">
        <div class="container">
          <div class="section__header">
            <span class="section__eyebrow">Agenda</span>
            <h1 class="section__title">Prochains matchs</h1>
            <p class="section__subtitle">Retrouvez le calendrier officiel et filtrez par équipe et compétition.</p>
          </div>
          <div class="filter-bar" role="group" aria-label="Filtres du calendrier">
            <div class="filter-bar__field">
              <label class="label" for="calendar-team">Équipe</label>
              <select class="select" id="calendar-team" data-component="calendar-team-select"></select>
            </div>
            <div class="filter-bar__field">
              <label class="label" for="calendar-competition">Compétition</label>
              <select class="select" id="calendar-competition" data-component="calendar-competition-select">
                <option value="">Toutes les compétitions</option>
                <option value="CH">Championnat</option>
                <option value="CP">Coupe de France / Coupes</option>
              </select>
            </div>
          </div>

          <div class="calendar-list" data-component="calendar-list" aria-live="polite"></div>
        </div>
      </section>

      <section class="section section--alt" id="telechargement-calendrier">
        <div class="container">
          <div class="section__header">
            <span class="section__eyebrow">Saison</span>
            <h2 class="section__title">Calendrier de la saison</h2>
            <p class="section__subtitle">Téléchargez la version prête à l'impression et ajoutez-la à votre vestiaire.</p>
            <div class="hero__actions" style="margin-top: 2rem;">
              <a class="btn btn--primary" href="<?= $assetsBase ?>/docs/calendrier-fcchiche.pdf" download>Télécharger le calendrier PDF</a>
            </div>
          </div>
        </div>
      </section>

      <section class="section">
        <div class="container">
          <div class="calendar-highlight">
            <div class="calendar-highlight__visual" aria-hidden="true">
              <div class="image-placeholder">Visuel 640x640</div>
            </div>
            <div class="calendar-highlight__content">
              <h2>Préparez vos déplacements</h2>
              <p>
                Chaque feuille de route indique l'heure de convocation, le lieu de rendez-vous et le type de compétition. Pensez
                à vérifier la mise à jour des terrains en cas d'intempéries.
              </p>
              <ul>
                <li>Notification automatique 48h avant le match</li>
                <li>Feuilles de route envoyées par les éducateurs</li>
                <li>Bénévoles logistique disponibles sur demande</li>
              </ul>
            </div>
          </div>
        </div>
      </section>

<?php
require_once __DIR__ . '/templates/footer.php';
