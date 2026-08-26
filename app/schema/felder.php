<?php
declare(strict_types=1);

/**
 * Das Herz des CMS: Hier steht, welche Felder der Kunde im Panel sieht.
 *
 * Was nicht in dieser Datei steht, kann er nicht aendern — Layout, Reihenfolge
 * der Sektionen und CSS bleiben unangetastet. Genau das schuetzt das
 * abgenommene Design.
 *
 * Feldtypen: text, mehrzeilig, absaetze, zahl, bild, auswahl, liste
 * 'pfad' ist die Punktnotation in die jeweilige JSON-Datei.
 * 'gruppe' bestimmt, unter welcher Ueberschrift der Eintrag im Panel steht.
 */

/**
 * Baut das Schema einer Leistungsseite. Fuenf Bloecke haben alle sechs Seiten
 * gemeinsam — Suchmaschine, Kopfbereich, Preis, Fragen, Schaltflaeche am Ende.
 * Was sie unterscheidet, ist ihr Kernmodul, und das kommt als $eigene dazu.
 *
 * Nicht jede Seite hat jeden Block: die Dellenseite zeigt statt eines
 * Vergleichsreglers drei Fotostationen und statt eines Preisabsatzes drei
 * Preiskarten. Ueber $optionen lassen sich die beiden gemeinsamen Bloecke
 * deshalb einzeln abschalten.
 *
 * @param list<array<string,mixed>>   $eigene   Zusaetzliche Gruppen dieser Seite
 * @param array{preis?:bool,vergleich?:bool,hero_bild?:bool,faq_kicker?:bool} $optionen
 * @return array<string,mixed>
 */
function leistungs_schema(string $titel, string $beschreibung, array $eigene, array $optionen = []): array
{
    $kopf = [
        [
            'titel'   => 'Suchmaschine',
            'hinweis' => 'Der Titel erscheint als blaue Zeile im Google-Ergebnis, die Beschreibung als grauer Text darunter.',
            'felder'  => [
                ['pfad' => 'seo.titel',        'typ' => 'text',       'label' => 'Titel bei Google', 'hilfe' => 'Höchstens 60 Zeichen, sonst schneidet Google ab.'],
                ['pfad' => 'seo.beschreibung', 'typ' => 'mehrzeilig', 'label' => 'Beschreibung bei Google', 'hilfe' => 'Höchstens 155 Zeichen.'],
            ],
        ],
        [
            'titel'  => 'Kopfbereich',
            'felder' => [
                ['pfad' => 'hero.kicker',   'typ' => 'text',       'label' => 'Zeile über der Überschrift'],
                ['pfad' => 'hero.titel',    'typ' => 'text',       'label' => 'Überschrift'],
                ['pfad' => 'hero.lead',     'typ' => 'mehrzeilig', 'label' => 'Einleitungstext'],
                ['pfad' => 'hero.cta',      'typ' => 'text',       'label' => 'Beschriftung der roten Schaltfläche'],
            ],
        ],
    ];

    // Lackierarbeiten und Ozon haben im Kopfbereich kein Foto: die eine zeigt
    // dort die Lackmuster, die andere nur Text. Ein Bildfeld ohne Bild dahinter
    // waere im Panel eine Falle.
    if ($optionen['hero_bild'] ?? true) {
        $kopf[1]['felder'][] = ['pfad' => 'hero.bild',     'typ' => 'bild', 'label' => 'Bild im Kopfbereich', 'hilfe' => 'Querformat, mindestens 2400 Pixel breit.'];
        $kopf[1]['felder'][] = ['pfad' => 'hero.bild_alt', 'typ' => 'text', 'label' => 'Bildbeschreibung'];
    }

    $preisBlock = [
        [
            'titel'   => 'Preis',
            'hinweis' => 'Auf keiner Seite steht ein Euro-Betrag. Erklärt wird das Vorgehen, nicht der Preis.',
            'felder'  => [
                ['pfad' => 'preis.kicker', 'typ' => 'text',       'label' => 'Zeile über der Überschrift'],
                ['pfad' => 'preis.titel',  'typ' => 'text',       'label' => 'Überschrift'],
                ['pfad' => 'preis.text',   'typ' => 'mehrzeilig', 'label' => 'Text'],
                ['pfad' => 'preis.cta',    'typ' => 'text',       'label' => 'Beschriftung der Schaltfläche'],
            ],
        ],
    ];

    $fragenBlock = [
        [
            'titel'   => 'Häufige Fragen',
            'hinweis' => 'Antworten am Anfang kurz und direkt halten — das ist die Form, die Google als Antwortbox übernimmt.',
            'felder'  => array_merge(
              // Die Lederseite zeigt ihre Fragen offen in zwei Spalten, ohne
              // die rote Zeile darueber.
              ($optionen['faq_kicker'] ?? true)
                ? [['pfad' => 'faq.kicker', 'typ' => 'text', 'label' => 'Zeile über der Überschrift']]
                : [],
              [
                ['pfad' => 'faq.titel',  'typ' => 'text', 'label' => 'Überschrift'],
                ['pfad' => 'faq.fragen', 'typ' => 'liste', 'label' => 'Frage', 'min' => 1, 'max' => 10,
                 'subfelder' => [
                    ['pfad' => 'frage',   'typ' => 'text',       'label' => 'Frage'],
                    ['pfad' => 'antwort', 'typ' => 'mehrzeilig', 'label' => 'Antwort'],
                 ]],
              ]
            ),
        ],
    ];

    $vergleichBlock = [
        [
            'titel'  => 'Vorher / Nachher',
            'hinweis' => 'Beide Bilder müssen dasselbe Fahrzeug aus demselben Winkel im selben Licht zeigen.',
            'felder' => [
                ['pfad' => 'vergleich.titel',       'typ' => 'text', 'label' => 'Überschrift'],
                ['pfad' => 'vergleich.beschreibung','typ' => 'mehrzeilig', 'label' => 'Text daneben'],
                ['pfad' => 'vergleich.vorher',      'typ' => 'bild', 'label' => 'Bild vorher'],
                ['pfad' => 'vergleich.vorher_alt',  'typ' => 'text', 'label' => 'Bildbeschreibung vorher'],
                ['pfad' => 'vergleich.nachher',     'typ' => 'bild', 'label' => 'Bild nachher'],
                ['pfad' => 'vergleich.nachher_alt', 'typ' => 'text', 'label' => 'Bildbeschreibung nachher'],
            ],
        ],
    ];

    $schlussBlock = [
        [
            'titel'  => 'Ganz unten',
            'felder' => [
                ['pfad' => 'cta_ueberschrift', 'typ' => 'text', 'label' => 'Überschrift über dem Kurzformular'],
            ],
        ],
    ];

    $gruppen = $kopf;
    $gruppen = array_merge($gruppen, $eigene);
    if ($optionen['preis']     ?? true) { $gruppen = array_merge($gruppen, $preisBlock); }
    $gruppen = array_merge($gruppen, $fragenBlock);
    if ($optionen['vergleich'] ?? true) { $gruppen = array_merge($gruppen, $vergleichBlock); }
    $gruppen = array_merge($gruppen, $schlussBlock);

    return [
        'gruppe'       => 'Leistungen',
        'titel'        => $titel,
        'beschreibung' => $beschreibung,
        'gruppen'      => $gruppen,
    ];
}

/**
 * Baut das Schema einer Rechtsseite. Alle vier sind gleich aufgebaut:
 * Kopf, Stand, Hinweisband und eine Liste nummerierter Abschnitte.
 *
 * @return array<string,mixed>
 */
function rechtstext_schema(string $titel, string $beschreibung): array
{
    return [
        'gruppe'       => 'Rechtliches',
        'titel'        => $titel,
        'beschreibung' => $beschreibung,
        'gruppen' => [
            [
                'titel'   => 'Kopf der Seite',
                'hinweis' => 'Solange noch markierte Stellen offen sind, steht oben ein rotes Hinweisband. Es verschwindet, sobald wir die Seite freigeben.',
                'felder'  => [
                    ['pfad' => 'titel', 'typ' => 'text',       'label' => 'Überschrift'],
                    ['pfad' => 'lead',  'typ' => 'mehrzeilig', 'label' => 'Einleitung'],
                    ['pfad' => 'stand', 'typ' => 'text',       'label' => 'Stand', 'hilfe' => 'z. B. Januar 2026'],
                ],
            ],
            [
                'titel'   => 'Abschnitte',
                'hinweis' => 'Hier kommt der Text aus dem Generator hinein. Eine Leerzeile im Textfeld macht einen neuen Absatz. Bitte keinen Abschnitt löschen, ohne den Text vorher gesichert zu haben.',
                'felder'  => [
                    ['pfad' => 'abschnitte', 'typ' => 'liste', 'label' => 'Abschnitt', 'min' => 1, 'max' => 25,
                     'subfelder' => [
                        ['pfad' => 'titel',    'typ' => 'text',     'label' => 'Überschrift'],
                        ['pfad' => 'absaetze', 'typ' => 'absaetze', 'label' => 'Text'],
                     ]],
                ],
            ],
        ],
    ];
}

return [

    /* ---------------------------------------------------------------- */
    'site' => [
        'gruppe'       => 'Stammdaten',
        'titel'        => 'Stammdaten',
        'beschreibung' => 'Adresse, Telefon und Öffnungszeiten. Diese Angaben erscheinen auf jeder Seite — oben in der Leiste, im Fußbereich und im Kontaktbereich.',
        'gruppen' => [
            [
                'titel'  => 'Betrieb',
                'felder' => [
                    ['pfad' => 'firma.name',    'typ' => 'text', 'label' => 'Firmenname', 'hilfe' => 'Erscheint im Fußbereich und im Impressum.'],
                    ['pfad' => 'firma.inhaber', 'typ' => 'text', 'label' => 'Inhaber'],
                    ['pfad' => 'firma.logo_zusatz', 'typ' => 'text', 'label' => 'Zusatz neben dem Logo',
                     'hilfe' => 'Steht klein rechts neben der Wortmarke, z. B. Smart Repair. Zwei Wörter werden untereinander gesetzt.'],
                    ['pfad' => 'firma.strasse', 'typ' => 'text', 'label' => 'Straße und Hausnummer'],
                    ['pfad' => 'firma.plz',     'typ' => 'text', 'label' => 'Postleitzahl'],
                    ['pfad' => 'firma.ort',     'typ' => 'text', 'label' => 'Ort'],
                    ['pfad' => 'firma.ustid',   'typ' => 'text', 'label' => 'USt-IdNr.', 'hilfe' => 'Pflichtangabe im Impressum.'],
                    ['pfad' => 'firma.postanschrift.strasse', 'typ' => 'text', 'label' => 'Postanschrift — Straße',
                     'hilfe' => 'Nur falls Post woanders hingeht als in die Werkstatt. Steht in Widerruf und AGB, nicht im Impressum.'],
                    ['pfad' => 'firma.postanschrift.plz',     'typ' => 'text', 'label' => 'Postanschrift — PLZ'],
                    ['pfad' => 'firma.postanschrift.ort',     'typ' => 'text', 'label' => 'Postanschrift — Ort'],
                ],
            ],
            [
                'titel'  => 'Erreichbarkeit',
                'felder' => [
                    ['pfad' => 'kontakt.telefon',      'typ' => 'text', 'label' => 'Telefon', 'hilfe' => 'So wie es auf der Seite stehen soll, z. B. 07 11 / 82 85 940'],
                    ['pfad' => 'kontakt.telefon_link', 'typ' => 'text', 'label' => 'Telefon zum Anklicken', 'hilfe' => 'Ohne Leerzeichen, mit Ländervorwahl: +4971182859400'],
                    ['pfad' => 'kontakt.mobil',        'typ' => 'text', 'label' => 'Mobil'],
                    ['pfad' => 'kontakt.mobil_link',   'typ' => 'text', 'label' => 'Mobil zum Anklicken'],
                    ['pfad' => 'kontakt.email',        'typ' => 'text', 'label' => 'E-Mail'],
                    ['pfad' => 'oeffnungszeiten.text', 'typ' => 'text', 'label' => 'Öffnungszeiten', 'hilfe' => 'Kurzform für die Leiste oben, z. B. Mo–Fr 9–17 Uhr'],
                    ['pfad' => 'oeffnungszeiten.hinweis', 'typ' => 'text', 'label' => 'Zusatz zu den Zeiten',
                     'hilfe' => 'Steht klein unter den Zeiten, z. B. der Hinweis, vorher anzurufen.'],
                ],
            ],
            [
                'titel'  => 'Kennzahlen',
                'hinweis' => 'Diese Zahlen stehen groß auf der Startseite. Bitte nur eintragen, was sich belegen lässt — erfundene Bewertungen sind abmahnfähig.',
                'felder' => [
                    ['pfad' => 'kennzahlen.gruendungsjahr.wert', 'typ' => 'zahl', 'label' => 'Gegründet im Jahr',
                     'hilfe' => 'Vierstellig, z. B. 2005. Die Jahre im Handwerk rechnet die Seite daraus — die Zahl altert nicht.'],
                    ['pfad' => 'kennzahlen.google_bewertung.wert', 'typ' => 'text', 'label' => 'Google-Bewertung', 'hilfe' => 'Mit Komma, z. B. 5,0'],
                    ['pfad' => 'kennzahlen.google_anzahl.wert',    'typ' => 'zahl', 'label' => 'Anzahl Google-Rezensionen'],
                    ['pfad' => 'kennzahlen.google_profil_url',     'typ' => 'text', 'label' => 'Link zum Google-Profil',
                     'hilfe' => 'Die Bewertung im Fußbereich wird damit anklickbar — so kann jeder sie nachprüfen. Leer lassen schaltet den Link ab.'],
                ],
            ],
            [
                'titel'   => 'Kundenbewertungen',
                'hinweis' => 'Diese Zitate stehen auf jeder Seite im Fußbereich, deshalb liegen sie bei den Stammdaten. Bitte nur echte Google-Rezensionen eintragen — erfundene Bewertungen verstoßen gegen das Wettbewerbsrecht. Ab vier Einträgen wird daraus ein Schieber mit Knöpfen; sechs bis acht sind eine gute Zahl.',
                'felder'  => [
                    ['pfad' => 'bewertungen.titel', 'typ' => 'text', 'label' => 'Überschrift'],
                    ['pfad' => 'bewertungen.eintraege', 'typ' => 'liste', 'label' => 'Bewertung', 'min' => 1, 'max' => 12, 'sortierbar' => true,
                     'subfelder' => [
                        ['pfad' => 'text',    'typ' => 'mehrzeilig', 'label' => 'Text der Bewertung', 'hilfe' => 'Ohne Anführungszeichen — die setzt die Seite selbst. Wörtlich übernehmen; Auslassungen mit … markieren.'],
                        ['pfad' => 'name',    'typ' => 'text',       'label' => 'Name', 'hilfe' => 'Abgekürzt, z. B. M. Knapp'],
                        ['pfad' => 'auftrag', 'typ' => 'text',       'label' => 'Auftrag', 'hilfe' => 'Was gemacht wurde, z. B. Hagelschaden, BMW M3. Darf leer bleiben.'],
                     ]],
                ],
            ],
        ],
    ],

    /* ---------------------------------------------------------------- */
    'home' => [
        'gruppe'       => 'Startseite',
        'titel'        => 'Startseite',
        'beschreibung' => 'Die Texte und Bilder der Startseite, in der Reihenfolge, in der sie dort erscheinen.',
        'gruppen' => [
            [
                'titel'  => 'Kopfbereich',
                'felder' => [
                    ['pfad' => 'hero.kicker',       'typ' => 'text',       'label' => 'Zeile über der Überschrift'],
                    ['pfad' => 'hero.titel',        'typ' => 'text',       'label' => 'Überschrift, erste Zeile'],
                    ['pfad' => 'hero.titel_zusatz', 'typ' => 'text',       'label' => 'Überschrift, zweite Zeile', 'hilfe' => 'Wird grau dargestellt.'],
                    ['pfad' => 'hero.lead',         'typ' => 'mehrzeilig', 'label' => 'Einleitungstext'],
                    ['pfad' => 'hero.fineprint',    'typ' => 'text',       'label' => 'Kleingedrucktes unter den Schaltflächen'],
                    ['pfad' => 'hero.bild',         'typ' => 'bild',       'label' => 'Hintergrundbild', 'hilfe' => 'Querformat, mindestens 2400 Pixel breit. Dunkle Aufnahmen wirken hier am besten.'],
                    ['pfad' => 'hero.bild_alt',     'typ' => 'text',       'label' => 'Bildbeschreibung', 'hilfe' => 'Beschreibt das Bild für Menschen, die es nicht sehen können. Pflicht.'],
                ],
            ],
            [
                'titel'  => 'Vier Argumente unter dem Kopfbereich',
                'felder' => [
                    ['pfad' => 'trust', 'typ' => 'liste', 'label' => 'Argumente', 'min' => 4, 'max' => 4,
                     'subfelder' => [
                        ['pfad' => 'titel', 'typ' => 'text', 'label' => 'Überschrift'],
                        ['pfad' => 'text',  'typ' => 'text', 'label' => 'Erklärung'],
                     ]],
                ],
            ],
            [
                'titel'  => 'Der Betrieb',
                'felder' => [
                    ['pfad' => 'betrieb.bild',       'typ' => 'bild',       'label' => 'Foto', 'hilfe' => 'Am besten der Inhaber oder das Team vor der Halle. Keine Stockfotos.'],
                    ['pfad' => 'betrieb.bild_alt',   'typ' => 'text',       'label' => 'Bildbeschreibung'],
                    ['pfad' => 'betrieb.badge.name', 'typ' => 'text',       'label' => 'Name im schwarzen Kasten'],
                    ['pfad' => 'betrieb.badge.text', 'typ' => 'mehrzeilig', 'label' => 'Text im schwarzen Kasten'],
                ],
            ],
            [
                'titel'   => 'Vorher / Nachher',
                'hinweis' => 'Beide Bilder müssen dasselbe Fahrzeug aus demselben Winkel zeigen, sonst funktioniert der Schieberegler nicht.',
                'felder'  => [
                    ['pfad' => 'ergebnisse.faelle', 'typ' => 'liste', 'label' => 'Fälle', 'min' => 1, 'max' => 3,
                     'subfelder' => [
                        ['pfad' => 'name',    'typ' => 'text',       'label' => 'Bezeichnung', 'hilfe' => 'z. B. Hagelschaden Dach'],
                        ['pfad' => 'meta',    'typ' => 'text',       'label' => 'Dauer', 'hilfe' => 'z. B. 3 Tage'],
                        ['pfad' => 'note',    'typ' => 'mehrzeilig', 'label' => 'Beschreibung'],
                        ['pfad' => 'vorher',  'typ' => 'bild',       'label' => 'Bild vorher'],
                        ['pfad' => 'nachher', 'typ' => 'bild',       'label' => 'Bild nachher'],
                     ]],
                ],
            ],
            [
                'titel'  => 'Mobiler Service',
                'felder' => [
                    ['pfad' => 'mobiler_service.titel', 'typ' => 'text',       'label' => 'Überschrift'],
                    ['pfad' => 'mobiler_service.text',  'typ' => 'mehrzeilig', 'label' => 'Text'],
                ],
            ],
        ],
    ],

    /* ---------------------------------------------------------------- */
    'leistungen' => [
        'gruppe'       => 'Startseite',
        'titel'        => 'Bereiche am Fahrzeug',
        'beschreibung' => 'Die sieben Bereiche am Fahrzeug auf der Startseite. Sechs davon haben eine eigene Unterseite.',
        'gruppen' => [
            [
                'titel'   => 'Bereiche',
                'hinweis' => 'Die Position bestimmt, wo der Marker auf dem Fahrzeugfoto sitzt: 0 % ist links bzw. oben, 100 % rechts bzw. unten. Nach einem neuen Fahrzeugfoto müssen alle sieben Marker neu gesetzt werden.',
                'felder'  => [
                    ['pfad' => 'eintraege', 'typ' => 'liste', 'label' => 'Bereiche', 'min' => 7, 'max' => 7, 'sortierbar' => false,
                     'subfelder' => [
                        ['pfad' => 'titel',     'typ' => 'text',       'label' => 'Überschrift'],
                        ['pfad' => 'tag',       'typ' => 'text',       'label' => 'Kategorie', 'hilfe' => 'Exterieur, Interieur oder Karosserie'],
                        ['pfad' => 'lead',      'typ' => 'mehrzeilig', 'label' => 'Beschreibung'],
                        ['pfad' => 'hotspot.x', 'typ' => 'zahl',       'label' => 'Position von links (%)'],
                        ['pfad' => 'hotspot.y', 'typ' => 'zahl',       'label' => 'Position von oben (%)'],
                     ]],
                ],
            ],
        ],
    ],

    /* ================================================================== */
    /* Leistungen — je Seite ein Eintrag.                                  */
    /*                                                                     */
    /* Editierbar sind Texte und Bilder. Wie viele Zonen, welche           */
    /* Lackschichten, welche Reihenfolge: das bleibt im Code. Das sind     */
    /* Designentscheidungen, keine Inhalte — freigegeben zerlegt der Kunde */
    /* die Module unbeabsichtigt.                                          */
    /* ================================================================== */

    'leistungen-hub' => [
        'gruppe'       => 'Leistungen',
        'titel'        => 'Übersicht Leistungen',
        'beschreibung' => 'Die Seite, auf der alle sechs Leistungen untereinander stehen.',
        'gruppen' => [
            [
                'titel'  => 'Suchmaschine',
                'hinweis' => 'Der Titel erscheint als blaue Zeile im Google-Ergebnis, die Beschreibung als grauer Text darunter.',
                'felder' => [
                    ['pfad' => 'seo.titel',        'typ' => 'text',       'label' => 'Titel bei Google', 'hilfe' => 'Höchstens 60 Zeichen, sonst schneidet Google ab.'],
                    ['pfad' => 'seo.beschreibung', 'typ' => 'mehrzeilig', 'label' => 'Beschreibung bei Google', 'hilfe' => 'Höchstens 155 Zeichen.'],
                ],
            ],
            [
                'titel'  => 'Kopfbereich',
                'felder' => [
                    ['pfad' => 'hero.kicker',     'typ' => 'text',       'label' => 'Zeile über der Überschrift'],
                    ['pfad' => 'hero.titel',      'typ' => 'text',       'label' => 'Überschrift'],
                    ['pfad' => 'hero.lead',       'typ' => 'mehrzeilig', 'label' => 'Einleitungstext'],
                    ['pfad' => 'hero.bild',       'typ' => 'bild',       'label' => 'Hintergrundbild'],
                    ['pfad' => 'hero.bild_alt',   'typ' => 'text',       'label' => 'Bildbeschreibung'],
                    ['pfad' => 'hero.gilt_titel', 'typ' => 'text',       'label' => 'Überschrift der kurzen Liste rechts'],
                ],
            ],
            [
                'titel'  => 'Was meistens zusammengehört',
                'felder' => [
                    ['pfad' => 'kombinationen.kicker',       'typ' => 'text',       'label' => 'Zeile über der Überschrift'],
                    ['pfad' => 'kombinationen.titel',        'typ' => 'text',       'label' => 'Überschrift'],
                    ['pfad' => 'kombinationen.beschreibung', 'typ' => 'mehrzeilig', 'label' => 'Text daneben'],
                    ['pfad' => 'kombinationen.faelle', 'typ' => 'liste', 'label' => 'Fall', 'min' => 3, 'max' => 3, 'sortierbar' => false,
                     'subfelder' => [
                        ['pfad' => 'kicker', 'typ' => 'text',       'label' => 'Zeile über der Überschrift'],
                        ['pfad' => 'titel',  'typ' => 'text',       'label' => 'Überschrift'],
                        ['pfad' => 'text',   'typ' => 'mehrzeilig', 'label' => 'Text'],
                     ]],
                ],
            ],
            [
                'titel'   => 'Was wir nicht machen',
                'hinweis' => 'Ehrlich gehaltene Abgrenzung. Sie spart Anrufe, die niemandem etwas bringen.',
                'felder'  => [
                    ['pfad' => 'grenzen.titel', 'typ' => 'text',       'label' => 'Überschrift'],
                    ['pfad' => 'grenzen.lead',  'typ' => 'mehrzeilig', 'label' => 'Einleitung'],
                    ['pfad' => 'grenzen.punkte', 'typ' => 'liste', 'label' => 'Punkt', 'min' => 1, 'max' => 8,
                     'subfelder' => [
                        ['pfad' => 'titel', 'typ' => 'text',       'label' => 'Überschrift'],
                        ['pfad' => 'text',  'typ' => 'mehrzeilig', 'label' => 'Erklärung'],
                     ]],
                ],
            ],
            [
                'titel'  => 'Kurztexte der sechs Leistungen',
                'hinweis' => 'Ein Satz je Leistung, dazu die Schlagworte und das Vorschaubild, das beim Überfahren erscheint.',
                'felder' => [
                    ['pfad' => 'eintraege.fahrzeugpflege-exterieur.kurz',     'typ' => 'mehrzeilig', 'label' => 'Exterieur — Kurztext'],
                    ['pfad' => 'eintraege.fahrzeugpflege-exterieur.bild',     'typ' => 'bild',       'label' => 'Exterieur — Vorschaubild'],
                    ['pfad' => 'eintraege.fahrzeugpflege-exterieur.bild_alt', 'typ' => 'text',       'label' => 'Exterieur — Bildbeschreibung'],
                    ['pfad' => 'eintraege.fahrzeugpflege-interieur.kurz',     'typ' => 'mehrzeilig', 'label' => 'Interieur — Kurztext'],
                    ['pfad' => 'eintraege.fahrzeugpflege-interieur.bild',     'typ' => 'bild',       'label' => 'Interieur — Vorschaubild'],
                    ['pfad' => 'eintraege.fahrzeugpflege-interieur.bild_alt', 'typ' => 'text',       'label' => 'Interieur — Bildbeschreibung'],
                    ['pfad' => 'eintraege.dellen-hagelschaden.kurz',          'typ' => 'mehrzeilig', 'label' => 'Dellen — Kurztext'],
                    ['pfad' => 'eintraege.dellen-hagelschaden.bild',          'typ' => 'bild',       'label' => 'Dellen — Vorschaubild'],
                    ['pfad' => 'eintraege.dellen-hagelschaden.bild_alt',      'typ' => 'text',       'label' => 'Dellen — Bildbeschreibung'],
                    ['pfad' => 'eintraege.lackierarbeiten.kurz',              'typ' => 'mehrzeilig', 'label' => 'Lackierarbeiten — Kurztext'],
                    ['pfad' => 'eintraege.lackierarbeiten.bild',              'typ' => 'bild',       'label' => 'Lackierarbeiten — Vorschaubild'],
                    ['pfad' => 'eintraege.lackierarbeiten.bild_alt',          'typ' => 'text',       'label' => 'Lackierarbeiten — Bildbeschreibung'],
                    ['pfad' => 'eintraege.lederreparatur.kurz',               'typ' => 'mehrzeilig', 'label' => 'Lederreparatur — Kurztext'],
                    ['pfad' => 'eintraege.lederreparatur.bild',               'typ' => 'bild',       'label' => 'Lederreparatur — Vorschaubild'],
                    ['pfad' => 'eintraege.lederreparatur.bild_alt',           'typ' => 'text',       'label' => 'Lederreparatur — Bildbeschreibung'],
                    ['pfad' => 'eintraege.ozonbehandlung.kurz',               'typ' => 'mehrzeilig', 'label' => 'Ozonbehandlung — Kurztext'],
                    ['pfad' => 'eintraege.ozonbehandlung.bild',               'typ' => 'bild',       'label' => 'Ozonbehandlung — Vorschaubild'],
                    ['pfad' => 'eintraege.ozonbehandlung.bild_alt',           'typ' => 'text',       'label' => 'Ozonbehandlung — Bildbeschreibung'],
                ],
            ],
        ],
    ],

    /* ---------------------------------------------------------------- */
    'leistung-fahrzeugpflege-exterieur' => leistungs_schema(
        'Fahrzeugpflege Exterieur',
        'Lack, Politur und Versiegelung — die Seite unter /leistungen/fahrzeugpflege-exterieur/.',
        [
            [
                'titel'   => 'Die drei Politurstufen',
                'hinweis' => 'Die Zeichnung mit den Lackschichten baut die Website selbst. Hier stehen nur die Texte dazu.',
                'felder'  => [
                    ['pfad' => 'querschnitt.kicker',       'typ' => 'text',       'label' => 'Zeile über der Überschrift'],
                    ['pfad' => 'querschnitt.titel',        'typ' => 'text',       'label' => 'Überschrift'],
                    ['pfad' => 'querschnitt.beschreibung', 'typ' => 'mehrzeilig', 'label' => 'Text daneben'],
                    ['pfad' => 'querschnitt.hinweis',      'typ' => 'mehrzeilig', 'label' => 'Hinweis unter der Zeichnung'],
                    ['pfad' => 'querschnitt.stufen', 'typ' => 'liste', 'label' => 'Stufe', 'min' => 3, 'max' => 3, 'sortierbar' => false,
                     'subfelder' => [
                        ['pfad' => 'name',         'typ' => 'text',       'label' => 'Name der Stufe'],
                        ['pfad' => 'unterzeile',   'typ' => 'text',       'label' => 'Unterzeile'],
                        ['pfad' => 'ueberschrift', 'typ' => 'text',       'label' => 'Überschrift der Tafel'],
                        ['pfad' => 'text',         'typ' => 'mehrzeilig', 'label' => 'Beschreibung'],
                        ['pfad' => 'wirksam_gegen','typ' => 'text',       'label' => 'Dagegen wirksam'],
                        ['pfad' => 'zeit',         'typ' => 'text',       'label' => 'Zeit'],
                        ['pfad' => 'wiederholbar', 'typ' => 'text',       'label' => 'Wiederholbar'],
                     ]],
                ],
            ],
            [
                'titel'  => 'Schutzschicht-Vergleich',
                'felder' => [
                    ['pfad' => 'schutz.kicker',  'typ' => 'text',       'label' => 'Zeile über der Überschrift'],
                    ['pfad' => 'schutz.titel',   'typ' => 'text',       'label' => 'Überschrift'],
                    ['pfad' => 'schutz.lead',    'typ' => 'mehrzeilig', 'label' => 'Einleitung'],
                    ['pfad' => 'schutz.hinweis', 'typ' => 'mehrzeilig', 'label' => 'Hinweis unter der Tabelle'],
                ],
            ],
        ]
    ),

    /* ---------------------------------------------------------------- */
    'leistung-fahrzeugpflege-interieur' => leistungs_schema(
        'Fahrzeugpflege Interieur',
        'Innenraumaufbereitung — die Seite unter /leistungen/fahrzeugpflege-interieur/.',
        [
            [
                'titel'   => 'Die sechs Zonen im Innenraum',
                'hinweis' => 'Die Zahl der Zonen liegt fest. Namen, Zeiten und Texte können Sie ändern.',
                'felder'  => [
                    ['pfad' => 'zonen_sektion.kicker',       'typ' => 'text',       'label' => 'Zeile über der Überschrift'],
                    ['pfad' => 'zonen_sektion.titel',        'typ' => 'text',       'label' => 'Überschrift'],
                    ['pfad' => 'zonen_sektion.beschreibung', 'typ' => 'mehrzeilig', 'label' => 'Text daneben'],
                    ['pfad' => 'zonen', 'typ' => 'liste', 'label' => 'Zone', 'min' => 6, 'max' => 6, 'sortierbar' => false,
                     'subfelder' => [
                        ['pfad' => 'name',   'typ' => 'text',       'label' => 'Name der Zone'],
                        ['pfad' => 'kurz',   'typ' => 'text',       'label' => 'Kurzbeschreibung'],
                        ['pfad' => 'zeit',   'typ' => 'text',       'label' => 'Dauer', 'hilfe' => 'z. B. 2 – 3 Stunden'],
                        ['pfad' => 'text',   'typ' => 'mehrzeilig', 'label' => 'Beschreibung'],
                        ['pfad' => 'grenze', 'typ' => 'mehrzeilig', 'label' => 'Wo die Grenze liegt'],
                     ]],
                ],
            ],
            [
                'titel'   => 'Trockeneis',
                'hinweis' => 'Eigener Abschnitt zwischen den Zonen und der Trocknung.',
                'felder'  => [
                    ['pfad' => 'trockeneis.kicker', 'typ' => 'text',       'label' => 'Zeile über der Überschrift'],
                    ['pfad' => 'trockeneis.titel',  'typ' => 'text',       'label' => 'Überschrift'],
                    ['pfad' => 'trockeneis.lead',   'typ' => 'mehrzeilig', 'label' => 'Einleitung'],
                    ['pfad' => 'trockeneis.punkte', 'typ' => 'liste', 'label' => 'Punkt', 'min' => 2, 'max' => 4,
                     'subfelder' => [
                        ['pfad' => 'titel', 'typ' => 'text',       'label' => 'Überschrift'],
                        ['pfad' => 'text',  'typ' => 'mehrzeilig', 'label' => 'Beschreibung'],
                     ]],
                ],
            ],
            [
                'titel'  => 'Ablauf und Trocknung',
                'felder' => [
                    ['pfad' => 'trocknung.kicker', 'typ' => 'text',       'label' => 'Zeile über der Überschrift'],
                    ['pfad' => 'trocknung.titel',  'typ' => 'text',       'label' => 'Überschrift'],
                    ['pfad' => 'trocknung.lead',   'typ' => 'mehrzeilig', 'label' => 'Einleitung'],
                    ['pfad' => 'trocknung.schritte', 'typ' => 'liste', 'label' => 'Schritt', 'min' => 4, 'max' => 4, 'sortierbar' => false,
                     'subfelder' => [
                        ['pfad' => 'zeit',  'typ' => 'text',       'label' => 'Zeitpunkt'],
                        ['pfad' => 'titel', 'typ' => 'text',       'label' => 'Überschrift'],
                        ['pfad' => 'text',  'typ' => 'mehrzeilig', 'label' => 'Beschreibung'],
                     ]],
                ],
            ],
        ]
    ),

    /* ---------------------------------------------------------------- */
    'leistung-dellen-hagelschaden' => leistungs_schema(
        'Dellen & Hagelschaden',
        'Ausbeulen ohne Lackieren — die Seite unter /leistungen/dellen-hagelschaden/.',
        [
            [
                'titel'   => 'Machbarkeitsprüfung',
                'hinweis' => 'Die vier Karten, die zeigen, wann Drücken funktioniert und wann nicht.',
                'felder'  => [
                    ['pfad' => 'machbarkeit.kicker', 'typ' => 'text',       'label' => 'Zeile über der Überschrift'],
                    ['pfad' => 'machbarkeit.titel',  'typ' => 'text',       'label' => 'Überschrift'],
                    ['pfad' => 'machbarkeit.lead',   'typ' => 'mehrzeilig', 'label' => 'Text daneben'],
                    ['pfad' => 'machbarkeit.checks', 'typ' => 'liste', 'label' => 'Karte', 'min' => 4, 'max' => 4, 'sortierbar' => false,
                     'subfelder' => [
                        ['pfad' => 'titel',  'typ' => 'text',       'label' => 'Überschrift'],
                        ['pfad' => 'urteil', 'typ' => 'text',       'label' => 'Urteil', 'hilfe' => 'Kurzes Etikett, z. B. „geht" oder „geht nicht"'],
                        ['pfad' => 'text',   'typ' => 'mehrzeilig', 'label' => 'Erklärung'],
                     ]],
                ],
            ],
            [
                'titel'   => 'Panel-Karte',
                'hinweis' => 'Die Anordnung der Bauteile im Raster liegt fest. Namen, Dellenzahlen und Texte können Sie ändern.',
                'felder'  => [
                    ['pfad' => 'panelkarte.kicker',           'typ' => 'text',       'label' => 'Zeile über der Überschrift'],
                    ['pfad' => 'panelkarte.titel',            'typ' => 'text',       'label' => 'Überschrift'],
                    ['pfad' => 'panelkarte.beschreibung',     'typ' => 'mehrzeilig', 'label' => 'Text daneben'],
                    ['pfad' => 'panelkarte.bildunterschrift', 'typ' => 'text',       'label' => 'Bildunterschrift'],
                    ['pfad' => 'panelkarte.hinweis',          'typ' => 'mehrzeilig', 'label' => 'Hinweis unter der Karte'],
                    ['pfad' => 'panelkarte.panels', 'typ' => 'liste', 'label' => 'Bauteil', 'min' => 8, 'max' => 8, 'sortierbar' => false,
                     'subfelder' => [
                        ['pfad' => 'name',           'typ' => 'text',       'label' => 'Bauteil'],
                        ['pfad' => 'anzahl',         'typ' => 'zahl',       'label' => 'Anzahl Dellen'],
                        ['pfad' => 'demontage',      'typ' => 'text',       'label' => 'Demontage'],
                        ['pfad' => 'zugang',         'typ' => 'text',       'label' => 'Zugang'],
                        ['pfad' => 'schwierigkeit',  'typ' => 'text',       'label' => 'Schwierigkeit'],
                        ['pfad' => 'text',           'typ' => 'mehrzeilig', 'label' => 'Beschreibung'],
                     ]],
                ],
            ],
            [
                'titel'   => 'Drei Fotostationen',
                'hinweis' => 'Diese Seite hat statt eines Schiebereglers drei Aufnahmen aus dem Ablauf.',
                'felder'  => [
                    ['pfad' => 'stationen.kicker', 'typ' => 'text',       'label' => 'Zeile über der Überschrift'],
                    ['pfad' => 'stationen.titel',  'typ' => 'text',       'label' => 'Überschrift'],
                    ['pfad' => 'stationen.lead',   'typ' => 'mehrzeilig', 'label' => 'Einleitung'],
                    ['pfad' => 'stationen.eintraege', 'typ' => 'liste', 'label' => 'Station', 'min' => 3, 'max' => 3, 'sortierbar' => false,
                     'subfelder' => [
                        ['pfad' => 'titel',    'typ' => 'text',       'label' => 'Überschrift'],
                        ['pfad' => 'text',     'typ' => 'mehrzeilig', 'label' => 'Beschreibung'],
                        ['pfad' => 'bild',     'typ' => 'bild',       'label' => 'Foto'],
                        ['pfad' => 'bild_alt', 'typ' => 'text',       'label' => 'Bildbeschreibung'],
                     ]],
                ],
            ],
            [
                'titel'   => 'Versicherung und Gutachten',
                'hinweis' => 'Vorsicht: Was hier steht, ist eine Zusage an den Kunden. Bitte nur, was Sie auch einhalten.',
                'felder'  => [
                    ['pfad' => 'versicherung.kicker', 'typ' => 'text',       'label' => 'Zeile über der Überschrift'],
                    ['pfad' => 'versicherung.titel',  'typ' => 'text',       'label' => 'Überschrift'],
                    ['pfad' => 'versicherung.text',   'typ' => 'mehrzeilig', 'label' => 'Einleitung'],
                    ['pfad' => 'versicherung.punkte', 'typ' => 'liste', 'label' => 'Punkt', 'min' => 1, 'max' => 6,
                     'subfelder' => [
                        ['pfad' => 'titel', 'typ' => 'text',       'label' => 'Überschrift'],
                        ['pfad' => 'text',  'typ' => 'mehrzeilig', 'label' => 'Erklärung'],
                     ]],
                ],
            ],
            [
                'titel'   => 'Preis',
                'hinweis' => 'Diese Seite erklärt den Preis in drei Karten statt in einem Absatz. Euro-Beträge stehen auf keiner Seite.',
                'felder'  => [
                    ['pfad' => 'preis.karten', 'typ' => 'liste', 'label' => 'Karte', 'min' => 3, 'max' => 3, 'sortierbar' => false,
                     'subfelder' => [
                        ['pfad' => 'kicker', 'typ' => 'text',       'label' => 'Zeile über der Überschrift'],
                        ['pfad' => 'titel',  'typ' => 'text',       'label' => 'Überschrift'],
                        ['pfad' => 'text',   'typ' => 'mehrzeilig', 'label' => 'Text'],
                     ]],
                ],
            ],
        ],
        ['preis' => false, 'vergleich' => false]
    ),

    /* ---------------------------------------------------------------- */
    'leistung-lackierarbeiten' => leistungs_schema(
        'Lackierarbeiten',
        'Beilackierung, Farbtonfindung und Beklebung — die Seite unter /leistungen/lackierarbeiten/.',
        [
            [
                'titel'  => 'Farbtonfindung',
                'felder' => [
                    ['pfad' => 'farbton.kicker',   'typ' => 'text',       'label' => 'Zeile über der Überschrift'],
                    ['pfad' => 'farbton.titel',    'typ' => 'text',       'label' => 'Überschrift'],
                    ['pfad' => 'farbton.lead',     'typ' => 'mehrzeilig', 'label' => 'Einleitung'],
                    ['pfad' => 'farbton.bild',     'typ' => 'bild',       'label' => 'Bild'],
                    ['pfad' => 'farbton.bild_alt', 'typ' => 'text',       'label' => 'Bildbeschreibung'],
                    ['pfad' => 'farbton.schritte', 'typ' => 'liste', 'label' => 'Schritt', 'min' => 2, 'max' => 4, 'sortierbar' => false,
                     'subfelder' => [
                        ['pfad' => 'titel', 'typ' => 'text',       'label' => 'Überschrift'],
                        ['pfad' => 'text',  'typ' => 'mehrzeilig', 'label' => 'Beschreibung'],
                     ]],
                ],
            ],
            [
                'titel'   => 'Fahrzeugbeklebung',
                'hinweis' => 'Dieser Abschnitt ist noch unbestätigt. Bitte streichen Sie, was Sie nicht anbieten.',
                'felder'  => [
                    ['pfad' => 'beklebung.kicker',  'typ' => 'text',       'label' => 'Zeile über der Überschrift'],
                    ['pfad' => 'beklebung.titel',   'typ' => 'text',       'label' => 'Überschrift'],
                    ['pfad' => 'beklebung.lead',    'typ' => 'mehrzeilig', 'label' => 'Einleitung'],
                    ['pfad' => 'beklebung.hinweis', 'typ' => 'mehrzeilig', 'label' => 'Hinweis im hellen Kasten'],
                    ['pfad' => 'beklebung.cta',     'typ' => 'text',       'label' => 'Beschriftung der Schaltfläche'],
                    ['pfad' => 'beklebung.varianten', 'typ' => 'liste', 'label' => 'Variante', 'min' => 1, 'max' => 6,
                     'subfelder' => [
                        ['pfad' => 'titel', 'typ' => 'text',       'label' => 'Überschrift'],
                        ['pfad' => 'text',  'typ' => 'mehrzeilig', 'label' => 'Beschreibung'],
                     ]],
                ],
            ],
        ],
        ['hero_bild' => false]
    ),

    /* ---------------------------------------------------------------- */
    'leistung-lederreparatur' => leistungs_schema(
        'Lederreparatur',
        'Farbabrieb, Risse und Löcher — die Seite unter /leistungen/lederreparatur/.',
        [
            [
                'titel'  => 'Welches Material',
                'felder' => [
                    ['pfad' => 'material.kicker', 'typ' => 'text', 'label' => 'Überschrift des Abschnitts'],
                    ['pfad' => 'material.karten', 'typ' => 'liste', 'label' => 'Material', 'min' => 3, 'max' => 3, 'sortierbar' => false,
                     'subfelder' => [
                        ['pfad' => 'name',   'typ' => 'text',       'label' => 'Material'],
                        ['pfad' => 'urteil', 'typ' => 'text',       'label' => 'Etikett', 'hilfe' => 'z. B. „alles möglich"'],
                        ['pfad' => 'text',   'typ' => 'mehrzeilig', 'label' => 'Beschreibung'],
                     ]],
                ],
            ],
            [
                'titel'   => 'Die vier Schadensgrade',
                'hinweis' => 'Die Reihenfolge liegt fest — sie geht von leicht nach schwer.',
                'felder'  => [
                    ['pfad' => 'grade_sektion.kicker',       'typ' => 'text',       'label' => 'Zeile über der Überschrift'],
                    ['pfad' => 'grade_sektion.titel',        'typ' => 'text',       'label' => 'Überschrift'],
                    ['pfad' => 'grade_sektion.beschreibung', 'typ' => 'mehrzeilig', 'label' => 'Text daneben'],
                    ['pfad' => 'grade', 'typ' => 'liste', 'label' => 'Grad', 'min' => 4, 'max' => 4, 'sortierbar' => false,
                     'subfelder' => [
                        ['pfad' => 'name',         'typ' => 'text',       'label' => 'Kurzname'],
                        ['pfad' => 'ueberschrift', 'typ' => 'text',       'label' => 'Überschrift der Tafel'],
                        ['pfad' => 'text',         'typ' => 'mehrzeilig', 'label' => 'Beschreibung'],
                        ['pfad' => 'aufwand',      'typ' => 'text',       'label' => 'Aufwand'],
                        ['pfad' => 'dauer',        'typ' => 'text',       'label' => 'Dauer'],
                        ['pfad' => 'mobil',        'typ' => 'text',       'label' => 'Mobil möglich'],
                        ['pfad' => 'erwartung',    'typ' => 'mehrzeilig', 'label' => 'Ehrliche Erwartung', 'hilfe' => 'Was man hinterher noch sieht.'],
                     ]],
                ],
            ],
            [
                'titel'  => 'Pflegehinweise',
                'felder' => [
                    ['pfad' => 'preis.pflege_titel', 'typ' => 'text', 'label' => 'Überschrift der Liste'],
                ],
            ],
        ],
        ['faq_kicker' => false]
    ),

    /* ---------------------------------------------------------------- */
    'leistung-ozonbehandlung' => leistungs_schema(
        'Ozonbehandlung',
        'Gegen Rauch, Tiergeruch und Schimmel — die Seite unter /leistungen/ozonbehandlung/.',
        [
            [
                'titel'   => 'Geruchsdiagnose',
                'hinweis' => 'Fünf Geruchsquellen zur Auswahl. Die Zahl liegt fest, die Texte nicht.',
                'felder'  => [
                    ['pfad' => 'diagnose.kicker',       'typ' => 'text',       'label' => 'Zeile über der Überschrift'],
                    ['pfad' => 'diagnose.titel',        'typ' => 'text',       'label' => 'Überschrift'],
                    ['pfad' => 'diagnose.beschreibung', 'typ' => 'mehrzeilig', 'label' => 'Text daneben'],
                    ['pfad' => 'quellen', 'typ' => 'liste', 'label' => 'Geruchsquelle', 'min' => 5, 'max' => 5, 'sortierbar' => false,
                     'subfelder' => [
                        ['pfad' => 'label',        'typ' => 'text',       'label' => 'Beschriftung der Schaltfläche'],
                        ['pfad' => 'urteil',       'typ' => 'text',       'label' => 'Etikett', 'hilfe' => 'z. B. „Ozon hilft"'],
                        ['pfad' => 'ueberschrift', 'typ' => 'text',       'label' => 'Überschrift der Tafel'],
                        ['pfad' => 'text',         'typ' => 'mehrzeilig', 'label' => 'Beschreibung'],
                        ['pfad' => 'sinnvoll',     'typ' => 'text',       'label' => 'Ozon sinnvoll'],
                        ['pfad' => 'vorarbeit',    'typ' => 'text',       'label' => 'Vorarbeit'],
                        ['pfad' => 'dauer',        'typ' => 'text',       'label' => 'Dauer gesamt'],
                        ['pfad' => 'rueckfall',    'typ' => 'mehrzeilig', 'label' => 'Kommt zurück, wenn …'],
                     ]],
                ],
            ],
            [
                'titel'  => 'Ablauf und Sperrzeit',
                'felder' => [
                    ['pfad' => 'sperrzeit.kicker', 'typ' => 'text',       'label' => 'Zeile über der Überschrift'],
                    ['pfad' => 'sperrzeit.titel',  'typ' => 'text',       'label' => 'Überschrift'],
                    ['pfad' => 'sperrzeit.lead',   'typ' => 'mehrzeilig', 'label' => 'Einleitung'],
                    ['pfad' => 'sperrzeit.phasen', 'typ' => 'liste', 'label' => 'Phase', 'min' => 5, 'max' => 5, 'sortierbar' => false,
                     'subfelder' => [
                        ['pfad' => 'titel',    'typ' => 'text',       'label' => 'Überschrift'],
                        ['pfad' => 'text',     'typ' => 'mehrzeilig', 'label' => 'Beschreibung'],
                        ['pfad' => 'zustand',  'typ' => 'text',       'label' => 'Zustand des Fahrzeugs'],
                     ]],
                ],
            ],
            [
                'titel'  => 'Was Ozon nicht kann',
                'felder' => [
                    ['pfad' => 'grenzen.kicker', 'typ' => 'text',       'label' => 'Zeile über der Überschrift'],
                    ['pfad' => 'grenzen.titel',  'typ' => 'text',       'label' => 'Überschrift'],
                    ['pfad' => 'grenzen.lead',   'typ' => 'mehrzeilig', 'label' => 'Einleitung'],
                    ['pfad' => 'grenzen.punkte', 'typ' => 'liste', 'label' => 'Punkt', 'min' => 1, 'max' => 6,
                     'subfelder' => [
                        ['pfad' => 'titel', 'typ' => 'text',       'label' => 'Überschrift'],
                        ['pfad' => 'text',  'typ' => 'mehrzeilig', 'label' => 'Erklärung'],
                     ]],
                ],
            ],
            [
                'titel'   => 'Zwei Bilder statt Vorher/Nachher',
                'hinweis' => 'Diese Seite hat bewusst keinen Schieberegler — Geruch lässt sich nicht fotografieren.',
                'felder'  => [
                    ['pfad' => 'beleg.hinweis', 'typ' => 'mehrzeilig', 'label' => 'Text im hellen Kasten'],
                ],
            ],
        ],
        ['hero_bild' => false, 'vergleich' => false]
    ),

    /* ================================================================== */
    /* Weitere Seiten                                                      */
    /* ================================================================== */

    'galerie' => [
        'gruppe'       => 'Weitere Seiten',
        'titel'        => 'Galerie',
        'beschreibung' => 'Drei Aufträge im Detail und das Bildraster mit allen Arbeiten.',
        'gruppen' => [
            [
                'titel'  => 'Suchmaschine',
                'felder' => [
                    ['pfad' => 'seo.titel',        'typ' => 'text',       'label' => 'Titel bei Google'],
                    ['pfad' => 'seo.beschreibung', 'typ' => 'mehrzeilig', 'label' => 'Beschreibung bei Google'],
                ],
            ],
            [
                'titel'   => 'Kopfbereich',
                'hinweis' => 'Achtung beim Einleitungstext: Solange nicht alle Bilder aus eigener Arbeit stammen, darf dort nicht stehen, dass sie es tun. Das ist abmahnfähig.',
                'felder'  => [
                    ['pfad' => 'hero.kicker',     'typ' => 'text',       'label' => 'Zeile über der Überschrift'],
                    ['pfad' => 'hero.titel',      'typ' => 'text',       'label' => 'Überschrift'],
                    ['pfad' => 'hero.lead',       'typ' => 'mehrzeilig', 'label' => 'Einleitungstext'],
                    ['pfad' => 'hero.cta_titel',  'typ' => 'text',       'label' => 'Überschrift über der Schaltfläche'],
                    ['pfad' => 'hero.cta',        'typ' => 'text',       'label' => 'Beschriftung der Schaltfläche'],
                ],
            ],
            [
                'titel'   => 'Drei Aufträge im Detail',
                'hinweis' => 'Beide Bilder eines Falls müssen dasselbe Fahrzeug aus demselben Winkel zeigen, sonst zeigt der Schieberegler Beleuchtung statt Arbeit.',
                'felder'  => [
                    ['pfad' => 'faelle.kicker', 'typ' => 'text', 'label' => 'Überschrift des Abschnitts'],
                    ['pfad' => 'faelle.eintraege', 'typ' => 'liste', 'label' => 'Fall', 'min' => 1, 'max' => 3, 'sortierbar' => false,
                     'subfelder' => [
                        ['pfad' => 'kicker',            'typ' => 'text',       'label' => 'Zeile über der Überschrift'],
                        ['pfad' => 'titel',             'typ' => 'text',       'label' => 'Überschrift'],
                        ['pfad' => 'text',              'typ' => 'mehrzeilig', 'label' => 'Beschreibung'],
                        ['pfad' => 'vergleich.vorher',      'typ' => 'bild', 'label' => 'Bild vorher'],
                        ['pfad' => 'vergleich.vorher_alt',  'typ' => 'text', 'label' => 'Bildbeschreibung vorher'],
                        ['pfad' => 'vergleich.nachher',     'typ' => 'bild', 'label' => 'Bild nachher'],
                        ['pfad' => 'vergleich.nachher_alt', 'typ' => 'text', 'label' => 'Bildbeschreibung nachher'],
                     ]],
                ],
            ],
            [
                'titel'   => 'Bildraster',
                'hinweis' => 'Die Kategorie steuert den Filter über dem Raster. Schreiben Sie sie genau so wie bei den anderen Bildern, sonst entsteht ein neuer Filterknopf.',
                'felder'  => [
                    ['pfad' => 'raster.titel',        'typ' => 'text',       'label' => 'Überschrift'],
                    ['pfad' => 'raster.beschreibung', 'typ' => 'mehrzeilig', 'label' => 'Text daneben'],
                    ['pfad' => 'raster.fussnote',     'typ' => 'mehrzeilig', 'label' => 'Hinweis unter dem Raster'],
                    ['pfad' => 'raster.bilder', 'typ' => 'liste', 'label' => 'Bild', 'min' => 1, 'max' => 40,
                     'subfelder' => [
                        ['pfad' => 'bild',      'typ' => 'bild', 'label' => 'Foto'],
                        ['pfad' => 'alt',       'typ' => 'text', 'label' => 'Bildbeschreibung'],
                        ['pfad' => 'kategorie', 'typ' => 'text', 'label' => 'Kategorie', 'hilfe' => 'Exterieur, Interieur, Dellen & Hagel, Lack, Leder oder Ozon'],
                     ]],
                ],
            ],
        ],
    ],

    'kontakt' => [
        'gruppe'       => 'Weitere Seiten',
        'titel'        => 'Kontakt & Anfahrt',
        'beschreibung' => 'Anfrageformular, Anfahrtsbeschreibung und der Abschnitt zur Versicherung.',
        'gruppen' => [
            [
                'titel'  => 'Suchmaschine',
                'felder' => [
                    ['pfad' => 'seo.titel',        'typ' => 'text',       'label' => 'Titel bei Google'],
                    ['pfad' => 'seo.beschreibung', 'typ' => 'mehrzeilig', 'label' => 'Beschreibung bei Google'],
                ],
            ],
            [
                'titel'   => 'Kopfbereich',
                'hinweis' => 'Telefonnummern und E-Mail stehen unter Stammdaten — sie werden hier automatisch eingesetzt.',
                'felder'  => [
                    ['pfad' => 'hero.kicker', 'typ' => 'text',       'label' => 'Zeile über der Überschrift'],
                    ['pfad' => 'hero.titel',  'typ' => 'text',       'label' => 'Überschrift'],
                    ['pfad' => 'hero.lead',   'typ' => 'mehrzeilig', 'label' => 'Einleitungstext'],
                ],
            ],
            [
                'titel'  => 'Neben dem Formular',
                'felder' => [
                    ['pfad' => 'ohne_formular.titel', 'typ' => 'text', 'label' => 'Überschrift des Kastens'],
                    ['pfad' => 'ohne_formular.wege', 'typ' => 'liste', 'label' => 'Weg', 'min' => 1, 'max' => 3, 'sortierbar' => false,
                     'subfelder' => [
                        ['pfad' => 'titel', 'typ' => 'text',       'label' => 'Überschrift'],
                        ['pfad' => 'text',  'typ' => 'mehrzeilig', 'label' => 'Beschreibung'],
                     ]],
                    ['pfad' => 'zeiten.titel',   'typ' => 'text',       'label' => 'Überschrift Öffnungszeiten'],
                    ['pfad' => 'zeiten.hinweis', 'typ' => 'mehrzeilig', 'label' => 'Hinweis unter den Öffnungszeiten'],
                ],
            ],
            [
                'titel'   => 'Anfahrt',
                'hinweis' => 'Die Anschrift kommt aus den Stammdaten. Hier stehen nur die Erklärungen dazu — und das Kartenbild, sobald eines vorliegt.',
                'felder'  => [
                    ['pfad' => 'anfahrt.kicker',            'typ' => 'text', 'label' => 'Zeile über der Überschrift'],
                    ['pfad' => 'anfahrt.titel',             'typ' => 'text', 'label' => 'Überschrift'],
                    ['pfad' => 'anfahrt.karte.bild',        'typ' => 'bild', 'label' => 'Kartenbild', 'hilfe' => 'Ein Bildschirmfoto des Kartenausschnitts. Absichtlich keine eingebettete Karte — die würde Daten an Google senden, bevor jemand zustimmt.'],
                    ['pfad' => 'anfahrt.karte.bild_alt',    'typ' => 'text', 'label' => 'Bildbeschreibung'],
                    ['pfad' => 'anfahrt.punkte', 'typ' => 'liste', 'label' => 'Punkt', 'min' => 1, 'max' => 6,
                     'subfelder' => [
                        ['pfad' => 'titel', 'typ' => 'text',       'label' => 'Überschrift'],
                        ['pfad' => 'text',  'typ' => 'mehrzeilig', 'label' => 'Beschreibung'],
                     ]],
                ],
            ],
            [
                'titel'   => 'Versicherung und Gutachten',
                'hinweis' => 'Vorsicht: Was hier steht, ist eine Zusage an den Kunden. Bitte nur, was Sie auch einhalten.',
                'felder'  => [
                    ['pfad' => 'versicherung.kicker', 'typ' => 'text',       'label' => 'Zeile über der Überschrift'],
                    ['pfad' => 'versicherung.titel',  'typ' => 'text',       'label' => 'Überschrift'],
                    ['pfad' => 'versicherung.lead',   'typ' => 'mehrzeilig', 'label' => 'Einleitung'],
                    ['pfad' => 'versicherung.punkte', 'typ' => 'liste', 'label' => 'Punkt', 'min' => 1, 'max' => 6,
                     'subfelder' => [
                        ['pfad' => 'titel', 'typ' => 'text',       'label' => 'Überschrift'],
                        ['pfad' => 'text',  'typ' => 'mehrzeilig', 'label' => 'Erklärung'],
                     ]],
                ],
            ],
        ],
    ],

    /* ================================================================== */
    /* Rechtliches                                                         */
    /* ================================================================== */

    'impressum'   => rechtstext_schema('Impressum', 'Anbieterkennzeichnung nach § 5 DDG.'),
    'datenschutz' => rechtstext_schema('Datenschutzerklärung', 'Was mit den Daten der Besucher passiert.'),
    'agb'         => rechtstext_schema('AGB', 'Allgemeine Geschäftsbedingungen.'),
    'widerruf'    => rechtstext_schema('Widerrufsrecht', 'Belehrung für Verbraucher bei Verträgen aus der Ferne.'),
];
