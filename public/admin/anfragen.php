<?php
declare(strict_types=1);

require dirname(__DIR__, 2) . '/app/bootstrap.php';
require APP_ROOT . '/lib/auth.php';
require APP_ROOT . '/lib/speichern.php';
require APP_ROOT . '/lib/anfrage.php';

auth_verlangen();

/* Fotos -----------------------------------------------------------------
   Die Bilder liegen bewusst ausserhalb des Webroots — es sind Aufnahmen
   fremder Fahrzeuge. Ausgeliefert werden sie nur hier, nach der Anmeldung,
   und nur Dateinamen, die wir selbst vergeben haben. */
if (isset($_GET['foto'])) {
    $pfad = anfrage_fotopfad((string) ($_GET['a'] ?? ''), (string) $_GET['foto']);
    if ($pfad === null) {
        http_response_code(404);
        exit('Nicht gefunden.');
    }

    header('Content-Type: image/webp');
    header('Content-Length: ' . filesize($pfad));
    header('Cache-Control: private, no-store');
    // Kein inline-Rendern fremder Dateien ohne Not — die Seite bindet das
    // Bild selbst ein, ein direkter Aufruf soll nichts ausfuehren.
    header("Content-Security-Policy: default-src 'none'; img-src 'self'");
    header('X-Content-Type-Options: nosniff');
    readfile($pfad);
    exit;
}

$nurLesen = !inhalte_beschreibbar();
$meldung  = '';

/* Löschen ---------------------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_pruefen();

    if ($nurLesen) {
        $meldung = 'Auf dieser Vorschau kann nichts gelöscht werden.';
    } elseif (anfrage_loeschen((string) ($_POST['kennung'] ?? ''))) {
        header('Location: /admin/anfragen.php?geloescht=1');
        exit;
    } else {
        $meldung = 'Diese Anfrage gibt es nicht mehr.';
    }
}

$kennung = (string) ($_GET['a'] ?? '');
$einzeln = $kennung !== '' ? anfrage_lesen($kennung) : null;

if ($kennung !== '' && $einzeln === null) {
    http_response_code(404);
    exit('Diese Anfrage gibt es nicht.');
}

// Ansehen heisst gelesen. Auf einer schreibgeschuetzten Vorschau passiert
// dabei nichts — die Anfrage bleibt lesbar, nur das Kennzeichen bleibt stehen.
if ($einzeln !== null && !$nurLesen) {
    anfrage_gelesen_setzen($kennung);
    $einzeln['gelesen'] = true;
}

$liste = $einzeln === null ? anfragen_lesen(200) : [];

/** Aus "2026-08-17T09:14:22+02:00" wird "17.08.2026, 09:14 Uhr". */
function anfrage_zeit(string $roh): string
{
    $zeit = date_create($roh);

    return $zeit === false ? $roh : $zeit->format('d.m.Y, H:i') . ' Uhr';
}

/** Die Zeile, die in der Liste zeigt, worum es geht. */
function anfrage_betreff(array $a): string
{
    $leistungen = array_filter((array) ($a['leistungen'] ?? []));

    return $leistungen !== [] ? implode(', ', $leistungen) : 'ohne Angabe';
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= $einzeln === null ? 'Anfragen' : 'Anfrage von ' . h((string) $einzeln['name']) ?> — Smartrepair Reutter</title>
<link rel="stylesheet" href="/admin/assets/admin.css">
<link rel="icon" href="/assets/favicon.svg" type="image/svg+xml">
</head>
<body class="app">

<header class="kopf">
  <div class="marke">
    <a href="<?= $einzeln === null ? '/admin/' : '/admin/anfragen.php' ?>" class="zurueck">← <?= $einzeln === null ? 'Übersicht' : 'Alle Anfragen' ?></a>
    <span class="sub">Anfragen</span>
  </div>
  <div class="kopf-rechts">
    <a href="/" target="_blank" rel="noopener">Website ansehen ↗</a>
    <span class="wer"><?= h(auth_benutzername()) ?></span>
    <a href="/admin/?abmelden=1" class="knopf schlicht">Abmelden</a>
  </div>
</header>

<main class="inhalt<?= $einzeln !== null ? ' schmal' : '' ?>">

  <?php if ($meldung !== ''): ?>
    <p class="hinweis fehler"><?= h($meldung) ?></p>
  <?php endif; ?>
  <?php if (isset($_GET['geloescht'])): ?>
    <p class="hinweis erfolg">Anfrage gelöscht.</p>
  <?php endif; ?>
  <?php if ($nurLesen): ?>
    <p class="hinweis nur-lesen">
      <strong>Vorschau ohne Speicher.</strong> Anfragen sind hier lesbar,
      lassen sich aber nicht als gelesen markieren oder löschen.
    </p>
  <?php endif; ?>

<?php if ($einzeln === null): ?>

  <h1>Eingegangene Anfragen</h1>
  <p class="vorspann">
    Jede Anfrage über die Website landet zusätzlich hier — auch dann, wenn die
    E-Mail unterwegs hängen bleibt. Antworten Sie direkt an die angegebene
    Adresse oder Telefonnummer.
  </p>

  <?php if ($liste === []): ?>
    <div class="leerer-stand">
      <p>Noch keine Anfrage eingegangen.</p>
      <p class="klein">Sobald jemand das Formular abschickt, steht sie hier — samt Fotos, falls welche dabei waren.</p>
    </div>
  <?php else: ?>
    <div class="anfrage-liste">
      <?php foreach ($liste as $a): ?>
      <a class="anfrage-zeile<?= ($a['gelesen'] ?? false) ? '' : ' ist-neu' ?>"
         href="/admin/anfragen.php?a=<?= attr((string) $a['kennung']) ?>">
        <span class="zeile-zeit">
          <?= h(anfrage_zeit((string) ($a['zeitpunkt'] ?? ''))) ?>
          <?php if (!($a['gelesen'] ?? false)): ?><span class="marke-neu">neu</span><?php endif; ?>
        </span>
        <span class="zeile-name"><?= h((string) ($a['name'] ?? '')) ?></span>
        <span class="zeile-betreff"><?= h(anfrage_betreff($a)) ?></span>
        <span class="zeile-anhang">
          <?php if (($a['fotos'] ?? []) !== []): ?><?= count((array) $a['fotos']) ?> Foto<?= count((array) $a['fotos']) === 1 ? '' : 's' ?><?php endif; ?>
        </span>
      </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

<?php else: ?>

  <h1><?= h((string) $einzeln['name']) ?></h1>
  <p class="vorspann"><?= h(anfrage_zeit((string) ($einzeln['zeitpunkt'] ?? ''))) ?> · über <?= h((string) ($einzeln['herkunft'] ?? 'Formular')) ?></p>

  <?php
    /* Erst die Wege zurueck — darum geht es hier. Danach das Fahrzeug. */
    $telefon = (string) ($einzeln['telefon'] ?? '');
    $email   = (string) ($einzeln['email'] ?? '');
  ?>
  <div class="antwort-wege">
    <?php if ($telefon !== ''): ?>
      <a class="knopf primaer" href="tel:<?= attr(preg_replace('/[^0-9+]/', '', $telefon) ?? '') ?>">Anrufen: <?= h($telefon) ?></a>
    <?php endif; ?>
    <?php if ($email !== ''): ?>
      <a class="knopf schlicht" href="mailto:<?= attr($email) ?>?subject=<?= attr(rawurlencode('Ihre Anfrage bei Smartrepair Reutter')) ?>">Antworten: <?= h($email) ?></a>
    <?php endif; ?>
  </div>

  <?php
    $fahrzeug = array_filter([
        'Marke'     => (string) ($einzeln['marke'] ?? ''),
        'Modell'    => (string) ($einzeln['modell'] ?? ''),
        'Baujahr'   => (string) ($einzeln['baujahr'] ?? ''),
        'Lackfarbe' => (string) ($einzeln['lackfarbe'] ?? ''),
        'Fahrzeug'  => (string) ($einzeln['fahrzeug'] ?? ''),
        'Ort'       => (string) ($einzeln['ort'] ?? ''),
    ], static fn (string $w): bool => $w !== '');
    $leistungen = array_filter((array) ($einzeln['leistungen'] ?? []));
  ?>

  <?php if ($leistungen !== []): ?>
  <section class="anfrage-block">
    <h2>Gewünschte Leistungen</h2>
    <ul class="marken-liste">
      <?php foreach ($leistungen as $l): ?><li><?= h((string) $l) ?></li><?php endforeach; ?>
    </ul>
  </section>
  <?php endif; ?>

  <?php if (trim((string) ($einzeln['beschreibung'] ?? '')) !== ''): ?>
  <section class="anfrage-block">
    <h2>Beschreibung</h2>
    <p class="anfrage-text"><?= nl2br(h((string) $einzeln['beschreibung'])) ?></p>
  </section>
  <?php endif; ?>

  <?php if ($fahrzeug !== []): ?>
  <section class="anfrage-block">
    <h2>Fahrzeug</h2>
    <dl class="anfrage-daten">
      <?php foreach ($fahrzeug as $label => $wert): ?>
      <div><dt><?= h($label) ?></dt><dd><?= h($wert) ?></dd></div>
      <?php endforeach; ?>
    </dl>
  </section>
  <?php endif; ?>

  <?php if (($einzeln['fotos'] ?? []) !== []): ?>
  <section class="anfrage-block">
    <h2>Mitgeschickte Fotos</h2>
    <div class="anfrage-fotos">
      <?php foreach ((array) $einzeln['fotos'] as $i => $foto): ?>
      <a href="/admin/anfragen.php?a=<?= attr($kennung) ?>&amp;foto=<?= attr((string) $foto) ?>" target="_blank" rel="noopener">
        <img src="/admin/anfragen.php?a=<?= attr($kennung) ?>&amp;foto=<?= attr((string) $foto) ?>"
             alt="Vom Absender mitgeschicktes Foto <?= (int) $i + 1 ?>" loading="lazy">
      </a>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <section class="anfrage-block">
    <h2>Einwilligung</h2>
    <p class="anfrage-text">
      <?= ($einzeln['datenschutz'] ?? false)
          ? 'Der Datenschutzhinweis wurde beim Abschicken bestätigt.'
          : 'Kein Haken gesetzt — diese Anfrage hätte nicht durchgehen dürfen.' ?>
    </p>
  </section>

  <?php if (!$nurLesen): ?>
  <form method="post" class="anfrage-loeschen"
        onsubmit="return confirm('Diese Anfrage endgültig löschen? Auch die Fotos werden gelöscht.')">
    <?= csrf_feld() ?>
    <input type="hidden" name="kennung" value="<?= attr($kennung) ?>">
    <button type="submit" class="knopf schlicht">Anfrage löschen</button>
    <span class="loesch-hinweis">Erledigt? Dann darf sie weg — gespeicherte Kundendaten sollen nicht länger liegen als nötig.</span>
  </form>
  <?php endif; ?>

<?php endif; ?>

</main>
</body>
</html>
