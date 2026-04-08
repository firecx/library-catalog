// Enable wheel-to-scroll horizontally and drag-to-scroll for .books-case
(function(){
  const strips = document.querySelectorAll('.books-case');
  strips.forEach(strip => {
    // Note: wheel handler removed so page scroll behaves normally.

    // Drag to scroll (desktop)
    let isDown = false;
    let startX;
    let scrollLeft;

    strip.addEventListener('mousedown', (e) => {
      isDown = true;
      strip.classList.add('dragging');
      startX = e.pageX - strip.offsetLeft;
      scrollLeft = strip.scrollLeft;
    });

    strip.addEventListener('mouseleave', () => {
      isDown = false;
      strip.classList.remove('dragging');
    });

    strip.addEventListener('mouseup', () => {
      isDown = false;
      strip.classList.remove('dragging');
    });

    strip.addEventListener('mousemove', (e) => {
      if (!isDown) return;
      e.preventDefault();
      const x = e.pageX - strip.offsetLeft;
      const walk = (x - startX) * 1; // scroll-fast multiplier
      strip.scrollLeft = scrollLeft - walk;
    });

    // Touch: allow native swipe but prevent vertical->horizontal surprises
    // (mostly rely on browser touch scrolling)
  });
})();

// Add desktop scroll buttons for each .books-case
(function(){
  function createButton(direction){
    const btn = document.createElement('button');
    btn.className = `scroll-button scroll-${direction}`;
    btn.setAttribute('aria-label', direction === 'left' ? 'Scroll left' : 'Scroll right');
    btn.innerHTML = direction === 'left' ? '&#9664;' : '&#9654;';
    return btn;
  }

  function initButtons(){
    const strips = document.querySelectorAll('.books-case');
    strips.forEach(strip => {
      const panel = strip.closest('.books-panel') || strip.parentElement;
      if (!panel) return;

      // avoid duplicating buttons
      if (panel.querySelector('.scroll-left')) return;

      const left = createButton('left');
      const right = createButton('right');

      left.addEventListener('click', () => {
        const card = strip.querySelector('.book-card');
        const gap = parseInt(getComputedStyle(strip).gap) || 20;
        const amount = (card ? card.offsetWidth : 240) + gap;
        // compute safe target and use scrollTo so we clamp to 0
        const target = Math.max(0, Math.round(strip.scrollLeft - amount));
        strip.scrollTo({ left: target, behavior: 'smooth' });
        // debug
        setTimeout(updateEdgeButtons, 450);
      });

      right.addEventListener('click', () => {
        const card = strip.querySelector('.book-card');
        const gap = parseInt(getComputedStyle(strip).gap) || 20;
        const amount = (card ? card.offsetWidth : 240) + gap;
        const target = Math.min(strip.scrollWidth - strip.clientWidth, Math.round(strip.scrollLeft + amount));
        strip.scrollTo({ left: target, behavior: 'smooth' });
        setTimeout(updateEdgeButtons, 450);
      });

      panel.appendChild(left);
      panel.appendChild(right);

      // hide left/right when at edges so UI feels correct
      function updateEdgeButtons(){
        // Determine whether any card is hidden to the left/right by checking
        // the first and last card bounding boxes against the strip viewport.
        const firstCard = strip.querySelector('.book-card');
        const lastCard = strip.querySelector('.book-card:last-child');
        const stripRect = strip.getBoundingClientRect();
        const eps = 1; // tolerance for fractional pixels

        let leftVisible = false;
        let rightVisible = false;

        if (firstCard) {
          const firstRect = firstCard.getBoundingClientRect();
          // if left edge of first card is left of strip viewport, it's hidden
          leftVisible = firstRect.left < stripRect.left - eps;
        }

        if (lastCard) {
          const lastRect = lastCard.getBoundingClientRect();
          // if right edge of last card is right of strip viewport, it's hidden
          rightVisible = lastRect.right > stripRect.right + eps;
        }

        left.style.visibility = leftVisible ? 'visible' : 'hidden';
        right.style.visibility = rightVisible ? 'visible' : 'hidden';
      }

      // re-evaluate when images load (they can change layout)
      const imgs = strip.querySelectorAll('img');
      imgs.forEach(img => img.addEventListener('load', () => setTimeout(updateEdgeButtons, 50)));
      // re-evaluate on resize as layout changes
      window.addEventListener('resize', () => setTimeout(updateEdgeButtons, 50));

      // update on scroll and on init. Use a debounce to catch smooth scrolling.
      let scrollDebounce;
      strip.addEventListener('scroll', () => {
        if (scrollDebounce) clearTimeout(scrollDebounce);
        scrollDebounce = setTimeout(updateEdgeButtons, 60);
      }, { passive: true });
      // call after a short delay to account for layout
      setTimeout(updateEdgeButtons, 50);

      // show/hide based on viewport (desktop only)
      function updateVisibility(){
        const show = window.innerWidth > 768;
        left.style.display = show ? 'flex' : 'none';
        right.style.display = show ? 'flex' : 'none';
      }
      updateVisibility();
      window.addEventListener('resize', updateVisibility);

      // Auto-scroll left every 5-10s when applicable. Pauses on hover/drag.
      let autoTimer = null;
      let autoActive = true;

      function autoScrollOnce(){
        if (window.innerWidth <= 768) return; // skip mobile
        if (strip.scrollWidth <= strip.clientWidth) return; // nothing to scroll
        if (strip.classList.contains('dragging')) return; // user interacting
        const stripRect = strip.getBoundingClientRect();
        const maxScrollLeft = strip.scrollWidth - strip.clientWidth;
        const eps = 1;

        // If we're already at (or very near) the right edge, wrap to start
        if (strip.scrollLeft >= maxScrollLeft - eps) {
          strip.scrollTo({ left: 0, behavior: 'smooth' });
          setTimeout(updateEdgeButtons, 500);
          return;
        }

        const lastCard = strip.querySelector('.book-card:last-child');
        if (!lastCard) return;
        const lastRect = lastCard.getBoundingClientRect();

        // only scroll right if at least one card is hidden to the right
        if (!(lastRect.right > stripRect.right + eps)) return;

        const gap = parseInt(getComputedStyle(strip).gap) || 20;
        const amount = (lastCard.offsetWidth || 240) + gap;
        const target = Math.min(maxScrollLeft, Math.round(strip.scrollLeft + amount));
        strip.scrollTo({ left: target, behavior: 'smooth' });
        setTimeout(updateEdgeButtons, 500);

        // if we've reached the right edge with this step, wrap back to the first element
        if (target >= maxScrollLeft - eps) {
          setTimeout(() => {
            strip.scrollTo({ left: 0, behavior: 'smooth' });
            setTimeout(updateEdgeButtons, 500);
          }, 800);
        }
      }

      function scheduleAuto(){
        if (autoTimer) clearTimeout(autoTimer);
        if (!autoActive) return;
        const delay = 5000 + Math.floor(Math.random() * 5001); // 5000-10000ms
        autoTimer = setTimeout(() => {
          autoScrollOnce();
          scheduleAuto();
        }, delay);
      }

      strip.addEventListener('mouseenter', () => { autoActive = false; if (autoTimer) clearTimeout(autoTimer); });
      strip.addEventListener('mouseleave', () => { autoActive = true; scheduleAuto(); });

      // Reset auto schedule after user clicks navigation
      left.addEventListener('click', scheduleAuto);
      right.addEventListener('click', scheduleAuto);

      // start auto behaviour
      scheduleAuto();
    });
  }

  if (document.readyState === 'loading'){
    document.addEventListener('DOMContentLoaded', initButtons);
  } else {
    initButtons();
  }
})();
