<?php
declare(strict_types=1);

require dirname(__DIR__, 2) . '/app/bootstrap.php';
require APP_ROOT . '/lib/auth.php';

$schema  = require APP_ROOT . '/schema/felder.php';
$meldung = '';
$erfolg  = '';

/* Abmelden ------------------------------------------------------------- */
if (isset($_GET['abmelden'])) {
    auth_abmelden();
    header('Location: /admin/');
    exit;
}

/* Anmelden ------------------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !auth_angemeldet()) {
    csrf_pruefen();
    [$ok, $meldung] = auth_anmelden((string) ($_POST['benutzer'] ?? ''), (string) ($_POST['passwort'] ?? ''));
    if ($ok) {
        $weiter = (string) ($_GET['weiter'] ?? '/admin/');
        // Nur eigene Pfade, kein offener Redirect nach draussen.
        header('Location: ' . (preg_match('#^/admin/[a-z0-9./?=_-]*$#i', $weiter) ? $weiter : '/admin/'));
        exit;
    }
}

if (isset($_GET['gespeichert'])) {
    $erfolg = 'Änderungen gespeichert.';
}

$angemeldet = auth_angemeldet();
?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= $angemeldet ? 'Inhalte pflegen' : 'Anmeldung' ?> — Fahrzeugpflege Reutter</title>
<link rel="stylesheet" href="/admin/assets/admin.css">
<link rel="icon" href="/assets/favicon.svg" type="image/svg+xml">
</head>
<body class="<?= $angemeldet ? 'app' : 'login-seite' ?>">

<?php if (!$angemeldet): ?>

  <main class="login-karte">
    <div class="marke">
      <span class="wortmarke"><span>REU</span><span>T</span><i></i><span>T</span><span>ER</span></span>
      <span class="sub">Inhalte pflegen</span>
    </div>

    <?php if ($meldung !== ''): ?>
      <p class="hinweis fehler"><?= h($meldung) ?></p>
    <?php endif; ?>

    <form method="post">
      <?= csrf_feld() ?>
      <label>
        <span>Benutzername</span>
        <input type="text" name="benutzer" autocomplete="username" required autofocus>
      </label>
      <label>
        <span>Passwort</span>
        <input type="password" name="passwort" autocomplete="current-password" required>
      </label>
      <button type="submit" class="knopf primaer">Anmelden</button>
    </form>

    <?php if (auth_benutzer() === []): ?>
      <p class="hinweis">
        Es ist noch kein Zugang eingerichtet. Auf dem Server einmal
        <code>php bin/passwort-setzen.php</code> ausführen.
      </p>
    <?php endif; ?>
  </main>

<?php else: ?>

  <header class="kopf">
    <div class="marke">
      <span class="wortmarke"><span>REU</span><span>T</span><i></i><span>T</span><span>ER</span></span>
      <span class="sub">Inhalte pflegen</span>
    </div>
    <div class="kopf-rechts">
      <a href="/" target="_blank" rel="noopener">Website ansehen ↗</a>
      <span class="wer"><?= h(auth_benutzername()) ?></span>
      <a href="?abmelden=1" class="knopf schlicht">Abmelden</a>
    </div>
  </header>

  <main class="inhalt">
    <?php if ($erfolg !== ''): ?>
      <p class="hinweis erfolg"><?= h($erfolg) ?></p>
    <?php endif; ?>

    <h1>Was möchten Sie ändern?</h1>
    <p class="vorspann">
      Alle Änderungen sind sofort auf der Website sichtbar. Die jeweils letzte
      Fassung wird automatisch gesichert — es kann also nichts verloren gehen.
    </p>

    <div class="kacheln">
      <?php foreach ($schema as $name => $bereich): ?>
      <a class="kachel" href="/admin/edit.php?bereich=<?= attr($name) ?>">
        <span class="kachel-titel"><?= h($bereich['titel']) ?></span>
        <span class="kachel-text"><?= h($bereich['beschreibung']) ?></span>
        <span class="kachel-pfeil" aria-hidden="true">→</span>
      </a>
      <?php endforeach; ?>
    </div>

    <section class="hilfe">
      <h2>Kurz erklärt</h2>
      <dl>
        <dt>Bilder</dt>
        <dd>Beim Hochladen werden Fotos automatisch verkleinert und ins richtige Format gebracht. Sie können die Originaldatei direkt aus der Kamera nehmen — je größer, desto besser.</dd>
        <dt>Bildbeschreibung</dt>
        <dd>Kurzer Satz, der beschreibt, was auf dem Bild zu sehen ist. Wird gebraucht von Menschen, die die Seite vorlesen lassen, und von Google.</dd>
        <dt>Etwas kaputt gemacht?</dt>
        <dd>Kein Problem. Melden Sie sich, die vorherige Fassung ist gespeichert und in einer Minute zurückgeholt.</dd>
      </dl>
    </section>
  </main>

<?php endif; ?>

</body>
</html>
