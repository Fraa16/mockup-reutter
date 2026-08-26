<?php
declare(strict_types=1);

require dirname(__DIR__, 2) . '/app/bootstrap.php';
require APP_ROOT . '/lib/auth.php';
require APP_ROOT . '/lib/speichern.php';

auth_verlangen();

$schemas = require APP_ROOT . '/schema/felder.php';
$bereich = (string) ($_GET['bereich'] ?? '');

// Bereichsname gegen das Schema pruefen, nie ungefiltert in einen Dateipfad.
if (!isset($schemas[$bereich])) {
    http_response_code(404);
    exit('Unbekannter Bereich.');
}

$schema = $schemas[$bereich];
$daten  = content($bereich);
$fehler = [];

/* Auf einer schreibgeschuetzten Vorschau kann nichts gespeichert werden.
   Das Formular bleibt lesbar, der Knopf verschwindet. */
$nurLesen = !inhalte_beschreibbar();

/* Speichern ------------------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$nurLesen) {
    csrf_pruefen();

    [$daten, $fehler] = eingaben_uebernehmen($schema, $daten, $_POST);

    // Bilder: erst annehmen, dann den Dateinamen ins Feld schreiben.
    foreach (bild_felder($schema) as $feldpfad => $formularname) {
        $datei = datei_aus_post($formularname);
        if ($datei === null) {
            continue;
        }
        [$name, $meldung] = bild_annehmen($datei);
        if ($meldung !== null) {
            $fehler[$formularname] = $meldung;
        } elseif ($name !== null) {
            setze($daten, $feldpfad, $name);
        }
    }

    if ($fehler === []) {
        content_speichern($bereich, $daten);
        header('Location: /admin/edit.php?bereich=' . rawurlencode($bereich) . '&gespeichert=1');
        exit;
    }
}

/**
 * Sammelt alle Bildfelder samt ihrem Formularnamen — auch die in Listen.
 *
 * @param array<string,mixed> $schema
 * @return array<string,string> Pfad in der JSON-Datei => Name im Formular
 */
function bild_felder(array $schema): array
{
    $treffer = [];

    foreach ($schema['gruppen'] as $gruppe) {
        foreach ($gruppe['felder'] as $feld) {
            if ($feld['typ'] === 'bild') {
                $treffer[$feld['pfad']] = feld_schluessel($feld['pfad']);
                continue;
            }
            if ($feld['typ'] !== 'liste') {
                continue;
            }
            $anzahl = count((array) get(content_aktuell(), $feld['pfad'], []));
            foreach ($feld['subfelder'] as $sub) {
                if ($sub['typ'] !== 'bild') {
                    continue;
                }
                for ($i = 0; $i < $anzahl; $i++) {
                    $treffer["{$feld['pfad']}.{$i}.{$sub['pfad']}"] =
                        feld_schluessel($feld['pfad']) . "_{$i}_" . feld_schluessel($sub['pfad']);
                }
            }
        }
    }

    return $treffer;
}

/** @return array<string,mixed> */
function content_aktuell(): array
{
    return content((string) ($_GET['bereich'] ?? ''));
}

/**
 * Holt einen Upload aus $_FILES.
 *
 * @return array{name:string,type:string,tmp_name:string,error:int,size:int}|null
 */
function datei_aus_post(string $name): ?array
{
    if (!isset($_FILES[$name]) || $_FILES[$name]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    return $_FILES[$name];
}

$gespeichert = isset($_GET['gespeichert']);
?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= h($schema['titel']) ?> bearbeiten — Smartrepair Reutter</title>
<link rel="stylesheet" href="/admin/assets/admin.css">
<link rel="icon" href="/assets/favicon.svg" type="image/svg+xml">
</head>
<body class="app">

<header class="kopf">
  <div class="marke">
    <a href="/admin/" class="zurueck">← Übersicht</a>
    <span class="sub"><?= h($schema['titel']) ?></span>
  </div>
  <div class="kopf-rechts">
    <a href="/" target="_blank" rel="noopener">Website ansehen ↗</a>
    <span class="wer"><?= h(auth_benutzername()) ?></span>
    <a href="/admin/?abmelden=1" class="knopf schlicht">Abmelden</a>
  </div>
</header>

<main class="inhalt schmal">
  <?php if ($gespeichert): ?>
    <p class="hinweis erfolg">Gespeichert. Die Änderungen sind auf der Website sichtbar.</p>
  <?php endif; ?>
  <?php if ($fehler !== []): ?>
    <div class="hinweis fehler">
      <strong>Bitte noch korrigieren:</strong>
      <ul><?php foreach ($fehler as $m): ?><li><?= h($m) ?></li><?php endforeach; ?></ul>
    </div>
  <?php endif; ?>

  <h1><?= h($schema['titel']) ?></h1>
  <p class="vorspann"><?= h($schema['beschreibung']) ?></p>

  <?php if ($nurLesen): ?>
  <p class="hinweis nur-lesen">
    <strong>Nur zum Ansehen.</strong> Auf dieser Vorschau lässt sich nichts speichern —
    sie läuft auf einem Server ohne beschreibbaren Speicher. Auf dem richtigen
    Hosting funktioniert das Bearbeiten normal.
  </p>
  <?php endif; ?>

  <form method="post" enctype="multipart/form-data" class="formular">
    <?= csrf_feld() ?>

    <?php foreach ($schema['gruppen'] as $gruppe): ?>
    <section class="gruppe">
      <h2><?= h($gruppe['titel']) ?></h2>
      <?php if (isset($gruppe['hinweis'])): ?>
        <p class="gruppen-hinweis"><?= h($gruppe['hinweis']) ?></p>
      <?php endif; ?>

      <?php foreach ($gruppe['felder'] as $feld): ?>
        <?php if ($feld['typ'] === 'liste'): ?>

          <?php $eintraege = (array) get($daten, $feld['pfad'], []); ?>
          <div class="liste">
            <?php foreach (array_values($eintraege) as $i => $eintrag): ?>
            <fieldset class="listen-eintrag">
              <legend><?= h($feld['label']) ?> <?= $i + 1 ?></legend>
              <?php foreach ($feld['subfelder'] as $sub): ?>
                <?php
                  $name = feld_schluessel($feld['pfad']) . "[{$i}][" . feld_schluessel($sub['pfad']) . ']';
                  $dateiName = feld_schluessel($feld['pfad']) . "_{$i}_" . feld_schluessel($sub['pfad']);
                  feld_ausgeben($sub, get($eintrag, $sub['pfad']), $name, $dateiName);
                ?>
              <?php endforeach; ?>
            </fieldset>
            <?php endforeach; ?>
          </div>

        <?php else: ?>
          <?php feld_ausgeben($feld, get($daten, $feld['pfad']), feld_schluessel($feld['pfad'])); ?>
        <?php endif; ?>
      <?php endforeach; ?>
    </section>
    <?php endforeach; ?>

    <div class="formular-fuss">
      <?php if (!$nurLesen): ?>
      <button type="submit" class="knopf primaer">Änderungen speichern</button>
      <?php endif; ?>
      <a href="/admin/" class="knopf schlicht"><?= $nurLesen ? 'Zurück zur Übersicht' : 'Abbrechen' ?></a>
    </div>
  </form>
</main>

<?php
/**
 * Gibt ein einzelnes Feld aus.
 *
 * @param array<string,mixed> $feld
 */
function feld_ausgeben(array $feld, mixed $wert, string $name, ?string $dateiName = null): void
{
    $id = 'f_' . preg_replace('/[^a-z0-9]+/i', '_', $name);
    $dateiName ??= $name;
    ?>
    <div class="feld feld-<?= attr($feld['typ']) ?>">
      <label for="<?= attr($id) ?>"><?= h($feld['label']) ?></label>
      <?php if (isset($feld['hilfe'])): ?>
        <p class="feld-hilfe"><?= h($feld['hilfe']) ?></p>
      <?php endif; ?>

      <?php if ($feld['typ'] === 'mehrzeilig'): ?>
        <textarea id="<?= attr($id) ?>" name="<?= attr($name) ?>" rows="4"><?= h((string) $wert) ?></textarea>

      <?php elseif ($feld['typ'] === 'absaetze'): ?>
        <?php /* Gespeichert ist eine Liste von Absaetzen, bearbeitet wird ein
                Textfeld — mit einer Leerzeile zwischen den Absaetzen. */ ?>
        <textarea id="<?= attr($id) ?>" name="<?= attr($name) ?>" rows="10"><?= h(implode("\n\n", (array) ($wert ?? []))) ?></textarea>
        <p class="feld-hilfe">Für einen neuen Absatz eine Leerzeile einfügen.</p>

      <?php elseif ($feld['typ'] === 'zahl'): ?>
        <input type="number" step="any" id="<?= attr($id) ?>" name="<?= attr($name) ?>" value="<?= attr((string) $wert) ?>">

      <?php elseif ($feld['typ'] === 'bild'): ?>
        <div class="bild-feld">
          <?php if ($wert !== null && $wert !== '' && is_file(PUBLIC_ROOT . '/uploads/' . $wert)): ?>
            <img class="vorschau" src="/uploads/<?= attr((string) $wert) ?>" alt="" loading="lazy">
          <?php else: ?>
            <div class="vorschau leer">kein Bild</div>
          <?php endif; ?>
          <div class="bild-aktionen">
            <input type="file" id="<?= attr($id) ?>" name="<?= attr($dateiName) ?>" accept="image/jpeg,image/png,image/webp">
            <input type="hidden" name="<?= attr($name) ?>" value="<?= attr((string) $wert) ?>">
            <p class="feld-hilfe">JPG, PNG oder WebP, bis 12 MB. Wird automatisch verkleinert.</p>
          </div>
        </div>

      <?php else: ?>
        <input type="text" id="<?= attr($id) ?>" name="<?= attr($name) ?>" value="<?= attr((string) $wert) ?>">
      <?php endif; ?>
    </div>
    <?php
}
?>
</body>
</html>
