/**
 * Composants UI - FC Chiche
 * Bibliothèque de composants réutilisables
 */

const Components = {
    /**
     * Initialiser le chemin des assets
     */
    init() {
        const body = document.querySelector('body');
        this.assetBase = body?.getAttribute('data-assets-base') || '/assets';
        assert(typeof this.assetBase === 'string', 'assetBase must be a string');
    },

    /**
     * Carte de match
     */
    matchCard(match, isResult = false) {
        assert(match && typeof match === 'object', 'Match must be an object');
        
        const isHome = match.is_home;
        const homeTeam = isHome ? 'CHICHE FC' : match.adversaire;
        const awayTeam = isHome ? match.adversaire : 'CHICHE FC';
        
        const defaultLogo = `${this.assetBase}/images/default-team-logo.svg`;
        const homeLogo = isHome ? `${this.assetBase}/images/logo.svg` : (match.logo_adversaire || defaultLogo);
        const awayLogo = isHome ? (match.logo_adversaire || defaultLogo) : `${this.assetBase}/images/logo.svg`;
        const homeLogoOriginal = homeLogo;
        const awayLogoOriginal = awayLogo;
        
        let scoreHTML = '';
        let resultBadge = '';
        
        if (isResult && match.score_home !== null && match.score_away !== null) {
            scoreHTML = `
                <div class="match-score">
                    <div class="match-score-values">
                        <span>${match.score_home}</span>
                        <span class="match-score-separator">-</span>
                        <span>${match.score_away}</span>
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
                    <span>${this.escapeHtml(match.date_fr)}</span>
                </div>
                <div class="match-card-body">
                    <div class="match-teams">
                        <div class="match-team">
                            <div class="match-team-logo">
                                <img src="${this.escapeAttribute(homeLogo)}" alt="${this.escapeAttribute(homeTeam)}" data-original-src="${this.escapeAttribute(homeLogoOriginal)}" onerror="Components.handleImageError(this)">
                            </div>
                            <div class="match-team-name">${this.escapeHtml(homeTeam)}</div>
                            ${isHome ? '<div class="match-team-lieu">Domicile</div>' : ''}
                        </div>
                        ${scoreHTML}
                        <div class="match-team">
                            <div class="match-team-logo">
                                <img src="${this.escapeAttribute(awayLogo)}" alt="${this.escapeAttribute(awayTeam)}" data-original-src="${this.escapeAttribute(awayLogoOriginal)}" onerror="Components.handleImageError(this)">
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
     * Gestion erreur chargement image
     */
    handleImageError(img) {
        const defaultLogo = `${Components.assetBase}/images/default-team-logo.svg`;
        if (img.src !== defaultLogo) {
            img.src = defaultLogo;
        }
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
                    <td><strong>${team.pts}</strong></td>
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
     * Générer vague SVG
     */
    generateWaveSVG() {
        return `
            <svg class="hero-wave" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 120" preserveAspectRatio="none">
                <path fill="#f3f6fa" fill-opacity="1" d="M0,64L48,69.3C96,75,192,85,288,80C384,75,480,53,576,48C672,43,768,53,864,58.7C960,64,1056,64,1152,58.7C1248,53,1344,43,1392,37.3L1440,32L1440,120L1392,120C1344,120,1248,120,1152,120C1056,120,960,120,864,120C768,120,672,120,576,120C480,120,384,120,288,120C192,120,96,120,48,120L0,120Z"></path>
            </svg>
        `;
    },

    /**
     * Hero section avec vague
     */
    hero(title, subtitle, backgroundImage = null) {
        assert(typeof title === 'string' && title.trim().length > 0, 'Hero title must be a non-empty string');
        assert(subtitle === null || subtitle === undefined || typeof subtitle === 'string', 'Hero subtitle must be a string or null');

        const hasBackground = typeof backgroundImage === 'string' && backgroundImage.trim().length > 0;
        const heroStyles = hasBackground ? ` style="--hero-background: url('${this.escapeAttribute(backgroundImage)}')"` : '';
        const heroClass = hasBackground ? 'page-hero page-hero--with-image' : 'page-hero';
        const subtitleText = typeof subtitle === 'string' ? subtitle.trim() : '';
        const subtitleHtml = subtitleText.length > 0 ? `<p>${this.escapeHtml(subtitleText)}</p>` : '';

        return `
            <section class="${heroClass}"${heroStyles}>
                <div class="page-hero-content">
                    <h1>${this.escapeHtml(title)}</h1>
                    ${subtitleHtml}
                </div>
                ${this.generateWaveSVG()}
            </section>
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
    },

    /**
     * Escape pour attribut HTML
     */
    escapeAttribute(value) {
        assert(typeof this.assetBase === 'string', 'assetBase must be initialised');
        const fallback = '';

        if (value === null || value === undefined) {
            return fallback;
        }

        const stringValue = String(value);
        assert(typeof stringValue === 'string', 'Attribute value must be convertible to string');

        const escaped = stringValue
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
        assert(typeof escaped === 'string', 'Escaped attribute must be a string');

        return escaped;
    }
};

// Fonction assert
function assert(condition, message) {
    if (!condition) {
        throw new Error(`Assertion failed: ${message}`);
    }
}

// Initialiser au chargement
document.addEventListener('DOMContentLoaded', () => {
    Components.init();
});

// Export
window.Components = Components;