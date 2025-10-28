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
          Restons en contact
        </p>
        <h1 class="text-white text-4xl md:text-6xl font-black leading-tight drop-shadow-xl">
          Le FC Chiché à votre écoute
        </h1>
        <p class="text-slate-200 text-base md:text-lg drop-shadow">
          Une question, un partenariat, une inscription ? L'équipe vous répond rapidement.
        </p>
      </div>
    </main>

    <section class="relative z-10 bg-off-white/90 py-20">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid gap-10 lg:grid-cols-[1.2fr_1fr]">
          <div class="glass-card-event-dark rounded-2xl p-8 shadow-2xl">
            <h2 class="text-white text-3xl font-bold mb-6">Envoyez-nous un message</h2>
            <form class="space-y-6" method="post" action="#" autocomplete="off">
              <div>
                <label for="name" class="block text-sm font-semibold text-gray-200 mb-2">Nom complet</label>
                <input
                  id="name"
                  name="name"
                  type="text"
                  required
                  class="w-full rounded-xl border border-white/10 bg-white/10 px-4 py-3 text-white placeholder-gray-400 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/40"
                  placeholder="Jean Dupont"
                />
              </div>

              <div>
                <label for="email" class="block text-sm font-semibold text-gray-200 mb-2">Adresse e-mail</label>
                <input
                  id="email"
                  name="email"
                  type="email"
                  required
                  class="w-full rounded-xl border border-white/10 bg-white/10 px-4 py-3 text-white placeholder-gray-400 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/40"
                  placeholder="vous@exemple.com"
                />
              </div>

              <div>
                <label for="subject" class="block text-sm font-semibold text-gray-200 mb-2">Sujet</label>
                <input
                  id="subject"
                  name="subject"
                  type="text"
                  required
                  class="w-full rounded-xl border border-white/10 bg-white/10 px-4 py-3 text-white placeholder-gray-400 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/40"
                  placeholder="Objet de votre message"
                />
              </div>

              <div>
                <label for="message" class="block text-sm font-semibold text-gray-200 mb-2">Message</label>
                <textarea
                  id="message"
                  name="message"
                  rows="5"
                  required
                  class="w-full rounded-xl border border-white/10 bg-white/10 px-4 py-3 text-white placeholder-gray-400 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/40"
                  placeholder="Détaillez votre demande..."
                ></textarea>
              </div>

              <button
                type="submit"
                class="w-full flex items-center justify-center rounded-xl bg-primary px-6 py-3 text-base font-bold text-white transition-transform duration-200 hover:bg-primary/90 hover:scale-[1.02]"
              >
                Envoyer
              </button>
            </form>
          </div>

          <aside class="glass-card-event-dark rounded-2xl p-8 shadow-2xl flex flex-col gap-6">
            <div>
              <h3 class="text-white text-xl font-semibold mb-2">Siège du club</h3>
              <p class="text-gray-300 text-sm leading-relaxed">
                Stade Municipal de Chiché<br />
                Rue du Stade<br />
                79350 Chiché, France
              </p>
            </div>

            <div class="flex flex-col gap-2 text-gray-300 text-sm">
              <p class="flex items-center gap-3">
                <span class="material-symbols-outlined text-primary">call</span>
                05 49 00 00 00
              </p>
              <p class="flex items-center gap-3">
                <span class="material-symbols-outlined text-primary">mail</span>
                contact@fcchiche.fr
              </p>
              <p class="flex items-center gap-3">
                <span class="material-symbols-outlined text-primary">schedule</span>
                Permanence : mercredi 18h - 20h
              </p>
            </div>

            <div class="flex flex-col gap-3">
              <h3 class="text-white text-xl font-semibold">Rejoignez la communauté</h3>
              <p class="text-gray-300 text-sm">
                Retrouvez toutes les actualités du club sur nos réseaux sociaux.
              </p>
              <div class="flex gap-4">
                <a href="#" class="text-gray-300 hover:text-primary transition-transform duration-200 hover:scale-110" aria-label="Instagram">
                  <svg fill="currentColor" height="28" viewBox="0 0 24 24" width="28" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.85s-.012 3.584-.07 4.85c-.148 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07s-3.584-.012-4.85-.07c-3.25-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.85s.012-3.584.07-4.85C2.25 3.854 3.726 2.31 6.978 2.163 8.244 2.105 8.622 2.093 12 2.093m0-2.093c-3.264 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948s.014 3.667.072 4.947c.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072s3.667-.014 4.947-.072c4.358-.2 6.78-2.618 6.98-6.98.059-1.281.073-1.689.073-4.948s-.014-3.667-.072-4.947c-.2-4.358-2.618-6.78-6.98-6.98C15.667.014 15.264 0 12 0zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.406-11.845a1.44 1.44 0 1 0 0 2.88 1.44 1.44 0 0 0 0-2.88z" />
                  </svg>
                </a>
                <a href="#" class="text-gray-300 hover:text-primary transition-transform duration-200 hover:scale-110" aria-label="Facebook">
                  <svg fill="currentColor" height="28" viewBox="0 0 24 24" width="28" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                  </svg>
                </a>
              </div>
            </div>
          </aside>
        </div>
      </div>
    </section>

<?php
require_once __DIR__ . '/templates/footer.php';
