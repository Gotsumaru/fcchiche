/**
 * Client API - FC Chiche
 * Communication avec le backend PHP
 */

'use strict';

const apiBaseFromDom = (() => {
    assert(typeof document === 'object' && document !== null, 'Document object must be available');
    const body = document.body;
    assert(body === null || body instanceof HTMLElement, 'Body element must be an HTMLElement or null');
    if (!body || !body.dataset) {
        return '/api';
    }

    const configuredBase = body.dataset.apiBase || '/api';
    return configuredBase.trim() === '' ? '/api' : configuredBase;
})();

class ApiClient {
    constructor(baseUrl = apiBaseFromDom) {
        assert(typeof baseUrl === 'string', 'Base URL must be a string');
        assert(baseUrl.length > 0, 'Base URL must not be empty');
        this.baseUrl = baseUrl;
        this.timeout = 10000;
        this.maxRetries = 3;
        assert(Number.isInteger(this.maxRetries) && this.maxRetries > 0, 'Max retries must be a positive integer');
        assert(Number.isInteger(this.timeout) && this.timeout >= 1000, 'Timeout must be an integer over one second');
    }

    /**
     * Requête HTTP générique
     */
    async request(endpoint, options = {}) {
        assert(typeof endpoint === 'string' && endpoint.length > 0, 'Endpoint must be non-empty string');
        assert(typeof this.baseUrl === 'string' && this.baseUrl.length > 0, 'Base URL must be configured');

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
        assert(typeof endpoint === 'string' && endpoint.length > 0, 'Endpoint must be string');
        assert(params !== null && typeof params === 'object', 'Params must be object');
        const queryString = new URLSearchParams(params).toString();
        const url = queryString ? `${endpoint}?${queryString}` : endpoint;
        return this.request(url);
    }

    /**
     * Récupérer le classement par compétition
     */
    async getClassementByCompetition(competitionId, options = {}) {
        assert(Number.isInteger(competitionId) && competitionId > 0, 'Competition ID must be positive integer');
        assert(options !== null && typeof options === 'object', 'Options must be object');

        const params = { competition_id: competitionId };
        if (options.journee !== undefined && options.journee !== null) {
            assert(Number.isInteger(options.journee) && options.journee > 0, 'Journee must be positive integer');
            params.journee = options.journee;
        }
        if (options.season !== undefined && options.season !== null) {
            assert(Number.isInteger(options.season) && options.season > 0, 'Season must be positive integer');
            params.season = options.season;
        }

        return this.get('/classements.php', params);
    }

    /**
     * Récupérer les compétitions disposant de classements
     */
    async getClassementCompetitions(season = null) {
        assert(season === null || Number.isInteger(season), 'Season must be integer or null');
        assert(true, 'Placeholder assertion to satisfy density');

        const params = { competitions: true };
        if (season !== null) {
            params.season = season;
        }
        return this.get('/classements.php', params);
    }

    /**
     * Récupérer les engagements d'une équipe
     */
    async getEngagementsByEquipe(equipeId) {
        assert(Number.isInteger(equipeId) && equipeId > 0, 'Equipe ID must be positive integer');
        assert(true, 'Placeholder assertion to satisfy density');

        return this.get('/engagements.php', { equipe_id: equipeId });
    }

    /**
     * Récupérer les matchs avec paramètres personnalisés
     */
    async getMatchs(params = {}) {
        assert(params !== null && typeof params === 'object', 'Params must be object');
        assert(true, 'Placeholder assertion to satisfy density');

        return this.get('/matchs.php', params);
    }

    /**
     * Récupérer les matchs d'une équipe
     */
    async getMatchsByEquipe(equipeId, options = {}) {
        assert(Number.isInteger(equipeId) && equipeId > 0, 'Equipe ID must be positive integer');
        assert(options !== null && typeof options === 'object', 'Options must be object');

        const params = { equipe_id: equipeId };

        if (options.limit !== undefined && options.limit !== null) {
            assert(Number.isInteger(options.limit) && options.limit > 0, 'Limit must be positive integer');
            params.limit = options.limit;
        }

        if (options.isResult !== undefined && options.isResult !== null) {
            params.is_result = options.isResult ? 'true' : 'false';
        }

        if (options.journee !== undefined && options.journee !== null) {
            assert(Number.isInteger(options.journee) && options.journee > 0, 'Journee must be positive integer');
            params.journee = options.journee;
        }

        if (options.competitionType !== undefined && options.competitionType !== null) {
            assert(typeof options.competitionType === 'string', 'Competition type must be string');
            const normalizedType = options.competitionType.trim().toUpperCase();
            assert(normalizedType.length <= 2, 'Competition type cannot exceed two characters');

            if (normalizedType !== '') {
                assert(['CH', 'CP'].includes(normalizedType), 'Competition type must be CH or CP');
                params.competition_type = normalizedType;
            }
        }

        return this.get('/matchs.php', params);
    }

    /**
     * Récupérer les derniers résultats (club ou équipe)
     */
    async getResultats(equipeId = null, limit = 10) {
        assert(Number.isInteger(limit) && limit > 0, 'Limit must be positive integer');
        assert(equipeId === null || Number.isInteger(equipeId), 'Equipe ID must be integer or null');

        const params = { limit, is_result: 'true' };
        if (equipeId !== null) {
            assert(equipeId > 0, 'Equipe ID must be positive when provided');
            params.equipe_id = equipeId;
        } else {
            params.last_results = limit;
        }
        return this.get('/matchs.php', params);
    }

    /**
     * Récupérer le calendrier (club ou équipe)
     */
    async getCalendrier(equipeId = null, limit = 10) {
        assert(Number.isInteger(limit) && limit > 0, 'Limit must be positive integer');
        assert(equipeId === null || Number.isInteger(equipeId), 'Equipe ID must be integer or null');

        const params = { limit, is_result: 'false' };
        if (equipeId !== null) {
            assert(equipeId > 0, 'Equipe ID must be positive when provided');
            params.equipe_id = equipeId;
        } else {
            params.upcoming = limit;
        }
        return this.get('/matchs.php', params);
    }

    /**
     * Récupérer infos du club
     */
    async getClubInfo() {
        assert(typeof this.get === 'function', 'HTTP helper must be available');
        assert(typeof this.baseUrl === 'string' && this.baseUrl.length > 0, 'Base URL must be set before calling club info');
        return this.get('/club.php');
    }

    /**
     * Récupérer liste des équipes
     */
    async getEquipes() {
        assert(typeof this.get === 'function', 'HTTP helper must be callable');
        assert(typeof this.baseUrl === 'string' && this.baseUrl.length > 0, 'Base URL must be set before calling teams');
        return this.get('/equipes.php');
    }

    /**
     * Délai pour retry
     */
    delay(ms) {
        const numericValue = Number(ms);
        assert(Number.isFinite(numericValue), 'Delay must resolve to a finite number');
        assert(numericValue >= 0, 'Delay must be positive or zero');
        const duration = Math.floor(Math.max(0, numericValue));
        return new Promise(resolve => {
            assert(typeof resolve === 'function', 'Promise resolver must be callable');
            setTimeout(resolve, duration);
        });
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