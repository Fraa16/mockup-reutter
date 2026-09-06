# Die Website auf IONOS bringen

Stand: 05.09.2026 · Anleitung zum Mitklicken · Vertrag 97829981

---

## Wo wir stehen

Das hier ist **nicht** der Livegang. Es ist der Aufbau eines Probeexemplars auf
dem echten Server, unter einer eigenen Testadresse. Drei Phasen:

1. **Jetzt:** Die Seite kommt auf IONOS, unter `smartrepair-reutter.de`.
   Für Google ist sie gesperrt — sie ist erreichbar, taucht aber in keiner
   Suche auf. Damit ist Daniels Foto-Upload freigeschaltet, und das ist der
   eigentliche Zweck.
2. **Danach:** Daniel lädt Fotos hoch, ihr füllt die letzten Lücken.
3. **Zum Schluss:** Der Umschalttag. Dann leitet `clean-box.eu` nur noch
   weiter, und Google bekommt die Seite frei. Das steht in `umzug.md` und ist
   ein eigener Termin — an den Dateien ändert sich dabei nichts mehr.

**Die alte Seite bleibt die ganze Zeit online und wird nicht angefasst.**

## Warum überhaupt IONOS, wo doch alles auf Vercel läuft?

Vercel ist das **Schaufenster**. Die Adresse dort zeigt die Website, damit man
sich den Stand ansehen kann, ohne etwas hochzuladen. Mehr macht sie nicht:
Vercel kann nichts **speichern**. Keinen geänderten Text, kein hochgeladenes
Foto, keine Anfrage aus dem Kontaktformular. Deshalb ist der Bereich `/admin/`
dort gar nicht erreichbar — ein Anmeldeformular, hinter dem nichts funktioniert,
wäre nur verwirrend.

IONOS ist das **Zuhause**. Ganz normaler Webspace, und dort darf geschrieben
werden. Erst wenn die Website dort liegt, gibt es:

* den Bearbeitungsbereich unter `smartrepair-reutter.de/admin/`
* Daniels Foto-Upload vom Handy
* das Kontaktformular, das wirklich E-Mails verschickt

Beides bleibt nebeneinander bestehen und stört sich nicht. Vercel ist die
Vorschau für Zwischenstände, IONOS die echte Website.

## Was du dafür brauchst

* Zugang zum IONOS-Kundenmenü
* Ein Programm zum Dateiübertragen. **FileZilla** ist kostenlos und läuft auf
  Mac und Windows: <https://filezilla-project.org> — die *Client*-Fassung, nicht
  den Server.
* Die Dateien des Projekts auf deinem Rechner — wie du drankommst, steht
  gleich in Schritt 0
* Ungefähr eine Stunde beim ersten Mal

Die Menüs bei IONOS heißen je nach Vertrag und Jahr etwas anders. Ich schreibe
deshalb dazu, **wonach** du suchst, nicht nur, wo es angeblich steht.

---

## Schritt 0 · Die Dateien auf deinen Rechner holen

Hochgeladen wird von deinem Mac aus. Die Dateien liegen aber auf GitHub, also
müssen sie erst herunter.

1. <https://github.com/Fraa16/mockup-reutter> öffnen
2. Oben links steht ein Umschalter auf `main`. **Draufklicken und
   `claude/website-mockup-implementation-x303oy` auswählen.**
3. Grüner Knopf **Code** → **Download ZIP**
4. Im Downloads-Ordner auf die Datei doppelklicken. Daraus wird ein Ordner mit
   einem langen Namen — darin liegen `app`, `bin`, `data`, `public` und der
   Rest.

> **Der Umschalter auf den Branch ist der Punkt, an dem es schiefgeht.** In
> `main` steht ein alter Stand: ohne Foto-Upload, ohne Transportseite, ohne die
> `.user.ini` aus Schritt 1. Sieht die Seite nach dem Hochladen aus wie vor
> Wochen, ist hier der Fehler.

> **Der Finder zeigt weniger, als da ist.** `.htaccess` und `.user.ini`
> beginnen mit einem Punkt und sind im Finder unsichtbar. In FileZilla siehst
> du sie. Der Ordner ist also vollständig, auch wenn er im Finder kleiner
> wirkt.

**Geschafft, wenn:** Der entpackte Ordner enthält `app`, `bin`, `data` und
`public`.

## Schritt 1 · PHP einstellen — schon erledigt

PHP ist die Technik, mit der der Server die Seiten zusammensetzt — dasselbe,
was später Daniels Änderungen entgegennimmt.

**Die Version steht bei dir bereits auf 8.4.** Damit ist dieser Schritt durch.

Die beiden anderen Werte — `upload_max_filesize` und `post_max_size`, die
maximale Größe eines hochgeladenen Fotos — **gibt es im IONOS-Menü nicht.**
Dort lässt sich nur die Version wählen. Such nicht weiter danach.

Stattdessen liegen sie als Datei im Projekt (`public/.user.ini`) und gehen in
Schritt 3 ganz normal mit hoch. Du musst dafür nichts tun. IONOS liest die
Datei beim nächsten Aufruf, spätestens nach fünf Minuten.

> **Warum das überhaupt wichtig ist:** Ohne diese beiden Werte erlaubt der
> Server nur 2 MB je Datei. Handyfotos sind 3 bis 8 MB groß — Daniels Upload
> würde bei praktisch jedem Bild abbrechen.
>
> Ob es geklappt hat, siehst du am Ende in Schritt 8: Auf der Fotoseite im
> Bearbeitungsbereich steht die Größe, die der Server **wirklich** erlaubt.
> Die Seite behauptet dort nichts, sie rechnet nach.

## Schritt 2 · Verbindung zum Server herstellen

### FileZilla installieren

Falls noch nicht geschehen: <https://filezilla-project.org/download.php?type=client>

> **Auf der Seite stehen mehrere Knöpfe.** Du willst **„Download FileZilla
> Client"** — die kostenlose Fassung. Nicht *FileZilla Pro* (kostet Geld, wird
> hier nicht gebraucht) und nicht *FileZilla Server* (das ist das Gegenstück,
> das man selbst betreibt).

Auf dem Mac lädst du eine Datei mit `macos` im Namen. Doppelklick darauf im
Downloads-Ordner, dann die entstandene `FileZilla`-App in den Ordner
**Programme** ziehen.

Meldet macOS beim ersten Start, das Programm stamme von einem nicht
verifizierten Entwickler: Rechtsklick auf FileZilla → **Öffnen** → im Dialog
noch einmal **Öffnen**. Danach startet es künftig normal.

> **Alternative:** IONOS verlinkt auf derselben Seite, auf der die Zugänge
> stehen, auch **Cyberduck** — ebenfalls kostenlos und auf dem Mac etwas
> aufgeräumter. Die Schritte hier sind für FileZilla geschrieben; wenn du
> Cyberduck lieber magst, sag Bescheid, dann schreibe ich sie dafür um.

### Zugang bei IONOS vorbereiten

Im Kundenmenü nach **„Sichere FTP-Zugänge verwalten"** suchen. Dort steht
bereits ein Zugang, `u113483144`.

> **Den nimmst du — leg keinen neuen an.** Der Knopf „Neues Konto erstellen"
> steht daneben und lädt dazu ein, aber der vorhandene passt genau: In der
> Spalte *Verzeichnis* steht `/`, also die oberste Ebene, auf der `html` liegt
> und `neu` entstehen soll. Und in der Spalte *Protokoll* steht `SFTP + SSH` —
> beides, was du brauchst: SFTP für Schritt 3, SSH für Schritt 7.

**Passwort setzen.** Auf die drei Punkte (⋮) rechts in der Zeile, dann
*Passwort ändern*. Das bestehende Passwort zeigt IONOS nicht an, du musst also
ein neues vergeben — such nicht danach.

Zum Passwort selbst:

* **Nicht dasselbe wie fürs IONOS-Konto.** Dieser Zugang darf alles auf dem
  Webspace lesen und schreiben, später auch Daniels Fotos und die Anfragen mit
  Kundendaten.
* **Lang und zufällig** — Passwortgenerator, 20 Zeichen oder mehr.
  SSH-Zugänge werden rund um die Uhr automatisch durchprobiert. Bleib bei
  Buchstaben, Ziffern und einfachen Sonderzeichen; Umlaute machen in Terminal
  und FTP-Programmen manchmal Ärger.
* **Vorher wegspeichern.** Nach dem Klick auf *Speichern* zeigt IONOS es nie
  wieder, und du brauchst es zweimal: hier und in Schritt 7.

**Serveradresse holen.** Auf den Benutzernamen `u113483144` klicken — in der
Detailansicht steht der Name des Servers.

**In FileZilla eintragen**, oben in die Leiste:

| Feld | Wert |
|---|---|
| Server | `sftp://` und direkt dahinter die Serveradresse |
| Benutzername | `u113483144` |
| Passwort | das eben vergebene |
| Port | `22` |

> **Das `sftp://` vorne ist der entscheidende Teil.** Ohne diesen Vorsatz
> versucht FileZilla eine gewöhnliche, unverschlüsselte FTP-Verbindung auf
> einem anderen Anschluss — den es hier nicht gibt. Du bekommst dann nur
> „Verbindung fehlgeschlagen" und suchst den Fehler beim Passwort oder beim
> Servernamen, wo er nicht liegt.

Beim ersten Verbinden fragt FileZilla nach einem „unbekannten Hostschlüssel".
Das ist normal, einmal bestätigen.

**Geschafft, wenn:** Rechts in FileZilla erscheint eine Ordnerliste, in der
`html` steht. Dann bist du an der richtigen Stelle für Schritt 3.

## Schritt 3 · Die neue Seite in einen eigenen Ordner legen

Auf diesem Webspace liegt schon eine Website: die alte, unter `clean-box.eu`.
Laut deiner Domainübersicht steckt sie im Ordner **`html`** — in der Liste
steht bei `clean-box.eu` genau das als Ziel: `/html`.

> **In diesen Ordner kommt nichts hinein, und daraus wird nichts gelöscht.**
> Die alte Seite bleibt, wie sie ist, und läuft weiter.

Die neue Seite bekommt einen eigenen Ordner **daneben**. Leg ihn in FileZilla
mit Rechtsklick → „Verzeichnis erstellen" an und nenn ihn `neu`. Darin noch
einen mit dem Namen `web`. Am Ende sieht es so aus:

```
  (die oberste Ebene deines Vertrags)
  ├── html/         ← die alte Seite, unangetastet
  └── neu/
      ├── app/      ← nicht im Browser erreichbar
      ├── data/     ← nicht im Browser erreichbar
      ├── bin/      ← nicht im Browser erreichbar
      └── web/      ← hierauf zeigt gleich smartrepair-reutter.de
          ├── index.php
          ├── .htaccess
          ├── admin/
          ├── assets/
          └── uploads/
```

FileZilla ist zweigeteilt: **links dein MacBook, rechts der Server.** Links
klickst du dich zu dem entpackten Ordner aus Schritt 0 durch — meist
`fra` → `Downloads` → der Ordner mit dem langen Namen.

> **Erst die beiden Ordner anlegen, dann ziehen.** Wer gleich zieht, lädt alles
> auf die oberste Ebene ab, wo es nicht hingehört — und `app`, `bin` und `data`
> fehlen dann ganz, weil sie außerhalb von `public` liegen.

Gezogen wird von links nach rechts, und dabei kommt es jedes Mal darauf an,
**wo die rechte Seite steht**:

1. **Rechts in `neu` stehen.** Links die Ordner `app`, `data` und `bin`
   markieren und hinüberziehen.
2. **Rechts per Doppelklick nach `neu/web` wechseln.** Den Ordner **`public`
   ziehst du nicht mit** — stattdessen links per Doppelklick hineingehen, darin
   alles auswählen (⌘A) und **den Inhalt** hinüberziehen. Aus `public/index.php`
   wird also `neu/web/index.php`, nicht `neu/web/public/index.php`.
3. Alles andere im Projekt — `chats`, `project`, `export`, `docs`, `bildpool`,
   `api`, `vercel.json`, `README.md` — bleibt auf deinem Rechner. Das sind
   Arbeitsmaterialien, die auf dem Server nichts zu suchen haben.

> **Zwei Dateien mit einem Punkt am Anfang.** In `public/` liegen
> `.htaccess` (ohne sie läuft alles außer der Startseite ins Leere) und
> `.user.ini` (die beiden Größenwerte aus Schritt 1). Beide werden gebraucht.
>
> In der **linken** Hälfte von FileZilla siehst du sie ganz normal — anders als
> der Finder blendet FileZilla auf deinem Rechner nichts aus. Wenn du im Ordner
> `public` alles auswählst (⌘A) und hinüberziehst, sind sie also dabei.
>
> Rechts, auf dem Server, siehst du sie meist ebenfalls. Falls nicht:
> Menü **Server → Erzwinge Anzeigen versteckter Dateien**. Das ist eine reine
> Anzeigeeinstellung — hochgeladen wird unabhängig davon.

> **Der Punkt geht beim Herunterladen verloren.** Solange du aus dem
> entpackten Projektordner ziehst, stimmt alles. Aber sobald du eine einzelne
> solche Datei aus einer Nachricht oder von einer Webseite herunterlädst,
> speichern macOS und Chrome sie als `htaccess` oder `user.ini` — **ohne den
> Punkt**. Hochgeladen liegt sie dann neben der echten Datei und tut gar
> nichts.
>
> Zu erkennen ist das an zwei fast gleichen Namen im selben Ordner. Zu beheben
> so: erst die alte Datei **mit** Punkt löschen, dann die neue per Rechtsklick
> → *Umbenennen* auf den richtigen Namen setzen. In dieser Reihenfolge, weil
> Umbenennen auf eine vorhandene Datei fehlschlägt.

Warum dieser Umweg über zwei Ordner: `app` und `data` enthalten Passwörter und
Kundendaten und dürfen **nicht** über den Browser erreichbar sein. Deshalb
liegen sie eine Ebene über dem, worauf die Adresse zeigt. Und weil alles in
`neu/` zusammensteckt, wird am Umschalttag keine einzige Datei verschoben — es
wird nur die Adresse umgehängt.

**Geschafft, wenn:** In `neu` liegen `app`, `data`, `bin` und `web`, und in
`neu/web` liegt eine `index.php`.

> **Falls doch alles auf der obersten Ebene gelandet ist.** Passiert leicht,
> wenn man zieht, bevor die Ordner da sind. Es ist nichts kaputt und von außen
> ist nichts zu sehen, denn auf die oberste Ebene zeigt keine Domain.
>
> 1. `neu` und darin `web` anlegen wie oben
> 2. `app`, `bin` und `data` nach `neu` hochladen — die fehlen in diesem Fall
>    ganz, weil sie außerhalb von `public` liegen
> 3. Den Inhalt von `public` nach `neu/web` hochladen
> 4. Auf der obersten Ebene genau diese sechs löschen: `admin`, `assets`,
>    `uploads`, `.htaccess`, `.user.ini`, `index.php`
>
> **Beim Löschen nichts anderes anfassen.** Deine Sachen erkennst du in der
> Spalte *Last modified* am heutigen Datum; die Ordner von IONOS und der alten
> Seite — `backup`, `conf`, `files`, `html`, `log`, `phptmp`, `restore`,
> `.forward` — sind Jahre alt. **Eine Ausnahme:** `logs` trägt ebenfalls ein
> frisches Datum, gehört aber IONOS. Dort steht in der Spalte *Owner/Group*
> `root root` statt deiner Benutzernummer.

## Schritt 4 · Die Domain auf den neuen Ordner zeigen lassen

In deiner Domainübersicht steht bei `smartrepair-reutter.de` heute
„Domain nicht verwendet“ und daneben der Link „Domain verwenden“.
Genau den klickst du an.

Es öffnet sich eine lange Liste. Du willst die Zeile **„Webspace verbinden"** —
erkennbar an der Beschreibung *„Domain mit Website bzw. Ordner auf Ihrem
Webspace verbinden."* Danach kommt das Feld für den Ordner, dort trägst du
**`neu/web`** ein.

> **Drei Zeilen in derselben Liste klingen richtig und sind es nicht:**
>
> * **„Website erstellen"** installiert WordPress, Typo3 oder Ähnliches in den
>   Ordner — das würde die hochgeladenen Dateien überschreiben.
> * **„Bestehende Website verbinden"** meint Websites, die *bei IONOS* gebaut
>   wurden, also Homepage-Baukasten oder Shop. Nicht selbst hochgeladene
>   Dateien.
> * **„Externe Seite verbinden"** ist für Hosting bei einem anderen Anbieter.

> **Warum die echte Domain und nicht erst eine Testadresse:** Die Domain zeigt
> heute nirgendwohin, es kann also nichts kaputtgehen. Für Google ist die Seite
> ohnehin gesperrt, und niemand außer euch kennt die Adresse — sie ist seit ein
> paar Tagen registriert und stand nie irgendwo. Dafür ändert sich am
> Umschalttag an den Adressen **gar nichts** mehr: kein Umhängen, kein zweites
> Zertifikat, keine Testadresse, die man wieder abschalten muss. Das ist der
> Tag, an dem am wenigsten schiefgehen soll.

> **Das Sicherheitszertifikat nicht vergessen.** Bei `smartrepair-reutter.de`
> steht in der Liste ein rotes Schlosssymbol — es gibt noch keins. Rechts in
> der Übersicht steht „SSL-Zertifikate: 1 von 2 verwendet“, du hast
> also noch eines frei. Über „Verwalten“ der neuen Domain zuweisen.
>
> Beim Einrichten steht bei *Verwendungszweck* **„Für meine IONOS Website
> verwenden"** — das ist die richtige Wahl, IONOS installiert das Zertifikat
> dann selbst. Die Alternative wäre, es herunterzuladen und woanders einzubauen.
>
> IONOS stellt ein **Wildcard-Zertifikat** aus: Es sichert
> `smartrepair-reutter.de` und alles davor, also auch
> `www.smartrepair-reutter.de`. Für den Umschalttag ist damit kein zweites
> Zertifikat nötig.
>
> Ohne Zertifikat zeigt der Browser eine Sicherheitswarnung statt der Seite,
> weil die Website konsequent auf verschlüsselte Verbindungen umleitet. Nach
> dem Einrichten dauert es ein paar Minuten, bis es greift.

**Geschafft, wenn:** `https://smartrepair-reutter.de` zeigt die Website, mit
Bildern und Farben, ohne Warnung. Fehlt die Gestaltung und es kommt nur Text,
ist die `.htaccess` nicht mitgekommen — siehe Schritt 3.

## Schritt 5 · Die Gegenprobe · **nicht überspringen**

> Ruf diese beiden Adressen im Browser auf:
>
> * `smartrepair-reutter.de/data/users.php`
> * `smartrepair-reutter.de/app/bootstrap.php`
>
> **Beide müssen einen Fehler zeigen** — „404", „Nicht gefunden" oder
> „Zugriff verweigert". Das ist hier das gute Ergebnis.
>
> **Erscheint stattdessen Text oder lädt eine Datei herunter, ist Schritt 3
> schiefgegangen.** Dann liegen `app` und `data` versehentlich in `neu/web`,
> und damit stehen im Netz: das Passwort zum Bearbeitungsbereich, die
> Zugangsdaten fürs E-Mail-Postfach und die Namen, Telefonnummern und Fotos
> aller Kunden, die je das Formular ausgefüllt haben. Sofort beide Ordner aus
> `neu/web` löschen und Schritt 3 wiederholen.

## Schritt 6 · Postfach für das Kontaktformular — kann warten

**Dieser Schritt lässt sich aufschieben.** Ohne Postfach wird eine Anfrage
trotzdem gespeichert und steht im Bearbeitungsbereich unter „Anfragen von der
Website"; nur die Benachrichtigungsmail bleibt aus. Solange die Seite für
Google gesperrt ist und die Adresse niemand kennt, kommt ohnehin nichts herein.

**Vor dem Umschalttag muss er aber erledigt sein.** Ab dann kommen echte
Anfragen, und ein ausbleibender Versand meldet sich nicht von selbst — es fehlt
einfach eine Mail, die niemand vermisst.

Das Formular verschickt über ein echtes Postfach — nicht über einen anonymen
Versand, denn solche Mails landen im Spam.

1. Im Kundenmenü unter **E-Mail** eine neue Adresse anlegen:
   `info@smartrepair-reutter.de`. Dasselbe Postfach nimmt Anfragen entgegen und
   verschickt sie — ein Postfach weniger zu pflegen.
2. Auf deinem Rechner die Datei `app/config/zugangsdaten.beispiel.php` öffnen,
   die Werte eintragen (Passwort des Postfachs, nicht des IONOS-Kontos), unter
   dem Namen **`zugangsdaten.php`** speichern und nach `neu/app/config/`
   hochladen.

In der Beispieldatei steht bei jeder Zeile, was hineingehört.

> **Ohne diese Datei geht keine Mail raus.** Kaputt geht dabei nichts: Die
> Anfrage wird trotzdem gespeichert und steht im Bearbeitungsbereich unter
> „Anfragen von der Website". Nur die Benachrichtigung bleibt aus — und das
> merkt man erst, wenn man sie vermisst.

**Geschafft, wenn:** Die Datei liegt in `neu/app/config/` und heißt
`zugangsdaten.php`.

## Schritt 7 · Zugänge anlegen

Der Bearbeitungsbereich hat noch kein Passwort. Das legst du **auf dem Server**
an, und dafür brauchst du ein Fenster, in dem du Befehle tippst statt zu
klicken. Das ist alles, was hinter „SSH" steckt: dieselbe Verbindung wie bei
FileZilla, nur ohne Mausbedienung.

Insgesamt sind es acht Zeilen zum Tippen. Der Reihe nach.

### 7.1 · SSH bei IONOS einschalten

Im Kundenmenü zu **„Sichere FTP-Zugänge verwalten"**, dieselbe Seite wie in
Schritt 2. In der Spalte *Protokoll* muss bei `u113483144` **`SFTP + SSH`**
stehen. Steht dort nur `SFTP`, über die drei Punkte (⋮) SSH dazuschalten.

### 7.2 · Das Terminal öffnen

Auf dem Mac: **⌘ + Leertaste**, `Terminal` tippen, Enter. Es öffnet sich ein
Fenster mit einer Zeile Text und einem blinkenden Strich.

### 7.3 · Mit dem Server verbinden

Tipp diese Zeile und drück Enter — die Adresse ist dieselbe wie in FileZilla:

```
ssh u113483144@access975427118.webspace-data.io
```

Beim allerersten Mal fragt es:

```
Are you sure you want to continue connecting (yes/no/[fingerprint])?
```

Dann `yes` tippen und Enter. Ausgeschrieben, nicht nur `y`.

Danach kommt:

```
u113483144@access975427118.webspace-data.io's password:
```

> **Hier ist die Stelle, an der die meisten hängenbleiben:** Beim Tippen des
> Passworts **passiert auf dem Bildschirm nichts.** Keine Punkte, keine
> Sternchen, der Strich bewegt sich nicht. Das ist Absicht und kein Fehler —
> das Passwort wird trotzdem entgegengenommen. Einfach blind eintippen (oder
> einfügen) und Enter drücken.

Es ist dasselbe Passwort wie in FileZilla. Danach steht da eine
Begrüßungsmeldung und wieder ein blinkender Strich — du bist drin.

### 7.4 · In den richtigen Ordner wechseln

```
cd neu
```

`cd` heißt „wechsle in den Ordner". Es passiert sichtbar nichts, das ist
richtig so.

### 7.5 · Das Skript starten

```
php8.4-cli bin/passwort-setzen.php
```

> **Warum nicht einfach `php`:** Unter SSH liegt bei IONOS im Pfad die
> Fassung, die sonst Webseiten ausliefert. Die gibt nur
> `Content-type: text/html` aus und bricht ab. Die Kommandozeilen-Fassung
> heißt dort `php8.4-cli`. Welche es gibt, zeigt `ls /usr/bin/php*`.

Jetzt läuft ein Frage-und-Antwort-Spiel ab. So sieht es aus, hier mit
Beispielantworten:

```
Panel-Zugang fuer Smartrepair Reutter
--------------------------------------------
Benutzername (z. B. reutter): francesco
Anzeigename (z. B. Daniel Reutter): Francesco
Passwort (mindestens 12 Zeichen):
Passwort wiederholen:

Zugang gespeichert: francesco
Anmeldung unter /admin/
```

Was in die vier Zeilen gehört:

| Frage | Was du tippst |
|---|---|
| Benutzername | Womit du dich anmeldest. Klein, ohne Leerzeichen, z. B. `francesco` |
| Anzeigename | Wie du im Panel oben rechts stehst, z. B. `Francesco` |
| Passwort | **Mindestens 12 Zeichen**, sonst bricht es ab. Wieder unsichtbar |
| Passwort wiederholen | Dasselbe nochmal |

Das ist **nicht** dasselbe Passwort wie fürs Hochladen — das hier ist der
Zugang zum Bearbeitungsbereich. Ein eigenes nehmen und wegspeichern.

### 7.6 · Dasselbe nochmal für Daniel

```
php8.4-cli bin/passwort-setzen.php
```

Diesmal steht oben zusätzlich:

```
Vorhandene Zugaenge: francesco
```

Das ist der Beweis, dass nichts überschrieben wird — das Skript ergänzt nur.
Als Benutzername `daniel`, als Anzeigename `Daniel Reutter`, und ein Passwort,
das du ihm weitergeben kannst.

### 7.7 · Fenster schließen

```
exit
```

**Geschafft, wenn:** Zweimal „Zugang gespeichert" auf dem Bildschirm stand.

> **Wenn nur `Content-type: text/html` erscheint und sonst nichts:** Dann
> wurde `php` statt `php8.4-cli` benutzt — siehe den Kasten bei 7.5.
>
> **Wenn „Zu kurz" kommt:** Das Passwort hatte weniger als 12 Zeichen. Das
> Skript bricht dann ganz ab — einfach neu starten.
>
> **Wenn „Die Eingaben stimmen nicht ueberein" kommt:** Beim zweiten Mal hat
> sich ein Tippfehler eingeschlichen. Auch hier: neu starten.

## Schritt 8 · Ausprobieren

1. `smartrepair-reutter.de/admin/` aufrufen und anmelden.
2. Oben auf **„Fotos"** und ein Testbild hochladen. Läuft es durch, stimmen die
   Einstellungen aus Schritt 1.
3. Auf der Website einmal das Kontaktformular abschicken und schauen, ob die
   E-Mail ankommt.
4. Zum Schluss `smartrepair-reutter.de/robots.txt` aufrufen. Dort muss
   `Disallow: /` stehen — das ist die Sperre für Google, und sie soll jetzt
   noch stehen.

Im Bearbeitungsbereich steht oben ein Hinweis, dass die Seite für Google
gesperrt ist. **Der gehört da hin.** Er verschwindet am Umschalttag, wenn unter
*Stammdaten → Sichtbarkeit bei Google* die Adresse eingetragen wird — das ist
der letzte Handgriff des ganzen Projekts, und er dauert zehn Sekunden.

Fertig. Ab hier kann Daniel loslegen — seine Anleitung liegt in
[`anleitung-daniel.md`](anleitung-daniel.md), zum Weiterschicken gibt es sie
auch als Webseite fürs Handy (Link steht oben in der Datei). Seine Adresse
ist `smartrepair-reutter.de/admin/` — und die bleibt so, auch nach dem
Umschalttag.

---

## Beim nächsten Mal: was du nicht überschreiben darfst

Wenn ich etwas an der Website geändert habe und du die neue Fassung hochlädst,
lädst du wieder `app`, `bin` und den Inhalt von `public` hoch — genau wie beim
ersten Mal, in dieselben Ordner. FileZilla fragt dann bei jeder Datei, ob es
überschreiben soll: **ja, alle überschreiben.** Das ist harmlos.

**Gefährlich ist nur das Gegenteil:** einen Ordner auf dem Server erst zu
löschen oder zu leeren und dann neu hochzuladen. Denn an fünf Stellen liegen
Dinge, die es nur auf dem Server gibt:

| Auf dem Server | Was dort liegt |
|---|---|
| `neu/web/uploads/` | Alle Fotos, die Daniel hochgeladen hat. |
| `neu/data/anfragen/` | Die eingegangenen Anfragen mit Namen und Telefonnummern. |
| `neu/data/users.php` | Die Passwörter für den Bearbeitungsbereich. |
| `neu/data/fotos-posteingang.json` | Welche Fotos noch auf Einsortierung warten. |
| `neu/app/config/zugangsdaten.php` | Die Zugangsdaten fürs E-Mail-Postfach. |

Von diesen fünf gibt es keine Kopie bei mir. Also: **hochladen und
überschreiben lassen, nie vorher aufräumen.**

Und noch eine Bitte in die andere Richtung: **Von Daniels Fotos gibt es keine
zweite Kopie.** Wenn er größere Mengen hochgeladen hat, zieh
`neu/web/uploads/` einmal per FileZilla auf deinen Rechner und schick es mir —
dann liegt es mit im Projekt und ist gesichert.

## Wenn etwas klemmt

| Was du siehst | Woran es liegt |
|---|---|
| Sicherheitswarnung statt Seite | Das SSL-Zertifikat fehlt oder greift noch nicht. Schritt 4. |
| **Internal Server Error**, aber die Startseite geht | Die `.htaccess` heißt auf dem Server `htaccess`, ohne Punkt — siehe den Kasten unter Schritt 3. Die Startseite merkt das nicht, jede Unterseite schon. |
| **Internal Server Error** | Eine ungültige Zeile in einer `.htaccess`. Erkennungszeichen: Die Meldung sagt zusätzlich, dass auch beim Fehlerdokument ein 500er auftrat — dann scheitert jede Anfrage, bevor PHP überhaupt startet. Schick mir die Adresse, das ist ein Fehler in meiner Datei, nicht in deinem Vorgehen. |
| Weiße Seite, sonst nichts | Fast immer die PHP-Version. Steht sie auf 8.4? |
| „Domain nicht verwendet“ bleibt stehen | Das Ziel wurde nicht gespeichert. Schritt 4 noch einmal; IONOS braucht dafür manchmal einige Minuten. |
| Startseite geht, alles andere ist „404" | Die `.htaccess` fehlt in `neu/web`. Versteckte Dateien einblenden, Schritt 3. |
| Seite ohne Gestaltung, nur Text | Der Ordner `assets` fehlt, oder `public` wurde als Ordner hochgeladen statt sein Inhalt. |
| „Es ist noch kein Zugang eingerichtet" | Schritt 7 fehlt. |
| Foto-Upload bricht ab | Die `.user.ini` ist nicht mitgekommen oder greift noch nicht (bis zu fünf Minuten). Auf der Fotoseite steht, welche Größe der Server gerade erlaubt — steht dort 2 MB, fehlt die Datei in `neu/web`. Versteckte Dateien einblenden, Schritt 3. |
| Anfragen kommen an, aber keine Mail | Schritt 6 fehlt oder das Postfach-Passwort stimmt nicht. |
| Der Bearbeitungsbereich sagt „nur zum Ansehen" | `neu/data` ist nicht beschreibbar. In FileZilla Rechtsklick auf den Ordner → Dateirechte → 775. |
| Die alte Seite sieht plötzlich kaputt aus | Die PHP-Umstellung aus Schritt 1 hat sie erwischt. Zurückstellen und mir Bescheid sagen. |

Wenn keiner dieser Punkte passt: schick mir, was auf dem Bildschirm steht.
**Aber keine Zugangsdaten** — die brauche ich nicht und will ich nicht haben.

---

*Die technischen Hintergründe zu jedem Schritt stehen in
[`deployment.md`](deployment.md), der Ablauf des Umschalttags in
[`umzug.md`](umzug.md).*
