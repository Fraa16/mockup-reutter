<?php
declare(strict_types=1);

/**
 * Das Herz des CMS: Hier steht, welche Felder der Kunde im Panel sieht.
 *
 * Was nicht in dieser Datei steht, kann er nicht aendern — Layout, Reihenfolge
 * der Sektionen und CSS bleiben unangetastet. Genau das schuetzt das
 * abgenommene Design.
 *
 * Feldtypen: text, mehrzeilig, zahl, bild, auswahl, liste
 * 'pfad' ist die Punktnotation in die jeweilige JSON-Datei.
 */

return [

    /* ---------------------------------------------------------------- */
    'site' => [
        'titel'        => 'Stammdaten',
        'beschreibung' => 'Adresse, Telefon und Öffnungszeiten. Diese Angaben erscheinen auf jeder Seite — oben in der Leiste, im Fußbereich und im Kontaktbereich.',
        'gruppen' => [
            [
                'titel'  => 'Betrieb',
                'felder' => [
                    ['pfad' => 'firma.name',    'typ' => 'text', 'label' => 'Firmenname', 'hilfe' => 'Erscheint im Fußbereich und im Impressum.'],
                    ['pfad' => 'firma.inhaber', 'typ' => 'text', 'label' => 'Inhaber'],
                    ['pfad' => 'firma.strasse', 'typ' => 'text', 'label' => 'Straße und Hausnummer'],
                    ['pfad' => 'firma.plz',     'typ' => 'text', 'label' => 'Postleitzahl'],
                    ['pfad' => 'firma.ort',     'typ' => 'text', 'label' => 'Ort'],
                    ['pfad' => 'firma.ustid',   'typ' => 'text', 'label' => 'USt-IdNr.', 'hilfe' => 'Pflichtangabe im Impressum.'],
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
                ],
            ],
            [
                'titel'  => 'Kennzahlen',
                'hinweis' => 'Diese Zahlen stehen groß auf der Startseite. Bitte nur eintragen, was sich belegen lässt — erfundene Bewertungen sind abmahnfähig.',
                'felder' => [
                    ['pfad' => 'kennzahlen.jahre.wert',            'typ' => 'zahl', 'label' => 'Jahre im Handwerk'],
                    ['pfad' => 'kennzahlen.google_bewertung.wert', 'typ' => 'text', 'label' => 'Google-Bewertung', 'hilfe' => 'Mit Komma, z. B. 5,0'],
                    ['pfad' => 'kennzahlen.google_anzahl.wert',    'typ' => 'zahl', 'label' => 'Anzahl Google-Rezensionen'],
                ],
            ],
        ],
    ],

    /* ---------------------------------------------------------------- */
    'home' => [
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
                'titel'   => 'Kundenbewertungen',
                'hinweis' => 'Bitte echte Google-Rezensionen eintragen. Erfundene Bewertungen verstoßen gegen das Wettbewerbsrecht.',
                'felder'  => [
                    ['pfad' => 'bewertungen.eintraege', 'typ' => 'liste', 'label' => 'Bewertungen', 'min' => 1, 'max' => 3,
                     'subfelder' => [
                        ['pfad' => 'text', 'typ' => 'mehrzeilig', 'label' => 'Text der Bewertung', 'hilfe' => 'Ohne Anführungszeichen — die setzt die Seite selbst.'],
                        ['pfad' => 'name', 'typ' => 'text',       'label' => 'Name', 'hilfe' => 'Abgekürzt, z. B. M. Keller'],
                        ['pfad' => 'ort',  'typ' => 'text',       'label' => 'Ort'],
                     ]],
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
        'titel'        => 'Leistungen',
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
];
