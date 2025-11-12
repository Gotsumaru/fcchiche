/**
 * Match Card Component
 * Displays a match with background image, team logos, and match details
 */

export default function MatchCard({ match }) {
  if (!match) return null

  // Résoudre l'image de fond basée sur la compétition/catégorie
  const resolveMatchImage = (match) => {
    const competition = match.competition?.toLowerCase() || ''
    const category = match.category_label?.toLowerCase() || ''

    // Règles pour sélectionner l'image
    const rules = [
      { pattern: /(champ|phase|d[0-9])/i, asset: 'rencontre.png' },
      { pattern: /(coupe|challenge|cp)/i, asset: 'rencontre.png' },
      { pattern: /(plateau|tournoi|festival)/i, asset: 'rencontre.png' },
      { pattern: /(u1|u13|u15|u17|jeune|formation)/i, asset: 'rencontre.png' },
      { pattern: /(femi|fémi|dames)/i, asset: 'rencontre.png' },
      { pattern: /(loisir|vétéran|veteran|amical|prépa|prepa)/i, asset: 'rencontre.png' }
    ]

    for (const rule of rules) {
      if (rule.pattern.test(competition) || rule.pattern.test(category)) {
        return '/assets/images/' + rule.asset
      }
    }

    return '/assets/images/rencontre.png'
  }

  // Formater la date
  const formatDate = (dateString) => {
    if (!dateString) return 'TBA'
    try {
      const date = new Date(dateString)
      return date.toLocaleDateString('fr-FR', {
        weekday: 'short',
        day: '2-digit',
        month: 'short'
      })
    } catch (e) {
      return dateString
    }
  }

  return (
    <a
      href="#"
      className="match-card"
      data-match-id={match.id}
      aria-label={`Match ${match.home} vs ${match.away} le ${formatDate(match.date)}`}
    >
      {/* Background image */}
      <div className="match-card__background">
        <img
          src={resolveMatchImage(match)}
          alt=""
          loading="lazy"
          decoding="async"
          aria-hidden="true"
        />
      </div>

      {/* Overlay */}
      <div className="match-card__overlay"></div>

      {/* Home team badge */}
      <div className="match-card__team match-card__team--home">
        <img
          src={`/assets/images/${match.home?.toLowerCase().replace(/\s+/g, '_')}.png`}
          alt={match.home}
          loading="lazy"
          onError={(e) => {
            e.target.style.display = 'none'
          }}
        />
        <span className="match-card__team-name">{match.home}</span>
      </div>

      {/* Away team badge */}
      <div className="match-card__team match-card__team--away">
        <img
          src={`/assets/images/${match.away?.toLowerCase().replace(/\s+/g, '_')}.png`}
          alt={match.away}
          loading="lazy"
          onError={(e) => {
            e.target.style.display = 'none'
          }}
        />
        <span className="match-card__team-name">{match.away}</span>
      </div>

      {/* Category label */}
      <span className="match-card__category">
        {match.category_label || match.competition}
      </span>

      {/* Center block with date and score */}
      <div className="match-card__center">
        <div className="match-card__date">{formatDate(match.date)}</div>
        <div className="match-card__vs">VS</div>
      </div>
    </a>
  )
}
