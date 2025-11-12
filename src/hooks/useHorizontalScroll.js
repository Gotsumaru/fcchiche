/**
 * Hook for horizontal scroll functionality
 * Manages scroll behavior for match cards and result cards
 */

import { useEffect, useRef } from 'react'

export function useHorizontalScroll(containerRef) {
  const trackRef = useRef(null)
  const SCROLL_STEP = 320 // pixels to scroll per click

  useEffect(() => {
    const container = containerRef?.current
    if (!container) return

    // Find the track element
    const track = container.querySelector('.home-scroll__track')
    if (!track) return

    // Find scroll buttons
    const prevBtn = container.querySelector('[data-action="scroll-prev"]')
    const nextBtn = container.querySelector('[data-action="scroll-next"]')

    if (!prevBtn || !nextBtn) return

    // Scroll left
    const handlePrevClick = () => {
      track.scrollBy({
        left: -SCROLL_STEP,
        behavior: 'smooth'
      })
    }

    // Scroll right
    const handleNextClick = () => {
      track.scrollBy({
        left: SCROLL_STEP,
        behavior: 'smooth'
      })
    }

    prevBtn.addEventListener('click', handlePrevClick)
    nextBtn.addEventListener('click', handleNextClick)

    return () => {
      prevBtn.removeEventListener('click', handlePrevClick)
      nextBtn.removeEventListener('click', handleNextClick)
    }
  }, [containerRef])
}
