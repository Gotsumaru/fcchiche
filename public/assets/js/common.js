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
})();
