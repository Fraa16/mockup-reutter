# Automatisch hochladen statt FileZilla

Stand: 26.11.2026

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
| `data/content/` → `neu/data/content/` | ja, **aber nur, was im Panel niemand geändert hat** |
| `public/uploads/` → `neu/web/uploads/` | nur Fotos, die auf dem Server fehlen **und** von einem Text gebraucht werden |
| alles andere in `data/` (Anfragen, Passwörter, Sicherungen) | **nein, nie** |

**Gelöscht wird nie.** Eine Datei, die im Repository verschwindet, bleibt auf
dem Server liegen. Das ist Absicht: Auf dem Server stehen Dinge, die es im
Repository nicht gibt und nicht geben darf — Daniels Fotos, die Anfragen mit
Namen und Telefonnummern, die Panel-Passwörter, der Postfach-Zugang. Ein
Abgleich, der „aufräumt", nimmt genau das mit. Harmloser Ballast ist das
kleinere Übel.

### Texte und Fotos: hoch, aber nie über eine Änderung aus dem Panel

Bis November 2026 gingen Texte und Fotos gar nicht automatisch hoch — man
musste jede geänderte Datei mit FileZilla übertragen. Der Grund war das Panel:
Sobald Daniel dort etwas ändert, ist die Fassung **auf dem Server** die
richtige. Eine Automatik, die stur alles aus dem Repository hochlädt, würde
seine Änderungen überschreiben, ohne dass es jemand merkt.

Seitdem entscheidet ein kleines Skript (`.github/scripts/inhalte-abgleichen.py`)
bei jedem Lauf **Datei für Datei**. Es holt dazu den Stand des Servers — nur
lesend — und vergleicht:

| Auf dem Server liegt … | Dann … |
|---|---|
| nichts | wird die Datei hochgeladen |
| genau dieselbe Fassung | passiert nichts |
| eine **frühere Fassung aus dem Repository** | wird sie ersetzt — niemand hat sie im Panel angefasst, der Server ist nur alt |
| eine kaputte Datei (kein gültiges JSON) | wird sie ersetzt — das Panel schreibt nie kaputtes JSON, so etwas bleibt nur nach einem abgebrochenen Hochladen liegen |
| **irgendetwas anderes** | **bleibt sie stehen**: Das ist eine Änderung aus dem Panel, und die gilt |

„Frühere Fassung" heißt: Byte für Byte gleich einer Fassung, die irgendwann im
Repository stand. Die Geschichte dafür liefert Git selbst. Auf dem Server liegt
kein Merkzettel, der verloren gehen oder veralten könnte.

**Fotos** gehen nur hoch, wenn sie auf dem Server fehlen **und** einer der Texte,
die nach dem Lauf live sind, sie benutzt. Ein Foto, das Daniel im Panel
gelöscht hat, kommt so nicht zurück — es steht in seinen Texten nicht mehr.
Ein Foto, das schon da ist, wird nie ersetzt; die Dateinamen tragen eine
Zufallskennung, gleicher Name heißt gleiches Bild. Fehlen nur die verkleinerten
Fassungen in `cache/`, werden die nachgezogen.

Die Reihenfolge ist immer: Code, dann Fotos, dann Texte. So steht nie ein Text
online, der auf ein Foto zeigt, das noch unterwegs ist.

**Was man sehen kann:** Jeder Lauf schreibt eine kurze Zusammenfassung auf
seine Seite unter **Actions** — welche Texte hochgingen, welche Fotos, und
welche Dateien wegen einer Panel-Änderung stehen geblieben sind. Letzteres
erscheint zusätzlich als gelbe Warnung.

### Wenn eine Datei stehen geblieben ist

Das ist kein Fehler. Es heißt: Jemand hat im Repository einen Text geändert,
den Daniel im Panel ebenfalls geändert hat. Die Website zeigt weiter Daniels
Fassung. Soll die Änderung aus dem Repository trotzdem online:

1. Die Datei mit FileZilla aus `neu/data/content/` holen — das ist Daniels
   aktueller Stand.
2. Die gewünschte Änderung **darin** nachtragen.
3. Diese Fassung ins Repository übernehmen und mergen.

Beim nächsten Lauf ist der Server dann „eine frühere Fassung" — und die neue
geht von selbst hoch. Für einzelne Angaben ist das Panel ohnehin der kürzere
Weg.

> **Warum das alles: Diese Falle hat einmal zugeschlagen.**
> Am 12.09.2026 landete eine Testanfrage im alten Postfach
> `info@clean-box.eu`. Die Adresse war im Repository seit einem Tag geändert,
> der Merge war durch, die Automatik gelaufen — auf dem Server stand trotzdem
> die alte, weil `site.json` damals nie automatisch hochging. Keine
> Fehlermeldung, kein rotes Häkchen. Die Probe der Kontaktadresse am Ende
> jedes Laufs stammt von damals und bleibt als zweite Sicherung.

## Nach jedem Lauf prüft GitHub selbst

Der letzte Schritt ruft die Website auf und meldet einen Fehlschlag, wenn

* die Startseite oder `/leistungen/` nicht mit 200 antwortet — letzteres prüft
  zugleich, ob die Umschreibregeln greifen, und
* `/data/users.php`, `/app/bootstrap.php` oder `/.user.ini` mit 200 antworten.
  Das wäre der ernste Fall: Passwörter und Kundendaten im Netz.

## Wenn etwas klemmt

| Meldung | Ursache |
|---|---|
| `Ein Repository-Geheimnis fehlt (…)` | Name vertippt — Groß-/Kleinschreibung zählt |
| `Login failed` | Passwort stimmt nicht mehr. Wurde es bei IONOS geändert, gehört es auch hier neu hinterlegt |
| `Fatal error: Host key verification failed` | Der Server hat einen neuen Schlüssel. Dann melden, bevor irgendetwas bestätigt wird |
| Die Änderung erscheint nicht | Zusammenfassung des Laufs ansehen: Steht die Datei unter „Im Panel geändert"? Siehe oben |

## Für später: die Übergabe

Läuft das Projekt aus, hängt an diesem Repository ein Vollzugriff auf den
Webspace. Zwei Wege, beide in Ordnung:

* Repository an Daniel übergeben, Geheimnisse bleiben stehen
* Geheimnisse löschen und das SFTP-Passwort bei IONOS neu setzen

Was nicht geht: das Repository liegen lassen und die Geheimnisse vergessen.

---

## Anfragen löschen sich selbst

Unabhängig von der Übertragung, aber aus demselben Gedanken: Jede Anfrage aus
dem Formular liegt als Datei auf dem Server — mit Namen, Telefonnummer und den
mitgeschickten Fotos. Die Datenschutzerklärung verspricht, dass sie nach einer
Frist verschwinden. Bisher hätte das jemand von Hand tun müssen.

Die Frist steht in den **Stammdaten → Anfragen aufbewahren** und liegt bei
**6 Monaten**. Dieselbe Zahl erscheint in der Datenschutzerklärung — sie kommt
aus derselben Quelle, Zusage und Verhalten können also nicht auseinanderlaufen.
Eine `0` schaltet das automatische Löschen ab; die Erklärung formuliert den
Satz dann entsprechend um.

Angestoßen wird das Aufräumen von einem beliebigen Seitenaufruf, höchstens
einmal am Tag. Kein zeitgesteuerter Auftrag beim Hoster, der eingerichtet
werden müsste — nach der Übergabe soll niemand mehr etwas einrichten.

**Angefasst wird ausschließlich `data/anfragen/`.** Die Fotos, die der Betrieb
für die Website hochlädt, liegen in `web/uploads/` und haben mit dieser Frist
nichts zu tun.

### Anfragen, aus denen ein Auftrag wurde

Für die gelten sechs bis zehn Jahre, nicht sechs Monate. Die Website kann das
nicht wissen, deshalb gibt es in der Anfragenliste den Knopf **„Von der Frist
ausnehmen"**. Ausgenommene Anfragen bleiben liegen, bis sie jemand von Hand
löscht.
