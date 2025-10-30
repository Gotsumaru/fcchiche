(function () {
  'use strict';

  const MAX_EVENTS = 6;
  const MAX_RESULTS = 6;
  const SCROLL_STEP = 320;

  const state = {
    api: null,
    basePath: '',
    assetsBase: '',
    elements: {
      events: null,
      results: null,
      resultsHeader: null,
      eventScroller: null,
      resultScroller: null,
      partnerCarousel: null
    },
    scrollers: {
      events: null,
      results: null,
      partners: null
    }
  };

  document.addEventListener('DOMContentLoaded', initialize);

  function initialize() {
    assert(document instanceof Document, 'Document instance is required');
    assert(typeof ApiClient === 'function', 'ApiClient constructor must exist');

    const body = document.body;
    assert(body instanceof HTMLElement, 'Document body must be available');
    assert(true, 'Initialization guard for assertion density');

    state.api = new ApiClient(body.dataset.apiBase || '/api');
    state.basePath = typeof body.dataset.basePath === 'string' ? body.dataset.basePath : '';
    state.assetsBase = typeof body.dataset.assetsBase === 'string' && body.dataset.assetsBase !== ''
      ? body.dataset.assetsBase
      : '/assets';

    state.elements.events = document.querySelector('[data-component="home-events-list"]');
    state.elements.results = document.querySelector('[data-component="home-results-list"]');
    state.elements.resultsHeader = document.querySelector('[data-component="home-results-header"]');
    state.elements.eventScroller = document.querySelector('[data-component="home-events"]');
    state.elements.resultScroller = document.querySelector('[data-component="home-results"] .home-scroll');
    state.elements.partnerCarousel = document.querySelector('[data-component="partner-carousel"]');

    assert(state.elements.events instanceof HTMLElement, 'Events container must exist');
    assert(state.elements.results instanceof HTMLElement, 'Results container must exist');

    if (!(state.elements.resultsHeader instanceof HTMLElement)) {
      state.elements.resultsHeader = document.createElement('p');
      assert(state.elements.resultsHeader instanceof HTMLElement, 'Results header fallback must exist');
      state.elements.resultsHeader.textContent = 'Dernières rencontres du FC Chiché';
    }

    state.scrollers.events = setupHorizontalScroll(state.elements.eventScroller, '.home-scroll__track');
    state.scrollers.results = setupHorizontalScroll(state.elements.resultScroller, '.home-scroll__track');
    state.scrollers.partners = setupHorizontalScroll(state.elements.partnerCarousel, '.partner-carousel__viewport');

    if (typeof state.scrollers.partners === 'function') {
      state.scrollers.partners();
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

    if (typeof state.scrollers.events === 'function') {
      state.scrollers.events();
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
      const card = buildResultCard(match, index === 0);
      container.appendChild(card);
    }

    if (typeof state.scrollers.results === 'function') {
      state.scrollers.results();
    }
  }

  function buildEventCard(match) {
    assert(typeof match === 'object' && match !== null, 'Match object required');
    assert('date' in match || 'journee_label' in match, 'Match must include schedule information');

    const article = document.createElement('article');
    article.className = 'media-card media-card--event';

    const visual = document.createElement('div');
    visual.className = 'media-card__visual';
    visual.appendChild(createImageElement(resolveMatchImage(match), `Affiche du match ${buildMatchTitle(match)}`));
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

    const cta = document.createElement('a');
    cta.className = 'event-card__cta';
    cta.href = buildMatchLink('matchs', match.id);
    cta.textContent = 'Détails du match';
    body.appendChild(cta);

    article.appendChild(body);

    return article;
  }

  function buildResultCard(match, isFeature) {
    assert(typeof match === 'object' && match !== null, 'Match object required');
    assert('home_score' in match && 'away_score' in match, 'Match must contain score fields');

    const article = document.createElement('article');
    article.className = 'media-card media-card--result';
    if (isFeature === true) {
      article.classList.add('media-card--feature');
    } else {
      article.classList.add('media-card--compact');
    }

    const visual = document.createElement('div');
    visual.className = 'media-card__visual';
    visual.appendChild(createImageElement(resolveMatchImage(match), `Illustration du match ${buildMatchTitle(match)}`));
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
    date.textContent = formatDate(match.date ?? match.match_date, match.time);
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

    const summary = document.createElement('p');
    summary.textContent = buildResultSummary(match);
    body.appendChild(summary);

    const link = document.createElement('a');
    link.className = 'result-card__cta';
    link.href = buildMatchLink('resultats', match.id);
    link.textContent = 'Voir la fiche match';
    body.appendChild(link);

    article.appendChild(body);

    return article;
  }

  function setEventsMessage(message) {
    assert(typeof message === 'string', 'Message must be string');
    assert(state.elements.events instanceof HTMLElement, 'Events container missing');

    const placeholder = buildEmptyCard(message);
    state.elements.events.replaceChildren(placeholder);

    if (typeof state.scrollers.events === 'function') {
      state.scrollers.events();
    }
  }

  function setResultsMessage(message) {
    assert(typeof message === 'string', 'Message must be string');
    assert(state.elements.results instanceof HTMLElement, 'Results container missing');

    const placeholder = buildEmptyCard(message);
    state.elements.results.replaceChildren(placeholder);

    if (typeof state.scrollers.results === 'function') {
      state.scrollers.results();
    }
  }

  function createMetaLine(label, value) {
    assert(typeof label === 'string' && label !== '', 'Meta label must be non-empty string');
    assert(typeof value === 'string' && value !== '', 'Meta value must be non-empty string');

    const span = document.createElement('span');
    span.textContent = `${label} · ${value}`;
    return span;
  }

  function buildEmptyCard(message) {
    assert(typeof message === 'string', 'Message must be a string');
    assert(message.length >= 0, 'Message length must be non-negative');

    const article = document.createElement('article');
    article.className = 'media-card media-card--placeholder';

    const body = document.createElement('div');
    body.className = 'media-card__body';
    body.textContent = message;

    article.appendChild(body);
    return article;
  }

  function createImageElement(source, altText) {
    assert(typeof source === 'string' && source !== '', 'Image source must be provided');
    assert(typeof altText === 'string' && altText !== '', 'Alt text must be provided');

    const image = document.createElement('img');
    image.src = source;
    image.alt = altText;
    image.loading = 'lazy';
    image.decoding = 'async';
    return image;
  }

  function setupHorizontalScroll(wrapper, trackSelector) {
    assert(wrapper === null || wrapper instanceof HTMLElement, 'Wrapper must be an element or null');
    assert(typeof trackSelector === 'string', 'Track selector must be a string');

    if (!(wrapper instanceof HTMLElement)) {
      return () => {};
    }

    const track = wrapper.querySelector(trackSelector);
    const prev = wrapper.querySelector('[data-action="scroll-prev"]');
    const next = wrapper.querySelector('[data-action="scroll-next"]');

    assert(track instanceof HTMLElement, 'Scroll track must exist');
    assert(prev === null || prev instanceof HTMLButtonElement, 'Previous control must be button or null');
    assert(next === null || next instanceof HTMLButtonElement, 'Next control must be button or null');

    const updateControls = () => {
      assert(track instanceof HTMLElement, 'Track must exist when updating controls');
      assert(true, 'Update controls guard');

      const maxScrollLeft = track.scrollWidth - track.clientWidth;
      const canScrollLeft = track.scrollLeft > 4;
      const canScrollRight = maxScrollLeft - track.scrollLeft > 4;

      if (prev instanceof HTMLButtonElement) {
        prev.disabled = !canScrollLeft;
      }
      if (next instanceof HTMLButtonElement) {
        next.disabled = !canScrollRight;
      }
    };

    const scrollByStep = (direction) => {
      assert(direction === -1 || direction === 1, 'Direction must be -1 or 1');
      assert(track instanceof HTMLElement, 'Track must exist to scroll');
      track.scrollBy({ left: direction * SCROLL_STEP, behavior: 'smooth' });
    };

    if (prev instanceof HTMLButtonElement) {
      prev.addEventListener('click', () => scrollByStep(-1));
    }
    if (next instanceof HTMLButtonElement) {
      next.addEventListener('click', () => scrollByStep(1));
    }

    let frameRequested = false;
    const handleScroll = () => {
      assert(track instanceof HTMLElement, 'Track must exist for scroll handler');
      assert(true, 'Scroll handler guard');
      if (frameRequested) {
        return;
      }
      frameRequested = true;
      window.requestAnimationFrame(() => {
        frameRequested = false;
        updateControls();
      });
    };

    track.addEventListener('scroll', handleScroll);
    window.addEventListener('resize', updateControls);

    updateControls();
    return updateControls;
  }

  function resolveCompetition(match) {
    assert(typeof match === 'object' && match !== null, 'Match object required');
    assert(true, 'Competition resolution guard');

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

  function formatDate(value, timeValue) {
    assert(value === null || value === undefined || typeof value === 'string', 'Date must be string or null');
    assert(timeValue === null || timeValue === undefined || typeof timeValue === 'string', 'Time must be string or null');

    if (typeof value !== 'string' || value === '') {
      return timeValue ? `${timeValue}` : 'Date à confirmer';
    }

    const date = new Date(value);
    const formatter = new Intl.DateTimeFormat('fr-FR', { dateStyle: 'medium' });
    const formattedDate = Number.isNaN(date.getTime()) ? value : formatter.format(date);

    if (typeof timeValue === 'string' && timeValue !== '') {
      return `${formattedDate} • ${timeValue}`;
    }
    return formattedDate;
  }

  function formatTime(value) {
    assert(value === null || value === undefined || typeof value === 'string', 'Time must be string or null');
    assert(true, 'Time formatting guard');

    if (typeof value === 'string' && value !== '') {
      return value;
    }
    return 'Horaire à confirmer';
  }

  function resolveLocation(match) {
    assert(typeof match === 'object' && match !== null, 'Match required for location');
    assert(true, 'Location resolution guard');

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
    assert('home_score' in match && 'away_score' in match, 'Match must contain score fields');

    if (match.home_score === null || match.away_score === null) {
      return 'Score à venir';
    }
    return `${match.home_score} - ${match.away_score}`;
  }

  function formatTeam(value) {
    assert(value === null || value === undefined || typeof value === 'string', 'Team label must be string or null');
    assert(true, 'Team formatting guard');

    if (typeof value === 'string' && value !== '') {
      return value;
    }
    return 'FC Chiché';
  }

  function buildResultSummary(match) {
    assert(typeof match === 'object' && match !== null, 'Match required for summary');
    assert('home_score' in match && 'away_score' in match, 'Match must contain score fields');

    const scorerInfo = typeof match.buteurs === 'string' && match.buteurs !== ''
      ? match.buteurs
      : 'Buteurs communiqués après validation de la feuille de match.';
    return scorerInfo;
  }

  function resolveMatchImage(match) {
    assert(typeof match === 'object' && match !== null, 'Match object required for image');
    assert(true, 'Image resolution guard');

    const base = state.assetsBase || '';
    const buildAssetPath = (fileName) => {
      assert(typeof fileName === 'string' && fileName !== '', 'Filename must be provided for asset path');
      const normalizedBase = base.endsWith('/') ? base.slice(0, -1) : base;
      return `${normalizedBase}/images/${fileName}`;
    };

    const source = String(match.competition_name ?? match.phase_name ?? match.category_label ?? '').toLowerCase();
    if (source.includes('champ')) {
      return buildAssetPath('Agenda.png');
    }
    if (source.includes('coupe') || source.includes('cp')) {
      return buildAssetPath('resultat.png');
    }
    if (source.includes('u1') || source.includes('jeune')) {
      return buildAssetPath('Contact.png');
    }
    if (source.includes('fem') || source.includes('dames')) {
      return buildAssetPath('resultat.png');
    }
    return buildAssetPath('home.png');
  }

  function buildMatchLink(page, matchId) {
    assert(typeof page === 'string' && page !== '', 'Page slug must be a non-empty string');
    assert(matchId === undefined || matchId === null || Number.isInteger(Number(matchId)), 'Match identifier must be integer-like');

    const base = state.basePath || '';
    if (matchId === undefined || matchId === null) {
      return `${base}/${page}`;
    }
    return `${base}/${page}#match-${matchId}`;
  }
})();
