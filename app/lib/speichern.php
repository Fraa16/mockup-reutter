<?php
declare(strict_types=1);

/**
 * Schreibzugriff auf die Inhaltsdateien.
 *
 * Vor jedem Speichern wandert die bisherige Fassung nach data/backups/ —
 * damit ist jede Aenderung des Kunden umkehrbar, ohne dass er ein Backup
 * anfordern muesste.
 */

/**
 * Setzt einen verschachtelten Wert per Punktnotation.
 *
 * @param array<string,mixed> $daten
 */
function setze(array &$daten, string $pfad, mixed $wert): void
{
    $teile = explode('.', $pfad);
    $zeiger = &$daten;

    foreach ($teile as $i => $teil) {
        if ($i === count($teile) - 1) {
            $zeiger[$teil] = $wert;
            return;
        }
        if (!isset($zeiger[$teil]) || !is_array($zeiger[$teil])) {
            $zeiger[$teil] = [];
        }
        $zeiger = &$zeiger[$teil];
    }
}

/**
 * Schreibt eine Inhaltsdatei. Erst in eine temporaere Datei, dann umbenennen —
 * so liegt nie eine halb geschriebene Datei da, wenn PHP mittendrin abbricht.
 *
 * @param array<string,mixed> $daten
 */
function content_speichern(string $name, array $daten): void
{
    if (!preg_match('/^[a-z0-9-]+$/', $name)) {
        throw new InvalidArgumentException("Ungueltiger Inhaltsname: {$name}");
    }

    $ziel = DATA_ROOT . '/content/' . $name . '.json';

    if (is_file($ziel)) {
        vorversion_sichern($name, $ziel);
    }

    $json = json_encode(
        $daten,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
    );

    $temp = $ziel . '.tmp';
    if (file_put_contents($temp, $json . "\n", LOCK_EX) === false) {
        throw new RuntimeException("Konnte {$name}.json nicht schreiben. Schreibrechte auf data/content/ pruefen.");
    }
    rename($temp, $ziel);
}

/**
 * Legt die bisherige Fassung als Zeitstempel-Kopie ab und haelt die letzten 30.
 */
function vorversion_sichern(string $name, string $quelle): void
{
    $ordner = DATA_ROOT . '/backups';
    if (!is_dir($ordner)) {
        mkdir($ordner, 0775, true);
    }

    copy($quelle, sprintf('%s/%s_%s.json', $ordner, $name, date('Y-m-d_His')));

    $vorhanden = glob($ordner . '/' . $name . '_*.json') ?: [];
    if (count($vorhanden) > 30) {
        sort($vorhanden);
        foreach (array_slice($vorhanden, 0, count($vorhanden) - 30) as $alt) {
            unlink($alt);
        }
    }
}

/**
 * Uebernimmt die abgeschickten Formularwerte anhand des Schemas.
 *
 * Wichtig: Es wird ausschliesslich ueber die Felder des Schemas iteriert, nie
 * ueber $_POST. Was nicht im Schema steht, kann damit auch nicht in die
 * Inhaltsdatei gelangen — egal was jemand ins Formular schmuggelt.
 *
 * @param array<string,mixed> $schema
 * @param array<string,mixed> $bestand
 * @param array<string,mixed> $eingaben
 * @return array{0:array<string,mixed>,1:array<string,string>} Daten und Fehler
 */
function eingaben_uebernehmen(array $schema, array $bestand, array $eingaben): array
{
    $fehler = [];

    foreach ($schema['gruppen'] as $gruppe) {
        foreach ($gruppe['felder'] as $feld) {
            if ($feld['typ'] === 'liste') {
                [$bestand, $fehler] = liste_uebernehmen($feld, $bestand, $eingaben, $fehler);
                continue;
            }

            $schluessel = feld_schluessel($feld['pfad']);
            if (!array_key_exists($schluessel, $eingaben)) {
                continue;
            }

            [$wert, $meldung] = wert_pruefen($feld, $eingaben[$schluessel]);
            if ($meldung !== null) {
                $fehler[$schluessel] = $feld['label'] . ': ' . $meldung;
                continue;
            }
            setze($bestand, $feld['pfad'], $wert);
        }
    }

    return [$bestand, $fehler];
}

/**
 * @param array<string,mixed> $feld
 * @param array<string,mixed> $bestand
 * @param array<string,mixed> $eingaben
 * @param array<string,string> $fehler
 * @return array{0:array<string,mixed>,1:array<string,string>}
 */
function liste_uebernehmen(array $feld, array $bestand, array $eingaben, array $fehler): array
{
    $basis = feld_schluessel($feld['pfad']);
    $zeilen = $eingaben[$basis] ?? null;

    if (!is_array($zeilen)) {
        return [$bestand, $fehler];
    }

    // Bestehende Eintraege behalten, damit Felder, die nicht im Panel stehen
    // (Slugs, interne Notizen), beim Speichern nicht verloren gehen.
    $alt = get($bestand, $feld['pfad'], []);
    $neu = [];

    foreach (array_values($zeilen) as $i => $zeile) {
        $eintrag = is_array($alt[$i] ?? null) ? $alt[$i] : [];

        foreach ($feld['subfelder'] as $sub) {
            $sk = feld_schluessel($sub['pfad']);
            if (!array_key_exists($sk, $zeile)) {
                continue;
            }
            [$wert, $meldung] = wert_pruefen($sub, $zeile[$sk]);
            if ($meldung !== null) {
                $fehler["{$basis}[{$i}][{$sk}]"] = $feld['label'] . ' ' . ($i + 1) . ' — ' . $sub['label'] . ': ' . $meldung;
                continue;
            }
            setze($eintrag, $sub['pfad'], $wert);
        }
        $neu[] = $eintrag;
    }

    setze($bestand, $feld['pfad'], $neu);

    return [$bestand, $fehler];
}

/**
 * @param array<string,mixed> $feld
 * @return array{0:mixed,1:string|null} Wert und, falls ungueltig, die Meldung
 */
function wert_pruefen(array $feld, mixed $roh): array
{
    $wert = is_string($roh) ? trim($roh) : $roh;

    switch ($feld['typ']) {
        case 'zahl':
            if ($wert === '') {
                return [0, null];
            }
            if (!is_numeric($wert)) {
                return [null, 'Bitte eine Zahl eintragen.'];
            }
            return [str_contains((string) $wert, '.') ? (float) $wert : (int) $wert, null];

        case 'bild':
            // Nur Dateinamen, kein Pfad — verhindert, dass jemand ueber das
            // Formular auf beliebige Dateien zeigt.
            if ($wert !== '' && !preg_match('/^[A-Za-z0-9._-]+$/', (string) $wert)) {
                return [null, 'Ungültiger Dateiname.'];
            }
            return [$wert, null];

        case 'text':
            if (mb_strlen((string) $wert) > 500) {
                return [null, 'Höchstens 500 Zeichen.'];
            }
            return [$wert, null];

        case 'mehrzeilig':
            if (mb_strlen((string) $wert) > 5000) {
                return [null, 'Höchstens 5000 Zeichen.'];
            }
            return [$wert, null];

        default:
            return [$wert, null];
    }
}

/** Aus 'hero.bild_alt' wird der Formularname 'hero__bild_alt'. */
function feld_schluessel(string $pfad): string
{
    return str_replace('.', '__', $pfad);
}
