<?php
declare(strict_types=1);

/**
 * Bild-Upload und Ableitungen.
 *
 * Hochgeladene Dateien werden grundsaetzlich neu kodiert, nie durchgereicht.
 * Das kostet einen Moment Rechenzeit und erledigt dafuer drei Dinge auf einmal:
 * eingebettete Nutzlasten in scheinbaren Bildern sind danach weg, GPS-Daten aus
 * Handyfotos ebenfalls, und das Ergebnis ist ein sauberes WebP.
 */

/**
 * Die 400er-Stufe ist fuer das Bildraster der Galerie: dort stehen auf dem
 * Handy zwei Kacheln nebeneinander, jede rund 178 px breit. Selbst auf einem
 * Bildschirm mit doppelter Aufloesung reichen 400 px — die naechstgroessere
 * Fassung waere dreimal so schwer, ohne dass man etwas davon sieht.
 */
const BILD_BREITEN     = [400, 640, 1024, 1440, 1920];
const BILD_MAX_BYTES   = 12 * 1024 * 1024;
const BILD_MAX_KANTE   = 4000;
const BILD_QUALITAET   = 82;

/**
 * Was wirklich durchgeht — nicht was wir uns wuenschen.
 *
 * BILD_MAX_BYTES ist unsere Obergrenze. Der Server hat aber eigene, und die
 * sind oft kleiner: upload_max_filesize steht in vielen Standardkonfigurationen
 * auf 2 MB. Wird post_max_size ueberschritten, verwirft PHP die Anfrage
 * komplett — $_POST und $_FILES sind dann leer, und ohne diese Zahl koennte
 * die Seite dem Benutzer nicht sagen, woran es lag.
 */
function bild_grenze_bytes(): int
{
    $inBytes = static function (string $wert): int {
        $wert = trim($wert);
        if ($wert === '') {
            return PHP_INT_MAX;
        }
        $zahl = (int) $wert;
        return match (strtolower(substr($wert, -1))) {
            'g'     => $zahl * 1024 * 1024 * 1024,
            'm'     => $zahl * 1024 * 1024,
            'k'     => $zahl * 1024,
            default => $zahl,
        };
    };

    return min(
        BILD_MAX_BYTES,
        $inBytes((string) ini_get('upload_max_filesize')),
        $inBytes((string) ini_get('post_max_size'))
    );
}

/** Die Grenze als lesbare Angabe, z. B. "2 MB". */
function bild_grenze_text(): string
{
    return round(bild_grenze_bytes() / 1024 / 1024, 1) . ' MB';
}

/**
 * Erlaubte Typen. Geprueft wird der tatsaechliche Inhalt, nicht die Endung
 * und nicht der vom Browser gemeldete MIME-Typ — beide sind faelschbar.
 */
const BILD_TYPEN = [
    IMAGETYPE_JPEG => 'imagecreatefromjpeg',
    IMAGETYPE_PNG  => 'imagecreatefrompng',
    IMAGETYPE_WEBP => 'imagecreatefromwebp',
];

/**
 * Ist die Anfrage an post_max_size gescheitert?
 *
 * Dann verwirft PHP den ganzen Rumpf: $_POST und $_FILES sind leer, obwohl
 * der Browser Daten geschickt hat. Ohne diese Pruefung liefe es in die
 * CSRF-Kontrolle, die mangels Token abbricht — und der Benutzer laese
 * "Sitzung abgelaufen", waehrend in Wahrheit sein Foto zu gross war.
 */
function upload_verworfen(): bool
{
    return $_SERVER['REQUEST_METHOD'] === 'POST'
        && $_POST === [] && $_FILES === []
        && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0;
}

/**
 * Nimmt eine hochgeladene Datei an und legt sie als WebP in public/uploads/ ab.
 *
 * @param array{name:string,type:string,tmp_name:string,error:int,size:int} $datei
 * @return array{0:string|null,1:string|null} Dateiname und, im Fehlerfall, die Meldung
 */
function bild_annehmen(array $datei, string $wunschname = ''): array
{
    $name = bild_dateiname($wunschname !== '' ? $wunschname : ($datei['name'] ?? 'bild'));

    [$ok, $meldung] = bild_umwandeln($datei, PUBLIC_ROOT . '/uploads/' . $name);
    if (!$ok) {
        return [null, $meldung];
    }

    bild_ableitungen_erzeugen($name);

    return [$name, null];
}

/**
 * Prueft eine hochgeladene Datei und schreibt sie als WebP an einen frei
 * waehlbaren Ort.
 *
 * Steckt getrennt von bild_annehmen(), weil Anfragefotos woanders hingehoeren
 * als Website-Bilder: sie liegen ausserhalb des Webroots und brauchen keine
 * Groessen fuer srcset. Die Pruefung und das Neukodieren sind dagegen fuer
 * beide dieselben und sollen es bleiben.
 *
 * @param array{name?:string,type?:string,tmp_name?:string,error?:int,size?:int} $datei
 * @return array{0:bool,1:string|null} Erfolg und, im Fehlerfall, die Meldung
 */
function bild_umwandeln(array $datei, string $zielpfad): array
{
    if (($datei['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return [false, null];
    }

    if ($datei['error'] !== UPLOAD_ERR_OK) {
        return [false, match ($datei['error']) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Die Datei ist größer als ' . bild_grenze_text() . '.',
            UPLOAD_ERR_PARTIAL                        => 'Die Übertragung wurde abgebrochen.',
            default                                   => 'Die Datei konnte nicht übertragen werden.',
        }];
    }

    // Muss ueber den PHP-Upload gekommen sein, nicht irgendein Pfad im System.
    if (!is_uploaded_file($datei['tmp_name'])) {
        return [false, 'Ungültiger Upload.'];
    }

    if ($datei['size'] > bild_grenze_bytes()) {
        return [false, 'Die Datei ist größer als ' . bild_grenze_text() . '.'];
    }

    $info = @getimagesize($datei['tmp_name']);
    if ($info === false || !isset(BILD_TYPEN[$info[2]])) {
        return [false, 'Das ist kein gültiges JPG-, PNG- oder WebP-Bild.'];
    }

    [$breite, $hoehe] = $info;
    if ($breite > BILD_MAX_KANTE * 2 || $hoehe > BILD_MAX_KANTE * 2) {
        return [false, 'Das Bild ist ungewöhnlich groß. Bitte auf maximal 8000 Pixel Kantenlänge verkleinern.'];
    }

    $lader = BILD_TYPEN[$info[2]];
    $quelle = @$lader($datei['tmp_name']);
    if (!$quelle instanceof GdImage) {
        return [false, 'Das Bild konnte nicht gelesen werden.'];
    }

    // Transparenz erhalten, sonst werden freigestellte PNGs schwarz.
    imagepalettetotruecolor($quelle);
    imagealphablending($quelle, true);
    imagesavealpha($quelle, true);

    if ($breite > BILD_MAX_KANTE || $hoehe > BILD_MAX_KANTE) {
        $quelle = bild_skalieren($quelle, min(BILD_MAX_KANTE, $breite));
    }

    $ordner = dirname($zielpfad);
    if (!is_dir($ordner) && !mkdir($ordner, 0775, true) && !is_dir($ordner)) {
        imagedestroy($quelle);
        return [false, 'Der Zielordner konnte nicht angelegt werden.'];
    }

    if (!imagewebp($quelle, $zielpfad, BILD_QUALITAET)) {
        imagedestroy($quelle);
        return [false, 'Das Bild konnte nicht gespeichert werden. Schreibrechte auf ' . $ordner . ' prüfen.'];
    }
    imagedestroy($quelle);

    return [true, null];
}

/**
 * Erzeugt die Groessen fuer srcset. Laeuft beim Upload einmal durch, damit im
 * Seitenaufruf nichts gerechnet werden muss.
 */
function bild_ableitungen_erzeugen(string $name): void
{
    $quellpfad = PUBLIC_ROOT . '/uploads/' . $name;
    if (!is_file($quellpfad)) {
        return;
    }

    $ordner = PUBLIC_ROOT . '/uploads/cache';
    if (!is_dir($ordner)) {
        mkdir($ordner, 0775, true);
    }

    $info = @getimagesize($quellpfad);
    if ($info === false) {
        return;
    }
    $originalBreite = $info[0];
    $basis = pathinfo($name, PATHINFO_FILENAME);

    foreach (BILD_BREITEN as $breite) {
        // Nie hochrechnen — das kostet Bytes und bringt keine Schaerfe.
        if ($breite >= $originalBreite) {
            continue;
        }
        $zielpfad = "{$ordner}/{$basis}-{$breite}.webp";
        if (is_file($zielpfad) && filemtime($zielpfad) >= filemtime($quellpfad)) {
            continue;
        }

        $quelle = @imagecreatefromwebp($quellpfad);
        if (!$quelle instanceof GdImage) {
            return;
        }
        $klein = bild_skalieren($quelle, $breite);
        imagewebp($klein, $zielpfad, BILD_QUALITAET);
        imagedestroy($quelle);
        imagedestroy($klein);
    }
}

function bild_skalieren(GdImage $quelle, int $zielBreite): GdImage
{
    $b = imagesx($quelle);
    $h = imagesy($quelle);
    $zielHoehe = (int) round($h * ($zielBreite / $b));

    $ziel = imagecreatetruecolor($zielBreite, $zielHoehe);
    imagealphablending($ziel, false);
    imagesavealpha($ziel, true);
    imagecopyresampled($ziel, $quelle, 0, 0, 0, 0, $zielBreite, $zielHoehe, $b, $h);

    return $ziel;
}

/**
 * Baut einen sicheren, sprechenden Dateinamen. Der Originalname wird nur als
 * Vorlage benutzt, nie uebernommen.
 */
function bild_dateiname(string $vorlage): string
{
    $basis = pathinfo($vorlage, PATHINFO_FILENAME);
    $basis = strtr($basis, ['ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue', 'Ä' => 'ae', 'Ö' => 'oe', 'Ü' => 'ue', 'ß' => 'ss']);
    $basis = strtolower((string) preg_replace('/[^A-Za-z0-9]+/', '-', $basis));
    $basis = trim($basis, '-');
    if ($basis === '') {
        $basis = 'bild';
    }
    $basis = mb_substr($basis, 0, 40);

    // Kurzes Suffix, damit ein erneuter Upload gleichen Namens die alte Datei
    // nicht ueberschreibt, solange sie noch irgendwo eingebunden ist.
    return $basis . '-' . substr(bin2hex(random_bytes(3)), 0, 5) . '.webp';
}

/**
 * Liefert srcset und Abmessungen fuer ein hochgeladenes Bild.
 *
 * @return array{srcset:string,breite:int,hoehe:int}
 */
function bild_quellen(string $name): array
{
    $pfad = PUBLIC_ROOT . '/uploads/' . $name;
    $info = is_file($pfad) ? @getimagesize($pfad) : false;

    if ($info === false) {
        return ['srcset' => '', 'breite' => 0, 'hoehe' => 0];
    }

    $basis = pathinfo($name, PATHINFO_FILENAME);
    $teile = [];

    foreach (BILD_BREITEN as $breite) {
        if ($breite >= $info[0]) {
            continue;
        }
        if (is_file(PUBLIC_ROOT . "/uploads/cache/{$basis}-{$breite}.webp")) {
            $teile[] = "/uploads/cache/{$basis}-{$breite}.webp {$breite}w";
        }
    }
    $teile[] = '/uploads/' . $name . ' ' . $info[0] . 'w';

    return ['srcset' => implode(', ', $teile), 'breite' => $info[0], 'hoehe' => $info[1]];
}
