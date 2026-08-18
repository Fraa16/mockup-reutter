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
     Umschalter — eine Leiste aus Schaltern, darunter ebenso viele Tafeln

     Sechs Stellen auf der Website taten genau dasselbe: Schalter aktiv
     setzen, aria-pressed pflegen, die zugehoerige Tafel zeigen, mit den
     Pfeiltasten durchgehen. Unterschiedlich waren die Klassennamen und das,
     was nebenher noch passiert — eine Hoehe, ein paar Balkenbreiten. Genau
     dafuer ist `nebenbei` da.

     Ohne dieses Skript stehen alle Tafeln untereinander und sind lesbar.
     Deshalb wird beim Start einmal geschaltet, statt den Anfangszustand ins
     HTML zu schreiben — die Seite muss auch ohne JavaScript etwas zeigen.

     `schalter` und `tafeln` nehmen auch mehrere Wahlausdruecke: der Hotspot
     hat zwei Leisten (Marker im Bild, Chips fuers Handy) auf denselben
     Tafeln, der Querschnitt zwei Saetze Tafeln auf einer Leiste.

     @param {{bereich:string, schalter:string|string[], tafeln:string|string[],
              nebenbei?:(index:number, tafel:Element)=>void}} o
     @returns {((index:number)=>void)|null}
     --------------------------------------------------------------------- */
  const umschalter = ({ bereich, schalter, tafeln, nebenbei }) => {
    const wurzel = document.getElementById(bereich);
    if (!wurzel) return null;

    const saetze = (wahl) => (Array.isArray(wahl) ? wahl : [wahl])
      .map((w) => Array.from(wurzel.querySelectorAll(w)))
      .filter((satz) => satz.length > 0);

    const leisten = saetze(schalter);
    const platten = saetze(tafeln);
    if (leisten.length === 0 || platten.length === 0) return null;

    const zeige = (index) => {
      leisten.forEach((leiste) => leiste.forEach((el, i) => {
        el.classList.toggle('active', i === index);
        el.setAttribute('aria-pressed', String(i === index));
      }));
      platten.forEach((satz) => satz.forEach((el, i) => {
        el.hidden = i !== index;
        el.classList.toggle('is-active', i === index);
      }));
      if (nebenbei) nebenbei(index, platten[0][index]);
    };

    leisten.forEach((leiste) => leiste.forEach((el, i) => {
      el.addEventListener('click', () => zeige(i));
      // Pfeiltasten laufen durch die Leiste, damit man nicht sieben Mal Tab
      // druecken muss, um zum letzten Bereich zu kommen.
      el.addEventListener('keydown', (e) => {
        const richtung = ['ArrowRight', 'ArrowDown'].includes(e.key) ? 1
                       : ['ArrowLeft', 'ArrowUp'].includes(e.key) ? -1 : 0;
        if (richtung === 0) return;
        e.preventDefault();
        const naechster = (i + richtung + leiste.length) % leiste.length;
        zeige(naechster);
        leiste[naechster].focus();
      });
    }));

    // Welche Tafel im HTML als aktiv markiert ist, gibt den Anfangszustand vor.
    zeige(Math.max(0, platten[0].findIndex((el) => el.classList.contains('is-active'))));
    return zeige;
  };

  /* ---------------------------------------------------------------------
     Leistungs-Hotspot (Startseite)
     Marker im Bild und — unterhalb 640 px — eine Chipleiste darunter.
     --------------------------------------------------------------------- */
  if (document.getElementById('hotspot')) {
    // Die Chipleiste steht im HTML auf hidden: ohne dieses Skript waeren es
    // sieben Knoepfe, die nichts tun, waehrend darunter ohnehin alle
    // Bereiche untereinander lesbar sind.
    const chipleiste = document.getElementById('hotspot-chips');
    if (chipleiste) chipleiste.hidden = false;

    umschalter({
      bereich:  'hotspot',
      schalter: ['.hotspot-dot', '.hotspot-chip'],
      tafeln:   '.hs-panel',
    });
  }

  /* ---------------------------------------------------------------------
     Vorher / Nachher
     --------------------------------------------------------------------- */
  // Vier der sechs Leistungsseiten haben denselben Regler, die Startseite
  // zusaetzlich eine Fallauswahl. Deshalb einmal generisch ueber alle .ba-frame
  // statt vier Kopien derselben Zieh-Logik.
  document.querySelectorAll('.ba-frame').forEach((rahmen) => {
    const nachher = rahmen.querySelector('.layer-after');
    const griff   = rahmen.querySelector('.ba-handle');
    if (!nachher || !griff) return;

    const bildVorher  = rahmen.querySelector('.layer-before img');
    const bildNachher = rahmen.querySelector('.layer-after img');
    // Die Fallauswahl gehoert zur selben Sektion, gibt es aber nur auf der Startseite.
    const sektion   = rahmen.closest('section') || document;
    const faelle    = Array.from(sektion.querySelectorAll('.case-btn'));
    const fallName  = sektion.querySelector('#ba-case-name');
    const fallNotiz = sektion.querySelector('#ba-case-note');

    let position = Number(rahmen.dataset.start) || 50;

    const setze = (prozent) => {
      position = Math.max(1.5, Math.min(98.5, prozent));
      nachher.style.clipPath = `inset(0 0 0 ${position}%)`;
      griff.style.left = position + '%';
      rahmen.setAttribute('aria-valuenow', String(Math.round(position)));
    };

    const ausX = (clientX) => {
      const r = rahmen.getBoundingClientRect();
      return ((clientX - r.left) / r.width) * 100;
    };

    let zieht = false;

    rahmen.addEventListener('pointerdown', (e) => {
      zieht = true;
      // Bewegungen weiterhin empfangen, auch wenn der Zeiger den Rahmen verlaesst.
      try { rahmen.setPointerCapture(e.pointerId); } catch { /* aelterer Browser */ }
      // Verhindert, dass der Browser stattdessen das Bild zu ziehen beginnt.
      e.preventDefault();
      setze(ausX(e.clientX));
    });

    rahmen.addEventListener('pointermove', (e) => {
      if (!zieht) return;
      e.preventDefault();
      setze(ausX(e.clientX));
    });

    const beende = (e) => {
      if (!zieht) return;
      zieht = false;
      if (e && e.pointerId != null) {
        try { rahmen.releasePointerCapture(e.pointerId); } catch { /* egal */ }
      }
    };
    rahmen.addEventListener('pointerup', beende);
    rahmen.addEventListener('pointercancel', beende);
    window.addEventListener('pointerup', beende);
    rahmen.addEventListener('dragstart', (e) => e.preventDefault());

    // Der Vergleich ist ein echtes Bedienelement und muss ohne Maus gehen.
    rahmen.setAttribute('tabindex', '0');
    rahmen.setAttribute('role', 'slider');
    rahmen.setAttribute('aria-label', 'Vergleich vorher / nachher');
    rahmen.setAttribute('aria-valuemin', '0');
    rahmen.setAttribute('aria-valuemax', '100');
    rahmen.addEventListener('keydown', (e) => {
      const schritt = e.shiftKey ? 10 : 2;
      if (e.key === 'ArrowLeft')       { setze(position - schritt); e.preventDefault(); }
      else if (e.key === 'ArrowRight') { setze(position + schritt); e.preventDefault(); }
      else if (e.key === 'Home')       { setze(2);  e.preventDefault(); }
      else if (e.key === 'End')        { setze(98); e.preventDefault(); }
    });

    const tausche = (img, quelle, srcset) => {
      if (!img || !quelle) return;
      if (srcset) { img.srcset = srcset; } else { img.removeAttribute('srcset'); }
      img.src = quelle;
    };

    faelle.forEach((btn, i) => {
      btn.addEventListener('click', () => {
        faelle.forEach((b, j) => {
          b.classList.toggle('active', i === j);
          b.setAttribute('aria-pressed', String(i === j));
        });
        // srcset zuerst: solange dort etwas steht, ignoriert der Browser src.
        tausche(bildVorher,  btn.dataset.vorher,  btn.dataset.vorherSrcset);
        tausche(bildNachher, btn.dataset.nachher, btn.dataset.nachherSrcset);
        if (fallName)  fallName.textContent  = btn.dataset.name;
        if (fallNotiz) fallNotiz.textContent = btn.dataset.note;
      });
    });

    setze(position);
  });

  /* ---------------------------------------------------------------------
     Lack-Querschnitt (Fahrzeugpflege Exterieur)
     Politurstufe waehlen, die Abtragszone im Klarlack waechst mit. Die Hoehe
     je Stufe steht an der Tafel, damit sie neben ihrem Text gepflegt wird
     und nicht im Skript.
     --------------------------------------------------------------------- */
  const abtragszone = document.getElementById('abtragszone');
  umschalter({
    bereich:  'querschnitt',
    schalter: '.stufe',
    tafeln:   ['.stufen-tafel', '.schnitt-abtrag'],
    nebenbei: (index, tafel) => {
      if (abtragszone) abtragszone.style.height = (Number(tafel.dataset.abtragHoehe) || 20) + 'px';
    },
  });

  /* ---------------------------------------------------------------------
     Zonen-Waehler (Fahrzeugpflege Interieur)
     --------------------------------------------------------------------- */
  umschalter({ bereich: 'zonenkarte', schalter: '.zone', tafeln: '.zonen-tafel' });

  /* ---------------------------------------------------------------------
     Fingernagel-Tiefentest (Lackierarbeiten)
     Zustand waehlen, die rote Schadenszone waechst durch die Schichten. Die
     Breiten stehen wie beim Querschnitt an der Tafel.
     --------------------------------------------------------------------- */
  const schadenszonen = Array.from(document.querySelectorAll('#tiefentest .schadenszone'));
  umschalter({
    bereich:  'tiefentest',
    schalter: '.tiefe',
    tafeln:   '.vorgehen-tafel',
    nebenbei: (index, tafel) => schadenszonen.forEach((zone) => {
      const breite = Number(tafel.dataset[zone.dataset.schicht]) || 0;
      zone.style.width = breite + '%';
      zone.classList.toggle('ist-leer', breite === 0);
    }),
  });

  /* ---------------------------------------------------------------------
     Vier Schadensgrade (Lederreparatur)
     --------------------------------------------------------------------- */
  umschalter({ bereich: 'schadensgrad', schalter: '.grad', tafeln: '.grad-tafel' });

  /* ---------------------------------------------------------------------
     Bildraster der Galerie: Filter und Vergroessern
     Ohne dieses Skript stehen alle zwanzig Bilder in Normalgroesse da —
     der brauchbare Grundzustand, deshalb steckt nichts davon im HTML.
     --------------------------------------------------------------------- */
  const galerieFilter = document.getElementById('galerie-filter');
  const galerieRaster = document.getElementById('galerie-raster');
  if (galerieFilter && galerieRaster) {
    const knoepfe = Array.from(galerieFilter.querySelectorAll('.filter'));
    const kacheln = Array.from(galerieRaster.querySelectorAll('.kachel'));

    knoepfe.forEach((btn) => {
      btn.addEventListener('click', () => {
        const gewaehlt = btn.dataset.kategorie;
        knoepfe.forEach((b) => {
          const an = b === btn;
          b.classList.toggle('active', an);
          b.setAttribute('aria-pressed', String(an));
        });
        kacheln.forEach((k) => {
          k.hidden = gewaehlt !== '' && k.dataset.kategorie !== gewaehlt;
          // Eine ausgeblendete Kachel darf nicht vergroessert wiederkommen.
          if (k.hidden) {
            k.classList.remove('ist-gross');
            k.setAttribute('aria-pressed', 'false');
          }
        });
      });
    });

    kacheln.forEach((k) => {
      k.addEventListener('click', () => {
        const gross = k.classList.toggle('ist-gross');
        k.setAttribute('aria-pressed', String(gross));
        const zoom = k.querySelector('.kachel-zoom');
        if (zoom) zoom.textContent = gross ? 'schließen' : 'groß';
      });
    });
  }

  /* ---------------------------------------------------------------------
     Leistungsindex (Leistungen-Hub)
     Zeile ueberfahren oder fokussieren, rechts wechselt das Vorschaubild.
     Ohne dieses Skript bleibt das erste Bild stehen — die Zeilen sind Links
     und funktionieren unabhaengig davon.
     --------------------------------------------------------------------- */
  const leistungsindex = document.getElementById('leistungsindex');
  if (leistungsindex) {
    const zeilen = Array.from(leistungsindex.querySelectorAll('.index-zeile'));
    const bilder = Array.from(leistungsindex.querySelectorAll('.vorschau-bild'));
    const titel  = Array.from(leistungsindex.querySelectorAll('.vorschau-zeile'));
    let aktiv = 0;

    const zeige = (index, erzwingen = false) => {
      if (index === aktiv && !erzwingen) return;
      aktiv = index;
      zeilen.forEach((el, i) => el.classList.toggle('active', i === index));
      bilder.forEach((el, i) => el.classList.toggle('is-active', i === index));
      titel.forEach((el, i) => { el.hidden = i !== index; });
    };

    // Beim Start einmal erzwingen: ohne Skript stehen alle Bildunterschriften
    // untereinander, erst hier wird eine davon ausgewaehlt.
    zeige(0, true);

    zeilen.forEach((el, i) => {
      el.addEventListener('mouseenter', () => zeige(i));
      // Auch beim Durchtabben, sonst haengt das Bild bei Tastaturbedienung fest.
      el.addEventListener('focus', () => zeige(i));
    });
  }

  /* ---------------------------------------------------------------------
     Geruchsdiagnose (Ozonbehandlung)
     --------------------------------------------------------------------- */
  umschalter({ bereich: 'diagnose', schalter: '.chip', tafeln: '.quellen-tafel' });

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
      const letzter = aktuell === schritte.length;
      weiter.innerHTML = letzter
        ? 'Anfrage senden'
        : 'Weiter <span class="btn-arrow" aria-hidden="true">→</span>';
      // Auf den Zwischenschritten ist das kein Absendeknopf. Sonst prueft der
      // Browser beim Klick die Pflichtfelder aus Schritt 3, findet sie
      // ausgeblendet, kann sie nicht anspringen — und bricht ab, ohne dass
      // ein submit-Ereignis entsteht. Der Knopf tut dann gar nichts.
      weiter.type = letzter ? 'submit' : 'button';
      zeichneTracker();
    };

    zurueck.addEventListener('click', () => geheZu(aktuell - 1));

    weiter.addEventListener('click', (e) => {
      // Im letzten Schritt ist es ein echter Absendeknopf, dann nichts tun.
      if (aktuell >= schritte.length) return;
      /* preventDefault ist hier kein Zierrat: geheZu() setzt weiter.type auf
         "submit", sobald der letzte Schritt erreicht ist — und zwar noch
         waehrend dieses Klicks. Die Standardaktion wird erst nach den
         Zuhoerern ausgewertet, sie sieht also bereits den Absendeknopf und
         schickt ab. Sichtbar wurde das beim Sprung von Schritt 2 auf 3:
         der Browser prueft die eben eingeblendeten Pflichtfelder und stellt
         eine Meldung „Bitte fuellen Sie dieses Feld aus" ueber das Namensfeld,
         bevor jemand ein Zeichen tippen konnte. */
      e.preventDefault();
      geheZu(aktuell + 1);
    });

    // Enter in einem Textfeld soll weiterblaettern statt ins Leere zu laufen.
    // Im Textfeld der Beschreibung bleibt Enter ein Zeilenumbruch.
    formular.addEventListener('keydown', (e) => {
      if (e.key !== 'Enter' || aktuell >= schritte.length) return;
      const ziel = e.target;
      if (!(ziel instanceof HTMLInputElement) || ziel.type === 'checkbox') return;
      e.preventDefault();
      geheZu(aktuell + 1);
    });

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

    /* Nach einer abgewiesenen Anfrage stand die Meldung „Bitte tragen Sie Ihren
       Namen ein" ueber Schritt 1 — das Namensfeld liegt aber in Schritt 3 und
       war ausgeblendet. Man musste zweimal weiterblaettern, um zu finden, was
       beanstandet wird.

       Deshalb: zum ersten Schritt springen, in dem etwas fehlt, und das Feld
       gleich anspringen. Die Meldung oben bleibt stehen, sie hat role="alert"
       und wird ohnehin vorgelesen. */
    const ersterFehler = formular.querySelector('.field.has-error, .consent.has-error');
    if (ersterFehler) {
      const schritt = Number(ersterFehler.closest('.form-step')?.dataset.step || 0);
      if (schritt > 0) {
        geheZu(schritt);
      }
      /* Ein Bild spaeter, nicht sofort: die Antwort des Servers traegt
         #anfrage im Adressfeld, und der Browser setzt den Fokus beim Sprung
         zum Sprungziel selbst noch einmal — direkt nach DOMContentLoaded
         gesetzt, landete er wieder auf dem Rumpf.
         preventScroll, damit die Fehlermeldung oben im Blick bleibt; und nur,
         wenn niemand schon von Hand irgendwo hineingegangen ist. */
      const feld = ersterFehler.querySelector('input,select,textarea');
      if (feld) {
        requestAnimationFrame(() => {
          const jetzt = document.activeElement;
          if (jetzt && jetzt !== document.body && jetzt !== document.documentElement) return;
          feld.focus({ preventScroll: true });
        });
      }
    } else if (aktuell === 1) {
      geheZu(1);
    }
  }

  /* ---------------------------------------------------------------------
     Panel-Karte (Dellen & Hagelschaden)
     Das Raster ist zweidimensional; die Pfeiltasten laufen trotzdem der
     Reihe nach durch — das ist berechenbarer als eine Navigation nach Lage.
     --------------------------------------------------------------------- */
  umschalter({ bereich: 'panelkarte', schalter: '.panel', tafeln: '.panel-tafel' });

  /* ---------------------------------------------------------------------
     Mobile Navigation
     --------------------------------------------------------------------- */
  const navToggle = document.getElementById('nav-toggle');
  const mainNav   = document.getElementById('main-nav');
  if (navToggle && mainNav) {
    /* Was hinter dem offenen Panel liegt, ist verdeckt — also darf es auch
       nicht bedienbar sein. Vorher lief der Fokus nach dem letzten Menuepunkt
       einfach in den Seiteninhalt weiter, der unsichtbar dahinter lag, und die
       Seite scrollte unter dem Panel mit.

       inert nimmt einen Bereich komplett aus Fokus und Vorlesen heraus. Wo es
       fehlt (aeltere Browser), greift wenigstens die Scrollsperre.

       Nicht einzeln aufgezaehlt, sondern von oben abgeleitet: Bewertungen und
       Kurzanfrage liegen zwischen </main> und dem Fuss, also in keinem der
       beiden — eine Liste haette sie stillschweigend uebersprungen, und der
       Fokus lief zehn Stationen weit hinter das Panel. Ausgenommen bleibt nur,
       was oberhalb des Panels sichtbar bleibt: Ortsleiste und Kopf. */
    const oben = [mainNav.closest('.site-header') || mainNav,
                  document.querySelector('.utility-bar')].filter(Boolean);
    const dahinter = Array.from(document.body.children).filter((el) =>
      !['SCRIPT', 'TEMPLATE', 'NOSCRIPT'].includes(el.tagName)
      && !oben.some((k) => el === k || el.contains(k) || k.contains(el)));

    /* Die Scrollsperre nimmt den Rumpf aus dem Scrollfluss (position:fixed im
       CSS), weil Safari auf iOS `overflow:hidden` beim Wischen ignoriert.
       Damit die Seite dabei nicht an den Anfang springt, wandert die gemerkte
       Position als negatives `top` an den Rumpf und kommt beim Schliessen
       zurueck. */
    let gemerktesY = 0;

    const setzeNav = (offen) => {
      if (offen) {
        gemerktesY = window.scrollY;
        /* Ohne Rumpf im Scrollfluss faellt am Schreibtisch die Bildlaufleiste
           weg und der Inhalt ruckt nach rechts. Auf dem Handy ist der Wert 0,
           dort passiert nichts. */
        const balken = window.innerWidth - document.documentElement.clientWidth;
        if (balken > 0) { document.body.style.paddingRight = balken + 'px'; }
        document.body.style.top = -gemerktesY + 'px';
      }

      mainNav.classList.toggle('is-open', offen);
      navToggle.setAttribute('aria-expanded', String(offen));
      navToggle.setAttribute('aria-label', offen ? 'Menü schließen' : 'Menü öffnen');
      document.body.classList.toggle('nav-offen', offen);
      dahinter.forEach((el) => { el.inert = offen; });

      if (!offen) {
        document.body.style.top = '';
        document.body.style.paddingRight = '';
        /* instant, weil html{scroll-behavior:smooth} den Sprung sonst
           animiert — die Seite fuehre nach dem Schliessen sichtbar an ihre
           eigene Position zurueck. */
        window.scrollTo({ top: gemerktesY, left: 0, behavior: 'instant' });
      }

      // Beim Schliessen zurueck auf den Knopf, sonst landet der Fokus im Nichts.
      if (!offen && mainNav.contains(document.activeElement)) {
        navToggle.focus();
      }
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

    /* Waehrend jemand tippt, muss die Leiste weg.
       Auf dem Handy schiebt die Bildschirmtastatur das Sichtfeld auf rund
       350 px zusammen. Davon gingen 181 px an fixierte Leisten — mehr als die
       Haelfte, und das genau in dem Moment, in dem das Feld unter dem Finger
       sichtbar bleiben muss.

       Nur dort, wo eine Tastatur einfaehrt: am Schreibtisch verdeckt die Leiste
       nichts, und ein Klick ins Feld wuerde sie ohne Not wegfahren lassen. */
    const eng  = window.matchMedia('(max-width: 980px)');
    let   tippt = false;

    /* Der Platzhalter am Fussende muss genau so hoch sein wie die Leiste —
       die ist je Breite 75, 69 oder 65 px hoch. Gemessen statt im CSS
       wiederholt, sonst blieben unten 10 px Luft stehen. */
    const messeHoehe = () => {
      document.documentElement.style.setProperty('--leiste-hoehe', stickyBar.offsetHeight + 'px');
    };

    const pruefe = () => {
      const y = window.scrollY || document.documentElement.scrollTop;
      const sichtbar = y > schwelle && !(tippt && eng.matches);
      stickyBar.classList.toggle('visible', sichtbar);
      // Die Leiste ist fixiert und wuerde sonst die letzte Fusszeile verdecken.
      // Das CSS macht daraus einen Platzhalter am Ende des Fusses.
      document.body.classList.toggle('sticky-an', sichtbar);
    };

    document.addEventListener('focusin', (e) => {
      if (e.target.closest('input, select, textarea')) { tippt = true; pruefe(); }
    });
    document.addEventListener('focusout', (e) => {
      if (!e.target.closest('input, select, textarea')) return;
      // Kurz warten: beim Sprung von einem Feld zum naechsten kaeme die Leiste
      // sonst zwischen zwei Feldern kurz zurueck und wackelt.
      setTimeout(() => {
        tippt = !!document.activeElement?.closest('input, select, textarea');
        pruefe();
      }, 120);
    });

    window.addEventListener('scroll', pruefe, { passive: true });
    window.addEventListener('resize', messeHoehe, { passive: true });
    messeHoehe();
    pruefe();
  }

  /* ---------------------------------------------------------------------
     Inhaltsverzeichnis der Rechtsseiten
     Steht im HTML offen — ohne dieses Skript bleibt es das auch, und die
     Seite ist genauso benutzbar wie vorher. Auf dem Handy nahm es bei
     zehn bis vierzehn Abschnitten den halben ersten Bildschirm ein,
     deshalb klappt es dort zu, bis jemand es aufmacht.
     --------------------------------------------------------------------- */
  const verzeichnis = document.getElementById('recht-verzeichnis');
  if (verzeichnis) {
    const schmal = window.matchMedia('(max-width: 640px)');

    // Nicht gegen den Nutzer arbeiten: wer selbst auf- oder zuklappt, behaelt
    // seinen Zustand. Das Ereignis kommt bei <details> verzoegert, deshalb
    // wird der zuletzt selbst gesetzte Wert gemerkt statt eines Zeitriegels.
    let vonHand = false;
    let selbstGesetzt = null;

    verzeichnis.addEventListener('toggle', () => {
      if (verzeichnis.open === selbstGesetzt) { selbstGesetzt = null; return; }
      vonHand = true;
    });

    const setze = (soll) => {
      if (verzeichnis.open === soll) return;
      selbstGesetzt = soll;
      verzeichnis.open = soll;
    };

    const anpassen = () => { if (!vonHand) setze(!schmal.matches); };
    anpassen();
    schmal.addEventListener('change', anpassen);

    // Ein Sprung ins Verzeichnis soll den Abschnitt zeigen, nicht die Liste.
    verzeichnis.querySelectorAll('a').forEach((a) => a.addEventListener('click', () => {
      if (schmal.matches) setze(false);
    }));
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
