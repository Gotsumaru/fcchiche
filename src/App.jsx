import { useState, useEffect } from 'react';
import { Header } from './components/Header';
import { useApi } from './hooks/useApi';
import apiClient from './services/api';

function App() {
  const [activeSection, setActiveSection] = useState('home');
  const [filters, setFilters] = useState({
    results: 'all',
    calendar: 'all',
    ranking: 'seniors1'
  });

  const { data: matchsData } = useApi(() => apiClient.getMatchs());
  const { data: classementsData } = useApi(() => apiClient.getClassements());
  const { data: equipesData } = useApi(() => apiClient.getEquipes());

  const handleNavigate = (sectionId) => {
    setActiveSection(sectionId);
    const section = document.getElementById(sectionId);
    if (section) {
      setTimeout(() => {
        section.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }, 100);
    }
  };

  // Mock data pour matchs et résultats (à remplacer par les vraies données API)
  const mockMatchs = [
    {
      id: 1,
      date: '18 Mai 2025 • 15h00',
      home: 'FC Chiché',
      away: 'Inter Bocage FC',
      score: '3 - 1',
      status: 'victory',
      competition: 'Seniors D3 • Phase 1'
    },
    {
      id: 2,
      date: '27 Avril 2025 • 15h00',
      home: 'F.C.V.G.',
      away: 'FC Chiché',
      score: '2 - 2',
      status: 'draw',
      competition: 'Seniors D5 • Phase 1'
    },
  ];

  const mockCalendar = [
    {
      id: 1,
      date: '25 Mai 2025 • 15h00',
      home: 'FC Chiché',
      away: 'Louzy ES',
      competition: 'Seniors D5 • Phase 1'
    },
    {
      id: 2,
      date: '01 Juin 2025 • 14h30',
      home: 'Pays Argentonnais',
      away: 'FC Chiché',
      competition: 'Seniors D3 • Phase 1'
    },
  ];

  const mockRanking = [
    { pos: 1, team: 'Aubinrorthais ES', pts: 40, j: 22, diff: '+18' },
    { pos: 2, team: 'L Absie Larg. Mout.', pts: 37, j: 22, diff: '+3' },
    { pos: 3, team: 'Fayenoirterre ES', pts: 34, j: 22, diff: '+2' },
    { pos: 4, team: 'FC Chiché', pts: 34, j: 22, diff: '+9', highlight: true },
    { pos: 5, team: 'Beaulieu Breuil ES', pts: 33, j: 22, diff: '+3' }
  ];

  return (
    <div className="app">
      <Header activeSection={activeSection} onNavigate={handleNavigate} />

      <main>
        {/* HOME SECTION */}
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
                  <a className="btn-primary" onClick={() => handleNavigate('results')} style={{ cursor: 'pointer' }}>
                    Consulter les derniers résultats
                  </a>
                  <a className="btn-secondary" onClick={() => handleNavigate('club')} style={{ cursor: 'pointer' }}>
                    Découvrir le club
                  </a>
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
                  <img
                    src="/assets/images/home.png"
                    alt="Stade du FC Chiché"
                    loading="lazy"
                    onError={(e) => { e.target.style.background = 'var(--accent-soft)'; e.target.style.height = '420px'; }}
                  />
                  <figcaption>Le vert de Chiché</figcaption>
                </figure>
                <div className="hero-callout">
                  <h3>Prochain match à domicile</h3>
                  <p>FC Chiché vs Louzy ES</p>
                  <span>Dimanche 25 mai • 15h00<br />Stade du Pas des Biches</span>
                  <a onClick={() => handleNavigate('calendar')} style={{ cursor: 'pointer' }}>Voir tout le calendrier →</a>
                </div>
              </aside>
            </div>
          </div>
        </section>

        {/* RESULTS SECTION */}
        <section id="results" data-section>
          <div className="section-container">
            <div className="section-header">
              <div className="section-badge">Actualisé chaque semaine</div>
              <h2 className="section-title">Derniers résultats</h2>
              <p className="section-subtitle">Analysez les performances de chaque équipe avec des fiches de match détaillées, un design clair et des codes couleurs instantanés.</p>
              <div className="filters">
                <button className="filter active">Toutes les équipes</button>
                <button className="filter">Seniors 1 • D3</button>
                <button className="filter">Seniors 2 • D5</button>
                <button className="filter">U15 • D1</button>
                <button className="filter">U13 • D4</button>
              </div>
            </div>
            <div className="cards-grid">
              {mockMatchs.length > 0 ? mockMatchs.map((match) => (
                <article key={match.id} className="result-card">
                  <div className="match-header">
                    <span className="match-date">{match.date}</span>
                    <span className={`match-status ${match.status}`}>
                      {match.status === 'victory' ? 'Victoire' : match.status === 'draw' ? 'Nul' : 'Défaite'}
                    </span>
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
                <div className="empty-state">Aucun résultat disponible</div>
              )}
            </div>
          </div>
        </section>

        {/* CALENDAR SECTION */}
        <section id="calendar" data-section>
          <div className="section-container">
            <div className="section-header">
              <div className="section-badge">Anticipez vos weekends</div>
              <h2 className="section-title">Calendrier des rencontres</h2>
              <p className="section-subtitle">Les matchs à venir sont regroupés par équipe. Ajoutez-les à votre agenda et rejoignez-nous au bord du terrain.</p>
              <div className="filters">
                <button className="filter active">Toutes les équipes</button>
                <button className="filter">Seniors 1 • D3</button>
                <button className="filter">Seniors 2 • D5</button>
                <button className="filter">U15 • D1</button>
                <button className="filter">U13 • D4</button>
              </div>
            </div>
            <div className="cards-grid">
              {mockCalendar.length > 0 ? mockCalendar.map((match) => (
                <article key={match.id} className="calendar-card">
                  <div className="calendar-date">{match.date}</div>
                  <div className="calendar-teams">
                    <div>{match.home}</div>
                    <div className="calendar-vs">VS</div>
                    <div>{match.away}</div>
                  </div>
                  <div className="calendar-competition">{match.competition}</div>
                </article>
              )) : (
                <div className="empty-state">Aucun match à venir</div>
              )}
            </div>
          </div>
        </section>

        {/* RANKING SECTION */}
        <section id="ranking" data-section>
          <div className="section-container">
            <div className="section-header">
              <div className="section-badge">Saison 2024-2025</div>
              <h2 className="section-title">Classements officiels</h2>
              <p className="section-subtitle">Retrouvez le positionnement des équipes dans leurs championnats respectifs et suivez la dynamique de la saison en cours.</p>
              <div className="filters">
                <button className="filter active">Seniors 1 • D3</button>
                <button className="filter">Seniors 2 • D5</button>
                <button className="filter">U15 • D1</button>
                <button className="filter">U13 • D4</button>
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
                {mockRanking.length > 0 ? mockRanking.map((row, idx) => (
                  <div key={idx} className={`ranking-row ${row.highlight ? 'highlight' : ''}`}>
                    <div className="ranking-pos">{row.pos}</div>
                    <div className="ranking-team">{row.team}</div>
                    <div className="ranking-stat">{row.pts}</div>
                    <div className="ranking-stat">{row.j}</div>
                    <div className="ranking-stat">{row.diff}</div>
                  </div>
                )) : (
                  <div style={{ padding: '40px', textAlign: 'center', color: 'var(--text-muted)' }}>
                    Classement non disponible
                  </div>
                )}
              </div>
            </div>
          </div>
        </section>

        {/* CLUB SECTION */}
        <section id="club" data-section>
          <div className="section-container">
            <div className="section-header">
              <div className="section-badge">Une identité forte</div>
              <h2 className="section-title">Le club et son territoire</h2>
              <p className="section-subtitle">Implanté au cœur de Chiché, le club s'appuie sur un réseau de bénévoles, d'éducateurs diplômés et de partenaires locaux qui partagent la même ambition : faire rayonner le football amateur.</p>
            </div>
            <div className="about">
              <article style={{ display: 'grid', gap: '32px', alignItems: 'start' }}>
                <div className="about-copy">
                  <strong style={{ fontSize: '28px', color: 'var(--accent-strong)' }}>Centre sportif du Pas des Biches</strong>
                  <p>Terrain d'honneur, tribunes couvertes, espace club-house et zone de préparation physique. Lieu de vie du club et point de ralliement de tous les passionnés.</p>
                </div>
              </article>
              <article style={{
                display: 'grid',
                gap: '18px',
                background: 'linear-gradient(135deg, rgba(255, 255, 255, 0.96), rgba(235, 243, 236, 0.92))',
                borderRadius: 'var(--radius-lg)',
                padding: '28px',
                border: '1px solid rgba(15, 27, 18, 0.06)'
              }}>
                <strong style={{ fontSize: '18px', color: 'var(--accent-strong)' }}>Un engagement sociétal</strong>
                <ul style={{ listStyle: 'none', display: 'grid', gap: '16px', color: 'var(--text-muted)', fontSize: '15px', lineHeight: '1.6' }}>
                  <li>• École de foot labellisée FFF</li>
                  <li>• Programme féminisation et mixité</li>
                  <li>• Accueil d'événements associatifs locaux</li>
                  <li>• Sensibilisation à l'arbitrage et à l'éco-responsabilité</li>
                </ul>
                <a className="btn-secondary" onClick={() => handleNavigate('calendar')} style={{ cursor: 'pointer', marginTop: '12px' }}>
                  Planifier ma venue
                </a>
              </article>
            </div>
          </div>
        </section>
      </main>

      {/* FOOTER */}
      <footer>
        <div className="footer-content">
          <div className="footer-top">
            <div className="footer-brand">
              <strong>FC Chiché</strong>
              <span>Club de football amateur affilié FFF • Stade du Pas des Biches • 79350 Chiché</span>
            </div>
            <div className="footer-links">
              <a onClick={() => handleNavigate('results')} style={{ cursor: 'pointer' }}>Résultats</a>
              <a onClick={() => handleNavigate('calendar')} style={{ cursor: 'pointer' }}>Calendrier</a>
              <a onClick={() => handleNavigate('ranking')} style={{ cursor: 'pointer' }}>Classement</a>
              <a onClick={() => handleNavigate('club')} style={{ cursor: 'pointer' }}>Nous soutenir</a>
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
    </div>
  );
}

export default App;
