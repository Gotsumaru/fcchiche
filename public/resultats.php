<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/templates/header.php';
?>

    <main class="liquid-glass-hero">
      <div class="liquid-glass-overlay">
        <div class="liquid-blob-1"></div>
        <div class="liquid-blob-2"></div>
        <div class="liquid-blob-3"></div>
      </div>

      <div class="hero-content flex flex-col items-center gap-6 px-4 text-center max-w-3xl">
        <p class="text-primary text-lg font-semibold uppercase tracking-wider drop-shadow-lg">
          Résultats officiels
        </p>
        <h1 class="text-white text-4xl md:text-6xl font-black leading-tight drop-shadow-xl">
          Les dernières rencontres
        </h1>
        <p class="text-slate-200 text-base md:text-lg drop-shadow">
          Résumé des performances du club toutes compétitions confondues.
        </p>
      </div>
    </main>

    <section class="relative z-10 bg-off-white/90 py-20">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid gap-8 md:grid-cols-2">
          <?php
          $matches = [
            [
              'date' => '20 octobre 2024',
              'competition' => 'Championnat - Journée 8',
              'opponent' => 'AS Bressuire',
              'score' => '2 - 1',
              'summary' => "Victoire dans les dernières minutes grâce à un but de Martin sur coup franc."
            ],
            [
              'date' => '13 octobre 2024',
              'competition' => 'Championnat - Journée 7',
              'opponent' => 'US Thouars',
              'score' => '3 - 0',
              'summary' => "Solide prestation défensive et doublé de Le Gall dans le dernier quart d'heure."
            ],
            [
              'date' => '06 octobre 2024',
              'competition' => 'Coupe des Deux-Sèvres - 16e',
              'opponent' => 'SC Niort',
              'score' => '1 - 1 (4-3 TAB)',
              'summary' => "Qualification aux tirs au but après une rencontre très disputée."
            ],
            [
              'date' => '29 septembre 2024',
              'competition' => 'Championnat - Journée 6',
              'opponent' => 'Parthenay FC',
              'score' => '0 - 0',
              'summary' => "Match fermé mais un point précieux à l'extérieur."
            ],
          ];

          foreach ($matches as $match) :
            ?>
            <article class="glass-card-event-dark rounded-2xl p-6 shadow-xl">
              <header class="flex flex-col gap-2">
                <p class="text-primary text-sm font-semibold uppercase tracking-wider">
                  <?= htmlspecialchars($match['competition'], ENT_QUOTES, 'UTF-8') ?>
                </p>
                <h2 class="text-white text-2xl font-bold">
                  FC Chiché vs <?= htmlspecialchars($match['opponent'], ENT_QUOTES, 'UTF-8') ?>
                </h2>
              </header>
              <div class="mt-4 flex flex-col gap-3 text-gray-200">
                <p class="text-sm flex items-center gap-2">
                  <span class="material-symbols-outlined text-primary">event</span>
                  <?= htmlspecialchars($match['date'], ENT_QUOTES, 'UTF-8') ?>
                </p>
                <p class="text-4xl font-black text-white drop-shadow">
                  <?= htmlspecialchars($match['score'], ENT_QUOTES, 'UTF-8') ?>
                </p>
                <p class="text-sm leading-relaxed text-gray-300">
                  <?= htmlspecialchars($match['summary'], ENT_QUOTES, 'UTF-8') ?>
                </p>
              </div>
            </article>
            <?php
          endforeach;
          ?>
        </div>
      </div>
    </section>

<?php
require_once __DIR__ . '/templates/footer.php';
