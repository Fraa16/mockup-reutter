# Umzug auf `smartrepair-reutter.de`

Stand: 05.09.2026 · Domain registriert, Testaufbau vorbereitet

Die alte Seite liegt unter `clean-box.eu`. Der Betrieb heißt künftig
**Smartrepair Reutter**, deshalb wechselt zugleich die Domain. Das sind zwei
Wechsel auf einmal — neue Adresse **und** neue Inhalte — und damit die
riskanteste Variante für die Sichtbarkeit bei Google. Machbar ist sie, wenn
die Weiterleitungen sitzen.

## Der Aufbau, der den Umschalttag entschärft

`smartrepair-reutter.de` ist registriert und liegt im **selben IONOS-Konto**
(Vertrag 97829981) wie `clean-box.eu`. Die neue Seite wird deshalb nicht in das
Verzeichnis der alten gelegt (`html/`), sondern in einen eigenen Zweig `neu/`
daneben — siehe `deployment.md`.

`smartrepair-reutter.de` zeigt von Anfang an auf `neu/web/`; unsichtbar bleibt
die Seite über `seo.live_domain`, nicht über eine geheime Adresse. Damit wird
am Umschalttag **weder eine Datei verschoben noch eine Adresse umgehängt** —
es bleibt das Freigeben und das Umstellen der alten Domain. Die alte Seite
läuft bis dahin unangetastet weiter, und ein Rückweg ist jederzeit möglich,
solange `html/` noch steht.

## Was uns dabei hilft

`basis_url()` in `app/lib/seo.php` liest den Host aus dem Request statt aus
einer Einstellung. Canonical, Sitemap, JSON-LD und die Vorschaubilder folgen
der neuen Domain **ohne Codeänderung**.

Die Freigabe für Google hängt dagegen an genau einem Feld: `site.json` →
`seo.live_domain`, im Panel unter *Stammdaten → Sichtbarkeit bei Google*.
Solange es leer ist, ist **nichts** indexierbar — auch der Testaufbau nicht.
Das Panel warnt sichtbar, solange die Sperre greift.

## Was noch fehlt

Die vollständige Liste der alten Adressen. Aus dieser Umgebung ist
`clean-box.eu` nicht erreichbar — der Proxy lässt sie nicht durch. Die Liste
muss deshalb von außen kommen:

```bash
# 1 — Alles, was intern verlinkt ist
wget --spider -r -l inf -np -e robots=off \
     --reject-regex '\.(jpg|jpeg|png|gif|css|js|ico)$' \
     https://www.clean-box.eu/ 2>&1 \
  | grep -oE 'https?://[^ ]*clean-box\.eu[^ ]*' | sort -u > alte-urls.txt

# 2 — Verwaiste Seiten, die nicht mehr verlinkt sind, aber noch ranken
curl -s "http://web.archive.org/cdx/search/cdx?url=clean-box.eu*&fl=original&collapse=urlkey&limit=1000" \
  > archiv-urls.txt
```

Dazu der Seitenbericht aus der Search Console (*Seiten → Indexiert →
Exportieren*).

## Weiterleitungen

Der vorbereitete Block steht in `public/.htaccess`, auskommentiert. Regeln:

- **Zuordnung vor Kanonisierung**, mit absoluten Zielen. Sonst läuft jede alte
  Adresse über zwei Weiterleitungen statt über eine.
- **Nie pauschal auf die Startseite.** Google wertet das als Soft-404 und wirft
  die Platzierung der Einzelseite weg. Jede alte Adresse bekommt ein
  thematisch passendes Ziel.
- Jede Regel wird einzeln geprüft: genau ein 301, das Ziel liefert 200, keine
  Kette, keine Schleife.

Bekannt sind bisher `/beklebung.html` und `/gallerie_beklebung.html` →
`/leistungen/lackierarbeiten/#beklebung`, und `/ozonbehandlung.html` →
`/leistungen/ozonbehandlung/`.

## Search Console

**Zwei** Properties, nicht eine:

| Property | Wofür |
|---|---|
| `clean-box.eu` | Seitenbericht der alten Seite, Adressänderungs-Werkzeug |
| `smartrepair-reutter.de` | ab dem Umschalttag |

Die alte Property **jetzt** anlegen, nicht später: Der Seitenbericht ist
rückwirkend — was Google heute im Index hat, sieht man sofort nach der
Bestätigung. Klicks und Impressionen dagegen fangen bei null an. Ohne die
Property fehlt der Vergleichsmaßstab für „hat der Umzug geschadet?".

Bestätigung per **URL-Präfix** und HTML-Datei ins Wurzelverzeichnis; die
Domain-Property bräuchte einen DNS-Eintrag.

## Umschalttag

Die Dateien liegen zu diesem Zeitpunkt seit Wochen an Ort und Stelle. Es wird
nichts hochgeladen und nichts verschoben.

1. **`www.smartrepair-reutter.de` einrichten** und ebenfalls auf `neu/web/`
   zeigen lassen. Die Domain selbst zeigt bereits dorthin — hier fehlt nur die
   `www.`-Schreibweise, auf die die `.htaccess` kanonisiert. Das
   Wildcard-Zertifikat von `smartrepair-reutter.de` deckt sie mit ab, ein
   zweites Zertifikat ist nicht nötig.
2. **Umzugsblock in der `.htaccess` scharfschalten** — erst jetzt, vorher
   sperrt die Kanonisierung die Seite aus.
3. **`seo.live_domain` im Panel eintragen** (*Stammdaten → Sichtbarkeit bei
   Google*): `smartrepair-reutter.de`. Ohne diesen Schritt bleibt die Seite
   dauerhaft unsichtbar. Das Warnband im Panel verschwindet, sobald es sitzt.
4. **Gegenprobe:** `curl https://www.smartrepair-reutter.de/robots.txt` muss
   `Allow: /` und die Sitemap-Zeile zeigen. Dasselbe für `sitemap.xml`.
   Zusätzlich eine beliebige Seite auf `noindex` prüfen — darf nicht mehr
   drinstehen.
5. **`html/` leeren** — erst jetzt, und nur dort. Weg müssen alle `*.html`,
   `index.php`, `robots.txt`, `sitemap.xml` sowie `logs/`, `counter/`,
   `cgi-bin/`. Danach kommt dorthin eine `.htaccess`, die ausschließlich
   weiterleitet.
   > Die Falle: Die `.htaccess` schickt nur an den Front-Controller, was keine
   > echte Datei ist. Bliebe die alte `robots.txt` von 2011 liegen, lieferte
   > Apache weiter sie aus — die generierte liefe nie.
6. Google-Unternehmensprofil: Website-Adresse auf die neue Domain ändern.
   Beim Namenswechsel das stärkste Signal, das wir haben
7. Search Console: neue Sitemap einreichen, die alte **nicht** löschen —
   Google arbeitet sie ab und lernt daraus die Weiterleitungen
8. Adressänderungs-Werkzeug in der alten Property auslösen

## Danach

`clean-box.eu` bleibt **dauerhaft** bezahlt und zeigt nur noch 301er. Läuft
sie aus, sterben alle Links, Verzeichniseinträge und gedruckten Verweise.
Keine Kopie der Seite dort liegen lassen — sonst stehen zwei Websites im
Index.

Verzeichniseinträge nachziehen, überall steht heute `clean-box.eu`:
cylex, 11880, lokalwissen, deutschbranchenbuch, youdriver,
stuttgarter-zeitung.de/unternehmen.

Vier Wochen wöchentlich in der Search Console:

- *Seiten* → tauchen **404 auf, die vorher indexiert waren**? Dann fehlt eine Regel
- *Seiten* → „Seite mit Weiterleitung" soll die **alten** Adressen zeigen
- Kein „Alternative Seite mit richtigem kanonischen Tag" auf der falschen Host-Variante

Schwankungen in den ersten zwei Wochen sind normal, auch nach unten. Nicht in
Panik zurückdrehen. Die Weiterleitungen bleiben dauerhaft.

## Postfach

Neues Postfach auf der neuen Domain, Weiterleitung vom alten
`info@clean-box.eu`. **SPF und DKIM für die neue Domain setzen** — sonst landen
die Formularmails im Spam.

**Entschieden am 05.09.2026: ein Postfach, `info@smartrepair-reutter.de`**, für
Empfang und Formularversand zusammen. Die ursprünglich vorgesehene Trennung mit
einem eigenen `website@` war ein Nice-to-have — sie hätte `info@` davor
geschützt, von Problemen beim automatischen Versand berührt zu werden. Für
einen Betrieb dieser Größe wiegt ein Postfach weniger zu pflegen schwerer.

**Zwei Dinge hängen daran, und beide gehören vor den Umschalttag:**

* Das Postfach muss stehen und der Versand einmal geprüft sein. Ohne
  `app/config/zugangsdaten.php` wird jede Anfrage zwar gespeichert und im
  Panel angezeigt, aber nicht verschickt — und das fällt niemandem auf, weil
  eine fehlende Mail nichts meldet.
* `kontakt.email` in den Stammdaten steht noch auf `info@clean-box.eu` und
  erscheint damit im Impressum und im Fußbereich. Ob Daniel die Adresse
  wechseln will, entscheidet **er**: Visitenkarten, Google-Profil und
  Kundschaft mit der alten Adresse im Telefon hängen daran. Die Weiterleitung
  vom alten Postfach macht beide Wege gangbar.
