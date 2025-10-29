      </main>

      <footer class="site-footer">
        <div class="site-footer__inner">
          <div class="site-footer__grid">
            <div class="site-footer__block">
              <div class="site-footer__brand">
                <img
                  src="<?= $assetsBase ?>/images/logo.svg"
                  width="48"
                  height="48"
                  alt="Logo FC Chiché"
                />
                <div>
                  <p class="site-footer__title">FC Chiché</p>
                  <p class="site-footer__subtitle">Club des Deux-Sèvres depuis 1960</p>
                </div>
              </div>
              <p class="site-footer__text">
                Le FC Chiché anime la vie footballistique locale avec un engagement fort pour la formation, la convivialité et le
                respect des valeurs du sport.
              </p>
            </div>

            <div class="site-footer__block">
              <h2 class="site-footer__heading">Club</h2>
              <ul class="site-footer__links">
                <li><a href="<?= $basePath ?>/calendrier">Calendrier des matchs</a></li>
                <li><a href="<?= $basePath ?>/resultats">Résultats officiels</a></li>
                <li><a href="<?= $basePath ?>/classement">Classements</a></li>
                <li><a href="<?= $basePath ?>/contact">Nous contacter</a></li>
              </ul>
            </div>

            <div class="site-footer__block">
              <h2 class="site-footer__heading">Nous suivre</h2>
              <ul class="site-footer__links">
                <li><a href="#" rel="noreferrer">Instagram</a></li>
                <li><a href="#" rel="noreferrer">Facebook</a></li>
                <li><a href="#" rel="noreferrer">YouTube</a></li>
              </ul>
              <a class="site-footer__cta" href="mailto:contact@fcchiche.fr">contact@fcchiche.fr</a>
            </div>
          </div>

          <div class="site-footer__bottom">
            <p>© <?= date('Y') ?> FC Chiché — Tous droits réservés.</p>
            <div class="site-footer__legal">
              <a href="#">Mentions légales</a>
              <span aria-hidden="true">•</span>
              <a href="#">Politique de confidentialité</a>
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
