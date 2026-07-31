(() => {
  const cfg = SITE_CONFIG;

  /* ---------------------------------------------------------------------
     Config values → DOM
     --------------------------------------------------------------------- */
  document.querySelectorAll('.js-years, #stat-years').forEach(el => { el.textContent = cfg.yearsInBusiness; });
  document.querySelectorAll('.js-rating, #stat-rating').forEach(el => { el.textContent = cfg.googleRating; });
  document.querySelectorAll('.js-review-count').forEach(el => { el.textContent = cfg.reviewCount; });

  /* ---------------------------------------------------------------------
     Hotspot service selector
     --------------------------------------------------------------------- */
  // Maps a hotspot's service to the matching step-2 chip, so "Diese
  // Leistung anfragen" actually preselects it in the form as promised.
  const HOTSPOT_TO_CHIP = {
    'Lack & Politur': 'Exterieur-Pflege',
    'Dellen & Hagel': 'Dellen / Hagelschaden',
    'Lackierarbeiten': 'Lackierarbeiten',
    'Felgen & Reifen': 'Felgenaufbereitung',
    'Innenraum': 'Interieur-Pflege',
    'Lederreparatur': 'Lederreparatur',
    'Ozonbehandlung': 'Ozonbehandlung'
  };

  const dotsHost = document.getElementById('hotspot-dots');
  let activeSpot = 0;

  function renderDots() {
    dotsHost.innerHTML = '';
    cfg.spots.forEach((s, i) => {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'hotspot-dot' + (i === activeSpot ? ' active' : '');
      btn.style.left = s.x + '%';
      btn.style.top = s.y + '%';
      btn.innerHTML = '<span class="bubble"><span class="ring"></span>' + s.num + '</span>';
      btn.addEventListener('click', () => { activeSpot = i; renderDots(); renderDetail(); });
      dotsHost.appendChild(btn);
    });
  }

  const hsTag = document.getElementById('hs-tag');
  const hsCount = document.getElementById('hs-count');
  const hsTitle = document.getElementById('hs-title');
  const hsLead = document.getElementById('hs-lead');
  const hsBullets = document.getElementById('hs-bullets');
  const hsRequest = document.getElementById('hs-request');

  function renderDetail() {
    const s = cfg.spots[activeSpot];
    hsTag.textContent = s.tag;
    hsCount.textContent = s.num + ' / 0' + cfg.spots.length;
    hsTitle.textContent = s.title;
    hsLead.textContent = s.lead;
    hsBullets.innerHTML = '';
    s.bullets.forEach(b => {
      const li = document.createElement('li');
      li.innerHTML = '<span class="swash"></span><span>' + b + '</span>';
      hsBullets.appendChild(li);
    });
    hsRequest.onclick = () => {
      goToStep(2);
      const chipLabel = HOTSPOT_TO_CHIP[s.label];
      if (chipLabel) selectChip(chipLabel);
    };
  }

  renderDots();
  renderDetail();

  /* ---------------------------------------------------------------------
     Vorher / Nachher — case switcher + draggable slider
     --------------------------------------------------------------------- */
  const caseListHost = document.getElementById('case-list');
  const baFrame = document.getElementById('ba-frame');
  const baAfter = document.getElementById('ba-after');
  const baHandle = document.getElementById('ba-handle');
  const baCaseName = document.getElementById('ba-case-name');
  const baCaseNote = document.getElementById('ba-case-note');

  let activeCase = 0;
  let baPercent = cfg.beforeAfterStart;

  function renderCases() {
    caseListHost.innerHTML = '';
    cfg.cases.forEach((c, i) => {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'case-btn' + (i === activeCase ? ' active' : '');
      btn.innerHTML = c.name + '<span class="meta">' + c.meta + '</span>';
      btn.addEventListener('click', () => { activeCase = i; renderCases(); renderCaseCaption(); });
      caseListHost.appendChild(btn);
    });
  }

  const baImgBefore = document.getElementById('ba-img-before');
  const baImgAfter = document.getElementById('ba-img-after');

  function renderCaseCaption() {
    const c = cfg.cases[activeCase];
    baCaseName.textContent = c.name;
    baCaseNote.textContent = c.note;
    // Swap the photographs too, not just the caption — otherwise every case
    // shows the same pair.
    if (c.before) baImgBefore.src = c.before;
    if (c.after) baImgAfter.src = c.after;
  }

  function setBA(percent) {
    baPercent = Math.max(1.5, Math.min(98.5, percent));
    baAfter.style.clipPath = `inset(0 0 0 ${baPercent}%)`;
    baHandle.style.left = baPercent + '%';
    baFrame.setAttribute('aria-valuenow', Math.round(baPercent));
  }

  function baPercentFromClientX(clientX) {
    const r = baFrame.getBoundingClientRect();
    return ((clientX - r.left) / r.width) * 100;
  }

  let dragging = false;

  baFrame.addEventListener('pointerdown', e => {
    dragging = true;
    // Route every subsequent move/up to this element even if the pointer
    // leaves the frame (or the window) mid-drag.
    if (baFrame.setPointerCapture) {
      try { baFrame.setPointerCapture(e.pointerId); } catch (_) {}
    }
    // Stops the browser starting a native image-drag on the photos, which
    // otherwise swallows the gesture the moment the pointer moves.
    e.preventDefault();
    setBA(baPercentFromClientX(e.clientX));
  });

  baFrame.addEventListener('pointermove', e => {
    if (!dragging) return;
    e.preventDefault();
    setBA(baPercentFromClientX(e.clientX));
  });

  const endDrag = e => {
    if (!dragging) return;
    dragging = false;
    if (baFrame.releasePointerCapture && e && e.pointerId != null) {
      try { baFrame.releasePointerCapture(e.pointerId); } catch (_) {}
    }
  };
  baFrame.addEventListener('pointerup', endDrag);
  baFrame.addEventListener('pointercancel', endDrag);
  // Belt and braces: a pointerup that lands outside the frame still ends it.
  window.addEventListener('pointerup', endDrag);

  // Native drag fires before pointermove in some browsers; refuse it outright.
  baFrame.addEventListener('dragstart', e => e.preventDefault());

  // Keyboard access — the slider is a real control, so it needs to work
  // without a pointer at all.
  baFrame.setAttribute('tabindex', '0');
  baFrame.setAttribute('role', 'slider');
  baFrame.setAttribute('aria-label', 'Vergleich vorher / nachher');
  baFrame.setAttribute('aria-valuemin', '0');
  baFrame.setAttribute('aria-valuemax', '100');
  baFrame.addEventListener('keydown', e => {
    const step = e.shiftKey ? 10 : 2;
    if (e.key === 'ArrowLeft') { setBA(baPercent - step); e.preventDefault(); }
    else if (e.key === 'ArrowRight') { setBA(baPercent + step); e.preventDefault(); }
    else if (e.key === 'Home') { setBA(2); e.preventDefault(); }
    else if (e.key === 'End') { setBA(98); e.preventDefault(); }
  });

  renderCases();
  renderCaseCaption();
  setBA(baPercent);

  /* ---------------------------------------------------------------------
     Multi-step request form
     --------------------------------------------------------------------- */
  const stepTracker = document.getElementById('step-tracker');
  const formSteps = Array.from(document.querySelectorAll('.form-step'));
  const prevBtn = document.getElementById('form-prev');
  const nextBtn = document.getElementById('form-next');
  const formHint = document.getElementById('form-hint');
  const chipRow = document.getElementById('chip-row');
  const requestForm = document.getElementById('request-form');

  let currentStep = 1;
  const pickedChips = new Set();

  function renderTracker() {
    stepTracker.innerHTML = '';
    cfg.formSteps.forEach((label, i) => {
      const n = i + 1;
      const done = n < currentStep;
      const current = n === currentStep;
      const item = document.createElement('div');
      item.className = 'step-item' + (done ? ' done' : '') + (current ? ' current' : '');
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'step-btn';
      btn.innerHTML = `<span class="dot">${done ? '✓' : String(n).padStart(2, '0')}</span><span class="step-label">${label}</span>`;
      btn.addEventListener('click', () => goToStep(n));
      item.appendChild(btn);
      const line = document.createElement('span');
      line.className = 'line';
      item.appendChild(line);
      stepTracker.appendChild(item);
    });
  }

  function renderChips() {
    chipRow.innerHTML = '';
    cfg.chips.forEach(label => {
      const btn = document.createElement('button');
      btn.type = 'button';
      const on = pickedChips.has(label);
      btn.className = 'chip' + (on ? ' active' : '');
      btn.innerHTML = '<span class="swash"></span>' + label;
      btn.addEventListener('click', () => {
        if (pickedChips.has(label)) pickedChips.delete(label); else pickedChips.add(label);
        renderChips();
      });
      chipRow.appendChild(btn);
    });
  }

  function selectChip(label) {
    pickedChips.add(label);
    renderChips();
  }

  function goToStep(n) {
    currentStep = Math.max(1, Math.min(3, n));
    formSteps.forEach(s => { s.hidden = Number(s.dataset.step) !== currentStep; });
    prevBtn.disabled = currentStep === 1;
    formHint.textContent = cfg.formHints[currentStep - 1];
    nextBtn.querySelector('span') && nextBtn.querySelector('span').remove();
    nextBtn.innerHTML = currentStep === 3
      ? 'Anfrage senden'
      : 'Weiter <span class="btn-arrow">→</span>';
    renderTracker();
  }

  prevBtn.addEventListener('click', () => goToStep(currentStep - 1));

  requestForm.addEventListener('submit', e => {
    e.preventDefault();
    if (currentStep < 3) {
      goToStep(currentStep + 1);
      return;
    }
    // Front-end only: no backend is wired up yet. Swap this for a real
    // submission (fetch to an endpoint, mailto fallback, etc.) at launch.
    nextBtn.textContent = 'Danke, wird geprüft ...';
    nextBtn.disabled = true;
  });

  renderChips();
  goToStep(1);

  /* ---------------------------------------------------------------------
     Mobile navigation
     --------------------------------------------------------------------- */
  const navToggle = document.getElementById('nav-toggle');
  const mainNav = document.getElementById('main-nav');

  function setNav(open) {
    mainNav.classList.toggle('is-open', open);
    navToggle.setAttribute('aria-expanded', String(open));
    navToggle.setAttribute('aria-label', open ? 'Menü schließen' : 'Menü öffnen');
  }

  navToggle.addEventListener('click', () => {
    setNav(navToggle.getAttribute('aria-expanded') !== 'true');
  });

  // Tapping a link should navigate and close, not leave the panel covering
  // the section it just jumped to.
  mainNav.addEventListener('click', e => {
    if (e.target.closest('a')) setNav(false);
  });

  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') setNav(false);
  });

  // Returning to the desktop layout must clear the mobile state, otherwise
  // the panel stays stuck open behind the desktop nav.
  const navBreak = window.matchMedia('(min-width: 981px)');
  const onNavBreak = () => { if (navBreak.matches) setNav(false); };
  navBreak.addEventListener ? navBreak.addEventListener('change', onNavBreak)
                            : navBreak.addListener(onNavBreak);

  /* ---------------------------------------------------------------------
     Sticky request bar
     --------------------------------------------------------------------- */
  const stickyBar = document.getElementById('sticky-bar');
  function updateStickyBar() {
    const shouldShow = (window.scrollY || document.documentElement.scrollTop) > 720;
    stickyBar.classList.toggle('visible', shouldShow);
  }
  window.addEventListener('scroll', updateStickyBar, { passive: true });
  updateStickyBar();

  /* ---------------------------------------------------------------------
     Scroll reveal
     --------------------------------------------------------------------- */
  const revealTargets = document.querySelectorAll('.rv, .rv-in');
  if ('IntersectionObserver' in window && revealTargets.length) {
    const io = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.15, rootMargin: '0px 0px -10% 0px' });
    revealTargets.forEach(el => io.observe(el));
  } else {
    revealTargets.forEach(el => el.classList.add('is-visible'));
  }
})();
