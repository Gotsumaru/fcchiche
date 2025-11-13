/**
 * Result Card Component
 * Displays a match result with color-coded background (green=win, red=loss, gray=draw)
 */

export default function ResultCard({ result }) {
  if (!result) return null

  // Résoudre le logo (utiliser logo.svg pour FC Chiché)
  const resolveLogo = (teamName) => {
    const isFCChiche = teamName?.toUpperCase().includes('FC CHICHE') ||
                       teamName?.toUpperCase().includes('CHICHE') ||
                       teamName?.toUpperCase() === 'FC CHICHE'

    if (isFCChiche) {
      return '/assets/images/logo.svg'
    }

    return null
  }

  // Déterminer quelle équipe est FC Chiché
  const homeIsChiche = result.home_name?.toUpperCase().includes('CHICHE') ||
                       result.home_name?.toUpperCase().includes('FC CHICHE')
  const awayIsChiche = result.away_name?.toUpperCase().includes('CHICHE') ||
                       result.away_name?.toUpperCase().includes('FC CHICHE')

  // Déterminer le résultat (victoire, défaite, égalité)
  const getMatchResult = () => {
    if (result.home_score === null || result.away_score === null) {
      return 'draw' // Pas de score = match nul par défaut
    }

    const homeScore = parseInt(result.home_score)
    const awayScore = parseInt(result.away_score)

    // Si FC Chiché joue à domicile
    if (homeIsChiche) {
      if (homeScore > awayScore) return 'win'
      if (homeScore < awayScore) return 'loss'
      return 'draw'
    }

    // Si FC Chiché joue à l'extérieur
    if (awayIsChiche) {
      if (awayScore > homeScore) return 'win'
      if (awayScore < homeScore) return 'loss'
      return 'draw'
    }

    return 'draw'
  }

  const matchResult = getMatchResult()

  // Formater la date
  const formatDate = (dateString) => {
    if (!dateString) return 'TBA'
    try {
      const date = new Date(dateString)
      return date.toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
      })
    } catch (e) {
      return dateString
    }
  }

  return (
    <article
      className={`result-card result-card--${matchResult}`}
      data-result-id={result.id}
    >
      {/* Header */}
      <div className="result-card__header">
        <span className="result-card__competition">{result.competition_name || result.competition}</span>
        <span className="result-card__date">{formatDate(result.date_time || result.date)}</span>
      </div>

      {/* Match display */}
      <div className="result-card__match">
        {/* Home team */}
        <div className="result-card__team">
          {(homeIsChiche || result.home_logo) && (
            <img
              className="result-card__logo"
              src={homeIsChiche ? resolveLogo(result.home_name) : result.home_logo}
              alt={result.home_name || result.home}
              loading="lazy"
              onError={(e) => {
                e.target.style.display = 'none'
              }}
            />
          )}
          <span className="result-card__team-name">{result.home_name || result.home}</span>
        </div>

        {/* Score */}
        <div className="result-card__score">
          <span className="result-card__score-value">
            {result.home_score !== null && result.home_score !== undefined ? result.home_score : '-'}
          </span>
          <span className="result-card__score-separator">-</span>
          <span className="result-card__score-value">
            {result.away_score !== null && result.away_score !== undefined ? result.away_score : '-'}
          </span>
        </div>

        {/* Away team */}
        <div className="result-card__team">
          <span className="result-card__team-name">{result.away_name || result.away}</span>
          {(awayIsChiche || result.away_logo) && (
            <img
              className="result-card__logo"
              src={awayIsChiche ? resolveLogo(result.away_name) : result.away_logo}
              alt={result.away_name || result.away}
              loading="lazy"
              onError={(e) => {
                e.target.style.display = 'none'
              }}
            />
          )}
        </div>
      </div>

      {/* Result badge */}
      <div className="result-card__badge">
        {matchResult === 'win' && '✓ Victoire'}
        {matchResult === 'loss' && '✗ Défaite'}
        {matchResult === 'draw' && '= Match nul'}
      </div>
    </article>
  )
}
