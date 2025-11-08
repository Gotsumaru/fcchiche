import { useState, useEffect } from 'react'
import './styles.css'

// Data structure - EXACT copy from original
const dataByTeam = {
  all: {
    results: [
      { date: '18 Mai 2025 • 15h00', home: 'FC Chiché', away: 'Inter Bocage FC', score: '3 - 1', status: 'victory', competition: 'Seniors D3 • Phase 1' },
      { date: '27 Avril 2025 • 15h00', home: 'F.C.V.G.', away: 'FC Chiché', score: '2 - 2', status: 'draw', competition: 'Seniors D5 • Phase 1' },
      { date: '17 Mai 2025 • 14h30', home: 'FC Chiché', away: 'GJ Esp. N-E 79', score: '1 - 4', status: 'defeat', competition: 'U15 D1 • Phase 2' },
      { date: '24 Mai 2025 • 15h00', home: 'FC Chiché', away: 'GJ Evsabb', score: '5 - 2', status: 'victory', competition: 'U13 D4 • Phase 2' }
    ],
    calendar: [
      { date: '25 Mai 2025 • 15h00', home: 'FC Chiché', away: 'Louzy ES', competition: 'Seniors D5 • Phase 1' },
      { date: '01 Juin 2025 • 14h30', home: 'Pays Argentonnais', away: 'FC Chiché', competition: 'Seniors D3 • Phase 1' },
      { date: '08 Juin 2025 • 15h00', home: 'FC Chiché', away: 'Moncoutant SA', competition: 'U13 D4 • Phase 2' }
    ]
  },
  seniors1: {
    results: [
      { date: '18 Mai 2025 • 15h00', home: 'FC Chiché', away: 'Inter Bocage FC', score: '3 - 1', status: 'victory', competition: 'Seniors D3 • Phase 1' },
      { date: '11 Mai 2025 • 15h00', home: 'Pays Argentonnais', away: 'FC Chiché', score: '0 - 1', status: 'victory', competition: 'Seniors D3 • Phase 1' }
    ],
    calendar: [
      { date: '01 Juin 2025 • 14h30', home: 'Pays Argentonnais', away: 'FC Chiché', competition: 'Seniors D3 • Phase 1' },
      { date: '08 Juin 2025 • 15h00', home: 'FC Chiché', away: 'Clazay Bocage FC', competition: 'Seniors D3 • Phase 1' }
    ],
    ranking: [
      { pos: 1, team: 'Aubinrorthais ES', pts: 40, j: 22, diff: '+18' },
      { pos: 2, team: 'L Absie Larg. Mout.', pts: 37, j: 22, diff: '+3' },
      { pos: 3, team: 'Fayenoirterre ES', pts: 34, j: 22, diff: '+2' },
      { pos: 4, team: 'FC Chiché', pts: 34, j: 22, diff: '+9', highlight: true },
      { pos: 5, team: 'Beaulieu Breuil ES', pts: 33, j: 22, diff: '+3' }
    ]
  },
  seniors2: {
    results: [
      { date: '27 Avril 2025 • 15h00', home: 'F.C.V.G.', away: 'FC Chiché', score: '2 - 2', status: 'draw', competition: 'Seniors D5 • Phase 1' }
    ],
    calendar: [
      { date: '25 Mai 2025 • 15h00', home: 'FC Chiché', away: 'Louzy ES', competition: 'Seniors D5 • Phase 1' }
    ],
    ranking: [
      { pos: 1, team: 'Airvo St Jouin FC', pts: 26, j: 10, diff: '+18' },
      { pos: 2, team: 'F.C.V.G.', pts: 20, j: 10, diff: '+13' },
      { pos: 3, team: 'FC Chiché', pts: 18, j: 10, diff: '+11', highlight: true },
      { pos: 4, team: 'Louzy ES', pts: 16, j: 10, diff: '+7' }
    ]
  },
  u15: {
    results: [
      { date: '17 Mai 2025 • 14h30', home: 'FC Chiché', away: 'GJ Esp. N-E 79', score: '1 - 4', status: 'defeat', competition: 'U15 D1 • Phase 2' }
    ],
    calendar: [
      { date: '31 Mai 2025 • 14h30', home: 'Nueillaubiers FC', away: 'FC Chiché', competition: 'U15 D1 • Phase 2' }
    ],
    ranking: [
      { pos: 1, team: 'Nueillaubiers FC', pts: 27, j: 9, diff: '+23' },
      { pos: 10, team: 'FC Chiché', pts: 6, j: 9, diff: '+15', highlight: true }
    ]
  },
  u13: {
    results: [
      { date: '24 Mai 2025 • 15h00', home: 'FC Chiché', away: 'GJ Evsabb', score: '5 - 2', status: 'victory', competition: 'U13 D4 • Phase 2' }
    ],
    calendar: [
      { date: '08 Juin 2025 • 15h00', home: 'FC Chiché', away: 'Moncoutant SA', competition: 'U13 D4 • Phase 2' }
    ],
    ranking: [
      { pos: 1, team: 'GJ Evsabb', pts: 27, j: 10, diff: '+58' },
      { pos: 14, team: 'FC Chiché', pts: -2, j: 6, diff: '+26', highlight: true }
    ]
  }
}

const getStatusLabel = (status) => {
  const labels = { victory: 'Victoire', draw: 'Match nul', defeat: 'Défaite' }
  return labels[status] || status
}

function Header({ activeSection, onNavClick, onToggleMobileNav }) {
  const [scrolled, setScrolled] = useState(false)

  useEffect(() => {
    const handleScroll = () => {
      setScrolled(window.scrollY > 24)
    }
    window.addEventListener('scroll', handleScroll, { passive: true })
    return () => window.removeEventListener('scroll', handleScroll)
  }, [])

  return (
    <header className={`topbar ${scrolled ? 'scrolled' : ''}`} id="topbar">
      <div className="topbar-content">
        <a className="brand" href="#home" onClick={(e) => { e.preventDefault(); onNavClick('home') }}>
          <div className="brand-mark">FC</div>
          <div className="brand-text">
            <span>FC Chiché</span>
            <small style={{ fontSize: '12px', color: 'var(--text-muted)', letterSpacing: '0.08em' }}>Depuis 1946</small>
          </div>
        </a>
        <nav className="nav-desktop">
          <a className={`nav-link ${activeSection === 'home' ? 'active' : ''}`} href="#home" onClick={(e) => { e.preventDefault(); onNavClick('home') }}>Accueil</a>
          <a className={`nav-link ${activeSection === 'results' ? 'active' : ''}`} href="#results" onClick={(e) => { e.preventDefault(); onNavClick('results') }}>Résultats</a>
          <a className={`nav-link ${activeSection === 'calendar' ? 'active' : ''}`} href="#calendar" onClick={(e) => { e.preventDefault(); onNavClick('calendar') }}>Calendrier</a>
          <a className={`nav-link ${activeSection === 'ranking' ? 'active' : ''}`} href="#ranking" onClick={(e) => { e.preventDefault(); onNavClick('ranking') }}>Classement</a>
          <a className={`nav-link ${activeSection === 'club' ? 'active' : ''}`} href="#club" onClick={(e) => { e.preventDefault(); onNavClick('club') }}>Le club</a>
        </nav>
        <button className="nav-cta" onClick={() => onNavClick('calendar')}>Billetterie</button>
        <button className="nav-toggle" id="navToggle" aria-label="Menu principal" onClick={onToggleMobileNav}>☰</button>
      </div>
    </header>
  )
}

function HeroSection({ onNavClick }) {
  return (
    <section className="hero" id="home" data-section>
      <div className="hero-inner">
        <div className="hero-layout">
          <div className="hero-content">
            <div className="hero-badge">Club amateur engagé • Deux-Sèvres</div>
            <h1 className="hero-title">Une histoire qui se joue sur le terrain et dans le village.</h1>
            <p className="hero-subtitle">
              FC Chiché fédère plus de 150 licenciés autour d'une vision engagée du football : former, fédérer et partager. Découvrez nos équipes, suivez les résultats en direct et vivez nos matchs à domicile.
            </p>
            <div className="hero-actions">
              <a className="btn-primary" href="#results" onClick={(e) => { e.preventDefault(); onNavClick('results') }}>Consulter les derniers résultats</a>
              <a className="btn-secondary" href="#club" onClick={(e) => { e.preventDefault(); onNavClick('club') }}>Découvrir le club</a>
            </div>
            <div className="hero-grid">
              <div className="hero-card">
                <h3>Licenciés</h3>
                <strong>150+</strong>
                <span className="section-subtitle" style={{ fontSize: '14px' }}>Éducateurs, joueurs, bénévoles et supporters engagés chaque semaine.</span>
              </div>
              <div className="hero-card">
                <h3>Équipes</h3>
                <strong>4</strong>
                <span className="section-subtitle" style={{ fontSize: '14px' }}>Des seniors aux jeunes, toutes les catégories défendent le maillot vert.</span>
              </div>
              <div className="hero-card">
                <h3>Matchs / saison</h3>
                <strong>60+</strong>
                <span className="section-subtitle" style={{ fontSize: '14px' }}>Un calendrier riche en émotions sur les terrains du département.</span>
              </div>
            </div>
          </div>
          <aside className="hero-media">
            <figure>
              <img src="/assets/images/home.png" alt="Stade du FC Chiché baigné de lumière" loading="lazy" width="4096" height="4096" />
              <figcaption>Le vert de Chiché</figcaption>
            </figure>
            <div className="hero-callout">
              <h3>Prochain match à domicile</h3>
              <p>FC Chiché vs Louzy ES</p>
              <span>Dimanche 25 mai • 15h00<br />Stade du Pas des Biches</span>
              <a href="#calendar" onClick={(e) => { e.preventDefault(); onNavClick('calendar') }}>Voir tout le calendrier</a>
            </div>
          </aside>
        </div>
      </div>
    </section>
  )
}

function ResultsSection({ filters, onFilterChange }) {
  const [results, setResults] = useState([])

  useEffect(() => {
    const teamKey = filters.results || 'all'
    const data = dataByTeam[teamKey]
    setResults(data?.results || [])
  }, [filters.results])

  return (
    <section id="results" data-section>
      <div className="section-container">
        <div className="section-header">
          <div className="section-badge">Actualisé chaque semaine</div>
          <h2 className="section-title">Derniers résultats</h2>
          <p className="section-subtitle">Analysez les performances de chaque équipe avec des fiches de match détaillées, un design clair et des codes couleurs instantanés.</p>
          <div className="filters" id="resultsFilters">
            <button className={`filter ${filters.results === 'all' ? 'active' : ''}`} onClick={() => onFilterChange('results', 'all')}>Toutes les équipes</button>
            <button className={`filter ${filters.results === 'seniors1' ? 'active' : ''}`} onClick={() => onFilterChange('results', 'seniors1')}>Seniors 1 • D3</button>
            <button className={`filter ${filters.results === 'seniors2' ? 'active' : ''}`} onClick={() => onFilterChange('results', 'seniors2')}>Seniors 2 • D5</button>
            <button className={`filter ${filters.results === 'u15' ? 'active' : ''}`} onClick={() => onFilterChange('results', 'u15')}>U15 • D1</button>
            <button className={`filter ${filters.results === 'u13' ? 'active' : ''}`} onClick={() => onFilterChange('results', 'u13')}>U13 • D4</button>
          </div>
        </div>
        <div className="cards-grid">
          {results.length > 0 ? results.map((match, idx) => (
            <article className="result-card" key={idx}>
              <div className="match-header">
                <span className="match-date">{match.date}</span>
                <span className={`match-status ${match.status}`}>{getStatusLabel(match.status)}</span>
              </div>
              <div className="match-body">
                <div className="match-teams">
                  <div className="team">
                    <span className="team-name">{match.home}</span>
                    <div className="team-logo">{match.home.includes('Chiché') ? '🏠' : '🚗'}</div>
                  </div>
                  <div className="match-score">{match.score}</div>
                  <div className="team">
                    <span className="team-name">{match.away}</span>
                    <div className="team-logo">{match.away.includes('Chiché') ? '🏠' : '🚗'}</div>
                  </div>
                </div>
                <div className="match-competition">{match.competition}</div>
              </div>
            </article>
          )) : (
            <div className="empty-state">Aucun résultat disponible actuellement.</div>
          )}
        </div>
      </div>
    </section>
  )
}

function CalendarSection({ filters, onFilterChange }) {
  const [calendar, setCalendar] = useState([])

  useEffect(() => {
    const teamKey = filters.calendar || 'all'
    const data = dataByTeam[teamKey]
    setCalendar(data?.calendar || [])
  }, [filters.calendar])

  return (
    <section id="calendar" data-section>
      <div className="section-container">
        <div className="section-header">
          <div className="section-badge">Anticipez vos weekends</div>
          <h2 className="section-title">Calendrier des rencontres</h2>
          <p className="section-subtitle">Les matchs à venir sont regroupés par équipe. Ajoutez-les à votre agenda et rejoignez-nous au bord du terrain.</p>
          <div className="filters" id="calendarFilters">
            <button className={`filter ${filters.calendar === 'all' ? 'active' : ''}`} onClick={() => onFilterChange('calendar', 'all')}>Toutes les équipes</button>
            <button className={`filter ${filters.calendar === 'seniors1' ? 'active' : ''}`} onClick={() => onFilterChange('calendar', 'seniors1')}>Seniors 1 • D3</button>
            <button className={`filter ${filters.calendar === 'seniors2' ? 'active' : ''}`} onClick={() => onFilterChange('calendar', 'seniors2')}>Seniors 2 • D5</button>
            <button className={`filter ${filters.calendar === 'u15' ? 'active' : ''}`} onClick={() => onFilterChange('calendar', 'u15')}>U15 • D1</button>
            <button className={`filter ${filters.calendar === 'u13' ? 'active' : ''}`} onClick={() => onFilterChange('calendar', 'u13')}>U13 • D4</button>
          </div>
        </div>
        <div className="cards-grid">
          {calendar.length > 0 ? calendar.map((match, idx) => (
            <article className="calendar-card" key={idx}>
              <div className="calendar-date">{match.date}</div>
              <div className="calendar-teams">
                <div>{match.home}</div>
                <div className="calendar-vs">VS</div>
                <div>{match.away}</div>
              </div>
              <div className="calendar-competition">{match.competition}</div>
            </article>
          )) : (
            <div className="empty-state">Aucun match à venir pour le moment.</div>
          )}
        </div>
      </div>
    </section>
  )
}

function RankingSection({ filters, onFilterChange }) {
  const [ranking, setRanking] = useState([])

  useEffect(() => {
    const teamKey = filters.ranking || 'seniors1'
    const data = dataByTeam[teamKey]
    setRanking(data?.ranking || [])
  }, [filters.ranking])

  return (
    <section id="ranking" data-section>
      <div className="section-container">
        <div className="section-header">
          <div className="section-badge">Saison 2024-2025</div>
          <h2 className="section-title">Classements officiels</h2>
          <p className="section-subtitle">Retrouvez le positionnement des équipes dans leurs championnats respectifs et suivez la dynamique de la saison en cours.</p>
          <div className="filters" id="rankingFilters">
            <button className={`filter ${filters.ranking === 'seniors1' ? 'active' : ''}`} onClick={() => onFilterChange('ranking', 'seniors1')}>Seniors 1 • D3</button>
            <button className={`filter ${filters.ranking === 'seniors2' ? 'active' : ''}`} onClick={() => onFilterChange('ranking', 'seniors2')}>Seniors 2 • D5</button>
            <button className={`filter ${filters.ranking === 'u15' ? 'active' : ''}`} onClick={() => onFilterChange('ranking', 'u15')}>U15 • D1</button>
            <button className={`filter ${filters.ranking === 'u13' ? 'active' : ''}`} onClick={() => onFilterChange('ranking', 'u13')}>U13 • D4</button>
          </div>
        </div>
        <div className="ranking-wrapper">
          <div className="ranking-table">
            <div className="ranking-row header">
              <div>Pos</div>
              <div>Équipe</div>
              <div>Pts</div>
              <div>Matchs</div>
              <div>Diff</div>
            </div>
            {ranking.length > 0 ? ranking.map((row, idx) => (
              <div className={`ranking-row ${row.highlight ? 'highlight' : ''}`} key={idx}>
                <div className="ranking-pos">{row.pos}</div>
                <div className="ranking-team">{row.team}</div>
                <div className="ranking-stat">{row.pts}</div>
                <div className="ranking-stat">{row.j ?? '-'}</div>
                <div className="ranking-stat">{row.diff ?? '-'}</div>
              </div>
            )) : (
              <div className="empty-state">Classement non disponible pour cette équipe.</div>
            )}
          </div>
        </div>
      </div>
    </section>
  )
}

function ClubSection({ onNavClick }) {
  return (
    <section id="club" data-section>
      <div className="section-container">
        <div className="section-header">
          <div className="section-badge">Une identité forte</div>
          <h2 className="section-title">Le club et son territoire</h2>
          <p className="section-subtitle">Implanté au cœur de Chiché, le club s'appuie sur un réseau de bénévoles, d'éducateurs diplômés et de partenaires locaux qui partagent la même ambition : faire rayonner le football amateur.</p>
        </div>
        <div className="about">
          <article className="about-card about-card--territory">
            <div className="about-copy">
              <strong>Centre sportif du Pas des Biches</strong>
              <p>Terrain d'honneur, tribunes couvertes, espace club-house et zone de préparation physique. Lieu de vie du club et point de ralliement de tous les passionnés.</p>
            </div>
            <div className="about-gallery">
              <figure className="about-frame about-frame--primary">
                <img src="/assets/images/Agenda.png" alt="Terrain du FC Chiché en nocturne" loading="lazy" width="4096" height="4096" />
                <figcaption className="about-frame__caption"><span className="about-frame__dot" aria-hidden="true"></span>Ambiance nocturne</figcaption>
              </figure>
              <figure className="about-frame about-frame--secondary">
                <img src="/assets/images/resultat.png" alt="Joueurs du FC Chiché à l'échauffement" loading="lazy" width="4096" height="4096" />
                <figcaption className="about-frame__caption"><span className="about-frame__dot" aria-hidden="true"></span>Échauffement collectif</figcaption>
              </figure>
            </div>
          </article>
          <article className="about-card about-card--panel">
            <strong>Un engagement sociétal</strong>
            <ul className="about-panel__list">
              <li>• École de foot labellisée FFF</li>
              <li>• Programme féminisation et mixité</li>
              <li>• Accueil d'événements associatifs locaux</li>
              <li>• Sensibilisation à l'arbitrage et à l'éco-responsabilité</li>
            </ul>
            <a className="btn-secondary" href="#calendar" onClick={(e) => { e.preventDefault(); onNavClick('calendar') }}>Planifier ma venue</a>
          </article>
        </div>
      </div>
    </section>
  )
}

function MobileNav({ activeSection, onNavClick, show, onClose }) {
  if (!show) return null

  return (
    <nav className="mobile-nav" id="mobileNav" aria-label="Navigation principale mobile">
      <div className="mobile-nav-grid">
        <a className={`mobile-nav-item ${activeSection === 'home' ? 'active' : ''}`} href="#home" onClick={(e) => { e.preventDefault(); onNavClick('home'); onClose() }}>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.6">
            <path d="M3 9.5L12 3L21 9.5V21H3V9.5Z"></path>
            <path d="M9 21V12H15V21"></path>
          </svg>
          Accueil
        </a>
        <a className={`mobile-nav-item ${activeSection === 'results' ? 'active' : ''}`} href="#results" onClick={(e) => { e.preventDefault(); onNavClick('results'); onClose() }}>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.6">
            <path d="M4 12L9 17L20 6"></path>
          </svg>
          Résultats
        </a>
        <a className={`mobile-nav-item ${activeSection === 'calendar' ? 'active' : ''}`} href="#calendar" onClick={(e) => { e.preventDefault(); onNavClick('calendar'); onClose() }}>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.6">
            <rect x="3" y="5" width="18" height="16" rx="2"></rect>
            <path d="M16 3V7"></path>
            <path d="M8 3V7"></path>
            <path d="M3 11H21"></path>
          </svg>
          Calendrier
        </a>
        <a className={`mobile-nav-item ${activeSection === 'ranking' ? 'active' : ''}`} href="#ranking" onClick={(e) => { e.preventDefault(); onNavClick('ranking'); onClose() }}>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.6">
            <path d="M7 9H4V21H7V9Z"></path>
            <path d="M13 3H10V21H13V3Z"></path>
            <path d="M19 15H16V21H19V15Z"></path>
          </svg>
          Classement
        </a>
      </div>
    </nav>
  )
}

function ScrollTopButton({ show }) {
  return (
    <button className={`scroll-top ${show ? 'visible' : ''}`} id="scrollTop" aria-label="Revenir en haut" onClick={() => window.scrollTo({ top: 0, behavior: 'smooth' })}>↑</button>
  )
}

function Footer({ onNavClick }) {
  return (
    <footer>
      <div className="footer-content">
        <div className="footer-top">
          <div className="footer-brand">
            <strong style={{ fontSize: '18px', color: 'var(--text)' }}>FC Chiché</strong>
            <span>Club de football amateur affilié FFF • Stade du Pas des Biches • 79350 Chiché</span>
          </div>
          <div className="footer-links">
            <a href="#results" onClick={(e) => { e.preventDefault(); onNavClick('results') }}>Résultats</a>
            <a href="#calendar" onClick={(e) => { e.preventDefault(); onNavClick('calendar') }}>Calendrier</a>
            <a href="#ranking" onClick={(e) => { e.preventDefault(); onNavClick('ranking') }}>Classement</a>
            <a href="#club" onClick={(e) => { e.preventDefault(); onNavClick('club') }}>Nous soutenir</a>
          </div>
          <div className="footer-links">
            <a href="mailto:contact@fcchiche.fr">contact@fcchiche.fr</a>
            <a href="tel:+33549715248">05 49 71 52 48</a>
            <span>Suivez-nous sur @fcchiche_officiel</span>
          </div>
        </div>
        <div className="footer-bottom">
          <span>© FC Chiché 2025 • Tous droits réservés</span>
          <span>Site optimisé PWA • Responsive design</span>
        </div>
      </div>
    </footer>
  )
}

export default function App() {
  const [showLoader, setShowLoader] = useState(true)
  const [activeSection, setActiveSection] = useState('home')
  const [pageTransition, setPageTransition] = useState(false)
  const [showMobileNav, setShowMobileNav] = useState(false)
  const [scrollVisible, setScrollVisible] = useState(false)
  const [filters, setFilters] = useState({
    results: 'all',
    calendar: 'all',
    ranking: 'seniors1'
  })

  useEffect(() => {
    setTimeout(() => setShowLoader(false), 900)
  }, [])

  useEffect(() => {
    const handleScroll = () => {
      setScrollVisible(window.scrollY > 400)
    }
    window.addEventListener('scroll', handleScroll, { passive: true })
    return () => window.removeEventListener('scroll', handleScroll)
  }, [])

  const handleNavClick = (sectionId) => {
    setPageTransition(true)
    setTimeout(() => {
      const element = document.getElementById(sectionId)
      if (element) {
        element.scrollIntoView({ behavior: 'smooth', block: 'start' })
      }
      setActiveSection(sectionId)
      setShowMobileNav(false)
      setTimeout(() => setPageTransition(false), 260)
    }, 220)
  }

  const handleFilterChange = (scope, team) => {
    setFilters(prev => ({
      ...prev,
      [scope]: team
    }))
  }

  return (
    <>
      <div className={`initial-loader ${!showLoader ? 'hidden' : ''}`}>
        <div className="loader-logo">FC</div>
      </div>
      <div className={`page-transition ${pageTransition ? 'active' : ''}`}></div>
      <Header activeSection={activeSection} onNavClick={handleNavClick} onToggleMobileNav={() => setShowMobileNav(!showMobileNav)} />
      <main>
        <HeroSection onNavClick={handleNavClick} />
        <ResultsSection filters={filters} onFilterChange={handleFilterChange} />
        <CalendarSection filters={filters} onFilterChange={handleFilterChange} />
        <RankingSection filters={filters} onFilterChange={handleFilterChange} />
        <ClubSection onNavClick={handleNavClick} />
      </main>
      <MobileNav activeSection={activeSection} onNavClick={handleNavClick} show={showMobileNav} onClose={() => setShowMobileNav(false)} />
      <ScrollTopButton show={scrollVisible} />
      <Footer onNavClick={handleNavClick} />
    </>
  )
}
