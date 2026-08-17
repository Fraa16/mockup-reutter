<?php
declare(strict_types=1);

/**
 * Erzeugt die verkleinerten Fassungen aller Bilder in public/uploads/.
 *
 * Beim Hochladen ueber das Panel passiert das automatisch. Dieses Skript ist
 * fuer zwei Faelle da: Bilder, die von Hand in den Ordner gelegt wurden, und
 * das Nachziehen, wenn sich die Groessenliste in app/lib/images.php aendert.
 *
 * Aufruf:  php bin/ableitungen.php
 */

require dirname(__DIR__) . '/app/bootstrap.php';
require APP_ROOT . '/lib/images.php';

$bilder = glob(PUBLIC_ROOT . '/uploads/*.webp') ?: [];
if ($bilder === []) {
    exit("Keine Bilder in public/uploads/ gefunden.\n");
}

$vorher = ordnergroesse(PUBLIC_ROOT . '/uploads/cache');

foreach ($bilder as $pfad) {
    $name = basename($pfad);
    bild_ableitungen_erzeugen($name);
    $info = @getimagesize($pfad);
    $erzeugt = [];
    foreach (BILD_BREITEN as $breite) {
        $ziel = PUBLIC_ROOT . '/uploads/cache/' . pathinfo($name, PATHINFO_FILENAME) . "-{$breite}.webp";
        if (is_file($ziel)) {
            $erzeugt[] = $breite;
        }
    }
    printf("%-46s %5d px  →  %s\n", $name, $info[0] ?? 0, $erzeugt ? implode(', ', $erzeugt) : '—');
}

$nachher = ordnergroesse(PUBLIC_ROOT . '/uploads/cache');
printf("\n%d Bilder. Ableitungen: %s (vorher %s)\n", count($bilder), lesbar($nachher), lesbar($vorher));

function ordnergroesse(string $ordner): int
{
    return array_sum(array_map('filesize', glob($ordner . '/*') ?: []));
}

function lesbar(int $bytes): string
{
    return $bytes > 1048576 ? round($bytes / 1048576, 1) . ' MB' : round($bytes / 1024) . ' KB';
}
