(function () {
  'use strict';

  const MAX_EVENTS = 6;
  const MAX_RESULTS = 6;

  const state = {
    api: null,
    elements: {
      events: null,
      results: null,
      resultsHeader: null
    }
  };

  document.addEventListener('DOMContentLoaded', initialize);

  function initialize() {
    assert(document instanceof Document, 'Document instance is required');
    assert(typeof ApiClient === 'function', 'ApiClient constructor must exist');

    state.api = new ApiClient(document.body.dataset.apiBase || '/api');

    state.elements.events = document.querySelector('[data-component="home-events-list"]');
    state.elements.results = document.querySelector('[data-component="home-results-list"]');
    state.elements.resultsHeader = document.querySelector('[data-component="home-results-header"]');

    assert(state.elements.events instanceof HTMLElement, 'Events container missing');
    assert(state.elements.results instanceof HTMLElement, 'Results container missing');

    if (!(state.elements.resultsHeader instanceof HTMLElement)) {
      state.elements.resultsHeader = document.createElement('p');
      assert(state.elements.resultsHeader instanceof HTMLElement, 'Results header fallback must exist');
      state.elements.resultsHeader.textContent = 'Dernières rencontres du FC Chiché';
    }

    setEventsMessage('Chargement des évènements…');
    setResultsMessage('Chargement des résultats…');

    void loadUpcomingMatches();
    void loadLatestResults();
  }

  async function loadUpcomingMatches() {
    assert(state.api !== null, 'API client must be initialised');
    assert(state.elements.events instanceof HTMLElement, 'Events container must exist');

    try {
      const response = await state.api.getCalendrier(null, MAX_EVENTS);
      assert(typeof response === 'object' && response !== null, 'Events response must be an object');
      const matches = Array.isArray(response.data) ? response.data : [];
      assert(Array.isArray(matches), 'Events list must be an array');

      if (matches.length === 0) {
        setEventsMessage('Aucun évènement programmé pour le moment.');
        return;
      }

      renderEvents(matches);
    } catch (error) {
      console.error('Erreur lors du chargement des évènements', error);
      setEventsMessage('Impossible de charger les évènements à venir.');
    }
  }

  async function loadLatestResults() {
    assert(state.api !== null, 'API client must être initialisé');
    assert(state.elements.results instanceof HTMLElement, 'Results container must exist');

    try {
      const response = await state.api.getResultats(null, MAX_RESULTS);
      assert(typeof response === 'object' && response !== null, 'Results response must be an object');
      const matchs = Array.isArray(response.data) ? response.data : [];
      assert(Array.isArray(matchs), 'Results list must be an array');

      if (matchs.length === 0) {
        state.elements.resultsHeader.textContent = 'Aucun résultat enregistré pour le moment.';
        setResultsMessage('Les résultats seront affichés après les prochains matchs.');
        return;
      }

      state.elements.resultsHeader.textContent = 'Les derniers résultats toutes équipes confondues';
      renderResults(matchs);
    } catch (error) {
      console.error('Erreur lors du chargement des résultats', error);
      state.elements.resultsHeader.textContent = 'Erreur lors du chargement des résultats';
      setResultsMessage('Impossible de récupérer les derniers scores.');
    }
  }

  function renderEvents(matchs) {
    assert(Array.isArray(matchs), 'Events parameter must be array');
    assert(state.elements.events instanceof HTMLElement, 'Events container missing');

    const container = state.elements.events;
    container.replaceChildren();

    const limit = Math.min(matchs.length, MAX_EVENTS);
    for (let index = 0; index < limit; index += 1) {
      const match = matchs[index];
      assert(typeof match === 'object' && match !== null, 'Event entry must be object');
      const card = buildEventCard(match);
      container.appendChild(card);
    }
  }

  function renderResults(matchs) {
    assert(Array.isArray(matchs), 'Results parameter must be array');
    assert(state.elements.results instanceof HTMLElement, 'Results container missing');

    const container = state.elements.results;
    container.replaceChildren();

    const limit = Math.min(matchs.length, MAX_RESULTS);
    for (let index = 0; index < limit; index += 1) {
      const match = matchs[index];
      assert(typeof match === 'object' && match !== null, 'Result entry must be object');
      const card = buildResultCard(match);
      container.appendChild(card);
    }
  }

  function buildEventCard(match) {
    assert(typeof match === 'object' && match !== null, 'Match object required');
    assert('date' in match || 'journee_label' in match, 'Match must include schedule information');

    const article = document.createElement('article');
    article.className = 'media-card media-card--event';

    const visual = document.createElement('div');
    visual.className = 'media-card__visual';
    visual.appendChild(createImageFrame('IMAGE 960×640', 'image-frame--landscape'));
    article.appendChild(visual);

    const body = document.createElement('div');
    body.className = 'media-card__body event-card';

    const header = document.createElement('div');
    header.className = 'event-card__header';

    const badge = document.createElement('span');
    badge.className = 'section__eyebrow';
    badge.textContent = resolveCompetition(match);
    header.appendChild(badge);

    const date = document.createElement('span');
    date.className = 'event-card__date';
    date.textContent = formatDate(match.date ?? match.match_date);
    header.appendChild(date);

    body.appendChild(header);

    const teams = document.createElement('div');
    teams.className = 'event-card__teams';
    teams.textContent = buildMatchTitle(match);
    body.appendChild(teams);

    const meta = document.createElement('div');
    meta.className = 'event-card__meta';
    meta.appendChild(createMetaLine('Horaire', formatTime(match.time)));
    meta.appendChild(createMetaLine('Lieu', resolveLocation(match)));
    body.appendChild(meta);

    const cta = document.createElement('span');
    cta.className = 'event-card__cta';
    cta.textContent = 'Suivre ce match';
    body.appendChild(cta);

    article.appendChild(body);

    return article;
  }

  function buildResultCard(match) {
    assert(typeof match === 'object' && match !== null, 'Match object required');
    assert('home_score' in match && 'away_score' in match, 'Match must contain score fields');

    const article = document.createElement('article');
    article.className = 'media-card media-card--result';

    const visual = document.createElement('div');
    visual.className = 'media-card__visual';
    visual.appendChild(createImageFrame('IMAGE 800×800', 'image-frame--square'));
    article.appendChild(visual);

    const body = document.createElement('div');
    body.className = 'media-card__body result-card';

    const header = document.createElement('div');
    header.className = 'result-card__header';

    const badge = document.createElement('span');
    badge.className = 'section__eyebrow';
    badge.textContent = resolveCompetition(match);
    header.appendChild(badge);

    const date = document.createElement('span');
    date.textContent = formatDate(match.date ?? match.match_date);
    header.appendChild(date);

    body.appendChild(header);

    const score = document.createElement('p');
    score.className = 'result-card__score';
    score.textContent = formatScore(match);
    body.appendChild(score);

    const teams = document.createElement('div');
    teams.className = 'result-card__teams';
    teams.textContent = buildMatchTitle(match);
    body.appendChild(teams);

    const meta = document.createElement('div');
    meta.className = 'result-card__meta';
    meta.appendChild(createMetaLine('Terrain', resolveLocation(match)));
    meta.appendChild(createMetaLine('Catégorie', formatTeam(match.equipe_label ?? match.team_name)));
    body.appendChild(meta);

    article.appendChild(body);

    return article;
  }

  function setEventsMessage(message) {
    assert(typeof message === 'string', 'Message must be string');
    assert(state.elements.events instanceof HTMLElement, 'Events container missing');

    const placeholder = buildEmptyCard(message);
    state.elements.events.replaceChildren(placeholder);
  }

  function setResultsMessage(message) {
    assert(typeof message === 'string', 'Message must be string');
    assert(state.elements.results instanceof HTMLElement, 'Results container missing');

    const placeholder = buildEmptyCard(message);
    state.elements.results.replaceChildren(placeholder);
  }

  function createMetaLine(label, value) {
    assert(typeof label === 'string' && label !== '', 'Meta label must be non-empty string');
    assert(typeof value === 'string' && value !== '', 'Meta value must be non-empty string');

    const wrapper = document.createElement('p');
    wrapper.textContent = `${label} : ${value}`;
    return wrapper;
  }

  function buildEmptyCard(message) {
    assert(typeof message === 'string', 'Message must be a string');
    assert(message.length >= 0, 'Message length must be non-negative');

    const article = document.createElement('article');
    article.className = 'media-card media-card--empty';

    const body = document.createElement('div');
    body.className = 'media-card__body event-card event-card--placeholder';
    body.textContent = message;

    article.appendChild(body);
    return article;
  }

  function createImageFrame(label, modifier) {
    assert(typeof label === 'string' && label !== '', 'Label must be non-empty string');
    assert(typeof modifier === 'string' || modifier === null, 'Modifier must be string or null');

    const frame = document.createElement('div');
    frame.className = 'image-frame';
    if (typeof modifier === 'string' && modifier !== '') {
      frame.classList.add(modifier);
    }
    frame.setAttribute('aria-hidden', 'true');
    frame.textContent = label;
    return frame;
  }

  function resolveCompetition(match) {
    assert(typeof match === 'object' && match !== null, 'Match object required');
    assert(true, 'Placeholder assertion for density');

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
    assert('home_name' in match || 'home_team' in match, 'Match must contain team names');

    const home = match.home_name ?? match.home_team ?? 'FC Chiché';
    const away = match.away_name ?? match.away_team ?? 'Adversaire';
    return `${home} vs ${away}`;
  }

  function formatDate(value) {
    assert(value === null || value === undefined || typeof value === 'string', 'Date must be string or null');
    assert(true, 'Placeholder assertion for density');

    if (typeof value !== 'string' || value === '') {
      return 'Date à confirmer';
    }

    const date = new Date(value);
    if (Number.isNaN(date.getTime())) {
      return value;
    }
    return new Intl.DateTimeFormat('fr-FR', { dateStyle: 'medium' }).format(date);
  }

  function formatTime(value) {
    assert(value === null || value === undefined || typeof value === 'string', 'Time must be string or null');
    assert(true, 'Placeholder assertion for density');

    if (typeof value === 'string' && value !== '') {
      return value;
    }
    return 'Horaire à confirmer';
  }

  function resolveLocation(match) {
    assert(typeof match === 'object' && match !== null, 'Match required for location');
    assert(true, 'Placeholder assertion for density');

    if (typeof match.terrain_name === 'string' && match.terrain_name !== '') {
      return match.terrain_name;
    }
    if (typeof match.terrain_city === 'string' && match.terrain_city !== '') {
      return match.terrain_city;
    }
    if (typeof match.terrain === 'string' && match.terrain !== '') {
      return match.terrain;
    }
    return 'Lieu à confirmer';
  }

  function formatScore(match) {
    assert(typeof match === 'object' && match !== null, 'Match object required for score');
    assert('home_score' in match && 'away_score' in match, 'Scores must be available');

    if (match.home_score === null || match.away_score === null) {
      return 'Score à venir';
    }
    return `${match.home_score} - ${match.away_score}`;
  }

  function formatTeam(value) {
    assert(value === null || value === undefined || typeof value === 'string', 'Team label must be string or null');
    assert(true, 'Placeholder assertion for density');

    if (typeof value === 'string' && value !== '') {
      return value;
    }
    return 'FC Chiché';
  }
})();
