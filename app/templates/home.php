<?php
/**
 * Startseite — 1:1 nach dem Design-Mockup (project/Reutter Website.dc.html).
 * Reihenfolge und Texte der Sektionen sind bewusst unveraendert.
 *
 * @var array<string,mixed> $seite  Inhalt aus data/content/home.json
 */
$s          = site();
$leistungen = content('leistungen')['eintraege'];
$kennzahl   = static fn (string $k): string => (string) get(site(), "kennzahlen.$k.wert", '');

/* Der Betrieb steht nur hier — die Unterseiten verweisen ueber @id darauf,
   statt ihn zu wiederholen. */
$jsonld = [seo_jsonld_betrieb()];

partial('kopf', [
    'titel'        => get($seite, 'seo.titel'),
    'beschreibung' => get($seite, 'seo.beschreibung'),
    'aktiv'        => '',
    'jsonld'       => $jsonld,
    'og_bild'      => get($seite, 'hero.bild'),
    'og_bild_alt'  => get($seite, 'hero.bild_alt'),
    'lcp_bild'     => get($seite, 'hero.bild'),
    'lcp_sizes'    => '(max-width: 980px) 100vw, 50vw',
]);
?>

<!-- Hero -->
<section id="top" class="hero">
  <div class="parallax-frame">
    <div class="parallax-layer">
      <?= bild(get($seite, 'hero.bild'), get($seite, 'hero.bild_alt'), [
          'class' => 'slot-img',
          'sizes' => '(max-width: 980px) 100vw, 50vw',
          // Das Bild ueber der Falz — es soll nicht warten.
          'fetchpriority' => 'high',
      ]) ?>
    </div>
  </div>
  <div class="scrim" aria-hidden="true"></div>
  <div class="accent-bar" aria-hidden="true"></div>
  <div class="wrap">
    <div class="hero-copy">
      <div class="kicker"><?= swash() ?><span class="label"><?= h(get($seite, 'hero.kicker')) ?></span></div>
      <h1><?= h(get($seite, 'hero.titel')) ?><br><span class="muted"><?= h(get($seite, 'hero.titel_zusatz')) ?></span></h1>
      <p class="lead"><?= h(get($seite, 'hero.lead')) ?></p>
      <div class="cta-row">
        <a href="/kontakt/#anfrage" class="btn btn-red">Termin anfragen <span class="btn-arrow" aria-hidden="true">→</span></a>
        <a href="tel:<?= attr(get($s, 'kontakt.telefon_link')) ?>" class="btn btn-outline"><?= h(get($s, 'kontakt.telefon')) ?></a>
      </div>
      <p class="fineprint"><?= h(get($seite, 'hero.fineprint')) ?></p>
    </div>
    <div class="hero-stats">
      <div class="stat">
        <div class="value"><?= h($kennzahl('jahre')) ?></div>
        <div class="label">Jahre im Handwerk</div>
      </div>
      <div class="stat">
        <div class="value"><span><?= h($kennzahl('google_bewertung')) ?></span><span class="stars" aria-hidden="true">★★★★★</span></div>
        <div class="label">Google-Bewertungen</div>
      </div>
      <div class="stat">
        <div class="value"><?= h(get($seite, 'hero.kachel_3.wert')) ?></div>
        <div class="label"><?= h(get($seite, 'hero.kachel_3.label')) ?></div>
      </div>
    </div>
  </div>
</section>

<?php partial('trust-strip', ['punkte' => $seite['trust']]); ?>

<!-- Leistungen / Hotspot -->
<section id="leistungen" class="section section-light">
  <div class="wrap">
    <div class="section-head light">
      <div style="max-width:720px">
        <div class="kicker"><?= swash() ?><span class="label"><?= h(get($seite, 'leistungen_sektion.kicker')) ?></span></div>
        <h2 class="rv"><?= h(get($seite, 'leistungen_sektion.titel')) ?></h2>
      </div>
      <p class="desc"><?= h(get($seite, 'leistungen_sektion.beschreibung')) ?></p>
    </div>

    <?php /* Die Kennung fasst Marker, Chipleiste und Tafeln zusammen — der
            gemeinsame Umschalter in main.js sucht darin. */ ?>
    <div class="hotspot-panel" id="hotspot">
      <div class="hotspot-visual">
        <?= bild(get($seite, 'leistungen_sektion.bild'), get($seite, 'leistungen_sektion.bild_alt'), [
            'class' => 'slot-img',
            'sizes' => '(max-width: 980px) 92vw, 52vw',
        ]) ?>
        <div class="scrim" aria-hidden="true"></div>
        <div class="tag">Interaktiv · <?= count($leistungen) ?> Bereiche</div>
        <div id="hotspot-dots">
          <?php foreach ($leistungen as $i => $l): ?>
          <button type="button" class="hotspot-dot<?= $i === 0 ? ' active' : '' ?>"
                  style="left:<?= (float) $l['hotspot']['x'] ?>%;top:<?= (float) $l['hotspot']['y'] ?>%"
                  data-spot="<?= $i ?>" aria-pressed="<?= $i === 0 ? 'true' : 'false' ?>"
                  aria-controls="hs-panel-<?= $i ?>">
            <span class="bubble"><span class="ring" aria-hidden="true"></span><?= h($l['num']) ?></span>
            <span class="visually-hidden"><?= h($l['titel']) ?></span>
          </button>
          <?php endforeach; ?>
        </div>
      </div>

      <?php /* Zweite Bedienung fuers Handy: die Marker im Bild sind 34 px
              gross und stehen teils dicht beieinander — mit dem Daumen ist das
              nichts. Die Leiste ist nur unterhalb 640 px sichtbar.

              Sie startet mit hidden und wird von main.js freigeschaltet: ohne
              JavaScript wuerden hier sieben Knoepfe stehen, die nichts tun,
              waehrend darunter ohnehin alle Bereiche untereinander lesbar
              sind. */ ?>
      <div class="hotspot-chips" id="hotspot-chips" role="group"
           aria-label="Bereich wählen" hidden>
        <?php foreach ($leistungen as $i => $l): ?>
        <button type="button" class="hotspot-chip" data-spot="<?= $i ?>"
                aria-pressed="<?= $i === 0 ? 'true' : 'false' ?>"
                aria-controls="hs-panel-<?= $i ?>">
          <span class="nummer"><?= h($l['num']) ?></span><?= h($l['titel']) ?>
        </button>
        <?php endforeach; ?>
      </div>

      <?php /* Alle sieben Panels stehen im HTML — der Text ist damit fuer
              Suchmaschinen sichtbar, JS blendet nur um. */ ?>
      <div class="hotspot-detail">
        <?php foreach ($leistungen as $i => $l): ?>
        <div class="hs-panel<?= $i === 0 ? ' is-active' : '' ?>" id="hs-panel-<?= $i ?>">
          <div class="head">
            <span class="tag"><?= h($l['tag']) ?></span>
            <span class="count"><?= h($l['num']) ?> / <?= h(str_pad((string) count($leistungen), 2, '0', STR_PAD_LEFT)) ?></span>
          </div>
          <h3><?= h($l['titel']) ?></h3>
          <p class="lead"><?= h($l['lead']) ?></p>
          <ul class="bullets">
            <?php foreach ($l['bullets'] as $b): ?>
            <li><?= swash() ?><span><?= h($b) ?></span></li>
            <?php endforeach; ?>
          </ul>
          <div class="cta">
            <a href="/kontakt/#anfrage?leistung=<?= attr(rawurlencode($l['chip'])) ?>" class="btn btn-red">
              Diese Leistung anfragen <span class="btn-arrow" aria-hidden="true">→</span>
            </a>
            <?php if ($l['eigene_seite']): ?>
            <a class="hint-link" href="/leistungen/<?= attr($l['slug']) ?>/">Mehr zu <?= h($l['titel']) ?> →</a>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- Vorher / Nachher -->
<section id="ergebnisse" class="section section-dark ba-section">
  <div class="top-rule" aria-hidden="true"></div>
  <div class="wrap">
    <div class="ba-head">
      <div>
        <div class="kicker"><?= swash() ?><span class="label"><?= h(get($seite, 'ergebnisse.kicker')) ?></span></div>
        <h2><?= h(get($seite, 'ergebnisse.titel')) ?></h2>
      </div>
      <div class="case-list" id="case-list">
        <?php foreach (get($seite, 'ergebnisse.faelle', []) as $i => $f): ?>
        <?php /* Auch die srcset-Listen mitgeben: seit die Bilder in mehreren
                Groessen ausgeliefert werden, genuegt ein Tausch von src nicht
                mehr — der Browser bleibt sonst bei der Fassung aus srcset. */ ?>
        <?php $qv = bild_quellen($f['vorher']); $qn = bild_quellen($f['nachher']); ?>
        <button type="button" class="case-btn<?= $i === 0 ? ' active' : '' ?>" data-case="<?= $i ?>"
                data-vorher="<?= attr(upload($f['vorher'])) ?>" data-nachher="<?= attr(upload($f['nachher'])) ?>"
                data-vorher-srcset="<?= attr($qv['srcset']) ?>" data-nachher-srcset="<?= attr($qn['srcset']) ?>"
                data-name="<?= attr($f['name']) ?>" data-note="<?= attr($f['note']) ?>"
                aria-pressed="<?= $i === 0 ? 'true' : 'false' ?>">
          <?= h($f['name']) ?><span class="meta"><?= h($f['meta']) ?></span>
        </button>
        <?php endforeach; ?>
      </div>
    </div>

    <?php $erst = get($seite, 'ergebnisse.faelle.0'); ?>
    <div class="ba-frame" id="ba-frame" data-start="<?= attr((string) get($seite, 'ergebnisse.start_position', 52)) ?>">
      <div class="layer layer-before">
        <?= bild($erst['vorher'], 'Vorher — Zustand vor der Bearbeitung', [
            'class' => 'slot-img', 'id' => 'ba-img-before',
            'sizes' => '(max-width: 980px) 92vw, 55vw',
        ]) ?>
      </div>
      <div class="layer layer-after" id="ba-after">
        <?= bild($erst['nachher'], 'Nachher — Zustand nach der Bearbeitung', [
            'class' => 'slot-img', 'id' => 'ba-img-after',
            'sizes' => '(max-width: 980px) 92vw, 55vw',
        ]) ?>
      </div>
      <div class="ba-handle" id="ba-handle" aria-hidden="true">
        <div class="grip"><span>←</span><span>→</span></div>
      </div>
      <div class="ba-label before">Vorher</div>
      <div class="ba-label after">Nachher</div>
      <div class="ba-caption">
        <div class="name" id="ba-case-name"><?= h($erst['name']) ?></div>
        <div class="note" id="ba-case-note"><?= h($erst['note']) ?></div>
      </div>
    </div>
    <p class="ba-hint"><?= h(get($seite, 'ergebnisse.hinweis')) ?></p>
  </div>
</section>

<!-- Ablauf -->
<section id="ablauf" class="section-white">
  <div class="wrap">
    <div class="ablauf-grid">
      <div class="ablauf-intro">
        <div class="kicker"><?= swash() ?><span class="label"><?= h(get($seite, 'ablauf.kicker')) ?></span></div>
        <h2><?= h(get($seite, 'ablauf.titel')) ?></h2>
        <p><?= h(get($seite, 'ablauf.lead')) ?></p>
        <a href="/kontakt/#anfrage" class="btn btn-dark">Mit Schritt 1 beginnen <span class="btn-arrow" aria-hidden="true">→</span></a>
      </div>
      <div class="step-list">
        <?php $schritte = get($seite, 'ablauf.schritte', []); ?>
        <?php foreach ($schritte as $i => $sch): ?>
        <div class="step-row rv<?= $i === count($schritte) - 1 ? ' final' : '' ?>">
          <div class="num"><?= h($sch['num']) ?></div>
          <div>
            <h3><?= h($sch['titel']) ?></h3>
            <p><?= h($sch['text']) ?></p>
          </div>
          <?php /* Zeilenumbruch ist Teil des Layouts, deshalb bewusst als Markup. */ ?>
          <div class="duration"><?= strip_tags($sch['dauer'], '<br>') ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- Galerie -->
<section id="galerie" class="section section-dark">
  <div class="wrap">
    <div class="gallery-head">
      <div>
        <div class="kicker"><?= swash() ?><span class="label"><?= h(get($seite, 'galerie.kicker')) ?></span></div>
        <h2><?= h(get($seite, 'galerie.titel')) ?></h2>
      </div>
      <a href="/galerie/" class="link"><?= h(get($seite, 'galerie.link_text')) ?></a>
    </div>
    <div class="gallery-grid">
      <?php foreach (get($seite, 'galerie.kacheln', []) as $k): ?>
      <?php
        $klassen = 'tile';
        if (($k['span'] ?? 1) === 2) { $klassen .= ' span-2'; }
        if (($k['row']  ?? 1) === 2) { $klassen .= ' row-2'; }
      ?>
      <div class="<?= attr($klassen) ?>">
        <?= bild($k['bild'], $k['alt'], [
            'class' => 'slot-img',
            'sizes' => '(max-width: 640px) 92vw, (max-width: 980px) 46vw, 23vw',
        ]) ?>
        <?php if (isset($k['caption'])): ?><div class="caption"><?= h($k['caption']) ?></div><?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php partial('mobil-banner', ['b' => $seite['mobiler_service']]); ?>

<!-- Betrieb & Bewertungen -->
<section id="betrieb" class="section section-light">
  <div class="wrap">
    <div class="betrieb-top">
      <div class="betrieb-photo">
        <?= bild(get($seite, 'betrieb.bild'), get($seite, 'betrieb.bild_alt'), [
            'class' => 'slot-img',
            'sizes' => '(max-width: 980px) 92vw, 45vw',
        ]) ?>
        <div class="badge">
          <div class="name"><?= h(get($seite, 'betrieb.badge.name')) ?></div>
          <div class="desc"><?= h(get($seite, 'betrieb.badge.text')) ?></div>
        </div>
      </div>
      <div class="betrieb-copy">
        <div class="kicker"><?= swash() ?><span class="label"><?= h(get($seite, 'betrieb.kicker')) ?></span></div>
        <h2><?= h(get($seite, 'betrieb.titel_vorne')) ?><?= h($kennzahl('jahre')) ?><?= h(get($seite, 'betrieb.titel_hinten')) ?></h2>
        <?php foreach (get($seite, 'betrieb.absaetze', []) as $absatz): ?>
        <p><?= h($absatz) ?></p>
        <?php endforeach; ?>
        <div class="betrieb-stats">
          <div class="stat">
            <div class="value"><?= h($kennzahl('jahre')) ?>+</div>
            <div class="label">Jahre Erfahrung</div>
          </div>
          <div class="stat">
            <div class="value"><?= h($kennzahl('google_bewertung')) ?></div>
            <div class="label">Google-Schnitt</div>
          </div>
          <div class="stat">
            <div class="value"><?= h(get($seite, 'betrieb.kachel_3.wert')) ?></div>
            <div class="label"><?= h(get($seite, 'betrieb.kachel_3.label')) ?></div>
          </div>
        </div>
      </div>
    </div>

    <div class="reviews-head">
      <h3><?= h(get($s, 'bewertungen.titel', 'Was Kunden schreiben')) ?></h3>
      <div class="rating">
        <span class="stars" aria-hidden="true">★★★★★</span>
        <span class="count"><?= h($kennzahl('google_bewertung')) ?> auf Google · <?= h($kennzahl('google_anzahl')) ?> Bewertungen</span>
      </div>
    </div>
    <div class="review-grid">
      <?php /* Bewertungen sind Stammdaten — der geteilte Fuss zeigt sie auf allen
              anderen Seiten. Hier stehen sie im Abschnitt "Betrieb", deshalb ist
              der Block im Fuss unterdrueckt. */ ?>
      <?php foreach (get($s, 'bewertungen.eintraege', []) as $r): ?>
      <div class="review-card rv">
        <span class="stars" aria-hidden="true">★★★★★</span>
        <p>„<?= h($r['text']) ?>"</p>
        <div class="author">
          <?= swash() ?>
          <span class="name"><?= h($r['name']) ?></span>
          <span class="loc">· <?= h($r['ort']) ?></span>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Anfrage -->
<section id="anfrage" class="section anfrage-section">
  <div class="deco" aria-hidden="true"></div>
  <div class="wrap">
    <div class="anfrage-grid">
      <div class="anfrage-intro">
        <div class="kicker"><?= swash() ?><span class="label"><?= h(get($seite, 'anfrage.kicker')) ?></span></div>
        <h2><?= h(get($seite, 'anfrage.titel')) ?></h2>
        <p><?= h(get($seite, 'anfrage.lead')) ?></p>
        <div class="contact-list">
          <div class="row">
            <div class="label">Werkstatt</div>
            <a href="tel:<?= attr(get($s, 'kontakt.telefon_link')) ?>"><?= h(get($s, 'kontakt.telefon')) ?></a>
          </div>
          <div class="row">
            <div class="label">Mobil</div>
            <a href="tel:<?= attr(get($s, 'kontakt.mobil_link')) ?>"><?= h(get($s, 'kontakt.mobil')) ?></a>
          </div>
          <div class="row email">
            <div class="label">E-Mail</div>
            <a href="mailto:<?= attr(get($s, 'kontakt.email')) ?>"><?= h(get($s, 'kontakt.email')) ?></a>
          </div>
        </div>
        <div class="map-frame">
          <div class="img-placeholder"><span><?= h(get($seite, 'anfrage.karte_platzhalter')) ?></span></div>
        </div>
      </div>

      <?php partial('anfrage-form', ['a' => $seite['anfrage']]); ?>
    </div>
  </div>
</section>

<?php /* Beide Bloecke aus: Bewertungen stehen oben im Abschnitt "Betrieb", und
        das ausfuehrliche Formular sitzt bereits in der Anfrage-Sektion. */ ?>
<?php partial('fuss', ['zeigeBewertungen' => false, 'zeigeFormular' => false]); ?>
