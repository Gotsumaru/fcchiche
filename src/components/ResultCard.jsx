/**
 * Result Card Component
 * Displays a match result with logos and final score
 */

export default function ResultCard({ result }) {
  if (!result) return null

  // Résoudre le logo (utiliser logo.svg pour FC Chiché)
  const resolveLogo = (logoUrl, teamName) => {
    const isFCChiche = teamName?.toUpperCase().includes('FC CHICHE') ||
                       teamName?.toUpperCase().includes('CHICHE') ||
                       teamName?.toUpperCase() === 'FC CHICHE'

    if (isFCChiche) {
      return '/assets/images/logo.svg'
    }

    return logoUrl
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
    <article
      className="media-card media-card--result media-card--compact"
      data-result-id={result.id}
    >
      <div className="media-card__body result-card">
        {/* Header with competition and date */}
        <div className="result-card__header">
          <span className="section__eyebrow">{result.competition}</span>
          <span>{formatDate(result.date)}</span>
        </div>

        {/* Match display with logos and score */}
        <div className="result-card__match-display">
          {/* Home team */}
          <div className="result-card__team-block">
            {result.home_logo && (
              <img
                className="result-card__team-logo"
                src={resolveLogo(result.home_logo, result.home_name || result.home)}
                alt={`Logo ${result.home_name || result.home}`}
                loading="lazy"
                onError={(e) => {
                  e.target.style.display = 'none'
                }}
              />
            )}
            <div className="result-card__team-info">
              <div className="result-card__team-name">{result.home_name || result.home}</div>
              <div className="result-card__team-score">
                {result.home_score !== undefined ? result.home_score : '-'}
              </div>
            </div>
          </div>

          {/* Score separator */}
          <div className="result-card__separator">
            <span className="result-card__vs">VS</span>
          </div>

          {/* Away team */}
          <div className="result-card__team-block">
            <div className="result-card__team-info">
              <div className="result-card__team-name">{result.away_name || result.away}</div>
              <div className="result-card__team-score">
                {result.away_score !== undefined ? result.away_score : '-'}
              </div>
            </div>
            {result.away_logo && (
              <img
                className="result-card__team-logo"
                src={resolveLogo(result.away_logo, result.away_name || result.away)}
                alt={`Logo ${result.away_name || result.away}`}
                loading="lazy"
                onError={(e) => {
                  e.target.style.display = 'none'
                }}
              />
            )}
          </div>
        </div>
      </div>
    </article>
  )
}
