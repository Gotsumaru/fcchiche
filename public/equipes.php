<?php
declare(strict_types=1);

$pageTitle = 'Nos équipes | FC Chiché';

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/templates/header.php';
?>
      <section class="section">
        <div class="container">
          <div class="section__header">
            <span class="section__eyebrow">Effectifs</span>
            <h1 class="section__title">Nos équipes</h1>
            <p class="section__subtitle">Sélectionnez votre catégorie et découvrez la composition de chaque groupe.</p>
          </div>

          <div class="pill-group" role="tablist" aria-label="Filtrer les équipes">
            <button class="pill is-active" type="button">Toutes</button>
            <button class="pill" type="button">Séniors</button>
            <button class="pill" type="button">U17</button>
            <button class="pill" type="button">U15</button>
            <button class="pill" type="button">U13</button>
          </div>

          <?php
          $teams = [
              [
                  'href' => $basePath . '/equipes/fcchiche1.php',
                  'title' => 'FC Chiché 1',
                  'subtitle' => 'Seniors Départemental 3 – Phase 1 – Poule 1',
                  'image' => [
                      'alt' => "L'équipe fanion du FC Chiché prête pour le coup d'envoi",
                      'width' => 641,
                      'height' => 418,
                      'sizes' => '(max-width: 768px) 100vw, 320px',
                      'fallback' => [
                          'file' => 'premiere.jpg',
                          'type' => 'image/jpeg',
                      ],
                      'sources' => [
                          [
                              'type' => 'image/webp',
                              'files' => [
                                  ['file' => 'premiere-480.webp', 'descriptor' => '480w'],
                                  ['file' => 'premiere-800.webp', 'descriptor' => '800w'],
                                  ['file' => 'premiere-1200.webp', 'descriptor' => '1200w'],
                              ],
                          ],
                      ],
                  ],
              ],
              [
                  'href' => $basePath . '/equipes/fcchiche2.php',
                  'title' => 'FC Chiché 2',
                  'subtitle' => 'Seniors Départemental 4 – Phase 1 – Poule 2',
                  'image' => [
                      'alt' => "Le groupe réserve du FC Chiché réuni autour de son staff",
                      'width' => 645,
                      'height' => 420,
                      'sizes' => '(max-width: 768px) 100vw, 320px',
                      'fallback' => [
                          'file' => 'Reserve.jpg',
                          'type' => 'image/jpeg',
                      ],
                      'sources' => [
                          [
                              'type' => 'image/webp',
                              'files' => [
                                  ['file' => 'Reserve-480.webp', 'descriptor' => '480w'],
                                  ['file' => 'Reserve-800.webp', 'descriptor' => '800w'],
                                  ['file' => 'Reserve-1200.webp', 'descriptor' => '1200w'],
                              ],
                          ],
                      ],
                  ],
              ],
              [
                  'href' => $basePath . '/equipes/u17.php',
                  'title' => 'FC Chiché U17',
                  'subtitle' => 'U17 Régional 2 – Groupe C',
                  'image' => [
                      'alt' => "Les U17 du FC Chiché en pleine séance de travail tactique",
                      'width' => 2048,
                      'height' => 1152,
                      'sizes' => '(max-width: 768px) 100vw, 320px',
                      'fallback' => [
                          'file' => 'deuxieme.jpg',
                          'type' => 'image/jpeg',
                      ],
                      'sources' => [],
                  ],
              ],
              [
                  'href' => $basePath . '/equipes/u15.php',
                  'title' => 'FC Chiché U15',
                  'subtitle' => 'U15 Départemental 1 – Poule Nord',
                  'image' => [
                      'alt' => "Les U15 du FC Chiché réunis avant le match",
                      'width' => 646,
                      'height' => 421,
                      'sizes' => '(max-width: 768px) 100vw, 320px',
                      'fallback' => [
                          'file' => 'U15.jpg',
                          'type' => 'image/jpeg',
                      ],
                      'sources' => [
                          [
                              'type' => 'image/webp',
                              'files' => [
                                  ['file' => 'u15-480.webp', 'descriptor' => '480w'],
                                  ['file' => 'u15-800.webp', 'descriptor' => '800w'],
                                  ['file' => 'u15-1200.webp', 'descriptor' => '1200w'],
                              ],
                          ],
                      ],
                  ],
              ],
              [
                  'href' => $basePath . '/equipes/u13.php',
                  'title' => 'FC Chiché U13',
                  'subtitle' => 'U13 Départemental – Plateau Bocage',
                  'image' => [
                      'alt' => "L'équipe U13 du FC Chiché réunie autour de son éducateur",
                      'width' => 647,
                      'height' => 419,
                      'sizes' => '(max-width: 768px) 100vw, 320px',
                      'fallback' => [
                          'file' => 'U13.jpg',
                          'type' => 'image/jpeg',
                      ],
                      'sources' => [],
                  ],
              ],
          ];
          ?>
          <div class="team-grid" style="margin-top: 2.5rem;">
            <?php foreach ($teams as $team):
              $image = $team['image'];
            ?>
              <a class="team-card" href="<?= htmlspecialchars($team['href'], ENT_QUOTES, 'UTF-8') ?>">
                <div class="team-card__media">
                  <picture>
                    <?php
                    $sources = $image['sources'] ?? [];
                    foreach ($sources as $source):
                        $srcsetEntries = [];
                        foreach ($source['files'] as $entry) {
                            $srcsetEntries[] = $assetsBase . '/images/' . $entry['file'] . ' ' . $entry['descriptor'];
                        }
                        $srcset = implode(', ', $srcsetEntries);
                    ?>
                      <source
                        srcset="<?= htmlspecialchars($srcset, ENT_QUOTES, 'UTF-8') ?>"
                        type="<?= htmlspecialchars($source['type'], ENT_QUOTES, 'UTF-8') ?>"
                        sizes="<?= htmlspecialchars($image['sizes'], ENT_QUOTES, 'UTF-8') ?>"
                      />
                    <?php endforeach; ?>
                    <img
                      src="<?= $assetsBase ?>/images/<?= htmlspecialchars($image['fallback']['file'], ENT_QUOTES, 'UTF-8') ?>"
                      width="<?= (int) $image['width'] ?>"
                      height="<?= (int) $image['height'] ?>"
                      alt="<?= htmlspecialchars($image['alt'], ENT_QUOTES, 'UTF-8') ?>"
                      loading="lazy"
                      decoding="async"
                    />
                  </picture>
                </div>
                <div>
                  <h3><?= htmlspecialchars($team['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                  <p><?= htmlspecialchars($team['subtitle'], ENT_QUOTES, 'UTF-8') ?></p>
                </div>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
      </section>

<?php
require_once __DIR__ . '/templates/footer.php';
