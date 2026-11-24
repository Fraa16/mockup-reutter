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
 * fuehrt der rote Hauptknopf auf die Mobilnummer. Gleich daneben steht seit
 * November 2026 die Werkstatt, siehe festnetz().
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
 * Die Festnetznummer der Werkstatt.
 *
 * Bis November 2026 stand an zweiter Stelle WhatsApp. Der Betrieb bietet das
 * nicht mehr an und wollte die Werkstattnummer sichtbarer haben — sie steht
 * jetzt ueberall dort, wo vorher WhatsApp stand.
 *
 * @return array{anzeige:string,ziel:string}
 */
function festnetz(): array
{
    $s = site();

    return [
        'anzeige' => (string) get($s, 'kontakt.telefon', ''),
        'ziel'    => 'tel:' . get($s, 'kontakt.telefon_link', ''),
    ];
}

/**
 * Kleine Bildzeichen fuer die Kontaktknoepfe, direkt im Markup.
 *
 * Als Inline-SVG statt als Datei: kein zusaetzlicher Abruf, und die Farbe
 * folgt per currentColor dem Knopf — auch beim Ueberfahren. Die
 * Sicherheitsregel in .htaccess erlaubt das, sie verbietet nur Skripte.
 *
 * Herkunft: Hoerer, Halle und Winkel aus Material Icons (Apache 2.0). Die
 * Halle steht fuer die Werkstattnummer, der Hoerer fuer die Mobilnummer.
 */
function icon(string $name, string $class = 'icon'): string
{
    $pfade = [
        'aufklappen' => 'M16.59 8.59 12 13.17 7.41 8.59 6 10l6 6 6-6z',
        'telefon'  => 'M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z',
        'werkstatt' => 'M22 21V7L12 3 2 7v14h5v-9h10v9h5zm-11-2H9v2h2v-2zm2-3h-2v2h2v-2zm2 3h-2v2h2v-2z',
    ];
    if (!isset($pfade[$name])) {
        return '';
    }

    return '<svg class="' . attr($class) . '" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false">'
        . '<path fill="currentColor" d="' . $pfade[$name] . '"/></svg>';
}
