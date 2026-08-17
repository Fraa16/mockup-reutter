<?php
/**
 * Lackierarbeiten — nachgebaut aus
 * export/project/Leistung Lackierarbeiten.dc.html.
 *
 * Kernmodul: der Fingernagel-Tiefentest. Drei Zustaende, dazu ein Schnitt
 * durch die Lackschichten, in dem die rote Schadenszone mitwandert.
 *
 * Abweichung vom Entwurf: der Abschnitt #beklebung gehoert nicht dazu. Er
 * kommt aus der Angebotsklaerung — Fahrzeugbeklebung bekommt keinen eigenen
 * Reiter, sondern hier eine Sprungmarke, auf die spaeter die alte
 * /beklebung.html per 301 zeigt.
 *
 * @var array<string,mixed> $seite
 * @var array<string,mixed> $leistung
 */
$s      = site();
$tiefen = get($seite, 'tiefen', []);

/* Der Hero traegt hier kein Foto, sondern die Musterreihe — deshalb kein
   lcp_bild. Vorgeladen wird nichts, es gibt nichts vorzuladen. */
/* Strukturierte Daten: der Betrieb liegt auf der Startseite, hier stehen die
   Leistung selbst, der Weg dorthin und die Fragen, die auf der Seite sichtbar
   sind. Google verlangt, dass jede ausgezeichnete Frage auch im Text steht —
   deshalb kommt beides aus derselben Datei. */
$jsonld = [
    seo_jsonld_leistung($leistung, (string) get($seite, 'seo.beschreibung')),
    seo_jsonld_brotkrumen([
        ['label' => 'Startseite', 'ziel' => '/'],
        ['label' => 'Leistungen', 'ziel' => '/leistungen/'],
        ['label' => $leistung['titel']],
    ]),
    seo_jsonld_faq(get($seite, 'faq.fragen', [])),
];

partial('kopf', [
    'titel'        => get($seite, 'seo.titel'),
    'beschreibung' => get($seite, 'seo.beschreibung'),
    'jsonld'       => $jsonld,
    'og_bild'      => get($seite, 'hero.bild'),
    'og_bild_alt'  => get($seite, 'hero.bild_alt'),
    'aktiv'        => 'leistungen',
]);
?>

<!-- Hero -->
<section class="leistung-hero ist-lack">
  <div class="accent-bar" aria-hidden="true"></div>
  <div class="wrap">
    <?php partial('brotkrumen', ['pfade' => [
        ['label' => 'Startseite', 'ziel' => '/'],
        ['label' => 'Leistungen', 'ziel' => '/leistungen/'],
        ['label' => 'Lackierarbeiten'],
    ]]); ?>
    <div class="leistung-hero-copy">
      <div class="kicker"><?= swash() ?><span class="label"><?= h(get($seite, 'hero.kicker')) ?></span></div>
      <h1><?= h(get($seite, 'hero.titel')) ?></h1>
      <p class="lead"><?= h(get($seite, 'hero.lead')) ?></p>
    </div>

    <div class="lack-hero-fuss">
      <?php /* Die Musterreihe ist reine Illustration — fuenf Mischungen aus
              derselben Farbnummer. Fuer Screenreader traegt sie nichts bei,
              was der Text daneben nicht schon sagt. */ ?>
      <div class="lack-muster">
        <div class="muster-reihe" aria-hidden="true">
          <?php foreach (get($seite, 'hero.swatches', []) as $sw): ?>
          <div class="muster">
            <span class="muster-farbe" style="height:<?= (int) $sw['hoehe'] ?>px;background:<?= attr($sw['farbe']) ?>"></span>
            <span class="muster-label"><?= h($sw['label']) ?></span>
          </div>
          <?php endforeach; ?>
        </div>
        <p class="muster-text"><?= h(get($seite, 'hero.swatch_text')) ?></p>
      </div>
      <div class="cta-row">
        <a href="#kurzanfrage" class="btn btn-red"><?= h(get($seite, 'hero.cta')) ?> <span class="btn-arrow" aria-hidden="true">→</span></a>
        <a href="tel:<?= attr(get($s, 'kontakt.telefon_link')) ?>" class="btn btn-outline"><?= h(get($s, 'kontakt.telefon')) ?></a>
      </div>
    </div>
  </div>
</section>

<!-- Fingernagel-Tiefentest -->
<section class="tiefentest">
  <div class="wrap">
    <div class="section-head light">
      <div style="max-width:700px">
        <div class="kicker"><?= swash() ?><span class="label"><?= h(get($seite, 'tiefentest.kicker')) ?></span></div>
        <h2><?= h(get($seite, 'tiefentest.titel')) ?></h2>
      </div>
      <p class="desc"><?= h(get($seite, 'tiefentest.beschreibung')) ?></p>
    </div>

    <div id="tiefentest">
      <div class="tiefen-wahl" role="group" aria-label="Kratzertiefe wählen">
        <?php foreach ($tiefen as $i => $t): ?>
        <button type="button" class="tiefe<?= $i === 1 ? ' active' : '' ?>" data-tiefe="<?= $i ?>"
                aria-pressed="<?= $i === 1 ? 'true' : 'false' ?>" aria-controls="tiefen-tafel">
          <span class="tiefe-kopf">
            <span class="tiefe-num"><?= h($t['num']) ?></span>
            <span class="tiefe-urteil"><?= h($t['urteil']) ?></span>
          </span>
          <span class="tiefe-titel"><?= h($t['titel']) ?></span>
          <span class="tiefe-gefuehl"><?= h($t['gefuehl']) ?></span>
        </button>
        <?php endforeach; ?>
      </div>

      <div class="tiefen-panel">
        <div class="lack-schnitt">
          <div class="schnitt-titel"><?= h(get($seite, 'tiefentest.schema_titel')) ?></div>
          <?php /* Die Schichthoehen sind Design und stehen im CSS. Nur die
                  Breite der Schadenszone wechselt — sie kommt aus den
                  Tiefendaten und wird vom Skript gesetzt. */ ?>
          <div class="lack-schichten">
            <?php foreach (get($seite, 'tiefentest.schichten', []) as $sch): ?>
            <div class="lack-schicht lack-<?= attr($sch['rolle']) ?>">
              <span class="schicht-name"><?= h($sch['name']) ?></span>
              <?php if ($sch['zusatz'] !== ''): ?>
                <span class="schicht-zusatz"><?= h($sch['zusatz']) ?></span>
              <?php endif; ?>
              <?php if ($sch['rolle'] !== 'blech'): ?>
                <?php $breite = (int) ($tiefen[1]['balken_' . $sch['rolle']] ?? 0); ?>
                <span class="schadenszone<?= $breite === 0 ? ' ist-leer' : '' ?>" data-schicht="<?= attr($sch['rolle']) ?>"
                      style="width:<?= $breite ?>%" aria-hidden="true"></span>
              <?php endif; ?>
            </div>
            <?php endforeach; ?>
          </div>
          <p class="schnitt-hinweis"><?= h(get($seite, 'tiefentest.hinweis')) ?></p>
        </div>

        <?php /* Alle drei Tafeln stehen im HTML, JS blendet um. Die
                Balkenbreiten haengen an der Tafel, damit sie neben dem
                zugehoerigen Text gepflegt werden. */ ?>
        <div class="vorgehen" id="tiefen-tafel">
          <?php foreach ($tiefen as $i => $t): ?>
          <div class="vorgehen-tafel<?= $i === 1 ? ' is-active' : '' ?>"
               data-klarlack="<?= (int) $t['balken_klarlack'] ?>"
               data-basis="<?= (int) $t['balken_basis'] ?>"
               data-fueller="<?= (int) $t['balken_fueller'] ?>">
            <div class="vorgehen-kicker"><?= h(get($seite, 'tiefentest.vorgehen_kicker')) ?></div>
            <h3><?= h($t['aktion']) ?></h3>
            <p><?= h($t['text']) ?></p>
            <dl class="vorgehen-werte">
              <div><dt>Standzeit</dt><dd><?= h($t['standzeit']) ?></dd></div>
              <div><dt>Lack nötig</dt><dd><?= h($t['lack_noetig']) ?></dd></div>
              <div>
                <dt>Zuständig</dt>
                <dd class="ist-rot">
                  <?php if ($t['zustaendig_ziel'] !== ''): ?>
                    <a href="<?= attr($t['zustaendig_ziel']) ?>"><?= h($t['zustaendig']) ?></a>
                  <?php else: ?>
                    <?= h($t['zustaendig']) ?>
                  <?php endif; ?>
                </dd>
              </div>
            </dl>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Farbtonfindung -->
<section class="farbton">
  <div class="wrap">
    <div class="farbton-grid">
      <div class="farbton-intro">
        <div class="kicker"><?= swash() ?><span class="label"><?= h(get($seite, 'farbton.kicker')) ?></span></div>
        <h2><?= h(get($seite, 'farbton.titel')) ?></h2>
        <p><?= h(get($seite, 'farbton.lead')) ?></p>
        <div class="farbton-bild">
          <?= bild(get($seite, 'farbton.bild'), get($seite, 'farbton.bild_alt'), [
              'class' => 'slot-img',
              'sizes' => '(max-width: 980px) 92vw, 30vw',
          ]) ?>
        </div>
      </div>
      <ol class="farbton-schritte">
        <?php foreach (get($seite, 'farbton.schritte', []) as $sch): ?>
        <li class="farbton-schritt<?= !empty($sch['betont']) ? ' ist-betont' : '' ?>">
          <span class="schritt-num"><?= h($sch['num']) ?></span>
          <h3><?= h($sch['titel']) ?></h3>
          <p><?= h($sch['text']) ?></p>
        </li>
        <?php endforeach; ?>
      </ol>
    </div>
  </div>
</section>

<!-- Fahrzeugbeklebung — Sprungmarke fuer die alte /beklebung.html -->
<section class="beklebung" id="beklebung">
  <div class="wrap">
    <div class="beklebung-grid">
      <div class="beklebung-intro">
        <div class="kicker"><?= swash() ?><span class="label"><?= h(get($seite, 'beklebung.kicker')) ?></span></div>
        <h2><?= h(get($seite, 'beklebung.titel')) ?></h2>
        <p><?= h(get($seite, 'beklebung.lead')) ?></p>
        <a href="#kurzanfrage" class="btn btn-red"><?= h(get($seite, 'beklebung.cta')) ?> <span class="btn-arrow" aria-hidden="true">→</span></a>
      </div>
      <div class="beklebung-varianten">
        <?php foreach (get($seite, 'beklebung.varianten', []) as $v): ?>
        <div class="beklebung-karte">
          <h3><?= h($v['titel']) ?></h3>
          <p><?= h($v['text']) ?></p>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <p class="beklebung-hinweis"><?= swash() ?><span><?= h(get($seite, 'beklebung.hinweis')) ?></span></p>
  </div>
</section>

<!-- Vorher / Nachher -->
<section class="ba-section leistung-vergleich ist-hell">
  <div class="wrap">
    <div class="ba-head">
      <div>
        <div class="kicker"><?= swash() ?><span class="label"><?= h(get($seite, 'vergleich.kicker')) ?></span></div>
        <h2><?= h(get($seite, 'vergleich.titel')) ?></h2>
      </div>
      <p class="ba-desc"><?= h(get($seite, 'vergleich.beschreibung')) ?></p>
    </div>
    <?php partial('vergleich', ['v' => $seite['vergleich']]); ?>
    <p class="ba-hint">
      <?= h(get($seite, 'vergleich.fussnote_vor')) ?><a href="/galerie/"><?= h(get($seite, 'vergleich.fussnote_link')) ?></a>.
    </p>
  </div>
</section>

<!-- Preisrahmen -->
<section class="preis-band">
  <div class="band-schraege" aria-hidden="true"></div>
  <div class="wrap">
    <div>
      <div class="preis-kicker"><?= h(get($seite, 'preis.kicker')) ?></div>
      <h2><?= h(get($seite, 'preis.titel')) ?></h2>
      <p><?= h(get($seite, 'preis.text')) ?></p>
    </div>
    <a href="#kurzanfrage" class="btn btn-black"><?= h(get($seite, 'preis.cta')) ?></a>
  </div>
</section>

<!-- FAQ -->
<section class="faq-section">
  <div class="wrap">
    <div class="faq-grid">
      <div class="faq-intro">
        <div class="kicker"><?= swash() ?><span class="label"><?= h(get($seite, 'faq.kicker')) ?></span></div>
        <h2><?= h(get($seite, 'faq.titel')) ?></h2>
        <p class="faq-hinweis">
          <?= h(get($seite, 'faq.hinweis_vor')) ?><a href="tel:<?= attr(get($s, 'kontakt.telefon_link')) ?>"><?= h(get($s, 'kontakt.telefon')) ?></a>
        </p>
      </div>
      <div class="faq-liste">
        <?php foreach (get($seite, 'faq.fragen', []) as $i => $f): ?>
        <details class="faq-eintrag" name="faq-lack"<?= $i === 0 ? ' open' : '' ?>>
          <summary>
            <span class="faq-frage"><?= h($f['frage']) ?></span>
            <span class="faq-zeichen" aria-hidden="true"></span>
          </summary>
          <div class="faq-antwort">
            <p>
              <?= h($f['antwort']) ?>
              <?php if (isset($f['link'])): ?>
                <a href="<?= attr($f['link']['ziel']) ?>"><?= h($f['link']['text']) ?></a>.
              <?php endif; ?>
            </p>
          </div>
        </details>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- Verwandte Leistungen -->
<section class="verwandt ist-grau">
  <div class="wrap">
    <h2><?= h(get($seite, 'verwandt.titel')) ?></h2>
    <div class="verwandt-grid">
      <?php foreach (get($seite, 'verwandt.karten', []) as $k): ?>
      <?php
        $ziel = null;
        foreach (leistungen_mit_seite() as $l) {
            if ($l['slug'] === $k['slug']) { $ziel = $l; break; }
        }
        if ($ziel === null) { continue; }
      ?>
      <a class="verwandt-karte" href="/leistungen/<?= attr($ziel['slug']) ?>/">
        <div class="verwandt-kicker"><?= h($k['kicker']) ?></div>
        <div class="verwandt-titel"><?= h($ziel['seitenname']) ?></div>
        <div class="verwandt-text"><?= h($k['text']) ?></div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php partial('fuss', ['ctaUeberschrift' => get($seite, 'cta_ueberschrift')]); ?>
