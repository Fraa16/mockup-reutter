<?php
declare(strict_types=1);

/**
 * Nimmt genau EIN Foto entgegen und antwortet als JSON.
 *
 * Warum einzeln und nicht alle auf einmal: Zwanzig Handyfotos à vier Megabyte
 * sind achtzig Megabyte in einem POST. Daran scheitert geteiltes Hosting
 * dreifach — post_max_size, max_file_uploads (Standard 20) und
 * max_execution_time, denn GD rechnet je Bild sechs Fassungen. Einzeln
 * geschickt bleibt jede Anfrage klein, der Fortschritt ist sichtbar, und ein
 * Abbruch unterwegs kostet ein Foto statt aller.
 *
 * Ohne JavaScript wird diese Datei nie aufgerufen — fotos.php nimmt dann
 * selbst entgegen, was in einen POST passt.
 */

require dirname(__DIR__, 2) . '/app/bootstrap.php';
require APP_ROOT . '/lib/auth.php';
require APP_ROOT . '/lib/speichern.php';
require APP_ROOT . '/lib/posteingang.php';

header('Content-Type: application/json; charset=utf-8');


/** @param array<string,mixed> $daten */
function antwort(int $status, array $daten): never
{
    http_response_code($status);
    echo json_encode($daten, JSON_UNESCAPED_UNICODE);
    exit;
}

/* auth_verlangen() leitet auf die Anmeldung um — als Antwort auf einen
   Hintergrundaufruf waere das eine HTML-Seite, die das Skript nicht lesen
   kann. Deshalb hier ein sauberer Status. */
if (!auth_angemeldet()) {
    antwort(401, ['fehler' => 'Nicht angemeldet. Bitte die Seite neu laden.']);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    antwort(405, ['fehler' => 'Nur per POST.']);
}

if (upload_verworfen()) {
    antwort(413, ['fehler' => 'Das Foto ist zu groß für den Server (mehr als ' . bild_grenze_text() . ').']);
}

csrf_pruefen();

if (!inhalte_beschreibbar()) {
    antwort(403, ['fehler' => 'Auf dieser Vorschau lässt sich nichts hochladen.']);
}

if (!isset($_FILES['foto'])) {
    antwort(400, ['fehler' => 'Es kam keine Datei an.']);
}

[$name, $meldung] = bild_annehmen($_FILES['foto']);

if ($name === null) {
    antwort(422, ['fehler' => $meldung ?? 'Das Foto konnte nicht angenommen werden.']);
}

posteingang_ergaenzen($name, (string) ($_FILES['foto']['name'] ?? ''));

antwort(200, [
    'datei'  => $name,
    'quelle' => '/uploads/' . $name,
]);
