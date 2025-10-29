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
        <div class="flex flex-col gap-8">
          <form class="glass-card-event-dark rounded-2xl p-6 shadow-xl" data-component="results-filters" novalidate>
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
              <div class="space-y-2">
                <h2 class="text-white text-2xl font-bold">Derniers résultats par équipe</h2>
                <p class="text-sm text-gray-300">
                  Le dernier match apparaît toujours en haut de la liste.
                </p>
              </div>
              <div class="flex w-full flex-col gap-4 md:w-auto md:flex-row md:items-end">
                <label class="flex w-full flex-col gap-2 text-left md:w-72">
                  <span class="text-xs font-semibold uppercase tracking-wide text-gray-300">Équipe</span>
                  <select
                    class="rounded-xl border border-white/10 bg-white/10 px-4 py-3 text-white shadow-inner focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/60"
                    data-component="results-team-select"
                    aria-label="Filtrer les résultats par équipe"
                  >
                    <option value="">Chargement…</option>
                  </select>
                </label>
                <label class="flex w-full flex-col gap-2 text-left md:w-64">
                  <span class="text-xs font-semibold uppercase tracking-wide text-gray-300">Type de compétition</span>
                  <select
                    class="rounded-xl border border-white/10 bg-white/10 px-4 py-3 text-white shadow-inner focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/60"
                    data-component="results-competition-select"
                    aria-label="Filtrer les résultats par type de compétition"
                  >
                    <option value="">Toutes compétitions</option>
                    <option value="CH">Championnat</option>
                    <option value="CP">Coupe</option>
                  </select>
                </label>
              </div>
            </div>
          </form>

          <div
            class="grid gap-8 md:grid-cols-2"
            data-component="results-list"
            aria-live="polite"
          >
            <p class="glass-card-event-dark rounded-2xl p-6 text-sm text-gray-300">
              Choisissez une équipe pour afficher ses derniers résultats officiels.
            </p>
          </div>

          <noscript>
            <p class="rounded-2xl bg-white/10 p-4 text-sm text-amber-200">
              Activez JavaScript pour consulter les résultats dynamiques du FC Chiché.
            </p>
          </noscript>
        </div>
      </div>
    </section>

<?php
require_once __DIR__ . '/templates/footer.php';
