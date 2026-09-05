<?php
/**
 * Transport & Abschleppdienst — /leistungen/transport-abschleppdienst/.
 *
 * Steht nicht im Design-Bundle; die Leistung kam am 04.09.2026 vom Kunden
 * dazu. Bewusst aus vorhandenen Bausteinen gebaut — Leistungs-Hero,
 * Grenzen-Raster, Preisband, FAQ, Verwandte — damit kein eigenes CSS noetig
 * ist und die Seite sich nicht von den anderen sieben absetzt.
 *
 * Als einzige Leistung ohne Hotspot: Abschleppen und Transportieren lassen
 * sich nicht an einer Stelle des Fahrzeugs verorten. Siehe home.php.
 *
 * @var array<string,mixed> $seite
 * @var array<string,mixed> $leistung  Eintrag aus leistungen.json
 */
$s = site();

$jsonld = [
    seo_jsonld_betrieb(),
    seo_jsonld_brotkrumen([
        ['label' => 'Startseite', 'ziel' => '/'],
        ['label' => 'Leistungen', 'ziel' => '/leistungen/'],
        ['label' => $leistung['seitenname']],
    ]),
];

partial('kopf', [
    'titel'        => get($seite, 'seo.titel'),
    'beschreibung' => get($seite, 'seo.beschreibung'),
    'jsonld'       => $jsonld,
    'og_bild'      => get($seite, 'hero.bild'),
    'og_bild_alt'  => get($seite, 'hero.bild_alt'),
    'aktiv'        => 'leistungen',
    'lcp_bild'     => get($seite, 'hero.bild'),
    'lcp_sizes'    => '(max-width: 980px) 100vw, 50vw',
]);
?>

<!-- Hero -->
<section class="leistung-hero">
  <div class="hero-bild">
    <?= bild(get($seite, 'hero.bild'), get($seite, 'hero.bild_alt'), [
        'class' => 'slot-img',
        'sizes' => '(max-width: 980px) 100vw, 50vw',
        'fetchpriority' => 'high',
    ]) ?>
  </div>
  <div class="scrim" aria-hidden="true"></div>
  <div class="accent-bar" aria-hidden="true"></div>
  <div class="wrap">
    <?php partial('brotkrumen', ['pfade' => [
        ['label' => 'Startseite', 'ziel' => '/'],
        ['label' => 'Leistungen', 'ziel' => '/leistungen/'],
        ['label' => $leistung['seitenname']],
    ]]); ?>
    <div class="leistung-hero-grid">
      <div>
        <div class="kicker"><?= swash() ?><span class="label"><?= h(get($seite, 'hero.kicker')) ?></span></div>
        <h1><?= h(get($seite, 'hero.titel')) ?></h1>
        <p class="lead"><?= h(get($seite, 'hero.lead')) ?></p>
      </div>
      <?php /* Beim Abschleppen ist der Anruf der Normalfall, nicht das
              Formular — wer liegen bleibt, tippt nicht. Deshalb steht die
              Nummer hier oben und nicht erst im Fuss. */ ?>
      <div class="leistung-hero-cta">
        <a href="tel:<?= attr(get($s, 'kontakt.telefon_link')) ?>" class="btn btn-red">
          <?= h(get($s, 'kontakt.telefon')) ?> <span class="btn-arrow" aria-hidden="true">→</span>
        </a>
        <a href="#kurzanfrage" class="btn btn-outline"><?= h(get($seite, 'hero.cta')) ?></a>
      </div>
    </div>
  </div>
</section>

<?php /* Zwei Straenge, gleicher Aufbau: links die Einordnung, rechts die
        Punkte. Dasselbe Raster wie 'Was Ozon nicht kann' und der
        Trockeneis-Abschnitt. */ ?>
<!-- Abschleppdienst -->
<section class="trockeneis">
  <div class="wrap">
    <div class="grenzen-grid">
      <div class="grenzen-intro">
        <div class="kicker"><?= swash() ?><span class="label"><?= h(get($seite, 'abschleppen.kicker')) ?></span></div>
        <h2><?= h(get($seite, 'abschleppen.titel')) ?></h2>
        <p><?= h(get($seite, 'abschleppen.lead')) ?></p>
      </div>
      <div class="grenzen-punkte">
        <?php foreach (get($seite, 'abschleppen.punkte', []) as $p): ?>
        <div class="grenzen-punkt">
          <h3><?= h($p['titel']) ?></h3>
          <p><?= h($p['text']) ?></p>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- Transporte -->
<section class="grenzen">
  <div class="wrap">
    <div class="grenzen-grid">
      <div class="grenzen-intro">
        <div class="kicker"><?= swash() ?><span class="label"><?= h(get($seite, 'transporte.kicker')) ?></span></div>
        <h2><?= h(get($seite, 'transporte.titel')) ?></h2>
        <p><?= h(get($seite, 'transporte.lead')) ?></p>
      </div>
      <div class="grenzen-punkte">
        <?php foreach (get($seite, 'transporte.punkte', []) as $p): ?>
        <div class="grenzen-punkt">
          <h3><?= h($p['titel']) ?></h3>
          <p><?= h($p['text']) ?></p>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
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
        <details class="faq-eintrag" name="faq-transport"<?= $i === 0 ? ' open' : '' ?>>
          <summary>
            <span class="faq-frage"><?= h($f['frage']) ?></span>
            <span class="faq-zeichen" aria-hidden="true"></span>
          </summary>
          <div class="faq-antwort">
            <p><?= h($f['antwort']) ?></p>
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
