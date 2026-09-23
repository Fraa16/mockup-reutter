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

  /* Ungespeicherte Aenderungen im grossen Formular merken. Ein Entfernen-
     Knopf schickt ein eigenes kleines Formular ab, und die Seite laedt neu —
     was im grossen noch nicht gespeichert war, ist danach weg. Die Rueckfrage
     sagt das dazu, statt es still geschehen zu lassen. */
  let ungespeichert = false;
  document.querySelectorAll('form.formular').forEach((formular) => {
    const merken = () => { ungespeichert = true; };
    formular.addEventListener('input', merken);
    formular.addEventListener('change', merken);
    formular.addEventListener('submit', () => { ungespeichert = false; });
  });

  document.querySelectorAll('[data-bestaetigen]').forEach((formular) => {
    formular.addEventListener('submit', (e) => {
      let frage = formular.dataset.bestaetigen;
      if (ungespeichert && !formular.classList.contains('formular')) {
        frage += '\n\nAchtung: Ihre ungespeicherten Änderungen auf dieser Seite gehen dabei verloren.';
      }
      if (!window.confirm(frage)) {
        e.preventDefault();
      }
    });
  });
})();
