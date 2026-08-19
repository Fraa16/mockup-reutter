# Auftrag: das CMS aus diesem Projekt als wiederverwendbare Basis herausziehen

Stand: 18.08.2026 · geschrieben als Übergabe an eine neue Sitzung

Diese Datei ist die vollständige Arbeitsgrundlage. Sie setzt **kein** Wissen aus
vorherigen Gesprächen voraus. Alle Zahlen darin sind an diesem Repository
gemessen, nicht geschätzt.

---

## 1 · Was gebaut werden soll

Das CMS dieses Projekts soll als eigenständiges **Template-Repository** bei
GitHub liegen, damit künftige Kundenprojekte damit starten können, statt es zu
kopieren.

Ergebnis ist ein neues, leeres Repo mit der Engine, einem minimalen
Beispielprojekt und einer Anleitung — **nicht** eine Änderung an diesem
Repository hier.

> **Die eine Entscheidung, die vorab zu klären ist:** wie das neue Repository
> heißen soll. Vorschlag `cms-basis`. Frag danach, bevor du anfängst.

---

## 2 · Woher

Repository `Fraa16/mockup-reutter`, Branch `main`. Es ist ein
selbstgebautes PHP-CMS ohne Framework, ohne Datenbank, ohne Build-Schritt.
Inhalte liegen als JSON in `data/content/`, das Panel unter `/admin/`.

Deployment beim Kunden ist schlichtes SFTP auf IONOS. **Das ist der Grund für
mehrere Entscheidungen weiter unten und darf nicht wegoptimiert werden.**

---

## 3 · Die Trennlinie

Gemessen: 7 173 Zeilen PHP insgesamt.

### Engine — kommt mit, bleibt bei jedem Projekt gleich

| Datei | Zeilen | Aufgabe |
|---|---|---|
| `app/bootstrap.php` | 45 | Konstanten, Fehlerbehandlung, lädt die Bibliotheken |
| `app/lib/content.php` | 77 | JSON laden, `get()` mit Punktnotation |
| `app/lib/render.php` | 139 | Templates, `partial()`, `asset()` mit Cache-Buster |
| `app/lib/speichern.php` | 257 | Schreiben mit automatischer Vorgängersicherung |
| `app/lib/auth.php` | 310 | Anmeldung, Sitzung, Sperre nach Fehlversuchen |
| `app/lib/anfrage.php` | 467 | Formular: Prüfung, Honigtopf, Zeitfalle, Rate-Limit |
| `app/lib/mail.php` | 323 | Handgeschriebener SMTP-Client |
| `app/lib/images.php` | 235 | Upload, Verkleinern, WebP-Ableitungen |
| `app/lib/seo.php` | 273 | Canonical, Open Graph, JSON-LD, Sitemap |
| `public/admin/index.php` | 206 | Übersicht, Anmeldung |
| `public/admin/edit.php` | 253 | Bearbeitungsformular aus dem Schema |
| `public/admin/anfragen.php` | 250 | Posteingang |
| `public/admin/assets/admin.css` | 242 | Panel-Gestaltung, **ohne Markenbezug** |
| `bin/passwort-setzen.php` | 91 | Zugang anlegen |
| `bin/ableitungen.php` | 49 | Bildableitungen neu erzeugen |

### Projektspezifisch — kommt **nicht** mit

- `app/schema/felder.php` (866 Zeilen) — die Feldliste. Pro Projekt neu.
- `app/templates/` (20 Dateien) — das Design.
- `data/content/*.json` — die Inhalte.
- `public/assets/css/styles.css`, `js/main.js` — Frontend.

### Hybrid — Mechanik bleibt, Inhalt wird ersetzt

`public/index.php` (133 Zeilen). Die Routenauflösung ist allgemein, die
Routentabelle in Zeile 19–31 und die Leistungs-Slug-Logik in Zeile 88–100 sind
Reutter-spezifisch. Im Beispielprojekt auf zwei, drei Routen eindampfen; die
Slug-Whitelist als Muster erhalten, sie ist ein Sicherheitsmerkmal.

---

## 4 · Die zehn Verdrahtungen, die parametrisiert werden müssen

Vollständig — gemessen, nicht geschätzt. Mehr als diese zehn Stellen gibt es
nicht.

| Datei | Zeile | Heute |
|---|---|---|
| `app/lib/auth.php` | 40 | `session_name('reutter_panel')` |
| `bin/passwort-setzen.php` | 24 | `"Panel-Zugang fuer Fahrzeugpflege Reutter"` |
| `bin/passwort-setzen.php` | 31 | `'Benutzername (z. B. reutter)'` |
| `bin/passwort-setzen.php` | 37 | `'Anzeigename (z. B. Daniel Reutter)'` |
| `public/admin/index.php` | 44 | Seitentitel `… — Fahrzeugpflege Reutter` |
| `public/admin/index.php` | 54 | `src="/assets/logo/reutter-wortmarke-weiss.webp"` |
| `public/admin/index.php` | 106 | dasselbe Bild noch einmal |
| `public/admin/edit.php` | 117 | Seitentitel |
| `public/admin/anfragen.php` | 89 | Seitentitel |
| `public/admin/anfragen.php` | 170 | Mail-Betreff `Ihre Anfrage bei Fahrzeugpflege Reutter` |

Der Favicon-Pfad `/assets/favicon.svg` steht an drei Stellen und bleibt so —
das ist eine Konvention, kein Markenbezug.

### Nachtrag: die Zehn waren die falsche Messung

Beim Umsetzen kam heraus, dass diese Liste **nur Markenzeichenketten** erfasst.
Der Suchlauf griff auf `reutter|clean-box|Korntal|logo|favicon` — Feldnamen wie
`marke` konnte er gar nicht finden. Drei strukturelle Verdrahtungen kommen
dazu, und die sind mehr Arbeit als alle zehn Zeichenketten zusammen:

| Wo | Was |
|---|---|
| `app/lib/anfrage.php`, `mail.php`, `public/admin/anfragen.php` | die Fahrzeugfelder des Formulars: `marke`, `modell`, `baujahr`, `lackfarbe`, `fahrzeug`, `leistungen[]` — in allen drei Dateien |
| `app/lib/seo.php` | `AutoRepair` als Betriebstyp, `makesOffer` aus `leistungen.json`, `/leistungen/<slug>/` in `seo_jsonld_leistung()`, die feste Routenliste in `seo_seitenliste()` |
| `app/lib/content.php` | `leistungen_mit_seite()` samt Kommentar über „Felgen & Reifen" |

**Lehre für den nächsten Suchlauf:** nach dem Datenmodell suchen, nicht nur nach
dem Namen des Kunden. Also auch nach Feldnamen, Routenpräfixen und
Schema.org-Typen.

**Lösung:** eine neue Datei `app/config/projekt.php`, die ins Git gehört. Der
Ordner `app/config/` existiert als Konvention bereits (`zugangsdaten.php` liegt
dort und ist bewusst *nicht* im Git — siehe `.gitignore`).

```php
<?php
declare(strict_types=1);

/**
 * Alles, was von Projekt zu Projekt verschieden ist und nicht in den Inhalten
 * steht. Diese Datei gehoert ins Git — sie enthaelt keine Geheimnisse.
 */
return [
    'name'         => 'Beispielbetrieb',        // Panel-Titel, Mail-Betreff
    'sitzung'      => 'beispiel_panel',         // session_name, je Projekt eigen
    'panel_logo'   => '/assets/logo/wortmarke-weiss.webp',
    'panel_logo_breite' => 527,
    'panel_logo_hoehe'  => 56,
];
```

Zugriff über eine kleine Funktion in `content.php`, im Stil des vorhandenen
`get()` und `site()` — dort steht schon alles, was Daten lädt.

Die Datei trägt am Ende mehr als nur die zehn Zeichenketten: auch den
Schema.org-Typ des Betriebs und die Seitenliste für die `sitemap.xml`, weil
beides pro Projekt verschieden ist und sonst in `seo.php` festhängt. **Kein neues Framework, keine Klasse** — der Rest des
Projekts ist Funktionen und Arrays, das bleibt so.

---

## 5 · Entscheidungen, die schon gefallen sind

Nicht neu aufrollen, sondern umsetzen. Begründungen stehen dabei, damit
erkennbar ist, wann eine Entscheidung fällt.

**Template-Repository, kein Composer-Paket.** Composer wäre die Lehrbuchlösung,
brächte aber einen Build-Schritt in ein Projekt, das keinen hat — und das
Deployment ist SFTP auf IONOS. *Umsteigen lohnt sich, sobald derselbe Fehler in
drei Repos behoben werden muss.*

**Kein Git-Submodul.** Bricht den „Ordner hochladen"-Ablauf.

**Deutsche Bezeichner und Kommentare bleiben.** Das gesamte Projekt heißt
`speichern()`, `anfrage_pruefen()`, `bild_quellen()`, `$fehler`, `$werte`.
Kommentare erklären das *Warum*, nicht das *Was*. **Nicht ins Englische
übersetzen und nicht umbenennen.**

**Das Panel iteriert das Schema, niemals `$_POST`.** Ein Feld, das nicht im
Schema steht, kann nicht geschrieben werden. Das ist ein Sicherheitsmerkmal und
muss beim Eindampfen erhalten bleiben.

**`.gitignore` weitgehend übernehmen.** Sie ist begründet: `data/anfragen/`
enthält personenbezogene Daten fremder Kunden, `public/uploads/cache/` gehört
bewusst *ins* Repo, weil Vercel ein schreibgeschütztes Dateisystem hat.

---

## 6 · Arbeitsschritte

1. **Neues Repo anlegen** (Name vorher erfragen) und in den Einstellungen als
   Template-Repository markieren.
2. **Engine übernehmen** — die Dateien aus Abschnitt 3, unverändert.
3. **`app/config/projekt.php` anlegen** und die zehn Stellen aus Abschnitt 4
   darauf umstellen.
4. **Minimales Beispielprojekt bauen** — siehe Abschnitt 7.
5. **`LIESMICH.md` schreiben** — siehe Abschnitt 8.
6. **Abnahme** — siehe Abschnitt 9.

---

## 7 · Was das Beispielprojekt zeigen muss

So klein wie möglich, aber **lauffähig** — wer das Template benutzt, soll eine
funktionierende Seite sehen und nicht erst Reste wegräumen.

- **Zwei Seiten**: eine Startseite und eine Kontaktseite mit Anfrageformular.
- **Ein Schema mit allen sechs Feldtypen**, damit sie an einem Beispiel
  ablesbar sind: `text`, `mehrzeilig`, `absaetze`, `zahl`, `bild`, `liste`.
  Wer in `edit.php` nach `text` sucht, findet nichts — fünf Typen haben einen
  eigenen Zweig, `text` ist der Rückfall am Ende. Das ist Absicht, kein Fehler.
  Der Kommentar in `app/schema/felder.php` Zeile 11 nennt zusätzlich `auswahl` —
  **den Typ gibt es nicht**, `edit.php` hat keine Behandlung dafür. Beim
  Übernehmen aus dem Kommentar streichen.
- **Ein Platzhalterlogo** und ein Platzhalterbild, damit nichts ins Leere zeigt.
- **Kein Reutter-Rest**: keine Leistungsseiten, keine Rechtstexte, keine Bilder
  aus diesem Projekt. Prüfen mit `grep -ri reutter .` — muss leer sein.

---

## 8 · Was in die `LIESMICH.md` der Basis gehört

Zielgruppe ist die eigene Agentur in sechs Monaten, nicht die Öffentlichkeit.

- **Wie man ein Schema schreibt.** Die sechs Feldtypen, was `pfad` bedeutet
  (Punktnotation in die JSON-Datei), was `gruppe` steuert. Am besten anhand des
  Beispiels aus Abschnitt 7.
- **Der Ablauf für ein neues Projekt:** Template benutzen →
  `app/config/projekt.php` ausfüllen → Schema schreiben → Templates bauen →
  `php bin/passwort-setzen.php`.
- **Was wo liegt** — die Tabelle aus Abschnitt 3 in Kurzform.
- **Die zwei Betriebshinweise:** `php bin/ableitungen.php` nach neuen Bildern,
  und dass `app/config/zugangsdaten.php` für den SMTP-Versand gebraucht wird und
  nicht ins Git gehört.
- **Was das CMS bewusst nicht kann:** keine Benutzerverwaltung, keine
  Versionierung über die letzte Sicherung hinaus, keine Mehrsprachigkeit. Ein
  ehrlicher Abschnitt darüber spart die Enttäuschung im dritten Projekt.

---

## 9 · Abnahme

Ohne diese Punkte ist die Arbeit nicht fertig:

1. **`grep -ri reutter .` im neuen Repo ist leer.** Auch in Kommentaren.
2. **Das Beispielprojekt läuft**: `php -S 127.0.0.1:8000 -t public` — beide
   Seiten rendern ohne Fehler oder Warnungen.
3. **Panel vollständig durchgespielt**: anmelden, einen Bereich öffnen, je einen
   Wert pro Feldtyp ändern, speichern, Änderung auf der Seite sichtbar, Sicherung
   in `data/backups/` angelegt.
4. **Formular durchgespielt**: absenden führt auf die Dankeseite; Pflichtfeld
   leer führt zurück mit Fehlermeldung und erhaltenen Eingaben.
5. **Ohne JavaScript** sind beide Seiten lesbar und das Formular absendbar.
6. **Zweites Projekt zur Probe**: aus dem Template ein Wegwerf-Repo erzeugen,
   `projekt.php` ausfüllen, starten. Wenn dabei etwas anzupassen ist, das nicht
   in `projekt.php` steht, fehlt eine Verdrahtung aus Abschnitt 4.

---

## 10 · Was nicht passieren darf

- **Dieses Repository nicht verändern.** `mockup-reutter` ist ein abgenommenes
  Kundenprojekt. Die Basis wird herausgezogen, nicht herausgeschnitten.
- **Keine Abhängigkeiten hinzufügen.** Kein Composer, kein npm, kein
  Build-Schritt. Das Projekt läuft auf einem IONOS-Tarif mit PHP 8.4 und sonst
  nichts.
- **Keine Funktionen erfinden**, die im Original nicht da sind. Wer beim
  Übernehmen eine Lücke findet, schreibt sie in die `LIESMICH.md` unter „kann
  das CMS nicht", statt sie nebenbei zu bauen.
- **Nichts stillschweigend umbenennen** — weder Dateien noch Funktionen noch
  Sprache.
