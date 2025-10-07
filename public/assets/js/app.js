/**
 * Application principale - FC Chiche
 * Initialisation et gestion des pages
 */

class App {
    constructor() {
        this.api = new ApiClient();
        this.router = new Router();
        this.equipes = [];
        this.currentEquipe = 'SEM 1'; // Équipe par défaut
        
        this.init();
    }
    
    /**
     * Initialiser l'application
     */
    async init() {
        try {
            // Charger les équipes
            await this.loadEquipes();
            
            // Enregistrer les routes
            this.registerRoutes();
            
            // Démarrer le router
            this.router.start();
            
            // Activer scroll reveal
            this.initScrollReveal();
            
        } catch (error) {
            console.error('Failed to initialize app:', error);
            this.showError('Erreur de chargement de l\'application');
        }
    }
    
    /**
     * Charger la liste des équipes
     */
    async loadEquipes() {
        try {
            const response = await this.api.getEquipes();
            this.equipes = response.equipes || [];
        } catch (error) {
            console.error('Failed to load equipes:', error);
            this.equipes = [];
        }
    }
    
    /**
     * Enregistrer les routes
     */
    registerRoutes() {
        this.router
            .register('/', () => this.pageResultats())
            .register('/resultats', () => this.pageResultats())
            .register('/calendrier', () => this.pageCalendrier())
            .register('/classement', () => this.pageClassement())
            .register('/club', () => this.pageClub());
    }
    
    /**
     * PAGE RÉSULTATS
     */
    async pageResultats() {
        const app = document.getElementById('app');
        
        app.innerHTML = `
            ${Components.hero('Résultats', 'Les derniers matchs du FC Chiche')}
            <div class="page-content">
                <div class="container">
                    <div class="filters">
                        <div class="filters-group">
                            ${Components.equipesFilter(this.equipes, this.currentEquipe)}
                        </div>
                    </div>
                    <div id="results-content">
                        ${Components.loading()}
                    </div>
                </div>
            </div>
        `;
        
        // Event listener filtre
        const filter = document.getElementById('equipe-filter');
        if (filter) {
            filter.addEventListener('change', (e) => {
                this.currentEquipe = e.target.value || 'SEM 1';
                this.loadResultats();
            });
        }
        
        // Charger les résultats
        await this.loadResultats();
    }
    
    /**
     * Charger les résultats
     */
    async loadResultats() {
        const container = document.getElementById('results-content');
        if (!container) return;
        
        container.innerHTML = Components.loading();
        
        try {
            const response = await this.api.getResultats(this.currentEquipe, 10);
            
            if (!response.success) {
                throw new Error(response.error || 'Erreur de chargement');
            }
            
            const resultats = response.resultats || [];
            const stats = response.stats || {};
            
            if (resultats.length === 0) {
                container.innerHTML = Components.emptyState(
                    '⚽',
                    'Aucun résultat',
                    'Aucun match terminé pour le moment'
                );
                return;
            }
            
            let html = '';
            
            // Stats bar
            if (stats.matchs_joues > 0) {
                html += Components.statsBar(stats);
            }
            
            // Liste des matchs
            let counter = 0;
            const maxIterations = 20;
            
            resultats.forEach((match) => {
                if (counter >= maxIterations) return;
                html += Components.matchCard(match, true);
                counter++;
            });
            
            container.innerHTML = html;
            
        } catch (error) {
            console.error('Failed to load resultats:', error);
            container.innerHTML = Components.emptyState(
                '⚠️',
                'Erreur de chargement',
                'Impossible de charger les résultats'
            );
        }
    }
    
    /**
     * PAGE CALENDRIER
     */
    async pageCalendrier() {
        const app = document.getElementById('app');
        
        app.innerHTML = `
            ${Components.hero('Calendrier', 'Les prochains matchs du FC Chiche')}
            <div class="page-content">
                <div class="container">
                    <div class="filters">
                        <div class="filters-group">
                            ${Components.equipesFilter(this.equipes, this.currentEquipe)}
                        </div>
                    </div>
                    <div id="calendar-content">
                        ${Components.loading()}
                    </div>
                </div>
            </div>
        `;
        
        // Event listener filtre
        const filter = document.getElementById('equipe-filter');
        if (filter) {
            filter.addEventListener('change', (e) => {
                this.currentEquipe = e.target.value || 'SEM 1';
                this.loadCalendrier();
            });
        }
        
        // Charger le calendrier
        await this.loadCalendrier();
    }
    
    /**
     * Charger le calendrier
     */
    async loadCalendrier() {
        const container = document.getElementById('calendar-content');
        if (!container) return;
        
        container.innerHTML = Components.loading();
        
        try {
            const response = await this.api.getCalendrier(this.currentEquipe, 10);
            
            if (!response.success) {
                throw new Error(response.error || 'Erreur de chargement');
            }
            
            const matchs = response.matchs || [];
            
            if (matchs.length === 0) {
                container.innerHTML = Components.emptyState(
                    '📅',
                    'Aucun match',
                    'Aucun match prévu pour le moment'
                );
                return;
            }
            
            let html = '';
            let counter = 0;
            const maxIterations = 20;
            
            matchs.forEach((match) => {
                if (counter >= maxIterations) return;
                html += Components.matchCard(match, false);
                counter++;
            });
            
            container.innerHTML = html;
            
        } catch (error) {
            console.error('Failed to load calendrier:', error);
            container.innerHTML = Components.emptyState(
                '⚠️',
                'Erreur de chargement',
                'Impossible de charger le calendrier'
            );
        }
    }
    
    /**
     * PAGE CLASSEMENT
     */
    async pageClassement() {
        const app = document.getElementById('app');
        
        app.innerHTML = `
            ${Components.hero('Classement', 'Les classements des équipes')}
            <div class="page-content">
                <div class="container">
                    <div class="filters">
                        <div class="filters-group">
                            ${Components.equipesFilter(this.equipes, this.currentEquipe)}
                        </div>
                    </div>
                    <div id="classement-content">
                        ${Components.loading()}
                    </div>
                </div>
            </div>
        `;
        
        // Event listener filtre
        const filter = document.getElementById('equipe-filter');
        if (filter) {
            filter.addEventListener('change', (e) => {
                this.currentEquipe = e.target.value || 'SEM 1';
                this.loadClassement();
            });
        }
        
        // Charger le classement
        await this.loadClassement();
    }
    
    /**
     * Charger le classement
     */
    async loadClassement() {
        const container = document.getElementById('classement-content');
        if (!container) return;
        
        container.innerHTML = Components.loading();
        
        try {
            if (!this.currentEquipe) {
                container.innerHTML = Components.emptyState(
                    '🏆',
                    'Sélectionnez une équipe',
                    'Choisissez une équipe pour voir son classement'
                );
                return;
            }
            
            const response = await this.api.getClassement(this.currentEquipe);
            
            if (!response.success) {
                throw new Error(response.error || 'Erreur de chargement');
            }
            
            const classement = response.classement || [];
            
            if (classement.length === 0) {
                container.innerHTML = Components.emptyState(
                    '🏆',
                    'Classement indisponible',
                    'Aucun classement disponible pour cette équipe'
                );
                return;
            }
            
            container.innerHTML = Components.classementTable(classement, 'CHICHE FC');
            
        } catch (error) {
            console.error('Failed to load classement:', error);
            container.innerHTML = Components.emptyState(
                '⚠️',
                'Erreur de chargement',
                'Impossible de charger le classement'
            );
        }
    }
    
    /**
     * PAGE CLUB
     */
    async pageClub() {
        const app = document.getElementById('app');
        
        app.innerHTML = `
            ${Components.hero('Le Club', 'Découvrez le FC Chiche')}
            <div class="page-content">
                <div class="container">
                    <div id="club-content">
                        ${Components.loading()}
                    </div>
                </div>
            </div>
        `;
        
        await this.loadClubInfo();
    }
    
    /**
     * Charger les infos du club
     */
    async loadClubInfo() {
        const container = document.getElementById('club-content');
        if (!container) return;
        
        try {
            const response = await this.api.getClubInfo();
            
            if (!response.success) {
                throw new Error(response.error || 'Erreur de chargement');
            }
            
            const club = response.club || {};
            
            container.innerHTML = `
                <div class="card fade-in-up">
                    <h2>${Components.escapeHtml(club.name || 'FC Chiche')}</h2>
                    <p><strong>Adresse :</strong> ${Components.escapeHtml(club.address1 || '')}</p>
                    <p>${Components.escapeHtml(club.postal_code || '')} ${Components.escapeHtml(club.location || '')}</p>
                    <p><strong>District :</strong> ${Components.escapeHtml(club.district_name || '')}</p>
                </div>
            `;
            
        } catch (error) {
            console.error('Failed to load club info:', error);
            container.innerHTML = Components.emptyState(
                '⚠️',
                'Erreur de chargement',
                'Impossible de charger les informations du club'
            );
        }
    }
    
    /**
     * Scroll reveal animation
     */
    initScrollReveal() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('revealed');
                }
            });
        }, { threshold: 0.1 });
        
        document.addEventListener('DOMContentLoaded', () => {
            const elements = document.querySelectorAll('.scroll-reveal');
            elements.forEach(el => observer.observe(el));
        });
    }
    
    /**
     * Afficher une erreur globale
     */
    showError(message) {
        const app = document.getElementById('app');
        app.innerHTML = Components.emptyState(
            '⚠️',
            'Erreur',
            message
        );
    }
}

// Initialiser l'application au chargement
document.addEventListener('DOMContentLoaded', () => {
    window.app = new App();
});