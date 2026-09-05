# Hochladen auf IONOS

Stand: 04.09.2026 · was beim Deployment stimmen muss

---

## Der Ordnerbaum

`public/` wird **nicht als Ordner** hochgeladen — sein **Inhalt** kommt in den
Ordner, auf den die Domain zeigt. `app/` und `data/` liegen eine Ebene darüber.

Auf diesem Vertrag liegt bereits die alte Seite (`clean-box.eu` in `html/`).
Die neue Seite bekommt deshalb einen **eigenen Zweig daneben**, nicht deren
Verzeichnis:

```
/homepages/xx/dxxxxxxx/            ← Vertragswurzel, kein Web-Zugriff
├── html/                          ← clean-box.eu, alte Seite, unangetastet
└── neu/
    ├── app/                       ← Code, Templates, Schema
    ├── data/                      ← Inhalte, Anfragen, users.php
    ├── bin/
    └── web/                       ← hierhin zeigt smartrepair-reutter.de
        ├── index.php
        ├── .htaccess
        ├── admin/
        ├── assets/
        └── uploads/
            └── cache/
```

`app/bootstrap.php` setzt die Wurzeln relativ zueinander
(`BASE_ROOT = dirname(__DIR__)`), deshalb muss diese Verschachtelung stimmen.
Wie der Zweig heißt, ist dagegen egal — nur die Ebenen müssen passen.

**Warum getrennt und nicht in `html/`:** Die alte Seite bleibt so während der
gesamten Bauzeit online, und am Umschalttag wird keine einzige Datei verschoben
— `smartrepair-reutter.de` zeigt von Anfang an auf `neu/web/`. Der in
`umzug.md` beschriebene Schritt „Verzeichnis leeren" betrifft danach nur noch
`html/`, das ab dann ausschließlich Weiterleitungen für `clean-box.eu`
ausliefert.

> **Die Falle:** Wer das Repository versehentlich komplett in die Dokumentwurzel
> schiebt, macht `data/users.php` (Passwort-Hash), `app/config/zugangsdaten.php`
> (SMTP) und `data/anfragen/` (Namen, Telefonnummern und Fotos fremder Kunden)
> über den Browser erreichbar. Die `.htaccess` fängt das nicht ab — sie kennt
> diese Ordner gar nicht.
>
> **Gegenprobe nach jedem Deployment:** `curl -I https://DOMAIN/data/users.php`
> und `https://DOMAIN/app/bootstrap.php` müssen 403 oder 404 liefern.

## PHP-Einstellungen

| Einstellung | Standard | Gebraucht | Warum |
|---|---|---|---|
| PHP-Version | — | **8.4** | Voraussetzung für den Betrieb |
| `upload_max_filesize` | oft 2M | **mindestens 12M** | Handyfotos sind 3–8 MB, neuere iPhones mehr |
| `post_max_size` | oft 8M | **mindestens 12M** | muss über `upload_max_filesize` liegen |

**Ohne diese beiden Werte funktioniert der Foto-Upload nicht.** Die Seite
rechnet die tatsächliche Grenze selbst aus (`bild_grenze_text()`) und schreibt
sie in die Hilfe, statt 12 MB zu behaupten — der Benutzer sieht also, was
wirklich geht. Aber bei 2 MB scheitert praktisch jedes Handyfoto.

**Bei IONOS stehen die beiden Größenwerte nicht im Kundenmenü** — dort lässt
sich nur die Version wählen. Deshalb liegen sie als `public/.user.ini` im
Repository und gehen mit dem Inhalt von `public/` hoch. Beide sind
`PHP_INI_PERDIR`, PHP liest die Datei vom Skriptverzeichnis bis zur
Dokumentwurzel — der Front-Controller liegt genau dort, also gilt sie für die
ganze Seite. Änderungen greifen erst nach bis zu fünf Minuten
(`user_ini.cache_ttl`), und ohne CGI/FastCGI wird die Datei stillschweigend
ignoriert.

## Was beim Hochladen **nicht** mit hochgeladen wird

| Pfad | Warum |
|---|---|
| `web/uploads/` | Was der Betrieb selbst hochlädt, liegt nur auf dem Server. Blind überschreiben löscht seine Fotos |
| `data/anfragen/` | Personenbezogene Daten fremder Kunden, gehören nicht ins Repository |
| `data/users.php` | Passwort-Hashes, wird auf dem Server angelegt |
| `data/fotos-posteingang.json` | Zwischenstand, gehört dem Server |
| `app/config/zugangsdaten.php` | SMTP-Zugang. Vorlage: `zugangsdaten.beispiel.php` liegt im Repository, kopieren und ausfüllen |

Umgekehrt gilt: **Es gibt keine Sicherung der hochgeladenen Fotos.** Nach
größeren Uploads durch den Betrieb `web/uploads/` per SFTP herunterladen und
mitcommitten — sonst hängt alles an der IONOS-Sicherung.

## Zugänge anlegen

```
php bin/passwort-setzen.php
```

Das Skript lädt vorhandene Benutzer und **ergänzt** sie, überschreibt also
nichts. Mehrere Konten sind damit ohne Codeänderung möglich — Daniel bekommt
ein eigenes, damit es einzeln gewechselt und gesperrt werden kann.

Rollen gibt es keine: Wer angemeldet ist, darf alles. Das ist Absicht — am
Ende des Projekts ist das CMS Daniels Selbstpflegesystem.

## Sichtbarkeit bei Google

`seo_indexierbar()` (`app/lib/seo.php`) gibt nur `true` zurück, wenn der
aufgerufene Host der Adresse in `site.json` → `seo.live_domain` entspricht.
Ist das Feld leer, liefert `robots.txt` ein `Disallow: /` und jede Seite trägt
ein `noindex` — **unabhängig vom Host**.

Das ist die sichere Richtung: Eine unfertige Seite, die in den Index rutscht,
ist wochenlang nicht mehr herauszubekommen; eine vergessene Freigabe kostet
eine Minute. Damit sie nicht vergessen wird, zeigt `/admin/` ein Warnband,
solange die aufgerufene Adresse gesperrt ist.

Der Vergleich ist exakt und ignoriert nur `https://`, einen Schrägstrich am
Ende und ein führendes `www.`. `smartrepair-reutter.de` passt damit
**nicht** auf `smartrepair-reutter.de` — die Testadresse bleibt gesperrt, ohne
dass dafür etwas Zusätzliches konfiguriert werden muss.

Am Umschalttag wird das Feld im Panel gefüllt. Kein Deployment nötig.
