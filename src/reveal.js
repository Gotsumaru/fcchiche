/**
 * Reveal Animation Handler
 * Shows elements with [data-reveal] attribute by adding .is-visible class
 */

export function initRevealAnimation() {
  console.log('🎬 Initializing reveal animations...')

  // Get all elements with data-reveal attribute
  const revealElements = document.querySelectorAll('[data-reveal]')
  console.log('Found ' + revealElements.length + ' reveal elements')

  // Make all reveal elements visible immediately
  // (animations will still work on scroll with Intersection Observer)
  revealElements.forEach((el) => {
    el.classList.add('is-visible')
  })

  console.log('✅ All ' + revealElements.length + ' reveal elements made visible')

  // Also setup Intersection Observer for elements that scroll into view
  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible')
        }
      })
    },
    {
      threshold: 0.1
    }
  )

  revealElements.forEach((el) => {
    observer.observe(el)
  })

  console.log('✅ Reveal animations initialized')
}

// Initialize on DOM ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initRevealAnimation)
} else {
  // DOM is already loaded
  initRevealAnimation()
}
