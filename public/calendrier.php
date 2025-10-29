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
          <header class="mb-8 space-y-4">
            <div>
              <h2 class="text-white text-3xl font-bold">Matchs à venir par équipe</h2>
              <p class="mt-2 text-sm text-gray-300">
                Les informations sont synchronisées directement depuis l'API fédérale.
              </p>
            </div>
            <label class="flex flex-col gap-2 text-left md:w-80">
              <span class="text-xs font-semibold uppercase tracking-wide text-gray-300">Équipe</span>
              <select
                class="rounded-xl border border-white/10 bg-white/10 px-4 py-3 text-white shadow-inner focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/60"
                data-component="calendar-team-select"
                aria-label="Filtrer le calendrier par équipe"
              >
                <option value="">Chargement…</option>
              </select>
            </label>
          </header>

          <div class="space-y-6" data-component="calendar-list" aria-live="polite">
            <p class="rounded-xl bg-white/10 p-4 text-sm text-gray-300">
              Sélectionnez une équipe pour afficher son calendrier des prochains matchs.
            </p>
          </div>

          <noscript>
            <p class="mt-6 rounded-xl bg-white/10 p-4 text-sm text-amber-200">
              Activez JavaScript pour consulter le calendrier dynamique du FC Chiché.
            </p>
          </noscript>
        </div>
      </div>
    </section>

<?php
require_once __DIR__ . '/templates/footer.php';
