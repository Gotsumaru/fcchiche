      </main><!-- /#page-content -->

      <!-- ==================================================================
           FOOTER - LIQUID GLASS DESIGN
           ================================================================== -->
      <footer class="footer-shell relative mt-auto w-full pt-16 pb-24 lg:mt-24 lg:pb-12 min-h-[400px]">
        <!-- Image de fond pour le footer -->
        <div class="absolute inset-0 overflow-hidden">
          <div
            class="w-full h-full bg-center bg-no-repeat bg-cover opacity-60"
            style='background-image: url("https://images.unsplash.com/photo-1574629810360-7efbbe195018?q=80&w=2940&auto=format&fit=crop");'
          ></div>
        </div>

        <div class="max-w-6xl mx-auto lg:px-4 lg:px-6 lg:px-8 relative z-10">
          <!-- Conteneur avec effet liquid glass -->
          <div class="glass-card-event-dark lg:rounded-2xl p-8 lg:p-12">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center md:text-left">

              <!-- Colonne 1: Informations club -->
              <div class="flex flex-col items-center md:items-start gap-4">
                <div class="flex items-center gap-3">
                  <img src="<?= $assetsBase ?>/images/logo.svg" width="40" height="40" alt="Logo FCChiche" />
                  <h2 class="text-white text-xl font-bold">FC Chiché</h2>
                </div>
                <p class="text-gray-300 text-sm">© 2024 FC Chiché</p>
                <p class="text-gray-300 text-sm">Tous droits réservés.</p>
                <a href="mailto:info@fcchiche.com" class="text-primary hover:text-primary/80 transition-colors text-sm font-medium">
                  info@fcchiche.com
                </a>
              </div>

              <!-- Colonne 2: Réseaux sociaux -->
              <div class="flex flex-col items-center md:items-start gap-4">
                <h3 class="font-bold text-white tracking-wide uppercase text-sm">Suivez-nous</h3>
                <div class="flex gap-4">
                  <a href="#" class="text-gray-300 hover:text-primary transition-all transform hover:scale-110" aria-label="Twitter">
                    <svg fill="currentColor" height="28" viewbox="0 0 24 24" width="28" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                      <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"></path>
                    </svg>
                  </a>
                  <a href="#" class="text-gray-300 hover:text-primary transition-all transform hover:scale-110" aria-label="Instagram">
                    <svg fill="currentColor" height="28" viewbox="0 0 24 24" width="28" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                      <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.85s-.012 3.584-.07 4.85c-.148 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07s-3.584-.012-4.85-.07c-3.25-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.85s.012-3.584.07-4.85C2.25 3.854 3.726 2.31 6.978 2.163 8.244 2.105 8.622 2.093 12 2.093m0-2.093c-3.264 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948s.014 3.667.072 4.947c.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072s3.667-.014 4.947-.072c4.358-.2 6.78-2.618 6.98-6.98.059-1.281.073-1.689.073-4.948s-.014-3.667-.072-4.947c-.2-4.358-2.618-6.78-6.98-6.98C15.667.014 15.264 0 12 0zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.406-11.845a1.44 1.44 0 1 0 0 2.88 1.44 1.44 0 0 0 0-2.88z"></path>
                    </svg>
                  </a>
                  <a href="#" class="text-gray-300 hover:text-primary transition-all transform hover:scale-110" aria-label="Facebook">
                    <svg fill="currentColor" height="28" viewbox="0 0 24 24" width="28" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                      <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"></path>
                    </svg>
                  </a>
                </div>
              </div>

              <!-- Colonne 3: Liens rapides -->
              <div class="flex flex-col items-center md:items-start gap-4">
                <h3 class="font-bold text-white tracking-wide uppercase text-sm">Liens rapides</h3>
                <nav class="flex flex-col gap-2">
                  <a href="#" class="text-gray-300 hover:text-primary transition-colors text-sm">Mentions légales</a>
                  <a href="#" class="text-gray-300 hover:text-primary transition-colors text-sm">Politique de confidentialité</a>
                  <a href="#" class="text-gray-300 hover:text-primary transition-colors text-sm">Nous rejoindre</a>
                  <a href="#" class="text-gray-300 hover:text-primary transition-colors text-sm">Devenir partenaire</a>
                </nav>
              </div>
            </div>

            <!-- Séparateur -->
            <div class="w-full h-px bg-gradient-to-r from-transparent via-gray-600 to-transparent my-8"></div>

            <!-- Ligne de copyright et slogan -->
            <div class="flex flex-col md:flex-row justify-between items-center gap-4 text-center md:text-left">
              <p class="text-gray-400 text-sm">
                Fondé en 1960 • Plus de 60 ans de passion footballistique
              </p>
              <p class="text-primary font-bold text-sm uppercase tracking-wider">
                Pour l'amour du maillot.
              </p>
            </div>
          </div>
        </div>
      </footer>
    </div>

  <!-- ====================================================================
       SCRIPTS
       ==================================================================== -->
  <!-- Common JavaScript -->
  <script src="<?= $assetsBase ?>/js/api.js"></script>
  <script src="<?= $assetsBase ?>/js/common.js"></script>

  <!-- Page-specific JavaScript -->
  <?php
  $pageJS = __DIR__ . '/../assets/js/' . $currentPage . '.js';
  if (file_exists($pageJS)) {
    echo '<script src="' . $assetsBase . '/js/' . $currentPage . '.js"></script>';
  }
  ?>
</body>
</html>
