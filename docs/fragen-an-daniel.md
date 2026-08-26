# Gesprächsleitfaden Daniel Reutter

Stand: 17.08.2026 · für das Gespräch am 18.08.

Alles, was die Website noch braucht und nur er beantworten kann. Sortiert
danach, was am längsten dauert — nicht danach, was am wichtigsten ist.

Antworten kannst du mir formlos durchgeben, ich trage sie ein. Die meisten
Werte lassen sich im Panel pflegen; wo nicht, ändere ich es direkt im Code.

---

## Antworten vom 25.08.2026 — und was daraus folgt

| # | Antwort | Umgesetzt |
|---|---|---|
| A1 | Fotos besorgt er, inkl. Vorher/Nachher | ⏳ warten |
| A2 | dito — **Launch-Blocker, bis sie da sind** | ⏳ warten |
| A3 | Kein Vektor, aber saubere JPG | ⏳ JPG ins Repo |
| A4 | **Name wird „Smartrepair Reutter"** — Google-Profil bleibt, die Website zieht nach | ✅ |
| B1 | Seit 2005 (21 Jahre) | ✅ wird gerechnet |
| B2 | Keine Zeitangabe, telefonisch klären | ✅ |
| B3 | Kein Messgerät | ✅ Zusage entfernt |
| B4 | Farbtonmessung **ja**, Musterblech **ja**. Lackieren extern — nicht erwähnen | ✅ |
| B5 | Fingernageltest raus | ✅ |
| B6 | Politur **nicht** inklusive | ✅ Zusage gestrichen |
| B7 | Alle vier Folienvarianten | ✅ |
| C1 | Kein Meisterbetrieb, nicht in der Handwerksrolle | ✅ Abschnitt entfällt |
| C2 | Daniel Reutter | ✅ |
| C3 | Alte Texte übernehmen | ⚠️ siehe unten |
| D | Domain **smartrepair-reutter.de** (vorläufig), neues Postfach mit Weiterleitung | 📄 `docs/umzug.md` |

Zusätzlich: keine Online-Termine (war ohnehin nie so gebaut — alle Knöpfe
heißen „Termin anfragen"), telefonisch am besten erreichbar, E-Mails können
liegen bleiben, Öffnungszeiten in der Regel 9–17 Uhr mit einer Stunde Vorlauf.

### Zu C3 — was aus den alten Texten wirklich brauchbar ist

**Übernommen sind die Entscheidungen, nicht der Text:**

- **Rechtsform geklärt.** Impressum und AGB nennen nur „Daniel Reutter", führen
  eine USt-IdNr. und kein Registergericht → Einzelunternehmen
- **Drei von sechs offenen AGB-Punkten sind damit beantwortet**: Geltungsbereich
  (Verbraucher *und* Unternehmer), Gewährleistung (ein Jahr bei Gebrauchtem und
  gegenüber Unternehmern) und Zurückbehaltungsrecht. Steht jetzt in den
  Platzhalter-Hinweisen von `agb.json`

**Nicht übernommen, mit Grund:**

- Die alten AGB sind ein **Händlerbund-Text von 2018** für einen Online-Shop
  unter `stuttgart-dellendoktor.de`. Sie regeln Warenkorb, Versandkosten,
  Eigentumsvorbehalt und Rücksendung — und enthalten den Satz „Sie tragen die
  Kosten für die Übersendung des defekten Fahrzeuges an uns"
- Die Haftungszusage des Händlerbunds hängt an einer **laufenden
  Mitgliedschaft**. Ohne sie behält man das Abmahnrisiko und verliert den
  Schutz — und der Text selbst ist lizenziert, nicht frei
- Impressum und AGB verweisen auf die **OS-Plattform der EU**. Die wurde am
  **20.07.2025 eingestellt**; ein Verweis darauf ist heute selbst angreifbar.
  Unsere Fassung hat das bereits richtig
- Der Datenschutz ist von vor der DSGVO. Unsere Fassung hat drei markierte
  Lücken, der alte Text hätte zwanzig unmarkierte

### Adresskonflikt — geklärt

Das alte Impressum nennt zwei Anschriften: Werkstatt **Carl-Peters-Straße 27,
Korntal**, Rechnung **Zabergäustraße 27, Stuttgart**. Unsere Seite führt
**Lembergstraße 27, Korntal-Münchingen** — und das ist richtig: Google-Profil,
cylex, 11880, lokalwissen und deutschbranchenbuch nennen alle diese Adresse.
Die Carl-Peters-Straße ist ein alter Stand, der Betrieb ist innerhalb Korntals
umgezogen.

Offen bleibt nur: Ist die **Zabergäustraße** noch die Rechnungsanschrift, und
soll sie im Widerruf als Empfangsadresse stehen? Ins Impressum muss sie nicht —
§ 5 DDG verlangt die Anschrift der Niederlassung, und das ist die Werkstatt.

## A · Was er besorgen muss — als Erstes ansprechen, das dauert

Diese vier Punkte blockieren den Launch und liegen nicht in unserer Hand.

### A1 — Fotos

Alles, was heute auf der Seite steht, ist Stock- bzw. KI-Material. Ein Bild ist
als „Inhaber/Team vor der Halle" beschriftet und zeigt einen Porsche 356 in
einer Palmenallee.

Gebraucht, **mindestens 2400 px an der langen Kante, unbearbeitet**:

- **1 Hero-Bild** — Fahrzeug nach der Aufbereitung, eher dunkel, seitliche
  Lichtkante
- **1 Fahrzeugfoto für den Leistungs-Hotspot** — Seiten- oder
  Dreiviertelansicht, auf der sieben Bereiche markierbar sind
- **1 Foto vom Betrieb** — er selbst oder das Team vor der Halle. Keine
  Stockfotos; das ist der Vertrauensanker der ganzen Seite
- **10–20 Galeriebilder** aus der täglichen Arbeit, gerne mit Notiz, welche
  Leistung darauf zu sehen ist

> Handyfotos sind völlig in Ordnung, solange sie scharf und nicht
> nachbearbeitet sind. Lieber zwanzig ehrliche Handybilder als drei
> aufgehübschte.

### A2 — Drei Vorher/Nachher-Paare

Heute zeigt jeder der drei Fälle **dasselbe Foto auf beiden Seiten**, die
Vorher-Hälfte ist nur per Filter abgedunkelt. Darunter steht wörtlich „Alle
Aufnahmen aus eigener Arbeit, unbearbeitet, gleiches Licht".

Gebraucht: je zwei Aufnahmen desselben Fahrzeugs — **gleicher Winkel, gleiches
Licht, gleicher Abstand**, einmal vorher, einmal nachher. Passend zu
Hagelschaden, Lackaufbereitung, Lederreparatur.

> Warum das drängt: Stockmaterial als eigene Arbeit auszugeben ist irreführende
> Werbung nach § 5 UWG und abmahnfähig. Wenn keine Paare zusammenkommen, muss
> die Sektion samt Begleittext vor dem Launch entschärft werden — das ist die
> Rückfallebene, aber sie kostet das stärkste Argument der Seite.

### A3 — Logo als echte Vektordatei

Was bisher kam, waren zwei Dateien mit demselben 934 × 107 px großen JPEG
darin — die zweite mit `.svg` am Ende, aber ohne einen einzigen Vektorpfad.

**Gebraucht wird die Originaldatei aus dem Grafikprogramm: `.ai`, `.eps`,
`.cdr` oder ein Vektor-PDF.**

> Wer das Logo für Fahrzeuge oder Schilder gesetzt hat, hat sie — ein
> Schneidplotter kann ohne Vektorpfade nicht arbeiten. Meist der
> Werbetechniker oder wer die Fahrzeugbeschriftung gemacht hat.
>
> Selbst prüfen: Datei im Texteditor öffnen. Steht dort `<image` und `base64`,
> ist es wieder eine Attrappe.

Bis dahin läuft die aufbereitete Rasterfassung — sie reicht für Kopf- und
Fußbereich der Website, nicht für Favicon, Social-Vorschau oder Druck.

### A4 — Google-Unternehmensprofil umbenennen

Läuft unter **„Smartrepair Reutter"**, offiziell heißt es **„Fahrzeugpflege
Reutter"**. Solange Website, Google-Profil und Verzeichnisse verschiedene Namen
tragen, kostet das lokale Sichtbarkeit.

Die 281 Rezensionen bleiben erhalten. Google prüft die Änderung unter Umständen
manuell — deshalb früh anstoßen.

Bei der Gelegenheit gleich mit ihm durchgehen:

- Ist die Adresse im Profil aktuell?
- Sind die Öffnungszeiten korrekt (Mo–Fr 9–17 Uhr, Sa + So zu)?
- Ist die **Festnetznummer** ergänzt? Dort steht bisher nur die Mobilnummer.

---

## B · Aussagen, die auf der Seite stehen und nicht bestätigt sind

Das hat der Design-Entwurf erfunden oder angenommen. Alles davon steht heute
online lesbar auf der Vorschau.

### B1 — Stimmen die 25 Jahre?

„25 Jahre im Handwerk" steht an drei Stellen. Entweder die echte Zahl — oder
die Angabe fliegt raus.

**→ Antwort: ______**

### B2 — Ozon: 60 Minuten oder ein halber Tag?

Die Startseite sagt „Fahrzeug danach **60 Minuten** gesperrt", die Ozonseite
„**½ Tag** — Fahrzeug gesperrt". Beides steht online und widerspricht sich.
Beide Zahlen stammen aus dem Entwurf.

**→ Welche gilt: ______**

### B3 — Ozon: Gibt es ein Messgerät?

Auf der Ozonseite steht: „wir geben es erst frei, wenn nichts mehr messbar
ist". Das setzt ein Restozon-Messgerät voraus.

Wenn keins da ist, muss der Satz weg — sonst ist es eine Zusage, die nicht
eingehalten werden kann.

**→ Messgerät vorhanden: ja / nein**

### B4 — Lackierarbeiten: Farbton messen und Musterblech?

**Die wichtigste Frage des Gesprächs.** Die ganze Lackierseite steht auf dieser
Aussage: dass der Farbton **am Fahrzeug gemessen** wird (nicht nur der Farbcode
abgelesen) und dass ein **Musterblech gespritzt** wird, bevor lackiert wird.

Stimmt das nicht, muss die Seite in ihrem Kern umgeschrieben werden.

**→ Farbtonmessung am Fahrzeug: ja / nein**
**→ Musterblech: ja / nein**

### B5 — Lackierarbeiten: Standzeiten des Fingernageltests

Drei Stufen mit je einer Angabe, wie lange das Fahrzeug bleibt:

| Stufe | derzeit auf der Seite | stimmt? |
|---|---|---|
| Kratzer bleibt am Fingernagel nicht hängen | ein halber bis ein Tag | |
| Fingernagel hakt leicht ein | ein bis zwei Tage | |
| Fingernagel hakt deutlich ein | zwei bis drei Tage | |

### B6 — Lackierarbeiten: „Politur des Übergangs inklusive"

Steht als Preiszusage auf der Seite. Gilt das so?

**→ ja / nein**

### B7 — Fahrzeugbeklebung: Was davon bietet er an?

Der Abschnitt auf der Lackierseite beschreibt vier Varianten. Das ist eine
Annahme aus dem alten Seitentext, keine Auskunft:

- [ ] Teilfolierung
- [ ] Vollfolierung
- [ ] Schriftzüge und Beschriftung
- [ ] Steinschlagschutzfolie

Was er nicht anbietet, wird gestrichen.

---

## C · Rechtliches

### C1 — Eintrag in der Handwerksrolle

Fahrzeuglackierer ist zulassungspflichtiges Handwerk nach Anlage A der
Handwerksordnung, reine Fahrzeugpflege und Aufbereitung dagegen nicht.

**→ Eingetragen: ja / nein**

- **Ja** → Kammer, Berufsbezeichnung und verleihender Staat müssen ins
  Impressum (§ 5 Abs. 1 Nr. 5 DDG). Bitte genauen Wortlaut erfragen.
- **Nein** → der Begriff **„Meisterbetrieb" darf nirgends** auf der Seite
  stehen. Wir vermeiden ihn bis zur Klärung ohnehin durchgängig.

### C2 — Name des Inhabers

Im Impressum steht derzeit „Daniel Reutter" als Annahme.

**→ Vollständiger Name wie im Handelsregister/Gewerbeschein: ______**

### C3 — Rechtstexte

Impressum, Datenschutz, AGB und Widerruf über **eRecht24 oder IT-Recht
Kanzlei** ziehen, am besten mit Aktualisierungsservice. Die vier Seiten stehen
fertig gebaut; alles Belegte ist eingetragen, der Rest ist sichtbar markiert.

Solange Markierungen offen sind, trägt jede der vier Seiten oben ein rotes
Hinweisband.

Konkret offen sind:

**Impressum (3)** — Inhabername (C2), Handwerksrolle (C1), Bildnachweise
(ergibt sich aus A1)

**AGB (6)** — Verbraucher oder Unternehmer als Hauptzielgruppe ·
**Zahlungsarten und Fälligkeit** · Stellplatz nach Fertigstellung ·
Fristen und Ablauf · Haftungsbeschränkung · anwaltliche Prüfung

**Widerruf (4)** — amtliches Muster (wörtlich, darf nicht umformuliert
werden) · Formulierung für die Auftragsbestätigung · Formular nach Anlage 2
EGBGB

**Datenschutz (3)** — Auftragsverarbeitungsvertrag mit IONOS ·
Speicherdauer der Logfiles · Aufbewahrungsfrist für Anfragen ohne Auftrag

> Von diesen ist **„Zahlungsarten und Fälligkeit"** die einzige, die er aus dem
> Kopf beantworten kann: Barzahlung, EC, Überweisung? Fällig bei Abholung oder
> auf Rechnung? Anzahlung bei größeren Aufträgen? Die kannst du gleich
> mitnehmen.

---

## D · Technisches — nicht Daniel, aber im selben Aufwasch

| Was | Wofür |
|---|---|
| PHP im IONOS-Vertrag auf **8.4** stellen | Voraussetzung für den Betrieb |
| SFTP-/SSH-Zugang, auf `/neu` beschränkt | Deployment |
| Postfach **`website@clean-box.eu`** anlegen | Formularversand, damit `info@` unangetastet bleibt |
| **Domain entscheiden** | `clean-box.eu` behalten oder `fahrzeugpflege-reutter.de` registrieren |
| PHP Extended Support kündigen | läuft als kostenpflichtiges Add-on für das alte PHP 7.4 |

> Die Domainentscheidung hat eine Nebenwirkung: Solange die Seite unter einer
> `vercel.app`-Adresse läuft, trägt sie `noindex` und ist für Google nicht
> existent. Das fällt automatisch weg, sobald die echte Domain darauf zeigt —
> aber ohne Entscheidung passiert eben nichts.

---

## E · Nachtrag aus der Suchbegriffs-Recherche (18.08.)

Vier Punkte, die beim Blick auf den Markt aufgetaucht sind. Herleitung steht in
`docs/suchbegriffe.md`.

### E1 — Zwei frühere Adressen sind abgeschaltet

Neben `clean-box.eu` gab es zwei weitere Seiten des Betriebs:
`stuttgart-hagelschaden.de` und `stuttgart-dellendoktor.de`. Beide sind heute
nicht mehr erreichbar; im Suchindex stehen sie noch.

**→ Bewusst aufgegeben, oder ausgelaufen?**

> Nur wenn sie ausgelaufen sind **und** Verweise darauf zeigten, lohnt es,
> eine davon zurückzuholen und weiterzuleiten. Ansonsten ist der Punkt
> erledigt. An der Domainfrage in Block D ändert er nichts — die bleibt
> `clean-box.eu` behalten oder wechseln.

### E2 — Ab-Preise je Leistung

Die mit Abstand häufigste Frage im Netz ist „was kostet das". Der Wettbewerb
beantwortet sie mit Zahlen — „ab 119,90 €", „Parkdellen ab 49 €". Unsere Seiten
sagen überall „Festpreis nach Begutachtung". Das stimmt, beantwortet die Frage
aber nicht.

**→ Eine belastbare Ab-Zahl je Bereich:**

| | ab … € |
|---|---|
| Parkdelle, einzeln | |
| Hagelschaden | |
| Lackaufbereitung / Politur | |
| Innenraumaufbereitung | |
| Lederreparatur | |
| Ozonbehandlung | |

### E3 — „Smart Repair" und „Dellendoktor"

Sein Google-Profil heißt „Smartrepair Reutter", der Verzeichniseintrag
„Fahrzeugpflege / Dellendoktor / Smartrepair Reutter". **Auf der neuen Website
kommt keiner der beiden Begriffe vor** — obwohl beide im Markt fest etabliert
sind und die Altseiten sie führen.

**→ Versteht er unter „Smart Repair" dasselbe wie der Markt (Dellen, Kratzer,
Lack, Kunststoff, Innenraum ohne Teiletausch), und will er so gefunden werden?**

### E4 — Ozon: die Frage aus B2, geschärft

Im Markt gilt: Behandlung je nach Intensität vier bis vierundzwanzig Stunden,
danach etwa eine Stunde, bis das Ozon zerfallen ist, dann lüften. Die „60
Minuten" von unserer Startseite entsprechen in mehreren Quellen der **Laufzeit
des Geräts** — nicht der Zeit bis zur Rückgabe.

**→ Wie lange läuft bei ihm das Gerät — und wie lange steht das Fahrzeug
insgesamt, bis der Kunde es wiederbekommt?**

---

## Kurzfassung für unterwegs

Wenn nur fünf Minuten bleiben, sind das die fünf Fragen:

1. **Fotos** — wann kommen sie, und schafft er drei Vorher/Nachher-Paare?
2. **Farbton am Fahrzeug messen und Musterblech** — ja oder nein?
3. **25 Jahre** — stimmt die Zahl?
4. **Handwerksrolle** — eingetragen oder nicht?
5. **Logo** — wer hat die Fahrzeugbeschriftung gemacht?
