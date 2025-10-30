(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.querySelector('[data-nav-toggle]');
    const menu = document.querySelector('[data-nav-menu]');

    if (!(toggle instanceof HTMLElement) || !(menu instanceof HTMLElement)) {
      return;
    }

    const srLabel = toggle.querySelector('.sr-only');
    const desktopQuery = window.matchMedia('(min-width: 1025px)');

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
      const expanded = toggle.getAttribute('aria-expanded') === 'true';
      setMenuState(!expanded);
    });

    menu.querySelectorAll('a').forEach((link) => {
      link.addEventListener('click', () => {
        setMenuState(false);
      });
    });

    const syncWithViewport = (event) => {
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
})();
