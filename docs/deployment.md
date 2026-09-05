# Hochladen auf IONOS

Stand: 04.09.2026 · was beim Deployment stimmen muss

---

## Der Ordnerbaum

`public/` wird **nicht als Ordner** hochgeladen — sein **Inhalt** kommt in die
Dokumentwurzel. `app/` und `data/` liegen eine Ebene darüber:

```
/homepages/xx/dxxxxxxx/            ← Vertragswurzel, kein Web-Zugriff
├── app/                           ← Code, Templates, Schema
├── data/                          ← Inhalte, Anfragen, users.php
├── bin/
└── htdocs/                        ← hierhin zeigt die Domain
    ├── index.php
    ├── .htaccess
    ├── admin/
    ├── assets/
    └── uploads/
        └── cache/
```

`app/bootstrap.php` setzt die Wurzeln relativ zueinander
(`BASE_ROOT = dirname(__DIR__)`), deshalb muss diese Verschachtelung stimmen.

> **Die Falle:** Wer das Repository versehentlich komplett nach `htdocs/`
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

Bei IONOS stehen beide Werte im Kundenmenü unter PHP-Einstellungen, alternativ
in einer `.user.ini` in der Dokumentwurzel.

## Was beim Hochladen **nicht** mit hochgeladen wird

| Pfad | Warum |
|---|---|
| `public/uploads/` | Was der Betrieb selbst hochlädt, liegt nur auf dem Server. Blind überschreiben löscht seine Fotos |
| `data/anfragen/` | Personenbezogene Daten fremder Kunden, gehören nicht ins Repository |
| `data/users.php` | Passwort-Hashes, wird auf dem Server angelegt |
| `data/fotos-posteingang.json` | Zwischenstand, gehört dem Server |
| `app/config/zugangsdaten.php` | SMTP-Zugang |

Umgekehrt gilt: **Es gibt keine Sicherung der hochgeladenen Fotos.** Nach
größeren Uploads durch den Betrieb `public/uploads/` per SFTP herunterladen und
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
