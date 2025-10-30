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
            <p class="section__subtitle">Retrouvez ici tous les scores et bilans de nos équipes.</p>
          </div>

          <div class="pill-group" role="tablist" aria-label="Filtrer les résultats">
            <button class="pill is-active" type="button">Séniors</button>
            <button class="pill" type="button">U17</button>
            <button class="pill" type="button">U15</button>
            <button class="pill" type="button">U13</button>
          </div>

          <div class="section" style="padding-top: 2.5rem; padding-bottom: 2.5rem;">
            <div class="table-shell" role="region" aria-label="Tableau des résultats">
              <table class="table">
                <thead>
                  <tr>
                    <th scope="col">Date</th>
                    <th scope="col">Compétition</th>
                    <th scope="col">Rencontre</th>
                    <th scope="col">Score</th>
                    <th scope="col">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>18 mai 2025</td>
                    <td>Championnat</td>
                    <td>Inter Bocage FC vs FC Chiché</td>
                    <td>—</td>
                    <td>
                      <a class="btn btn--ghost btn--icon" href="<?= $basePath ?>/fiche-match.html">Détails du match</a>
                    </td>
                  </tr>
                  <tr>
                    <td>11 mai 2025</td>
                    <td>Championnat</td>
                    <td>FC Chiché vs L'Absie</td>
                    <td>2 – 0</td>
                    <td>
                      <a class="btn btn--ghost btn--icon" href="<?= $basePath ?>/fiche-match.html">Détails du match</a>
                    </td>
                  </tr>
                  <tr>
                    <td>4 mai 2025</td>
                    <td>Coupe</td>
                    <td>FC Bressuire vs FC Chiché</td>
                    <td>1 – 1 (3–4 tab)</td>
                    <td>
                      <a class="btn btn--ghost btn--icon" href="<?= $basePath ?>/fiche-match.html">Détails du match</a>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <div class="section__header" style="margin-top: 0;">
            <span class="section__eyebrow">Derniers matchs joués</span>
            <h2 class="section__title">Le résumé du week-end</h2>
          </div>

          <div class="news-grid">
            <article class="glass-card">
              <div class="glass-card__media">
                <img src="https://images.unsplash.com/photo-1434648957308-5e6a859697e8?auto=format&fit=crop&w=1400&q=80" alt="Match senior" />
              </div>
              <div>
                <h3 class="glass-card__title">Séniors A — Victoire 2–0</h3>
                <p class="glass-card__excerpt">Deux buts signés Martin et Lucas pour conserver l'invincibilité à domicile.</p>
              </div>
            </article>
            <article class="glass-card">
              <div class="glass-card__media">
                <img src="https://images.unsplash.com/photo-1543328995-bbeeb2c0f855?auto=format&fit=crop&w=1400&q=80" alt="Match U17" />
              </div>
              <div>
                <h3 class="glass-card__title">U17 — Succès 3–1 à Parthenay</h3>
                <p class="glass-card__excerpt">Une prestation collective avec trois buteurs différents et une défense solide.</p>
              </div>
            </article>
            <article class="glass-card">
              <div class="glass-card__media">
                <img src="https://images.unsplash.com/photo-1471295253337-3ceaaedca402?auto=format&fit=crop&w=1400&q=80" alt="Match U15" />
              </div>
              <div>
                <h3 class="glass-card__title">U15 — Match nul 1–1</h3>
                <p class="glass-card__excerpt">Une égalisation à la dernière minute pour décrocher un point important.</p>
              </div>
            </article>
          </div>
        </div>
      </section>

<?php
require_once __DIR__ . '/templates/footer.php';
