export default function Footer() {
  return (
    <footer className="app-footer">
      <div className="app-footer__inner">
        <div className="app-footer__branding">
          <img
            src="/assets/images/logo.svg"
            width="56"
            height="56"
            alt="Logo FC Chiché"
            loading="lazy"
          />
          <div className="app-footer__branding-text">
            <p className="app-footer__title">FC Chiché</p>
            <p className="app-footer__subtitle">Club de football amateur du bocage bressuirais</p>
          </div>
        </div>

        <div className="app-footer__cta">
          <h2>Planifiez votre prochaine venue</h2>
          <p>
            Retrouvez toutes les informations pratiques pour vivre les matches à domicile et en déplacement avec les verts et blancs.
          </p>
          <div className="app-footer__cta-actions">
            <a className="btn btn--primary" href="/matchs">Voir le calendrier</a>
            <a className="btn btn--secondary" href="/contact">Prendre contact</a>
          </div>
        </div>

        <div className="app-footer__grid">
          <div className="app-footer__column">
            <h2>Navigation</h2>
            <ul>
              <li><a href="/">Accueil</a></li>
              <li><a href="/resultats">Résultats</a></li>
              <li><a href="/matchs">Calendrier</a></li>
              <li><a href="/classements">Classements</a></li>
              <li><a href="/contact">Contact</a></li>
            </ul>
          </div>
          <div className="app-footer__column">
            <h2>Vie du club</h2>
            <ul>
              <li><a href="/matchs#telechargement-calendrier">Télécharger le calendrier</a></li>
              <li><a href="/contact">Demander une licence</a></li>
              <li><a href="/">Actualités du club</a></li>
            </ul>
          </div>
          <div className="app-footer__column">
            <h2>Nous suivre</h2>
            <ul className="app-footer__socials">
              <li><a href="#" aria-label="Instagram">Instagram</a></li>
              <li><a href="#" aria-label="Facebook">Facebook</a></li>
              <li><a href="#" aria-label="YouTube">YouTube</a></li>
            </ul>
            <a className="app-footer__contact" href="mailto:contact@fcchiche.fr">contact@fcchiche.fr</a>
            <p className="app-footer__schedule">Accueil : Lundi - Vendredi 18h-20h · Week-end selon matchs</p>
          </div>
        </div>

        <div className="app-footer__bottom">
          <p>© FC Chiché 2025 – Tous droits réservés</p>
          <div className="app-footer__legal">
            <a href="#">Mentions légales</a>
            <span aria-hidden="true">•</span>
            <a href="#">Politique de confidentialité</a>
          </div>
        </div>
      </div>
    </footer>
  )
}
