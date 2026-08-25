# Umzug auf `smartrepair-reutter.de`

Stand: 25.08.2026

Die alte Seite liegt unter `clean-box.eu`. Der Betrieb heißt künftig
**Smartrepair Reutter**, deshalb wechselt zugleich die Domain. Das sind zwei
Wechsel auf einmal — neue Adresse **und** neue Inhalte — und damit die
riskanteste Variante für die Sichtbarkeit bei Google. Machbar ist sie, wenn
die Weiterleitungen sitzen.

## Was uns dabei hilft

`basis_url()` in `app/lib/seo.php` liest den Host aus dem Request statt aus
einer Einstellung. Canonical, Sitemap, JSON-LD und die Vorschaubilder folgen
der neuen Domain **ohne Codeänderung**. Auch das `noindex` der
Vercel-Vorschauen fällt automatisch weg, sobald die echte Domain darauf zeigt.

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

1. **Verzeichnis leeren, nicht überschreiben.** Weg müssen alle `*.html`,
   `index.php`, `robots.txt`, `sitemap.xml` sowie `logs/`, `counter/`,
   `cgi-bin/`.
   > Die Falle: Die `.htaccess` schickt nur an den Front-Controller, was keine
   > echte Datei ist. Bleibt die alte `robots.txt` von 2011 liegen, liefert
   > Apache weiter sie aus — die generierte läuft nie. Ergebnis wäre eine neue
   > Website, die Google die Sitemap von 2011 zeigt und `/css/` sperrt.
2. Neue Seite hochladen, Umzugsblock in der `.htaccess` scharfschalten
3. **Gegenprobe:** `curl https://www.smartrepair-reutter.de/robots.txt` muss
   den neuen Text zeigen. Dasselbe für `sitemap.xml`
4. Google-Unternehmensprofil: Website-Adresse auf die neue Domain ändern.
   Beim Namenswechsel das stärkste Signal, das wir haben
5. Search Console: neue Sitemap einreichen, die alte **nicht** löschen —
   Google arbeitet sie ab und lernt daraus die Weiterleitungen
6. Adressänderungs-Werkzeug in der alten Property auslösen

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
`info@clean-box.eu`. Dazu ein getrenntes `website@` für den Formularversand,
damit `info@` unangetastet bleibt. **SPF und DKIM für die neue Domain setzen** —
sonst landen die Formularmails im Spam.
