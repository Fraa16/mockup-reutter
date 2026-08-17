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
 * URL fuer eine Datei aus public/assets/ — mit Zeitstempel als Cache-Buster,
 * damit Aenderungen an CSS/JS beim Besucher sofort ankommen, obwohl die
 * Dateien mit langer Lebensdauer ausgeliefert werden.
 */
function asset(string $path): string
{
    $path = ltrim($path, '/');
    $file = PUBLIC_ROOT . '/assets/' . $path;
    $version = is_file($file) ? substr((string) filemtime($file), -6) : '';

    return '/assets/' . $path . ($version !== '' ? '?v=' . $version : '');
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
 * Das rote Parallelogramm aus dem Logo — im Mockup das durchgehende
 * Akzentelement. Groesse variiert je Einsatzort.
 */
function swash(string $class = 'swash'): string
{
    return '<span class="' . attr($class) . '" aria-hidden="true"></span>';
}
