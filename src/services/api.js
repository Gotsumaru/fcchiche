/**
 * API Client - Intégration avec les APIs PHP existantes
 */

const API_BASE = '/api';

export const apiClient = {
  async getMatchs() {
    try {
      const response = await fetch(`${API_BASE}/matchs`);
      if (!response.ok) throw new Error('Erreur API matchs');
      return await response.json();
    } catch (error) {
      console.error('Erreur getMatchs:', error);
      return { success: false, data: [] };
    }
  },

  async getClassements() {
    try {
      const response = await fetch(`${API_BASE}/classements`);
      if (!response.ok) throw new Error('Erreur API classements');
      return await response.json();
    } catch (error) {
      console.error('Erreur getClassements:', error);
      return { success: false, data: [] };
    }
  },

  async getEquipes() {
    try {
      const response = await fetch(`${API_BASE}/equipes`);
      if (!response.ok) throw new Error('Erreur API equipes');
      return await response.json();
    } catch (error) {
      console.error('Erreur getEquipes:', error);
      return { success: false, data: [] };
    }
  },

  async getCompetitions() {
    try {
      const response = await fetch(`${API_BASE}/competitions`);
      if (!response.ok) throw new Error('Erreur API competitions');
      return await response.json();
    } catch (error) {
      console.error('Erreur getCompetitions:', error);
      return { success: false, data: [] };
    }
  },

  async getClub() {
    try {
      const response = await fetch(`${API_BASE}/club`);
      if (!response.ok) throw new Error('Erreur API club');
      return await response.json();
    } catch (error) {
      console.error('Erreur getClub:', error);
      return { success: false, data: {} };
    }
  },
};

export default apiClient;
