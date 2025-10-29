<?php
declare(strict_types=1);

$pageTitle = 'Contact | FC Chiché';

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/templates/header.php';
?>
      <section class="section">
        <div class="container">
          <div class="section__header">
            <span class="section__eyebrow">Nous contacter</span>
            <h1 class="section__title">Contactez-nous</h1>
            <p class="section__subtitle">Le secrétariat répond à toutes vos questions sur les inscriptions, partenariats et matchs.</p>
          </div>

          <div class="grid" style="gap: 2rem; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));">
            <div class="contact-card">
              <div class="contact-card__info">
                <strong>Adresse</strong>
                <p>Stade de Chiché, 79350 Chiché</p>
                <strong>Email</strong>
                <p><a href="mailto:contact@fcchiche.fr">contact@fcchiche.fr</a></p>
                <strong>Téléphone</strong>
                <p><a href="tel:0600000000">06 00 00 00 00</a></p>
                <strong>Horaires</strong>
                <p>Lundi – Vendredi : 18h–20h<br />Samedi – Dimanche : selon matchs</p>
              </div>
              <form class="form-shell" method="post" action="#" novalidate>
                <div class="form-row">
                  <label class="label" for="lastname">Nom</label>
                  <input class="input" id="lastname" name="lastname" type="text" required />
                  <label class="label" for="firstname">Prénom</label>
                  <input class="input" id="firstname" name="firstname" type="text" required />
                </div>
                <label class="label" for="email">Email</label>
                <input class="input" id="email" name="email" type="email" required />
                <label class="label" for="subject">Sujet</label>
                <input class="input" id="subject" name="subject" type="text" required />
                <label class="label" for="message">Message</label>
                <textarea class="textarea" id="message" name="message" required></textarea>
                <button class="btn btn--primary" type="submit">Envoyer</button>
              </form>
            </div>
            <div class="map-shell">
              <iframe
                title="Localisation du FC Chiché"
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2706.655193924687!2d-0.543!3d46.783!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x480799b0517d8623%3A0x5d6587b9e90b8e0!2sChich%C3%A9!5e0!3m2!1sfr!2sfr!4v1700000000000"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
                allowfullscreen
              ></iframe>
            </div>
          </div>
        </div>
      </section>

<?php
require_once __DIR__ . '/templates/footer.php';
