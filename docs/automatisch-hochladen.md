# Automatisch hochladen statt FileZilla

Stand: 11.09.2026

Nach einem Merge in `main` überträgt GitHub die Website von selbst auf den
IONOS-Webspace. Die Handarbeit aus `anleitung-hochladen.md` entfällt damit für
Aktualisierungen — für das erste Aufsetzen bleibt sie gültig.

---

## Einmal einrichten: drei Angaben hinterlegen

GitHub braucht dieselben drei Angaben, die auch in FileZilla stehen. Sie werden
als **Repository-Geheimnisse** hinterlegt: GitHub zeigt sie nach dem Speichern
nie wieder an, auch dir nicht, und sie tauchen in keinem Protokoll auf.

Auf GitHub: **Settings → Secrets and variables → Actions → New repository
secret**. Drei Stück, die Namen müssen genau stimmen:

| Name | Wert |
|---|---|
| `IONOS_SERVER` | die Serveradresse, z. B. `access…….webspace-data.io` |
| `IONOS_BENUTZER` | der SFTP-Benutzer, `u………` |
| `IONOS_PASSWORT` | dessen Passwort |

> **Das ist derselbe Zugang, der auch alles darf.** Wer ihn hat, kann den
> ganzen Webspace lesen und schreiben. Er liegt damit an einer Stelle mehr als
> vorher — das ist der Preis der Automatik. Beim Abschluss des Projekts gehört
> er deshalb auf die Übergabeliste: entweder das Repository mit übergeben oder
> die drei Geheimnisse löschen und das SFTP-Passwort neu setzen.

## Erster Lauf: Probelauf

Nicht gleich scharf schalten. Auf GitHub unter **Actions → Auf IONOS hochladen
→ Run workflow** steht ein Haken **„Nur anzeigen, was passieren würde"** — der
ist voreingestellt. Ein Lauf damit überträgt nichts, listet aber jede Datei
auf, die er anfassen würde.

Sieht die Liste vernünftig aus, denselben Lauf noch einmal ohne den Haken.
Danach läuft es bei jedem Merge in `main` von allein.

## Was übertragen wird — und was nicht

| | |
|---|---|
| `app/` → `neu/app/` | ja |
| `bin/` → `neu/bin/` | ja |
| `public/` → `neu/web/` | ja, **ohne** `uploads/` |
| `data/` | **nein** |

**Gelöscht wird nie.** Eine Datei, die im Repository verschwindet, bleibt auf
dem Server liegen. Das ist Absicht: Auf dem Server stehen Dinge, die es im
Repository nicht gibt und nicht geben darf — Daniels Fotos, die Anfragen mit
Namen und Telefonnummern, die Panel-Passwörter, der Postfach-Zugang. Ein
Abgleich, der „aufräumt", nimmt genau das mit. Harmloser Ballast ist das
kleinere Übel.

### Warum `data/` außen vor bleibt

Sobald Daniel das Panel benutzt, ist die Fassung **auf dem Server** die
richtige, nicht die im Repository. Jede Übertragung von `data/content/` würde
seine Änderungen überschreiben — den geänderten Text, die neu einsortierten
Fotos, die gepflegten Öffnungszeiten.

**Die Folge, die man kennen muss:** Ändere ich einen Inhalt im Repository,
kommt er **nicht** von selbst auf die Website. Das ist kein Fehler, sondern
dieselbe Entscheidung von der anderen Seite. Inhaltsänderungen laufen deshalb
in drei Schritten: die aktuelle Datei vom Server holen, darin ändern, wieder
hochladen. So bleibt erhalten, was im Panel entstanden ist.

### Warum `uploads/` außen vor bleibt

Dort liegen die Fotos aus dem Panel. Sie gehören dem Server. Nebeneffekt: Ohne
diesen Ausschluss schöbe jeder Durchlauf sieben Megabyte Platzhalterbilder
erneut über die Leitung.

## Nach jedem Lauf prüft GitHub selbst

Der letzte Schritt ruft die Website auf und meldet einen Fehlschlag, wenn

* die Startseite oder `/leistungen/` nicht mit 200 antwortet — letzteres prüft
  zugleich, ob die Umschreibregeln greifen, und
* `/data/users.php`, `/app/bootstrap.php` oder `/.user.ini` mit 200 antworten.
  Das wäre der ernste Fall: Passwörter und Kundendaten im Netz.

## Wenn etwas klemmt

| Meldung | Ursache |
|---|---|
| `Das Repository-Geheimnis IONOS_… fehlt` | Name vertippt — Groß-/Kleinschreibung zählt |
| `Login failed` | Passwort stimmt nicht mehr. Wurde es bei IONOS geändert, gehört es auch hier neu hinterlegt |
| `Fatal error: Host key verification failed` | Der Server hat einen neuen Schlüssel. Dann melden, bevor irgendetwas bestätigt wird |
| Die Änderung erscheint nicht | Lag sie in `data/`? Siehe oben |

## Für später: die Übergabe

Läuft das Projekt aus, hängt an diesem Repository ein Vollzugriff auf den
Webspace. Zwei Wege, beide in Ordnung:

* Repository an Daniel übergeben, Geheimnisse bleiben stehen
* Geheimnisse löschen und das SFTP-Passwort bei IONOS neu setzen

Was nicht geht: das Repository liegen lassen und die Geheimnisse vergessen.
