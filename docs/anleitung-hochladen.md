# Die Website auf IONOS bringen

Stand: 05.09.2026 · Anleitung zum Mitklicken

---

## Wo wir stehen

Das hier ist **nicht** der Livegang. Es ist der Aufbau eines Probeexemplars auf
dem echten Server, unter einer eigenen Testadresse. Drei Phasen:

1. **Jetzt:** Die Seite kommt auf IONOS, unter `test.smartrepair-reutter.de`.
   Für Google ist sie gesperrt, niemand außer euch findet sie. Damit ist
   Daniels Foto-Upload freigeschaltet — das ist der eigentliche Zweck.
2. **Danach:** Daniel lädt Fotos hoch, ihr füllt die letzten Lücken.
3. **Zum Schluss:** Der Umschalttag. Dann zeigt `smartrepair-reutter.de` auf
   dieselben Dateien, `clean-box.eu` leitet nur noch weiter, und Google
   bekommt die Seite frei. Das steht in `umzug.md` und ist ein eigener Termin.

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

* den Bearbeitungsbereich unter `test.smartrepair-reutter.de/admin/`
* Daniels Foto-Upload vom Handy
* das Kontaktformular, das wirklich E-Mails verschickt

Beides bleibt nebeneinander bestehen und stört sich nicht. Vercel ist die
Vorschau für Zwischenstände, IONOS die echte Website.

## Was du dafür brauchst

* Zugang zum IONOS-Kundenmenü
* Ein Programm zum Dateiübertragen. **FileZilla** ist kostenlos und läuft auf
  Mac und Windows: <https://filezilla-project.org> — die *Client*-Fassung, nicht
  den Server.
* Die Dateien des Projekts auf deinem Rechner, so wie sie sind
* Ungefähr eine Stunde beim ersten Mal

Die Menüs bei IONOS heißen je nach Vertrag und Jahr etwas anders. Ich schreibe
deshalb dazu, **wonach** du suchst, nicht nur, wo es angeblich steht.

---

## Schritt 1 · PHP einstellen

PHP ist die Technik, mit der der Server die Seiten zusammensetzt — dasselbe,
was später Daniels Änderungen entgegennimmt. IONOS hat das eingebaut, es muss
nur richtig eingestellt sein.

Im IONOS-Kundenmenü den Bereich für **PHP** suchen — meist unter „Hosting" oder
„Webspace", dann „PHP-Einstellungen" oder „PHP konfigurieren".

Drei Werte setzen:

| Was | Worauf |
|---|---|
| PHP-Version | **8.4** |
| `upload_max_filesize` | **12M** |
| `post_max_size` | **12M** |

Die letzten beiden sind die maximale Größe einer hochgeladenen Datei. IONOS
liefert oft 2 MB aus — damit scheitert praktisch jedes Handyfoto, denn die sind
3 bis 8 MB groß. Ohne diesen Schritt funktioniert Daniels Foto-Upload nicht.

> **Achtung, die alte Seite läuft mit.** Falls sich die PHP-Version nur für den
> ganzen Vertrag umstellen lässt und nicht je Ordner: Prüf danach kurz, ob
> `clean-box.eu` noch normal aussieht. Alte Seiten vertragen neue PHP-Versionen
> nicht immer. Sieht sie kaputt aus, stell zurück und sag mir Bescheid — dann
> lösen wir das anders.

> **Falls du die Felder nicht findest:** Sie lassen sich auch als Datei setzen.
> Lege eine Textdatei namens `.user.ini` an mit genau diesen zwei Zeilen —
> wo sie hinkommt, steht in Schritt 3:
>
> ```
> upload_max_filesize = 12M
> post_max_size = 12M
> ```

**Geschafft, wenn:** In der Übersicht steht PHP 8.4.

## Schritt 2 · Verbindung zum Server herstellen

Im Kundenmenü nach **SFTP** oder **„SFTP & SSH"** suchen und dort einen Zugang
anlegen. IONOS zeigt dir danach vier Angaben: Serveradresse, Benutzername,
Passwort und Port (meist 22).

Diese vier trägst du in FileZilla oben in die Leiste ein und klickst
„Verbinden". Links siehst du deinen Rechner, rechts den Server.

> **Zwei Dinge zum Zugang:** Vergib ein eigenes Passwort, nicht dasselbe wie
> fürs IONOS-Konto. Und wenn du fertig bist, kannst du den Zugang im
> Kundenmenü einfach wieder löschen — beim nächsten Mal legst du einen neuen an.

**Geschafft, wenn:** Rechts in FileZilla siehst du Ordner des Servers.

## Schritt 3 · Die neue Seite in einen eigenen Ordner legen

Auf diesem Webspace liegt schon eine Website: die alte, unter `clean-box.eu`.
Sie steckt in einem Ordner, der meist `htdocs` heißt — du erkennst ihn an
Dateien wie `index.php`, an vielen `.html`-Dateien und oft an Ordnern namens
`logs` oder `cgi-bin`.

> **In diesen Ordner kommt nichts hinein, und daraus wird nichts gelöscht.**
> Die alte Seite bleibt, wie sie ist, und läuft weiter.

Die neue Seite bekommt einen eigenen Ordner **daneben**. Leg ihn in FileZilla
mit Rechtsklick → „Verzeichnis erstellen" an und nenn ihn `neu`. Darin noch
einen mit dem Namen `web`. Am Ende sieht es so aus:

```
  (die oberste Ebene deines Vertrags)
  ├── htdocs/       ← die alte Seite, unangetastet
  └── neu/
      ├── app/      ← nicht im Browser erreichbar
      ├── data/     ← nicht im Browser erreichbar
      ├── bin/      ← nicht im Browser erreichbar
      └── web/      ← hierauf zeigt gleich die Testadresse
          ├── index.php
          ├── .htaccess
          ├── admin/
          ├── assets/
          └── uploads/
```

Und so wird aus dem Projekt auf deinem Rechner dieser Baum:

1. Die Ordner **`app`, `data` und `bin`** ziehst du unverändert nach `neu`.
2. Den Ordner **`public` ziehst du nicht mit.** Stattdessen öffnest du ihn und
   ziehst **seinen Inhalt** nach `neu/web`. Aus `public/index.php` wird also
   `neu/web/index.php`, nicht `neu/web/public/index.php`.
3. Alles andere im Projekt — `chats`, `project`, `export`, `docs`, `bildpool`,
   `api`, `vercel.json`, `README.md` — bleibt auf deinem Rechner. Das sind
   Arbeitsmaterialien, die auf dem Server nichts zu suchen haben.

Die `.user.ini` aus Schritt 1, falls du sie brauchst, kommt nach `neu/web`.

> **Die versteckten Dateien:** FileZilla blendet Dateien aus, deren Name mit
> einem Punkt beginnt — und genau so heißt `.htaccess`, ohne die die Website
> nicht läuft. Im Menü unter „Server" den Punkt **„Versteckte Dateien
> anzeigen"** einschalten, sonst fehlt sie hinterher.

Warum dieser Umweg über zwei Ordner: `app` und `data` enthalten Passwörter und
Kundendaten und dürfen **nicht** über den Browser erreichbar sein. Deshalb
liegen sie eine Ebene über dem, worauf die Adresse zeigt. Und weil alles in
`neu/` zusammensteckt, wird am Umschalttag keine einzige Datei verschoben — es
wird nur die Adresse umgehängt.

**Geschafft, wenn:** In `neu` liegen `app`, `data`, `bin` und `web`, und in
`neu/web` liegt eine `index.php`.

## Schritt 4 · Die Testadresse einrichten

Im Kundenmenü zu den **Domains**, dort `smartrepair-reutter.de` auswählen und
eine **Subdomain** anlegen: `test.smartrepair-reutter.de`. Kostet nichts, ist
in zwei Minuten erledigt.

Bei der Subdomain trägst du als **Ziel** (heißt je nach Ansicht „Zielordner"
oder „Zielverzeichnis") den Ordner **`neu/web`** ein.

> **Die Falle: das Sicherheitszertifikat.** Die neue Adresse braucht ein
> eigenes SSL-Zertifikat — bei IONOS gibt es dafür einen Schalter an der
> Subdomain, oft „SSL aktivieren". Ohne das zeigt der Browser eine
> Sicherheitswarnung statt der Seite, weil die Website konsequent auf
> verschlüsselte Verbindungen umleitet. Das Zertifikat braucht danach ein paar
> Minuten, bis es greift.

**Geschafft, wenn:** `https://test.smartrepair-reutter.de` zeigt die Website,
mit Bildern und Farben, ohne Warnung. Fehlt die Gestaltung und es kommt nur
Text, ist die `.htaccess` nicht mitgekommen — siehe Schritt 3.

## Schritt 5 · Die Gegenprobe · **nicht überspringen**

> Ruf diese beiden Adressen im Browser auf:
>
> * `test.smartrepair-reutter.de/data/users.php`
> * `test.smartrepair-reutter.de/app/bootstrap.php`
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

## Schritt 6 · Postfach für das Kontaktformular

Das Formular auf der Website verschickt E-Mails über ein echtes Postfach —
nicht über einen anonymen Versand, denn solche Mails landen im Spam.

1. Im Kundenmenü unter **E-Mail** eine neue Adresse anlegen:
   `website@smartrepair-reutter.de`. Bewusst ein eigenes Postfach und nicht
   `info@` — wenn beim automatischen Versand etwas klemmt, soll das nicht das
   Postfach betreffen, über das Daniel seine Kundschaft erreicht.
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

Der Bearbeitungsbereich hat noch kein Passwort — das legst du auf dem Server an.
Dafür brauchst du **SSH**: eine Textkonsole zum Server. Im Kundenmenü ist das
derselbe Bereich wie SFTP in Schritt 2, meist mit einem eigenen Schalter zum
Aktivieren.

Auf dem Mac öffnest du „Terminal", unter Windows die „Eingabeaufforderung", und
tippst (mit deinen Angaben aus Schritt 2):

```
ssh BENUTZERNAME@SERVERADRESSE
```

Dann wechselst du in den neuen Ordner und startest das Skript:

```
cd neu
php bin/passwort-setzen.php
```

Das Skript fragt nach Benutzername, Anzeigename und Passwort. Mindestens
12 Zeichen, sonst bricht es ab. Führ es **zweimal** aus: einmal für dich, einmal
für Daniel. Es ergänzt vorhandene Zugänge und überschreibt nichts.

> **Kein SSH im Vertrag?** Dann sag mir Bescheid — es gibt einen zweiten Weg
> über zwei Einträge in der `.htaccess`. Der ist umständlicher, deshalb steht er
> hier nicht als Standard.

**Geschafft, wenn:** In der Konsole steht „Zugang gespeichert".

## Schritt 8 · Ausprobieren

1. `test.smartrepair-reutter.de/admin/` aufrufen und anmelden.
2. Oben auf **„Fotos"** und ein Testbild hochladen. Läuft es durch, stimmen die
   Einstellungen aus Schritt 1.
3. Auf der Website einmal das Kontaktformular abschicken und schauen, ob die
   E-Mail ankommt.
4. Zum Schluss `test.smartrepair-reutter.de/robots.txt` aufrufen. Dort muss
   `Disallow: /` stehen — das ist die Sperre für Google, und sie soll jetzt
   noch stehen.

Im Bearbeitungsbereich steht oben ein Hinweis, dass die Seite für Google
gesperrt ist. **Der gehört da hin.** Er verschwindet am Umschalttag, wenn unter
*Stammdaten → Sichtbarkeit bei Google* die Adresse eingetragen wird — das ist
der letzte Handgriff des ganzen Projekts, und er dauert zehn Sekunden.

Fertig. Ab hier kann Daniel loslegen — seine Anleitung liegt in
[`anleitung-daniel.md`](anleitung-daniel.md), zum Weiterschicken gibt es sie
auch als Webseite fürs Handy (Link steht oben in der Datei). Seine Adresse ist
vorerst `test.smartrepair-reutter.de/admin/`.

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
| Sicherheitswarnung statt Seite | Das SSL-Zertifikat der Testadresse fehlt oder greift noch nicht. Schritt 4. |
| Weiße Seite, sonst nichts | Fast immer die PHP-Version. Steht sie auf 8.4? |
| Startseite geht, alles andere ist „404" | Die `.htaccess` fehlt in `neu/web`. Versteckte Dateien einblenden, Schritt 3. |
| Seite ohne Gestaltung, nur Text | Der Ordner `assets` fehlt, oder `public` wurde als Ordner hochgeladen statt sein Inhalt. |
| „Es ist noch kein Zugang eingerichtet" | Schritt 7 fehlt. |
| Foto-Upload bricht ab | Die Werte aus Schritt 1 sind nicht angekommen. Auf der Fotoseite steht, welche Größe der Server gerade erlaubt — steht dort 2 MB, hat IONOS die Einstellung nicht übernommen. |
| Anfragen kommen an, aber keine Mail | Schritt 6 fehlt oder das Postfach-Passwort stimmt nicht. |
| Der Bearbeitungsbereich sagt „nur zum Ansehen" | `neu/data` ist nicht beschreibbar. In FileZilla Rechtsklick auf den Ordner → Dateirechte → 775. |
| Die alte Seite sieht plötzlich kaputt aus | Die PHP-Umstellung aus Schritt 1 hat sie erwischt. Zurückstellen und mir Bescheid sagen. |

Wenn keiner dieser Punkte passt: schick mir, was auf dem Bildschirm steht.
**Aber keine Zugangsdaten** — die brauche ich nicht und will ich nicht haben.

---

*Die technischen Hintergründe zu jedem Schritt stehen in
[`deployment.md`](deployment.md), der Ablauf des Umschalttags in
[`umzug.md`](umzug.md).*
