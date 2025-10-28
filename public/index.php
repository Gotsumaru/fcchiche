<?php
declare(strict_types=1);

/**
 * Page principale - FC Chiche
 * SPA (Single Page Application) avec navigation côté client
 */

require_once __DIR__ . '/bootstrap.php';

// Inclure le header
require_once __DIR__ . '/templates/header.php';
?>

    <!-- ==================================================================
         SECTION HERO - VITRE LIQUIDE TRANSPARENTE
         ================================================================== -->
    <main class="liquid-glass-hero">
      <!-- Overlay de vitre liquide transparent au-dessus du fond -->
      <div class="liquid-glass-overlay">
        <!-- Blobs liquides animés automatiquement -->
        <div class="liquid-blob-1"></div>
        <div class="liquid-blob-2"></div>
        <div class="liquid-blob-3"></div>
      </div>

      <!-- Contenu principal (logo, titre, description, CTA) -->
      <div class="hero-content flex flex-col gap-12 items-center text-center max-w-4xl px-4">
        <!-- Logo et titre -->
        <div class="flex flex-col lg:flex-row items-center gap-8 mb-6">
          <img
            src="<?= $assetsBase ?>/images/logo.svg"
            width="120"
            height="120"
            alt="Logo FCChiche"
            class="drop-shadow-2xl"
            style="filter: drop-shadow(0 10px 30px rgba(0, 0, 0, 0.8)) drop-shadow(0 4px 15px rgba(0, 0, 0, 0.6));"
          />
          <div class="flex flex-col gap-4 text-center lg:text-left">
            <h1 class="text-white text-5xl md:text-8xl font-black leading-tight tracking-[-0.033em] drop-shadow-lg" style="text-shadow: 2px 2px 2px #222222;">
              FC Chiché
            </h1>
            <p class="text-primary text-xl md:text-2xl font-bold uppercase tracking-wider" style="text-shadow: 2px 2px 2px #ffffff;">
              Pour l'amour du maillot.
            </p>
          </div>
        </div>

        <!-- Description -->
        <h2 class="text-slate-200 text-lg md:text-xl font-normal leading-relaxed max-w-2xl mx-auto" style="text-shadow: 2px 2px 2px #444444;">
          Le Football Club de Chiché, fondé en 1960, est un club des Deux-Sèvres qui fait vivre la passion du football depuis plus de soixante ans dont il porte fièrement les couleurs vert et blanc
        </h2>

        <!-- Call-to-action -->
        <button class="flex min-w-[120px] max-w-[520px] cursor-pointer items-center justify-center overflow-hidden rounded-xl h-14 px-8 bg-primary text-white text-lg font-bold leading-normal tracking-[0.015em] hover:bg-primary/90 transition-all transform hover:scale-105 shadow-lg">
          <span class="truncate">Voir les prochains matchs</span>
        </button>
      </div>
    </main>

    <!-- ==================================================================
         SECTION ÉVÉNEMENTS
         ================================================================== -->
    <div class="w-full bg-off-white relative z-10">
      <section class="py-16 pb-32 lg:pb-16 relative min-h-screen flex items-center overflow-hidden">

        <!-- Image en arrière-plan avec effet parallaxe -->
        <!-- Desktop: Image à gauche avec 70% de largeur et bords arrondis -->
        <!-- Mobile: Image pleine largeur sans bords arrondis -->
        <div class="absolute left-0 w-full lg:w-[70%] h-[50vh] lg:h-[80vh] lg:rounded-r-[3rem] overflow-hidden">
          <div
            class="parallax-image w-full h-full bg-center bg-no-repeat bg-cover lg:rounded-r-[3rem]"
            style='box-shadow: inset 0px 0px 15px 8px rgba(0,0,0,0.46); background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuAFRxw_8I30mPdxMXVSskeM8CGqAEtNlv78bXPEw3jbH0mXBr72BUJU3oVj3fLDIQqpf0ZsOY7jqly2w3gi4Yy3_uxpT_Qb0TlXyxjCwHRvj68dBOEfxD2zJWLh-9_Hvf7HJX2_d24aHUw-0hqRQ5Nf03_FGK_1-4Po3bmM3pOCvvHOkvuzqmGPtREIzcZLRfGvXdEN60oBhn_KgD8JA17wc8Zje2sPc3ULUUdlXv6l5ldyId0jzgnS8PY6KuvgKVQzeH8XJAXJFMw");'
          ></div>
        </div>

        <!-- Carte événement flottante -->
        <!-- Desktop: Positionnée à droite de l'image et centrée verticalement -->
        <!-- Mobile: Centrée et en dessous de l'image -->
        <div class="w-full max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 flex items-center min-h-[80vh]">
          <div class="ml-auto mr-0 w-full lg:w-auto">
            <div class="flex flex-col lg:flex-row gap-8 items-center justify-end">
              <div
                class="w-full lg:w-[480px] mt-[30vh] lg:mt-0"
              >
                <article class="glass-card-event-dark rounded-2xl p-8 shadow-2xl">
                  <div class="flex flex-col gap-4">
                    <!-- Badge -->
                    <p class="text-primary text-sm font-bold uppercase tracking-wider">
                      Prochain match
                    </p>

                    <!-- Titre -->
                    <h3 class="text-white text-3xl font-bold leading-tight">
                      FCChiche vs. Town United
                    </h3>

                    <!-- Informations du match -->
                    <div class="flex flex-col gap-3 mt-2">
                      <!-- Date -->
                      <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <p class="text-gray-200 text-base font-medium">29 octobre 2024</p>
                      </div>

                      <!-- Heure -->
                      <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-gray-200 text-base font-medium">15:00</p>
                      </div>

                      <!-- Lieu -->
                      <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <p class="text-gray-200 text-base font-medium">The Community Stadium</p>
                      </div>
                    </div>

                    <!-- Description -->
                    <p class="text-gray-300 text-base leading-relaxed mt-2">
                      Le plus grand match de la saison est là. Rejoignez-nous et encouragez l'équipe !
                    </p>

                    <!-- Bouton CTA -->
                    <div class="mt-4">
                      <button class="w-full flex items-center justify-center overflow-hidden rounded-xl h-12 px-6 bg-primary text-white text-base font-bold leading-normal hover:bg-primary/90 transition-all transform hover:scale-[1.02]">
                        <span>Acheter des billets</span>
                      </button>
                    </div>
                  </div>
                </article>
              </div>
            </div>
          </div>
        </div>
      </section>

<?php
// Inclure le footer
require_once __DIR__ . '/templates/footer.php';
?>
