<?php
/**
 * Dellen & Hagelschaden — nachgebaut aus export/project/Leistung Dellen.dc.html.
 *
 * Bewusst ein eigenes Template, kein geteiltes: Die sechs Leistungsseiten sind
 * unterschiedlich aufgebaut und haben je ein eigenes interaktives Modul. Hier
 * ist es die Panel-Karte — Bauteil anklicken, Dellenzahl und Aufwand lesen.
 *
 * @var array<string,mixed> $seite     Inhalt aus leistung-dellen-hagelschaden.json
 * @var array<string,mixed> $leistung  Indexeintrag aus leistungen.json
 */
$s = site();

$panels = get($seite, 'panelkarte.panels', []);
$gesamt = array_sum(array_column($panels, 'anzahl'));

partial('kopf', [
    'titel'        => get($seite, 'seo.titel'),
    'beschreibung' => get($seite, 'seo.beschreibung'),
    'aktiv'        => 'leistungen',
    'lcp_bild'     => get($seite, 'hero.bild'),
]);
?>

<!-- Hero -->
<section class="leistung-hero">
  <div class="hero-bild">
    <img class="slot-img" src="<?= attr(upload(get($seite, 'hero.bild'))) ?>"
         alt="<?= attr(get($seite, 'hero.bild_alt')) ?>" width="1448" height="1086" fetchpriority="high">
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
      <div class="leistung-hero-cta">
        <?php /* Der Entwurf zeigt hier auf #anfrage, was dort die Sektion
                "Passt oft dazu" ist. Gemeint ist das Formular — also dorthin. */ ?>
        <a href="#kurzanfrage" class="btn btn-red">
          <?= h(get($seite, 'hero.cta')) ?> <span class="btn-arrow" aria-hidden="true">→</span>
        </a>
        <a href="tel:<?= attr(get($s, 'kontakt.telefon_link')) ?>" class="btn btn-outline"><?= h(get($s, 'kontakt.telefon')) ?></a>
      </div>
    </div>
  </div>
</section>

<!-- Machbarkeit -->
<section class="machbarkeit">
  <div class="wrap">
    <div class="abschnitt-linie">
      <span class="label"><?= h(get($seite, 'machbarkeit.kicker')) ?></span>
      <span class="linie" aria-hidden="true"></span>
    </div>
    <h2><?= h(get($seite, 'machbarkeit.titel')) ?></h2>
    <p class="lead"><?= h(get($seite, 'machbarkeit.lead')) ?></p>
    <div class="check-grid">
      <?php foreach (get($seite, 'machbarkeit.checks', []) as $c): ?>
      <div class="check">
        <div class="check-kopf">
          <span class="num"><?= h($c['num']) ?></span>
          <span class="urteil<?= !empty($c['hervorgehoben']) ? ' ist-wichtig' : '' ?>"><?= h($c['urteil']) ?></span>
        </div>
        <h3><?= h($c['titel']) ?></h3>
        <p><?= h($c['text']) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Panelkarte -->
<section class="panelkarte">
  <div class="wrap">
    <div class="section-head light">
      <div style="max-width:700px">
        <div class="kicker"><?= swash() ?><span class="label"><?= h(get($seite, 'panelkarte.kicker')) ?></span></div>
        <h2><?= h(get($seite, 'panelkarte.titel')) ?></h2>
      </div>
      <p class="desc"><?= h(get($seite, 'panelkarte.beschreibung')) ?></p>
    </div>

    <div class="panelkarte-panel" id="panelkarte">
      <div class="panelkarte-schema">
        <div class="panelkarte-kopf">
          <span class="bildunterschrift"><?= h(get($seite, 'panelkarte.bildunterschrift')) ?></span>
          <span class="gesamt"><?= h((string) $gesamt) ?> Dellen gesamt</span>
        </div>
        <?php /* Die Rasterposition ist Design und steht deshalb im Inline-Style,
                nicht im CMS. Inhalt sind Name, Zahl und die Kennwerte. */ ?>
        <div class="panel-raster" role="group" aria-label="Bauteile des Fahrzeugs">
          <?php foreach ($panels as $i => $p): ?>
          <button type="button" class="panel<?= $i === 2 ? ' active' : '' ?>"
                  style="grid-column:<?= (int) $p['spalte'] ?>;grid-row:<?= (int) $p['zeile'] ?>"
                  data-panel="<?= $i ?>" aria-pressed="<?= $i === 2 ? 'true' : 'false' ?>"
                  aria-controls="panel-detail">
            <span class="panel-name"><?= h($p['name']) ?></span>
            <span class="panel-zahl"><?= h((string) $p['anzahl']) ?></span>
          </button>
          <?php endforeach; ?>
        </div>
        <p class="panelkarte-hinweis"><?= h(get($seite, 'panelkarte.hinweis')) ?></p>
      </div>

      <?php /* Alle acht Detailtafeln stehen im HTML, damit die Texte im Quelltext
              auffindbar sind. JS blendet nur um. */ ?>
      <div class="panelkarte-detail" id="panel-detail">
        <?php foreach ($panels as $i => $p): ?>
        <div class="panel-tafel<?= $i === 2 ? ' is-active' : '' ?>"<?= $i === 2 ? '' : ' hidden' ?>>
          <div class="tafel-name"><?= h($p['name']) ?></div>
          <div class="tafel-zahl">
            <span class="wert"><?= h((string) $p['anzahl']) ?></span>
            <span class="einheit">Dellen erfasst</span>
          </div>
          <p><?= h($p['text']) ?></p>
          <dl class="tafel-werte">
            <div><dt>Demontage</dt><dd><?= h($p['demontage']) ?></dd></div>
            <div><dt>Zugang</dt><dd><?= h($p['zugang']) ?></dd></div>
            <div><dt>Schwierigkeit</dt><dd class="stufe"><?= h($p['schwierigkeit']) ?></dd></div>
          </dl>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- Drei Stationen -->
<section class="stationen">
  <div class="wrap">
    <div class="stationen-kopf">
      <div class="kicker"><?= swash() ?><span class="label"><?= h(get($seite, 'stationen.kicker')) ?></span></div>
      <h2><?= h(get($seite, 'stationen.titel')) ?></h2>
      <p><?= h(get($seite, 'stationen.lead')) ?></p>
    </div>
    <div class="stationen-grid">
      <?php foreach (get($seite, 'stationen.eintraege', []) as $i => $st): ?>
      <div class="station">
        <div class="station-bild">
          <img class="slot-img" src="<?= attr(upload($st['bild'])) ?>" alt="<?= attr($st['bild_alt']) ?>"
               width="1100" height="825" loading="lazy">
          <span class="station-nr<?= $i === 2 ? ' ist-letzte' : '' ?>">Station <?= h(str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT)) ?></span>
        </div>
        <h3><?= h($st['titel']) ?></h3>
        <p><?= h($st['text']) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Versicherung -->
<section class="versicherung">
  <div class="wrap">
    <div class="versicherung-grid">
      <div>
        <div class="kicker"><?= swash() ?><span class="label"><?= h(get($seite, 'versicherung.kicker')) ?></span></div>
        <h2><?= h(get($seite, 'versicherung.titel')) ?></h2>
        <p><?= h(get($seite, 'versicherung.text')) ?></p>
        <a class="hint-link" href="/kontakt/#anfrage"><?= h(get($seite, 'versicherung.link_text')) ?></a>
      </div>
      <div class="versicherung-punkte">
        <?php foreach (get($seite, 'versicherung.punkte', []) as $p): ?>
        <div class="punkt">
          <div class="punkt-titel"><?= h($p['titel']) ?></div>
          <div class="punkt-text"><?= h($p['text']) ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- Preisvorgehen und FAQ -->
<section class="preis-faq">
  <div class="wrap">
    <div class="preis-grid">
      <?php foreach (get($seite, 'preis.karten', []) as $k): ?>
      <div class="preis-karte">
        <div class="preis-kicker"><?= h($k['kicker']) ?></div>
        <div class="preis-titel"><?= h($k['titel']) ?></div>
        <p><?= h($k['text']) ?></p>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="faq-grid">
      <div class="faq-intro">
        <div class="kicker"><?= swash() ?><span class="label"><?= h(get($seite, 'faq.kicker')) ?></span></div>
        <h2><?= h(get($seite, 'faq.titel')) ?></h2>
        <a href="#kurzanfrage" class="btn btn-dark">
          <?= h(get($seite, 'faq.cta')) ?> <span class="btn-arrow" aria-hidden="true">→</span>
        </a>
      </div>
      <?php /* <details> statt eigener Klapplogik: funktioniert ohne JavaScript
              und ist von Haus aus per Tastatur bedienbar. Das gemeinsame name
              macht daraus nativ ein Akkordeon — es ist immer nur eine Frage
              offen, wie im Entwurf, und zwar ohne eine Zeile JavaScript.
              Aeltere Browser ignorieren das Attribut und lassen mehrere offen. */ ?>
      <div class="faq-liste">
        <?php foreach (get($seite, 'faq.fragen', []) as $i => $f): ?>
        <details class="faq-eintrag" name="faq-dellen"<?= $i === 0 ? ' open' : '' ?>>
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
        // Name und Ziel kommen aus dem Index, damit sie an einer Stelle gepflegt werden.
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
