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
          Calendrier officiel
        </p>
        <h1 class="text-white text-4xl md:text-6xl font-black leading-tight drop-shadow-xl">
          Prochains rendez-vous
        </h1>
        <p class="text-slate-200 text-base md:text-lg drop-shadow">
          Retrouvez toutes les dates à venir du FC Chiché pour ne rien manquer.
        </p>
      </div>
    </main>

    <section class="relative z-10 bg-off-white/90 py-20">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="glass-card-event-dark rounded-2xl p-8 shadow-2xl">
          <header class="mb-8">
            <h2 class="text-white text-3xl font-bold">Matchs à venir</h2>
            <p class="text-gray-300 text-sm mt-2">Les horaires peuvent être amenés à évoluer selon les décisions de la ligue.</p>
          </header>

          <div class="space-y-6">
            <?php
            $upcomingMatches = [
              [
                'date' => '27 octobre 2024',
                'time' => '17:30',
                'opponent' => 'FC Nantes (B)',
                'location' => 'Stade Municipal de Chiché',
                'type' => 'Championnat - Journée 9'
              ],
              [
                'date' => '03 novembre 2024',
                'time' => '15:00',
                'opponent' => 'AS Cholet',
                'location' => 'Stade Auguste Bonal',
                'type' => 'Championnat - Journée 10'
              ],
              [
                'date' => '10 novembre 2024',
                'time' => '18:00',
                'opponent' => 'US Saint-Varent',
                'location' => 'Complexe Sportif Saint-Varent',
                'type' => 'Coupe des Deux-Sèvres - 8e'
              ],
              [
                'date' => '17 novembre 2024',
                'time' => '16:00',
                'opponent' => 'AS Bressuire',
                'location' => 'Stade Municipal de Chiché',
                'type' => 'Championnat - Journée 11'
              ],
            ];

            foreach ($upcomingMatches as $match) :
              ?>
              <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 bg-white/5 rounded-xl px-6 py-5">
                <div class="flex flex-col gap-2">
                  <p class="text-primary text-sm font-semibold uppercase tracking-wider">
                    <?= htmlspecialchars($match['type'], ENT_QUOTES, 'UTF-8') ?>
                  </p>
                  <h3 class="text-white text-2xl font-bold">
                    FC Chiché vs <?= htmlspecialchars($match['opponent'], ENT_QUOTES, 'UTF-8') ?>
                  </h3>
                  <p class="text-gray-300 text-sm flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">location_on</span>
                    <?= htmlspecialchars($match['location'], ENT_QUOTES, 'UTF-8') ?>
                  </p>
                </div>
                <div class="flex flex-col items-start lg:items-end gap-2 text-gray-200">
                  <p class="flex items-center gap-2 text-base font-semibold">
                    <span class="material-symbols-outlined text-primary">event</span>
                    <?= htmlspecialchars($match['date'], ENT_QUOTES, 'UTF-8') ?>
                  </p>
                  <p class="flex items-center gap-2 text-sm">
                    <span class="material-symbols-outlined text-primary">schedule</span>
                    Coup d'envoi à <?= htmlspecialchars($match['time'], ENT_QUOTES, 'UTF-8') ?>
                  </p>
                </div>
              </div>
              <?php
            endforeach;
            ?>
          </div>
        </div>
      </div>
    </section>

<?php
require_once __DIR__ . '/templates/footer.php';
