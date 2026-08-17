<?php
declare(strict_types=1);

/**
 * Einstiegspunkt fuer Vercel.
 *
 * Vercel erwartet die Serverless-Funktion unter api/. Hier passiert nichts
 * ausser dem Weiterreichen an den echten Front-Controller — die Website haengt
 * nicht an dieser Datei, und auf dem regulaeren Hosting (IONOS) wird sie gar
 * nicht angefasst. Die Pfadkonstanten kommen aus app/bootstrap.php und
 * rechnen von ihrem eigenen Ort aus, deshalb stimmen sie hier genauso.
 *
 * Was auf Vercel NICHT geht: Das Dateisystem ist schreibgeschuetzt. Der
 * Redaktionsbereich laesst sich ansehen, aber nichts speichern — dafuer
 * braucht es ein Hosting mit beschreibbarem data/ und public/uploads/.
 */

/* Auf IONOS erledigt die .htaccess die Verteilung: sie reicht nur an den
   Front-Controller weiter, was keine echte Datei ist, und /admin/ ist eine.
   Hier landet dagegen jede Adresse, deshalb die Unterscheidung von Hand.

   Geprueft wird gegen eine feste Liste, nicht gegen den Pfad aus der Anfrage —
   sonst waere das hier ein Weg, jede beliebige Datei einzubinden. */
$pfad = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

/* /admin ohne Schraegstrich muss auf /admin/ umgeleitet werden, sonst bleibt
   die Anmeldung haengen: das Sitzungs-Cookie traegt den Pfad /admin/, und ein
   Cookie mit diesem Pfad wird fuer die Adresse /admin nicht mitgeschickt
   (RFC 6265 — /admin/ ist kein Praefix von /admin). Die Folge waere eine neue,
   leere Sitzung bei jedem Aufruf, ein CSRF-Token, das zu nichts passt, und
   „Die Sitzung ist abgelaufen" nach dem Anmelden.

   Auf IONOS erledigt das Apache selbst (mod_dir haengt bei Verzeichnissen den
   Schraegstrich per 301 an). Hier muss es von Hand stehen. */
if ($pfad === '/admin') {
    header('Location: /admin/', true, 301);
    return;
}

$panel = [
    '/admin/'             => '/public/admin/index.php',
    '/admin/index.php'    => '/public/admin/index.php',
    '/admin/edit.php'     => '/public/admin/edit.php',
    '/admin/anfragen.php' => '/public/admin/anfragen.php',
];

if (isset($panel[$pfad])) {
    require dirname(__DIR__) . $panel[$pfad];
    return;
}

/* Browser fragen /favicon.ico von sich aus an. Dafuer die vollstaendige
   Fehlerseite zu rendern, ist Verschwendung — die Seite verweist auf
   assets/favicon.svg. */
if ($pfad === '/favicon.ico') {
    http_response_code(404);
    return;
}

require dirname(__DIR__) . '/public/index.php';
