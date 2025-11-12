/**
 * API Client - Communicates with PHP backend
 *
 * In development: Uses mock data for testing UI
 * In production: Uses PHP API on same domain (/api)
 */

import { mockMatches } from './mockData'

// Configuration API
const API_BASE = '/api'

// Décider d'utiliser les mock data:
// - En production: toujours utiliser l'API réelle
// - En dev: utiliser la variable VITE_USE_MOCK_DATA (default: true pour UI testing rapide)
// - Si VITE_USE_REAL_API=true: forcer l'API réelle même en dev
const USE_MOCK_DATA = import.meta.env.DEV && import.meta.env.VITE_USE_MOCK_DATA !== 'false'

class ApiClient {
  async request(endpoint, options = {}) {
    const url = `${API_BASE}${endpoint}`
    const response = await fetch(url, {
      headers: {
        'Content-Type': 'application/json',
        ...options.headers,
      },
      ...options,
    })

    if (!response.ok) {
      throw new Error(`API Error: ${response.status} ${response.statusText}`)
    }

    return response.json()
  }

  // Match data
  async getUpcomingMatches(limit = 6) {
    if (USE_MOCK_DATA) {
      return new Promise(resolve => {
        setTimeout(() => resolve(mockMatches.upcoming.slice(0, limit)), 300)
      })
    }
    const result = await this.request('/matchs.php?upcoming=' + limit)
    const data = result.data || []
    // Map PHP field names to React component expectations
    return data.map(match => ({
      id: match.id,
      date: match.date_time,
      home: match.home_name,
      away: match.away_name,
      competition: match.competition_name,
      location: match.terrain_name,
      ...match // Include all original fields for flexibility
    }))
  }

  async getLatestResults(limit = 6) {
    if (USE_MOCK_DATA) {
      return new Promise(resolve => {
        setTimeout(() => resolve(mockMatches.results.slice(0, limit)), 300)
      })
    }
    const result = await this.request('/matchs.php?last_results=' + limit)
    const data = result.data || []
    // Map PHP field names to React component expectations
    return data.map(match => ({
      id: match.id,
      date: match.date_time,
      home: match.home_name,
      away: match.away_name,
      score: match.home_score !== null && match.away_score !== null
        ? `${match.home_score}-${match.away_score}`
        : 'N/A',
      competition: match.competition_name,
      location: match.terrain_name,
      ...match // Include all original fields for flexibility
    }))
  }

  async getMatches(params = {}) {
    const query = new URLSearchParams(params).toString()
    const result = await this.request(`/matchs.php?${query}`)
    return result.data || []
  }

  // Teams data
  async getTeams() {
    const result = await this.request('/equipes.php')
    return result.data || []
  }

  // Standings data
  async getStandings(competitionId) {
    const result = await this.request(`/classements.php?competition_id=${competitionId}`)
    return result.data || []
  }

  async getCompetitions() {
    const result = await this.request('/classements.php?competitions=true')
    return result.data || []
  }
}

export default new ApiClient()
