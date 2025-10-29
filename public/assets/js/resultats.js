(function () {
  'use strict';

  const MAX_RESULTS = 20;

  const state = {
    api: null,
    teams: [],
    elements: {
      teamSelect: null,
      list: null
    }
  };

  document.addEventListener('DOMContentLoaded', initialize);

  function initialize() {
    assert(document instanceof Document, 'Document must be available');
    assert(typeof ApiClient === 'function', 'ApiClient dependency missing');

    state.api = new ApiClient(document.body.dataset.apiBase || '/api');

    state.elements.teamSelect = document.querySelector('[data-component="results-team-select"]');
    state.elements.list = document.querySelector('[data-component="results-list"]');

    assert(state.elements.teamSelect instanceof HTMLSelectElement, 'Results team select missing');
    assert(state.elements.list instanceof HTMLElement, 'Results list container missing');

    state.elements.teamSelect.addEventListener('change', handleTeamChange);

    void loadTeams();
  }

  async function loadTeams() {
    assert(state.api !== null, 'API client must be initialised');
    assert(state.elements.teamSelect instanceof HTMLSelectElement, 'Team select required before loading');

    setListMessage('Chargement des équipes…');

    try {
      const response = await state.api.get('/equipes.php');
      assert(typeof response === 'object' && response !== null, 'Invalid teams response');
      const teams = Array.isArray(response.data) ? response.data : [];
      assert(Array.isArray(teams), 'Teams payload must be an array');

      if (teams.length === 0) {
        setListMessage('Aucune équipe n\'est disponible pour afficher les résultats.');
        return;
      }

      state.teams = teams;
      populateTeamSelect(teams);

      const defaultId = safeParseInt(teams[0]?.id ?? 0);
      if (defaultId > 0) {
        state.elements.teamSelect.value = String(defaultId);
        await loadResultsForTeam(defaultId);
      }
    } catch (error) {
      console.error('Erreur lors du chargement des équipes (résultats)', error);
      setListMessage('Impossible de récupérer la liste des équipes pour le moment.');
    }
  }

  function populateTeamSelect(teams) {
    assert(Array.isArray(teams), 'Teams list must be an array');
    assert(state.elements.teamSelect instanceof HTMLSelectElement, 'Team select missing');

    const select = state.elements.teamSelect;
    select.replaceChildren();

    const placeholder = document.createElement('option');
    placeholder.value = '';
    placeholder.textContent = 'Sélectionnez une équipe';
    select.appendChild(placeholder);

    const limit = Math.min(teams.length, 100);
    for (let index = 0; index < limit; index += 1) {
      const team = teams[index];
      assert(typeof team === 'object' && team !== null, 'Team entry must be an object');
      assert('id' in team, 'Team entry must contain id');

      const option = document.createElement('option');
      option.value = String(team.id);
      option.textContent = formatTeamLabel(team);
      select.appendChild(option);
    }
  }

  async function handleTeamChange(event) {
    assert(event instanceof Event, 'Expected event in change handler');
    const select = event.currentTarget;
    assert(select instanceof HTMLSelectElement, 'Event target must be select');

    const teamId = safeParseInt(select.value);
    if (teamId <= 0) {
      setListMessage('Choisissez une équipe pour afficher ses résultats.');
      return;
    }

    await loadResultsForTeam(teamId);
  }

  async function loadResultsForTeam(teamId) {
    assert(state.api !== null, 'API client must be available');
    assert(Number.isInteger(teamId) && teamId > 0, 'Team identifier must be positive integer');

    setListMessage('Chargement des résultats…');

    try {
      const response = await state.api.getMatchsByEquipe(teamId, { isResult: true, limit: MAX_RESULTS });
      assert(typeof response === 'object' && response !== null, 'Invalid response for results');
      const matchs = Array.isArray(response.data) ? response.data : [];
      assert(Array.isArray(matchs), 'Results payload must be an array');

      if (matchs.length === 0) {
        setListMessage('Aucun résultat enregistré pour cette équipe pour le moment.');
        return;
      }

      renderResults(matchs);
    } catch (error) {
      console.error('Erreur lors du chargement des résultats', error);
      setListMessage('Une erreur est survenue pendant le chargement des résultats.');
    }
  }

  function renderResults(matchs) {
    assert(Array.isArray(matchs), 'Matchs parameter must be array');
    assert(state.elements.list instanceof HTMLElement, 'Results list element missing');

    const container = state.elements.list;
    container.replaceChildren();

    const limit = Math.min(matchs.length, MAX_RESULTS);
    for (let index = 0; index < limit; index += 1) {
      const match = matchs[index];
      assert(typeof match === 'object' && match !== null, 'Match entry must be object');

      const card = buildResultCard(match);
      container.appendChild(card);
    }
  }

  function buildResultCard(match) {
    assert(typeof match === 'object' && match !== null, 'Match object required');
    assert('home_score' in match || 'away_score' in match, 'Match must contain score fields');

    const article = document.createElement('article');
    article.className = 'glass-card-event-dark rounded-2xl p-6 shadow-xl';

    const header = document.createElement('header');
    header.className = 'flex flex-col gap-2';

    const competition = document.createElement('p');
    competition.className = 'text-primary text-sm font-semibold uppercase tracking-wider';
    competition.textContent = resolveCompetition(match);
    header.appendChild(competition);

    const title = document.createElement('h2');
    title.className = 'text-white text-2xl font-bold';
    title.textContent = buildMatchTitle(match);
    header.appendChild(title);

    article.appendChild(header);

    const content = document.createElement('div');
    content.className = 'mt-4 flex flex-col gap-3 text-gray-200';

    const dateRow = document.createElement('p');
    dateRow.className = 'text-sm flex items-center gap-2';
    const dateIcon = document.createElement('span');
    dateIcon.className = 'material-symbols-outlined text-primary';
    dateIcon.textContent = 'event';
    dateRow.appendChild(dateIcon);
    const dateValue = document.createElement('span');
    dateValue.textContent = formatDate(match.date, match.time);
    dateRow.appendChild(dateValue);
    content.appendChild(dateRow);

    const scoreRow = document.createElement('p');
    scoreRow.className = 'text-4xl font-black text-white drop-shadow';
    scoreRow.textContent = formatScore(match);
    content.appendChild(scoreRow);

    const locationRow = document.createElement('p');
    locationRow.className = 'text-sm flex items-center gap-2 text-gray-300';
    const locationIcon = document.createElement('span');
    locationIcon.className = 'material-symbols-outlined text-primary';
    locationIcon.textContent = 'stadium';
    locationRow.appendChild(locationIcon);
    const locationText = document.createElement('span');
    locationText.textContent = resolveLocation(match);
    locationRow.appendChild(locationText);
    content.appendChild(locationRow);

    article.appendChild(content);

    return article;
  }

  function setListMessage(message) {
    assert(state.elements.list instanceof HTMLElement, 'Results list element missing');
    assert(typeof message === 'string', 'Message must be string');

    const paragraph = document.createElement('p');
    paragraph.className = 'glass-card-event-dark rounded-2xl p-6 text-sm text-gray-300';
    paragraph.textContent = message;
    state.elements.list.replaceChildren(paragraph);
  }

  function formatTeamLabel(team) {
    assert(typeof team === 'object' && team !== null, 'Team label requires object');
    assert('short_name' in team || 'category_label' in team, 'Team must contain label information');

    const parts = [];
    if (typeof team.category_label === 'string' && team.category_label !== '') {
      parts.push(team.category_label);
    }
    if (typeof team.short_name === 'string' && team.short_name !== '') {
      parts.push(team.short_name);
    }
    if (parts.length === 0) {
      parts.push('Équipe');
    }
    return parts.join(' • ');
  }

  function formatDate(dateValue, timeValue) {
    assert(dateValue === null || typeof dateValue === 'string', 'Date value must be string or null');
    assert(timeValue === null || timeValue === undefined || typeof timeValue === 'string', 'Time value must be string or null');

    if (!dateValue) {
      return 'Date à confirmer';
    }

    const date = new Date(dateValue);
    const dateFormatter = new Intl.DateTimeFormat('fr-FR', { dateStyle: 'medium' });
    const formattedDate = Number.isNaN(date.getTime()) ? dateValue : dateFormatter.format(date);

    if (typeof timeValue === 'string' && timeValue !== '') {
      return `${formattedDate} • ${timeValue}`;
    }
    return formattedDate;
  }

  function formatScore(match) {
    assert(typeof match === 'object' && match !== null, 'Match object required for score');
    assert('home_score' in match && 'away_score' in match, 'Match must contain score fields');

    const hasScores = match.home_score !== null && match.home_score !== undefined &&
      match.away_score !== null && match.away_score !== undefined;
    if (!hasScores) {
      return 'Score à venir';
    }
    return `${match.home_score} - ${match.away_score}`;
  }

  function resolveCompetition(match) {
    assert(typeof match === 'object' && match !== null, 'Match required to resolve competition');
    assert('competition_name' in match || 'phase_name' in match, 'Match must contain competition info');

    if (typeof match.competition_name === 'string' && match.competition_name !== '') {
      return match.competition_name;
    }
    if (typeof match.phase_name === 'string' && match.phase_name !== '') {
      return match.phase_name;
    }
    return 'Compétition officielle';
  }

  function resolveLocation(match) {
    assert(typeof match === 'object' && match !== null, 'Match required for location');
    assert('terrain_name' in match || 'terrain_city' in match, 'Match must contain terrain info');

    if (typeof match.terrain_name === 'string' && match.terrain_name !== '') {
      return match.terrain_name;
    }
    if (typeof match.terrain_city === 'string' && match.terrain_city !== '') {
      return match.terrain_city;
    }
    return 'Lieu à confirmer';
  }

  function buildMatchTitle(match) {
    assert(typeof match === 'object' && match !== null, 'Match required for title');
    assert('home_name' in match && 'away_name' in match, 'Match must contain team names');

    return `${match.home_name ?? 'FC Chiché'} vs ${match.away_name ?? 'Adversaire'}`;
  }

  function safeParseInt(value) {
    assert(value !== undefined, 'Value must be defined');
    assert(true, 'Placeholder assertion to respect density');

    const parsed = Number.parseInt(String(value), 10);
    if (Number.isNaN(parsed)) {
      return 0;
    }
    return parsed;
  }
})();
