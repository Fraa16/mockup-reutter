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

> **Diese Falle hat einmal zugeschlagen — sie ist nicht theoretisch.**
> Am 12.09.2026 landete eine Testanfrage im alten Postfach
> `info@clean-box.eu`. Die Adresse war im Repository seit einem Tag geändert,
> der Merge war durch, die Automatik gelaufen — auf dem Server stand trotzdem
> die alte, weil `site.json` in `data/` liegt.
>
> Das Tückische daran ist nicht der Fehler, sondern seine Lautlosigkeit: Es
> gibt keine Fehlermeldung, kein rotes Häkchen, keinen Unterschied im Log. Die
> Website läuft, sie zeigt nur ältere Inhalte. Aufgefallen ist es durch Zufall.
>
> Seitdem prüft der letzte Schritt der Automatik, ob die Website die
> Kontaktadresse aus dem Repository zeigt, und warnt, wenn nicht. Nur eine
> Warnung, kein Fehlschlag: Nach der Übergabe pflegt Daniel die Inhalte im
> Panel, und dann ist der Server im Recht.

### Inhalte von Hand übertragen

Zwei Wege, je nach Umfang:

**Eine einzelne Angabe** — im Panel ändern. Nichts herunterladen, nichts
hochladen, kein Risiko, dass dabei etwas anderes überschrieben wird. Das ist
der richtige Weg für die Kontaktadresse, die Öffnungszeiten, einen Textabsatz.

**Eine ganze Datei** — etwa eine fertig überarbeitete Rechtsseite. Dann:

1. In FileZilla `neu/data/content/` öffnen und die betroffene Datei auf den
   Schreibtisch ziehen. Das ist die Sicherung; ohne sie gibt es keinen Rückweg.
2. Die Fassung aus dem Repository hochladen und die alte überschreiben.
3. Die Seite im Browser aufrufen und nachsehen, ob der neue Text dasteht.

**`galerie.json` gehört nicht dazu.** Sobald im Panel ein Foto einsortiert
wurde, steht in dieser Datei etwas, das es im Repository nicht gibt. Sie zu
überschreiben wirft die Einsortierung weg. Dasselbe gilt für alles, was
sonst im Panel entstanden ist.

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
| `Ein Repository-Geheimnis fehlt (…)` | Name vertippt — Groß-/Kleinschreibung zählt |
| `Login failed` | Passwort stimmt nicht mehr. Wurde es bei IONOS geändert, gehört es auch hier neu hinterlegt |
| `Fatal error: Host key verification failed` | Der Server hat einen neuen Schlüssel. Dann melden, bevor irgendetwas bestätigt wird |
| Die Änderung erscheint nicht | Lag sie in `data/`? Siehe oben |

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
