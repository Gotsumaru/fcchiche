/**
 * Router SPA - FC Chiche
 * Gestion navigation client-side
 */

class Router {
    constructor() {
        this.routes = {};
        this.currentRoute = null;
        this.maxIterations = 100;
        
        window.addEventListener('popstate', () => this.handleRoute());
        
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-link]')) {
                e.preventDefault();
                this.navigateTo(e.target.getAttribute('href'));
            }
        });
    }
    
    /**
     * Enregistrer une route
     */
    register(path, handler) {
        assert(typeof path === 'string' && path.length > 0, 'Path must be non-empty string');
        assert(typeof handler === 'function', 'Handler must be a function');
        
        this.routes[path] = handler;
        return this;
    }
    
    /**
     * Naviguer vers une route
     */
    navigateTo(path) {
        assert(typeof path === 'string', 'Path must be a string');
        
        window.history.pushState(null, null, path);
        this.handleRoute();
    }
    
    /**
     * Gérer la route actuelle
     */
    async handleRoute() {
        const path = window.location.pathname;
        
        // Mettre à jour navigation active
        this.updateActiveLinks(path);
        
        // Trouver le handler correspondant
        let handler = this.routes[path];
        
        // Route par défaut si non trouvée
        if (!handler) {
            handler = this.routes['/'] || this.routes['*'];
        }
        
        if (handler) {
            this.currentRoute = path;
            await handler();
            
            // Scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });
        } else {
            console.error(`No handler found for route: ${path}`);
        }
    }
    
    /**
     * Mettre à jour les liens actifs
     */
    updateActiveLinks(path) {
        const links = document.querySelectorAll('[data-link]');
        let counter = 0;
        
        links.forEach((link) => {
            if (counter >= this.maxIterations) return;
            
            const href = link.getAttribute('href');
            if (href === path || (path === '/' && href === '/resultats')) {
                link.classList.add('active');
            } else {
                link.classList.remove('active');
            }
            
            counter++;
        });
    }
    
    /**
     * Obtenir paramètres URL
     */
    getQueryParams() {
        const params = new URLSearchParams(window.location.search);
        const result = {};
        
        for (const [key, value] of params.entries()) {
            result[key] = value;
        }
        
        return result;
    }
    
    /**
     * Démarrer le router
     */
    start() {
        this.handleRoute();
    }
}

// Fonction assert pour validation
function assert(condition, message) {
    if (!condition) {
        throw new Error(`Assertion failed: ${message}`);
    }
}

// Export
window.Router = Router;