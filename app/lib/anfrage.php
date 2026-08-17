<?php
declare(strict_types=1);

/**
 * Verarbeitung der Anfrageformulare.
 *
 * Beide Formulare der Website schicken hierher: das ausfuehrliche auf der
 * Kontaktseite und die Kurzanfrage im Fussbereich. Sie unterscheiden sich nur
 * in der Zahl der Felder, nicht im Weg.
 *
 * Zum Spamschutz bewusst kein Captcha — das waere entweder eine
 * Google-Verbindung oder eine Huerde fuer genau die Kunden, die ohnehin schon
 * zoegern. Stattdessen drei Massnahmen, die niemand merkt: ein Feld, das
 * Menschen nicht sehen, eine Mindestzeit zum Ausfuellen und eine Obergrenze
 * pro Absender.
 *
 * Ebenfalls bewusst kein CSRF-Token: das braucht eine Session und damit ein
 * Cookie fuer jeden Besucher — fuer ein Formular, bei dem ein gefaelschter
 * Absender niemandem schadet. Stattdessen wird die Herkunft des Requests
 * geprueft, was ohne Cookie auskommt und zu unserer Aussage passt, dass diese
 * Seite keinen Einwilligungsbanner braucht.
 */

const ANFRAGE_MINDESTZEIT   = 3;      // Sekunden. Schneller tippt kein Mensch.
const ANFRAGE_HOECHSTALTER  = 43200;  // 12 Stunden. Danach ist das Formular alt.
const ANFRAGE_MAX_PRO_IP    = 5;
const ANFRAGE_ZEITFENSTER   = 3600;   // pro Stunde
const ANFRAGE_MAX_FOTOS     = 3;

/**
 * Nimmt eine abgeschickte Anfrage entgegen.
 *
 * @param array<string,mixed> $eingaben  $_POST
 * @param array<string,mixed> $dateien   $_FILES
 * @return array{0:bool,1:array<string,string>,2:array<string,mixed>}
 *         Erfolg, Feldfehler, zurueckzuschreibende Werte
 */
function anfrage_verarbeiten(array $eingaben, array $dateien): array
{
    if (!anfrage_herkunft_stimmt()) {
        return [false, ['allgemein' => 'Die Anfrage kam nicht von dieser Website. Bitte laden Sie die Seite neu.'], []];
    }

    $werte = anfrage_werte_lesen($eingaben);

    // Stille Abweisung: Bots sollen glauben, es habe geklappt, damit sie es
    // nicht mit einer anderen Masche erneut versuchen.
    if (anfrage_ist_bot($eingaben)) {
        return [true, [], $werte];
    }

    if (!anfrage_limit_frei()) {
        return [false, ['allgemein' => 'Es sind gerade sehr viele Anfragen von Ihrem Anschluss eingegangen. Bitte rufen Sie uns an.'], $werte];
    }

    $fehler = anfrage_pruefen($werte);
    if ($fehler !== []) {
        return [false, $fehler, $werte];
    }

    $kennung = date('Y-m-d_His') . '-' . substr(bin2hex(random_bytes(3)), 0, 5);

    [$fotos, $fotoFehler] = anfrage_fotos_annehmen($dateien, $kennung);
    if ($fotoFehler !== []) {
        return [false, $fotoFehler, $werte];
    }

    anfrage_ablegen($kennung, $werte, $fotos);
    anfrage_limit_vermerken();

    // Der Versand darf die Anfrage nicht gefaehrden: sie liegt zu diesem
    // Zeitpunkt bereits sicher auf der Platte. Scheitert die Mail — etwa weil
    // noch keine Zugangsdaten hinterlegt sind — sieht der Betrieb sie trotzdem
    // im Panel, und der Absender bekommt keine Fehlermeldung fuer etwas, das
    // er richtig gemacht hat.
    anfrage_versenden($kennung, $werte, $fotos);

    return [true, [], $werte];
}

/**
 * Zustandslose Herkunftspruefung.
 *
 * Schickt der Browser einen Origin- oder Referer-Kopf, muss er zu dieser Seite
 * passen. Fehlen beide — manche Datenschutz-Erweiterungen entfernen sie —,
 * lassen wir durch: Honeypot, Zeitfalle und Limit greifen weiterhin, und ein
 * pauschales Nein wuerde echte Anfragen kosten.
 */
function anfrage_herkunft_stimmt(): bool
{
    $eigener = $_SERVER['HTTP_HOST'] ?? '';

    foreach (['HTTP_ORIGIN', 'HTTP_REFERER'] as $kopf) {
        $wert = $_SERVER[$kopf] ?? '';
        if ($wert === '') {
            continue;
        }
        return strcasecmp((string) parse_url($wert, PHP_URL_HOST), $eigener) === 0
            || strcasecmp((string) parse_url($wert, PHP_URL_HOST), (string) parse_url('//' . $eigener, PHP_URL_HOST)) === 0;
    }

    return true;
}

/** @param array<string,mixed> $eingaben */
function anfrage_ist_bot(array $eingaben): bool
{
    // Das Honigtopf-Feld ist fuer Menschen unsichtbar. Wer es ausfuellt, liest
    // das Formular nicht, sondern das HTML.
    if (trim((string) ($eingaben['firma_zusatz'] ?? '')) !== '') {
        return true;
    }

    $gestartet = (int) ($eingaben['gestartet'] ?? 0);
    if ($gestartet <= 0) {
        return true;
    }

    $gebraucht = time() - $gestartet;

    return $gebraucht < ANFRAGE_MINDESTZEIT || $gebraucht > ANFRAGE_HOECHSTALTER;
}

/**
 * Liest die Felder beider Formulare. Was nicht geschickt wurde, bleibt leer —
 * die Kurzanfrage hat weniger Felder als das ausfuehrliche Formular.
 *
 * @param array<string,mixed> $eingaben
 * @return array<string,mixed>
 */
function anfrage_werte_lesen(array $eingaben): array
{
    $text = static fn (string $feld, int $max = 200): string
        => mb_substr(trim((string) ($eingaben[$feld] ?? '')), 0, $max);

    $leistungen = $eingaben['leistungen'] ?? [];
    if (!is_array($leistungen)) {
        $leistungen = [];
    }
    // Die Kurzanfrage schickt ein einzelnes Auswahlfeld statt der Chips.
    $einzel = $text('leistung');
    if ($einzel !== '' && $leistungen === []) {
        $leistungen = [$einzel];
    }

    return [
        'name'         => $text('name', 120),
        'telefon'      => $text('telefon', 60),
        'email'        => $text('email', 180),
        'marke'        => $text('marke', 60),
        'modell'       => $text('modell', 80),
        'baujahr'      => $text('baujahr', 10),
        'lackfarbe'    => $text('lackfarbe', 60),
        'fahrzeug'     => $text('fahrzeug', 120),
        'ort'          => $text('ort', 60),
        'beschreibung' => $text('beschreibung', 4000),
        'leistungen'   => array_slice(array_map(
            static fn ($l): string => mb_substr(trim((string) $l), 0, 60),
            $leistungen
        ), 0, 10),
        'datenschutz'  => isset($eingaben['datenschutz']),
        'herkunft'     => $text('herkunft', 40) !== '' ? $text('herkunft', 40) : 'kontaktformular',
    ];
}

/**
 * @param array<string,mixed> $werte
 * @return array<string,string>
 */
function anfrage_pruefen(array $werte): array
{
    $fehler = [];

    if ($werte['name'] === '') {
        $fehler['name'] = 'Bitte tragen Sie Ihren Namen ein.';
    }

    if ($werte['telefon'] === '' && $werte['email'] === '') {
        $fehler['telefon'] = 'Wir brauchen eine Telefonnummer oder eine E-Mail-Adresse — sonst können wir nicht antworten.';
    }

    if ($werte['email'] !== '' && !filter_var($werte['email'], FILTER_VALIDATE_EMAIL)) {
        $fehler['email'] = 'Diese E-Mail-Adresse sieht nicht richtig aus.';
    }

    if ($werte['telefon'] !== '' && !preg_match('/[0-9]{5,}/', preg_replace('/[^0-9]/', '', $werte['telefon']) ?? '')) {
        $fehler['telefon'] = 'Diese Telefonnummer sieht zu kurz aus.';
    }

    if ($werte['datenschutz'] !== true) {
        $fehler['datenschutz'] = 'Ohne die Bestätigung zum Datenschutz dürfen wir die Anfrage nicht bearbeiten.';
    }

    return $fehler;
}

/**
 * Nimmt die angehaengten Fotos an. Sie landen ausserhalb des Webroots — es
 * sind Fotos fremder Fahrzeuge, die niemand ueber eine geratene URL abrufen
 * koennen soll.
 *
 * @param array<string,mixed> $dateien
 * @return array{0:list<string>,1:array<string,string>}
 */
function anfrage_fotos_annehmen(array $dateien, string $kennung): array
{
    $feld = $dateien['fotos'] ?? null;
    if (!is_array($feld) || !isset($feld['name']) || !is_array($feld['name'])) {
        return [[], []];
    }

    $fotos = [];
    $anzahl = min(count($feld['name']), ANFRAGE_MAX_FOTOS);

    for ($i = 0; $i < $anzahl; $i++) {
        $einzel = [
            'name'     => $feld['name'][$i]     ?? '',
            'tmp_name' => $feld['tmp_name'][$i] ?? '',
            'error'    => $feld['error'][$i]    ?? UPLOAD_ERR_NO_FILE,
            'size'     => $feld['size'][$i]     ?? 0,
        ];

        if ($einzel['error'] === UPLOAD_ERR_NO_FILE) {
            continue;
        }

        $dateiname = 'foto-' . ($i + 1) . '.webp';
        $ziel = DATA_ROOT . '/anfragen/' . $kennung . '/' . $dateiname;

        [$ok, $meldung] = bild_umwandeln($einzel, $ziel);
        if (!$ok && $meldung !== null) {
            return [[], ['fotos' => 'Foto ' . ($i + 1) . ': ' . $meldung]];
        }
        if ($ok) {
            $fotos[] = $dateiname;
        }
    }

    return [$fotos, []];
}

/**
 * Legt die Anfrage als JSON ab. Das passiert vor dem Mailversand und
 * unabhaengig davon — eine Anfrage, die im Netz haengen bleibt, ist ein
 * verlorener Auftrag.
 *
 * @param array<string,mixed> $werte
 * @param list<string>        $fotos
 */
function anfrage_ablegen(string $kennung, array $werte, array $fotos): void
{
    $ordner = DATA_ROOT . '/anfragen/' . $kennung;
    if (!is_dir($ordner) && !mkdir($ordner, 0775, true) && !is_dir($ordner)) {
        error_log('Anfrage ' . $kennung . ' konnte nicht abgelegt werden: Ordner nicht anlegbar.');
        return;
    }

    $satz = $werte + [
        'kennung'   => $kennung,
        'zeitpunkt' => date('c'),
        'fotos'     => $fotos,
        'gelesen'   => false,
    ];

    $json = json_encode($satz, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        error_log('Anfrage ' . $kennung . ': JSON konnte nicht erzeugt werden.');
        return;
    }

    $ziel = $ordner . '/anfrage.json';
    $temp = $ziel . '.tmp';
    if (file_put_contents($temp, $json . "\n", LOCK_EX) === false) {
        error_log('Anfrage ' . $kennung . ' konnte nicht geschrieben werden.');
        return;
    }
    rename($temp, $ziel);
}

/** Zaehlt Anfragen je Absender, damit ein Skript nicht das Postfach flutet. */
function anfrage_limit_frei(): bool
{
    $eintraege = anfrage_limit_lesen();
    $ip = anfrage_ip();

    return count(array_filter(
        $eintraege[$ip] ?? [],
        static fn (int $t): bool => $t > time() - ANFRAGE_ZEITFENSTER
    )) < ANFRAGE_MAX_PRO_IP;
}

function anfrage_limit_vermerken(): void
{
    $eintraege = anfrage_limit_lesen();
    $ip = anfrage_ip();

    $eintraege[$ip][] = time();

    // Beim Schreiben gleich aufraeumen, sonst waechst die Datei unbegrenzt.
    foreach ($eintraege as $schluessel => $zeiten) {
        $frisch = array_values(array_filter($zeiten, static fn (int $t): bool => $t > time() - ANFRAGE_ZEITFENSTER));
        if ($frisch === []) {
            unset($eintraege[$schluessel]);
        } else {
            $eintraege[$schluessel] = $frisch;
        }
    }

    @file_put_contents(DATA_ROOT . '/.anfrage-limit.json', json_encode($eintraege), LOCK_EX);
}

/** @return array<string,list<int>> */
function anfrage_limit_lesen(): array
{
    $datei = DATA_ROOT . '/.anfrage-limit.json';
    if (!is_file($datei)) {
        return [];
    }

    $daten = json_decode((string) file_get_contents($datei), true);

    return is_array($daten) ? $daten : [];
}

/**
 * Der Absender, nur zum Zaehlen. Hinter einem Reverse Proxy steht die echte
 * Adresse im weitergereichten Kopf.
 */
function anfrage_ip(): string
{
    $weitergereicht = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
    if ($weitergereicht !== '') {
        $erste = trim(explode(',', $weitergereicht)[0]);
        if (filter_var($erste, FILTER_VALIDATE_IP)) {
            return $erste;
        }
    }

    return (string) ($_SERVER['REMOTE_ADDR'] ?? 'unbekannt');
}

/**
 * Liest die abgelegten Anfragen fuer das Panel, neueste zuerst.
 *
 * @return list<array<string,mixed>>
 */
function anfragen_lesen(int $hoechstens = 100): array
{
    $ordner = DATA_ROOT . '/anfragen';
    if (!is_dir($ordner)) {
        return [];
    }

    $gefunden = glob($ordner . '/*/anfrage.json') ?: [];
    rsort($gefunden);

    $liste = [];
    foreach (array_slice($gefunden, 0, $hoechstens) as $datei) {
        $satz = json_decode((string) file_get_contents($datei), true);
        if (is_array($satz)) {
            $liste[] = $satz;
        }
    }

    return $liste;
}

/**
 * Pruefung der Kennung.
 *
 * Die Kennung kommt aus der URL und wird zu einem Ordnernamen. Deshalb wird
 * sie gegen die Form geprueft, in der wir sie selbst vergeben — nicht nur
 * gegen "enthaelt keine Punkte".
 */
function anfrage_kennung_gueltig(string $kennung): bool
{
    return (bool) preg_match('/^\d{4}-\d{2}-\d{2}_\d{6}-[0-9a-f]{5}$/', $kennung);
}

/**
 * Liest eine einzelne Anfrage.
 *
 * @return array<string,mixed>|null
 */
function anfrage_lesen(string $kennung): ?array
{
    if (!anfrage_kennung_gueltig($kennung)) {
        return null;
    }

    $datei = DATA_ROOT . '/anfragen/' . $kennung . '/anfrage.json';
    if (!is_file($datei)) {
        return null;
    }

    $satz = json_decode((string) file_get_contents($datei), true);

    return is_array($satz) ? $satz : null;
}

/**
 * Setzt das Gelesen-Kennzeichen. Scheitert lautlos, wenn die Ablage nicht
 * beschreibbar ist — auf einer Vorschau ohne Schreibrechte soll das Ansehen
 * einer Anfrage keinen Fehler werfen.
 */
function anfrage_gelesen_setzen(string $kennung, bool $gelesen = true): void
{
    $satz = anfrage_lesen($kennung);
    if ($satz === null || ($satz['gelesen'] ?? false) === $gelesen) {
        return;
    }

    $satz['gelesen'] = $gelesen;
    $json = json_encode($satz, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        return;
    }

    $ziel = DATA_ROOT . '/anfragen/' . $kennung . '/anfrage.json';
    $temp = $ziel . '.tmp';
    if (@file_put_contents($temp, $json . "\n", LOCK_EX) !== false) {
        @rename($temp, $ziel);
    }
}

/** Loescht eine Anfrage samt Fotos. */
function anfrage_loeschen(string $kennung): bool
{
    if (!anfrage_kennung_gueltig($kennung)) {
        return false;
    }

    $ordner = DATA_ROOT . '/anfragen/' . $kennung;
    if (!is_dir($ordner)) {
        return false;
    }

    // Eine Ebene reicht: in dem Ordner liegen nur die JSON-Datei und Fotos.
    foreach (glob($ordner . '/*') ?: [] as $datei) {
        if (is_file($datei)) {
            @unlink($datei);
        }
    }

    return @rmdir($ordner);
}

/** Liefert den Pfad zu einem Anfragefoto — oder null, wenn es das nicht gibt. */
function anfrage_fotopfad(string $kennung, string $dateiname): ?string
{
    if (!anfrage_kennung_gueltig($kennung) || !preg_match('/^foto-[1-9]\.webp$/', $dateiname)) {
        return null;
    }

    $pfad = DATA_ROOT . '/anfragen/' . $kennung . '/' . $dateiname;

    return is_file($pfad) ? $pfad : null;
}

/** Zaehlt die noch nicht geoeffneten Anfragen fuer die Kachel im Panel. */
function anfragen_ungelesen(): int
{
    return count(array_filter(
        anfragen_lesen(500),
        static fn (array $a): bool => ($a['gelesen'] ?? false) !== true
    ));
}
