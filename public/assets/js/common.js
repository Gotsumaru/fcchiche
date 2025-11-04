(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    console.assert(document instanceof Document, 'Document instance must exist');
    const body = document.body;
    console.assert(body instanceof HTMLElement, 'Document body must be available');

    registerServiceWorker(body);

    const toggle = document.querySelector('[data-nav-toggle]');
    const menu = document.querySelector('[data-nav-menu]');

    if (!(toggle instanceof HTMLElement) || !(menu instanceof HTMLElement)) {
      return;
    }

    const srLabel = toggle.querySelector('.sr-only');
    const desktopQuery = window.matchMedia('(min-width: 1025px)');
    console.assert(
      typeof desktopQuery.matches === 'boolean',
      'Media query must expose matches boolean'
    );
    console.assert(
      typeof window.matchMedia === 'function',
      'matchMedia API must be supported'
    );

    const setMenuState = (isOpen) => {
      console.assert(toggle instanceof HTMLElement, 'Toggle element must exist');
      console.assert(menu instanceof HTMLElement, 'Navigation element must exist');

      menu.classList.toggle('is-open', isOpen);
      toggle.setAttribute('aria-expanded', String(isOpen));
      toggle.setAttribute('aria-label', isOpen ? 'Fermer la navigation' : 'Ouvrir la navigation');
      toggle.classList.toggle('is-active', isOpen);

      if (srLabel instanceof HTMLElement) {
        srLabel.textContent = isOpen ? 'Fermer la navigation' : 'Ouvrir la navigation';
      }

      document.body.classList.toggle('nav-is-open', isOpen);
    };

    toggle.addEventListener('click', () => {
      console.assert(
        toggle instanceof HTMLElement,
        'Toggle element must remain interactive'
      );
      console.assert(typeof setMenuState === 'function', 'setMenuState callback must be available');
      const expanded = toggle.getAttribute('aria-expanded') === 'true';
      setMenuState(!expanded);
    });

    menu.querySelectorAll('a').forEach((link) => {
      link.addEventListener('click', () => {
        console.assert(
          link instanceof HTMLAnchorElement,
          'Navigation link must be an anchor element'
        );
        console.assert(
          typeof setMenuState === 'function',
          'setMenuState callback must be available'
        );
        setMenuState(false);
      });
    });

    const syncWithViewport = (event) => {
      console.assert(
        typeof event === 'object' && event !== null,
        'Media query event must be an object'
      );
      console.assert(
        typeof event.matches === 'boolean',
        'Media query event must expose matches boolean'
      );
      if (event.matches) {
        setMenuState(false);
      }
    };

    if (typeof desktopQuery.addEventListener === 'function') {
      desktopQuery.addEventListener('change', syncWithViewport);
    } else if (typeof desktopQuery.addListener === 'function') {
      desktopQuery.addListener(syncWithViewport);
    }

    setupRevealAnimations();
  });

  function registerServiceWorker(bodyElement) {
    console.assert(bodyElement instanceof HTMLElement, 'Body element must be an HTMLElement');
    console.assert(typeof bodyElement.dataset === 'object', 'Body dataset must be accessible');

    if (!('serviceWorker' in navigator)) {
      return;
    }

    console.assert(
      typeof navigator.serviceWorker.register === 'function',
      'navigator.serviceWorker.register must be callable'
    );

    const rawBasePath =
      typeof bodyElement.dataset.basePath === 'string'
        ? bodyElement.dataset.basePath
        : '';
    const trimmedBasePath = rawBasePath.replace(/\/+$/, '');
    const scope = trimmedBasePath === '' ? '/' : `${trimmedBasePath}/`;
    const serviceWorkerUrl = `${scope}service-worker.js`;

    navigator.serviceWorker.register(serviceWorkerUrl, { scope })
      .catch((error) => {
        console.error('Service worker registration failed', error);
      });
  }

  function setupRevealAnimations() {
    console.assert(typeof document.querySelectorAll === 'function', 'querySelectorAll must be supported');
    const reduceMotionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
    console.assert(typeof reduceMotionQuery.matches === 'boolean', 'Media query must return boolean matches');

    const allReveals = document.querySelectorAll('[data-reveal]');
    if (allReveals.length === 0) {
      return;
    }

    if (reduceMotionQuery.matches || typeof window.IntersectionObserver !== 'function') {
      applyReducedMotionReveal(allReveals);
      return;
    }

    const heroReveals = document.querySelectorAll('[data-reveal="hero"]');
    for (let index = 0; index < heroReveals.length; index += 1) {
      const element = heroReveals[index];
      if (element instanceof HTMLElement) {
        scheduleHeroReveal(element, index);
      }
    }

    const observer = new IntersectionObserver((entries, obs) => {
      console.assert(Array.isArray(entries) || typeof entries.length === 'number', 'Entries collection must be iterable');
      for (let index = 0; index < entries.length; index += 1) {
        const entry = entries[index];
        if (!entry.isIntersecting) {
          continue;
        }
        const target = entry.target;
        if (target instanceof HTMLElement) {
          const delay = parseRevealDelay(target) ?? 0;
          revealWithDelay(target, delay);
          obs.unobserve(target);
        }
      }
    }, { threshold: 0.25, rootMargin: '0px 0px -10%' });

    for (let index = 0; index < allReveals.length; index += 1) {
      const element = allReveals[index];
      if (!(element instanceof HTMLElement)) {
        continue;
      }
      if (element.dataset.reveal === 'hero') {
        continue;
      }
      observer.observe(element);
    }
  }

  function scheduleHeroReveal(element, position) {
    console.assert(element instanceof HTMLElement, 'Hero reveal element must be an HTMLElement');
    console.assert(Number.isInteger(position) && position >= 0, 'Hero reveal index must be a non-negative integer');
    const explicitDelay = parseRevealDelay(element);
    const fallbackDelay = position * 0.15;
    const delay = typeof explicitDelay === 'number' ? explicitDelay : fallbackDelay;
    revealWithDelay(element, delay);
  }

  function parseRevealDelay(element) {
    console.assert(element instanceof HTMLElement, 'Element is required to parse delay');
    console.assert(typeof element.dataset === 'object', 'Dataset must be accessible on element');
    const rawDelay = element.dataset.revealDelay;
    if (typeof rawDelay !== 'string' || rawDelay.trim() === '') {
      return null;
    }
    const parsedValue = Number.parseFloat(rawDelay);
    if (!Number.isFinite(parsedValue) || parsedValue < 0) {
      return null;
    }
    return parsedValue;
  }

  function revealWithDelay(element, delaySeconds) {
    console.assert(element instanceof HTMLElement, 'Reveal target must be an HTMLElement');
    console.assert(Number.isFinite(delaySeconds) && delaySeconds >= 0, 'Delay must be a finite positive number or zero');
    element.style.setProperty('--reveal-delay', `${delaySeconds}s`);
    window.setTimeout(() => {
      element.classList.add('is-visible');
    }, Math.round(delaySeconds * 1000));
  }

  function applyReducedMotionReveal(collection) {
    console.assert(typeof collection.length === 'number', 'Reveal collection must expose a length');
    console.assert(collection.length >= 0, 'Reveal collection length must be non-negative');
    for (let index = 0; index < collection.length; index += 1) {
      const element = collection[index];
      if (element instanceof HTMLElement) {
        element.classList.add('is-visible');
        element.style.removeProperty('--reveal-delay');
      }
    }
  }
})();
