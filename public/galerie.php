<?php
declare(strict_types=1);

$pageTitle = 'Photos | FC Chiché';

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/templates/header.php';
?>
      <section class="section">
        <div class="container">
          <div class="section__header">
            <span class="section__eyebrow">Souvenirs</span>
            <h1 class="section__title">Galerie photos</h1>
            <p class="section__subtitle">Revivez les plus beaux moments du FC Chiché en images.</p>
          </div>

          <div class="pill-group" role="tablist" aria-label="Filtrer la galerie">
            <button class="pill is-active" type="button">Tous</button>
            <button class="pill" type="button">Séniors</button>
            <button class="pill" type="button">Jeunes</button>
            <button class="pill" type="button">Événements</button>
            <button class="pill" type="button">Vie du club</button>
          </div>

          <div class="gallery-grid" style="margin-top: 2.5rem;">
            <?php
            $photos = [
                [
                    'alt' => "L'équipe première du FC Chiché célèbre sa victoire", 'width' => 641, 'height' => 418,
                    'sizes' => '(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 360px',
                    'fallback' => 'premiere.jpg',
                    'sources' => [
                        ['type' => 'image/webp', 'files' => [
                            ['file' => 'premiere-480.webp', 'descriptor' => '480w'],
                            ['file' => 'premiere-800.webp', 'descriptor' => '800w'],
                            ['file' => 'premiere-1200.webp', 'descriptor' => '1200w'],
                        ]],
                    ],
                ],
                [
                    'alt' => "La réserve du FC Chiché rassemblée avant le coup d'envoi", 'width' => 645, 'height' => 420,
                    'sizes' => '(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 360px',
                    'fallback' => 'Reserve.jpg',
                    'sources' => [
                        ['type' => 'image/webp', 'files' => [
                            ['file' => 'Reserve-480.webp', 'descriptor' => '480w'],
                            ['file' => 'Reserve-800.webp', 'descriptor' => '800w'],
                            ['file' => 'Reserve-1200.webp', 'descriptor' => '1200w'],
                        ]],
                    ],
                ],
                [
                    'alt' => 'Ambiance des tribunes du FC Chiché lors d’un match à domicile', 'width' => 2048, 'height' => 1152,
                    'sizes' => '(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 360px',
                    'fallback' => 'home.jpg',
                    'sources' => [
                        ['type' => 'image/webp', 'files' => [
                            ['file' => 'home-480.webp', 'descriptor' => '480w'],
                            ['file' => 'home-800.webp', 'descriptor' => '800w'],
                            ['file' => 'home-1200.webp', 'descriptor' => '1200w'],
                        ]],
                    ],
                ],
                [
                    'alt' => 'Feuilles de route prêtes pour les matchs du week-end', 'width' => 1792, 'height' => 1024,
                    'sizes' => '(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 360px',
                    'fallback' => 'convocation.jpg',
                    'sources' => [
                        ['type' => 'image/webp', 'files' => [
                            ['file' => 'convocation.webp', 'descriptor' => '1792w'],
                        ]],
                    ],
                ],
                [
                    'alt' => 'Analyse des classements par le staff du FC Chiché', 'width' => 1792, 'height' => 1024,
                    'sizes' => '(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 360px',
                    'fallback' => 'classement.jpg',
                    'sources' => [
                        ['type' => 'image/webp', 'files' => [
                            ['file' => 'classement.webp', 'descriptor' => '1792w'],
                        ]],
                    ],
                ],
                [
                    'alt' => 'Terrain principal du FC Chiché sous le soleil', 'width' => 612, 'height' => 422,
                    'sizes' => '(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 360px',
                    'fallback' => 'terrain.jpg',
                    'sources' => [
                        ['type' => 'image/webp', 'files' => [
                            ['file' => 'terrain.webp', 'descriptor' => '612w'],
                        ]],
                    ],
                ],
            ];

            foreach ($photos as $photo):
              $sources = $photo['sources'] ?? [];
            ?>
              <div class="gallery-item">
                <picture>
                  <?php
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
                      sizes="<?= htmlspecialchars($photo['sizes'], ENT_QUOTES, 'UTF-8') ?>"
                    />
                  <?php endforeach; ?>
                  <img
                    src="<?= $assetsBase ?>/images/<?= htmlspecialchars($photo['fallback'], ENT_QUOTES, 'UTF-8') ?>"
                    width="<?= (int) $photo['width'] ?>"
                    height="<?= (int) $photo['height'] ?>"
                    alt="<?= htmlspecialchars($photo['alt'], ENT_QUOTES, 'UTF-8') ?>"
                    loading="lazy"
                    decoding="async"
                  />
                </picture>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </section>

<?php
require_once __DIR__ . '/templates/footer.php';
