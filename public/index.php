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

/**
 * Abgeschickte Anfragen. Beide Formulare der Website landen hier.
 *
 * Post-Redirect-Get: Nach dem Erfolg wird weitergeleitet, damit ein Neuladen
 * die Anfrage nicht ein zweites Mal schickt. Im Fehlerfall bleiben wir auf der
 * Kontaktseite und geben die Eingaben zurueck ins Formular — niemand soll
 * alles noch einmal tippen, weil ein Haken fehlte.
 */
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && $pfad === '/kontakt/') {
    require APP_ROOT . '/lib/anfrage.php';
    require APP_ROOT . '/lib/mail.php';

    [$erfolg, $formularFehler, $formularWerte] = anfrage_verarbeiten($_POST, $_FILES);

    if ($erfolg) {
        header('Location: /danke/', true, 303);
        exit;
    }
}

/**
 * Adressen fuer Suchmaschinen.
 *
 * Beide werden erzeugt und nicht als Datei gepflegt: die Leistungsseiten
 * kommen aus dem CMS, eine von Hand gepflegte sitemap.xml waere nach dem
 * ersten Umbenennen falsch. Die Ausgabe haengt am Host — auf einer Vorschau
 * unter *.vercel.app steht in robots.txt ein Verbot, damit die Vorschau nicht
 * neben der echten Domain im Index landet.
 */
if ($pfad === '/sitemap.xml/' || $pfad === '/robots.txt/') {
    if ($pfad === '/robots.txt/') {
        header('Content-Type: text/plain; charset=utf-8');
        if (!seo_indexierbar()) {
            exit("User-agent: *\nDisallow: /\n");
        }
        exit("User-agent: *\nAllow: /\nDisallow: /admin/\nDisallow: /danke/\n\nSitemap: " . absolut('/sitemap.xml') . "\n");
    }

    header('Content-Type: application/xml; charset=utf-8');
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    foreach (seo_seitenliste() as $eintrag) {
        echo "  <url>\n";
        echo '    <loc>' . htmlspecialchars(absolut($eintrag['pfad']), ENT_XML1) . "</loc>\n";
        echo '    <priority>' . $eintrag['prio'] . "</priority>\n";
        echo "  </url>\n";
    }
    echo '</urlset>' . "\n";
    exit;
}

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
    // Nur gesetzt, wenn gerade eine Anfrage schiefgegangen ist. Das Formular
    // fuellt sich damit wieder und zeigt, was fehlt.
    'fehler'   => $formularFehler ?? [],
    'werte'    => $formularWerte  ?? [],
]);
