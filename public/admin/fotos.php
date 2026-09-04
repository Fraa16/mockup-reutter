<?php
declare(strict_types=1);

/**
 * Fotos hochladen und einsortieren.
 *
 * Die Seite ist fuers Handy gebaut: Daniel fotografiert im Betrieb und laedt
 * direkt hoch. Zugeordnet und beschriftet wird danach — am Handy schreibt
 * niemand Bildbeschreibungen.
 *
 * Mit JavaScript gehen die Fotos einzeln an foto-upload.php, mit Fortschritt
 * je Datei. Ohne JavaScript nimmt diese Seite selbst entgegen, was in einen
 * POST passt. Beide Wege landen in derselben Verarbeitung.
 */

require dirname(__DIR__, 2) . '/app/bootstrap.php';
require APP_ROOT . '/lib/auth.php';
require APP_ROOT . '/lib/speichern.php';
require APP_ROOT . '/lib/posteingang.php';

auth_verlangen();

$nurLesen = !inhalte_beschreibbar();
$fehler   = [];
$erfolg   = '';

/* Siehe foto-upload.php: ohne diese Pruefung stuende hier "Sitzung
   abgelaufen", obwohl schlicht das Foto zu gross war. */
if (upload_verworfen()) {
    $fehler[] = 'Das Foto ist zu groß für den Server (mehr als ' . bild_grenze_text() . ').';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_pruefen();
    $aktion = (string) ($_POST['aktion'] ?? '');

    if ($nurLesen) {
        $fehler[] = 'Auf dieser Vorschau lässt sich nichts ändern.';
    } elseif ($aktion === 'hochladen') {
        /* Rueckfallebene ohne JavaScript. $_FILES dreht bei mehreren Dateien
           die Struktur um — je Eigenschaft ein Feld statt je Datei ein Satz.
           Deshalb hier wieder zusammengesetzt. */
        $roh = $_FILES['fotos'] ?? null;
        $anzahl = is_array($roh['name'] ?? null) ? count($roh['name']) : 0;
        $gezaehlt = 0;

        for ($i = 0; $i < $anzahl; $i++) {
            if ((int) $roh['error'][$i] === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            $datei = [
                'name'     => (string) $roh['name'][$i],
                'type'     => (string) $roh['type'][$i],
                'tmp_name' => (string) $roh['tmp_name'][$i],
                'error'    => (int) $roh['error'][$i],
                'size'     => (int) $roh['size'][$i],
            ];
            [$name, $meldung] = bild_annehmen($datei);
            if ($name === null) {
                $fehler[] = $datei['name'] . ': ' . ($meldung ?? 'nicht angenommen');
                continue;
            }
            posteingang_ergaenzen($name, $datei['name']);
            $gezaehlt++;
        }

        if ($gezaehlt > 0) {
            header('Location: /admin/fotos.php?hochgeladen=' . $gezaehlt);
            exit;
        }
        if ($fehler === []) {
            $fehler[] = 'Es kam keine Datei an.';
        }
    } elseif ($aktion === 'galerie') {
        $meldung = posteingang_in_galerie(
            (string) ($_POST['datei'] ?? ''),
            (string) ($_POST['kategorie'] ?? ''),
            (string) ($_POST['alt'] ?? '')
        );
        if ($meldung !== null) {
            $fehler[] = $meldung;
        } else {
            header('Location: /admin/fotos.php?uebernommen=1');
            exit;
        }
    } elseif ($aktion === 'loeschen') {
        foto_loeschen((string) ($_POST['datei'] ?? ''));
        header('Location: /admin/fotos.php?geloescht=1');
        exit;
    }
}

$posteingang = posteingang_lesen();
$kategorien  = galerie_kategorien();
$inGalerie   = count(content('galerie')['raster']['bilder'] ?? []);
?><!doctype html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Fotos — Smartrepair Reutter</title>
<link rel="stylesheet" href="/admin/assets/admin.css">
<link rel="icon" href="/assets/favicon.svg" type="image/svg+xml">
</head>
<body class="app">

<header class="kopf">
  <div class="marke">
    <a href="/admin/" class="zurueck">← Übersicht</a>
    <span class="sub">Fotos</span>
  </div>
  <div class="kopf-rechts">
    <a href="/galerie/" target="_blank" rel="noopener">Galerie ansehen ↗</a>
    <span class="wer"><?= h(auth_benutzername()) ?></span>
    <a href="/admin/?abmelden=1" class="knopf schlicht">Abmelden</a>
  </div>
</header>

<main class="inhalt">
  <h1>Fotos</h1>

  <?php if ($nurLesen): ?>
    <p class="hinweis">Diese Vorschau ist schreibgeschützt. Hochladen geht nur auf dem echten Server.</p>
  <?php endif; ?>
  <?php foreach ($fehler as $f): ?>
    <p class="hinweis fehler"><?= h($f) ?></p>
  <?php endforeach; ?>
  <?php if (isset($_GET['hochgeladen'])): ?>
    <p class="hinweis erfolg"><?= (int) $_GET['hochgeladen'] ?> Foto(s) hochgeladen.</p>
  <?php endif; ?>
  <?php if (isset($_GET['uebernommen'])): ?>
    <p class="hinweis erfolg">Foto ist in der Galerie — jetzt <?= (int) $inGalerie ?> Bilder.</p>
  <?php endif; ?>
  <?php if (isset($_GET['geloescht'])): ?>
    <p class="hinweis erfolg">Foto gelöscht.</p>
  <?php endif; ?>

  <?php if (!$nurLesen): ?>
  <section class="gruppe">
    <h2>Neue Fotos hochladen</h2>
    <p class="gruppen-hinweis">
      Mehrere auf einmal auswählen geht. Die Fotos werden automatisch verkleinert
      und in ein platzsparendes Format gebracht; Aufnahmeort und Kameradaten fallen dabei weg.
    </p>

    <?php /* accept="image/*" ist hier wichtiger, als es aussieht: nur damit
            bietet das iPhone die Mediathek samt Kamera an — und wandelt seine
            HEIC-Aufnahmen beim Hochladen nach JPEG um. Eine enge Liste
            verhindert das und der Server bekaeme ein Format, das er nicht
            lesen kann. */ ?>
    <form method="post" enctype="multipart/form-data" id="foto-formular">
      <?= csrf_feld() ?>
      <input type="hidden" name="aktion" value="hochladen">
      <div class="feld">
        <label for="fotos">Fotos auswählen</label>
        <input type="file" id="fotos" name="fotos[]" multiple accept="image/*">
        <p class="feld-hilfe">JPG, PNG oder WebP, bis <?= h(bild_grenze_text()) ?> je Bild. Vom iPhone aufgenommene Fotos werden beim Hochladen umgewandelt.</p>
      </div>
      <div class="formular-fuss">
        <button type="submit" class="knopf primaer">Hochladen</button>
      </div>
      <ul class="upload-liste" id="upload-liste" hidden></ul>
    </form>
  </section>
  <?php endif; ?>

  <section class="gruppe">
    <h2>Posteingang<?= $posteingang !== [] ? ' (' . count($posteingang) . ')' : '' ?></h2>

    <?php if ($posteingang === []): ?>
      <p class="gruppen-hinweis">Nichts da. Hochgeladene Fotos warten hier, bis sie einer Kategorie zugeordnet sind.</p>
    <?php else: ?>
      <p class="gruppen-hinweis">
        Jedes Foto braucht eine Kategorie und eine Bildbeschreibung, dann kann es
        in die Galerie. Die Beschreibung wird Blinden vorgelesen und steht bei Google.
      </p>

      <?php foreach ($posteingang as $e): ?>
      <form method="post" class="posteingang-eintrag">
        <?= csrf_feld() ?>
        <input type="hidden" name="datei" value="<?= attr($e['datei']) ?>">
        <img class="vorschau" src="/uploads/<?= attr($e['datei']) ?>" alt="" loading="lazy">
        <div class="posteingang-felder">
          <div class="feld">
            <label for="kat-<?= attr($e['datei']) ?>">Kategorie</label>
            <select id="kat-<?= attr($e['datei']) ?>" name="kategorie">
              <?php foreach ($kategorien as $k): ?>
              <option value="<?= attr($k) ?>"><?= h($k) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="feld">
            <label for="alt-<?= attr($e['datei']) ?>">Bildbeschreibung</label>
            <input type="text" id="alt-<?= attr($e['datei']) ?>" name="alt"
                   placeholder="Was ist zu sehen? z. B. Hagelschaden am Dach, von innen gedrückt">
          </div>
          <p class="feld-hilfe">
            <?= h($e['original']) ?> · hochgeladen <?= h(date('d.m.Y H:i', (int) $e['zeit'])) ?>
          </p>
          <?php if (!$nurLesen): ?>
          <div class="posteingang-knoepfe">
            <button type="submit" name="aktion" value="galerie" class="knopf primaer">In die Galerie</button>
            <button type="submit" name="aktion" value="loeschen" class="knopf schlicht">Löschen</button>
          </div>
          <?php endif; ?>
        </div>
      </form>
      <?php endforeach; ?>
    <?php endif; ?>
  </section>

  <section class="hilfe">
    <h2>Was gute Fotos ausmacht</h2>
    <dl>
      <dt>Quer halten, nicht hochkant</dt>
      <dd>Die Galerie ist auf liegende Bilder gebaut. Hochkant funktioniert, wirkt aber verloren.</dd>

      <dt>Nichts nachbearbeiten</dt>
      <dd>Keine Filter, nicht aufhellen. Das Ergebnis soll für sich sprechen — und aufgehübschte Bilder fallen auf.</dd>

      <dt>Vorher und Nachher gehören zusammen</dt>
      <dd>
        Beide Aufnahmen müssen <strong>dasselbe Fahrzeug aus demselben Winkel im
        selben Licht</strong> zeigen. Am besten: vor der Arbeit ein Foto machen,
        Standort merken, nach der Arbeit noch einmal von genau dort. Das ist der
        wichtigste Punkt auf dieser Seite — Bilder, die als eigene Arbeit
        ausgegeben werden, es aber nicht sind, sind abmahnfähig.
      </dd>

      <dt>Lieber zwanzig ehrliche als drei perfekte</dt>
      <dd>Handyfotos sind völlig in Ordnung, solange sie scharf sind.</dd>
    </dl>
  </section>
</main>

<script src="/admin/assets/fotos.js" defer></script>
</body>
</html>
