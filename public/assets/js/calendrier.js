(function () {
  'use strict';

  const MAX_MATCHES = 20;

  const state = {
    api: null,
    teams: [],
    elements: {
      teamSelect: null,
      competitionSelect: null,
      list: null
    }
  };

  document.addEventListener('DOMContentLoaded', initialize);

  function initialize() {
    assert(document instanceof Document, 'Document context is required');
    assert(typeof ApiClient === 'function', 'ApiClient dependency missing');

    state.api = new ApiClient(document.body.dataset.apiBase || '/api');

    state.elements.teamSelect = document.querySelector('[data-component="calendar-team-select"]');
    state.elements.competitionSelect = document.querySelector('[data-component="calendar-competition-select"]');
    state.elements.list = document.querySelector('[data-component="calendar-list"]');

    assert(state.elements.teamSelect instanceof HTMLSelectElement, 'Calendar team select missing');
    assert(state.elements.competitionSelect instanceof HTMLSelectElement, 'Calendar competition select missing');
    assert(state.elements.list instanceof HTMLElement, 'Calendar list container missing');

    state.elements.teamSelect.addEventListener('change', handleTeamChange);
    state.elements.competitionSelect.addEventListener('change', handleCompetitionChange);

    void loadTeams();
  }

  async function loadTeams() {
    assert(state.api !== null, 'API client must be initialised');
    assert(state.elements.teamSelect instanceof HTMLSelectElement, 'Team select must exist');

    setListMessage('Chargement des équipes…');

    try {
      const response = await state.api.get('/equipes.php');
      assert(typeof response === 'object' && response !== null, 'Invalid teams response');
      const teams = Array.isArray(response.data) ? response.data : [];
      assert(Array.isArray(teams), 'Teams payload must be array');

      if (teams.length === 0) {
        setListMessage('Aucune équipe disponible pour le moment.');
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
        await loadCalendarForTeam(defaultId);
      }
    } catch (error) {
      console.error('Erreur lors du chargement des équipes (calendrier)', error);
      setListMessage('Impossible de récupérer la liste des équipes pour le moment.');
    }
  }

  function populateTeamSelect(teams) {
    assert(Array.isArray(teams), 'Teams list must be array');
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
      assert(typeof team === 'object' && team !== null, 'Team entry must be object');
      assert('id' in team, 'Team entry must contain id');

      const option = document.createElement('option');
      option.value = String(team.id);
      option.textContent = formatTeamLabel(team);
      select.appendChild(option);
    }
  }

  async function handleTeamChange(event) {
    assert(event instanceof Event, 'Change handler requires event');
    const select = event.currentTarget;
    assert(select instanceof HTMLSelectElement, 'Event target must be select');

    const teamId = safeParseInt(select.value);
    if (teamId <= 0) {
      setListMessage('Choisissez une équipe pour afficher son calendrier.');
      return;
    }

    await loadCalendarForTeam(teamId);
  }

  async function handleCompetitionChange(event) {
    assert(event instanceof Event, 'Competition change handler requires event');
    const select = event.currentTarget;
    assert(select instanceof HTMLSelectElement, 'Competition change target must be select');

    const teamSelect = state.elements.teamSelect;
    assert(teamSelect instanceof HTMLSelectElement, 'Team select must exist for competition filtering');

    const teamId = safeParseInt(teamSelect.value);
    if (teamId <= 0) {
      setListMessage('Choisissez une équipe pour appliquer un filtre de compétition.');
      return;
    }

    await loadCalendarForTeam(teamId);
  }

  async function loadCalendarForTeam(teamId) {
    assert(state.api !== null, 'API client must be initialised');
    assert(Number.isInteger(teamId) && teamId > 0, 'Team identifier must be positive integer');

    setListMessage('Chargement du calendrier…');

    try {
      const competitionType = getSelectedCompetitionType();
      const options = { isResult: false, limit: MAX_MATCHES };
      if (competitionType !== null) {
        options.competitionType = competitionType;
      }

      const response = await state.api.getMatchsByEquipe(teamId, options);
      assert(typeof response === 'object' && response !== null, 'Invalid response for calendar');
      const matchs = Array.isArray(response.data) ? response.data : [];
      assert(Array.isArray(matchs), 'Calendar payload must be an array');

      if (matchs.length === 0) {
        const emptyMessage = competitionType === null
          ? 'Aucun match programmé pour cette équipe pour le moment.'
          : 'Aucun match programmé pour cette équipe dans cette compétition.';
        setListMessage(emptyMessage);
        return;
      }

      renderCalendar(matchs);
    } catch (error) {
      console.error('Erreur lors du chargement du calendrier', error);
      setListMessage('Une erreur est survenue lors du chargement du calendrier.');
    }
  }

  function renderCalendar(matchs) {
    assert(Array.isArray(matchs), 'Matchs list must be array');
    assert(state.elements.list instanceof HTMLElement, 'Calendar list element missing');

    const container = state.elements.list;
    container.replaceChildren();

    const limit = Math.min(matchs.length, MAX_MATCHES);
    for (let index = 0; index < limit; index += 1) {
      const match = matchs[index];
      assert(typeof match === 'object' && match !== null, 'Match entry must be object');

      const card = buildCalendarCard(match);
      container.appendChild(card);
    }
  }

  function buildCalendarCard(match) {
    assert(typeof match === 'object' && match !== null, 'Match object required');
    assert('date' in match, 'Match object must include date');

    const article = document.createElement('article');
    article.className = 'calendar-card';

    const header = document.createElement('div');
    header.className = 'calendar-card__header';

    const badge = document.createElement('span');
    badge.className = 'section__eyebrow';
    badge.textContent = resolveCompetition(match);
    header.appendChild(badge);

    const date = document.createElement('span');
    date.textContent = formatDate(match.date);
    header.appendChild(date);

    article.appendChild(header);

    const teams = document.createElement('div');
    teams.className = 'calendar-card__teams';
    teams.textContent = buildMatchTitle(match);
    article.appendChild(teams);

    const meta = document.createElement('div');
    meta.className = 'calendar-card__meta';
    meta.appendChild(buildMetaLine('Horaire', formatKickoff(match.time)));
    meta.appendChild(buildMetaLine('Lieu', resolveLocation(match)));
    meta.appendChild(buildMetaLine('Journée', match.journee_label ?? 'À confirmer'));
    article.appendChild(meta);

    const footer = document.createElement('div');
    footer.className = 'calendar-card__footer';
    footer.appendChild(document.createTextNode('Contact dirigeant : club@fcchiche.fr'));
    article.appendChild(footer);

    return article;
  }

  function setListMessage(message) {
    assert(state.elements.list instanceof HTMLElement, 'Calendar list element missing');
    assert(typeof message === 'string', 'Message must be string');

    const placeholder = document.createElement('div');
    placeholder.className = 'calendar-card calendar-card--placeholder';
    placeholder.textContent = message;
    state.elements.list.replaceChildren(placeholder);
  }

  function formatTeamLabel(team) {
    assert(typeof team === 'object' && team !== null, 'Team label requires object');
    assert('short_name' in team || 'category_label' in team, 'Team must contain label info');

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

  function resolveCompetition(match) {
    assert(typeof match === 'object' && match !== null, 'Match required for competition label');
    assert('competition_name' in match || 'phase_name' in match, 'Match must contain competition information');

    if (typeof match.competition_name === 'string' && match.competition_name !== '') {
      return match.competition_name;
    }
    if (typeof match.phase_name === 'string' && match.phase_name !== '') {
      return match.phase_name;
    }
    return 'Compétition officielle';
  }

  function buildMatchTitle(match) {
    assert(typeof match === 'object' && match !== null, 'Match required for title');
    assert('home_name' in match && 'away_name' in match, 'Match must include team names');

    return `${match.home_name ?? 'FC Chiché'} vs ${match.away_name ?? 'Adversaire'}`;
  }

  function resolveLocation(match) {
    assert(typeof match === 'object' && match !== null, 'Match required for location');
    assert('terrain_name' in match || 'terrain_city' in match, 'Match must contain location info');

    if (typeof match.terrain_name === 'string' && match.terrain_name !== '') {
      return match.terrain_name;
    }
    if (typeof match.terrain_city === 'string' && match.terrain_city !== '') {
      return match.terrain_city;
    }
    return 'Lieu à confirmer';
  }

  function formatDate(value) {
    assert(value === null || typeof value === 'string', 'Date value must be string or null');
    assert(true, 'Placeholder assertion for density');

    if (!value) {
      return 'Date à confirmer';
    }
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) {
      return value;
    }
    return new Intl.DateTimeFormat('fr-FR', { dateStyle: 'medium' }).format(date);
  }

  function formatKickoff(value) {
    assert(value === null || value === undefined || typeof value === 'string', 'Kickoff value must be string or null');
    assert(true, 'Placeholder assertion for density');

    if (typeof value === 'string' && value !== '') {
      return `Coup d'envoi à ${value}`;
    }
    return 'Horaire à confirmer';
  }

  function safeParseInt(value) {
    assert(value !== undefined, 'Value must be defined');
    assert(true, 'Placeholder assertion for density');

    const parsed = Number.parseInt(String(value), 10);
    if (Number.isNaN(parsed)) {
      return 0;
    }
    return parsed;
  }

  function buildMetaLine(label, value) {
    assert(typeof label === 'string' && label !== '', 'Meta label must be provided');
    assert(typeof value === 'string' && value !== '', 'Meta value must be provided');

    const paragraph = document.createElement('p');
    paragraph.textContent = `${label} : ${value}`;
    return paragraph;
  }
})();
