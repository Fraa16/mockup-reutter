<?php
/**
 * Wegwerf-Pruefdatei. Nach dem Ablesen loeschen.
 *
 * Vergleicht jede Datei auf dem Server mit der Groesse, die sie im Projekt
 * hat. Damit faellt beides auf: was fehlt, und was beim Upload abgeschnitten
 * wurde. Zeigt nur Namen und Groessen, nie Inhalte.
 *
 * Liegt in web/ und rechnet von dort: __DIR__ ist web/, eine Ebene darueber
 * stehen app/, data/ und bin/.
 */
declare(strict_types=1);

const SOLL = [
    'app/bootstrap.php' => 1027,
    'app/config/zugangsdaten.beispiel.php' => 2495,
    'app/lib/anfrage.php' => 14722,
    'app/lib/auth.php' => 10058,
    'app/lib/content.php' => 1958,
    'app/lib/images.php' => 9753,
    'app/lib/mail.php' => 10984,
    'app/lib/posteingang.php' => 4887,
    'app/lib/render.php' => 4498,
    'app/lib/seo.php' => 10536,
    'app/lib/speichern.php' => 8393,
    'app/schema/felder.php' => 60161,
    'app/templates/danke.php' => 2203,
    'app/templates/fehler.php' => 1374,
    'app/templates/galerie.php' => 6234,
    'app/templates/home.php' => 17495,
    'app/templates/kontakt.php' => 8601,
    'app/templates/leistung-dellen-hagelschaden.php' => 11307,
    'app/templates/leistung-fahrzeugpflege-exterieur.php' => 10728,
    'app/templates/leistung-fahrzeugpflege-interieur.php' => 10266,
    'app/templates/leistung-lackierarbeiten.php' => 8682,
    'app/templates/leistung-lederreparatur.php' => 9737,
    'app/templates/leistung-ozonbehandlung.php' => 10270,
    'app/templates/leistung-transport-abschleppdienst.php' => 6697,
    'app/templates/leistungen.php' => 6850,
    'app/templates/partials/anfrage-form.php' => 6460,
    'app/templates/partials/bewertungen.php' => 3038,
    'app/templates/partials/brotkrumen.php' => 674,
    'app/templates/partials/fuss.php' => 5033,
    'app/templates/partials/kopf.php' => 6731,
    'app/templates/partials/kurzanfrage.php' => 3163,
    'app/templates/partials/mobil-banner.php' => 681,
    'app/templates/partials/trust-strip.php' => 1033,
    'app/templates/partials/vergleich.php' => 1015,
    'app/templates/rechtstext.php' => 7078,
    'data/content/agb.json' => 8106,
    'data/content/datenschutz.json' => 8309,
    'data/content/galerie.json' => 8213,
    'data/content/home.json' => 10392,
    'data/content/impressum.json' => 6873,
    'data/content/kontakt.json' => 5150,
    'data/content/leistung-dellen-hagelschaden.json' => 13214,
    'data/content/leistung-fahrzeugpflege-exterieur.json' => 9103,
    'data/content/leistung-fahrzeugpflege-interieur.json' => 13139,
    'data/content/leistung-lackierarbeiten.json' => 9719,
    'data/content/leistung-lederreparatur.json' => 11614,
    'data/content/leistung-ozonbehandlung.json' => 14147,
    'data/content/leistung-transport-abschleppdienst.json' => 6373,
    'data/content/leistungen-hub.json' => 9552,
    'data/content/leistungen.json' => 7918,
    'data/content/site.json' => 8904,
    'data/content/widerruf.json' => 6783,
    'bin/ableitungen.php' => 1578,
    'bin/passwort-setzen.php' => 2665,
    'web/.htaccess' => 5050,
    'web/.user.ini' => 1217,
    'web/admin/anfragen.php' => 9272,
    'web/admin/assets/admin.css' => 12579,
    'web/admin/assets/fotos.js' => 2873,
    'web/admin/edit.php' => 8896,
    'web/admin/foto-upload.php' => 2159,
    'web/admin/fotos.php' => 9359,
    'web/admin/index.php' => 9868,
    'web/assets/css/styles.css' => 165376,
    'web/assets/favicon.svg' => 954,
    'web/assets/fonts/barlow-400-latin-ext.woff2' => 14268,
    'web/assets/fonts/barlow-400-latin.woff2' => 22196,
    'web/assets/fonts/barlow-500-latin-ext.woff2' => 14416,
    'web/assets/fonts/barlow-500-latin.woff2' => 22008,
    'web/assets/fonts/barlow-600-latin-ext.woff2' => 14968,
    'web/assets/fonts/barlow-600-latin.woff2' => 22772,
    'web/assets/fonts/saira-700-latin-ext.woff2' => 25476,
    'web/assets/fonts/saira-700-latin.woff2' => 32888,
    'web/assets/fonts/saira-800-latin-ext.woff2' => 25476,
    'web/assets/fonts/saira-800-latin.woff2' => 32888,
    'web/assets/js/main.js' => 35731,
    'web/assets/logo/reutter-dunkel.webp' => 25596,
    'web/assets/logo/reutter-weiss.svg' => 6709,
    'web/assets/logo/reutter-weiss.webp' => 16076,
    'web/assets/logo/reutter-wortmarke-dunkel.webp' => 17524,
    'web/assets/logo/reutter-wortmarke-weiss.webp' => 11096,
    'web/assets/logo/reutter.svg' => 6709,
    'web/index.php' => 5235,
];

const UPLOADS_ANZAHL = 108;
const UPLOADS_BYTES  = 7446214;

$wurzel = dirname(__DIR__);

/* Zweiter Schritt: die Route wirklich aufrufen und dabei zeigen, was schiefgeht.
   Steht bewusst VOR jeder eigenen Ausgabe — sonst scheitern die header()-Aufrufe
   der Seite und man liest „headers already sent" statt der Ursache.

   Der Shutdown-Handler ist die Rueckfallebene: Hat der Hoster display_errors
   fest abgeschaltet, greift ini_set() nicht, aber error_get_last() weiss beim
   Beenden trotzdem, woran es lag. */
if (isset($_GET['fehler'])) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    register_shutdown_function(static function (): void {
        $letzter = error_get_last();
        if ($letzter === null) {
            return;
        }
        echo "\n<hr><h2>Letzter Fehler</h2><pre>";
        echo htmlspecialchars($letzter['message'] . "\n\n"
            . $letzter['file'] . ', Zeile ' . $letzter['line']);
        echo "</pre>";
    });

    $_SERVER['REQUEST_URI'] = isset($_GET['route']) ? (string) $_GET['route'] : '/leistungen/';
    require __DIR__ . '/index.php';
    exit;
}
$fehlt = [];
$falsch = [];
$ok = 0;

foreach (SOLL as $rel => $groesse) {
    $pfad = $wurzel . '/' . $rel;
    if (!is_file($pfad)) {
        $fehlt[] = $rel;
    } elseif (filesize($pfad) !== $groesse) {
        $falsch[] = [$rel, $groesse, filesize($pfad)];
    } else {
        $ok++;
    }
}

/* uploads/ wird nur gezaehlt — 107 Bilder einzeln aufzulisten hilft niemandem. */
$bilder = 0;
$bytes = 0;
$verz = $wurzel . '/web/uploads';
if (is_dir($verz)) {
    $lauf = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($verz, FilesystemIterator::SKIP_DOTS));
    foreach ($lauf as $datei) {
        if ($datei->isFile()) {
            $bilder++;
            $bytes += $datei->getSize();
        }
    }
}

header('Content-Type: text/html; charset=utf-8');
echo "<!doctype html><meta charset=utf-8><title>Pruefung</title>";
echo "<h1>Pruefung</h1>";
echo "<p>PHP " . PHP_VERSION . " &middot; Upload-Grenze " . ini_get('upload_max_filesize')
   . " / " . ini_get('post_max_size') . "</p>";
echo "<p>data/ beschreibbar: " . (is_writable($wurzel . '/data') ? 'ja' : '<b>NEIN</b>') . "</p>";

if ($fehlt === [] && $falsch === []) {
    echo "<h2>Alle " . count(SOLL) . " Dateien vollstaendig und unveraendert.</h2>";
} else {
    echo "<h2>Gefunden: " . count($fehlt) . " fehlend, " . count($falsch) . " unvollstaendig</h2>";
}

if ($fehlt !== []) {
    echo "<h3>Fehlt ganz</h3><ul>";
    foreach ($fehlt as $f) { echo "<li>" . htmlspecialchars($f) . "</li>"; }
    echo "</ul>";
}

if ($falsch !== []) {
    echo "<h3>Falsche Groesse (abgeschnitten oder alte Fassung)</h3>";
    echo "<table border=1 cellpadding=4><tr><th>Datei</th><th>soll</th><th>ist</th></tr>";
    foreach ($falsch as [$f, $s, $i]) {
        echo "<tr><td>" . htmlspecialchars($f) . "</td><td>{$s}</td><td>{$i}</td></tr>";
    }
    echo "</table>";
}

echo "<h3>Bilder in web/uploads/</h3>";
echo "<p>" . $bilder . " von " . UPLOADS_ANZAHL . " Dateien, "
   . number_format($bytes) . " von " . number_format(UPLOADS_BYTES) . " Bytes</p>";

echo "<hr><p><a href=\"?fehler=1\">Zweiter Schritt: /leistungen/ mit sichtbaren Fehlermeldungen aufrufen</a></p>";
