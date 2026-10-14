<?php
/**
 * Kontakt & Anfahrt — nachgebaut aus export/project/Kontakt.dc.html.
 *
 * Reihenfolge seit Oktober 2026: Anruf und WhatsApp ganz oben, dann Anfahrt
 * und Versicherung, das Formular ganz unten. Der Inhaber will angerufen
 * werden; das Formular bleibt nur fuer die, die lieber schreiben. Das
 * Kontaktband im Fuss ist abgeschaltet, weil dieselben Kacheln schon im
 * Kopf der Seite stehen.
 *
 * @var array<string,mixed> $seite
 */
$s = site();

$adresse   = get($s, 'firma.strasse') . ', ' . get($s, 'firma.plz') . ' ' . get($s, 'firma.ort');
$karte     = get($seite, 'anfahrt.karte', []);
$kartenBild = (string) ($karte['bild'] ?? '');

/* Auf der Kontaktseite steht der Betrieb ein zweites Mal — mit derselben
   @id. Suchmaschinen fuehren beides zusammen, und wer diese Seite direkt
   aufruft, bringt Adresse und Oeffnungszeiten trotzdem mit. */
$jsonld = [
    seo_jsonld_betrieb(),
    seo_jsonld_brotkrumen([
        ['label' => 'Startseite', 'ziel' => '/'],
        ['label' => 'Kontakt'],
    ]),
];

partial('kopf', [
    'titel'        => get($seite, 'seo.titel'),
    'beschreibung' => get($seite, 'seo.beschreibung'),
    'aktiv'        => 'kontakt',
    'jsonld'       => $jsonld,
]);
?>

<!-- Hero -->
<section class="kontakt-hero">
  <div class="accent-bar" aria-hidden="true"></div>
  <div class="wrap">
    <?php partial('brotkrumen', ['pfade' => [
        ['label' => 'Startseite', 'ziel' => '/'],
        ['label' => 'Kontakt'],
    ]]); ?>
    <div class="kontakt-hero-grid">
      <div>
        <div class="kicker"><?= swash() ?><span class="label"><?= h(get($seite, 'hero.kicker')) ?></span></div>
        <h1><?= h(get($seite, 'hero.titel')) ?></h1>
        <p class="lead"><?= h(get($seite, 'hero.lead')) ?></p>
      </div>
      <?php partial('kontakt-kacheln'); ?>
    </div>
  </div>
</section>

<!-- Anfahrt -->
<section class="anfahrt" id="anfahrt">
  <div class="wrap">
    <div class="anfahrt-grid">
      <div class="anfahrt-karte">
        <?php if ($kartenBild !== ''): ?>
          <?= bild($kartenBild, (string) ($karte['bild_alt'] ?? ''), [
              'class' => 'slot-img',
              'sizes' => '(max-width: 980px) 92vw, 45vw',
          ]) ?>
          <?php partial('karten-quelle', ['karte' => $karte]); ?>
        <?php else: ?>
          <?php /* Lieber ein sichtbarer Platzhalter als ein Foto an der Stelle,
                  an der eine Karte stehen soll. */ ?>
          <div class="karte-platzhalter"><?= h($karte['platzhalter_text'] ?? '') ?></div>
        <?php endif; ?>
      </div>
      <div>
        <div class="kicker"><?= swash() ?><span class="label"><?= h(get($seite, 'anfahrt.kicker')) ?></span></div>
        <h2><?= h(get($seite, 'anfahrt.titel')) ?></h2>

        <div class="anfahrt-adresse">
          <?= swash() ?>
          <div>
            <div class="adresse-titel"><?= h(get($seite, 'anfahrt.adresse_titel')) ?></div>
            <address>
              <?= h(get($s, 'firma.name')) ?><br>
              <?= h(get($s, 'firma.strasse')) ?><br>
              <?= h(get($s, 'firma.plz')) ?> <?= h(get($s, 'firma.ort')) ?>
            </address>
            <a class="karte-link" href="https://www.google.com/maps/search/?api=1&amp;query=<?= attr(rawurlencode(get($s, 'firma.name') . ', ' . $adresse)) ?>"
               rel="noopener noreferrer"><?= h($karte['link_text'] ?? 'Route planen') ?> <span aria-hidden="true">→</span></a>
          </div>
        </div>

        <div class="anfahrt-punkte">
          <?php foreach (get($seite, 'anfahrt.punkte', []) as $p): ?>
          <div class="anfahrt-punkt">
            <h3><?= h($p['titel']) ?></h3>
            <p><?= h($p['text']) ?></p>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Versicherung und Gutachten -->
<section class="versicherung-kontakt">
  <div class="wrap">
    <div class="grenzen-grid">
      <div class="grenzen-intro">
        <div class="kicker"><?= swash() ?><span class="label"><?= h(get($seite, 'versicherung.kicker')) ?></span></div>
        <h2><?= h(get($seite, 'versicherung.titel')) ?></h2>
        <p><?= h(get($seite, 'versicherung.lead')) ?></p>
      </div>
      <div class="grenzen-punkte">
        <?php foreach (get($seite, 'versicherung.punkte', []) as $p): ?>
        <div class="grenzen-punkt">
          <h3><?= h($p['titel']) ?></h3>
          <p><?= h($p['text']) ?></p>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- Anfrageformular -->
<?php /* Bewusst zuletzt und ohne Verweis von anderen Seiten: wer lieber
        schreibt, findet es hier, aber kein Knopf der Seite fuehrt mehr
        hierher. Die Seitenspalte zeigt daneben noch einmal den schnelleren
        Weg. */ ?>
<section class="anfrage-seite" id="anfrage">
  <div class="anfrage-schraege" aria-hidden="true"></div>
  <div class="wrap">
    <div class="anfrage-seite-grid">
      <div>
        <div class="kicker"><?= swash() ?><span class="label"><?= h(get($seite, 'formular.kicker')) ?></span></div>
        <h2 class="formular-titel"><?= h(get($seite, 'formular.titel')) ?></h2>
        <?php if (($einleitung = (string) get($seite, 'formular.text', '')) !== ''): ?>
        <p class="formular-text"><?= h($einleitung) ?></p>
        <?php endif; ?>
        <?php /* Nach einer fehlgeschlagenen Anfrage kommen die Eingaben zurueck
                ins Formular — partial() erbt den Gueltigkeitsbereich nicht,
                deshalb ausdruecklich weiterreichen. */ ?>
        <?php partial('anfrage-form', ['fehler' => $fehler ?? [], 'werte' => $werte ?? []]); ?>
      </div>

      <aside class="anfrage-seitenspalte">
        <div class="seiten-karte">
          <div class="karte-titel"><?= h(get($seite, 'ohne_formular.titel')) ?></div>
          <?php foreach (get($seite, 'ohne_formular.wege', []) as $w): ?>
          <?php
            /* WhatsApp-Ziel aus der Mobilnummer bilden — ein Link, kein
               eingebetteter Dienst. Beim Seitenaufruf geht nichts an Meta. */
            $ziel = $w['art'] === 'whatsapp' ? whatsapp_link() : anruf()['ziel'];
            $zusatz = $w['art'] === 'whatsapp'
                ? 'An ' . get($s, 'kontakt.mobil') . '. '
                : anruf()['anzeige'] . '. ';
          ?>
          <a class="karte-weg" href="<?= attr($ziel) ?>"<?= $w['art'] === 'whatsapp' ? ' target="_blank" rel="noopener noreferrer"' : '' ?>>
            <?= swash() ?>
            <span>
              <span class="weg-titel"><?= h($w['titel']) ?></span>
              <span class="weg-text"><?= h($zusatz . $w['text']) ?></span>
            </span>
          </a>
          <?php endforeach; ?>
        </div>

        <div class="seiten-karte">
          <div class="karte-titel"><?= h(get($seite, 'zeiten.titel')) ?></div>
          <?php
            /* Aus "09:00"/"17:00" wird "9 – 17 Uhr". Die Tage stehen schon in
               der Zeilenbeschriftung, sonst stuende dort "Montag – Freitag |
               Mo–Fr 9–17 Uhr". */
            $von  = ltrim((string) get($s, 'oeffnungszeiten.strukturiert.0.von', '09:00'), '0');
            $bis  = ltrim((string) get($s, 'oeffnungszeiten.strukturiert.0.bis', '17:00'), '0');
            $kurz = static fn (string $t): string => str_ends_with($t, ':00') ? substr($t, 0, -3) : $t;
            $spanne = $kurz($von) . ' – ' . $kurz($bis) . ' Uhr';
          ?>
          <dl class="zeiten-liste">
            <div><dt>Montag – Freitag</dt><dd><?= h($spanne) ?></dd></div>
            <div><dt>Samstag</dt><dd class="ist-zu">geschlossen</dd></div>
            <div><dt>Sonntag</dt><dd class="ist-zu">geschlossen</dd></div>
          </dl>
          <?php /* Zwei verschiedene Dinge: der Anruf vorher steht in den
                  Stammdaten, weil er zu den Zeiten gehoert; die Annahme nach
                  Termin ist eine Aussage dieser Seite. */ ?>
          <?php if (($vorlauf = (string) get($s, 'oeffnungszeiten.hinweis', '')) !== ''): ?>
          <p class="zeiten-hinweis"><?= h($vorlauf) ?></p>
          <?php endif; ?>
          <p class="zeiten-hinweis"><?= h(get($seite, 'zeiten.hinweis')) ?></p>
        </div>
      </aside>
    </div>
  </div>
</section>

<?php partial('fuss', ['zeigeFormular' => false]); ?>
