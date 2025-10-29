<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/templates/header.php';
?>
      <section class="page-hero">
        <div class="page-hero__inner">
          <div class="page-hero__content">
            <span class="page-hero__eyebrow">Restons connectés</span>
            <h1 class="page-hero__title">Contactez le FC Chiché</h1>
            <p class="page-hero__subtitle">
              Licences, partenariats, presse ou bénévolat : notre équipe vous accompagne et répond rapidement à vos demandes.
            </p>
            <div class="chip-list">
              <span class="chip">Réponse sous 48h</span>
              <span class="chip">Partenaires &amp; bénévoles</span>
              <span class="chip">Support licences</span>
            </div>
          </div>
          <div class="page-hero__media">
            <img
              src="https://images.unsplash.com/photo-1521412644187-c49fa049e84d?q=80&amp;auto=format&amp;fit=crop&amp;w=1200"
              alt="Supporters rassemblés autour du stade"
              loading="lazy"
            />
          </div>
        </div>
      </section>

      <section class="section">
        <div class="container">
          <div class="contact-grid">
            <article class="panel-card contact-card">
              <header class="panel-card__header">
                <div>
                  <h2 class="panel-card__title">Écrivez-nous</h2>
                  <p class="panel-card__subtitle">Complétez le formulaire, nous revenons vers vous au plus vite.</p>
                </div>
              </header>
              <form class="contact-card__form" method="post" action="#" autocomplete="off" novalidate>
                <label class="panel-card__field">
                  <span>Nom complet</span>
                  <input id="name" name="name" type="text" placeholder="Jean Dupont" required />
                </label>
                <label class="panel-card__field">
                  <span>Adresse e-mail</span>
                  <input id="email" name="email" type="email" placeholder="vous@exemple.com" autocomplete="email" required />
                </label>
                <label class="panel-card__field">
                  <span>Téléphone</span>
                  <input id="phone" name="phone" type="tel" placeholder="06 00 00 00 00" autocomplete="tel" />
                </label>
                <label class="panel-card__field">
                  <span>Objet</span>
                  <input id="subject" name="subject" type="text" placeholder="Objet de votre message" required />
                </label>
                <label class="panel-card__field">
                  <span>Message</span>
                  <textarea id="message" name="message" rows="5" placeholder="Détaillez votre demande…" required></textarea>
                </label>
                <button class="btn btn--primary" type="submit">Envoyer</button>
              </form>
            </article>

            <aside class="panel-card contact-card">
              <div class="panel-card__header">
                <h2 class="panel-card__title">Coordonnées</h2>
                <p class="panel-card__subtitle">Stade municipal — Rue du Stade, 79350 Chiché</p>
              </div>
              <div class="contact-card__info">
                <div class="contact-list">
                  <span>Standard : 05&nbsp;49&nbsp;00&nbsp;00&nbsp;00 (mercredi 18h-20h)</span>
                  <span>Administratif : <a href="mailto:contact@fcchiche.fr">contact@fcchiche.fr</a></span>
                  <span>Partenariats : <a href="mailto:partenaires@fcchiche.fr">partenaires@fcchiche.fr</a></span>
                </div>
                <div class="pill-list">
                  <span>Instagram</span>
                  <span>Facebook</span>
                  <span>YouTube</span>
                </div>
                <p class="section__subtitle">
                  Permanence au club-house les soirs de match à domicile et le samedi matin (licences jeunes).
                </p>
              </div>
            </aside>
          </div>
        </div>
      </section>

      <section class="section section--tint">
        <div class="container">
          <div class="highlight-grid">
            <article class="highlight-card">
              <h3 class="highlight-card__title">Venir au stade</h3>
              <p>Parkings gratuits, accès PMR dédié et navette centre-bourg les jours de grosses affluences.</p>
              <div class="pill-list">
                <span>Parking Nord</span>
                <span>Navette supporters</span>
                <span>Accueil PMR</span>
              </div>
            </article>
            <article class="highlight-card">
              <h3 class="highlight-card__title">Rejoindre l'équipe bénévole</h3>
              <p>
                Buvette, accueil, logistique ou communication : toutes les compétences sont les bienvenues pour faire grandir le
                club.
              </p>
              <a class="feature-card__link" href="mailto:benevoles@fcchiche.fr">Écrire à la cellule bénévoles</a>
            </article>
          </div>
        </div>
      </section>

<?php
require_once __DIR__ . '/templates/footer.php';
