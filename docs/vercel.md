# Die Vorschau auf Vercel

Vercel ist nicht das Zuhause dieser Website — das ist IONOS. Die Vorschau gibt
es nur, um den Stand im Browser ansehen zu können, ohne vorher etwas
hochzuladen.

## Warum das überhaupt eine Frage ist

Andere Projekte sind statisch: fertige HTML-Dateien, der Server reicht sie
durch. Diese Seite ist PHP — der Server setzt jede Seite beim Aufruf aus
Vorlage und Inhaltsdateien zusammen. Genau das trägt später das CMS: Der Kunde
ändert einen Text, PHP schreibt ihn in die JSON-Datei, der nächste Besucher
sieht ihn. Bei einer statischen Seite gäbe es niemanden, der das entgegennehmen
könnte.

Vercel kann PHP über die Community-Runtime `vercel-php`. Eingetragen ist
`0.8.0` — das ist PHP 8.4, dieselbe Version wie später auf IONOS.

## Was auf Vercel nicht funktioniert

| | |
|---|---|
| **Speichern im Redaktionsbereich** | Das Dateisystem ist schreibgeschützt. Das Panel erkennt das und zeigt einen Hinweis statt eines Fehlers. |
| **Der Redaktionsbereich überhaupt** | `/admin/` läuft dort ins 404. Absicht: Ohne `data/users.php` — die liegt bewusst nicht im Git — könnte sich sowieso niemand anmelden, und ein öffentlich erreichbares Anmeldeformular ohne Zweck ist nur Angriffsfläche. |
| **Das Anfrageformular** | Braucht SMTP-Zugangsdaten, die es hier nicht gibt. |

Zum Ausprobieren des CMS: lokal mit `php -S localhost:8000 -t public`, oder auf
dem IONOS-Staging, sobald die Zugänge da sind.

## Die Konfiguration — und die eine Falle darin

`api/index.php` ist nur ein Einstiegspunkt und reicht an `public/index.php`
weiter. Auf IONOS wird die Datei nie angefasst.

`vercel.json` bildet ab, was auf IONOS die `.htaccess` macht: Assets und
Uploads direkt, alles andere an den Front-Controller, dazu dieselben
Sicherheits-Header inklusive CSP. `X-Robots-Tag: noindex` kommt dazu — eine
Vorschau gehört nicht in den Google-Index.

**Was hier schon einmal schiefgegangen ist:** In den Routen stand zusätzlich
`{ "handle": "filesystem" }`. Das heißt „sieh zuerst nach, ob es die Datei
gibt". Vercels Ausgabeverzeichnis ist `public/` — bei einem Aufruf von `/`
fand Vercel also `public/index.php` und lieferte sie als Datei aus, statt sie
auszuführen. Im Browser kam ein Download an.

Deshalb steht dort jetzt kein Dateisystem-Check mehr. Jede Anfrage, die nicht
`/assets/` oder `/uploads/` ist, geht direkt an die Funktion. Nebeneffekt, der
uns entgegenkommt: An eine `.php`-Datei im Quelltext kommt von außen niemand
mehr heran, weil keine Route dorthin zeigt.

## Wenn etwas nicht geht

| Was der Browser zeigt | Wo es klemmt |
|---|---|
| Es lädt eine Datei herunter | Eine Route zeigt auf eine Datei statt auf die Funktion. Siehe oben. |
| `404: NOT_FOUND` | Die Routen greifen nicht — `vercel.json` prüfen. |
| `500: FUNCTION_INVOCATION_FAILED` | PHP-Fehler. Runtime-Logs im Vercel-Dashboard unter der Deployment-Seite. |
| Seite ohne Gestaltung | `/assets/` wird nicht ausgeliefert. Dann steht das Ausgabeverzeichnis des Projekts nicht auf `public`. |
