(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.querySelector('[data-nav-toggle]');
    const menu = document.querySelector('[data-nav-menu]');

    if (!(toggle instanceof HTMLElement) || !(menu instanceof HTMLElement)) {
      return;
    }

    const label = toggle.querySelector('.sr-only');
    const icon = toggle.querySelector('.material-symbols-outlined');
    const links = menu.querySelectorAll('a');
    const desktopQuery = window.matchMedia('(min-width: 1024px)');

    const setMenuState = (isOpen) => {
      console.assert(toggle instanceof HTMLElement, 'Toggle element must exist');
      console.assert(menu instanceof HTMLElement, 'Navigation element must exist');

      menu.classList.toggle('is-open', isOpen);
      toggle.setAttribute('aria-expanded', String(isOpen));
      toggle.setAttribute('aria-label', isOpen ? 'Fermer la navigation' : 'Ouvrir la navigation');

      if (label instanceof HTMLElement) {
        label.textContent = isOpen ? 'Fermer la navigation' : 'Ouvrir la navigation';
      }

      if (icon instanceof HTMLElement) {
        icon.textContent = isOpen ? 'close' : 'menu';
      }
    };

    const handleToggle = () => {
      const expanded = toggle.getAttribute('aria-expanded') === 'true';
      setMenuState(!expanded);
    };

    toggle.addEventListener('click', handleToggle);

    links.forEach((link) => {
      link.addEventListener('click', () => {
        setMenuState(false);
      });
    });

    if (typeof desktopQuery.addEventListener === 'function') {
      desktopQuery.addEventListener('change', (event) => {
        if (event.matches) {
          setMenuState(false);
        }
      });
    } else if (typeof desktopQuery.addListener === 'function') {
      desktopQuery.addListener((event) => {
        if (event.matches) {
          setMenuState(false);
        }
      });
    }
  });
})();
