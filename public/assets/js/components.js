/**
 * Composants UI - FC Chiche
 * Builders pour générer le HTML des composants
 */

const Components = {
    /**
     * Card de match (résultat ou calendrier)
     */
    matchCard(match, isResult = false) {
        assert(match && typeof match === 'object', 'Match must be an object');
        
        const isHome = match.lieu === 'DOM';
        const homeTeam = isHome ? 'CHICHE FC' : match.exterieur;
        const awayTeam = isHome ? match.exterieur : 'CHICHE FC';
        const homeLogo = isHome ? '/assets/images/logo.svg' : (match.logo_adversaire || '/assets/images/placeholder-logo.svg');
        const awayLogo = isHome ? (match.logo_adversaire || '/assets/images/placeholder-logo.svg') : '/assets/images/logo.svg';
        
        let scoreHTML = '';
        let resultBadge = '';
        
        if (isResult && match.home_score !== null) {
            const homeScore = isHome ? match.home_score : match.away_score;
            const awayScore = isHome ? match.away_score : match.home_score;
            
            scoreHTML = `
                <div class="match-score">
                    <div class="match-score-display">
                        <span>${homeScore}</span>
                        <span class="match-score-separator">-</span>
                        <span>${awayScore}</span>
                    </div>
                </div>
            `;
            
            if (match.resultat) {
                const resultClass = match.resultat === 'V' ? 'victoire' : 
                                   match.resultat === 'N' ? 'nul' : 'defaite';
                const resultLabel = match.resultat === 'V' ? 'Victoire' : 
                                   match.resultat === 'N' ? 'Nul' : 'Défaite';
                resultBadge = `<span class="match-result-badge ${resultClass}">${resultLabel}</span>`;
            }
        } else {
            scoreHTML = `
                <div class="match-score">
                    <div class="match-score-time">${match.time || '00H00'}</div>
                </div>
            `;
        }
        
        const competitionType = match.competition_type === 'CP' ? 'Coupe' : 'Championnat';
        
        return `
            <div class="match-card fade-in-up">
                <div class="match-card-header">
                    <div class="match-card-competition">
                        <span class="match-card-badge">${competitionType}</span>
                        <span>${this.escapeHtml(match.competition)}</span>
                    </div>
                    <span>${match.date_fr}</span>
                </div>
                <div class="match-card-body">
                    <div class="match-teams">
                        <div class="match-team">
                            <div class="match-team-logo">
                                <img src="${homeLogo}" alt="${homeTeam}" onerror="this.src='/assets/images/placeholder-logo.svg'">
                            </div>
                            <div class="match-team-name">${this.escapeHtml(homeTeam)}</div>
                            ${isHome ? '<div class="match-team-lieu">Domicile</div>' : ''}
                        </div>
                        ${scoreHTML}
                        <div class="match-team">
                            <div class="match-team-logo">
                                <img src="${awayLogo}" alt="${awayTeam}" onerror="this.src='/assets/images/placeholder-logo.svg'">
                            </div>
                            <div class="match-team-name">${this.escapeHtml(awayTeam)}</div>
                            ${!isHome ? '<div class="match-team-lieu">Extérieur</div>' : ''}
                        </div>
                    </div>
                </div>
                <div class="match-card-footer">
                    <span>${this.escapeHtml(match.equipe_chiche || '')}</span>
                    ${resultBadge}
                    ${match.terrain && !isResult ? `<span>📍 ${this.escapeHtml(match.terrain)}</span>` : ''}
                </div>
            </div>
        `;
    },
    
    /**
     * Barre de stats
     */
    statsBar(stats) {
        assert(stats && typeof stats === 'object', 'Stats must be an object');
        
        return `
            <div class="stats-bar fade-in-up">
                <div class="stat-item">
                    <span class="stat-value">${stats.matchs_joues || 0}</span>
                    <span class="stat-label">Matchs</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value" style="color: var(--color-victoire)">${stats.victoires || 0}</span>
                    <span class="stat-label">Victoires</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value" style="color: var(--color-nul)">${stats.nuls || 0}</span>
                    <span class="stat-label">Nuls</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value" style="color: var(--color-defaite)">${stats.defaites || 0}</span>
                    <span class="stat-label">Défaites</span>
                </div>
            </div>
        `;
    },
    
    /**
     * Tableau de classement
     */
    classementTable(classement, currentTeam = null) {
        assert(Array.isArray(classement), 'Classement must be an array');
        
        let rowsHTML = '';
        let counter = 0;
        const maxIterations = 50;
        
        classement.forEach((team) => {
            if (counter >= maxIterations) return;
            
            const isCurrentTeam = currentTeam && team.equipe === currentTeam;
            const highlightClass = isCurrentTeam ? 'highlight' : '';
            
            rowsHTML += `
                <tr class="${highlightClass}">
                    <td class="classement-rank">${team.rang}</td>
                    <td>
                        <div class="classement-team">
                            ${team.logo_url ? `<img src="${team.logo_url}" class="classement-team-logo" alt="${team.equipe}">` : ''}
                            <span>${this.escapeHtml(team.equipe)}</span>
                        </div>
                    </td>
                    <td>${team.pts}</td>
                    <td>${team.joues}</td>
                    <td>${team.g || 0}</td>
                    <td>${team.n || 0}</td>
                    <td>${team.p || 0}</td>
                    <td>${team.bp || 0}</td>
                    <td>${team.bc || 0}</td>
                    <td>${team.diff > 0 ? '+' : ''}${team.diff}</td>
                </tr>
            `;
            
            counter++;
        });
        
        return `
            <div class="classement-table">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Équipe</th>
                            <th>Pts</th>
                            <th>J</th>
                            <th>G</th>
                            <th>N</th>
                            <th>P</th>
                            <th>BP</th>
                            <th>BC</th>
                            <th>Diff</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${rowsHTML}
                    </tbody>
                </table>
            </div>
        `;
    },
    
    /**
     * Filtre équipes
     */
    equipesFilter(equipes, selectedEquipe = null) {
        assert(Array.isArray(equipes), 'Equipes must be an array');
        
        let optionsHTML = '<option value="">Toutes les équipes</option>';
        let counter = 0;
        const maxIterations = 20;
        
        equipes.forEach((equipe) => {
            if (counter >= maxIterations) return;
            
            const selected = selectedEquipe === equipe.display_name ? 'selected' : '';
            optionsHTML += `<option value="${equipe.display_name}" ${selected}>${this.escapeHtml(equipe.display_name)}</option>`;
            
            counter++;
        });
        
        return `
            <div class="filter-item">
                <label class="filter-label" for="equipe-filter">Équipe</label>
                <select id="equipe-filter" class="select">
                    ${optionsHTML}
                </select>
            </div>
        `;
    },
    
    /**
     * Loading spinner
     */
    loading() {
        return `
            <div class="loading-container">
                <div class="spinner"></div>
            </div>
        `;
    },
    
    /**
     * Empty state
     */
    emptyState(icon, title, message) {
        return `
            <div class="empty-state">
                <div class="empty-state-icon">${icon}</div>
                <h3 class="empty-state-title">${this.escapeHtml(title)}</h3>
                <p class="empty-state-text">${this.escapeHtml(message)}</p>
            </div>
        `;
    },
    
    /**
     * Hero section
     */
    hero(title, subtitle, backgroundImage = '/assets/images/placeholder-bg.jpg') {
        return `
            <div class="page-hero" style="background-image: url('${backgroundImage}')">
                <div class="page-hero-content">
                    <h1>${this.escapeHtml(title)}</h1>
                    ${subtitle ? `<p>${this.escapeHtml(subtitle)}</p>` : ''}
                </div>
            </div>
        `;
    },
    
    /**
     * Escape HTML pour sécurité
     */
    escapeHtml(text) {
        if (text === null || text === undefined) return '';
        
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
};

// Fonction assert
function assert(condition, message) {
    if (!condition) {
        throw new Error(`Assertion failed: ${message}`);
    }
}

// Export
window.Components = Components;