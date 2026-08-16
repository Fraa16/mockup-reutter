<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

/**
 * Front-Controller. Jede Anfrage laeuft hier durch, .htaccess leitet alles
 * hierher um, was keine echte Datei ist.
 */

$pfad = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$pfad = '/' . trim(rawurldecode($pfad), '/');
$pfad = $pfad === '/' ? '/' : $pfad . '/';

/**
 * Feste Routen. Alles, was hier nicht steht, ist entweder eine Leistungsseite
 * oder ein 404 — geraten wird nichts.
 */
$routen = [
    '/'              => ['template' => 'home',       'inhalt' => 'home'],
    // Der Hub holt die Liste der Leistungen ueber leistungen_mit_seite();
    // 'leistungen-hub' traegt nur die eigenen Texte der Uebersichtsseite.
    // data/content/leistungen.json bleibt der reine Index.
    '/leistungen/'   => ['template' => 'leistungen', 'inhalt' => 'leistungen-hub'],
    '/galerie/'      => ['template' => 'galerie',    'inhalt' => 'galerie'],
    '/kontakt/'      => ['template' => 'kontakt',    'inhalt' => 'kontakt'],
    '/danke/'        => ['template' => 'danke',      'inhalt' => null],
    '/impressum/'    => ['template' => 'rechtstext', 'inhalt' => 'impressum'],
    '/datenschutz/'  => ['template' => 'rechtstext', 'inhalt' => 'datenschutz'],
    '/agb/'          => ['template' => 'rechtstext', 'inhalt' => 'agb'],
    '/widerruf/'     => ['template' => 'rechtstext', 'inhalt' => 'widerruf'],
];

$route = $routen[$pfad] ?? null;
$leistung = null;

// Leistungsseiten: /leistungen/<slug>/ — der Slug wird gegen die gepflegte
// Liste geprueft, nie ungefiltert weitergereicht.
//
// Jede Leistung hat ein eigenes Template und eine eigene Inhaltsdatei. Das ist
// Absicht: die sechs Seiten sind unterschiedlich aufgebaut und haben je ein
// eigenes interaktives Modul — ein gemeinsames Template wuerde sie einebnen.
if ($route === null && preg_match('#^/leistungen/([a-z0-9-]+)/$#', $pfad, $treffer)) {
    foreach (leistungen_mit_seite() as $eintrag) {
        if ($eintrag['slug'] === $treffer[1]) {
            $leistung = $eintrag;
            $route = [
                'template' => 'leistung-' . $eintrag['slug'],
                'inhalt'   => 'leistung-' . $eintrag['slug'],
            ];
            break;
        }
    }
}

if ($route === null) {
    http_response_code(404);
    echo render('fehler', ['titel' => 'Seite nicht gefunden', 'code' => 404]);
    exit;
}

// Templates, die noch nicht gebaut sind, sollen die Seite nicht zerlegen —
// waehrend der Entwicklung kommt stattdessen ein sichtbarer Platzhalter.
if (!is_file(APP_ROOT . '/templates/' . $route['template'] . '.php')) {
    http_response_code(503);
    echo render('fehler', [
        'titel' => 'Diese Seite wird gerade gebaut',
        'code'  => 503,
        'notiz' => 'Template "' . $route['template'] . '.php" fehlt noch.',
    ]);
    exit;
}

echo render($route['template'], [
    'seite'    => $route['inhalt'] !== null ? content($route['inhalt']) : [],
    'leistung' => $leistung,
    'pfad'     => $pfad,
]);
