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
                    'category' => 'seniors',
                    'title' => 'Seniors — communion avec le public',
                    'description' => 'Instantané pris après un succès à domicile.',
                    'image' => [
                        'alt' => 'Album seniors du FC Chiché après une victoire à domicile',
                        'width' => 2005,
                        'height' => 1888,
                        'sizes' => '(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 360px',
                        'src' => 'galeries/442481364_943464594244213_5499343482462645514_n.jpg',
                        'srcset' => [
                            ['file' => 'galeries/442481364_943464594244213_5499343482462645514_n.jpg', 'descriptor' => '2005w'],
                        ],
                    ],
                ],
                [
                    'category' => 'seniors',
                    'title' => 'Seniors — ambiance à Chiché',
                    'description' => 'La tribune principale vit la rencontre au rythme du groupe fanion.',
                    'image' => [
                        'alt' => 'Public et joueurs du FC Chiché réunis pour un match seniors',
                        'width' => 1578,
                        'height' => 1564,
                        'sizes' => '(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 360px',
                        'src' => 'galeries/445351737_943464437577562_900514706040850129_n.jpg',
                        'srcset' => [
                            ['file' => 'galeries/445351737_943464437577562_900514706040850129_n.jpg', 'descriptor' => '1578w'],
                        ],
                    ],
                ],
                [
                    'category' => 'jeunes',
                    'title' => 'Jeunes — entraînement collectif',
                    'description' => 'Les catégories de formation affinent leur technique sur la pelouse.',
                    'image' => [
                        'alt' => 'Séance d’entraînement des jeunes licenciés du FC Chiché',
                        'width' => 1792,
                        'height' => 1024,
                        'sizes' => '(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 360px',
                        'src' => 'galeries/76ac98b24fbd9dcea187ba544db6e770.jpg',
                        'srcset' => [
                            ['file' => 'galeries/76ac98b24fbd9dcea187ba544db6e770.jpg', 'descriptor' => '1792w'],
                        ],
                    ],
                ],
                [
                    'category' => 'jeunes',
                    'title' => 'Jeunes — ateliers techniques',
                    'description' => 'Focus sur la progression et l’esprit d’équipe des sections jeunes.',
                    'image' => [
                        'alt' => 'Travail technique des catégories jeunes du FC Chiché',
                        'width' => 1792,
                        'height' => 1024,
                        'sizes' => '(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 360px',
                        'src' => 'galeries/684399bd8f6dc425b7441f37e5b6ce36.jpg',
                        'srcset' => [
                            ['file' => 'galeries/684399bd8f6dc425b7441f37e5b6ce36.jpg', 'descriptor' => '1792w'],
                        ],
                    ],
                ],
                [
                    'category' => 'evenements',
                    'title' => 'Événements — ambiance tribune',
                    'description' => 'Les supporters se retrouvent autour des animations du club.',
                    'image' => [
                        'alt' => 'Ambiance des supporters du FC Chiché lors d’un événement du club',
                        'width' => 2048,
                        'height' => 1152,
                        'sizes' => '(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 360px',
                        'src' => 'galeries/0344a63bc84ae9c3f7ebcfc1cde08d16.jpg',
                        'srcset' => [
                            ['file' => 'galeries/0344a63bc84ae9c3f7ebcfc1cde08d16.jpg', 'descriptor' => '2048w'],
                        ],
                    ],
                ],
                [
                    'category' => 'evenements',
                    'title' => 'Événements — rencontre partenaires',
                    'description' => 'Un moment convivial partagé avec l’écosystème du club.',
                    'image' => [
                        'alt' => 'Soirée partenaires du FC Chiché au club-house',
                        'width' => 4272,
                        'height' => 2848,
                        'sizes' => '(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 360px',
                        'src' => 'galeries/e3e53bbb1f97be7fba3471d14e57a4dc.jpg',
                        'srcset' => [
                            ['file' => 'galeries/e3e53bbb1f97be7fba3471d14e57a4dc.jpg', 'descriptor' => '4272w'],
                        ],
                    ],
                ],
                [
                    'category' => 'vie-club',
                    'title' => 'Vie du club — bénévoles mobilisés',
                    'description' => 'Les équipes administratives préparent l’accueil des licenciés.',
                    'image' => [
                        'alt' => 'Bénévoles du FC Chiché à l’œuvre dans le club-house',
                        'width' => 1080,
                        'height' => 617,
                        'sizes' => '(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 360px',
                        'src' => 'bureau.jpg',
                        'srcset' => [
                            ['file' => 'bureau.jpg', 'descriptor' => '1080w'],
                        ],
                    ],
                ],
                [
                    'category' => 'vie-club',
                    'title' => 'Vie du club — convivialité à la buvette',
                    'description' => 'L’esprit associatif et festif les jours de match.',
                    'image' => [
                        'alt' => 'Buvette du FC Chiché animée avant une rencontre',
                        'width' => 1792,
                        'height' => 1024,
                        'sizes' => '(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 360px',
                        'src' => 'buvette.jpg',
                        'srcset' => [
                            ['file' => 'buvette.jpg', 'descriptor' => '1792w'],
                        ],
                    ],
                ],
            ];

            foreach ($photos as $photo):
              $image = $photo['image'];
              $srcsetEntries = [];
              foreach ($image['srcset'] as $entry) {
                  $srcsetEntries[] = $assetsBase . '/images/' . $entry['file'] . ' ' . $entry['descriptor'];
              }
              $srcset = implode(', ', $srcsetEntries);
            ?>
              <figure
                class="gallery-item"
                data-category="<?= htmlspecialchars($photo['category'], ENT_QUOTES, 'UTF-8') ?>"
              >
                <picture>
                  <img
                    src="<?= $assetsBase ?>/images/<?= htmlspecialchars($image['src'], ENT_QUOTES, 'UTF-8') ?>"
                    <?php if ($srcset !== ''): ?>
                      srcset="<?= htmlspecialchars($srcset, ENT_QUOTES, 'UTF-8') ?>"
                      sizes="<?= htmlspecialchars($image['sizes'], ENT_QUOTES, 'UTF-8') ?>"
                    <?php endif; ?>
                    width="<?= (int) $image['width'] ?>"
                    height="<?= (int) $image['height'] ?>"
                    alt="<?= htmlspecialchars($image['alt'], ENT_QUOTES, 'UTF-8') ?>"
                    loading="lazy"
                    decoding="async"
                  />
                </picture>
                <figcaption class="gallery-item__caption">
                  <span class="gallery-item__label"><?= htmlspecialchars($photo['title'], ENT_QUOTES, 'UTF-8') ?></span>
                  <span class="gallery-item__meta"><?= htmlspecialchars($photo['description'], ENT_QUOTES, 'UTF-8') ?></span>
                </figcaption>
              </figure>
            <?php endforeach; ?>
          </div>
        </div>
      </section>

<?php
require_once __DIR__ . '/templates/footer.php';
