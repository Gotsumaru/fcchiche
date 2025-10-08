/**
 * Client API - FC Chiche
 * Communication avec le backend PHP
 */

const apiBaseFromDom = (() => {
    const body = document.body;
    if (!body || !body.dataset) {
        return '/api';
    }

    const configuredBase = body.dataset.apiBase || '/api';
    return configuredBase.trim() === '' ? '/api' : configuredBase;
})();

class ApiClient {
    constructor(baseUrl = apiBaseFromDom) {
        this.baseUrl = baseUrl;
        this.timeout = 10000;
        this.maxRetries = 3;
    }
    
    /**
     * Requête HTTP générique
     */
    async request(endpoint, options = {}) {
        assert(typeof endpoint === 'string' && endpoint.length > 0, 'Endpoint must be non-empty string');
        
        const url = `${this.baseUrl}${endpoint}`;
        const defaultOptions = {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        };
        
        const config = { ...defaultOptions, ...options };
        let retries = 0;
        let lastError = null;
        
        while (retries < this.maxRetries) {
            try {
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), this.timeout);
                
                const response = await fetch(url, {
                    ...config,
                    signal: controller.signal
                });
                
                clearTimeout(timeoutId);
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const data = await response.json();
                return data;
                
            } catch (error) {
                lastError = error;
                retries++;
                
                if (retries < this.maxRetries) {
                    await this.delay(500 * retries);
                }
            }
        }
        
        console.error(`API request failed after ${this.maxRetries} retries:`, lastError);
        throw lastError;
    }
    
    /**
     * GET request
     */
    async get(endpoint, params = {}) {
        const queryString = new URLSearchParams(params).toString();
        const url = queryString ? `${endpoint}?${queryString}` : endpoint;
        return this.request(url);
    }
    
    /**
     * Récupérer les derniers résultats
     */
    async getResultats(equipe = null, limit = 10) {
        const params = { limit };
        if (equipe) {
            params.equipe = equipe;
        }
        return this.get('/resultats.php', params);
    }
    
    /**
     * Récupérer le calendrier
     */
    async getCalendrier(equipe = null, limit = 10) {
        const params = { limit };
        if (equipe) {
            params.equipe = equipe;
        }
        return this.get('/calendrier.php', params);
    }
    
    /**
     * Récupérer le classement
     */
    async getClassement(equipe) {
        assert(typeof equipe === 'string' && equipe.length > 0, 'Equipe must be non-empty string');
        return this.get('/classement.php', { equipe });
    }
    
    /**
     * Récupérer infos du club
     */
    async getClubInfo() {
        return this.get('/club.php');
    }
    
    /**
     * Récupérer liste des équipes
     */
    async getEquipes() {
        return this.get('/equipes.php');
    }
    
    /**
     * Délai pour retry
     */
    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
}

// Fonction assert
function assert(condition, message) {
    if (!condition) {
        throw new Error(`Assertion failed: ${message}`);
    }
}

// Export
window.ApiClient = ApiClient;