<?php
declare(strict_types=1);

require dirname(__DIR__, 2) . '/app/bootstrap.php';
require APP_ROOT . '/lib/auth.php';
require APP_ROOT . '/lib/posteingang.php';
require APP_ROOT . '/lib/speichern.php';   // inhalte_beschreibbar()
require APP_ROOT . '/lib/anfrage.php';

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
<title><?= $angemeldet ? 'Inhalte pflegen' : 'Anmeldung' ?> — Smartrepair Reutter</title>
<link rel="stylesheet" href="/admin/assets/admin.css">
<link rel="icon" href="/assets/favicon.svg" type="image/svg+xml">
</head>
<body class="<?= $angemeldet ? 'app' : 'login-seite' ?>">

<?php if (!$angemeldet): ?>

  <main class="login-karte">
    <div class="marke">
      <img class="wortmarke" src="/assets/logo/reutter-weiss.svg" alt="Smartrepair Reutter" width="1697" height="131">
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
      <?php /* Zwei verschiedene Ursachen, zwei verschiedene Hinweise. Ohne
              diese Unterscheidung sucht man auf einer Vorschau nach einer
              Datei, die dort nie liegen wird. Preisgegeben wird dabei nichts,
              was nicht ohnehin in der Dokumentation steht — die Namen der
              beiden Variablen sind kein Geheimnis, ihre Werte stehen hier
              nicht. */ ?>
      <?php if (auth_umgebung('PANEL_BENUTZER') !== '' || auth_umgebung('PANEL_PASSWORT_HASH') !== ''): ?>
      <p class="hinweis fehler">
        Der Zugang aus den Umgebungsvariablen ist unvollständig:
        <code>PANEL_BENUTZER</code> <?= auth_umgebung('PANEL_BENUTZER') !== '' ? 'ist gesetzt' : '<strong>fehlt</strong>' ?>,
        <code>PANEL_PASSWORT_HASH</code>
        <?= auth_umgebung('PANEL_PASSWORT_HASH') === '' ? '<strong>fehlt</strong>'
            : (str_starts_with(auth_umgebung('PANEL_PASSWORT_HASH'), '$2y$')
               ? 'ist gesetzt' : '<strong>ist kein bcrypt-Hash</strong> (beginnt nicht mit <code>$2y$</code> — meist von der Shell verschluckt)') ?>.
      </p>
      <?php else: ?>
      <p class="hinweis">
        Es ist noch kein Zugang eingerichtet. Auf dem Server einmal
        <code>php bin/passwort-setzen.php</code> ausführen — oder, auf einer
        Vorschau ohne Schreibrechte, <code>PANEL_BENUTZER</code> und
        <code>PANEL_PASSWORT_HASH</code> setzen.
      </p>
      <?php endif; ?>
    <?php endif; ?>
  </main>

<?php else: ?>

  <header class="kopf">
    <div class="marke">
      <img class="wortmarke" src="/assets/logo/reutter-weiss.svg" alt="Smartrepair Reutter" width="1697" height="131">
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

    <?php if (!inhalte_beschreibbar()): ?>
    <?php /* Ohne diesen Hinweis stuende hier „Alle Änderungen sind sofort
            sichtbar" — auf einer Vorschau ohne Schreibrechte ist das schlicht
            falsch. Der Hinweis in den Formularen kommt erst, wenn man eines
            geöffnet hat; das ist einen Klick zu spät. */ ?>
    <p class="hinweis nur-lesen">
      <strong>Nur zum Ansehen.</strong> Diese Vorschau läuft auf einem Server ohne
      beschreibbaren Speicher — Sie können sich alles anschauen, gespeichert wird
      nichts. Auf dem richtigen Hosting funktioniert das Bearbeiten normal.
    </p>
    <?php else: ?>
    <p class="vorspann">
      Alle Änderungen sind sofort auf der Website sichtbar. Die jeweils letzte
      Fassung wird automatisch gesichert — es kann also nichts verloren gehen.
    </p>
    <?php endif; ?>

    <?php if (!seo_indexierbar()): ?>
    <?php /* Die Google-Sperre ist absichtlich stumm — sie steht in robots.txt
            und in einer Zeile im Seitenkopf, beides sieht man im Alltag nie.
            Genau deshalb steht sie hier: Wer auf der fertigen Domain
            angemeldet ist und das liest, weiss, dass noch ein Handgriff
            fehlt. Ohne diesen Hinweis waere der wahrscheinlichste Fehler eine
            Website, die live geht und monatelang unauffindbar bleibt. */ ?>
    <p class="hinweis nur-lesen">
      <strong>Für Google gesperrt.</strong> Diese Adresse taucht in keiner Suche
      auf — während des Aufbaus ist das richtig so. Sobald die Website
      offiziell startet, unter <a href="/admin/edit.php?bereich=site">Stammdaten
      → Sichtbarkeit bei Google</a> die Adresse eintragen.
    </p>
    <?php endif; ?>

    <?php /* Die Anfragen stehen bewusst vor den Inhalten: wer sich hier
            anmeldet, will meist wissen, ob jemand geschrieben hat. */ ?>
    <section class="bereichsgruppe">
      <h2>Posteingang</h2>
      <div class="kacheln">
        <a class="kachel" href="/admin/anfragen.php">
          <span class="kachel-titel">Anfragen von der Website</span>
          <span class="kachel-text">Alles, was über die Formulare hereinkommt — mit Fotos, falls welche dabei waren.</span>
          <?php if (($neu = anfragen_ungelesen()) > 0): ?>
          <span class="kachel-zahl"><?= (int) $neu ?> neu</span>
          <?php endif; ?>
          <span class="kachel-pfeil" aria-hidden="true">→</span>
        </a>
        <?php /* Fotos stehen hier und nicht bei den Inhalten: sie kommen von
                unterwegs herein, genau wie die Anfragen, und werden hier
                einsortiert statt in einem Formular bearbeitet. */ ?>
        <a class="kachel" href="/admin/fotos.php">
          <span class="kachel-titel">Fotos</span>
          <span class="kachel-text">Vom Handy hochladen und in die Galerie einsortieren.</span>
          <?php if (($wartend = count(posteingang_lesen())) > 0): ?>
          <span class="kachel-zahl"><?= (int) $wartend ?> wartet</span>
          <?php endif; ?>
          <span class="kachel-pfeil" aria-hidden="true">→</span>
        </a>
      </div>
    </section>

    <?php
      /* Sechzehn Kacheln nebeneinander sind eine Wand. Deshalb nach Gruppen
         sortiert — in der Reihenfolge, in der die Gruppen hier stehen, nicht
         in der Reihenfolge der Schemadatei. */
      $reihenfolge = ['Stammdaten', 'Startseite', 'Leistungen', 'Weitere Seiten', 'Rechtliches'];
      $nachGruppe  = [];
      foreach ($schema as $name => $bereich) {
          $nachGruppe[$bereich['gruppe'] ?? 'Sonstiges'][$name] = $bereich;
      }
      $gruppen = [];
      foreach ($reihenfolge as $g) {
          if (isset($nachGruppe[$g])) {
              $gruppen[$g] = $nachGruppe[$g];
              unset($nachGruppe[$g]);
          }
      }
      $gruppen += $nachGruppe;   // was keiner bekannten Gruppe angehoert, faellt hinten an
    ?>

    <?php foreach ($gruppen as $gruppenName => $bereiche): ?>
    <section class="bereichsgruppe">
      <h2><?= h($gruppenName) ?></h2>
      <div class="kacheln">
        <?php foreach ($bereiche as $name => $bereich): ?>
        <a class="kachel" href="/admin/edit.php?bereich=<?= attr($name) ?>">
          <span class="kachel-titel"><?= h($bereich['titel']) ?></span>
          <span class="kachel-text"><?= h($bereich['beschreibung']) ?></span>
          <span class="kachel-pfeil" aria-hidden="true">→</span>
        </a>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endforeach; ?>

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
