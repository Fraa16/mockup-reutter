/**
 * Fotos einzeln hochladen, mit Fortschritt je Datei.
 *
 * Ohne dieses Skript geht das Formular ganz normal ab und laedt hoch, was in
 * einen POST passt — der brauchbare Grundzustand. Hier kommt nur dazu, dass
 * die Dateien nacheinander gehen: zwanzig Handyfotos sind achtzig Megabyte,
 * daran scheitert geteiltes Hosting. Einzeln bleibt jede Anfrage klein, und
 * ein Abbruch unterwegs kostet ein Foto statt aller.
 */
(() => {
  'use strict';

  const formular = document.getElementById('foto-formular');
  const auswahl = document.getElementById('fotos');
  const liste = document.getElementById('upload-liste');
  if (!formular || !auswahl || !liste || !window.fetch) return;

  const token = formular.querySelector('input[name="csrf"], input[name="_token"]');
  const knopf = formular.querySelector('button[type="submit"]');

  const zeile = (name) => {
    const li = document.createElement('li');
    li.className = 'upload-zeile';
    li.innerHTML = '<span class="upload-name"></span><span class="upload-stand">wartet</span>';
    li.querySelector('.upload-name').textContent = name;
    liste.appendChild(li);
    return li.querySelector('.upload-stand');
  };

  formular.addEventListener('submit', async (e) => {
    const dateien = Array.from(auswahl.files || []);
    if (!dateien.length) return;

    e.preventDefault();
    liste.hidden = false;
    liste.textContent = '';
    knopf.disabled = true;
    auswahl.disabled = true;

    let fertig = 0;
    for (const datei of dateien) {
      const stand = zeile(datei.name);
      stand.textContent = 'lädt …';
      try {
        const daten = new FormData();
        daten.append('foto', datei);
        if (token) daten.append(token.name, token.value);

        const antwort = await fetch('/admin/foto-upload.php', {
          method: 'POST',
          body: daten,
          credentials: 'same-origin',
        });
        const ergebnis = await antwort.json().catch(() => ({}));

        if (antwort.ok) {
          stand.textContent = 'fertig';
          stand.className = 'upload-stand ist-gut';
          fertig++;
        } else {
          stand.textContent = ergebnis.fehler || 'fehlgeschlagen';
          stand.className = 'upload-stand ist-schlecht';
        }
      } catch {
        /* Abgebrochene Verbindung, Funkloch. Die uebrigen Dateien laufen
           weiter — deshalb steht das try innerhalb der Schleife. */
        stand.textContent = 'Verbindung unterbrochen';
        stand.className = 'upload-stand ist-schlecht';
      }
    }

    /* Neu laden, damit der Posteingang darunter die neuen Fotos zeigt. Nur
       wenn wirklich etwas ankam — sonst waere die Fehlerliste sofort weg. */
    if (fertig > 0) {
      window.location = '/admin/fotos.php?hochgeladen=' + fertig;
      return;
    }
    knopf.disabled = false;
    auswahl.disabled = false;
  });
})();
