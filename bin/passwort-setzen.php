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

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Dieses Skript laeuft nur auf der Kommandozeile.\n");
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
