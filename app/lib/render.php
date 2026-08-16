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
 * Das rote Parallelogramm aus dem Logo — im Mockup das durchgehende
 * Akzentelement. Groesse variiert je Einsatzort.
 */
function swash(string $class = 'swash'): string
{
    return '<span class="' . attr($class) . '" aria-hidden="true"></span>';
}
