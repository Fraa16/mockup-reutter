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

$panel = [
    '/admin'              => '/public/admin/index.php',
    '/admin/'             => '/public/admin/index.php',
    '/admin/index.php'    => '/public/admin/index.php',
    '/admin/edit.php'     => '/public/admin/edit.php',
    '/admin/anfragen.php' => '/public/admin/anfragen.php',
];

if (isset($panel[$pfad])) {
    require dirname(__DIR__) . $panel[$pfad];
    return;
}

require dirname(__DIR__) . '/public/index.php';
