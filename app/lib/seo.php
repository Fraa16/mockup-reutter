<?php
declare(strict_types=1);

/**
 * Kanonische Adressen, Vorschaubilder fuer geteilte Links und die
 * strukturierten Daten fuer Suchmaschinen.
 *
 * Nichts davon war bisher da: keine canonical, kein og:image, kein JSON-LD,
 * keine sitemap.xml. Fuer einen Betrieb, der ueber die Ortssuche gefunden
 * werden will, ist besonders der Eintrag als AutoRepair mit Adresse,
 * Telefonnummer und Oeffnungszeiten wichtig — das ist die Angabe, die Google
 * mit dem Unternehmensprofil abgleicht.
 */

/**
 * Schema und Host der laufenden Instanz.
 *
 * Bewusst aus dem Request und nicht aus einer Einstellung: die Domain steht
 * noch nicht fest (clean-box.eu behalten oder fahrzeugpflege-reutter.de
 * registrieren, siehe docs/offene-punkte.md). Waere sie fest eingetragen,
 * zeigten canonical und sitemap nach dem Umzug auf die falsche Adresse.
 */
function basis_url(): string
{
    $host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
    $sicher = ($_SERVER['HTTPS'] ?? '') !== ''
        || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https'
        || ($_SERVER['SERVER_PORT'] ?? '') === '443';

    return ($sicher ? 'https://' : 'http://') . $host;
}

/** Aus einem Pfad wird eine vollstaendige Adresse. */
function absolut(string $pfad): string
{
    return basis_url() . '/' . ltrim($pfad, '/');
}

/**
 * Der Pfad der aktuellen Seite, normalisiert wie im Router: mit fuehrendem und
 * abschliessendem Schraegstrich, ohne Query. Damit landet dieselbe Seite nicht
 * unter /kontakt, /kontakt/ und /kontakt/?utm=... dreimal im Index.
 */
function seo_pfad(): string
{
    $pfad = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $pfad = '/' . trim(rawurldecode($pfad), '/');

    return $pfad === '/' ? '/' : $pfad . '/';
}

/**
 * Soll diese Auslieferung in den Suchindex?
 *
 * Indexiert wird ausschliesslich die eine Adresse, die in den Stammdaten als
 * Livedomain eingetragen ist. Alles andere — Vorschauen, Testadressen, der
 * Rechner daheim — bleibt draussen.
 *
 * Die Entscheidung liegt bewusst im Inhalt und nicht im Code: Beim Umschalten
 * ist genau ein Feld im Panel zu fuellen, kein Deployment noetig. Und solange
 * es leer ist, ist NICHTS indexierbar. Das ist die sichere Richtung — eine
 * unfertige Seite, die versehentlich in den Index rutscht, bekommt man dort
 * wochenlang nicht mehr heraus, waehrend eine vergessene Freigabe eine Minute
 * kostet. Damit sie nicht vergessen wird, warnt das Panel sichtbar, solange
 * die aufgerufene Adresse gesperrt ist.
 *
 * Der Test greift exakt: „test.smartrepair-reutter.de" passt nicht auf
 * „smartrepair-reutter.de" und bleibt gesperrt, ohne dass dafuer irgendwo
 * etwas Zusaetzliches eingetragen werden muss.
 */
function seo_indexierbar(): bool
{
    $host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
    $live = strtolower(trim((string) get(site(), 'seo.live_domain', '')));

    if ($live === '') {
        return false;
    }

    /* Eingetragen wird von Hand, also kommt hier alles an, was man beim
       Kopieren aus der Adresszeile mitnimmt: „https://", ein Schraegstrich
       hinten, ein „www." davor. Alles drei bedeutet dieselbe Domain und darf
       nicht am Vergleich scheitern. Ein Port bleibt stehen — damit laesst
       sich die Freigabe lokal mit „localhost:8000" nachpruefen. */
    $blank = static function (string $wert): string {
        $wert = (string) preg_replace('#^https?://#', '', trim($wert));
        $wert = explode('/', $wert)[0];

        return (string) preg_replace('#^www\.#', '', rtrim($wert, '.'));
    };

    return $blank($host) === $blank($live)
        // Guertel und Hosentraeger: selbst wenn jemand eine Vorschauadresse
        // eintraegt, bleibt sie draussen.
        && !str_ends_with($host, '.vercel.app');
}

/**
 * Das Bild, das beim Teilen eines Links erscheint.
 *
 * Ohne Angabe nimmt es das Hero-Bild der Startseite — das ist immer ein
 * gepflegtes Bild und niemand muss eine zweite Stelle daran denken.
 */
function seo_vorschaubild(?string $bild = null): string
{
    $bild = $bild !== null && $bild !== '' ? $bild : (string) get(content('home'), 'hero.bild', '');
    if ($bild === '') {
        return '';
    }

    // Fuer die Vorschau die grosse Fassung: die Netzwerke skalieren selbst.
    return absolut(upload($bild));
}

/**
 * Der Betrieb als strukturierte Daten.
 *
 * Bewusst OHNE aggregateRating. Die 5,0 aus 281 Bewertungen sind belegt, aber
 * sie stammen aus dem Google-Unternehmensprofil. Bewertungen, die auf einer
 * fremden Plattform gesammelt wurden, im eigenen Markup auszuzeichnen,
 * widerspricht den Richtlinien fuer strukturierte Daten und kann eine
 * manuelle Massnahme ausloesen. Google kennt die Bewertung ohnehin — sie
 * kommt aus dem eigenen Profil, das oben verlinkt ist.
 *
 * @return array<string,mixed>
 */
function seo_jsonld_betrieb(): array
{
    $s = site();

    $daten = [
        '@context'  => 'https://schema.org',
        '@type'     => 'AutoRepair',
        '@id'       => absolut('/') . '#betrieb',
        'name'      => get($s, 'firma.name'),
        'url'       => absolut('/'),
        'telephone' => get($s, 'kontakt.telefon_link'),
        'email'     => get($s, 'kontakt.email'),
        'address'   => [
            '@type'           => 'PostalAddress',
            'streetAddress'   => get($s, 'firma.strasse'),
            'postalCode'      => get($s, 'firma.plz'),
            'addressLocality' => get($s, 'firma.ort'),
            'addressCountry'  => get($s, 'firma.land'),
        ],
        'vatID'     => get($s, 'firma.ustid'),
        'areaServed' => array_map(
            static fn (string $ort): array => ['@type' => 'City', 'name' => $ort],
            get($s, 'einsatzgebiet', [])
        ),
        'openingHoursSpecification' => array_map(
            static fn (array $z): array => [
                '@type'     => 'OpeningHoursSpecification',
                'dayOfWeek' => array_map(static fn (string $t): string => [
                    'Mo' => 'Monday', 'Di' => 'Tuesday', 'Mi' => 'Wednesday',
                    'Do' => 'Thursday', 'Fr' => 'Friday', 'Sa' => 'Saturday', 'So' => 'Sunday',
                ][$t] ?? $t, $z['tage']),
                'opens'  => $z['von'],
                'closes' => $z['bis'],
            ],
            get($s, 'oeffnungszeiten.strukturiert', [])
        ),
        'makesOffer' => array_map(
            static fn (array $l): array => [
                '@type'       => 'Offer',
                'itemOffered' => ['@type' => 'Service', 'name' => $l['titel']],
            ],
            content('leistungen')['eintraege'] ?? []
        ),
    ];

    $bild = seo_vorschaubild();
    if ($bild !== '') {
        $daten['image'] = $bild;
    }
    $profil = (string) get($s, 'kennzahlen.google_profil_url', '');
    if ($profil !== '') {
        $daten['sameAs'] = [$profil];
    }

    return array_filter($daten, static fn ($w): bool => $w !== '' && $w !== [] && $w !== null);
}

/**
 * Eine Leistungsseite als Service.
 *
 * @param array<string,mixed> $leistung Eintrag aus leistungen.json
 * @return array<string,mixed>
 */
function seo_jsonld_leistung(array $leistung, string $beschreibung): array
{
    $s = site();

    return [
        '@context'    => 'https://schema.org',
        '@type'       => 'Service',
        'name'        => $leistung['titel'],
        'description' => $beschreibung,
        'serviceType' => $leistung['titel'],
        'url'         => absolut('/leistungen/' . $leistung['slug'] . '/'),
        'provider'    => ['@id' => absolut('/') . '#betrieb'],
        'areaServed'  => array_map(
            static fn (string $ort): array => ['@type' => 'City', 'name' => $ort],
            get($s, 'einsatzgebiet', [])
        ),
    ];
}

/**
 * Brotkrumen als strukturierte Daten — dieselbe Liste, die im Kopf der Seite
 * sichtbar steht.
 *
 * @param list<array{label:string,ziel?:string}> $pfade
 * @return array<string,mixed>
 */
function seo_jsonld_brotkrumen(array $pfade): array
{
    $eintraege = [];
    foreach ($pfade as $i => $p) {
        $eintrag = [
            '@type'    => 'ListItem',
            'position' => $i + 1,
            'name'     => $p['label'],
        ];
        if (isset($p['ziel'])) {
            $eintrag['item'] = absolut($p['ziel']);
        }
        $eintraege[] = $eintrag;
    }

    return ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $eintraege];
}

/**
 * Fragen und Antworten, die auf der Seite sichtbar sind.
 *
 * @param list<array{frage:string,antwort:string}> $fragen
 * @return array<string,mixed>|null
 */
function seo_jsonld_faq(array $fragen): ?array
{
    if ($fragen === []) {
        return null;
    }

    return [
        '@context'   => 'https://schema.org',
        '@type'      => 'FAQPage',
        'mainEntity' => array_map(static fn (array $f): array => [
            '@type'          => 'Question',
            'name'           => $f['frage'],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['antwort']],
        ], $fragen),
    ];
}

/**
 * Gibt die Bloecke als <script type="application/ld+json"> aus.
 *
 * JSON_HEX_TAG schliesst aus, dass ein Inhalt mit spitzen Klammern das
 * Skript-Element vorzeitig beendet.
 */
function seo_jsonld_ausgeben(array ...$bloecke): string
{
    $ausgabe = '';
    foreach (array_filter($bloecke) as $block) {
        $json = json_encode($block, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP);
        if ($json !== false) {
            $ausgabe .= '<script type="application/ld+json">' . $json . "</script>\n";
        }
    }

    return $ausgabe;
}

/**
 * Alle Adressen der Website, in der Reihenfolge ihrer Wichtigkeit.
 *
 * @return list<array{pfad:string,prio:string}>
 */
function seo_seitenliste(): array
{
    $seiten = [
        ['pfad' => '/',             'prio' => '1.0'],
        ['pfad' => '/leistungen/',  'prio' => '0.9'],
        ['pfad' => '/galerie/',     'prio' => '0.7'],
        ['pfad' => '/kontakt/',     'prio' => '0.8'],
    ];

    foreach (leistungen_mit_seite() as $l) {
        $seiten[] = ['pfad' => '/leistungen/' . $l['slug'] . '/', 'prio' => '0.9'];
    }

    foreach (['impressum', 'datenschutz', 'agb', 'widerruf'] as $recht) {
        $seiten[] = ['pfad' => '/' . $recht . '/', 'prio' => '0.3'];
    }

    // /danke/ steht bewusst nicht drin: die Seite ergibt nur nach einer
    // abgeschickten Anfrage Sinn und hat allein keinen Wert.
    return $seiten;
}
