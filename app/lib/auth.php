<?php
declare(strict_types=1);

/**
 * Anmeldung fuers Panel.
 *
 * Bewusst klein gehalten: ein Login, keine Rollenverwaltung, keine
 * Registrierung, kein Passwort-Zuruecksetzen per Mail. Jede dieser Funktionen
 * waere zusaetzliche Angriffsflaeche fuer einen Betrieb mit genau einem Nutzer.
 * Passwort vergessen loest der Betreuer ueber bin/passwort-setzen.php.
 */

const AUTH_MAX_VERSUCHE   = 5;
const AUTH_SPERRE_SEKUNDEN = 900;   // 15 Minuten
const AUTH_LEERLAUF        = 7200;  // 2 Stunden ohne Aktivitaet -> abgemeldet

function auth_session_starten(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    /* In einer Serverless-Umgebung ist nur das Temp-Verzeichnis beschreibbar.
       PHP nimmt das meist ohnehin, aber darauf verlassen wollen wir uns nicht:
       ohne Schreibrecht scheitert session_start() still und niemand kaeme
       hinein. Dass die Sitzung beim Wechsel der Instanz verloren geht, laesst
       sich damit nicht verhindern — dafuer braeuchte es einen gemeinsamen
       Speicher, den es auf der Vorschau nicht gibt. */
    if (getenv('VERCEL') !== false || getenv('AWS_LAMBDA_FUNCTION_NAME') !== false) {
        session_save_path(sys_get_temp_dir());
    }

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/admin/',
        'secure'   => !empty($_SERVER['HTTPS']) || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https',
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_name('reutter_panel');
    session_start();
}

/**
 * Die hinterlegten Zugaenge.
 *
 * Normalfall ist data/users.php, angelegt ueber bin/passwort-setzen.php. Die
 * Datei ist bewusst nicht im Git — Passwort-Hashes gehoeren nicht ins
 * Repository.
 *
 * Genau deshalb gibt es auf einem Host, auf den nur das Repository ausgerollt
 * wird, gar keinen Zugang. Fuer diesen Fall der Rueckfall auf zwei
 * Umgebungsvariablen: PANEL_BENUTZER und PANEL_PASSWORT_HASH (ein
 * bcrypt-Hash, kein Klartext). Sie sind fuer die Vorschau gedacht, damit man
 * sich das Panel ansehen kann, und gehoeren vor dem Livegang entfernt.
 *
 * @return array<string,array{passwort:string,name:string}>
 */
function auth_benutzer(): array
{
    $datei = DATA_ROOT . '/users.php';
    if (is_file($datei)) {
        return require $datei;
    }

    $benutzer = (string) (getenv('PANEL_BENUTZER') ?: '');
    $hash     = (string) (getenv('PANEL_PASSWORT_HASH') ?: '');

    if ($benutzer === '' || !str_starts_with($hash, '$2y$')) {
        return [];
    }

    return [strtolower($benutzer) => ['passwort' => $hash, 'name' => $benutzer]];
}

function auth_angemeldet(): bool
{
    auth_session_starten();

    if (empty($_SESSION['benutzer'])) {
        return false;
    }

    // Abgelaufene Sitzungen gelten nicht mehr, auch wenn das Cookie noch da ist.
    if (time() - ($_SESSION['zuletzt'] ?? 0) > AUTH_LEERLAUF) {
        auth_abmelden();
        return false;
    }

    $_SESSION['zuletzt'] = time();

    return true;
}

function auth_benutzername(): string
{
    return (string) ($_SESSION['name'] ?? $_SESSION['benutzer'] ?? '');
}

/**
 * Zaehlt Fehlversuche pro Benutzername in einer Datei — auf Shared Hosting
 * gibt es keinen gemeinsamen Speicher, auf den man sich verlassen koennte.
 */
function auth_gesperrt(string $benutzer): int
{
    $eintraege = auth_versuche_lesen();
    $key = strtolower($benutzer);

    if (($eintraege[$key]['anzahl'] ?? 0) < AUTH_MAX_VERSUCHE) {
        return 0;
    }

    $rest = AUTH_SPERRE_SEKUNDEN - (time() - $eintraege[$key]['letzter']);

    return $rest > 0 ? $rest : 0;
}

/** @return array<string,array{anzahl:int,letzter:int}> */
function auth_versuche_lesen(): array
{
    $datei = DATA_ROOT . '/.login-versuche.json';
    if (!is_file($datei)) {
        return [];
    }

    $daten = json_decode((string) file_get_contents($datei), true);

    return is_array($daten) ? $daten : [];
}

function auth_versuch_vermerken(string $benutzer, bool $erfolg): void
{
    $datei = DATA_ROOT . '/.login-versuche.json';
    $eintraege = auth_versuche_lesen();
    $key = strtolower($benutzer);

    if ($erfolg) {
        unset($eintraege[$key]);
    } else {
        $abgelaufen = isset($eintraege[$key])
            && time() - $eintraege[$key]['letzter'] > AUTH_SPERRE_SEKUNDEN;
        $eintraege[$key] = [
            'anzahl'  => $abgelaufen ? 1 : (($eintraege[$key]['anzahl'] ?? 0) + 1),
            'letzter' => time(),
        ];
    }

    // Alte Eintraege mitnehmen, damit die Datei nicht unbegrenzt waechst.
    foreach ($eintraege as $k => $e) {
        if (time() - $e['letzter'] > AUTH_SPERRE_SEKUNDEN * 4) {
            unset($eintraege[$k]);
        }
    }

    /* Auf einem schreibgeschuetzten Dateisystem laesst sich nicht mitzaehlen.
       Das @ unterdrueckt nur die Warnung, nicht das Problem: dort gibt es
       dann keine Bremse gegen Durchprobieren. Fuer die Vorschau, die ohnehin
       hinter dem Zugriffsschutz von Vercel liegt, ist das vertretbar — auf
       dem echten Hosting ist data/ beschreibbar und die Sperre greift. */
    @file_put_contents($datei, json_encode($eintraege), LOCK_EX);
}

/**
 * @return array{0:bool,1:string} Erfolg und, im Fehlerfall, die Meldung
 */
function auth_anmelden(string $benutzer, string $passwort): array
{
    auth_session_starten();

    if (($rest = auth_gesperrt($benutzer)) > 0) {
        return [false, 'Zu viele Fehlversuche. Bitte in ' . ceil($rest / 60) . ' Minuten erneut versuchen.'];
    }

    $benutzerliste = auth_benutzer();
    $eintrag = $benutzerliste[strtolower($benutzer)] ?? null;

    // Auch ohne Treffer einen Hash pruefen: sonst verraet die Antwortzeit,
    // welche Benutzernamen existieren.
    $hash = $eintrag['passwort'] ?? '$2y$12$usersuchtNichtsGefundenDummyHashXXXXXXXXXXXXXXXXXXXXXXXXX';

    if (!password_verify($passwort, $hash) || $eintrag === null) {
        auth_versuch_vermerken($benutzer, false);
        return [false, 'Benutzername oder Passwort stimmt nicht.'];
    }

    auth_versuch_vermerken($benutzer, true);

    // Neue Session-ID nach dem Anmelden — verhindert Session Fixation.
    session_regenerate_id(true);
    $_SESSION['benutzer'] = strtolower($benutzer);
    $_SESSION['name']     = $eintrag['name'];
    $_SESSION['zuletzt']  = time();

    return [true, ''];
}

function auth_abmelden(): void
{
    auth_session_starten();
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }

    session_destroy();
}

/** Leitet auf die Anmeldung um, wenn niemand angemeldet ist. */
function auth_verlangen(): void
{
    if (auth_angemeldet()) {
        return;
    }

    $ziel = $_SERVER['REQUEST_URI'] ?? '/admin/';
    header('Location: /admin/?weiter=' . rawurlencode($ziel));
    exit;
}

/* -------------------------------------------------------------------------
   CSRF
   ------------------------------------------------------------------------- */

function csrf_token(): string
{
    auth_session_starten();

    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf'];
}

function csrf_feld(): string
{
    return '<input type="hidden" name="_token" value="' . attr(csrf_token()) . '">';
}

/** Bricht bei fehlendem oder falschem Token ab — jede POST-Route ruft das auf. */
function csrf_pruefen(): void
{
    auth_session_starten();
    $gesendet = (string) ($_POST['_token'] ?? '');

    if ($gesendet === '' || !hash_equals((string) ($_SESSION['csrf'] ?? ''), $gesendet)) {
        http_response_code(419);
        exit('Die Sitzung ist abgelaufen. Bitte die Seite neu laden und noch einmal speichern.');
    }
}
