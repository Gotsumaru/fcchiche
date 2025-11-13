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

  // Résoudre le logo (utiliser logo.svg pour FC Chiché)
  const resolveLogo = (teamName) => {
    const isFCChiche = teamName?.toUpperCase().includes('FC CHICHE') ||
                       teamName?.toUpperCase().includes('CHICHE') ||
                       teamName?.toUpperCase() === 'FC CHICHE'

    if (isFCChiche) {
      return '/assets/images/logo.svg'
    }

    // Pour les autres équipes, retourner null (pas de logo pour l'instant)
    return null
  }

  // Déterminer quelle équipe doit avoir son logo affiché
  const homeIsChiche = match.home_name?.toUpperCase().includes('CHICHE') ||
                       match.home_name?.toUpperCase().includes('FC CHICHE')
  const awayIsChiche = match.away_name?.toUpperCase().includes('CHICHE') ||
                       match.away_name?.toUpperCase().includes('FC CHICHE')

  // Formater la catégorie
  const formatCategory = (category) => {
    if (!category) return ''

    // Normaliser la chaîne
    const normalized = category.toUpperCase().trim()

    // Vérifier les patterns exacts avec boundaries pour éviter les faux positifs
    // Pattern: "SENIOR 1" ou "SENIORS 1"
    if (/SENIORS?\s+1\b/.test(normalized)) {
      return 'Première'
    }

    // Pattern: "SENIOR 2" ou "SENIORS 2"
    if (/SENIORS?\s+2\b/.test(normalized)) {
      return 'Réserve A'
    }

    // Pattern: "SENIOR 3" ou "SENIORS 3"
    if (/SENIORS?\s+3\b/.test(normalized)) {
      return 'Réserve B'
    }

    return category
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
      aria-label={`Match ${match.home_name || match.home} vs ${match.away_name || match.away} le ${formatDate(match.date)}`}
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
        {homeIsChiche && (
          <img
            src={resolveLogo(match.home_name || match.home)}
            alt={match.home_name || match.home}
            loading="lazy"
            style={{ width: 'clamp(52px, 14vw, 76px)', height: 'clamp(52px, 14vw, 76px)', objectFit: 'contain' }}
          />
        )}
        {match.home_logo && !homeIsChiche && (
          <img
            src={match.home_logo}
            alt={match.home_name || match.home}
            loading="lazy"
            style={{ width: 'clamp(52px, 14vw, 76px)', height: 'clamp(52px, 14vw, 76px)', objectFit: 'contain' }}
            onError={(e) => {
              e.target.style.display = 'none'
            }}
          />
        )}
        <span className="match-card__team-name">{match.home_name || match.home}</span>
      </div>

      {/* Away team badge */}
      <div className="match-card__team match-card__team--away">
        {awayIsChiche && (
          <img
            src={resolveLogo(match.away_name || match.away)}
            alt={match.away_name || match.away}
            loading="lazy"
            style={{ width: 'clamp(52px, 14vw, 76px)', height: 'clamp(52px, 14vw, 76px)', objectFit: 'contain' }}
          />
        )}
        {match.away_logo && !awayIsChiche && (
          <img
            src={match.away_logo}
            alt={match.away_name || match.away}
            loading="lazy"
            style={{ width: 'clamp(52px, 14vw, 76px)', height: 'clamp(52px, 14vw, 76px)', objectFit: 'contain' }}
            onError={(e) => {
              e.target.style.display = 'none'
            }}
          />
        )}
        <span className="match-card__team-name">{match.away_name || match.away}</span>
      </div>

      {/* Category label */}
      <span className="match-card__category">
        {formatCategory(match.category_label || match.competition)}
      </span>

      {/* Center block with date and score */}
      <div className="match-card__center">
        <div className="match-card__date">{formatDate(match.date)}</div>
        <div className="match-card__vs">VS</div>
      </div>
    </a>
  )
}
