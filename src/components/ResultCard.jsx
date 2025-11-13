/**
 * Result Card Component
 * Displays a match result with color-coded ProfileCard (green=win, red=loss, gray=draw)
 */

import ProfileCard from './ProfileCard'
import './ResultCard.css'

export default function ResultCard({ result }) {
  if (!result) return null

  // Formater la catégorie
  const formatCategory = (category) => {
    if (!category) return ''

    // Normaliser la chaîne
    const normalized = category.toUpperCase().trim()

    // Pattern: "SENIOR 1" ou "SENIORS 1" (du PHP category_label)
    if (/SENIORS?\s+1\b/.test(normalized)) {
      return 'Première'
    }

    // Pattern: "SENIOR 2" ou "SENIORS 2" (du PHP category_label)
    if (/SENIORS?\s+2\b/.test(normalized)) {
      return 'Réserve A'
    }

    // Pattern: "SENIOR 3" ou "SENIORS 3" (du PHP category_label)
    if (/SENIORS?\s+3\b/.test(normalized)) {
      return 'Réserve B'
    }

    // Patterns pour les compétitions brutes (quand category_label n'est pas créé)
    // Départemental 1 ou D1 → Première
    if (/D[ÉE]PARTEMENTAL\s+1\b|(?:^|\s)D1\b/.test(normalized)) {
      return 'Première'
    }

    // Départemental 2-3 ou D2-D3 → Réserve A
    if (/D[ÉE]PARTEMENTAL\s+[23]\b|(?:^|\s)D[23]\b/.test(normalized)) {
      return 'Réserve A'
    }

    // Départemental 4-5 ou D4-D5 → Réserve B
    if (/D[ÉE]PARTEMENTAL\s+[45]\b|(?:^|\s)D[45]\b/.test(normalized)) {
      return 'Réserve B'
    }

    return category
  }

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

  // Logo FC Chiché ou de l'adversaire (défini avant usage)
  const homeLogo = homeIsChiche ? resolveLogo(result.home_name) : result.home_logo
  const awayLogo = awayIsChiche ? resolveLogo(result.away_name) : result.away_logo

  // Formater la date
  const formatDate = (dateString) => {
    if (!dateString) return 'TBA'
    try {
      const date = new Date(dateString)
      return date.toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: 'short'
      })
    } catch (e) {
      return dateString
    }
  }

  // Créer le contenu de l'avatar avec les logos des équipes
  const createMatchAvatar = () => {
    return (
      <div className="result-avatar">
        <img
          src="/assets/images/resultat_match.png"
          alt="Match result background"
          className="result-avatar__bg"
        />
        <div className="result-avatar__logos">
          {homeLogo && (
            <img
              src={homeLogo}
              alt={result.home_name}
              className="result-avatar__logo result-avatar__logo--home"
            />
          )}
          {awayLogo && (
            <img
              src={awayLogo}
              alt={result.away_name}
              className="result-avatar__logo result-avatar__logo--away"
            />
          )}
        </div>
      </div>
    )
  }

  // Couleurs selon résultat
  const resultColors = {
    win: {
      gradient: 'linear-gradient(145deg, #00b40044 0%, #00d40022 100%)',
      glow: 'rgba(0, 180, 0, 0.67)',
      glowSize: '60%'
    },
    loss: {
      gradient: 'linear-gradient(145deg, #dc262644 0%, #ef444422 100%)',
      glow: 'rgba(220, 38, 38, 0.67)',
      glowSize: '60%'
    },
    draw: {
      gradient: 'linear-gradient(145deg, #78787844 0%, #98989822 100%)',
      glow: 'rgba(120, 120, 120, 0.67)',
      glowSize: '60%'
    }
  }

  const colors = resultColors[matchResult]

  const score = `${result.home_score ?? '-'} - ${result.away_score ?? '-'}`
  const teamNames = `${result.home_name || 'Dom.'} vs ${result.away_name || 'Ext.'}`

  const resultText = matchResult === 'win' ? '✓ Victoire' : matchResult === 'loss' ? '✗ Défaite' : '= Match nul'

  // Calculer les points
  const points = matchResult === 'win' ? '+3' : matchResult === 'draw' ? '+1' : '+0'

  // Créer le composant des points
  const pointsBadge = (
    <div className="pc-points-badge">{points}</div>
  )

  return (
    <ProfileCard
      avatarContent={createMatchAvatar()}
      miniAvatarContent={pointsBadge}
      name={score}
      title={teamNames}
      handle={formatCategory(result.competition_name || result.competition)}
      status={formatDate(result.date_time || result.date)}
      contactText={resultText}
      showUserInfo={true}
      enableTilt={true}
      enableMobileTilt={false}
      mobileTiltSensitivity={3}
      innerGradient={colors.gradient}
      behindGlowColor={colors.glow}
      behindGlowSize={colors.glowSize}
      className="result-match-card"
    />
  )
}
