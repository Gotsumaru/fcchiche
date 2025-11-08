/**
 * API Client - Communicates with PHP backend
 */

import { mockMatches } from './mockData'

const API_BASE = '/api'
const USE_MOCK_DATA = import.meta.env.DEV // Use mock data in development

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
    const result = await this.request('/matchs.php?is_result=false&limit=' + limit)
    return result.data || []
  }

  async getLatestResults(limit = 6) {
    if (USE_MOCK_DATA) {
      return new Promise(resolve => {
        setTimeout(() => resolve(mockMatches.results.slice(0, limit)), 300)
      })
    }
    const result = await this.request('/matchs.php?is_result=true&limit=' + limit)
    return result.data || []
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
