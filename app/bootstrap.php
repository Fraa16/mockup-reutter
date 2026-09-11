<?php
declare(strict_types=1);

/**
 * Startpunkt fuer jeden Request. Wird ausschliesslich von public/index.php
 * eingebunden — app/ und data/ liegen ausserhalb des Webroots und sind damit
 * nicht direkt aufrufbar.
 */

define('APP_ROOT',  __DIR__);
define('BASE_ROOT', dirname(__DIR__));
define('DATA_ROOT', BASE_ROOT . '/data');

/* Wo der oeffentliche Ordner liegt, weiss der Einstiegspunkt am besten — er
   liegt selbst darin. index.php und die Panel-Seiten setzen PUBLIC_ROOT
   deshalb, bevor sie diese Datei einbinden.
 *
   Vorher stand hier fest BASE_ROOT . '/public'. Das ging so lange gut, wie der
   Ordner beim Hochladen auch „public" hiess. Auf dem IONOS-Server heisst er
   „web" — und damit legte bild_annehmen() jedes hochgeladene Foto in einem
   frisch angelegten Ordner „public" NEBEN dem Webbereich ab. Die Datei war da,
   der Browser fand sie nie: 404, obwohl der Eintrag in der Galerie stand.
   Nebenwirkung war stiller: bild_quellen() suchte die verkleinerten Fassungen
   an derselben falschen Stelle, fand keine, und jede Seite lieferte
   Handybesuchern das volle Bild ohne srcset aus.
 *
   Der Rueckfall unten ist fuer Aufrufe von der Kommandozeile. Er sucht den
   Ordner, in dem index.php liegt, statt auf einen Namen zu wetten — je nach
   Hoster heisst er public, web, htdocs oder httpdocs. */
if (!defined('PUBLIC_ROOT')) {
    $oeffentlich = BASE_ROOT . '/public';
    if (!is_file($oeffentlich . '/index.php')) {
        foreach ((array) glob(BASE_ROOT . '/*/index.php') as $treffer) {
            $oeffentlich = dirname((string) $treffer);
            break;
        }
    }
    define('PUBLIC_ROOT', $oeffentlich);
    unset($oeffentlich);
}

// Fehler gehoeren ins Log, nicht auf die Seite. Lokal wird das ueber
// config.local.php wieder aufgedreht.
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

require APP_ROOT . '/lib/content.php';
require APP_ROOT . '/lib/render.php';
// bild() braucht bild_quellen() fuer srcset — deshalb gehoert images.php
// seit dem Umbau in jeden Seitenaufruf, nicht nur ins Panel.
require APP_ROOT . '/lib/images.php';
require APP_ROOT . '/lib/seo.php';

// Lokale Entwicklungseinstellungen, falls vorhanden (nicht im Git).
if (is_file(APP_ROOT . '/config.local.php')) {
    require APP_ROOT . '/config.local.php';
}
