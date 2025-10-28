/**
 * Application principale FC Chiche
 * SPA avec navigation, API calls et affichage dynamique
 */

'use strict';

// Configuration
const CONFIG = {
    apiBase: document.body.dataset.apiBase || '/api',
    basePath: document.body.dataset.basePath || '',
    maxRetries: 3,
    retryDelay: 1000
};

// État application
const STATE = {
    currentPage: 'home',
    currentFilters: {
        results: 'all',
        calendar: 'all',
        ranking: null
    },
    cache: {
        equipes: null,
        club: null
    }
};

// Utilitaires
const Utils = {
    /**
     * Appel API avec retry
     */
    async fetchAPI(endpoint, retries = CONFIG.maxRetries) {
        assert(typeof endpoint === 'string' && endpoint.length > 0, 'Endpoint must be non-empty string');
        assert(retries >= 0, 'Retries must be >= 0');
        
        const url = `${CONFIG.apiBase}/${endpoint}`;
        let lastError;
        let counter = 0;
        
        while (counter <= retries) {
            try {
                const response = await fetch(url);
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }
                const data = await response.json();
                return data.data || data;
            } catch (error) {
                lastError = error;
                counter++;
                if (counter <= retries) {
                    await new Promise(resolve => setTimeout(resolve, CONFIG.retryDelay));
                }
            }
        }
        
        console.error(`API call failed after ${retries} retries:`, lastError);
        throw lastError;
    },

    /**
     * Formater date
     */
    formatDate(dateString) {
        assert(typeof dateString === 'string', 'Date must be string');
        
        const date = new Date(dateString);
        const options = { 
            day: 'numeric', 
            month: 'long', 
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        };
        return date.toLocaleDateString('fr-FR', options).replace(':', 'h');
    },

    /**
     * État match (victoire/nul/défaite)
     */
    getMatchStatus(match, clubId = 5403) {
        assert(match && typeof match === 'object', 'Match must be object');
        
        if (!match.is_result || match.home_score === null || match.away_score === null) {
            return 'upcoming';
        }
        
        const isHome = match.home_club_id === clubId;
        const ourScore = isHome ? match.home_score : match.away_score;
        const theirScore = isHome ? match.away_score : match.home_score;
        
        if (ourScore > theirScore) return 'victory';
        if (ourScore < theirScore) return 'defeat';
        return 'draw';
    },

    /**
     * Emoji domicile/extérieur
     */
    getLocationEmoji(isHome) {
        return isHome ? '🏠' : '🚗';
    },

    /**
     * Nom court équipe
     */
    getTeamShortName(equipe) {
        if (!equipe) return 'Équipe';
        return equipe.short_name || equipe.name || 'Équipe';
    },

    /**
     * Afficher état vide
     */
    showEmptyState(container, icon, text) {
        assert(container instanceof HTMLElement, 'Container must be HTMLElement');
        
        container.innerHTML = `
            <div class="empty-state">
                <div class="empty-icon">${icon}</div>
                <div class="empty-text">${text}</div>
            </div>
        `;
    },

    /**
     * Afficher erreur
     */
    showError(container, message) {
        assert(container instanceof HTMLElement, 'Container must be HTMLElement');
        
        container.innerHTML = `
            <div class="error-state">
                <div class="error-icon">⚠️</div>
                <div class="error-text">${message}</div>
                <button class="error-retry" onclick="location.reload()">Réessayer</button>
            </div>
        `;
    }
};

// Navigation
const Navigation = {
    /**
     * Initialiser navigation
     */
    init() {
        // Navigation desktop
        document.querySelectorAll('.nav-desktop-link').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const page = link.dataset.page;
                if (page) this.navigateTo(page);
            });
        });

        // Navigation mobile
        document.querySelectorAll('.nav-mobile-link').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const page = link.dataset.page;
                if (page) this.navigateTo(page);
            });
        });

        // Logo et boutons
        document.querySelectorAll('[data-page]').forEach(el => {
            if (!el.classList.contains('nav-desktop-link') && !el.classList.contains('nav-mobile-link')) {
                el.addEventListener('click', (e) => {
                    e.preventDefault();
                    const page = el.dataset.page;
                    if (page) this.navigateTo(page);
                });
            }
        });

        // Gestion historique navigateur
        window.addEventListener('popstate', (e) => {
            const page = e.state?.page || 'home';
            this.navigateTo(page, false);
        });
    },

    /**
     * Naviguer vers page
     */
    async navigateTo(page, pushState = true) {
        assert(typeof page === 'string', 'Page must be string');
        
        if (page === STATE.currentPage) return;

        // Masquer pages
        document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
        
        // Afficher page cible
        const targetPage = document.getElementById(page);
        if (!targetPage) {
            console.error(`Page ${page} not found`);
            return;
        }
        targetPage.classList.add('active');

        // Mettre à jour navigation
        document.querySelectorAll('.nav-desktop-link').forEach(link => {
            link.classList.toggle('active', link.dataset.page === page);
        });
        document.querySelectorAll('.nav-mobile-link').forEach(link => {
            link.classList.toggle('active', link.dataset.page === page);
        });

        // Historique
        if (pushState) {
            const url = page === 'home' ? '/' : `/${page}`;
            window.history.pushState({ page }, '', CONFIG.basePath + url);
        }

        // Mettre à jour état
        STATE.currentPage = page;

        // Scroll top
        window.scrollTo({ top: 0, behavior: 'smooth' });

        // Charger contenu
        await this.loadPageContent(page);
    },

    /**
     * Charger contenu page
     */
    async loadPageContent(page) {
        switch (page) {
            case 'home':
                await Pages.loadHome();
                break;
            case 'results':
                await Pages.loadResults();
                break;
            case 'calendar':
                await Pages.loadCalendar();
                break;
            case 'ranking':
                await Pages.loadRanking();
                break;
        }
    }
};

// Pages
const Pages = {
    /**
     * Page d'accueil
     */
    async loadHome() {
        const container = document.getElementById('homeContent');
        if (!container) return;

        try {
            // Charger données en parallèle
            const [club, equipes, nextMatchs, lastResults] = await Promise.all([
                Utils.fetchAPI('club'),
                Utils.fetchAPI('equipes'),
                Utils.fetchAPI('matchs?upcoming=3'),
                Utils.fetchAPI('matchs?results=5')
            ]);

            STATE.cache.club = club;
            STATE.cache.equipes = equipes;

            // Afficher contenu
            container.innerHTML = `
                <div class="home-grid">
                    <div class="home-section">
                        <h3 class="section-title">Prochain match à domicile</h3>
                        ${this.renderNextMatch(nextMatchs)}
                    </div>
                    
                    <div class="home-section">
                        <h3 class="section-title">Derniers résultats</h3>
                        ${this.renderLastResults(lastResults.slice(0, 3))}
                    </div>
                    
                    <div class="home-section home-stats">
                        <h3 class="section-title">Nos équipes</h3>
                        <div class="stats-grid">
                            <div class="stat-card">
                                <div class="stat-value">${equipes.length}</div>
                                <div class="stat-label">Équipes</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-value">${club.name || 'FC Chiche'}</div>
                                <div class="stat-label">Club</div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        } catch (error) {
            Utils.showError(container, 'Erreur de chargement');
            console.error('Error loading home:', error);
        }
    },

    /**
     * Render prochain match
     */
    renderNextMatch(matchs) {
        const homeMatch = matchs.find(m => m.is_home) || matchs[0];
        
        if (!homeMatch) {
            return '<div class="info-box">Aucun match à venir</div>';
        }

        return `
            <div class="match-card match-card--featured">
                <div class="match-date">${Utils.formatDate(homeMatch.date)}</div>
                <div class="match-competition">${homeMatch.competition_name || 'Championnat'}</div>
                <div class="match-teams">
                    <div class="match-team">
                        <div class="match-team-name">${homeMatch.home_name}</div>
                    </div>
                    <div class="match-vs">VS</div>
                    <div class="match-team">
                        <div class="match-team-name">${homeMatch.away_name}</div>
                    </div>
                </div>
                ${homeMatch.terrain_name ? `<div class="match-location">📍 ${homeMatch.terrain_name}</div>` : ''}
            </div>
        `;
    },

    /**
     * Render derniers résultats
     */
    renderLastResults(results) {
        if (!results || results.length === 0) {
            return '<div class="info-box">Aucun résultat récent</div>';
        }

        return `
            <div class="results-list">
                ${results.map(match => `
                    <div class="result-card result-card--${Utils.getMatchStatus(match)}">
                        <div class="result-header">
                            <span class="result-date">${Utils.formatDate(match.date)}</span>
                            <span class="result-location">${Utils.getLocationEmoji(match.is_home)}</span>
                        </div>
                        <div class="result-score">
                            <span class="result-team">${match.is_home ? 'FC Chiche' : match.opponent_name}</span>
                            <span class="result-score-value">${match.is_home ? match.home_score : match.away_score} - ${match.is_home ? match.away_score : match.home_score}</span>
                            <span class="result-team">${match.is_home ? match.opponent_name : 'FC Chiche'}</span>
                        </div>
                        <div class="result-competition">${match.competition_name || ''}</div>
                    </div>
                `).join('')}
            </div>
        `;
    },

    /**
     * Page résultats
     */
    async loadResults() {
        const container = document.getElementById('resultsContent');
        const filtersContainer = document.getElementById('resultsFilters');
        
        if (!container || !filtersContainer) return;

        try {
            // Charger équipes si pas en cache
            if (!STATE.cache.equipes) {
                STATE.cache.equipes = await Utils.fetchAPI('equipes');
            }

            // Initialiser filtres
            this.initFilters(filtersContainer, 'results');

            // Charger résultats
            await this.filterResults(STATE.currentFilters.results);

        } catch (error) {
            Utils.showError(container, 'Erreur de chargement des résultats');
            console.error('Error loading results:', error);
        }
    },

    /**
     * Filtrer résultats
     */
    async filterResults(teamFilter) {
        const container = document.getElementById('resultsContent');
        if (!container) return;

        container.innerHTML = '<div class="loading">Chargement...</div>';

        try {
            let endpoint = 'matchs?results=30';
            
            if (teamFilter !== 'all') {
                const equipe = STATE.cache.equipes.find(e => this.getTeamFilterId(e) === teamFilter);
                if (equipe) {
                    endpoint = `matchs?equipe_id=${equipe.id}&results=30`;
                }
            }

            const results = await Utils.fetchAPI(endpoint);

            if (!results || results.length === 0) {
                Utils.showEmptyState(container, '📊', 'Aucun résultat trouvé');
                return;
            }

            container.innerHTML = `
                <div class="results-grid">
                    ${results.map(match => `
                        <div class="result-card result-card--${Utils.getMatchStatus(match)}">
                            <div class="result-header">
                                <span class="result-date">${Utils.formatDate(match.date)}</span>
                                <span class="result-location">${Utils.getLocationEmoji(match.is_home)}</span>
                            </div>
                            <div class="result-score">
                                <div class="result-team">${match.is_home ? 'FC Chiche' : match.opponent_name}</div>
                                <div class="result-score-value">
                                    ${match.home_score !== null ? match.home_score : '-'} - ${match.away_score !== null ? match.away_score : '-'}
                                </div>
                                <div class="result-team">${match.is_home ? match.opponent_name : 'FC Chiche'}</div>
                            </div>
                            <div class="result-competition">${match.competition_name || ''}</div>
                        </div>
                    `).join('')}
                </div>
            `;

        } catch (error) {
            Utils.showError(container, 'Erreur de chargement');
            console.error('Error filtering results:', error);
        }
    },

    /**
     * Page calendrier
     */
    async loadCalendar() {
        const container = document.getElementById('calendarContent');
        const filtersContainer = document.getElementById('calendarFilters');
        
        if (!container || !filtersContainer) return;

        try {
            if (!STATE.cache.equipes) {
                STATE.cache.equipes = await Utils.fetchAPI('equipes');
            }

            this.initFilters(filtersContainer, 'calendar');
            await this.filterCalendar(STATE.currentFilters.calendar);

        } catch (error) {
            Utils.showError(container, 'Erreur de chargement du calendrier');
            console.error('Error loading calendar:', error);
        }
    },

    /**
     * Filtrer calendrier
     */
    async filterCalendar(teamFilter) {
        const container = document.getElementById('calendarContent');
        if (!container) return;

        container.innerHTML = '<div class="loading">Chargement...</div>';

        try {
            let endpoint = 'matchs?upcoming=20';
            
            if (teamFilter !== 'all') {
                const equipe = STATE.cache.equipes.find(e => this.getTeamFilterId(e) === teamFilter);
                if (equipe) {
                    endpoint = `matchs?equipe_id=${equipe.id}&upcoming=20`;
                }
            }

            const matchs = await Utils.fetchAPI(endpoint);

            if (!matchs || matchs.length === 0) {
                Utils.showEmptyState(container, '📅', 'Aucun match à venir');
                return;
            }

            container.innerHTML = `
                <div class="calendar-grid">
                    ${matchs.map(match => `
                        <div class="calendar-card">
                            <div class="calendar-date">${Utils.formatDate(match.date)}</div>
                            <div class="calendar-teams">
                                <div class="calendar-team">${match.home_name}</div>
                                <div class="calendar-vs">VS</div>
                                <div class="calendar-team">${match.away_name}</div>
                            </div>
                            <div class="calendar-competition">${match.competition_name || ''}</div>
                            ${match.terrain_name ? `<div class="calendar-location">📍 ${match.terrain_name}</div>` : ''}
                        </div>
                    `).join('')}
                </div>
            `;

        } catch (error) {
            Utils.showError(container, 'Erreur de chargement');
            console.error('Error filtering calendar:', error);
        }
    },

    /**
     * Page classements
     */
    async loadRanking() {
        const container = document.getElementById('rankingContent');
        const filtersContainer = document.getElementById('rankingFilters');
        
        if (!container || !filtersContainer) return;

        try {
            // Charger compétitions avec classements
            const competitions = await Utils.fetchAPI('classements?competitions=true');

            if (!competitions || competitions.length === 0) {
                Utils.showEmptyState(container, '🏆', 'Aucun classement disponible');
                filtersContainer.innerHTML = '';
                return;
            }

            // Créer filtres
            filtersContainer.innerHTML = competitions.map((comp, idx) => `
                <button class="filter-item ${idx === 0 ? 'active' : ''}" data-competition="${comp.id}">
                    ${comp.name || 'Championnat'}
                </button>
            `).join('');

            // Event listeners
            filtersContainer.querySelectorAll('.filter-item').forEach(btn => {
                btn.addEventListener('click', async () => {
                    filtersContainer.querySelectorAll('.filter-item').forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    await this.filterRanking(parseInt(btn.dataset.competition, 10));
                });
            });

            // Charger premier classement
            STATE.currentFilters.ranking = competitions[0].id;
            await this.filterRanking(competitions[0].id);

        } catch (error) {
            Utils.showError(container, 'Erreur de chargement des classements');
            console.error('Error loading rankings:', error);
        }
    },

    /**
     * Filtrer classement
     */
    async filterRanking(competitionId) {
        assert(typeof competitionId === 'number' && competitionId > 0, 'Competition ID must be positive number');
        
        const container = document.getElementById('rankingContent');
        if (!container) return;

        container.innerHTML = '<div class="loading">Chargement...</div>';

        try {
            const classement = await Utils.fetchAPI(`classements?competition_id=${competitionId}`);

            if (!classement || classement.length === 0) {
                Utils.showEmptyState(container, '🏆', 'Classement non disponible');
                return;
            }

            container.innerHTML = `
                <div class="ranking-table">
                    <div class="ranking-header">
                        <div class="ranking-col ranking-col--pos">Pos</div>
                        <div class="ranking-col ranking-col--team">Équipe</div>
                        <div class="ranking-col ranking-col--pts">Pts</div>
                        <div class="ranking-col ranking-col--played">J</div>
                        <div class="ranking-col ranking-col--diff">Diff</div>
                    </div>
                    ${classement.map(row => {
                        const isOurTeam = row.cl_no === 5403;
                        return `
                            <div class="ranking-row ${isOurTeam ? 'ranking-row--highlight' : ''}">
                                <div class="ranking-col ranking-col--pos">${row.ranking}</div>
                                <div class="ranking-col ranking-col--team">
                                    ${row.club_name || 'Club'}
                                </div>
                                <div class="ranking-col ranking-col--pts">${row.points}</div>
                                <div class="ranking-col ranking-col--played">${row.games_played}</div>
                                <div class="ranking-col ranking-col--diff">${row.goal_difference >= 0 ? '+' : ''}${row.goal_difference}</div>
                            </div>
                        `;
                    }).join('')}
                </div>
            `;

        } catch (error) {
            Utils.showError(container, 'Erreur de chargement');
            console.error('Error filtering ranking:', error);
        }
    },

    /**
     * Initialiser filtres équipes
     */
    initFilters(container, type) {
        assert(container instanceof HTMLElement, 'Container must be HTMLElement');
        assert(['results', 'calendar'].includes(type), 'Type must be results or calendar');
        
        const equipes = STATE.cache.equipes || [];
        
        container.innerHTML = `
            <button class="filter-item active" data-team="all">Toutes les équipes</button>
            ${equipes.map(equipe => `
                <button class="filter-item" data-team="${this.getTeamFilterId(equipe)}">
                    ${Utils.getTeamShortName(equipe)}
                </button>
            `).join('')}
        `;

        // Event listeners
        container.querySelectorAll('.filter-item').forEach(btn => {
            btn.addEventListener('click', async () => {
                container.querySelectorAll('.filter-item').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                
                const team = btn.dataset.team || 'all';
                STATE.currentFilters[type] = team;
                
                if (type === 'results') {
                    await this.filterResults(team);
                } else {
                    await this.filterCalendar(team);
                }
            });
        });
    },

    /**
     * ID filtre équipe
     */
    getTeamFilterId(equipe) {
        return (equipe.short_name || equipe.name || '').toLowerCase().replace(/\s+/g, '_');
    }
};

// Assertions helper
function assert(condition, message) {
    if (!condition) {
        console.error('Assertion failed:', message);
        throw new Error(message);
    }
}

// Initialisation
document.addEventListener('DOMContentLoaded', async () => {
    try {
        // Initialiser navigation
        Navigation.init();

        // Masquer loader
        setTimeout(() => {
            const loader = document.getElementById('initialLoader');
            if (loader) {
                loader.classList.add('hidden');
            }
        }, 800);

        // Charger page initiale
        const path = window.location.pathname.replace(CONFIG.basePath, '').split('/').filter(p => p)[0];
        const initialPage = ['home', 'results', 'calendar', 'ranking'].includes(path) ? path : 'home';
        
        await Navigation.navigateTo(initialPage, false);

    } catch (error) {
        console.error('Initialization error:', error);
    }
});

// Register Service Worker
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register(CONFIG.basePath + '/service-worker.js')
            .then(registration => console.log('SW registered:', registration.scope))
            .catch(error => console.log('SW registration failed:', error));
    });
}