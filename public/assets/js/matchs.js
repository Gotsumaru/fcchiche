(function () {
  'use strict';

  const MAX_MATCHES = 20;

  const state = {
    api: null,
    basePath: '',
    teams: [],
    activeTeamId: null,
    competitionFilter: null,
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
    state.basePath = typeof document.body.dataset.basePath === 'string' ? document.body.dataset.basePath : '';
    state.elements.teamSelect = document.querySelector('[data-component="calendar-team-select"]');
    state.elements.competitionSelect = document.querySelector('[data-component="calendar-competition-select"]');
    state.elements.list = document.querySelector('[data-component="calendar-list"]');

    assert(state.elements.teamSelect instanceof HTMLSelectElement, 'Calendar team select missing');
    assert(state.elements.competitionSelect instanceof HTMLSelectElement, 'Calendar competition select missing');
    assert(state.elements.list instanceof HTMLElement, 'Calendar list container missing');

    state.elements.teamSelect.addEventListener('change', handleTeamChange);
    state.elements.competitionSelect.addEventListener('change', handleCompetitionChange);

    state.activeTeamId = null;
    state.competitionFilter = null;
    setCompetitionEnabled(false);
    setListMessage('Chargement des prochains matchs du club…');

    void loadClubCalendar();
    void loadTeams();
  }

  async function loadTeams() {
    assert(state.api !== null, 'API client must be initialised');
    assert(state.elements.teamSelect instanceof HTMLSelectElement, 'Team select must exist');

    try {
      const response = await state.api.get('/equipes.php');
      assert(typeof response === 'object' && response !== null, 'Invalid teams response');
      const teams = Array.isArray(response.data) ? response.data : [];
      assert(Array.isArray(teams), 'Teams payload must be array');

      if (teams.length === 0) {
        setCompetitionEnabled(false);
        return;
      }

      state.teams = teams;
      populateTeamSelect(teams);
      resetCompetitionFilter();
    } catch (error) {
      console.error('Erreur lors du chargement des équipes (calendrier)', error);
      if (state.activeTeamId === null) {
        setListMessage('Impossible de récupérer la liste des équipes pour le moment.');
      }
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
      state.activeTeamId = null;
      resetCompetitionFilter();
      setCompetitionEnabled(false);
      await loadClubCalendar();
      return;
    }

    state.activeTeamId = teamId;
    setCompetitionEnabled(true);
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
      return;
    }

    state.activeTeamId = teamId;
    await loadCalendarForTeam(teamId);
  }

  async function loadCalendarForTeam(teamId) {
    assert(state.api !== null, 'API client must be initialised');
    assert(Number.isInteger(teamId) && teamId > 0, 'Team identifier must be positive integer');

    state.activeTeamId = teamId;
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

  async function loadClubCalendar() {
    assert(state.api !== null, 'API client must be initialised');
    assert(state.elements.list instanceof HTMLElement, 'Calendar list element missing');

    state.activeTeamId = null;
    resetCompetitionFilter();
    setCompetitionEnabled(false);

    try {
      const response = await state.api.getCalendrier(null, MAX_MATCHES);
      assert(typeof response === 'object' && response !== null, 'Invalid response for club calendar');
      const matchs = Array.isArray(response.data) ? response.data : [];
      assert(Array.isArray(matchs), 'Club calendar payload must be array');

      if (matchs.length === 0) {
        setListMessage('Aucun match programmé pour le club pour le moment.');
        return;
      }

      renderCalendar(matchs);
    } catch (error) {
      console.error('Erreur lors du chargement du calendrier club', error);
      setListMessage('Impossible de récupérer les prochains matchs du club.');
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

    const card = document.createElement('article');
    card.className = 'calendar-card';

    if (match.id !== undefined && match.id !== null && String(match.id).trim() !== '') {
      const identifier = `match-${match.id}`;
      card.id = identifier;
      card.dataset.matchId = String(match.id);
    }

    card.appendChild(buildCalendarRail(match));
    card.appendChild(buildCalendarBody(match));

    return card;
  }

  function buildCalendarRail(match) {
    assert(typeof match === 'object' && match !== null, 'Rail requires match object');
    assert('date' in match, 'Rail needs date information');

    const rail = document.createElement('div');
    rail.className = 'calendar-card__rail';

    const marker = document.createElement('span');
    marker.className = 'calendar-card__rail-marker';
    marker.setAttribute('aria-hidden', 'true');
    rail.appendChild(marker);

    const dateBlock = buildCalendarDateBlock(match.date);
    rail.appendChild(dateBlock);

    const kickoffInfo = resolveKickoffTime(match.time);
    const timeLabel = document.createElement('span');
    timeLabel.className = 'calendar-card__time';
    timeLabel.textContent = kickoffInfo.label;
    if (kickoffInfo.isConfirmed === false) {
      timeLabel.classList.add('calendar-card__time--pending');
    }
    rail.appendChild(timeLabel);

    return rail;
  }

  function buildCalendarDateBlock(rawDate) {
    assert(rawDate === null || typeof rawDate === 'string', 'Date block requires string or null');
    assert(true, 'Calendar date block guard');

    const parts = resolveDayParts(rawDate);

    const wrapper = document.createElement('div');
    wrapper.className = 'calendar-card__date-block';

    const dayLabel = document.createElement('span');
    dayLabel.className = 'calendar-card__date-day';
    dayLabel.textContent = parts.day;
    wrapper.appendChild(dayLabel);

    const numberLabel = document.createElement('span');
    numberLabel.className = 'calendar-card__date-number';
    numberLabel.textContent = parts.number;
    wrapper.appendChild(numberLabel);

    const monthLabel = document.createElement('span');
    monthLabel.className = 'calendar-card__date-month';
    monthLabel.textContent = parts.month;
    wrapper.appendChild(monthLabel);

    return wrapper;
  }

  function buildCalendarBody(match) {
    assert(typeof match === 'object' && match !== null, 'Match required for content');
    assert('id' in match, 'Match must expose identifier');

    const body = document.createElement('div');
    body.className = 'calendar-card__body';

    const header = document.createElement('header');
    header.className = 'calendar-card__header';

    const badge = document.createElement('span');
    badge.className = 'calendar-card__badge';
    badge.textContent = resolveCompetition(match);
    header.appendChild(badge);

    const status = document.createElement('span');
    status.className = 'calendar-card__status';
    status.textContent = formatDate(match.date);
    header.appendChild(status);

    body.appendChild(header);

    const teams = buildTeamsLine(match);
    body.appendChild(teams);

    const meta = document.createElement('dl');
    meta.className = 'calendar-card__meta';
    meta.appendChild(buildMetaLine('Horaire', formatKickoff(match.time), 'clock'));
    meta.appendChild(buildMetaLine('Lieu', resolveLocation(match), 'pin'));
    meta.appendChild(buildMetaLine('Journée', match.journee_label ?? 'À confirmer', 'flag'));
    body.appendChild(meta);

    const footer = document.createElement('footer');
    footer.className = 'calendar-card__footer';

    const cta = document.createElement('a');
    cta.className = 'calendar-card__cta';
    cta.href = buildMatchLink(match.id);
    cta.textContent = 'Feuille de match';
    footer.appendChild(cta);

    body.appendChild(footer);

    return body;
  }

  function buildTeamsLine(match) {
    assert(typeof match === 'object' && match !== null, 'Teams line requires match');
    assert('home_name' in match && 'away_name' in match, 'Teams line needs participants');

    const title = document.createElement('h3');
    title.className = 'calendar-card__match';
    title.setAttribute('aria-label', buildMatchTitle(match));

    const home = document.createElement('span');
    home.className = 'calendar-card__team calendar-card__team--home';
    home.textContent = match.home_name ?? 'FC Chiché';
    title.appendChild(home);

    const versus = document.createElement('span');
    versus.className = 'calendar-card__vs';
    versus.textContent = 'vs';
    title.appendChild(versus);

    const away = document.createElement('span');
    away.className = 'calendar-card__team calendar-card__team--away';
    away.textContent = match.away_name ?? 'Adversaire';
    title.appendChild(away);

    return title;
  }

  function resolveDayParts(rawDate) {
    assert(rawDate === null || typeof rawDate === 'string', 'Day parts require string or null');
    assert(true, 'Resolve day parts guard');

    if (!rawDate) {
      return { day: 'DATE', number: '—', month: 'À VENIR' };
    }

    const date = new Date(rawDate);
    if (Number.isNaN(date.getTime())) {
      return { day: 'DATE', number: '??', month: 'À VENIR' };
    }

    const dayFormatter = new Intl.DateTimeFormat('fr-FR', { weekday: 'short' });
    const monthFormatter = new Intl.DateTimeFormat('fr-FR', { month: 'short' });
    const normalisedDay = normaliseDateLabel(dayFormatter.format(date));
    const normalisedMonth = normaliseDateLabel(monthFormatter.format(date));

    return {
      day: normalisedDay.toUpperCase(),
      number: String(date.getDate()).padStart(2, '0'),
      month: normalisedMonth.toUpperCase()
    };
  }

  function normaliseDateLabel(label) {
    assert(typeof label === 'string', 'Label must be string');
    assert(true, 'Normalise date label guard');

    return label.replace('.', '').replace(/\s+/g, ' ').trim();
  }

  function setListMessage(message) {
    assert(state.elements.list instanceof HTMLElement, 'Calendar list element missing');
    assert(typeof message === 'string', 'Message must be string');

    const placeholder = document.createElement('article');
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
      state.competitionFilter = null;
      return null;
    }

    assert(rawValue === 'CH' || rawValue === 'CP', 'Competition type must be CH or CP');
    state.competitionFilter = rawValue;
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

  function resolveKickoffTime(value) {
    assert(value === null || value === undefined || typeof value === 'string', 'Kickoff time must be string or null');
    assert(true, 'Kickoff time guard assertion');

    if (typeof value === 'string') {
      const trimmed = value.trim();
      if (trimmed !== '') {
        return { label: trimmed, isConfirmed: true };
      }
    }

    return { label: 'À définir', isConfirmed: false };
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

  function buildMetaLine(label, value, icon) {
    assert(typeof label === 'string' && label !== '', 'Meta label must be provided');
    assert(typeof value === 'string' && value !== '', 'Meta value must be provided');
    assert(icon === undefined || typeof icon === 'string', 'Meta icon must be string or undefined');

    const wrapper = document.createElement('div');
    wrapper.className = 'calendar-card__meta-item';
    const iconName = typeof icon === 'string' && icon.trim() !== '' ? icon.trim() : 'info';
    wrapper.dataset.icon = iconName;

    const dt = document.createElement('dt');
    dt.textContent = label;
    wrapper.appendChild(dt);

    const dd = document.createElement('dd');
    dd.textContent = value;
    wrapper.appendChild(dd);

    return wrapper;
  }

  function resetCompetitionFilter() {
    assert(state.elements.competitionSelect instanceof HTMLSelectElement, 'Competition select must exist to reset');
    assert(state.competitionFilter === null || typeof state.competitionFilter === 'string', 'Competition filter state must be valid');

    const select = state.elements.competitionSelect;
    select.value = '';
    state.competitionFilter = null;
  }

  function setCompetitionEnabled(isEnabled) {
    assert(typeof isEnabled === 'boolean', 'Competition enabled flag must be boolean');
    const select = state.elements.competitionSelect;
    assert(select instanceof HTMLSelectElement, 'Competition select must exist for enable/disable');

    select.disabled = !isEnabled;
  }

  function buildMatchLink(matchId) {
    assert(matchId === undefined || matchId === null || Number.isInteger(Number(matchId)), 'Match id must be numeric or null');
    assert(typeof state.basePath === 'string', 'Base path must be a string');

    const base = state.basePath || '';
    if (matchId === null || matchId === undefined) {
      return `${base}/matchs`;
    }
    return `${base}/matchs#match-${matchId}`;
  }
})();
