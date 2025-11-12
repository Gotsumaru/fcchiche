/**
 * Result Card Component
 * Displays a match result with logos and final score
 */

export default function ResultCard({ result }) {
  if (!result) return null

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
            <img
              className="result-card__team-logo"
              src={`/assets/images/${result.home?.toLowerCase().replace(/\s+/g, '_')}.png`}
              alt={`Logo ${result.home}`}
              loading="lazy"
              onError={(e) => {
                e.target.style.display = 'none'
              }}
            />
            <div className="result-card__team-info">
              <div className="result-card__team-name">{result.home}</div>
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
              <div className="result-card__team-name">{result.away}</div>
              <div className="result-card__team-score">
                {result.away_score !== undefined ? result.away_score : '-'}
              </div>
            </div>
            <img
              className="result-card__team-logo"
              src={`/assets/images/${result.away?.toLowerCase().replace(/\s+/g, '_')}.png`}
              alt={`Logo ${result.away}`}
              loading="lazy"
              onError={(e) => {
                e.target.style.display = 'none'
              }}
            />
          </div>
        </div>
      </div>
    </article>
  )
}
