/**
 * COMMON JAVASCRIPT - SHARED ACROSS ALL PAGES
 */

// Masquer l'indicateur de scroll après le début du défilement
window.addEventListener('scroll', () => {
  const scrollIndicator = document.querySelector('.scroll-indicator');
  if (scrollIndicator) {
    if (window.scrollY > 100) {
      scrollIndicator.style.opacity = '0';
      scrollIndicator.style.transition = 'opacity 0.5s ease';
    } else if (window.scrollY === 0) {
      scrollIndicator.style.opacity = '1';
    }
  }
});

// Effet parallaxe sur l'image de la section événement
window.addEventListener('scroll', () => {
  const parallaxImage = document.querySelector('.parallax-image');
  if (parallaxImage) {
    const rect = parallaxImage.getBoundingClientRect();

    if (rect.top < window.innerHeight && rect.bottom > 0) {
      const offset = (window.innerHeight - rect.top) * 0.1;
      parallaxImage.style.transform = `translateY(${offset}px)`;
    }
  }
});
