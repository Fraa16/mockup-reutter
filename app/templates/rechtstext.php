<?php
/**
 * Gemeinsames Template der vier Rechtsseiten — Impressum, Datenschutz, AGB
 * und Widerruf. Nachgebaut aus export/project/Impressum.dc.html; die anderen
 * beiden Entwuerfe des Bundles sind bis auf die Abschnittsnamen identisch,
 * deshalb hier ein Template statt vier.
 *
 * Aufbau je Abschnitt: entweder fertiger Text (was belegt ist, steht drin)
 * oder ein sichtbar markierter Platzhalter. Verbindliche Rechtstexte kommen
 * vom Generator des Kunden, nicht von uns — das rote Band oben sagt das auch
 * dem Kunden, solange die Seite im Aufbau ist.
 *
 * @var array<string,mixed> $seite
 * @var string              $pfad
 */
$s = site();

/* Die vier Rechtsseiten verweisen aufeinander. Die Liste steht hier und nicht
   im Inhalt, weil sie zur Seitenstruktur gehoert, nicht zum Text. */
$rechtsseiten = [
    '/impressum/'   => 'Impressum',
    '/datenschutz/' => 'Datenschutz',
    '/agb/'         => 'AGB',
    '/widerruf/'    => 'Widerruf',
];

$abschnitte = get($seite, 'abschnitte', []);
$imAufbau   = (bool) get($seite, 'im_aufbau', true);

/* Der Weg dorthin — dieselbe Liste, die oben sichtbar steht. */
$jsonld = [seo_jsonld_brotkrumen([
    ['label' => 'Startseite', 'ziel' => '/'],
    ['label' => get($seite, 'titel')],
])];

partial('kopf', [
    'titel'        => get($seite, 'seo.titel'),
    'beschreibung' => get($seite, 'seo.beschreibung'),
    'aktiv'        => '',
    'jsonld'       => $jsonld,
]);
?>

<!-- Kopf -->
<section class="recht-hero">
  <div class="accent-bar" aria-hidden="true"></div>
  <div class="wrap">
    <?php partial('brotkrumen', ['pfade' => [
        ['label' => 'Startseite', 'ziel' => '/'],
        ['label' => get($seite, 'titel')],
    ]]); ?>
    <div class="recht-hero-grid">
      <div>
        <div class="kicker"><?= swash() ?><span class="label">Rechtliches</span></div>
        <h1><?= h(get($seite, 'titel')) ?></h1>
        <p class="lead"><?= h(get($seite, 'lead')) ?></p>
      </div>
      <div class="recht-meta">
        <div class="meta-titel">Stand</div>
        <div class="meta-wert"><?= h(get($seite, 'stand', 'noch offen')) ?></div>
        <nav class="recht-chips" aria-label="Weitere Rechtsseiten">
          <?php foreach ($rechtsseiten as $ziel => $label): ?>
            <?php if ($ziel === $pfad): ?>
              <span class="recht-chip ist-aktiv" aria-current="page"><?= h($label) ?></span>
            <?php else: ?>
              <a class="recht-chip" href="<?= attr($ziel) ?>"><?= h($label) ?></a>
            <?php endif; ?>
          <?php endforeach; ?>
        </nav>
      </div>
    </div>
  </div>
</section>

<?php if ($imAufbau): ?>
<!-- Hinweisband, solange die Seite Platzhalter traegt -->
<section class="recht-band">
  <div class="wrap">
    <?= swash() ?>
    <p><?= h(get($seite, 'band_hinweis')) ?></p>
  </div>
</section>
<?php endif; ?>

<!-- Inhalt -->
<section class="recht-inhalt">
  <div class="wrap">
    <?php /* Das Verzeichnis stand auf dem Handy in voller Laenge ueber dem
            Text — bei zehn bis vierzehn Abschnitten anderthalb Bildschirme
            Navigation, bevor der erste Satz kommt. Deshalb ein <details>:
            am Schreibtisch bleibt es offen und klebt in der Spalte, auf dem
            Handy klappt main.js es zu. Ohne JavaScript steht es offen — also
            genau so, wie es vorher immer war. */ ?>
    <nav class="recht-verzeichnis" aria-label="Inhaltsverzeichnis">
      <details id="recht-verzeichnis" open>
        <summary class="verzeichnis-titel">Inhalt</summary>
        <ol>
          <?php foreach ($abschnitte as $i => $a): ?>
          <li>
            <a href="#abschnitt-<?= $i + 1 ?>">
              <span class="verzeichnis-num"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
              <span><?= h($a['titel']) ?></span>
            </a>
          </li>
          <?php endforeach; ?>
        </ol>
      </details>
    </nav>

    <div class="recht-text">
      <?php foreach ($abschnitte as $i => $a): ?>
      <section class="recht-abschnitt" id="abschnitt-<?= $i + 1 ?>">
        <div class="abschnitt-kopf">
          <span class="abschnitt-num"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
          <h2><?= h($a['titel']) ?></h2>
        </div>

        <?php foreach ($a['absaetze'] ?? [] as $p): ?>
        <p><?= h($p) ?></p>
        <?php endforeach; ?>

        <?php if (!empty($a['zeilen'])): ?>
        <?php /* Anschrift und Kontaktdaten stehen als Zeilenblock, nicht als
                Fliesstext — so sind sie auch maschinell lesbar. */ ?>
        <address class="abschnitt-zeilen">
          <?php foreach ($a['zeilen'] as $z): ?>
          <span><?= h($z) ?></span>
          <?php endforeach; ?>
        </address>
        <?php endif; ?>

        <?php if (!empty($a['liste'])): ?>
        <ul class="abschnitt-liste">
          <?php foreach ($a['liste'] as $l): ?>
          <li><?= swash() ?><span><?= h($l) ?></span></li>
          <?php endforeach; ?>
        </ul>
        <?php endif; ?>

        <?php if (!empty($a['platzhalter'])): ?>
        <?php /* Sichtbar leer statt stillschweigend unvollstaendig. */ ?>
        <div class="recht-platzhalter">
          <?= swash() ?>
          <div>
            <div class="platzhalter-titel"><?= h($a['platzhalter']['titel']) ?></div>
            <p><?= h($a['platzhalter']['hinweis']) ?></p>
          </div>
        </div>
        <?php endif; ?>

        <?php foreach ($a['nachtext'] ?? [] as $p): ?>
        <p><?= h($p) ?></p>
        <?php endforeach; ?>
      </section>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php partial('fuss', ['zeigeFormular' => false, 'zeigeBewertungen' => false]); ?>
