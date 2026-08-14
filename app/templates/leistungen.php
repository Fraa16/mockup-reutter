<?php
/**
 * Leistungen-Hub — nachgebaut aus export/project/Leistungen.dc.html.
 *
 * Kernmodul: der Leistungsindex. Sechs Zeilen links, ein mitlaufendes
 * Vorschaubild rechts, das beim Ueberfahren oder Fokussieren wechselt.
 *
 * Die Liste kommt aus leistungen.json ueber leistungen_mit_seite(); die
 * Zusaetze dieser Seite (Kurztext, Schlagworte, Vorschaubild) stehen in
 * leistungen-hub.json unter dem jeweiligen Slug.
 *
 * @var array<string,mixed> $seite
 */
$s = site();

/* Index und Zusatztexte zusammenfuehren. Leistungen ohne Hub-Eintrag fallen
   raus, statt halbfertig zu erscheinen. */
$zusatz    = get($seite, 'eintraege', []);
$leistungen = [];
foreach (leistungen_mit_seite() as $l) {
    if (isset($zusatz[$l['slug']])) {
        $leistungen[] = $zusatz[$l['slug']] + $l;
    }
}
usort($leistungen, static fn(array $a, array $b): int => strcmp($a['num'], $b['num']));

partial('kopf', [
    'titel'        => get($seite, 'seo.titel'),
    'beschreibung' => get($seite, 'seo.beschreibung'),
    'aktiv'        => 'leistungen',
    'lcp_bild'     => get($seite, 'hero.bild'),
]);
?>

<!-- Hero -->
<section class="hub-hero">
  <div class="hero-bild">
    <img class="slot-img" src="<?= attr(upload(get($seite, 'hero.bild'))) ?>"
         alt="<?= attr(get($seite, 'hero.bild_alt')) ?>" width="1448" height="1086" fetchpriority="high">
  </div>
  <div class="scrim" aria-hidden="true"></div>
  <div class="accent-bar" aria-hidden="true"></div>
  <div class="wrap">
    <?php partial('brotkrumen', ['pfade' => [
        ['label' => 'Startseite', 'ziel' => '/'],
        ['label' => 'Leistungen'],
    ]]); ?>
    <div class="hub-hero-grid">
      <div>
        <div class="kicker"><?= swash() ?><span class="label"><?= h(get($seite, 'hero.kicker')) ?></span></div>
        <h1><?= h(get($seite, 'hero.titel')) ?></h1>
        <p class="lead"><?= h(get($seite, 'hero.lead')) ?></p>
      </div>
      <div class="hub-gilt">
        <div class="gilt-titel"><?= h(get($seite, 'hero.gilt_titel')) ?></div>
        <ul class="bullets">
          <?php foreach (get($seite, 'hero.gilt', []) as $g): ?>
          <li><?= swash() ?><span><?= h($g) ?></span></li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </div>
</section>

<!-- Leistungsindex -->
<section class="hub-index">
  <div class="wrap">
    <div class="hub-index-grid" id="leistungsindex">
      <?php /* Die Ueberschrift der Sektion steckt in den Zeilen selbst — jede
              ist ein h2 und zugleich der Link auf ihre Seite. */ ?>
      <div class="index-liste">
        <?php foreach ($leistungen as $i => $l): ?>
        <?php /* <a> ist im Inhaltsmodell durchlaessig — Ueberschrift und Absatz
                duerfen darin stehen, ein <span> duerfte sie nicht enthalten. */ ?>
        <a class="index-zeile<?= $i === 0 ? ' active' : '' ?>" href="/leistungen/<?= attr($l['slug']) ?>/" data-index="<?= $i ?>">
          <span class="index-num"><?= h($l['num']) ?></span>
          <div class="index-text">
            <h2><?= h($l['seitenname']) ?></h2>
            <p class="index-kurz"><?= h($l['kurz']) ?></p>
            <div class="index-schlagworte">
              <?php foreach ($l['schlagworte'] as $w): ?>
              <span class="schlagwort"><?= h($w) ?></span>
              <?php endforeach; ?>
            </div>
          </div>
          <span class="index-pfeil" aria-hidden="true">→</span>
        </a>
        <?php endforeach; ?>
        <div class="index-abschluss" aria-hidden="true"></div>
      </div>

      <?php /* Die Vorschau ist reine Begleitung: jede Zeile traegt ihren Namen
              schon im Text. Deshalb aus dem Vorlesefluss genommen. */ ?>
      <div class="index-vorschau" aria-hidden="true">
        <div class="vorschau-rahmen">
          <?php foreach ($leistungen as $i => $l): ?>
          <span class="vorschau-bild<?= $i === 0 ? ' is-active' : '' ?>" data-vorschau="<?= $i ?>">
            <img src="<?= attr(upload($l['bild'])) ?>" alt="" width="1448" height="1086"
                 <?= $i === 0 ? '' : 'loading="lazy"' ?>>
          </span>
          <?php endforeach; ?>
          <span class="vorschau-verlauf"></span>
          <span class="vorschau-titel">
            <?php foreach ($leistungen as $i => $l): ?>
            <span class="vorschau-zeile<?= $i === 0 ? ' is-active' : '' ?>" data-vorschau-titel="<?= $i ?>">
              <span class="vorschau-kicker"><?= h($l['num']) ?> — <?= h($l['tag']) ?></span>
              <span class="vorschau-name"><?= h($l['seitenname']) ?></span>
            </span>
            <?php endforeach; ?>
          </span>
        </div>
        <p class="vorschau-hinweis"><?= h(get($seite, 'index.hinweis')) ?></p>
      </div>
    </div>
  </div>
</section>

<!-- Kombinationen -->
<section class="kombinationen">
  <div class="wrap">
    <div class="section-head light">
      <div style="max-width:700px">
        <div class="kicker"><?= swash() ?><span class="label"><?= h(get($seite, 'kombinationen.kicker')) ?></span></div>
        <h2><?= h(get($seite, 'kombinationen.titel')) ?></h2>
      </div>
      <p class="desc"><?= h(get($seite, 'kombinationen.beschreibung')) ?></p>
    </div>
    <div class="kombi-grid">
      <?php foreach (get($seite, 'kombinationen.faelle', []) as $f): ?>
      <div class="kombi-karte">
        <div class="kombi-kicker"><?= h($f['kicker']) ?></div>
        <h3><?= h($f['titel']) ?></h3>
        <p><?= h($f['text']) ?></p>
        <div class="kombi-links">
          <?php foreach ($f['slugs'] as $j => $slug): ?>
          <a href="/leistungen/<?= attr($slug) ?>/"><?= h($f['labels'][$j] ?? $slug) ?></a>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Was wir nicht machen -->
<section class="hub-grenzen">
  <div class="wrap">
    <div class="grenzen-grid">
      <div class="grenzen-intro">
        <h2><?= h(get($seite, 'grenzen.titel')) ?></h2>
        <p><?= h(get($seite, 'grenzen.lead')) ?></p>
      </div>
      <div class="grenzen-punkte">
        <?php foreach (get($seite, 'grenzen.punkte', []) as $p): ?>
        <div class="grenzen-punkt">
          <h3><?= h($p['titel']) ?></h3>
          <p><?= h($p['text']) ?></p>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<?php partial('fuss', ['ctaUeberschrift' => get($seite, 'cta_ueberschrift')]); ?>
