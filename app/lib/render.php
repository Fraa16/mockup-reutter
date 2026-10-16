<?php
declare(strict_types=1);

/**
 * Templating ohne Engine: PHP-Dateien mit ausgelagerten Variablen.
 * Ausgegeben wird grundsaetzlich ueber h() — nur an Stellen, an denen
 * bewusst Markup erlaubt ist, steht raw() im Template.
 */

/**
 * Escaping fuer HTML-Text. Kurzer Name, weil er in jedem Template steht.
 */
function h(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Escaping fuer Werte, die in Attributen landen (URLs, IDs).
 */
function attr(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Rendert ein Template aus app/templates/ und liefert das Markup zurueck.
 *
 * @param array<string,mixed> $vars
 */
function render(string $template, array $vars = []): string
{
    if (!preg_match('#^[a-z0-9/-]+$#', $template)) {
        throw new InvalidArgumentException("Ungueltiger Templatename: {$template}");
    }

    $file = APP_ROOT . '/templates/' . $template . '.php';
    if (!is_file($file)) {
        throw new RuntimeException("Template fehlt: {$template}.php");
    }

    extract($vars, EXTR_SKIP);
    ob_start();
    require $file;

    return (string) ob_get_clean();
}

/**
 * Bindet ein Teil-Template aus app/templates/partials/ direkt ein.
 *
 * @param array<string,mixed> $vars
 */
function partial(string $name, array $vars = []): void
{
    echo render('partials/' . $name, $vars);
}

/**
 * URL fuer eine Datei aus public/assets/ — mit einer Pruefsumme des Inhalts
 * als Cache-Buster, damit Aenderungen an CSS/JS beim Besucher sofort
 * ankommen, obwohl die Dateien ein Jahr lang im Browser liegen duerfen.
 *
 * Bis Oktober 2026 stand hier der Zeitstempel der Datei. Der sagt aber nur,
 * wann sie hochgeladen wurde, nicht was drinsteht — und beim Hochladen
 * entstehen Augenblicke, in denen Adresse und Inhalt nicht zusammenpassen.
 * Ein Besucher hielt danach die alte Stildatei unter der Adresse fest, und
 * das neue Aufklappmenue lag ungestaltet ueber der Startseite. Die
 * Pruefsumme haengt nur am Inhalt: gleiche Adresse heisst gleiche Datei.
 *
 * Gerechnet wird je Aufruf hoechstens einmal pro Datei; zwei Dateien mit
 * zusammen gut 200 KB kosten dabei unter einer Millisekunde.
 */
function asset(string $path): string
{
    static $versionen = [];

    $path = ltrim($path, '/');
    if (!isset($versionen[$path])) {
        $file = PUBLIC_ROOT . '/assets/' . $path;
        $summe = is_file($file) ? md5_file($file) : false;
        $versionen[$path] = $summe !== false ? substr($summe, 0, 10) : '';
    }

    return '/assets/' . $path . ($versionen[$path] !== '' ? '?v=' . $versionen[$path] : '');
}

/**
 * URL fuer ein hochgeladenes Bild aus public/uploads/.
 */
function upload(string $file): string
{
    return '/uploads/' . ltrim($file, '/');
}

/**
 * Ein Bild aus public/uploads/ als vollstaendiges <img>.
 *
 * Vorher stand in den Templates ein blankes <img src=...> mit fest
 * eingetragenen Abmessungen. Auf einem 390-px-Bildschirm lud die Galerie
 * damit 2,5 MB, weil jedes Handy dieselbe Datei bekam wie ein grosser
 * Monitor. bild_quellen() gab es schon, aufgerufen hat es niemand.
 *
 * Die Abmessungen kommen aus der Datei selbst, nicht aus dem Template: so
 * kann kein falsches Seitenverhaeltnis mehr entstehen, wenn der Betrieb ein
 * Bild austauscht. Fehlt die Datei, bleibt es beim blossen src — dann sieht
 * man den Platzhalter statt eines zerbrochenen Layouts.
 *
 * `sizes` sagt dem Browser, wie breit das Bild im fertigen Layout steht.
 * Ohne diese Angabe rechnet er mit der vollen Fensterbreite und laedt zu
 * viel. Die Voreinstellung passt fuer Bilder, die eine Spalte fuellen.
 *
 * @param array{class?:string,id?:string,sizes?:string,loading?:string,
 *              fetchpriority?:string,decoding?:string} $o
 */
function bild(string $datei, string $alt, array $o = []): string
{
    $datei = ltrim($datei, '/');
    $quellen = function_exists('bild_quellen') ? bild_quellen($datei) : ['srcset' => '', 'breite' => 0, 'hoehe' => 0];

    $teile = ['src="' . attr(upload($datei)) . '"', 'alt="' . attr($alt) . '"'];

    if ($quellen['srcset'] !== '') {
        $teile[] = 'srcset="' . attr($quellen['srcset']) . '"';
        $teile[] = 'sizes="' . attr($o['sizes'] ?? '(max-width: 980px) 100vw, 50vw') . '"';
    }
    if ($quellen['breite'] > 0) {
        $teile[] = 'width="' . $quellen['breite'] . '"';
        $teile[] = 'height="' . $quellen['hoehe'] . '"';
    }

    foreach (['class', 'id', 'loading', 'fetchpriority', 'decoding'] as $name) {
        if (!empty($o[$name])) {
            $teile[] = $name . '="' . attr((string) $o[$name]) . '"';
        }
    }

    // Alles ausser dem ersten Bild darf warten. Wer das anders will, setzt
    // loading ausdruecklich — die Hero-Bilder tun das.
    if (!isset($o['loading']) && !isset($o['fetchpriority'])) {
        $teile[] = 'loading="lazy"';
    }

    return '<img ' . implode(' ', $teile) . '>';
}

/**
 * Ob ein Vorher/Nachher-Paar gezeigt werden darf: zwei Bilder, und zwar zwei
 * verschiedene.
 *
 * Fehlt eines, oder steht zweimal dasselbe Foto drin, entfaellt der Regler
 * samt Abschnitt. Frueher zeigten solche Regler ein Platzhalterfoto auf beiden
 * Seiten, die Vorher-Haelfte per CSS abgedunkelt — auf einer Seite, die
 * unbearbeitete eigene Aufnahmen verspricht. Jetzt erscheint ein Vergleich
 * erst, wenn im Panel zwei echte Fotos eingetragen sind.
 *
 * Das Feld echtes_paar in aelteren Inhaltsdateien wird nicht mehr gelesen:
 * Im Panel liess es sich nie setzen.
 *
 * @param array<string,mixed> $v  mit den Schluesseln vorher und nachher
 */
function vergleich_echt(array $v): bool
{
    $vorher  = trim((string) ($v['vorher'] ?? ''));
    $nachher = trim((string) ($v['nachher'] ?? ''));

    return $vorher !== '' && $nachher !== '' && $vorher !== $nachher;
}

/**
 * Das rote Parallelogramm aus dem Logo — im Mockup das durchgehende
 * Akzentelement. Groesse variiert je Einsatzort.
 */
function swash(string $class = 'swash'): string
{
    return '<span class="' . attr($class) . '" aria-hidden="true"></span>';
}

/**
 * Die Nummer, unter der der Betrieb angerufen werden will.
 *
 * Der Inhaber nimmt Anrufe am liebsten selbst an, auch unterwegs — deshalb
 * fuehrt jeder Anrufknopf auf die Mobilnummer. Die Werkstattnummer steht
 * weiter im Fuss, auf der Kontaktseite und im Impressum, aber nirgends mehr
 * als Hauptweg.
 *
 * @return array{anzeige:string,ziel:string}
 */
function anruf(): array
{
    $s = site();

    return [
        'anzeige' => (string) get($s, 'kontakt.mobil', ''),
        'ziel'    => 'tel:' . get($s, 'kontakt.mobil_link', ''),
    ];
}

/**
 * Link in einen WhatsApp-Chat mit der Mobilnummer.
 *
 * Ein gewoehnlicher Link, kein eingebetteter Dienst: Beim Seitenaufruf geht
 * nichts an Meta, erst wer klickt, verlaesst die Seite. Auf den
 * Leistungsseiten steht die Leistung schon im ersten Satz — dann weiss der
 * Betrieb beim Lesen, worum es geht, und der Kunde muss nur noch Fotos
 * anhaengen.
 */
function whatsapp_link(string $leistung = ''): string
{
    $nummer = preg_replace('/\D+/', '', (string) get(site(), 'kontakt.mobil_link', ''));
    $text   = $leistung !== ''
        ? 'Hallo, ich habe eine Frage zu ' . $leistung . '. Fotos schicke ich mit.'
        : '';

    return 'https://wa.me/' . $nummer . ($text !== '' ? '?text=' . rawurlencode($text) : '');
}

/**
 * Kleine Bildzeichen fuer die Kontaktknoepfe, direkt im Markup.
 *
 * Als Inline-SVG statt als Datei: kein zusaetzlicher Abruf, und die Farbe
 * folgt per currentColor dem Knopf — auch beim Ueberfahren. Die
 * Sicherheitsregel in .htaccess erlaubt das, sie verbietet nur Skripte.
 *
 * Herkunft: Hoerer und Winkel aus Material Icons (Apache 2.0), WhatsApp-Zeichen aus
 * Simple Icons (CC0). Das WhatsApp-Zeichen darf laut Markenrichtlinie von
 * Meta genau dafuer stehen: als Verweis auf einen Chat.
 */
function icon(string $name, string $class = 'icon'): string
{
    $pfade = [
        'aufklappen' => 'M16.59 8.59 12 13.17 7.41 8.59 6 10l6 6 6-6z',
        'telefon'  => 'M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z',
        'whatsapp' => 'M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z',
    ];
    if (!isset($pfade[$name])) {
        return '';
    }

    return '<svg class="' . attr($class) . '" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false">'
        . '<path fill="currentColor" d="' . $pfade[$name] . '"/></svg>';
}
