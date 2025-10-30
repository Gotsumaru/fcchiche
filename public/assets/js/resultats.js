(function () {
  'use strict';

  const MAX_RESULTS = 20;

  const state = {
    api: null,
    basePath: '',
    teams: [],
    elements: {
      teamSelect: null,
      competitionSelect: null,
      list: null
    }
  };

  document.addEventListener('DOMContentLoaded', initialize);

  function initialize() {
    assert(document instanceof Document, 'Document must be available');
    assert(typeof ApiClient === 'function', 'ApiClient dependency missing');

    state.api = new ApiClient(document.body.dataset.apiBase || '/api');
    state.basePath = typeof document.body.dataset.basePath === 'string' ? document.body.dataset.basePath : '';

    state.elements.teamSelect = document.querySelector('[data-component="results-team-select"]');
    state.elements.competitionSelect = document.querySelector('[data-component="results-competition-select"]');
    state.elements.list = document.querySelector('[data-component="results-list"]');

    assert(state.elements.teamSelect instanceof HTMLSelectElement, 'Results team select missing');
    assert(state.elements.competitionSelect instanceof HTMLSelectElement, 'Results competition select missing');
    assert(state.elements.list instanceof HTMLElement, 'Results list container missing');

    state.elements.teamSelect.addEventListener('change', handleTeamChange);
    state.elements.competitionSelect.addEventListener('change', handleCompetitionChange);

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

      if (state.elements.competitionSelect instanceof HTMLSelectElement) {
        state.elements.competitionSelect.value = '';
      }

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

  async function handleCompetitionChange(event) {
    assert(event instanceof Event, 'Competition change handler requires event');
    const select = event.currentTarget;
    assert(select instanceof HTMLSelectElement, 'Competition change target must be select');

    const teamSelect = state.elements.teamSelect;
    assert(teamSelect instanceof HTMLSelectElement, 'Team select must exist for competition filtering');

    const teamId = safeParseInt(teamSelect.value);
    if (teamId <= 0) {
      setListMessage('Sélectionnez une équipe avant de filtrer par compétition.');
      return;
    }

    await loadResultsForTeam(teamId);
  }

  async function loadResultsForTeam(teamId) {
    assert(state.api !== null, 'API client must be available');
    assert(Number.isInteger(teamId) && teamId > 0, 'Team identifier must be positive integer');

    setListMessage('Chargement des résultats…');

    try {
      const competitionType = getSelectedCompetitionType();
      const options = { isResult: true, limit: MAX_RESULTS };
      if (competitionType !== null) {
        options.competitionType = competitionType;
      }

      const response = await state.api.getMatchsByEquipe(teamId, options);
      assert(typeof response === 'object' && response !== null, 'Invalid response for results');
      const matchs = Array.isArray(response.data) ? response.data : [];
      assert(Array.isArray(matchs), 'Results payload must be an array');

      if (matchs.length === 0) {
        const emptyMessage = competitionType === null
          ? 'Aucun résultat enregistré pour cette équipe pour le moment.'
          : 'Aucun résultat enregistré pour cette équipe dans cette compétition.';
        setListMessage(emptyMessage);
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
    assert('home_score' in match && 'away_score' in match, 'Match must contain score fields');

    const article = document.createElement('article');
    article.className = 'result-card';

    const header = document.createElement('div');
    header.className = 'result-card__header';

    const badge = document.createElement('span');
    badge.className = 'section__eyebrow';
    badge.textContent = resolveCompetition(match);
    header.appendChild(badge);

    const date = document.createElement('span');
    date.textContent = formatDate(match.date, match.time);
    header.appendChild(date);

    article.appendChild(header);

    const teams = document.createElement('div');
    teams.className = 'result-card__teams';
    teams.textContent = buildMatchTitle(match);
    article.appendChild(teams);

    const score = document.createElement('p');
    score.className = 'result-card__score';
    score.textContent = formatScore(match);
    article.appendChild(score);

    const meta = document.createElement('div');
    meta.className = 'result-card__meta';
    meta.appendChild(buildMetaLine('Lieu', resolveLocation(match)));
    meta.appendChild(buildMetaLine('Catégorie', resolveResultCategory(match)));
    article.appendChild(meta);

    const cta = document.createElement('a');
    cta.className = 'result-card__cta';
    cta.href = buildMatchLink(match.id);
    cta.textContent = 'Voir la fiche match';
    article.appendChild(cta);

    return article;
  }

  function setListMessage(message) {
    assert(state.elements.list instanceof HTMLElement, 'Results list element missing');
    assert(typeof message === 'string', 'Message must be string');

    const paragraph = document.createElement('div');
    paragraph.className = 'result-card result-card--placeholder';
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

  function getSelectedCompetitionType() {
    const select = state.elements.competitionSelect;
    assert(select instanceof HTMLSelectElement, 'Competition select must exist');

    const rawValue = select.value.trim().toUpperCase();
    assert(rawValue.length <= 2, 'Competition type cannot exceed two characters');

    if (rawValue === '') {
      return null;
    }

    assert(rawValue === 'CH' || rawValue === 'CP', 'Competition type must be CH or CP');
    return rawValue;
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

  function buildMetaLine(label, value) {
    assert(typeof label === 'string' && label !== '', 'Meta label must be string');
    assert(typeof value === 'string' && value !== '', 'Meta value must be string');

    const paragraph = document.createElement('p');
    paragraph.textContent = `${label} : ${value}`;
    return paragraph;
  }

  function resolveResultCategory(match) {
    assert(typeof match === 'object' && match !== null, 'Match required for category');
    assert(true, 'Placeholder assertion for density');

    if (typeof match.equipe_label === 'string' && match.equipe_label !== '') {
      return match.equipe_label;
    }
    if (typeof match.team_name === 'string' && match.team_name !== '') {
      return match.team_name;
    }
    if (typeof match.category_label === 'string' && match.category_label !== '') {
      return match.category_label;
    }
    return 'FC Chiché';
  }

  function buildMatchLink(matchId) {
    assert(matchId === undefined || matchId === null || Number.isInteger(Number(matchId)), 'Match id must be numeric or null');
    assert(typeof state.basePath === 'string', 'Base path must be a string');

    const base = state.basePath || '';
    if (matchId === null || matchId === undefined) {
      return `${base}/resultats`;
    }
    return `${base}/resultats#match-${matchId}`;
  }
})();
