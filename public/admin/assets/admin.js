/**
 * Rueckfragen vor Aktionen, die sich nicht rueckgaengig machen lassen.
 *
 * Stand frueher als onsubmit="return confirm(…)" im HTML. Das sieht
 * harmlos aus, wird aber von der Sicherheitsregel der Website abgewiesen:
 * script-src 'self' laesst nur Skripte aus eigenen Dateien zu, keine im
 * Markup eingebetteten. Auf dem Server erschien die Rueckfrage deshalb nie —
 * ein Klick auf "Anfrage loeschen" loeschte sofort.
 *
 * Hier steht sie in einer eigenen Datei und greift wieder.
 */
(() => {
  'use strict';

  document.querySelectorAll('[data-bestaetigen]').forEach((formular) => {
    formular.addEventListener('submit', (e) => {
      if (!window.confirm(formular.dataset.bestaetigen)) {
        e.preventDefault();
      }
    });
  });
})();
