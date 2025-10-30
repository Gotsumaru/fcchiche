      </main>

      <footer class="app-footer">
        <div class="app-footer__inner">
          <div class="app-footer__top">
            <div class="app-footer__brand">
              <img
                src="<?= $assetsBase ?>/images/logo.svg"
                width="52"
                height="52"
                alt="Logo FC Chiché"
                loading="lazy"
              />
              <div class="app-footer__brand-text">
                <p class="app-footer__title">FC Chiché</p>
                <p class="app-footer__subtitle">Club de football amateur du bocage bressuirais</p>
              </div>
            </div>
            <div class="app-footer__actions">
              <a class="btn btn--secondary" href="<?= $basePath ?>/matchs">Voir le calendrier</a>
              <a class="btn btn--ghost" href="<?= $basePath ?>/contact">Prendre contact</a>
            </div>
          </div>

          <div class="app-footer__grid">
            <div class="app-footer__column">
              <h2>Navigation</h2>
              <ul>
                <li><a href="<?= $basePath ?>/">Accueil</a></li>
                <li><a href="<?= $basePath ?>/resultats">Résultats</a></li>
                <li><a href="<?= $basePath ?>/matchs">Calendrier</a></li>
                <li><a href="<?= $basePath ?>/classements">Classements</a></li>
                <li><a href="<?= $basePath ?>/contact">Contact</a></li>
              </ul>
            </div>
            <div class="app-footer__column">
              <h2>Vie du club</h2>
              <ul>
                <li><a href="<?= $basePath ?>/matchs#telechargement-calendrier">Télécharger le calendrier</a></li>
                <li><a href="<?= $basePath ?>/contact">Demander une licence</a></li>
                <li><a href="<?= $basePath ?>/">Actualités du club</a></li>
              </ul>
            </div>
            <div class="app-footer__column">
              <h2>Nous suivre</h2>
              <ul class="app-footer__socials">
                <li><a href="#" aria-label="Instagram">Instagram</a></li>
                <li><a href="#" aria-label="Facebook">Facebook</a></li>
                <li><a href="#" aria-label="YouTube">YouTube</a></li>
              </ul>
              <a class="app-footer__contact" href="mailto:contact@fcchiche.fr">contact@fcchiche.fr</a>
              <p class="app-footer__schedule">Accueil : Lundi - Vendredi 18h-20h · Week-end selon matchs</p>
            </div>
          </div>

          <div class="app-footer__bottom">
            <p>© FC Chiché 2025 – Tous droits réservés</p>
            <div class="app-footer__legal">
              <a href="#">Mentions légales</a>
              <span aria-hidden="true">•</span>
              <a href="#">Politique de confidentialité</a>
            </div>
          </div>
        </div>
      </footer>
    </div>

  <script src="<?= $assetsBase ?>/js/api.js"></script>
  <script src="<?= $assetsBase ?>/js/common.js"></script>
  <?php
  $pageJS = __DIR__ . '/../assets/js/' . $currentPage . '.js';
  if (file_exists($pageJS)) {
      echo '<script src="' . $assetsBase . '/js/' . $currentPage . '.js"></script>';
  }
  ?>
</body>
</html>
