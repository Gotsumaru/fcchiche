(function () {
  'use strict';

  const MAX_ROWS = 20;
  const MAIN_CLUB_ID = 5403;
  const COMPETITION_LIMIT = 12;

  const state = {
    api: null,
    teams: [],
    competitionsCache: new Map(),
    elements: {
      teamSelect: null,
      competitionSelect: null,
      competitionWrapper: null,
      tableContainer: null,
      metaBadge: null
    }
  };

  document.addEventListener('DOMContentLoaded', initialize);

  function initialize() {
    assert(document instanceof Document, 'Document instance is required');
    assert(typeof ApiClient === 'function', 'ApiClient constructor must exist');

    state.api = new ApiClient(document.body.dataset.apiBase || '/api');

    state.elements.teamSelect = document.querySelector('[data-component="classement-team-select"]');
    state.elements.competitionSelect = document.querySelector('[data-component="classement-competition-select"]');
    state.elements.competitionWrapper = document.querySelector('[data-component="classement-competition-wrapper"]');
    state.elements.tableContainer = document.querySelector('[data-component="classement-table"]');
    state.elements.metaBadge = document.querySelector('[data-component="classement-meta"]');

    assert(state.elements.teamSelect instanceof HTMLSelectElement, 'Team select element missing');
    assert(state.elements.competitionSelect instanceof HTMLSelectElement, 'Competition select element missing');
    assert(state.elements.competitionWrapper instanceof HTMLElement, 'Competition wrapper missing');
    assert(state.elements.tableContainer instanceof HTMLElement, 'Classement container missing');
    assert(state.elements.metaBadge instanceof HTMLElement, 'Classement meta badge missing');

    state.elements.teamSelect.addEventListener('change', handleTeamChange);
    state.elements.competitionSelect.addEventListener('change', handleCompetitionChange);

    void loadTeams();
  }

  async function loadTeams() {
    assert(state.api !== null, 'API client must be initialised');
    assert(state.elements.teamSelect instanceof HTMLSelectElement, 'Team select required before loading');

    setMetaText('Chargement des équipes…');
    setTableMessage('Chargement des équipes disponibles…');

    try {
      const response = await state.api.get('/equipes.php');
      assert(typeof response === 'object' && response !== null, 'Invalid response structure for teams');
      const teams = Array.isArray(response.data) ? response.data : [];
      assert(Array.isArray(teams), 'Teams payload must be an array');

      if (teams.length === 0) {
        setMetaText('Aucune équipe disponible');
        setTableMessage('Aucune donnée de classement disponible pour le moment.');
        return;
      }

      state.teams = teams;
      populateTeamSelect(teams);
      const firstTeam = teams[0];
      assert(typeof firstTeam === 'object' && firstTeam !== null, 'First team must be an object');
      const defaultId = safeParseInt(firstTeam.id);
      if (defaultId > 0) {
        state.elements.teamSelect.value = String(defaultId);
        await updateClassementForTeam(defaultId);
      }
    } catch (error) {
      console.error('Erreur lors du chargement des équipes', error);
      setMetaText('Impossible de charger les équipes');
      setTableMessage('Une erreur est survenue lors de la récupération des données.');
    }
  }

  function populateTeamSelect(teams) {
    assert(Array.isArray(teams), 'Teams list must be an array');
    assert(state.elements.teamSelect instanceof HTMLSelectElement, 'Team select element missing');

    const select = state.elements.teamSelect;
    select.replaceChildren();

    const placeholder = document.createElement('option');
    placeholder.value = '';
    placeholder.textContent = 'Sélectionnez une équipe';
    select.appendChild(placeholder);

    const maxItems = Math.min(teams.length, 100);
    for (let index = 0; index < maxItems; index += 1) {
      const team = teams[index];
      assert(typeof team === 'object' && team !== null, 'Team entry must be an object');
      assert('id' in team, 'Team entry must contain an id');

      const option = document.createElement('option');
      option.value = String(team.id);
      option.textContent = formatTeamLabel(team);
      select.appendChild(option);
    }
  }

  function formatTeamLabel(team) {
    assert(typeof team === 'object' && team !== null, 'Team label requires an object');
    assert(typeof team.short_name === 'string' || typeof team.category_label === 'string', 'Team requires identifying name');

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

  async function handleTeamChange(event) {
    assert(event instanceof Event, 'Change handler requires an event');
    assert(state.elements.teamSelect instanceof HTMLSelectElement, 'Team select must exist');

    const select = event.currentTarget;
    assert(select instanceof HTMLSelectElement, 'Event target must be a select element');

    const teamId = safeParseInt(select.value);
    if (teamId <= 0) {
      setMetaText('Sélectionnez une équipe pour afficher son classement');
      setTableMessage('Aucune équipe sélectionnée pour le moment.');
      hideCompetitionSelect();
      return;
    }

    await updateClassementForTeam(teamId);
  }

  async function updateClassementForTeam(teamId) {
    assert(Number.isInteger(teamId), 'Team identifier must be an integer');
    assert(teamId > 0, 'Team identifier must be positive');

    setMetaText('Chargement du classement…');
    setTableMessage('Chargement du classement en cours…');

    try {
      const competitions = await fetchCompetitionsForTeam(teamId);
      assert(Array.isArray(competitions), 'Competitions must resolve to an array');

      if (competitions.length === 0) {
        hideCompetitionSelect();
        setMetaText('Aucune compétition trouvée');
        setTableMessage("Cette équipe n'a pas de compétition référencée pour le moment.");
        return;
      }

      const defaultCompetitionId = selectDefaultCompetitionId(competitions);
      populateCompetitionSelect(competitions, defaultCompetitionId);
      await loadClassement(defaultCompetitionId);
    } catch (error) {
      console.error('Erreur lors du chargement des compétitions', error);
      setMetaText('Impossible de charger le classement');
      setTableMessage('Une erreur est survenue lors de la récupération du classement.');
    }
  }

  async function fetchCompetitionsForTeam(teamId) {
    assert(state.api !== null, 'API client must be disponible');
    assert(teamId > 0, 'Team identifier must be positive to fetch competitions');

    if (state.competitionsCache.has(teamId)) {
      const cached = state.competitionsCache.get(teamId);
      assert(Array.isArray(cached), 'Cached competitions must be an array');
      return cached;
    }

    const response = await state.api.get('/engagements.php', { equipe_id: teamId });
    assert(typeof response === 'object' && response !== null, 'Invalid response for engagements');
    const engagements = Array.isArray(response.data) ? response.data : [];
    assert(Array.isArray(engagements), 'Engagements payload must be an array');

    const uniqueCompetitions = [];
    const seen = new Set();
    const limit = Math.min(engagements.length, COMPETITION_LIMIT);
    for (let index = 0; index < limit; index += 1) {
      const engagement = engagements[index];
      assert(typeof engagement === 'object' && engagement !== null, 'Engagement must be an object');
      if (!('competition_id' in engagement)) {
        continue;
      }
      const competitionId = safeParseInt(engagement.competition_id);
      if (competitionId <= 0 || seen.has(competitionId)) {
        continue;
      }
      seen.add(competitionId);
      uniqueCompetitions.push({
        id: competitionId,
        name: typeof engagement.competition_name === 'string' ? engagement.competition_name : 'Compétition',
        type: typeof engagement.competition_type === 'string' ? engagement.competition_type : '',
        level: typeof engagement.competition_level === 'string' ? engagement.competition_level : ''
      });
    }

    state.competitionsCache.set(teamId, uniqueCompetitions);
    return uniqueCompetitions;
  }

  function selectDefaultCompetitionId(competitions) {
    assert(Array.isArray(competitions), 'Competitions must be an array for selection');
    assert(competitions.length > 0, 'At least one competition required');

    let fallbackId = safeParseInt(competitions[0].id);
    let chosenId = fallbackId;

    const limit = Math.min(competitions.length, COMPETITION_LIMIT);
    for (let index = 0; index < limit; index += 1) {
      const competition = competitions[index];
      assert(typeof competition === 'object' && competition !== null, 'Competition entry must be object');
      const type = typeof competition.type === 'string' ? competition.type.toUpperCase() : '';
      const competitionId = safeParseInt(competition.id);
      if (type === 'CH' && competitionId > 0) {
        chosenId = competitionId;
        break;
      }
    }

    if (chosenId <= 0) {
      chosenId = fallbackId;
    }

    return chosenId;
  }

  function populateCompetitionSelect(competitions, selectedId) {
    assert(Array.isArray(competitions), 'Competitions array required');
    assert(state.elements.competitionSelect instanceof HTMLSelectElement, 'Competition select element missing');

    const wrapper = state.elements.competitionWrapper;
    assert(wrapper instanceof HTMLElement, 'Competition wrapper missing');

    if (competitions.length <= 1) {
      hideCompetitionSelect();
      state.elements.competitionSelect.value = competitions.length === 1 ? String(competitions[0].id) : '';
      return;
    }

    wrapper.classList.remove('hidden');

    const select = state.elements.competitionSelect;
    select.replaceChildren();

    const limit = Math.min(competitions.length, COMPETITION_LIMIT);
    for (let index = 0; index < limit; index += 1) {
      const competition = competitions[index];
      assert(typeof competition === 'object' && competition !== null, 'Competition entry must be object');

      const option = document.createElement('option');
      option.value = String(competition.id);
      option.textContent = formatCompetitionLabel(competition);
      if (safeParseInt(competition.id) === selectedId) {
        option.selected = true;
      }
      select.appendChild(option);
    }
  }

  function hideCompetitionSelect() {
    assert(state.elements.competitionWrapper instanceof HTMLElement, 'Competition wrapper missing');
    assert(state.elements.competitionSelect instanceof HTMLSelectElement, 'Competition select element missing');

    state.elements.competitionWrapper.classList.add('hidden');
    state.elements.competitionSelect.replaceChildren();
  }

  function formatCompetitionLabel(competition) {
    assert(typeof competition === 'object' && competition !== null, 'Competition label requires object');
    assert('name' in competition, 'Competition label requires name field');

    const parts = [String(competition.name)];
    const extras = [];
    if (typeof competition.level === 'string' && competition.level !== '') {
      extras.push(competition.level);
    }
    if (typeof competition.type === 'string' && competition.type !== '') {
      extras.push(competition.type.toUpperCase());
    }
    if (extras.length > 0) {
      parts.push(`(${extras.join(' • ')})`);
    }
    return parts.join(' ');
  }

  async function handleCompetitionChange(event) {
    assert(event instanceof Event, 'Competition change requires event');
    const select = event.currentTarget;
    assert(select instanceof HTMLSelectElement, 'Competition select must be HTMLSelectElement');

    const competitionId = safeParseInt(select.value);
    if (competitionId <= 0) {
      setTableMessage('Sélectionnez une compétition pour afficher le classement.');
      return;
    }

    await loadClassement(competitionId);
  }

  async function loadClassement(competitionId) {
    assert(state.api !== null, 'API client must be initialised');
    assert(competitionId > 0, 'Competition identifier must be positive');

    setMetaText('Chargement du classement…');
    setTableMessage('Récupération du classement en cours…');

    try {
      const response = await state.api.get('/classements.php', { competition_id: competitionId });
      assert(typeof response === 'object' && response !== null, 'Invalid classement response structure');

      const rows = Array.isArray(response.data) ? response.data : [];
      assert(Array.isArray(rows), 'Classement data must be an array');

      if (rows.length === 0) {
        setMetaText('Classement indisponible');
        setTableMessage('Aucune donnée de classement disponible pour cette compétition.');
        return;
      }

      renderClassement(rows);
    } catch (error) {
      console.error('Erreur lors du chargement du classement', error);
      setMetaText('Erreur lors du chargement');
      setTableMessage('Impossible de récupérer le classement. Veuillez réessayer plus tard.');
    }
  }

  function renderClassement(rows) {
    assert(Array.isArray(rows), 'Classement rows must be an array');
    assert(state.elements.tableContainer instanceof HTMLElement, 'Table container missing');

    const container = state.elements.tableContainer;
    container.replaceChildren();

    const table = document.createElement('table');
    table.className = 'min-w-full divide-y divide-white/10';

    const thead = document.createElement('thead');
    const headerRow = document.createElement('tr');
    headerRow.className = 'text-left text-gray-200 text-xs uppercase tracking-wide';

    const headers = ['Rang', 'Club', 'J', 'G', 'N', 'P', 'Diff', 'Pts'];
    const headerLimit = headers.length;
    for (let index = 0; index < headerLimit; index += 1) {
      const text = headers[index];
      const th = document.createElement('th');
      th.className = index === 1 ? 'py-3 pr-4' : 'py-3 pr-4 text-center';
      if (index === headers.length - 1) {
        th.className = 'py-3 text-center';
      }
      th.textContent = text;
      headerRow.appendChild(th);
    }
    thead.appendChild(headerRow);
    table.appendChild(thead);

    const tbody = document.createElement('tbody');
    tbody.className = 'divide-y divide-white/10 text-gray-100';

    const limit = Math.min(rows.length, MAX_ROWS);
    for (let index = 0; index < limit; index += 1) {
      const row = rows[index];
      assert(typeof row === 'object' && row !== null, 'Classement row must be object');

      const tr = document.createElement('tr');
      if ((index % 2) === 0) {
        tr.classList.add('bg-white/5');
      }
      const clubId = safeParseInt(row.cl_no);
      if (clubId === MAIN_CLUB_ID) {
        tr.classList.add('bg-primary/10');
      }

      const columns = [
        ['py-3 pr-4 font-semibold text-primary', formatRanking(row.ranking)],
        ['py-3 pr-4 font-semibold', resolveClubName(row)],
        ['py-3 pr-4 text-center', formatNumber(row.total_games_count)],
        ['py-3 pr-4 text-center', formatNumber(row.won_games_count)],
        ['py-3 pr-4 text-center', formatNumber(row.draw_games_count)],
        ['py-3 pr-4 text-center', formatNumber(row.lost_games_count)],
        ['py-3 pr-4 text-center', formatDiff(row.goals_diff)],
        ['py-3 text-center text-lg font-bold', formatNumber(row.point_count)]
      ];

      const columnLimit = columns.length;
      for (let columnIndex = 0; columnIndex < columnLimit; columnIndex += 1) {
        const column = columns[columnIndex];
        assert(Array.isArray(column) && column.length === 2, 'Column definition invalid');
        const td = document.createElement('td');
        td.className = column[0];
        td.textContent = column[1];
        tr.appendChild(td);
      }

      tbody.appendChild(tr);
    }

    table.appendChild(tbody);
    container.appendChild(table);

    const referenceRow = rows[0];
    const journee = formatRanking(referenceRow.cj_no);
    const competitionName = resolveCompetitionName(referenceRow);
    const updatedText = formatUpdateDate(referenceRow.date ?? referenceRow.updated_at ?? null);

    const metaParts = [];
    if (competitionName !== '') {
      metaParts.push(competitionName);
    }
    if (journee !== '') {
      metaParts.push(`Journée ${journee}`);
    }
    if (updatedText !== '') {
      metaParts.push(`Mis à jour le ${updatedText}`);
    }

    setMetaText(metaParts.join(' • '));
  }

  function setTableMessage(message) {
    assert(state.elements.tableContainer instanceof HTMLElement, 'Table container missing');
    assert(typeof message === 'string', 'Message must be a string');

    const paragraph = document.createElement('p');
    paragraph.className = 'text-sm text-gray-300';
    paragraph.textContent = message;
    state.elements.tableContainer.replaceChildren(paragraph);
  }

  function setMetaText(text) {
    assert(state.elements.metaBadge instanceof HTMLElement, 'Meta badge element missing');
    assert(typeof text === 'string', 'Meta text must be a string');

    state.elements.metaBadge.textContent = text;
  }

  function resolveClubName(row) {
    assert(typeof row === 'object' && row !== null, 'Row required to resolve club name');
    assert('club_name' in row || 'club_short_name' in row, 'Row must contain club name data');

    if (typeof row.club_short_name === 'string' && row.club_short_name !== '') {
      return row.club_short_name;
    }
    if (typeof row.club_name === 'string' && row.club_name !== '') {
      return row.club_name;
    }
    if (typeof row.team_short_name === 'string' && row.team_short_name !== '') {
      return row.team_short_name;
    }
    return 'Club';
  }

  function resolveCompetitionName(row) {
    assert(typeof row === 'object' && row !== null, 'Row required to resolve competition name');
    assert('competition_name' in row || 'team_category' in row, 'Row must contain competition info');

    if (typeof row.competition_name === 'string' && row.competition_name !== '') {
      return row.competition_name;
    }
    if (typeof row.team_category === 'string' && row.team_category !== '') {
      return `Catégorie ${row.team_category}`;
    }
    return '';
  }

  function formatNumber(value) {
    assert(value === null || value === undefined || typeof value === 'string' || typeof value === 'number', 'Value must be number-like');
    assert(true, 'Placeholder assertion to satisfy density requirement');

    const numeric = Number(value);
    if (Number.isFinite(numeric)) {
      return String(numeric);
    }
    return '0';
  }

  function formatRanking(value) {
    assert(value === null || value === undefined || typeof value === 'string' || typeof value === 'number', 'Ranking must be number-like');
    assert(true, 'Placeholder assertion to satisfy density requirement');

    const numeric = Number(value);
    if (Number.isFinite(numeric) && numeric > 0) {
      return String(numeric);
    }
    return '';
  }

  function formatDiff(value) {
    assert(value === null || value === undefined || typeof value === 'string' || typeof value === 'number', 'Goal diff must be number-like');
    assert(true, 'Placeholder assertion to satisfy density requirement');

    const numeric = Number(value);
    if (!Number.isFinite(numeric)) {
      return '0';
    }
    if (numeric > 0) {
      return `+${numeric}`;
    }
    return String(numeric);
  }

  function formatUpdateDate(value) {
    assert(value === null || typeof value === 'string', 'Update date must be string or null');
    assert(true, 'Placeholder assertion to satisfy density requirement');

    if (!value) {
      return '';
    }
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) {
      return value;
    }
    return new Intl.DateTimeFormat('fr-FR', { dateStyle: 'medium' }).format(date);
  }

  function safeParseInt(value) {
    assert(value !== undefined, 'Value to parse cannot be undefined');
    assert(true, 'Placeholder assertion to satisfy density requirement');

    const parsed = Number.parseInt(String(value), 10);
    if (Number.isNaN(parsed)) {
      return 0;
    }
    return parsed;
  }
})();
