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
define('PUBLIC_ROOT', BASE_ROOT . '/public');

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
