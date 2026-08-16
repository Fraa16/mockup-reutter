<?php
declare(strict_types=1);

/**
 * Inhalte kommen aus JSON-Dateien in data/content/ — keine Datenbank.
 * Das CMS schreibt genau diese Dateien, das Frontend liest sie.
 */

/**
 * Laedt eine Inhaltsdatei und haelt sie fuer die Dauer des Requests im Speicher.
 *
 * @return array<string,mixed>
 */
function content(string $name): array
{
    static $cache = [];

    if (isset($cache[$name])) {
        return $cache[$name];
    }

    // Nur Dateinamen aus Buchstaben, Ziffern, Bindestrich — kein Weg nach oben.
    if (!preg_match('/^[a-z0-9-]+$/', $name)) {
        throw new InvalidArgumentException("Ungueltiger Inhaltsname: {$name}");
    }

    $file = DATA_ROOT . '/content/' . $name . '.json';
    if (!is_file($file)) {
        throw new RuntimeException("Inhaltsdatei fehlt: {$name}.json");
    }

    $data = json_decode((string) file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);

    return $cache[$name] = $data;
}

/**
 * Globale Stammdaten (Firma, Kontakt, Navigation).
 *
 * @return array<string,mixed>
 */
function site(): array
{
    return content('site');
}

/**
 * Greift verschachtelt per Punktnotation zu und liefert einen Standardwert,
 * statt bei fehlenden Schluesseln eine Warnung zu werfen.
 *
 * Beispiel: get(site(), 'kontakt.telefon')
 */
function get(array $data, string $path, mixed $default = null): mixed
{
    foreach (explode('.', $path) as $key) {
        if (!is_array($data) || !array_key_exists($key, $data)) {
            return $default;
        }
        $data = $data[$key];
    }

    return $data;
}

/**
 * Die Leistungen, die laut Angebot eine eigene Unterseite bekommen.
 * 'Felgen & Reifen' ist bewusst nicht dabei und laeuft unter Exterieur mit.
 *
 * @return list<array<string,mixed>>
 */
function leistungen_mit_seite(): array
{
    return array_values(array_filter(
        content('leistungen')['eintraege'],
        static fn (array $l): bool => $l['eigene_seite'] === true
    ));
}
