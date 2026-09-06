<?php
declare(strict_types=1);

/**
 * Legt einen Panel-Zugang an oder setzt ein Passwort zurueck.
 *
 * Aufruf auf dem Server:   php bin/passwort-setzen.php
 *
 * Bewusst nur ueber die Kommandozeile: eine Weboberflaeche zum Anlegen von
 * Benutzern waere genau der Weg, ueber den ein selbstgebautes Panel
 * uebernommen wird.
 */

/* Der Test war frueher „PHP_SAPI !== 'cli'" und hat auf IONOS den legitimen
   Aufruf gesperrt: Dort liegt unter SSH nicht die CLI-Fassung von PHP im Pfad,
   sondern die CGI-Fassung. Sichtbar wurde das an einer Zeile
   „Content-type: text/html" und einem sofortigen Abbruch.

   Entscheidend ist nicht, welche PHP-Fassung laeuft, sondern ob eine
   Web-Anfrage dahintersteckt. Kommt der Aufruf ueber den Webserver, setzt
   dieser REQUEST_METHOD und HTTP_HOST; aus einer Shell heraus steht dort
   nichts. Der Schutz bleibt damit erhalten und der Aufruf per SSH
   funktioniert unabhaengig davon, wie der Hoster seine Binaerdateien benennt.

   Zweite Verteidigungslinie ohnehin: bin/ liegt ausserhalb des Webroots und
   ist ueber den Browser gar nicht erreichbar. */
if (PHP_SAPI !== 'cli' && (isset($_SERVER['REQUEST_METHOD']) || isset($_SERVER['HTTP_HOST']))) {
    http_response_code(403);
    exit("Dieses Skript laeuft nur auf der Kommandozeile.\n");
}

/* Die CGI-Fassung stellt jeder Ausgabe HTTP-Kopfzeilen voran. Auf der
   Kommandozeile ist das nur Rauschen vor der ersten Frage. */
if (PHP_SAPI !== 'cli' && !headers_sent()) {
    header_remove();
    header('Content-Type:');
}

$datenOrdner = dirname(__DIR__) . '/data';
$datei = $datenOrdner . '/users.php';

$benutzer = is_file($datei) ? require $datei : [];

echo "\nPanel-Zugang fuer Smartrepair Reutter\n";
echo str_repeat('-', 44), "\n";

if ($benutzer !== []) {
    echo "Vorhandene Zugaenge: ", implode(', ', array_keys($benutzer)), "\n\n";
}

$name = frage('Benutzername (z. B. reutter)');
if ($name === '') {
    exit("Abgebrochen.\n");
}
$name = strtolower($name);

$anzeige = frage('Anzeigename (z. B. Daniel Reutter)');

$passwort = frage_versteckt('Passwort (mindestens 12 Zeichen)');
if (mb_strlen($passwort) < 12) {
    exit("\nZu kurz. Mindestens 12 Zeichen — ein Panel ohne starkes Passwort ist ein offenes Panel.\n");
}
$wiederholung = frage_versteckt('Passwort wiederholen');
if ($passwort !== $wiederholung) {
    exit("\nDie Eingaben stimmen nicht ueberein.\n");
}

$benutzer[$name] = [
    // cost 12 statt der Voreinstellung 10: sperrbar langsam fuer Angreifer,
    // im Alltag mit einem Login pro Sitzung nicht spuerbar.
    'passwort' => password_hash($passwort, PASSWORD_BCRYPT, ['cost' => 12]),
    'name'     => $anzeige !== '' ? $anzeige : $name,
];

if (!is_dir($datenOrdner)) {
    mkdir($datenOrdner, 0775, true);
}

$inhalt = "<?php\n"
    . "// Automatisch erzeugt von bin/passwort-setzen.php — nicht von Hand bearbeiten.\n"
    . "// Diese Datei liegt ausserhalb des Webroots und gehoert nicht ins Git.\n"
    . "return " . var_export($benutzer, true) . ";\n";

file_put_contents($datei, $inhalt, LOCK_EX);
chmod($datei, 0640);

echo "\nZugang gespeichert: {$name}\n";
echo "Anmeldung unter /admin/\n\n";

function frage(string $text): string
{
    echo $text, ': ';
    return trim((string) fgets(STDIN));
}

function frage_versteckt(string $text): string
{
    echo $text, ': ';

    // Auf Unix die Eingabe ausblenden, damit das Passwort nicht im Terminal
    // und spaeter in der Shell-Historie steht.
    if (DIRECTORY_SEPARATOR === '/' && @shell_exec('command -v stty') !== null) {
        @shell_exec('stty -echo');
        $eingabe = trim((string) fgets(STDIN));
        @shell_exec('stty echo');
        echo "\n";
        return $eingabe;
    }

    return trim((string) fgets(STDIN));
}
