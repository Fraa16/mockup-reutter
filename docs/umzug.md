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

Die vollständige Liste der alten Adressen. Aus dieser Umgebung ist weder
`clean-box.eu` noch das Internet-Archiv erreichbar — der Proxy lässt beides
nicht durch. Die Liste muss deshalb von außen kommen.

**Der wget-Befehl, der hier früher stand, hilft auf einem Mac nicht weiter:**
wget gehört nicht zum Lieferumfang von macOS, der Aufruf endet in
`command not found`. Vier Wege, vom geringsten Aufwand aufwärts:

1. **`clean-box.eu/sitemap.xml` und `/robots.txt` im Browser aufrufen.**
   Dreißig Sekunden, und alte Baukasten- und CMS-Seiten haben oft eine, ohne
   dass es jemand weiß. Hier stand frueher, damit sei die Liste vollständig —
   dieser Fall hat das widerlegt: Die Sitemap ist von 2011 und nennt eine
   fremde, längst tote Domain. Sie ist ein Anhaltspunkt, kein Nachweis; den
   liefert erst Nummer 2.

2. **Search Console, Property `clean-box.eu`: *Seiten → Indexiert →
   Exportieren*.** Die wertvollste Liste, denn sie enthält genau die Adressen,
   die Google kennt und die Besucher bringen. Rückwirkend verfügbar, sobald
   die Property bestätigt ist.

3. **Screaming Frog SEO Spider** — kostenlos bis 500 Seiten, Mac-Programm mit
   Oberfläche. Findet auch, was Google nicht indexiert hat.

4. Nur wenn es unbedingt das Terminal sein soll: Homebrew installieren, dann
   `brew install wget`, dann der Befehl unten. Aufwändiger als die ersten drei.

```bash
# Alles, was intern verlinkt ist — setzt ein installiertes wget voraus
wget --spider -r -l inf -np -e robots=off \
     --reject-regex '\.(jpg|jpeg|png|gif|css|js|ico)$' \
     https://www.clean-box.eu/ 2>&1 \
  | grep -oE 'https?://[^ ]*clean-box\.eu[^ ]*' | sort -u > alte-urls.txt
```

Verwaiste Seiten, die nicht mehr verlinkt sind, aber noch ranken, kennt das
Internet-Archiv:

```bash
curl -s "http://web.archive.org/cdx/search/cdx?url=clean-box.eu*&fl=original&collapse=urlkey&limit=1000" \
  > archiv-urls.txt
```

## Weiterleitungen

Der vorbereitete Block steht in `public/.htaccess`, auskommentiert. Regeln:

- **Zuordnung vor Kanonisierung**, mit absoluten Zielen. Sonst läuft jede alte
  Adresse über zwei Weiterleitungen statt über eine.
- **Nie pauschal auf die Startseite.** Google wertet das als Soft-404 und wirft
  die Platzierung der Einzelseite weg. Jede alte Adresse bekommt ein
  thematisch passendes Ziel.
- Jede Regel wird einzeln geprüft: genau ein 301, das Ziel liefert 200, keine
  Kette, keine Schleife.

**Erledigt am 11.09.2026.** Die `sitemap.xml` der alten Seite lieferte
vierzehn Adressen; dazu kamen fünf, die dort fehlen, weil die Seiten nach 2011
entstanden: `ozonbehandlung`, `beklebung`, `gallerie_beklebung`, `datenschutz`
und `agb`. Alles steht in `public/.htaccess`, auskommentiert bis zum
Umschalttag.

Wer nachzählt, kommt auf **achtzehn** eigene `RewriteRule`-Zeilen und nicht auf
neunzehn Adressen. Das ist richtig so: Eine der vierzehn Adressen aus der
Sitemap ist die blanke Startseite `/`, und die braucht keine eigene Regel — sie
läuft über die Kanonisierung ganz am Ende des Blocks auf die neue Startseite.
Bleiben dreizehn plus die fünf nachgetragenen, macht achtzehn Zeilen, und mit
der Kanonisierung neunzehn Regeln.

### `datenschutz` und `agb` — gefunden über `site:`

Beide standen in keiner Liste, die wir hatten. Gefunden hat sie die Suche nach
`site:clean-box.eu`, also genau der Weg, der oben unter Punkt 1 steht.

Das ist der Grund, warum sich der Handgriff lohnt, auch wenn er unscheinbar
aussieht: Ohne ihn wäre ausgerechnet die **Datenschutzerklärung** der alten
Seite im 404 gelandet — eine Adresse, die aus jedem Impressum und aus jedem
Formular der alten Seite verlinkt war. Die AGB lagen daneben, im Auszug datiert
auf den 13.06.2018.

Zwei Beobachtungen aus derselben Suche, beide mit Folgen:

- **Google zeigt mindestens eine Seite ohne `www`** (`http://clean-box.eu ›
  fahrzeugpflege_interieur`), die übrigen mit. Eine URL-Präfix-Property hätte
  je nach Wahl die eine oder die andere Hälfte nicht gesehen. Die
  Domain-Property war also nicht nur die bequemere, sondern die einzig
  richtige Wahl. Für die Weiterleitungen selbst ist es folgenlos: Die Regeln
  greifen über den Pfad, nicht über den Host.
- **Google blendet die Dateiendung aus** — angezeigt wird `… › datenschutz`,
  nicht `datenschutz.html`. Für die fünf nachgetragenen Adressen ist die
  Endung damit *nicht* belegt. Ihre Regeln tragen deshalb
  `(\.html?|\.php)?` statt eines festen `.html`. Zu breit zu greifen kostet
  hier nichts — die Namen kommen auf der neuen Website nicht vor. Eine falsch
  geratene Endung kostet eine indexierte Seite.

Die Suche meldete außerdem, sie habe *„einige Einträge ausgelassen, die den 10
angezeigten Treffern sehr ähnlich sind"*. Es kann also noch mehr geben; der
Seitenbericht der Search Console klärt das abschließend.

Die Sitemap selbst ist ein Fundstück: Sie trägt `lastmod` vom 21.05.2011 und
nennt durchgehend `www.stuttgart-hagelschaden.de` — eine Domain, die heute
nicht mehr auflöst. Dasselbe bei der `robots.txt`, die zusätzlich `/css/`
sperrt. Beide Dateien liegen in `html/` und sind nach dem Umschalten aus dem
Weg.

## Search Console

**Zwei** Properties, nicht eine:

| Property | Wofür |
|---|---|
| `clean-box.eu` | Seitenbericht der alten Seite, Adressänderungs-Werkzeug |
| `smartrepair-reutter.de` | ab dem Umschalttag |

Die alte Property **jetzt** anlegen, nicht später: Der Seitenbericht ist
rückwirkend — er zeigt, was Google im Index hat, nicht erst das, was ab heute
passiert. Klicks und Impressionen dagegen fangen bei null an. Ohne die Property
fehlt der Vergleichsmaßstab für „hat der Umzug geschadet?".

> **Direkt nach der Bestätigung ist der Bericht leer.** Es steht dort *„Die
> Daten werden verarbeitet — bitte versuch es in einem Tag noch einmal"*, und
> zwar in beiden Kästen. Das ist kein Fehler und kein Zeichen dafür, dass die
> Bestätigung nicht gegriffen hat: Google baut die Berichte für eine neue
> Property erst auf. Ein bis drei Tage sind normal. Hier stand frueher, man
> sehe den Bestand „sofort nach der Bestätigung" — das stimmt nicht.
>
> Ein weiterer Grund, die Property früh anzulegen: Diese Wartezeit will man
> nicht am Umschalttag haben.
>
> Solange der Bericht lädt, gibt es zwei Auskünfte, die sofort funktionieren:
>
> * **`site:clean-box.eu`** in die normale Google-Suche. Braucht keinen Login
>   und zeigt ungefähr, welche Adressen im Index stehen. Nicht vollständig und
>   nicht verbindlich, aber in dreißig Sekunden da.
> * **URL-Prüfung** oben in der Search Console. Sie arbeitet sofort und
>   beantwortet für **eine** Adresse verbindlich, ob Google sie kennt — gut, um
>   einzelne alte Adressen gezielt nachzuschlagen.

Bestätigung als **Domain-Property über einen TXT-Eintrag im DNS**, nicht per
URL-Präfix. Hier stand frueher das Gegenteil.

Eine URL-Präfix-Property deckt immer genau eine Schreibweise ab — und welche
Schreibweise die alte Seite im Index hat, wissen wir nicht. Die Sitemap hilft
dabei ausdrücklich **nicht** weiter (siehe unten), es gibt also keine Quelle,
aus der sich die richtige Variante ableiten liesse. Man saehe demnach nur einen
Teil des Index und wuesste nicht einmal, welchen.

Die Domain-Property deckt alle vier Varianten auf einmal ab und macht die Frage
gegenstandslos. Der TXT-Eintrag ist bei IONOS unter *Domains & SSL → Domain →
DNS* in einer Minute gesetzt: Typ `TXT`, Host `@`, Wert der
`google-site-verification=…`-Text aus der Search Console.

> **Die `sitemap.xml` der alten Seite ist wertlos — auch als Quelle für die
> Weiterleitungen.** Sie liegt zwar unter `clean-box.eu/sitemap.xml`, aber
> jede der vierzehn Adressen darin lautet `http://www.stuttgart-hagelschaden.de/…`.
> Diese Domain löst heute nicht mehr auf (geprüft am 11.09.2026, kein
> DNS-Eintrag); `clean-box.eu` dagegen schon.
>
> Eine Sitemap, die auf eine fremde Domain zeigt, verwertet Google für die
> eigene Property nicht. Was Google unter `clean-box.eu` im Index hat, stammt
> also **nicht** aus dieser Datei, sondern aus dem normalen Crawl. Daraus folgt
> zweierlei: Die Adressliste für die Weiterleitungen kann die Sitemap nur als
> Anhaltspunkt liefern, nicht als Nachweis — verbindlich ist allein der
> Seitenbericht (*Indexierung → Seiten → Exportieren*). Und die Zahl der
> „erkannten Seiten" in der Sitemap-Übersicht ist ohne Aussagekraft: Sie steht
> dort auf 16, zuletzt gelesen am 20.09.2022, während die Datei heute vierzehn
> Adressen enthält.

> **Die Verknüpfung von IONOS ablehnen — sie wirft das Postfach ab.**
> Google bietet für IONOS-Domains eine Schaltfläche an, die den TXT-Eintrag
> automatisch setzt („Domain Connect"). Die Bestätigungsseite meldet dabei, sie
> müsse *„nicht vereinbare DNS-Einträge entfernen"* — und listet den
> **MX-Eintrag** auf, `@ → mx00.ionos.de`. Das ist genau der Eintrag, über den
> `info@clean-box.eu` seine Mails bekommt.
>
> Ein Klick auf „Verbinden" legt also das Postfach still, und zwar lautlos:
> Eingehende Mails prallen ab, und eine ausbleibende Mail meldet sich nicht.
> Die Automatik ersetzt die ganze Zone durch eine Vorlage, statt einen Eintrag
> zu ergänzen.
>
> Also **„Nein"**, und den TXT-Eintrag in der normalen DNS-Verwaltung von Hand
> anlegen: *Domains & SSL → Domain → DNS → Record hinzufügen*, Typ `TXT`,
> Host `@`. Danach prüfen, dass der MX-Eintrag unverändert dasteht. Mehrere
> TXT-Einträge nebeneinander sind normal — SPF und DKIM kommen später dazu.
>
> Dieselbe Falle stellt sich bei `smartrepair-reutter.de`, sobald dort ein
> Postfach eingerichtet ist.

## Umschalttag

Die Dateien liegen zu diesem Zeitpunkt seit Wochen an Ort und Stelle. Es wird
nichts hochgeladen und nichts verschoben.

**Der Zuschnitt hat sich vereinfacht:** `html/` wird nicht mehr leergeräumt und
mit einer Weiterleitungsdatei bestückt. Stattdessen zeigen **beide Domains auf
`neu/web/`**, und der Umzugsblock in der einen `.htaccess` erledigt die
Weiterleitungen. Damit ist auch die alte Falle vom Tisch: Die `robots.txt` und
`sitemap.xml` von 2011 liegen in `html/` und werden schlicht nicht mehr
ausgeliefert.

1. **`www.smartrepair-reutter.de` einrichten** und auf `neu/web/` zeigen lassen.
   Das Wildcard-Zertifikat deckt die Schreibweise mit ab.
2. **`clean-box.eu` und `www.clean-box.eu` auf `neu/web/` umhängen.** Ab diesem
   Moment ist die alte Seite offline und alles läuft über die Weiterleitungen.
3. **Umzugsblock in der `.htaccess` scharfschalten** — die Rautezeichen vor den
   `RewriteRule`- und `RewriteCond`-Zeilen entfernen.
4. **`seo.live_domain` im Panel eintragen** (*Stammdaten → Sichtbarkeit bei
   Google*): `smartrepair-reutter.de`. Ohne diesen Schritt bleibt die Seite
   dauerhaft unsichtbar. Das Warnband im Panel verschwindet, sobald es sitzt.
5. **Die beiden Hinweisbänder abschalten**, in `impressum.json` und
   `datenschutz.json` jeweils `im_aufbau` auf `false` — aber erst, wenn die
   darin genannte Bedingung wirklich erfüllt ist.
6. **Gegenprobe** — mit `http://`, nicht `https://`:

   ```bash
   curl -sIL http://www.clean-box.eu/ozonbehandlung.html | grep -iE '^(HTTP|location)'
   ```

   Es darf **genau ein** `301` erscheinen, direkt auf
   `https://www.smartrepair-reutter.de/leistungen/ozonbehandlung/`, gefolgt von
   einem `200`. Das `http` ist der Kern der Prüfung: Die alte Seite lief nur
   über http, also lauten alle Adressen im Google-Index so. Mit `https` geprüft
   sähe eine Kette aus zwei Sprüngen genauso gut aus wie ein einzelner.

   Dazu: `curl https://www.smartrepair-reutter.de/robots.txt` muss `Allow: /`
   liefern, und eine beliebige Seite darf kein `noindex` mehr tragen.
7. Google-Unternehmensprofil: Website-Adresse auf die neue Domain ändern.
   Beim Namenswechsel das stärkste Signal, das wir haben.
8. **Search Console: die neue Sitemap einreichen** — `sitemap.xml`, in der
   Property `smartrepair-reutter.de`. Sie wird vom CMS erzeugt und ist damit
   immer vollständig.

   Die **alte** Sitemap bringt dabei nichts. Hier stand frueher, man solle sie
   stehen lassen, weil Google daraus die Weiterleitungen lerne — das war
   falsch: Ihre Adressen zeigen auf `stuttgart-hagelschaden.de`, eine Domain
   ohne DNS-Eintrag (siehe den Kasten oben). Google kann daraus nichts
   ablaufen, also lernt es daraus auch keine Weiterleitung. Sie in der alten
   Property zu entfernen ist sauberer, als sie als Dauerfehler stehen zu
   lassen; nötig ist beides nicht.

   Was die Weiterleitungen tatsächlich bekannt macht, sind die 301er selbst:
   Google ruft die alten Adressen von sich aus wieder auf, weil sie im Index
   stehen, und sieht dabei das Ziel. Beschleunigen lässt sich das über Schritt 9
   und über *URL-Prüfung → Indexierung beantragen* für die wichtigsten drei bis
   vier alten Adressen.
9. Adressänderungs-Werkzeug in der alten Property auslösen.

`html/` kann danach in Ruhe archiviert und gelöscht werden. Solange es steht,
ist der Rückweg offen.

> **Ein Detail, das leicht übersehen wird:** Auch `clean-box.eu/robots.txt`
> läuft über die Kanonisierung auf die neue Domain. Das ist richtig so —
> Googlebot folgt bei der robots.txt bis zu fünf Weiterleitungen und benutzt
> die am Ende. Bekäme die alte Domain dagegen ihre eigene robots.txt, stünde
> dort `Disallow: /`, weil `seo_indexierbar()` nur die eingetragene Livedomain
> freigibt — und Google käme nie dazu, die Weiterleitungen überhaupt zu sehen.

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
