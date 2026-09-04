<?php
declare(strict_types=1);

/**
 * Foto-Posteingang.
 *
 * Hochgeladene Fotos landen zuerst hier und nicht direkt in der Galerie.
 * Nicht als Rechteschloss, sondern als Arbeitsteilung: Hochladen ist
 * Handyarbeit — unterwegs, mit einer Hand, zwischen zwei Auftraegen.
 * Zuordnen und Beschriften ist Schreibtischarbeit, und eine Bildbeschreibung
 * schreibt niemand auf dem Parkplatz.
 *
 * Die Datei liegt physisch schon in public/uploads/, damit die Vorschau im
 * Panel ohne Sonderweg funktioniert. Sichtbar auf der Website wird sie erst,
 * wenn jemand sie in die Galerie uebernimmt — nur dort steht sie im Inhalt.
 */

const POSTEINGANG_DATEI = 'fotos-posteingang.json';

/**
 * Der Posteingang, neueste zuerst.
 *
 * @return list<array{datei:string,original:string,zeit:int}>
 */
function posteingang_lesen(): array
{
    $pfad = DATA_ROOT . '/' . POSTEINGANG_DATEI;
    if (!is_file($pfad)) {
        return [];
    }

    $roh = json_decode((string) file_get_contents($pfad), true);
    if (!is_array($roh)) {
        return [];
    }

    /* Eintraege ohne Datei fallen raus. Das passiert, wenn jemand per FTP
       aufraeumt — dann soll das Panel nicht auf ein totes Bild zeigen. */
    $liste = array_values(array_filter(
        $roh,
        static fn ($e): bool => is_array($e)
            && isset($e['datei'])
            && is_file(PUBLIC_ROOT . '/uploads/' . $e['datei'])
    ));

    usort($liste, static fn (array $a, array $b): int => ($b['zeit'] ?? 0) <=> ($a['zeit'] ?? 0));

    return $liste;
}

/**
 * @param list<array{datei:string,original:string,zeit:int}> $liste
 */
function posteingang_schreiben(array $liste): void
{
    $pfad = DATA_ROOT . '/' . POSTEINGANG_DATEI;
    $text = json_encode(array_values($liste), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    file_put_contents($pfad, $text . "\n", LOCK_EX);
}

function posteingang_ergaenzen(string $datei, string $original): void
{
    $liste = posteingang_lesen();
    $liste[] = ['datei' => $datei, 'original' => $original, 'zeit' => time()];
    posteingang_schreiben($liste);
}

/**
 * Findet einen Eintrag. Zugleich die Pruefung fuer alles, was von aussen
 * kommt: nur was hier drinsteht, darf geloescht oder verschoben werden.
 * Aus einer Eingabe wird nie ein Pfad gebaut.
 *
 * @return array{datei:string,original:string,zeit:int}|null
 */
function posteingang_eintrag(string $datei): ?array
{
    foreach (posteingang_lesen() as $e) {
        if ($e['datei'] === $datei) {
            return $e;
        }
    }

    return null;
}

function posteingang_entfernen(string $datei): void
{
    posteingang_schreiben(array_filter(
        posteingang_lesen(),
        static fn (array $e): bool => $e['datei'] !== $datei
    ));
}

/**
 * Loescht ein Foto samt seiner verkleinerten Fassungen.
 *
 * Ohne die Ableitungen blieben je Bild bis zu fuenf Dateien im Cache liegen,
 * die niemand mehr findet — und beim naechsten Bild mit demselben Namen waere
 * das alte Bild in den kleinen Groessen wieder da.
 */
function foto_loeschen(string $datei): void
{
    if (posteingang_eintrag($datei) === null) {
        return;
    }

    @unlink(PUBLIC_ROOT . '/uploads/' . $datei);

    $basis = pathinfo($datei, PATHINFO_FILENAME);
    foreach (BILD_BREITEN as $breite) {
        @unlink(PUBLIC_ROOT . "/uploads/cache/{$basis}-{$breite}.webp");
    }

    posteingang_entfernen($datei);
}

/**
 * Haengt ein Foto aus dem Posteingang an das Galerieraster an.
 *
 * Das ist der einzige Weg, eine Liste im CMS wachsen zu lassen — das
 * Bearbeitungsformular zeigt immer nur die vorhandenen Eintraege.
 *
 * @return string|null Fehlermeldung, oder null bei Erfolg
 */
function posteingang_in_galerie(string $datei, string $kategorie, string $alt): ?string
{
    if (posteingang_eintrag($datei) === null) {
        return 'Dieses Foto liegt nicht mehr im Posteingang.';
    }

    $alt = trim($alt);
    if ($alt === '') {
        return 'Ohne Bildbeschreibung geht es nicht — sie steht im Alternativtext und wird vorgelesen.';
    }

    $galerie = content('galerie');
    $bilder  = $galerie['raster']['bilder'] ?? [];

    $bilder[] = [
        'kategorie' => $kategorie,
        'bild'      => $datei,
        'alt'       => $alt,
    ];

    $galerie['raster']['bilder'] = $bilder;
    content_speichern('galerie', $galerie);
    posteingang_entfernen($datei);

    return null;
}

/**
 * Die Kategorien des Galerierasters — aus dem Inhalt gelesen, nicht fest
 * eingetragen. Legt der Betrieb spaeter eine neue an, steht sie hier von
 * selbst zur Auswahl.
 *
 * @return list<string>
 */
function galerie_kategorien(): array
{
    $aus = [];
    foreach (content('galerie')['raster']['bilder'] ?? [] as $b) {
        if (!empty($b['kategorie'])) {
            $aus[$b['kategorie']] = true;
        }
    }
    ksort($aus);

    return array_keys($aus);
}
