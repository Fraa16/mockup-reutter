<?php
/**
 * Lederreparatur — nachgebaut aus
 * export/project/Leistung Leder.dc.html.
 *
 * Kernmodul: die vier Schadensgrade als Reiterleiste. Grad waehlen, darunter
 * erscheinen Ablauf, Aufwand, Dauer und — der ehrlichste Teil — was man
 * hinterher noch sieht.
 *
 * Zwei Besonderheiten gegenueber den anderen Leistungsseiten:
 * der Hero liegt spiegelverkehrt (Bild links, Text rechts), und die FAQ steht
 * offen in zwei Spalten statt als Aufklapper — beides so im Entwurf.
 *
 * @var array<string,mixed> $seite
 * @var array<string,mixed> $leistung
 */
$s     = site();
$grade = get($seite, 'grade', []);

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

<!-- Hero, gespiegelt: Bild links, Text rechts -->
<section class="leistung-hero ist-gespiegelt">
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
    <div class="leistung-hero-copy">
      <?php partial('brotkrumen', ['pfade' => [
          ['label' => 'Startseite', 'ziel' => '/'],
          ['label' => 'Leistungen', 'ziel' => '/leistungen/'],
          ['label' => 'Lederreparatur'],
      ]]); ?>
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

<!-- Materialcheck -->
<section class="material">
  <div class="wrap">
    <?php /* Die Beschriftung der Linie ist die Ueberschrift des Abschnitts —
            klein gesetzt, aber semantisch die h2. Die Materialnamen sind ihr
            untergeordnet. */ ?>
    <div class="abschnitt-linie">
      <h2 class="label"><?= h(get($seite, 'material.kicker')) ?></h2>
      <span class="linie" aria-hidden="true"></span>
    </div>
    <div class="material-grid">
      <?php foreach (get($seite, 'material.karten', []) as $k): ?>
      <div class="material-karte">
        <div class="material-kopf">
          <h3><?= h($k['name']) ?></h3>
          <span class="material-urteil<?= !empty($k['ist_gut']) ? ' ist-gut' : '' ?>"><?= h($k['urteil']) ?></span>
        </div>
        <p><?= h($k['text']) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Vier Schadensgrade -->
<section class="schadensgrad">
  <div class="wrap">
    <div class="section-head light">
      <div style="max-width:700px">
        <div class="kicker"><?= swash() ?><span class="label"><?= h(get($seite, 'grade_sektion.kicker')) ?></span></div>
        <h2><?= h(get($seite, 'grade_sektion.titel')) ?></h2>
      </div>
      <p class="desc"><?= h(get($seite, 'grade_sektion.beschreibung')) ?></p>
    </div>

    <div id="schadensgrad">
      <div class="grad-leiste" role="group" aria-label="Schadensgrad wählen">
        <?php foreach ($grade as $i => $g): ?>
        <button type="button" class="grad<?= $i === 1 ? ' active' : '' ?>" data-grad="<?= $i ?>"
                aria-pressed="<?= $i === 1 ? 'true' : 'false' ?>" aria-controls="grad-tafel">
          <span class="grad-kopf">
            <span class="grad-punkt" style="background:<?= attr($g['punkt_farbe']) ?>" aria-hidden="true"></span>
            <span class="grad-num">Grad <?= h($g['num']) ?></span>
          </span>
          <span class="grad-name"><?= h($g['name']) ?></span>
        </button>
        <?php endforeach; ?>
      </div>

      <?php /* Alle vier Tafeln stehen im HTML, JS blendet um. */ ?>
      <div class="grad-panel" id="grad-tafel">
        <?php foreach ($grade as $i => $g): ?>
        <div class="grad-tafel<?= $i === 1 ? ' is-active' : '' ?>">
          <div class="grad-ablauf">
            <h3><?= h($g['ueberschrift']) ?></h3>
            <p><?= h($g['text']) ?></p>
            <div class="ablauf-titel"><?= h(get($seite, 'grade_sektion.schritte_titel')) ?></div>
            <ol class="grad-schritte">
              <?php foreach ($g['schritte'] as $sch): ?>
              <li><span class="schritt-n"><?= h($sch['n']) ?></span><span class="schritt-t"><?= h($sch['t']) ?></span></li>
              <?php endforeach; ?>
            </ol>
          </div>
          <div class="wertespalte">
            <dl class="werte-liste">
              <div><dt>Aufwand</dt><dd><?= h($g['aufwand']) ?></dd></div>
              <div><dt>Dauer</dt><dd><?= h($g['dauer']) ?></dd></div>
              <div><dt>Mobil möglich</dt><dd><?= h($g['mobil']) ?></dd></div>
            </dl>
            <?php /* Der Kasten sagt, was hinterher noch zu sehen ist. Der steht
                    bewusst neben den Werten und nicht im Fliesstext. */ ?>
            <div class="werte-kasten">
              <div class="kasten-titel"><?= h(get($seite, 'grade_sektion.erwartung_titel')) ?></div>
              <p><?= h($g['erwartung']) ?></p>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- Vorher / Nachher mit zwei Makroaufnahmen -->
<section class="ba-section leder-vergleich">
  <div class="wrap">
    <div class="leder-ba-head">
      <div class="kicker"><?= swash() ?><span class="label"><?= h(get($seite, 'vergleich.kicker')) ?></span></div>
      <h2><?= h(get($seite, 'vergleich.titel')) ?></h2>
      <p><?= h(get($seite, 'vergleich.beschreibung')) ?></p>
    </div>
    <div class="leder-ba-grid">
      <?php partial('vergleich', ['v' => $seite['vergleich']]); ?>
      <div class="leder-makros">
        <?php foreach (get($seite, 'vergleich.makros', []) as $m): ?>
        <figure class="leder-makro">
          <?= bild($m['bild'], $m['alt'], [
              'class' => 'slot-img',
              'sizes' => '(max-width: 980px) 92vw, 36vw',
          ]) ?>
          <figcaption><?= h($m['label']) ?></figcaption>
        </figure>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- Preis und Pflege -->
<section class="leder-preis">
  <div class="wrap">
    <div class="leder-preis-grid">
      <div>
        <div class="preis-kicker"><?= h(get($seite, 'preis.kicker')) ?></div>
        <h2><?= h(get($seite, 'preis.titel')) ?></h2>
        <p><?= h(get($seite, 'preis.text')) ?></p>
        <a href="#kurzanfrage" class="btn btn-red"><?= h(get($seite, 'preis.cta')) ?> <span class="btn-arrow" aria-hidden="true">→</span></a>
      </div>
      <div class="pflege">
        <div class="pflege-titel"><?= h(get($seite, 'preis.pflege_titel')) ?></div>
        <ul class="bullets">
          <?php foreach (get($seite, 'preis.pflege', []) as $p): ?>
          <li><?= swash() ?><span><?= h($p) ?></span></li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </div>
</section>

<!-- FAQ, offen in zwei Spalten -->
<section class="faq-offen">
  <div class="wrap">
    <div class="faq-offen-head">
      <h2><?= h(get($seite, 'faq.titel')) ?></h2>
      <p>
        <?= h(get($seite, 'faq.hinweis_vor')) ?><br>
        <a href="tel:<?= attr(get($s, 'kontakt.telefon_link')) ?>"><?= h(get($s, 'kontakt.telefon')) ?></a>
      </p>
    </div>
    <div class="faq-offen-grid">
      <?php foreach (get($seite, 'faq.fragen', []) as $f): ?>
      <div class="faq-offen-eintrag">
        <h3><?= h($f['frage']) ?></h3>
        <p><?= h($f['antwort']) ?></p>
      </div>
      <?php endforeach; ?>
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
