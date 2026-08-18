# Suchbegriffe, Ortsstrategie und Fragen

Stand: 18.08.2026 · Grundlage für die Textüberarbeitung nach dem Gespräch mit
Daniel

Diese Recherche liegt **vor** der Überarbeitung, damit die Texte einmal
angefasst werden und nicht zweimal. Umgesetzt wird nichts davon jetzt.

## Wie die Begriffe gewichtet sind — und wie nicht

**Hier stehen keine Suchvolumina.** Dafür bräuchte es Google Keyword Planner,
ahrefs oder ein vergleichbares Werkzeug; ohne Zugang wäre jede Zahl erfunden,
und erfundene Zahlen in einer Keyword-Tabelle sind schädlicher als gar keine.

Gewichtet wird stattdessen nach **beobachtbarem Signal**: Taucht der Begriff in
den Titeln der Wettbewerber auf? Führen mehrere Anbieter ihn? Erscheint er in
den Ergebnissen zu einer Nutzerfrage? Jeder Begriff unten trägt seine Herkunft.

Zweite Einschränkung: Ich konnte die Seiten **nicht öffnen**, nur die
Trefferlisten lesen — der Netzwerk-Proxy dieser Umgebung blockiert das Abrufen
fremder Domains. Alles unten stammt aus Titeln und Beschreibungen in den
Suchergebnissen. Für die Frage „welcher Begriff wird im Markt verwendet" reicht
das; für „wie ist die Seite intern aufgebaut" nicht.

> **Und daraus folgt eine dritte, die ich in der ersten Fassung übersehen
> hatte:** Ein Suchtreffer belegt, dass eine Seite **einmal** existiert hat —
> nicht, dass sie **heute** erreichbar ist. Suchindizes und Branchenverzeichnisse
> führen abgeschaltete Seiten noch lange weiter. Wo unten von fremden Seiten die
> Rede ist, ist das eine Aussage über den Index, nicht über den Livezustand.

---

## A · Ortsstrategie

### Empfehlung: Stuttgart nach vorn, Korntal-Münchingen als Standort

Beobachtet:

- **Wettbewerber aus dem Umland führen durchgängig „Stuttgart" im Titel**, auch
  wenn sie gar nicht dort sitzen: Anbieter aus Backnang, Ostfildern, Fellbach
  und Esslingen tun es. Der Ortsanker richtet sich nach dem gesuchten Markt,
  nicht nach der Meldeadresse.
- **Reutters frühere Seiten machten es ebenso** („im Raum Stuttgart") — die
  sind allerdings abgeschaltet, der Titel stammt aus dem Suchindex. Zählt daher
  als Randnotiz, nicht als Beleg; siehe unten.
- Korntal-Münchingen hat rund 19 000 Einwohner. Stuttgart rund 600 000, dazu
  Ludwigsburg, Gerlingen, Ditzingen, Leonberg, Weilimdorf im Einzugsgebiet.

Die Empfehlung steht damit auf den beiden ersten Punkten — Wettbewerbertitel und
Einwohnerzahlen. Beide sind unabhängig davon, was mit den alten Seiten passiert
ist.

Konkret: **„Stuttgart" in Titel und Kicker, „Korntal-Münchingen" in Beschreibung,
Adressblock und strukturierten Daten.** Der Standort verschwindet nicht — er
wandert nur von der Werbefläche in die Faktenfläche, wo Google ihn für den
lokalen Bezug ohnehin ausliest.

### Ortsseiten: ja, aber später und nur mit echtem Inhalt

Der direkte Nachbar **Car Clean Company** fährt genau dieses Muster: eigene
Seiten für Gerlingen, Hemmingen und Korntal-Münchingen. Es funktioniert also im
lokalen Wettbewerb.

Für uns trotzdem **nicht zum Start**. Fünf Ortsseiten, die sich nur im
Ortsnamen unterscheiden, sind aus Googles Sicht dünner, duplizierter Inhalt und
können der ganzen Domain schaden. Sinnvoll werden sie erst mit echtem Bezug —
eigene Arbeiten aus dem Ort, Anfahrtszeit, Besonderheiten. Also frühestens,
wenn Daniels Fotos da sind.

**Der stärkere lokale Hebel liegt ohnehin außerhalb der Website:** das
Google-Unternehmensprofil (Frage A4) und die Verzeichniseinträge — siehe
Abschnitt F.

---

## A2 · Zwei abgeschaltete Vorgängerseiten

Bei der Recherche tauchten zwei weitere Domains des Betriebs in den
Trefferlisten auf: `stuttgart-hagelschaden.de` mit Startseite,
Dellenbeseitigung, Ozonbehandlung, Galerie und Impressum, sowie
`stuttgart-dellendoktor.de` mit Bildergalerie und Anfahrt.

**Beide sind nicht mehr erreichbar.** Nachgemessen:

| Domain | Auflösung |
|---|---|
| `clean-box.eu` | löst auf → IONOS |
| `stuttgart-hagelschaden.de` | **löst nicht auf** |
| `stuttgart-dellendoktor.de` | **löst nicht auf** |
| `fahrzeugpflege-reutter.de` | löst nicht auf |

Ob die beiden **nicht mehr registriert** sind oder nur **nirgendwohin zeigen**,
lässt sich von hier nicht feststellen: `whois` steht nicht zur Verfügung, und
der Egress-Proxy blockiert DNS-Abfragen über HTTPS. Das ist eine offene Frage,
keine Feststellung.

**Was daraus folgt — und was nicht.** Weiterleitungen sind kein Thema: von einer
Domain, die nicht auflöst, lässt sich nichts umleiten; sie müsste erst
zurückgeholt werden. Und an der Domainfrage aus Block D ändert sich nichts. Sie
bleibt: `clean-box.eu` behalten oder auf eine neue Domain wechseln — mit
Weiterleitung von `clean-box.eu`, der einzigen lebenden Adresse.

Interessant bleibt nur der **Titel**, den die Altseite im Index trägt:

> „Fahrzeugpflege Reutter – Dellendoktor – Hagelschaden Reparatur und
> Auto-Aufbereitung **im Raum Stuttgart**"

So hat sich der Betrieb selbst positioniert — auf Stuttgart, nicht auf Korntal.
Das stützt die Empfehlung aus Abschnitt A, trägt sie aber nicht.

---

## B · Begriffe je Seite

Jeder Begriff mit Herkunft. „Wettbewerbertitel" heißt: so steht es im Titel
mindestens eines Anbieters in der Trefferliste. „Eigener Bestand" heißt: der
Betrieb führt den Begriff selbst schon woanders.

### Startseite · `data/content/home.json`

| | |
|---|---|
| **Hauptbegriff** | Fahrzeugaufbereitung Stuttgart |
| **Nebenbegriffe** | Autoaufbereitung Stuttgart · Smart Repair Stuttgart · Dellendoktor Stuttgart |
| **Absicht** | Orientierung — „wer macht sowas hier in der Nähe" |
| **Heute** | H1 „Lack, Leder, Dellen.", Kicker „Fahrzeugpflege & KFZ-Aufbereitung" |
| **Lücke** | Kein Ortsbezug in H1 oder Kicker. „Smart Repair" und „Dellendoktor" fehlen komplett |

> **„Autoaufbereitung" schlägt „Fahrzeugpflege" im Markt.** In den
> Wettbewerbertiteln dominiert „Autoaufbereitung" bzw. „Autopflege". Der
> Firmenname bleibt selbstverständlich „Fahrzeugpflege Reutter" — aber im
> Fließtext und in Zwischenüberschriften sollte „Autoaufbereitung" mindestens
> gleichberechtigt vorkommen.

### Dellen & Hagelschaden · `data/content/leistung-dellen-hagelschaden.json`

| | |
|---|---|
| **Hauptbegriff** | Hagelschaden Reparatur Stuttgart |
| **Nebenbegriffe** | Dellen entfernen ohne Lackieren · Parkdelle · Beulendoktor / Dellendoktor · Ausbeulen ohne Lackieren · PDR |
| **Absicht** | Akut und kaufbereit — nach Hagel oder Parkschaden, oft mit Versicherungsfrage im Kopf |
| **Heute** | H1 „Die Delle geht raus. Der Lack bleibt drauf.", Kicker „Leistung 03 · Karosserie" |
| **Lücke** | **Die stärkste Seite des Betriebs führt seine eigenen Marktbegriffe nicht.** Weder „Dellendoktor" noch „Beulendoktor", „Parkdelle", „Ausbeultechnik" oder „PDR" |

Herkunft: „Dellen entfernen ohne Lackieren", „lackschadenfreie Ausbeultechnik",
„Ausbeulen ohne Lackieren", „Beulendoktor", „Dellendoktor", „Parkdelle",
„Türdelle", „Hagelschaden Teilkasko" — alle aus Wettbewerbertiteln.
„Dellendoktor" zusätzlich aus dem **eigenen Bestand**: der Verzeichniseintrag
lautet wörtlich „Fahrzeugpflege / Dellendoktor / Smartrepair Reutter".

### Lackaufbereitung · `data/content/leistung-fahrzeugpflege-exterieur.json`

| | |
|---|---|
| **Hauptbegriff** | Lackaufbereitung Stuttgart |
| **Nebenbegriffe** | Autopolitur · Keramikversiegelung · Kratzer entfernen · Lackkorrektur |
| **Absicht** | Gemischt — teils Zustand („Kratzer weg"), teils Werterhalt |
| **Heute** | H1 „Klarlack ist endlich.", Beschreibung nennt „Versiegelung oder Keramik" |
| **Lücke** | **„Keramikversiegelung" steht nur in der Beschreibung, in keiner Überschrift** — dabei ist es ein eigener, stark umkämpfter Suchbegriff mit eigener Exact-Match-Domain im Wettbewerb (`keramikversiegelung-stuttgart.de`) |

Weitere Marktbegriffe: „Swirls", „Hologramme", „ein-/mehrstufige Politur". Die
Seite sagt „zweistufig poliert" — das passt, ist aber nicht als Begriff gesetzt.

### Innenraum · `data/content/leistung-fahrzeugpflege-interieur.json`

| | |
|---|---|
| **Hauptbegriff** | Innenraumreinigung Auto Stuttgart |
| **Nebenbegriffe** | Innenaufbereitung · Polsterreinigung · Sprühextraktion · Hundehaare entfernen |
| **Absicht** | Zustand und Anlass (Verkauf, Rückgabe, Tierhaare) |
| **Heute** | H1 „Absaugen ist der Anfang, nicht die Arbeit.", Titel „Innenraumaufbereitung" |
| **Lücke** | Der Markt sucht „Innenraum**reinigung**", die Seite heißt „Innenraum**aufbereitung**". Beides gehört rein. „Hundehaare entfernen" führt ein Nachbarwettbewerber im Titel — ein konkreter Anlass, den wir gar nicht nennen |

### Lackierarbeiten · `data/content/leistung-lackierarbeiten.json`

| | |
|---|---|
| **Hauptbegriff** | Beilackierung Stuttgart |
| **Nebenbegriffe** | Teillackierung · Spot Repair · Lackreparatur · Kratzer lackieren |
| **Absicht** | Konkreter Schaden, Preisvergleich gegen die Fachwerkstatt |
| **Heute** | H1 „Der Farbcode ist ein Vorschlag. Nicht das Ergebnis." |
| **Lücke** | „Spot Repair" fehlt — ein etablierter Begriff, den ein Wettbewerber im Titel führt und der genau diese Leistung beschreibt |

⚠️ **Diese Seite hängt an Frage B4.** Titel und Beschreibung versprechen heute
„gemessener Farbton und Musterblech". Sagt Daniel dazu Nein, muss beides neu —
nicht nur der Fließtext. Und der Abschnitt zur Beklebung steht im **Widerspruch**
zur Hub-Seite, die Folierung unter „Was wir nicht machen" führt (Frage B7,
bereits als `_widerspruch` im Inhalt markiert).

### Lederreparatur · `data/content/leistung-lederreparatur.json`

| | |
|---|---|
| **Hauptbegriff** | Lederreparatur Auto Stuttgart |
| **Nebenbegriffe** | Autositz reparieren · Sitzwange · Lenkrad neu beziehen · Lederfärbung |
| **Absicht** | Konkreter Schaden, oft mit Foto in der Hand |
| **Heute** | H1 „Auffällig ist nicht die Farbe. Auffällig ist die fehlende Narbung." |
| **Lücke** | Vergleichsweise klein — „Sitzwange" und „Lenkrad" stehen schon im Titel. „Autositz reparieren" als Formulierung fehlt |

Wettbewerbsumfeld hier anders: Konkurrenz sind **Autosattlereien**, nicht
Aufbereiter. Wer „Lenkrad neu beziehen" sucht, landet bei einer Sattlerei — das
ist eine bewusste Abgrenzung wert.

### Ozonbehandlung · `data/content/leistung-ozonbehandlung.json`

| | |
|---|---|
| **Hauptbegriff** | Ozonbehandlung Auto Stuttgart |
| **Nebenbegriffe** | Geruch entfernen Auto · Rauchgeruch · Nikotingeruch · Schimmelgeruch · Geruchsneutralisation |
| **Absicht** | Fast reine Problemsuche — „Geruch geht nicht weg" |
| **Heute** | Titel und Beschreibung decken Rauch, Tier und Schimmel bereits ab |
| **Lücke** | „Nikotingeruch" und „Geruchsneutralisation" fehlen als Formulierung. Die eigene Altseite führt „Nikotin" bereits im Titel |

⚠️ **Und ein Fund zu Frage B2.** Der Markt beantwortet „wie lange dauert das"
so: Behandlung je nach Intensität **4 bis 24 Stunden**, danach etwa eine Stunde,
bis das Ozon zerfallen ist, dann lüften. Die 60 Minuten, die auf unserer
Startseite stehen, entsprechen in mehreren Quellen der **Laufzeit des Geräts** —
nicht der Zeit, bis das Fahrzeug wieder benutzbar ist. Das stützt die Angabe
„½ Tag" auf der Ozonseite und spricht gegen die „60 Minuten" auf der
Startseite. **Trotzdem muss Daniel sagen, wie er es hält** — hier steht nur,
was im Markt üblich ist, nicht was er tut.

### Leistungsübersicht · `data/content/leistungen-hub.json`

| | |
|---|---|
| **Hauptbegriff** | Smart Repair Stuttgart |
| **Nebenbegriffe** | Fahrzeugaufbereitung · Spot Repair · Kfz-Aufbereitung |
| **Absicht** | Vergleich — „was gibt es, was brauche ich" |
| **Lücke** | **Der beste Platz für „Smart Repair", und der Begriff fehlt auf der ganzen Website** |

> **Zu „Smart Repair".** Der Begriff steht für „Small to Medium Area Repair
> Techniques" und fasst genau zusammen, was der Betrieb macht: Dellen, Kratzer,
> Lack, Kunststoff, Innenraum, ohne Teiletausch. Er ist im Markt fest etabliert
> — mehrere Anbieter führen ihn im Titel, es gibt Portale und Verzeichnisse
> dafür. **Und das Google-Profil des Kunden heißt wörtlich „Smartrepair
> Reutter".** Ihn wegzulassen ist die auffälligste einzelne Lücke der Seite.

---

## C · Fragen für Antwortmaschinen

Antwortmaschinen — Googles Fragenkästen, ChatGPT-Suche, Perplexity — zitieren
Seiten, die eine Frage **kurz, direkt und als Frage erkennbar** beantworten. Die
FAQ-Auszeichnung steht bereits (`app/lib/seo.php`, `seo_jsonld_faq()`); sie ist
nur inhaltlich nicht auf echte Suchfragen ausgerichtet.

Diese Fragen tauchen in den Ergebnissen wiederholt auf:

**Dellen und Hagel**
- Was kostet es, eine Delle entfernen zu lassen?
- Zahlt die Teilkasko den Hagelschaden — und was ist mit der Selbstbeteiligung?
- Was ist fiktive Abrechnung, und lohnt sie sich?
- Geht das ohne Lackieren, und hält das?
- Wie lange dauert eine Hagelschadenreparatur?

**Ozon**
- Wie lange dauert eine Ozonbehandlung?
- Wie lange darf man danach nicht einsteigen?
- Ist Ozon gesundheitsschädlich?
- Geht Rauchgeruch damit wirklich weg — auch aus dem Himmel?

**Lack und Politur**
- Was kostet eine Lackaufbereitung?
- Was ist der Unterschied zwischen Politur und Keramikversiegelung?
- Wie oft darf man polieren?

**Leder**
- Was kostet eine Lederreparatur am Autositz?
- Sieht man die reparierte Stelle hinterher?
- Kann man eine durchgescheuerte Sitzwange retten?

**Innenraum**
- Was kostet eine Innenraumreinigung?
- Wie lange muss das Auto danach trocknen?
- Bekommt man Tierhaare wirklich raus?

Zur Umsetzung: **die Frage wörtlich als Überschrift, die Antwort im ersten
Satz darunter, dann erst die Begründung.** Die Seiten machen es heute
umgekehrt — erst die Herleitung, dann das Ergebnis. Das liest sich gut und wird
selten zitiert.

⚠️ **Fast jede dieser Fragen beginnt mit „Was kostet".** Der Wettbewerb
beantwortet das mit Zahlen: „ab 119,90 €", „ab 229,–", „Parkdellen ab 49 €";
der Markt nennt für kleine Dellen etwa 60 €, für mittlere etwa 80 €. Unsere
Seiten sagen durchgängig „Festpreis nach Begutachtung" — richtig, aber als
Antwort unbrauchbar. **Ohne mindestens eine Ab-Zahl je Leistung sind wir bei der
häufigsten Frage überhaupt nicht zitierfähig.** Das braucht Daniels Zahlen und
gehört in den Fragebogen.

---

## D · Zwei strukturelle Befunde, die nichts kosten

### D1 — Keine einzige H1 trägt einen Suchbegriff

„Die Delle geht raus. Der Lack bleibt drauf." · „Klarlack ist endlich." · „Der
Farbcode ist ein Vorschlag." — das ist gute Werbetexterei und soll bleiben. Aber
die H1 ist nach dem Titel das stärkste Signal auf der Seite, und keine einzige
enthält Leistung oder Ort.

**Vorschlag ohne Eingriff in die Texte: der Kicker.** Er steht direkt über der
H1, ist bereits im Inhalt pflegbar — und verschenkt heute seinen Platz mit
reiner Nummerierung:

| heute | Vorschlag |
|---|---|
| `Leistung 01 · Exterieur` | `Lackaufbereitung & Politur · Raum Stuttgart` |
| `Leistung 02 · Interieur` | `Innenraumreinigung · Raum Stuttgart` |
| `Leistung 03 · Karosserie` | `Hagelschaden & Dellen · Raum Stuttgart` |
| `Leistung 04 · Lack` | `Beilackierung & Spot Repair · Raum Stuttgart` |
| `Leistung 05 · Interieur` | `Lederreparatur · Raum Stuttgart` |
| `Leistung 06 · Interieur` | `Ozonbehandlung · Raum Stuttgart` |

Kostet keine Zeile Fließtext, ändert keine inhaltliche Aussage, hängt an keiner
von Daniels Antworten. **Von allen Punkten hier der beste Aufwand-Nutzen-Wert.**

### D2 — Vier Titel und eine Beschreibung werden abgeschnitten

Google schneidet Titel bei etwa 60 Zeichen, Beschreibungen bei etwa 160.
Betroffen: die vier Leistungstitel mit 71 bis 74 Zeichen (dort fällt
„Fahrzeugpflege Reutter" am Ende weg) und die Startseiten-Beschreibung mit 191
Zeichen — ein Drittel wird nie angezeigt.

Die Neufassungen unten sind nachgerechnet.

---

## E · Titel und Beschreibungen, neu

Schlüssel jeweils `seo.titel` und `seo.beschreibung` in der genannten Datei.

### Sofort umsetzbar

| Datei | Vorschlag |
|---|---|
| `home.json` | **T:** Fahrzeugpflege Reutter — Dellendoktor Stuttgart<br>**D:** Smart Repair im Raum Stuttgart: Hagelschaden, Parkdellen, Lackaufbereitung, Lederreparatur, Ozonbehandlung. Eigene Halle in Korntal, auch mobil. |
| `leistungen-hub.json` | **T:** Leistungen — Smart Repair & Aufbereitung Stuttgart<br>**D:** Sechs Leistungen aus einer Hand: Lackaufbereitung, Innenraum, Dellen und Hagelschaden, Beilackierung, Lederreparatur, Ozonbehandlung. Korntal-Münchingen. |
| `leistung-dellen-hagelschaden.json` | **T:** Hagelschaden & Dellen Stuttgart — ohne Lackieren<br>**D:** Dellendoktor für Hagelschaden und Parkdellen: gedrückt statt gespachtelt, der Originallack bleibt. Aufstellung für die Versicherung. Korntal, auch mobil. |
| `leistung-fahrzeugpflege-interieur.json` | **T:** Innenraumreinigung Auto Stuttgart — Sprühextraktion<br>**D:** Polster nass extrahiert statt nur abgesaugt, Schächte von Hand, Kunststoff matt aufgefrischt. Innenraumaufbereitung in Korntal-Münchingen, auch mobil. |
| `leistung-lederreparatur.json` | **T:** Lederreparatur Auto Stuttgart — Sitze & Lenkrad<br>**D:** Farbabrieb, durchgescheuerte Sitzwange, Risse und Brandlöcher. Autositz repariert mit geprägter Narbung statt glatter Stelle. Korntal-Münchingen bei Stuttgart. |
| `leistung-ozonbehandlung.json` | **T:** Ozonbehandlung Auto Stuttgart — Geruch entfernen<br>**D:** Rauch, Nikotin, Tiergeruch, Schimmel: Ozon zerlegt Geruchsmoleküle nach der Reinigung. Ozonbehandlung in Korntal-Münchingen, nur in der Halle. |
| `galerie.json` | **T:** Galerie — Arbeiten aus der Halle | Reutter<br>**D:** unverändert |
| `kontakt.json` · `impressum.json` · `datenschutz.json` · `agb.json` · `widerruf.json` | unverändert — Länge und Begriffe passen |

### Erst nach Daniels Antworten

| Datei | hängt an | warum |
|---|---|---|
| `leistung-lackierarbeiten.json` | **B4** (Farbtonmessung, Musterblech) und **B7** (Folierung) | Titel und Beschreibung führen heute genau die unbestätigte Aussage. Ohne B4 kein tragfähiger Hauptbegriff — „Beilackierung mit gemessenem Farbton" wäre sonst eine Zusage in der Trefferliste |
| `leistung-fahrzeugpflege-exterieur.json` | **Lackdickenmessung** | „nach Messung" steht heute im Titel. Gibt es kein Schichtdickenmessgerät, fällt der Differenzierer weg und der Titel braucht einen anderen |

---

## F · Was in den Fragebogen für Daniel gehört

Vier neue Punkte aus dieser Recherche. Gehören zu `docs/fragen-an-daniel.md`.

**F1 — Die beiden abgeschalteten Adressen.** `stuttgart-hagelschaden.de` und
`stuttgart-dellendoktor.de` laufen nicht mehr. Bewusst aufgegeben oder
ausgelaufen? Nur falls Letzteres und es zeigten Verweise darauf, lohnt eine
Rückholung samt Weiterleitung — sonst nicht. Kleine Frage, kein Blocker.

**F2 — Ab-Preise je Leistung.** Eine belastbare Zahl pro Bereich („Parkdelle ab
…", „Innenraum ab …"). Der Wettbewerb nennt sie, die häufigste Nutzerfrage ist
„was kostet das", und ohne Zahl beantworten wir sie nicht.

**F3 — Führt er „Smart Repair" als Begriff?** Sein Google-Profil heißt so. Auf
der neuen Seite kommt er nicht vor. Bevor wir ihn setzen: versteht er darunter
dasselbe, und will er so gefunden werden?

**F4 — Ozon, Nachschärfung zu B2.** Der Markt sagt: Behandlung 4 bis 24 Stunden
je nach Intensität, danach rund eine Stunde bis zum Zerfall, dann lüften. Die
Frage an ihn wird damit konkreter: *Wie lange läuft das Gerät, und wie lange
steht das Fahrzeug insgesamt, bis es der Kunde wieder bekommt?*

---

## Was das hier nicht ist

Keine Umsetzung — kein Text, kein Titel, keine Auszeichnung wurde geändert.

Kein Ersatz für einen Audit an der Live-Domain. Der kommt, wenn die Domain
entschieden ist und die Seiten indexierbar sind. Sinnvoll wird er erst dann:
heute trägt jede Seite `noindex`, es gibt keine Positionen zu messen.

Und keine Rangvorhersage. Was hier steht, ist beobachtetes Marktverhalten —
welche Begriffe der Wettbewerb benutzt und welche Fragen gestellt werden. Ob
eine Seite damit vorne landet, hängt an Dingen, die diese Recherche nicht
abdeckt: Verweise, Profilpflege, Alter der Domain, Wettbewerbsdruck.
