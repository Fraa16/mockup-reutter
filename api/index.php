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

require dirname(__DIR__) . '/public/index.php';
