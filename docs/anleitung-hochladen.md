# Die Website auf IONOS bringen

Stand: 05.09.2026 · Anleitung zum Mitklicken

---

## Vorweg: Warum überhaupt IONOS, wo doch alles auf Vercel läuft?

Vercel ist das **Schaufenster**. Die Adresse dort zeigt die Website, damit man
sich den Stand ansehen kann, ohne etwas hochzuladen. Mehr macht sie nicht:
Vercel kann nichts **speichern**. Keinen geänderten Text, kein hochgeladenes
Foto, keine Anfrage aus dem Kontaktformular. Deshalb ist der Bereich `/admin/`
dort gar nicht erreichbar — ein Anmeldeformular, hinter dem nichts funktioniert,
wäre nur verwirrend.

IONOS ist das **Zuhause**. Ganz normaler Webspace, und dort darf geschrieben
werden. Erst wenn die Website dort liegt, gibt es:

* den Bearbeitungsbereich unter `deine-domain.de/admin/`
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

## Schritt 3 · Die Dateien an die richtige Stelle legen

Das ist der einzige Schritt, bei dem es wirklich auf Genauigkeit ankommt.

Auf dem Server gibt es einen Ordner, auf den deine Domain zeigt. Bei IONOS
heißt er üblicherweise `htdocs`. Alles, was **darin** liegt, ist über den
Browser erreichbar. Alles, was **daneben** liegt, nicht.

Genau diese Trennung braucht die Website:

```
  (die oberste Ebene deines Vertrags)
  ├── app/            ← nicht im Browser erreichbar
  ├── data/           ← nicht im Browser erreichbar
  ├── bin/            ← nicht im Browser erreichbar
  └── htdocs/         ← hierauf zeigt die Domain
      ├── index.php
      ├── .htaccess
      ├── admin/
      ├── assets/
      └── uploads/
```

Und so wird aus dem Projekt auf deinem Rechner dieser Baum:

1. Die Ordner **`app`, `data` und `bin`** ziehst du unverändert auf die oberste
   Ebene — also **neben** `htdocs`, nicht hinein.
2. Den Ordner **`public` ziehst du nicht mit.** Stattdessen öffnest du ihn und
   ziehst **seinen Inhalt** nach `htdocs`. Aus `public/index.php` wird also
   `htdocs/index.php`, nicht `htdocs/public/index.php`.
3. Alles andere im Projekt — `chats`, `project`, `export`, `docs`, `bildpool`,
   `api`, `vercel.json`, `README.md` — bleibt auf deinem Rechner. Das sind
   Arbeitsmaterialien, die auf dem Server nichts zu suchen haben.

Die `.user.ini` aus Schritt 1, falls du sie brauchst, kommt nach `htdocs`.

> **Die versteckten Dateien:** FileZilla blendet Dateien aus, deren Name mit
> einem Punkt beginnt — und genau so heißt `.htaccess`, ohne die die Website
> nicht läuft. Im Menü unter „Server" den Punkt **„Versteckte Dateien
> anzeigen"** einschalten, sonst fehlt sie hinterher.

> **Falls es kein `htdocs` gibt** und die Domain direkt auf die oberste Ebene
> zeigt: Dann musst du das erst umstellen. Im Kundenmenü bei der Domain gibt es
> eine Einstellung dafür — sie heißt „Ziel", „Zielverzeichnis" oder
> „Domain-Ziel". Dort legst du einen Unterordner an und wählst ihn aus. Ohne
> diese Trennung geht Schritt 4 schief.

**Geschafft, wenn:** Rechts stehen auf der obersten Ebene `app`, `data`, `bin`
und `htdocs`, und in `htdocs` liegt eine `index.php`.

## Schritt 4 · Die Gegenprobe · **nicht überspringen**

> Ruf diese beiden Adressen im Browser auf, mit deiner Domain davor:
>
> * `deine-domain.de/data/users.php`
> * `deine-domain.de/app/bootstrap.php`
>
> **Beide müssen einen Fehler zeigen** — „404", „Nicht gefunden" oder
> „Zugriff verweigert". Das ist hier das gute Ergebnis.
>
> **Erscheint stattdessen Text oder lädt eine Datei herunter, ist Schritt 3
> schiefgegangen.** Dann liegen `app` und `data` versehentlich in `htdocs`, und
> damit stehen im Netz: das Passwort zum Bearbeitungsbereich, die Zugangsdaten
> fürs E-Mail-Postfach und die Namen, Telefonnummern und Fotos aller Kunden,
> die je das Formular ausgefüllt haben. Sofort beide Ordner aus `htdocs`
> löschen und Schritt 3 wiederholen.

## Schritt 5 · Zugänge anlegen

Der Bearbeitungsbereich hat noch kein Passwort — das legst du auf dem Server an.
Dafür brauchst du **SSH**: eine Textkonsole zum Server. Im Kundenmenü ist das
derselbe Bereich wie SFTP in Schritt 2, meist mit einem eigenen Schalter zum
Aktivieren.

Auf dem Mac öffnest du „Terminal", unter Windows die „Eingabeaufforderung", und
tippst (mit deinen Angaben aus Schritt 2):

```
ssh BENUTZERNAME@SERVERADRESSE
```

Dann, auf der obersten Ebene:

```
php bin/passwort-setzen.php
```

Das Skript fragt nach Benutzername, Anzeigename und Passwort. Mindestens
12 Zeichen, sonst bricht es ab. Führ es **zweimal** aus: einmal für dich, einmal
für Daniel. Es ergänzt vorhandene Zugänge und überschreibt nichts.

> **Kein SSH im Vertrag?** Dann sag mir Bescheid — es gibt einen zweiten Weg
> über zwei Einträge in der `.htaccess`. Der ist umständlicher, deshalb steht er
> hier nicht als Standard.

**Geschafft, wenn:** In der Konsole steht „Zugang gespeichert".

## Schritt 6 · Ausprobieren

1. `deine-domain.de` aufrufen — die Website erscheint mit Bildern und Farben.
   Fehlt die Gestaltung, ist die `.htaccess` nicht mitgekommen (siehe Schritt 3).
2. `deine-domain.de/admin/` aufrufen und anmelden.
3. Oben auf **„Fotos"** und ein Testbild hochladen. Läuft es durch, stimmen die
   Einstellungen aus Schritt 1.
4. Auf der Website einmal das Kontaktformular abschicken und schauen, ob die
   E-Mail ankommt.

Fertig. Ab hier kann Daniel loslegen — seine Anleitung liegt in
[`anleitung-daniel.md`](anleitung-daniel.md), zum Weiterschicken gibt es sie
auch als Webseite fürs Handy (Link steht oben in der Datei).

---

## Beim nächsten Mal: was du nicht überschreiben darfst

Wenn ich etwas an der Website geändert habe und du die neue Fassung hochlädst,
lädst du wieder `app`, `bin` und den Inhalt von `public` hoch — genau wie beim
ersten Mal. FileZilla fragt dann bei jeder Datei, ob es überschreiben soll:
**ja, alle überschreiben.** Das ist harmlos.

**Gefährlich ist nur das Gegenteil:** einen Ordner auf dem Server erst zu
löschen oder zu leeren und dann neu hochzuladen. Denn an fünf Stellen liegen
Dinge, die es nur auf dem Server gibt:

| Auf dem Server | Was dort liegt |
|---|---|
| `htdocs/uploads/` | Alle Fotos, die Daniel hochgeladen hat. |
| `data/anfragen/` | Die eingegangenen Anfragen mit Namen und Telefonnummern. |
| `data/users.php` | Die Passwörter für den Bearbeitungsbereich. |
| `data/fotos-posteingang.json` | Welche Fotos noch auf Einsortierung warten. |
| `app/config/zugangsdaten.php` | Die Zugangsdaten fürs E-Mail-Postfach. |

Von diesen fünf gibt es keine Kopie bei mir. Also: **hochladen und
überschreiben lassen, nie vorher aufräumen.**

Und noch eine Bitte in die andere Richtung: **Von Daniels Fotos gibt es keine
zweite Kopie.** Wenn er größere Mengen hochgeladen hat, zieh
`htdocs/uploads/` einmal per FileZilla auf deinen Rechner und schick es mir —
dann liegt es mit im Projekt und ist gesichert.

## Wenn etwas klemmt

| Was du siehst | Woran es liegt |
|---|---|
| Weiße Seite, sonst nichts | Fast immer die PHP-Version. Steht sie auf 8.4? |
| Startseite geht, alles andere ist „404" | Die `.htaccess` fehlt in `htdocs`. Versteckte Dateien einblenden, Schritt 3. |
| Seite ohne Gestaltung, nur Text | Der Ordner `assets` fehlt in `htdocs`, oder `public` wurde als Ordner hochgeladen statt sein Inhalt. |
| „Es ist noch kein Zugang eingerichtet" | Schritt 5 fehlt. |
| Foto-Upload bricht ab | Die Werte aus Schritt 1 sind nicht angekommen. Auf der Fotoseite steht, welche Größe der Server gerade erlaubt — steht dort 2 MB, hat IONOS die Einstellung nicht übernommen. |
| Der Bearbeitungsbereich sagt „nur zum Ansehen" | `data/` ist nicht beschreibbar. In FileZilla Rechtsklick auf den Ordner → Dateirechte → 775. |

Wenn keiner dieser Punkte passt: schick mir, was auf dem Bildschirm steht.
**Aber keine Zugangsdaten** — die brauche ich nicht und will ich nicht haben.

---

*Die technischen Hintergründe zu jedem Schritt — warum die Ordner so liegen
müssen, was die Werte bedeuten — stehen in [`deployment.md`](deployment.md).*
