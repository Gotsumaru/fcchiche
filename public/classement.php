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

      <div class="hero-content flex flex-col items-center gap-8 px-4 text-center max-w-4xl">
        <p class="text-primary text-lg font-semibold uppercase tracking-wider drop-shadow-lg">
          Saison 2024 - 2025
        </p>
        <h1 class="text-white text-4xl md:text-6xl font-black leading-tight tracking-tight drop-shadow-xl">
          Classement général
        </h1>
        <p class="text-slate-200 text-base md:text-lg max-w-2xl drop-shadow">
          Suivez la progression du FC Chiché et de ses adversaires tout au long de la saison.
        </p>
      </div>
    </main>

    <section class="relative z-10 bg-off-white/90 py-20">
      <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="glass-card-event-dark rounded-2xl p-8 shadow-2xl">
          <header class="flex flex-col gap-6 md:flex-row md:items-start md:justify-between">
            <div class="space-y-2">
              <h2 class="text-white text-3xl font-bold">Classements par équipe</h2>
              <p class="text-gray-300 text-sm">
                Sélectionnez l'équipe du FC Chiché pour afficher son classement de championnat en temps réel.
              </p>
            </div>
            <span
              class="inline-flex min-h-[42px] items-center justify-center rounded-full bg-primary/20 px-4 py-2 text-sm font-semibold text-primary"
              data-component="classement-meta"
            >
              Chargement des classements…
            </span>
          </header>

          <form class="mt-8 grid gap-4 md:grid-cols-2" data-component="classement-filters" novalidate>
            <label class="flex flex-col gap-2 text-left">
              <span class="text-xs font-semibold uppercase tracking-wide text-gray-300">Équipe</span>
              <select
                class="rounded-xl border border-white/10 bg-white/10 px-4 py-3 text-white shadow-inner focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/60"
                data-component="classement-team-select"
                aria-label="Sélectionner une équipe"
              >
                <option value="">Chargement…</option>
              </select>
            </label>

            <label
              class="hidden flex-col gap-2 text-left"
              data-component="classement-competition-wrapper"
            >
              <span class="text-xs font-semibold uppercase tracking-wide text-gray-300">Compétition</span>
              <select
                class="rounded-xl border border-white/10 bg-white/10 px-4 py-3 text-white shadow-inner focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/60"
                data-component="classement-competition-select"
                aria-label="Sélectionner une compétition"
              ></select>
            </label>
          </form>

          <div class="mt-10 overflow-x-auto" data-component="classement-table" aria-live="polite">
            <p class="text-sm text-gray-300">
              Choisissez une équipe pour afficher son classement.
            </p>
          </div>

          <noscript>
            <p class="mt-6 rounded-xl bg-white/10 p-4 text-sm text-amber-200">
              Activez JavaScript pour consulter les classements dynamiques du club.
            </p>
          </noscript>
        </div>
      </div>
    </section>

<?php
require_once __DIR__ . '/templates/footer.php';
