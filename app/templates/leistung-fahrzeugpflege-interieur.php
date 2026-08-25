<?php
/**
 * Fahrzeugpflege Interieur — nachgebaut aus
 * export/project/Leistung Interieur.dc.html.
 *
 * Kernmodul: der Sechs-Zonen-Waehler. Zone links auswaehlen, rechts erscheint
 * Verfahren, Dauer und die Grenze — was dort eben nicht geht.
 *
 * @var array<string,mixed> $seite
 * @var array<string,mixed> $leistung
 */
$s     = site();
$zonen = get($seite, 'zonen', []);

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
    'lcp_bild'     => get($seite, 'hero.bild'),
    'lcp_sizes'    => '(max-width: 980px) 100vw, 50vw',
]);
?>

<!-- Hero -->
<section class="leistung-hero ist-halb">
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
        ['label' => 'Interieur'],
    ]]); ?>
    <div class="leistung-hero-copy">
      <div class="kicker"><?= swash() ?><span class="label"><?= h(get($seite, 'hero.kicker')) ?></span></div>
      <h1><?= h(get($seite, 'hero.titel')) ?></h1>
      <p class="lead"><?= h(get($seite, 'hero.lead')) ?></p>
      <div class="cta-row">
        <a href="#kurzanfrage" class="btn btn-red"><?= h(get($seite, 'hero.cta')) ?> <span class="btn-arrow" aria-hidden="true">→</span></a>
        <a href="tel:<?= attr(get($s, 'kontakt.telefon_link')) ?>" class="btn btn-outline"><?= h(get($s, 'kontakt.telefon')) ?></a>
      </div>
      <div class="hero-kennzahlen">
        <?php foreach (get($seite, 'hero.kennzahlen', []) as $k): ?>
        <div class="hero-kennzahl">
          <div class="wert"><?= h($k['wert']) ?></div>
          <div class="label"><?= h($k['label']) ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- Sechs Zonen -->
<section class="zonen">
  <div class="wrap">
    <div class="section-head light">
      <div style="max-width:700px">
        <div class="kicker"><?= swash() ?><span class="label"><?= h(get($seite, 'zonen_sektion.kicker')) ?></span></div>
        <h2><?= h(get($seite, 'zonen_sektion.titel')) ?></h2>
      </div>
      <p class="desc"><?= h(get($seite, 'zonen_sektion.beschreibung')) ?></p>
    </div>

    <div class="zonen-panel" id="zonenkarte">
      <div class="zonen-liste" role="group" aria-label="Zone im Innenraum wählen">
        <?php foreach ($zonen as $i => $z): ?>
        <button type="button" class="zone<?= $i === 0 ? ' active' : '' ?>" data-zone="<?= $i ?>"
                aria-pressed="<?= $i === 0 ? 'true' : 'false' ?>" aria-controls="zonen-tafel">
          <span class="zone-num"><?= h($z['num']) ?></span>
          <span class="zone-text">
            <span class="zone-name"><?= h($z['name']) ?></span>
            <span class="zone-kurz"><?= h($z['kurz']) ?></span>
          </span>
          <span class="zone-zeit"><?= h($z['zeit']) ?></span>
        </button>
        <?php endforeach; ?>
      </div>

      <?php /* Alle sechs Tafeln stehen im HTML, JS blendet um. */ ?>
      <div class="zonen-detail" id="zonen-tafel">
        <?php foreach ($zonen as $i => $z): ?>
        <div class="zonen-tafel<?= $i === 0 ? ' is-active' : '' ?>">
          <div class="tafel-name"><?= h($z['kurz']) ?></div>
          <h3><?= h($z['name']) ?></h3>
          <p><?= h($z['text']) ?></p>
          <ul class="bullets">
            <?php foreach ($z['punkte'] as $punkt): ?>
            <li><?= swash() ?><span><?= h($punkt) ?></span></li>
            <?php endforeach; ?>
          </ul>
          <div class="zone-grenze">
            <div class="grenze-titel">Wo die Grenze liegt</div>
            <p>
              <?= h($z['grenze']) ?>
              <?php if (isset($z['grenze_link'])): ?>
                <a href="<?= attr($z['grenze_link']['ziel']) ?>"><?= h($z['grenze_link']['text']) ?></a>.
              <?php endif; ?>
            </p>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<?php /* Trockeneis steht bewusst zwischen den Zonen und der Trocknung: es
        ist ein Verfahren, kein Ort im Fahrzeug, und es erklaert, warum an
        manchen Stellen eben nicht nass gearbeitet wird. Aufbau wie der
        Grenzen-Block der anderen Leistungsseiten. */ ?>
<section class="trockeneis">
  <div class="wrap">
    <div class="grenzen-grid">
      <div class="grenzen-intro">
        <div class="kicker"><?= swash() ?><span class="label"><?= h(get($seite, 'trockeneis.kicker')) ?></span></div>
        <h2><?= h(get($seite, 'trockeneis.titel')) ?></h2>
        <p><?= h(get($seite, 'trockeneis.lead')) ?></p>
      </div>
      <div class="grenzen-punkte">
        <?php foreach (get($seite, 'trockeneis.punkte', []) as $pk): ?>
        <div class="grenzen-punkt">
          <h3><?= h($pk['titel']) ?></h3>
          <p><?= h($pk['text']) ?></p>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- Trocknung -->
<section class="trocknung">
  <div class="wrap">
    <div class="trocknung-grid">
      <div class="trocknung-intro">
        <div class="kicker"><?= swash() ?><span class="label"><?= h(get($seite, 'trocknung.kicker')) ?></span></div>
        <h2><?= h(get($seite, 'trocknung.titel')) ?></h2>
        <p><?= h(get($seite, 'trocknung.lead')) ?></p>
      </div>
      <?php /* Zeitstrahl als geordnete Liste — die Reihenfolge traegt hier
              tatsaechlich Bedeutung, anders als bei reiner Aufzaehlung. */ ?>
      <ol class="zeitstrahl">
        <?php foreach (get($seite, 'trocknung.schritte', []) as $sch): ?>
        <li class="zeitstrahl-schritt">
          <div class="schritt-zeit"><?= h($sch['zeit']) ?></div>
          <div class="schritt-inhalt">
            <h3><?= h($sch['titel']) ?></h3>
            <p><?= h($sch['text']) ?></p>
          </div>
        </li>
        <?php endforeach; ?>
      </ol>
    </div>
  </div>
</section>

<!-- Vorher / Nachher -->
<section class="ba-section leistung-vergleich">
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
<section class="faq-section ist-grau">
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
        <details class="faq-eintrag" name="faq-interieur"<?= $i === 0 ? ' open' : '' ?>>
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
<section class="verwandt">
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
