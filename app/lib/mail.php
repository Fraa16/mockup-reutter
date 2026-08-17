<?php
declare(strict_types=1);

/**
 * Mailversand ueber den SMTP-Server des Hosters.
 *
 * Bewusst nicht PHP's mail(): das verschickt ueber den lokalen Sendmail, und
 * solche Mails landen bei einer Domain mit SPF-Eintrag regelmaessig im Spam.
 * Ueber den echten Postausgang des Hosters greift dessen SPF, und die Anfrage
 * kommt an.
 *
 * Ebenso bewusst keine Bibliothek: dafuer braeuchte es Composer auf dem
 * Webspace. Was hier gebraucht wird — anmelden, eine Mail mit Anhaengen
 * abliefern — sind rund hundert Zeilen.
 *
 * Zugangsdaten stehen in app/config/zugangsdaten.php. Die Datei liegt nicht im
 * Git. Fehlt sie, wird nichts verschickt und nichts kaputt gemacht: die
 * Anfrage liegt zu diesem Zeitpunkt bereits abgelegt vor.
 */

/** @return array<string,mixed>|null */
function mail_konfiguration(): ?array
{
    static $konfiguration = false;

    if ($konfiguration === false) {
        $datei = APP_ROOT . '/config/zugangsdaten.php';
        $daten = is_file($datei) ? require $datei : null;
        $konfiguration = (is_array($daten) && !empty($daten['smtp']['host'])) ? $daten['smtp'] : null;
    }

    return $konfiguration;
}

function mail_bereit(): bool
{
    return mail_konfiguration() !== null;
}

/**
 * Verschickt die Anfrage an den Betrieb und, wenn eine Adresse vorliegt, eine
 * Eingangsbestaetigung an den Absender.
 *
 * @param array<string,mixed> $werte
 * @param list<string>        $fotos
 */
function anfrage_versenden(string $kennung, array $werte, array $fotos): bool
{
    if (!mail_bereit()) {
        error_log('Anfrage ' . $kennung . ' abgelegt, aber nicht verschickt: keine SMTP-Zugangsdaten hinterlegt.');
        return false;
    }

    $s = site();
    $an = (string) get($s, 'kontakt.email', '');
    if ($an === '') {
        error_log('Anfrage ' . $kennung . ': keine Empfaengeradresse in site.json.');
        return false;
    }

    $anhaenge = [];
    foreach ($fotos as $foto) {
        $pfad = DATA_ROOT . '/anfragen/' . $kennung . '/' . $foto;
        if (is_file($pfad)) {
            $anhaenge[] = ['name' => $foto, 'typ' => 'image/webp', 'inhalt' => (string) file_get_contents($pfad)];
        }
    }

    $betreff = 'Anfrage über die Website'
        . ($werte['leistungen'] !== [] ? ': ' . implode(', ', $werte['leistungen']) : '')
        . ' — ' . $werte['name'];

    $erfolg = mail_senden(
        $an,
        $betreff,
        anfrage_als_text($kennung, $werte, $fotos),
        // Antworten geht direkt an den Kunden, nicht an das Absenderpostfach.
        $werte['email'] !== '' ? $werte['email'] : null,
        $anhaenge
    );

    if ($werte['email'] !== '') {
        mail_senden(
            $werte['email'],
            'Ihre Anfrage bei ' . get($s, 'firma.name', 'uns'),
            bestaetigung_als_text($werte),
            $an
        );
    }

    return $erfolg;
}

/**
 * @param array<string,mixed> $werte
 * @param list<string>        $fotos
 */
function anfrage_als_text(string $kennung, array $werte, array $fotos): string
{
    $zeilen = ['Neue Anfrage über die Website', str_repeat('=', 40), ''];

    $feld = static function (string $beschriftung, string $wert) use (&$zeilen): void {
        if (trim($wert) !== '') {
            $zeilen[] = str_pad($beschriftung . ':', 16) . $wert;
        }
    };

    $feld('Name', (string) $werte['name']);
    $feld('Telefon', (string) $werte['telefon']);
    $feld('E-Mail', (string) $werte['email']);
    $zeilen[] = '';

    $fahrzeug = trim(implode(' ', array_filter([
        (string) $werte['marke'], (string) $werte['modell'],
        $werte['baujahr'] !== '' ? '(' . $werte['baujahr'] . ')' : '',
    ])));
    $feld('Fahrzeug', $fahrzeug !== '' ? $fahrzeug : (string) $werte['fahrzeug']);
    $feld('Lackfarbe', (string) $werte['lackfarbe']);
    $feld('Leistung', implode(', ', (array) $werte['leistungen']));
    $feld('Ort der Arbeit', (string) $werte['ort']);

    if (trim((string) $werte['beschreibung']) !== '') {
        $zeilen[] = '';
        $zeilen[] = 'Beschreibung:';
        $zeilen[] = (string) $werte['beschreibung'];
    }

    $zeilen[] = '';
    $zeilen[] = str_repeat('-', 40);
    $feld('Fotos', $fotos === [] ? 'keine' : count($fotos) . ' im Anhang');
    $feld('Eingegangen', date('d.m.Y, H:i') . ' Uhr');
    $feld('Formular', (string) $werte['herkunft']);
    $feld('Kennung', $kennung);

    return implode("\n", $zeilen) . "\n";
}

/** @param array<string,mixed> $werte */
function bestaetigung_als_text(array $werte): string
{
    $s = site();

    return implode("\n", [
        'Hallo ' . $werte['name'] . ',',
        '',
        'Ihre Anfrage ist bei uns eingegangen. Wir sehen sie uns an und melden',
        'uns in der Regel am selben Werktag.',
        '',
        'Wenn es eilig ist, rufen Sie einfach an: ' . get($s, 'kontakt.telefon', ''),
        '',
        'Viele Grüße',
        get($s, 'firma.name', ''),
        get($s, 'firma.strasse', '') . ', ' . get($s, 'firma.plz', '') . ' ' . get($s, 'firma.ort', ''),
        get($s, 'kontakt.telefon', ''),
        '',
        '--',
        'Diese Nachricht wurde automatisch erzeugt. Sie können darauf antworten.',
    ]) . "\n";
}

/**
 * Baut die Nachricht und liefert sie ab.
 *
 * @param list<array{name:string,typ:string,inhalt:string}> $anhaenge
 */
function mail_senden(string $an, string $betreff, string $text, ?string $antwortAn = null, array $anhaenge = []): bool
{
    $k = mail_konfiguration();
    if ($k === null) {
        return false;
    }

    $absender = (string) ($k['absender'] ?? $k['benutzer']);
    $grenze   = 'grenze-' . bin2hex(random_bytes(12));

    $kopf = [
        'From: ' . mail_adresse((string) ($k['absendername'] ?? 'Website'), $absender),
        'To: ' . $an,
        'Subject: ' . mail_kodiert($betreff),
        'Date: ' . date('r'),
        'Message-ID: <' . bin2hex(random_bytes(12)) . '@' . (parse_url('//' . ($_SERVER['HTTP_HOST'] ?? 'localhost'), PHP_URL_HOST) ?: 'localhost') . '>',
        'MIME-Version: 1.0',
    ];

    if ($antwortAn !== null && $antwortAn !== '') {
        $kopf[] = 'Reply-To: ' . $antwortAn;
    }

    if ($anhaenge === []) {
        $kopf[] = 'Content-Type: text/plain; charset=UTF-8';
        $kopf[] = 'Content-Transfer-Encoding: base64';
        $rumpf = chunk_split(base64_encode($text));
    } else {
        $kopf[] = 'Content-Type: multipart/mixed; boundary="' . $grenze . '"';
        $teile = [
            '--' . $grenze,
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: base64',
            '',
            chunk_split(base64_encode($text)),
        ];
        foreach ($anhaenge as $anhang) {
            $teile[] = '--' . $grenze;
            $teile[] = 'Content-Type: ' . $anhang['typ'] . '; name="' . $anhang['name'] . '"';
            $teile[] = 'Content-Transfer-Encoding: base64';
            $teile[] = 'Content-Disposition: attachment; filename="' . $anhang['name'] . '"';
            $teile[] = '';
            $teile[] = chunk_split(base64_encode($anhang['inhalt']));
        }
        $teile[] = '--' . $grenze . '--';
        $rumpf = implode("\r\n", $teile);
    }

    $nachricht = implode("\r\n", $kopf) . "\r\n\r\n" . $rumpf;

    return smtp_abliefern($k, $absender, $an, $nachricht);
}

function mail_adresse(string $name, string $adresse): string
{
    return mail_kodiert($name) . ' <' . $adresse . '>';
}

/** Betreff und Namen duerfen Umlaute enthalten, aber nicht roh im Kopf stehen. */
function mail_kodiert(string $text): string
{
    return preg_match('/[\x80-\xFF]/', $text) === 1
        ? '=?UTF-8?B?' . base64_encode($text) . '?='
        : $text;
}

/**
 * Der eigentliche SMTP-Dialog.
 *
 * @param array<string,mixed> $k
 */
function smtp_abliefern(array $k, string $von, string $an, string $nachricht): bool
{
    $host = (string) $k['host'];
    $port = (int) ($k['port'] ?? 587);

    // Drei Betriebsarten: 'ssl' spricht von der ersten Sekunde an
    // verschluesselt (Port 465), 'tls' beginnt im Klartext und schaltet mit
    // STARTTLS um (Port 587, der Normalfall bei IONOS), 'keine' laesst beides
    // weg — das braucht nur ein lokaler Testempfaenger.
    $art    = (string) ($k['verschluesselung'] ?? ($port === 465 ? 'ssl' : 'tls'));
    $direkt = $art === 'ssl';

    $verbindung = @stream_socket_client(
        ($direkt ? 'ssl://' : 'tcp://') . $host . ':' . $port,
        $fehlernummer,
        $fehlertext,
        (int) ($k['zeitlimit'] ?? 10),
        STREAM_CLIENT_CONNECT
    );

    if (!is_resource($verbindung)) {
        error_log("SMTP: keine Verbindung zu {$host}:{$port} ({$fehlertext})");
        return false;
    }

    stream_set_timeout($verbindung, (int) ($k['zeitlimit'] ?? 10));

    $lesen = static function () use ($verbindung): string {
        $antwort = '';
        while (($zeile = fgets($verbindung, 1024)) !== false) {
            $antwort .= $zeile;
            // Mehrzeilige Antworten haben nach dem Code einen Bindestrich.
            if (strlen($zeile) < 4 || $zeile[3] !== '-') {
                break;
            }
        }
        return $antwort;
    };

    $sagen = static function (string $befehl, string $erwartet) use ($verbindung, $lesen, $host): bool {
        if ($befehl !== '') {
            fwrite($verbindung, $befehl . "\r\n");
        }
        $antwort = $lesen();
        if (!str_starts_with($antwort, $erwartet)) {
            // Passwoerter tauchen im Log nicht auf.
            $sichtbar = str_starts_with($befehl, 'AUTH') || preg_match('/^[A-Za-z0-9+\/=]{8,}$/', $befehl) === 1
                ? '[Zugangsdaten]' : $befehl;
            error_log("SMTP {$host}: „{$sichtbar}“ ergab „" . trim($antwort) . '“, erwartet ' . $erwartet);
            return false;
        }
        return true;
    };

    $ok = $sagen('', '220')
        && $sagen('EHLO ' . ($_SERVER['HTTP_HOST'] ?? 'localhost'), '250');

    if ($ok && $art === 'tls') {
        $ok = $sagen('STARTTLS', '220')
            && stream_socket_enable_crypto($verbindung, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)
            // Nach dem Umschalten faengt die Sitzung von vorn an — der Server
            // hat alles vergessen, was vorher gesagt wurde.
            && $sagen('EHLO ' . ($_SERVER['HTTP_HOST'] ?? 'localhost'), '250');
    }

    if ($ok && !empty($k['benutzer'])) {
        $ok = $sagen('AUTH LOGIN', '334')
            && $sagen(base64_encode((string) $k['benutzer']), '334')
            && $sagen(base64_encode((string) $k['passwort']), '235');
    }

    $ok = $ok
        && $sagen('MAIL FROM:<' . $von . '>', '250')
        && $sagen('RCPT TO:<' . $an . '>', '250')
        && $sagen('DATA', '354')
        // Ein Punkt allein in einer Zeile beendet die Nachricht — steht er im
        // Text, muss er verdoppelt werden.
        && $sagen(preg_replace('/^\./m', '..', $nachricht) . "\r\n.", '250');

    if ($ok) {
        $sagen('QUIT', '221');
    }

    fclose($verbindung);

    return $ok;
}
