import { useState } from 'react'

export default function Header() {
  const [navOpen, setNavOpen] = useState(false)

  const navItems = [
    { id: 'index', label: 'Accueil', href: '/' },
    { id: 'resultats', label: 'Résultats', href: '/resultats' },
    { id: 'matchs', label: 'Calendrier', href: '/matchs' },
    { id: 'classements', label: 'Classements', href: '/classements' },
    { id: 'contact', label: 'Contact', href: '/contact' },
  ]

  return (
    <header className="app-header">
      <div className="app-header__inner">
        <nav id="main-navigation" className="app-nav" data-nav-menu>
          <ul className="app-nav__list">
            {navItems.map((item) => (
              <li key={item.id}>
                <a
                  className={`app-nav__link${item.id === 'index' ? ' is-active' : ''}`}
                  href={item.href}
                  aria-current={item.id === 'index' ? 'page' : undefined}
                >
                  {item.label}
                </a>
              </li>
            ))}
          </ul>
        </nav>

        <button
          type="button"
          className="app-header__toggle"
          onClick={() => setNavOpen(!navOpen)}
          aria-controls="main-navigation"
          aria-expanded={navOpen}
        >
          <span className="app-header__toggle-line" aria-hidden="true"></span>
          <span className="app-header__toggle-line" aria-hidden="true"></span>
          <span className="app-header__toggle-line" aria-hidden="true"></span>
          <span className="sr-only">Ouvrir la navigation</span>
        </button>
      </div>
    </header>
  )
}
