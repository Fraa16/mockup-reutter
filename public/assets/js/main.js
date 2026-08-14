/**
 * Fahrzeugpflege Reutter — Frontend-Verhalten.
 *
 * Grundregel: Die Seite ist ohne dieses Skript vollstaendig bedienbar. Inhalte
 * werden serverseitig gerendert, hier wird nur umgeschaltet, gezogen und
 * eingeklappt. Jedes Modul prueft zuerst, ob sein DOM ueberhaupt da ist —
 * die Unterseiten haben nicht alle Sektionen.
 */
(() => {
  'use strict';

  /* ---------------------------------------------------------------------
     Leistungs-Hotspot
     Alle Panels stehen im HTML; hier wird nur sichtbar geschaltet.
     --------------------------------------------------------------------- */
  const hotspotHost = document.getElementById('hotspot-dots');
  if (hotspotHost) {
    const dots = Array.from(hotspotHost.querySelectorAll('.hotspot-dot'));
    const panels = Array.from(document.querySelectorAll('.hs-panel'));

    const zeige = (index) => {
      dots.forEach((dot, i) => {
        const aktiv = i === index;
        dot.classList.toggle('active', aktiv);
        dot.setAttribute('aria-pressed', String(aktiv));
      });
      panels.forEach((panel, i) => {
        panel.hidden = i !== index;
        panel.classList.toggle('is-active', i === index);
      });
    };

    dots.forEach((dot, i) => {
      dot.addEventListener('click', () => zeige(i));
      // Pfeiltasten wandern durch die Bereiche, ohne dass man sieben Mal Tab
      // druecken muss.
      dot.addEventListener('keydown', (e) => {
        const richtung = e.key === 'ArrowRight' || e.key === 'ArrowDown' ? 1
                       : e.key === 'ArrowLeft'  || e.key === 'ArrowUp'   ? -1 : 0;
        if (richtung === 0) return;
        e.preventDefault();
        const naechster = (i + richtung + dots.length) % dots.length;
        zeige(naechster);
        dots[naechster].focus();
      });
    });
  }

  /* ---------------------------------------------------------------------
     Vorher / Nachher
     --------------------------------------------------------------------- */
  const baFrame = document.getElementById('ba-frame');
  if (baFrame) {
    const baAfter    = document.getElementById('ba-after');
    const baHandle   = document.getElementById('ba-handle');
    const imgBefore  = document.getElementById('ba-img-before');
    const imgAfter   = document.getElementById('ba-img-after');
    const caseName   = document.getElementById('ba-case-name');
    const caseNote   = document.getElementById('ba-case-note');
    const caseKnoepfe = Array.from(document.querySelectorAll('.case-btn'));

    let position = Number(baFrame.dataset.start) || 52;

    const setze = (prozent) => {
      position = Math.max(1.5, Math.min(98.5, prozent));
      baAfter.style.clipPath = `inset(0 0 0 ${position}%)`;
      baHandle.style.left = position + '%';
      baFrame.setAttribute('aria-valuenow', String(Math.round(position)));
    };

    const ausX = (clientX) => {
      const r = baFrame.getBoundingClientRect();
      return ((clientX - r.left) / r.width) * 100;
    };

    let zieht = false;

    baFrame.addEventListener('pointerdown', (e) => {
      zieht = true;
      // Bewegungen weiterhin empfangen, auch wenn der Zeiger den Rahmen verlaesst.
      try { baFrame.setPointerCapture(e.pointerId); } catch { /* aelterer Browser */ }
      // Verhindert, dass der Browser stattdessen das Bild zu ziehen beginnt.
      e.preventDefault();
      setze(ausX(e.clientX));
    });

    baFrame.addEventListener('pointermove', (e) => {
      if (!zieht) return;
      e.preventDefault();
      setze(ausX(e.clientX));
    });

    const beende = (e) => {
      if (!zieht) return;
      zieht = false;
      if (e && e.pointerId != null) {
        try { baFrame.releasePointerCapture(e.pointerId); } catch { /* egal */ }
      }
    };
    baFrame.addEventListener('pointerup', beende);
    baFrame.addEventListener('pointercancel', beende);
    window.addEventListener('pointerup', beende);
    baFrame.addEventListener('dragstart', (e) => e.preventDefault());

    // Der Vergleich ist ein echtes Bedienelement und muss ohne Maus gehen.
    baFrame.setAttribute('tabindex', '0');
    baFrame.setAttribute('role', 'slider');
    baFrame.setAttribute('aria-label', 'Vergleich vorher / nachher');
    baFrame.setAttribute('aria-valuemin', '0');
    baFrame.setAttribute('aria-valuemax', '100');
    baFrame.addEventListener('keydown', (e) => {
      const schritt = e.shiftKey ? 10 : 2;
      if (e.key === 'ArrowLeft')       { setze(position - schritt); e.preventDefault(); }
      else if (e.key === 'ArrowRight') { setze(position + schritt); e.preventDefault(); }
      else if (e.key === 'Home')       { setze(2);  e.preventDefault(); }
      else if (e.key === 'End')        { setze(98); e.preventDefault(); }
    });

    caseKnoepfe.forEach((btn, i) => {
      btn.addEventListener('click', () => {
        caseKnoepfe.forEach((b, j) => {
          b.classList.toggle('active', i === j);
          b.setAttribute('aria-pressed', String(i === j));
        });
        imgBefore.src = btn.dataset.vorher;
        imgAfter.src  = btn.dataset.nachher;
        caseName.textContent = btn.dataset.name;
        caseNote.textContent = btn.dataset.note;
      });
    });

    setze(position);
  }

  /* ---------------------------------------------------------------------
     Anfrageformular — aus den drei Fieldsets wird der Stepper
     Ohne dieses Skript bleiben alle drei Schritte sichtbar und absendbar.
     --------------------------------------------------------------------- */
  const formular = document.getElementById('request-form');
  if (formular) {
    const schritte = Array.from(formular.querySelectorAll('.form-step'));
    const zurueck  = document.getElementById('form-prev');
    const weiter   = document.getElementById('form-next');
    const hinweis  = document.getElementById('form-hint');
    const tracker  = document.getElementById('step-tracker');
    const labels   = ['Fahrzeug', 'Leistung', 'Kontakt'];
    const hinweise = [
      'Schritt 1 von 3',
      'Schritt 2 von 3 · Mehrfachauswahl möglich',
      'Letzter Schritt'
    ];

    let aktuell = 1;

    const zeichneTracker = () => {
      tracker.innerHTML = '';
      tracker.removeAttribute('aria-hidden');
      labels.forEach((label, i) => {
        const n = i + 1;
        const fertig = n < aktuell;
        const item = document.createElement('div');
        item.className = 'step-item' + (fertig ? ' done' : '') + (n === aktuell ? ' current' : '');
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'step-btn';
        btn.innerHTML = `<span class="dot">${fertig ? '✓' : String(n).padStart(2, '0')}</span>` +
                        `<span class="step-label">${label}</span>`;
        btn.addEventListener('click', () => geheZu(n));
        item.append(btn, Object.assign(document.createElement('span'), { className: 'line' }));
        tracker.appendChild(item);
      });
    };

    const geheZu = (n) => {
      aktuell = Math.max(1, Math.min(schritte.length, n));
      schritte.forEach((s) => { s.hidden = Number(s.dataset.step) !== aktuell; });
      zurueck.hidden = aktuell === 1;
      hinweis.textContent = hinweise[aktuell - 1];
      weiter.innerHTML = aktuell === schritte.length
        ? 'Anfrage senden'
        : 'Weiter <span class="btn-arrow" aria-hidden="true">→</span>';
      zeichneTracker();
    };

    zurueck.addEventListener('click', () => geheZu(aktuell - 1));

    formular.addEventListener('submit', (e) => {
      // Zwischenschritte schicken nichts ab, sie blaettern nur weiter.
      if (aktuell < schritte.length) {
        e.preventDefault();
        geheZu(aktuell + 1);
      }
      // Im letzten Schritt laeuft der normale POST an den Server.
    });

    // Ein Hotspot-Link kann eine Leistung vorauswaehlen:
    // /kontakt/#anfrage?leistung=Lederreparatur
    const gewuenscht = new URLSearchParams(window.location.search).get('leistung')
                    || new URLSearchParams(window.location.hash.split('?')[1] || '').get('leistung');
    if (gewuenscht) {
      const treffer = formular.querySelector(`input[name="leistungen[]"][value="${CSS.escape(gewuenscht)}"]`);
      if (treffer) {
        treffer.checked = true;
        geheZu(2);
      }
    }

    if (aktuell === 1) geheZu(1);
  }

  /* ---------------------------------------------------------------------
     Mobile Navigation
     --------------------------------------------------------------------- */
  const navToggle = document.getElementById('nav-toggle');
  const mainNav   = document.getElementById('main-nav');
  if (navToggle && mainNav) {
    const setzeNav = (offen) => {
      mainNav.classList.toggle('is-open', offen);
      navToggle.setAttribute('aria-expanded', String(offen));
      navToggle.setAttribute('aria-label', offen ? 'Menü schließen' : 'Menü öffnen');
    };

    navToggle.addEventListener('click', () => {
      setzeNav(navToggle.getAttribute('aria-expanded') !== 'true');
    });

    // Nach dem Antippen eines Links schliessen, sonst verdeckt das Panel den
    // Abschnitt, zu dem es gerade gesprungen ist.
    mainNav.addEventListener('click', (e) => {
      if (e.target.closest('a')) setzeNav(false);
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') setzeNav(false);
    });

    // Zurueck am Desktop muss den mobilen Zustand aufraeumen, sonst haengt das
    // Panel offen hinter der normalen Navigation.
    const breite = window.matchMedia('(min-width: 981px)');
    breite.addEventListener('change', () => { if (breite.matches) setzeNav(false); });
  }

  /* ---------------------------------------------------------------------
     Sticky-Anfrageleiste
     --------------------------------------------------------------------- */
  const stickyBar = document.getElementById('sticky-bar');
  if (stickyBar) {
    const schwelle = Number(stickyBar.dataset.abScroll) || 600;
    const pruefe = () => {
      const y = window.scrollY || document.documentElement.scrollTop;
      const sichtbar = y > schwelle;
      stickyBar.classList.toggle('visible', sichtbar);
      // Die Leiste ist fixiert und wuerde sonst die letzte Fusszeile verdecken.
      // Das CSS macht daraus einen Platzhalter am Ende des Fusses.
      document.body.classList.toggle('sticky-an', sichtbar);
    };
    window.addEventListener('scroll', pruefe, { passive: true });
    pruefe();
  }

  /* ---------------------------------------------------------------------
     Einblenden beim Scrollen
     --------------------------------------------------------------------- */
  const reveals = document.querySelectorAll('.rv, .rv-in');
  if (reveals.length) {
    if ('IntersectionObserver' in window) {
      const beobachter = new IntersectionObserver((eintraege) => {
        eintraege.forEach((eintrag) => {
          if (!eintrag.isIntersecting) return;
          eintrag.target.classList.add('is-visible');
          beobachter.unobserve(eintrag.target);
        });
      }, { threshold: 0.15, rootMargin: '0px 0px -10% 0px' });
      reveals.forEach((el) => beobachter.observe(el));
    } else {
      reveals.forEach((el) => el.classList.add('is-visible'));
    }
  }
})();
