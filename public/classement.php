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
          <header class="flex flex-col md:flex-row md:items-center md:justify-between gap-6 mb-8">
            <div>
              <h2 class="text-white text-3xl font-bold">Classement de la poule</h2>
              <p class="text-gray-300 text-sm">Mise à jour hebdomadaire après chaque journée de championnat.</p>
            </div>
            <span class="inline-flex items-center justify-center rounded-full bg-primary/20 text-primary text-sm font-semibold px-4 py-2">
              Dernière mise à jour : 24 octobre 2024
            </span>
          </header>

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-white/10">
              <thead>
                <tr class="text-left text-gray-200 text-xs uppercase tracking-wide">
                  <th class="py-3 pr-4">Rang</th>
                  <th class="py-3 pr-4">Club</th>
                  <th class="py-3 pr-4 text-center">J</th>
                  <th class="py-3 pr-4 text-center">G</th>
                  <th class="py-3 pr-4 text-center">N</th>
                  <th class="py-3 pr-4 text-center">P</th>
                  <th class="py-3 pr-4 text-center">Diff</th>
                  <th class="py-3 text-center">Pts</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-white/10 text-gray-100">
                <tr class="bg-white/5">
                  <td class="py-3 pr-4 font-semibold text-primary">1</td>
                  <td class="py-3 pr-4 font-semibold">FC Chiché</td>
                  <td class="py-3 pr-4 text-center">8</td>
                  <td class="py-3 pr-4 text-center">6</td>
                  <td class="py-3 pr-4 text-center">1</td>
                  <td class="py-3 pr-4 text-center">1</td>
                  <td class="py-3 pr-4 text-center">+14</td>
                  <td class="py-3 text-center text-lg font-bold">19</td>
                </tr>
                <tr>
                  <td class="py-3 pr-4">2</td>
                  <td class="py-3 pr-4">AS Bressuire</td>
                  <td class="py-3 pr-4 text-center">8</td>
                  <td class="py-3 pr-4 text-center">5</td>
                  <td class="py-3 pr-4 text-center">2</td>
                  <td class="py-3 pr-4 text-center">1</td>
                  <td class="py-3 pr-4 text-center">+9</td>
                  <td class="py-3 text-center font-semibold">17</td>
                </tr>
                <tr class="bg-white/5">
                  <td class="py-3 pr-4">3</td>
                  <td class="py-3 pr-4">Parthenay FC</td>
                  <td class="py-3 pr-4 text-center">8</td>
                  <td class="py-3 pr-4 text-center">4</td>
                  <td class="py-3 pr-4 text-center">2</td>
                  <td class="py-3 pr-4 text-center">2</td>
                  <td class="py-3 pr-4 text-center">+5</td>
                  <td class="py-3 text-center font-semibold">14</td>
                </tr>
                <tr>
                  <td class="py-3 pr-4">4</td>
                  <td class="py-3 pr-4">US Thouars</td>
                  <td class="py-3 pr-4 text-center">8</td>
                  <td class="py-3 pr-4 text-center">3</td>
                  <td class="py-3 pr-4 text-center">2</td>
                  <td class="py-3 pr-4 text-center">3</td>
                  <td class="py-3 pr-4 text-center">+1</td>
                  <td class="py-3 text-center font-semibold">11</td>
                </tr>
                <tr class="bg-white/5">
                  <td class="py-3 pr-4">5</td>
                  <td class="py-3 pr-4">SC Niort</td>
                  <td class="py-3 pr-4 text-center">8</td>
                  <td class="py-3 pr-4 text-center">2</td>
                  <td class="py-3 pr-4 text-center">3</td>
                  <td class="py-3 pr-4 text-center">3</td>
                  <td class="py-3 pr-4 text-center">-2</td>
                  <td class="py-3 text-center font-semibold">9</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </section>

<?php
require_once __DIR__ . '/templates/footer.php';
