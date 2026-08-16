<?php
/**
 * Galerie — nachgebaut aus export/project/Galerie.dc.html.
 *
 * Zwei Teile: drei Fallstudien mit Vergleichsregler (Layout wechselt die
 * Seite) und darunter das Bildraster mit Filter.
 *
 * Der Filter arbeitet ohne JavaScript nicht — ohne Skript stehen einfach
 * alle zwanzig Bilder da, was der sinnvolle Grundzustand ist. Das
 * Vergroessern eines Bildes ist ein Rasterwechsel, kein Overlay: die Kachel
 * belegt dann vier Felder statt einem.
 *
 * @var array<string,mixed> $seite
 */
$s      = site();
$bilder = get($seite, 'raster.bilder', []);

/* Kategorien in der Reihenfolge ihres ersten Auftretens, mit Anzahl. So
   steht die Filterleiste nie im Widerspruch zu den Kacheln darunter. */
$kategorien = [];
foreach ($bilder as $b) {
    $kategorien[$b['kategorie']] = ($kategorien[$b['kategorie']] ?? 0) + 1;
}

partial('kopf', [
    'titel'        => get($seite, 'seo.titel'),
    'beschreibung' => get($seite, 'seo.beschreibung'),
    'aktiv'        => 'galerie',
]);
?>

<!-- Hero -->
<section class="galerie-hero">
  <div class="accent-bar" aria-hidden="true"></div>
  <div class="wrap">
    <?php partial('brotkrumen', ['pfade' => [
        ['label' => 'Startseite', 'ziel' => '/'],
        ['label' => 'Galerie'],
    ]]); ?>
    <div class="galerie-hero-grid">
      <div>
        <div class="kicker"><?= swash() ?><span class="label"><?= h(get($seite, 'hero.kicker')) ?></span></div>
        <h1><?= h(get($seite, 'hero.titel')) ?></h1>
        <p class="lead"><?= h(get($seite, 'hero.lead')) ?></p>
      </div>
      <div class="galerie-hero-cta">
        <div class="cta-titel"><?= h(get($seite, 'hero.cta_titel')) ?></div>
        <a href="/kontakt/#anfrage" class="btn btn-red"><?= h(get($seite, 'hero.cta')) ?> <span class="btn-arrow" aria-hidden="true">→</span></a>
      </div>
    </div>
  </div>
</section>

<!-- Drei Fallstudien -->
<section class="faelle">
  <div class="wrap">
    <div class="abschnitt-linie">
      <h2 class="label"><?= h(get($seite, 'faelle.kicker')) ?></h2>
      <span class="linie" aria-hidden="true"></span>
    </div>

    <?php foreach (get($seite, 'faelle.eintraege', []) as $i => $f): ?>
    <?php
      $ziel = null;
      foreach (leistungen_mit_seite() as $l) {
          if ($l['slug'] === $f['slug']) { $ziel = $l; break; }
      }
    ?>
    <?php /* Jeder zweite Fall dreht die Seiten — Bild und Text tauschen. */ ?>
    <article class="fall<?= $i % 2 === 1 ? ' ist-gedreht' : '' ?>">
      <div class="fall-bild">
        <?php partial('vergleich', ['v' => $f['vergleich']]); ?>
      </div>
      <div class="fall-text">
        <div class="fall-kicker"><?= h($f['kicker']) ?></div>
        <h3><?= h($f['titel']) ?></h3>
        <p><?= h($f['text']) ?></p>
        <dl class="fall-werte">
          <?php foreach ($f['werte'] as $w): ?>
          <div><dt><?= h($w['label']) ?></dt><dd><?= h($w['wert']) ?></dd></div>
          <?php endforeach; ?>
        </dl>
        <?php if ($ziel !== null): ?>
        <a class="fall-link" href="/leistungen/<?= attr($ziel['slug']) ?>/">
          <?= h(get($seite, 'faelle.link_text')) ?> <span aria-hidden="true">→</span>
        </a>
        <?php endif; ?>
      </div>
    </article>
    <?php endforeach; ?>
  </div>
</section>

<!-- Bildraster -->
<section class="bildraster">
  <div class="wrap">
    <div class="raster-kopf">
      <h2><?= h(get($seite, 'raster.titel')) ?></h2>
      <p><?= h(get($seite, 'raster.beschreibung')) ?></p>
    </div>

    <?php /* Ohne JavaScript bleibt die Leiste sichtbar, filtert aber nicht —
            dann stehen alle Bilder da. Das ist der brauchbare Grundzustand,
            deshalb blenden wir sie nicht vorsorglich aus. */ ?>
    <div class="raster-filter" id="galerie-filter" role="group" aria-label="Nach Leistung filtern">
      <button type="button" class="filter active" data-kategorie="" aria-pressed="true">
        <?= h(get($seite, 'raster.alle_label')) ?><span class="filter-zahl"><?= count($bilder) ?></span>
      </button>
      <?php foreach ($kategorien as $name => $anzahl): ?>
      <button type="button" class="filter" data-kategorie="<?= attr($name) ?>" aria-pressed="false">
        <?= h($name) ?><span class="filter-zahl"><?= (int) $anzahl ?></span>
      </button>
      <?php endforeach; ?>
    </div>

    <div class="raster-grid" id="galerie-raster">
      <?php foreach ($bilder as $i => $b): ?>
      <button type="button" class="kachel" data-kategorie="<?= attr($b['kategorie']) ?>" aria-pressed="false">
        <img src="<?= attr(upload($b['bild'])) ?>" alt="<?= attr($b['alt']) ?>"
             width="1448" height="1086" <?= $i < 8 ? '' : 'loading="lazy"' ?>>
        <span class="kachel-verlauf" aria-hidden="true"></span>
        <span class="kachel-kategorie"><?= swash() ?><span><?= h($b['kategorie']) ?></span></span>
        <span class="kachel-zoom" aria-hidden="true">groß</span>
      </button>
      <?php endforeach; ?>
    </div>
    <p class="raster-fussnote"><?= h(get($seite, 'raster.fussnote')) ?></p>
  </div>
</section>

<?php partial('fuss', ['ctaUeberschrift' => get($seite, 'cta_ueberschrift')]); ?>
