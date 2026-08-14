<?php
/**
 * Ozonbehandlung — nachgebaut aus
 * export/project/Leistung Ozon.dc.html.
 *
 * Kernmodul: die Geruchsdiagnose. Quelle als Chip waehlen, darunter steht,
 * wo der Geruch sitzt, in welcher Reihenfolge gearbeitet wird und ob Ozon
 * ueberhaupt der richtige Weg ist.
 *
 * Diese Seite hat als einzige der sechs bewusst keinen Vorher/Nachher-Regler:
 * Geruch ist nicht fotografierbar. An seiner Stelle stehen zwei Prozessbilder
 * und ein Absatz, der genau das erklaert.
 *
 * @var array<string,mixed> $seite
 * @var array<string,mixed> $leistung
 */
$s       = site();
$quellen = get($seite, 'quellen', []);

/* Kein Bild ueber der Falz — der Hero ist reiner Text. */
partial('kopf', [
    'titel'        => get($seite, 'seo.titel'),
    'beschreibung' => get($seite, 'seo.beschreibung'),
    'aktiv'        => 'leistungen',
]);
?>

<!-- Hero -->
<section class="leistung-hero ist-ozon">
  <div class="accent-bar" aria-hidden="true"></div>
  <div class="wrap">
    <?php partial('brotkrumen', ['pfade' => [
        ['label' => 'Startseite', 'ziel' => '/'],
        ['label' => 'Leistungen', 'ziel' => '/leistungen/'],
        ['label' => 'Ozonbehandlung'],
    ]]); ?>
    <div class="kicker"><?= swash() ?><span class="label"><?= h(get($seite, 'hero.kicker')) ?></span></div>
    <h1><?= h(get($seite, 'hero.titel')) ?></h1>
    <div class="ozon-hero-fuss">
      <p class="lead"><?= h(get($seite, 'hero.lead')) ?></p>
      <div class="ozon-hero-werte">
        <?php foreach (get($seite, 'hero.kennzahlen', []) as $k): ?>
        <div class="hero-kennzahl">
          <div class="wert"><?= h($k['wert']) ?></div>
          <div class="label"><?= h($k['label']) ?></div>
        </div>
        <?php endforeach; ?>
        <a href="#kurzanfrage" class="btn btn-red"><?= h(get($seite, 'hero.cta')) ?> <span class="btn-arrow" aria-hidden="true">→</span></a>
      </div>
    </div>
  </div>
</section>

<!-- Geruchsdiagnose -->
<section class="diagnose">
  <div class="wrap">
    <div class="section-head light">
      <div style="max-width:720px">
        <div class="kicker"><?= swash() ?><span class="label"><?= h(get($seite, 'diagnose.kicker')) ?></span></div>
        <h2><?= h(get($seite, 'diagnose.titel')) ?></h2>
      </div>
      <p class="desc"><?= h(get($seite, 'diagnose.beschreibung')) ?></p>
    </div>

    <div id="diagnose">
      <div class="quellen-chips" role="group" aria-label="Geruchsquelle wählen">
        <?php foreach ($quellen as $i => $q): ?>
        <button type="button" class="chip<?= $i === 0 ? ' active' : '' ?>" data-quelle="<?= $i ?>"
                aria-pressed="<?= $i === 0 ? 'true' : 'false' ?>" aria-controls="quellen-tafel">
          <span class="chip-marke" aria-hidden="true"></span><?= h($q['label']) ?>
        </button>
        <?php endforeach; ?>
      </div>

      <?php /* Alle fuenf Tafeln stehen im HTML, JS blendet um. */ ?>
      <div class="quellen-panel" id="quellen-tafel">
        <?php foreach ($quellen as $i => $q): ?>
        <div class="quellen-tafel<?= $i === 0 ? ' is-active' : '' ?>"<?= $i === 0 ? '' : ' hidden' ?>>
          <div class="quellen-haupt">
            <div class="quellen-kopf">
              <span class="quellen-label"><?= h(get($seite, 'diagnose.sitz_titel')) ?></span>
              <span class="quellen-urteil<?= !empty($q['urteil_dunkel']) ? ' ist-dunkel' : '' ?>"><?= h($q['urteil']) ?></span>
            </div>
            <h3><?= h($q['ueberschrift']) ?></h3>
            <p><?= h($q['text']) ?></p>
            <div class="ablauf-titel"><?= h(get($seite, 'diagnose.reihenfolge_titel')) ?></div>
            <ol class="quellen-reihenfolge">
              <?php foreach ($q['reihenfolge'] as $r): ?>
              <li<?= !empty($r['betont']) ? ' class="ist-betont"' : '' ?>>
                <span class="schritt-n"><?= h($r['n']) ?></span>
                <span class="schritt-t"><?= h($r['t']) ?></span>
              </li>
              <?php endforeach; ?>
            </ol>
          </div>
          <div class="wertespalte">
            <dl class="werte-liste">
              <div><dt>Ozon sinnvoll</dt><dd><?= h($q['sinnvoll']) ?></dd></div>
              <div><dt>Vorarbeit</dt><dd><?= h($q['vorarbeit']) ?></dd></div>
              <div><dt>Dauer gesamt</dt><dd><?= h($q['dauer']) ?></dd></div>
            </dl>
            <div class="werte-kasten">
              <div class="kasten-titel"><?= h(get($seite, 'diagnose.rueckfall_titel')) ?></div>
              <p><?= h($q['rueckfall']) ?></p>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- Ablauf und Sperrzeit -->
<section class="sperrzeit">
  <div class="wrap">
    <div class="sperrzeit-intro">
      <div class="kicker"><?= swash() ?><span class="label"><?= h(get($seite, 'sperrzeit.kicker')) ?></span></div>
      <h2><?= h(get($seite, 'sperrzeit.titel')) ?></h2>
      <p><?= h(get($seite, 'sperrzeit.lead')) ?></p>
    </div>
    <?php /* Geordnete Liste: die Phasen laufen nacheinander ab, das ist keine
            beliebige Aufzaehlung. */ ?>
    <ol class="phasen">
      <?php foreach (get($seite, 'sperrzeit.phasen', []) as $p): ?>
      <li class="phase">
        <div class="phase-kopf">
          <span class="phase-punkt ist-<?= attr($p['zustand_art']) ?>" aria-hidden="true"></span>
          <span class="phase-nummer"><?= h($p['phase']) ?></span>
        </div>
        <h3><?= h($p['titel']) ?></h3>
        <p><?= h($p['text']) ?></p>
        <div class="phase-zustand ist-<?= attr($p['zustand_art']) ?>"><?= h($p['zustand']) ?></div>
      </li>
      <?php endforeach; ?>
    </ol>
  </div>
</section>

<!-- Was Ozon nicht kann -->
<section class="grenzen">
  <div class="wrap">
    <div class="grenzen-grid">
      <div class="grenzen-intro">
        <div class="kicker"><?= swash() ?><span class="label"><?= h(get($seite, 'grenzen.kicker')) ?></span></div>
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

<!-- Beleg statt Vorher/Nachher -->
<section class="beleg">
  <div class="wrap">
    <div class="beleg-grid">
      <?php foreach (get($seite, 'beleg.bilder', []) as $b): ?>
      <figure class="beleg-bild">
        <img class="slot-img" src="<?= attr(upload($b['bild'])) ?>" alt="<?= attr($b['alt']) ?>"
             width="1448" height="1086" loading="lazy">
        <figcaption class="<?= !empty($b['ist_rot']) ? 'ist-rot' : 'ist-schwarz' ?>"><?= h($b['label']) ?></figcaption>
      </figure>
      <?php endforeach; ?>
    </div>
    <?php /* Der Absatz ersetzt den Vorher/Nachher-Regler und begruendet, warum
            es ihn hier nicht gibt. Er gehoert damit zur Sache, nicht zur
            Bebilderung. */ ?>
    <p class="beleg-hinweis"><?= swash() ?><span><?= h(get($seite, 'beleg.hinweis')) ?></span></p>
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
        <details class="faq-eintrag" name="faq-ozon"<?= $i === 0 ? ' open' : '' ?>>
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
