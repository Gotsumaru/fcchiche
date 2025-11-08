import { useState, useEffect } from 'react';

export function Header({ activeSection, onNavigate }) {
  const [isScrolled, setIsScrolled] = useState(false);
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);

  useEffect(() => {
    const handleScroll = () => {
      setIsScrolled(window.scrollY > 24);
    };

    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  const handleNavClick = (section) => {
    onNavigate(section);
    setMobileMenuOpen(false);
  };

  const navItems = [
    { id: 'home', label: 'Accueil' },
    { id: 'results', label: 'Résultats' },
    { id: 'calendar', label: 'Calendrier' },
    { id: 'ranking', label: 'Classement' },
    { id: 'club', label: 'Le club' },
  ];

  return (
    <header className={`topbar ${isScrolled ? 'scrolled' : ''}`}>
      <div className="topbar-content">
        <a className="brand" onClick={() => handleNavClick('home')} style={{ cursor: 'pointer' }}>
          <div className="brand-mark">FC</div>
          <div className="brand-text">
            <span>FC Chiché</span>
            <small>Depuis 1946</small>
          </div>
        </a>

        <nav className="nav-desktop">
          {navItems.map((item) => (
            <a
              key={item.id}
              className={`nav-link ${activeSection === item.id ? 'active' : ''}`}
              onClick={() => handleNavClick(item.id)}
              style={{ cursor: 'pointer' }}
            >
              {item.label}
            </a>
          ))}
        </nav>

        <button
          className="nav-cta"
          onClick={() => handleNavClick('calendar')}
        >
          Billetterie
        </button>

        <button
          className="nav-toggle"
          onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
          aria-label="Menu"
        >
          {mobileMenuOpen ? '✕' : '☰'}
        </button>
      </div>
    </header>
  );
}
