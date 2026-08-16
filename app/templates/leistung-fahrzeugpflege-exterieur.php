<?php
/**
 * Fahrzeugpflege Exterieur — nachgebaut aus
 * export/project/Leistung Exterieur.dc.html.
 *
 * Kernmodul: der Lack-Querschnitt. Politurstufe waehlen, die schraffierte
 * Abtragszone im Klarlack waechst entsprechend.
 *
 * @var array<string,mixed> $seite
 * @var array<string,mixed> $leistung
 */
$s      = site();
$stufen = get($seite, 'querschnitt.stufen', []);

partial('kopf', [
    'titel'        => get($seite, 'seo.titel'),
    'beschreibung' => get($seite, 'seo.beschreibung'),
    'aktiv'        => 'leistungen',
    'lcp_bild'     => get($seite, 'hero.bild'),
]);
?>

<!-- Hero -->
<section class="leistung-hero ist-halb">
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
        ['label' => 'Exterieur'],
    ]]); ?>
    <div class="leistung-hero-copy">
      <div class="kicker"><?= swash() ?><span class="label"><?= h(get($seite, 'hero.kicker')) ?></span></div>
      <h1><?= h(get($seite, 'hero.titel')) ?></h1>
      <p class="lead"><?= h(get($seite, 'hero.lead')) ?></p>
      <div class="cta-row">
        <a href="#kurzanfrage" class="btn btn-red"><?= h(get($seite, 'hero.cta')) ?> <span class="btn-arrow" aria-hidden="true">→</span></a>
        <a href="tel:<?= attr(get($s, 'kontakt.telefon_link')) ?>" class="btn btn-outline"><?= h(get($s, 'kontakt.telefon')) ?></a>
      </div>
    </div>
  </div>
</section>

<!-- Lack-Querschnitt -->
<section class="querschnitt">
  <div class="wrap">
    <div class="section-head light">
      <div style="max-width:680px">
        <div class="kicker"><?= swash() ?><span class="label"><?= h(get($seite, 'querschnitt.kicker')) ?></span></div>
        <h2><?= h(get($seite, 'querschnitt.titel')) ?></h2>
      </div>
      <p class="desc"><?= h(get($seite, 'querschnitt.beschreibung')) ?></p>
    </div>

    <div class="querschnitt-grid" id="querschnitt">
      <div>
        <div class="stufen-wahl" role="group" aria-label="Politurstufe wählen">
          <?php foreach ($stufen as $i => $st): ?>
          <button type="button" class="stufe<?= $i === 1 ? ' active' : '' ?>" data-stufe="<?= $i ?>"
                  aria-pressed="<?= $i === 1 ? 'true' : 'false' ?>" aria-controls="stufen-tafel">
            <span class="stufe-name"><?= h($st['name']) ?></span>
            <span class="stufe-sub"><?= h($st['unterzeile']) ?></span>
          </button>
          <?php endforeach; ?>
        </div>

        <div class="schnitt">
          <div class="schnitt-kopf">
            <span class="schnitt-titel"><?= h(get($seite, 'querschnitt.schema_titel')) ?></span>
            <?php foreach ($stufen as $i => $st): ?>
            <span class="schnitt-abtrag<?= $i === 1 ? ' is-active' : '' ?>" data-abtrag="<?= $i ?>">
              Abtrag <?= h($st['abtrag']) ?>
            </span>
            <?php endforeach; ?>
          </div>

          <?php /* Die Schichthoehen sind Design und stehen deshalb im CSS. Nur die
                  Hoehe der Abtragszone wechselt — sie kommt aus den Stufendaten. */ ?>
          <div class="schichten">
            <?php foreach (get($seite, 'querschnitt.schichten', []) as $sch): ?>
            <div class="schicht schicht-<?= attr($sch['rolle']) ?>">
              <span class="schicht-name"><?= h($sch['name']) ?></span>
              <?php if ($sch['zusatz'] !== ''): ?>
                <span class="schicht-zusatz"><?= h($sch['zusatz']) ?></span>
              <?php endif; ?>
              <?php if ($sch['rolle'] === 'klarlack'): ?>
                <span class="abtragszone" id="abtragszone"
                      style="height:<?= (int) ($stufen[1]['abtrag_hoehe'] ?? 20) ?>px" aria-hidden="true"></span>
              <?php endif; ?>
            </div>
            <?php endforeach; ?>
          </div>
          <p class="schnitt-hinweis"><?= h(get($seite, 'querschnitt.hinweis')) ?></p>
        </div>
      </div>

      <?php /* Alle drei Tafeln im HTML, JS blendet um — damit die Texte im
              Quelltext stehen. */ ?>
      <div class="stufen-detail" id="stufen-tafel">
        <?php foreach ($stufen as $i => $st): ?>
        <?php /* Die Abtragshoehe haengt am Tafel-Element, damit sie neben dem
                zugehoerigen Text steht statt im Skript. */ ?>
        <div class="stufen-tafel<?= $i === 1 ? ' is-active' : '' ?>" data-tafel="<?= $i ?>"
             data-abtrag-hoehe="<?= (int) $st['abtrag_hoehe'] ?>">
          <div class="tafel-name"><?= h($st['name']) ?></div>
          <h3><?= h($st['ueberschrift']) ?></h3>
          <p><?= h($st['text']) ?></p>
          <dl class="tafel-werte">
            <div><dt>Dagegen wirksam</dt><dd><?= h($st['wirksam_gegen']) ?></dd></div>
            <div><dt>Zeit</dt><dd><?= h($st['zeit']) ?></dd></div>
            <div><dt>Wiederholbar</dt><dd><?= h($st['wiederholbar']) ?></dd></div>
          </dl>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- Schutzschicht -->
<section class="schutz">
  <div class="wrap">
    <div class="schutz-grid">
      <div>
        <div class="kicker"><?= swash() ?><span class="label"><?= h(get($seite, 'schutz.kicker')) ?></span></div>
        <h2><?= h(get($seite, 'schutz.titel')) ?></h2>
        <p><?= h(get($seite, 'schutz.lead')) ?></p>
      </div>
      <div>
        <?php /* Echte Tabelle: der Inhalt ist tabellarisch, und Screenreader
                lesen Zeilen- und Spaltenbezug damit korrekt vor. */ ?>
        <div class="tabelle-rahmen">
          <table class="schutz-tabelle">
            <thead>
              <tr>
                <?php foreach (get($seite, 'schutz.spalten', []) as $i => $sp): ?>
                <th scope="col"<?= $i === 3 ? ' class="ist-empfohlen"' : '' ?>><?= h($sp) ?></th>
                <?php endforeach; ?>
              </tr>
            </thead>
            <tbody>
              <?php foreach (get($seite, 'schutz.zeilen', []) as $z): ?>
              <tr>
                <th scope="row"><?= h($z['kriterium']) ?></th>
                <td><?= h($z['wachs']) ?></td>
                <td><?= h($z['versiegelung']) ?></td>
                <td class="ist-betont"><?= h($z['keramik']) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <p class="schutz-hinweis"><?= h(get($seite, 'schutz.hinweis')) ?></p>
      </div>
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

<!-- Preis und FAQ -->
<section class="preis-faq">
  <div class="wrap">
    <div class="preis-banner">
      <div>
        <div class="preis-kicker"><?= h(get($seite, 'preis.kicker')) ?></div>
        <h2><?= h(get($seite, 'preis.titel')) ?></h2>
        <p><?= h(get($seite, 'preis.text')) ?></p>
      </div>
      <a href="#kurzanfrage" class="btn btn-red"><?= h(get($seite, 'preis.cta')) ?></a>
    </div>

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
        <details class="faq-eintrag" name="faq-exterieur"<?= $i === 0 ? ' open' : '' ?>>
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
