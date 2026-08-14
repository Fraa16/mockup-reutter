<?php
/**
 * Seitenfuss. Traegt vier Bloecke, wie im zweiten Design-Bundle vorgegeben:
 * Bewertungen → Kurzanfrage → Fussbereich → Sticky-Leiste.
 *
 * @var bool   $zeigeBewertungen  Bewertungsblock. Auf der Startseite aus, weil
 *                                die Bewertungen dort schon im Abschnitt
 *                                "Betrieb" stehen — sonst stuenden sie zweimal.
 * @var bool   $zeigeFormular     Kurzanfrage. Auf Kontakt und den Rechtsseiten
 *                                aus: dort gibt es das ausfuehrliche Formular
 *                                oder gar keins.
 * @var string $ctaUeberschrift   Ueberschrift ueber der Kurzanfrage.
 */
$s = site();

$zeigeBewertungen = $zeigeBewertungen ?? true;
$zeigeFormular    = $zeigeFormular    ?? true;
$ctaUeberschrift  = $ctaUeberschrift  ?? 'Sagen Sie uns, was ansteht.';

$kennzahl = static fn (string $k): string => (string) get(site(), "kennzahlen.$k.wert", '');
?>
</main>

<?php if ($zeigeBewertungen): ?>
<!-- Bewertungen -->
<section class="reviews-section">
  <div class="wrap">
    <div class="reviews-head">
      <h2><?= h(get($s, 'bewertungen.titel', 'Was Kunden schreiben')) ?></h2>
      <div class="rating">
        <span class="stars" aria-hidden="true">★★★★★</span>
        <span class="count"><?= h($kennzahl('google_bewertung')) ?> auf Google · <?= h($kennzahl('google_anzahl')) ?> Bewertungen</span>
      </div>
    </div>
    <div class="review-grid">
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
<?php endif; ?>

<?php if ($zeigeFormular): ?>
  <?php partial('kurzanfrage', ['ueberschrift' => $ctaUeberschrift]); ?>
<?php endif; ?>

<!-- Footer -->
<footer class="site-footer">
  <div class="wrap">
    <div class="footer-grid">
      <div class="footer-brand">
        <span class="wordmark"><span>REU</span><span>T</span><?= swash() ?><span>T</span><span>ER</span></span>
        <p><?= h(get($s, 'footer.beschreibung')) ?></p>
        <div class="contact">
          <span>Telefon <?= h(get($s, 'kontakt.telefon')) ?></span>
          <span>Mobil <?= h(get($s, 'kontakt.mobil')) ?></span>
          <a href="mailto:<?= attr(get($s, 'kontakt.email')) ?>"><?= h(get($s, 'kontakt.email')) ?></a>
        </div>
      </div>
      <div class="footer-col">
        <div class="heading">Leistungen</div>
        <div class="links">
          <?php /* Nur die sechs Leistungen mit eigener Seite, wie im Entwurf.
                  'Felgen & Reifen' hat keine und bleibt ueber die Startseite und
                  den Exterieur-Abschnitt erreichbar. */ ?>
          <?php foreach (leistungen_mit_seite() as $l): ?>
            <a href="/leistungen/<?= attr($l['slug']) ?>/"><?= h($l['seitenname']) ?></a>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="footer-col">
        <div class="heading">Einsatzgebiet</div>
        <div class="links">
          <?php foreach (get($s, 'einsatzgebiet', []) as $ort): ?>
            <span><?= h($ort) ?></span>
          <?php endforeach; ?>
          <span class="muted"><?= h(get($s, 'einsatzgebiet_zusatz')) ?></span>
        </div>
      </div>
      <div class="footer-col">
        <div class="heading">Rechtliches</div>
        <div class="links">
          <?php foreach (get($s, 'footer.rechtliches', []) as $punkt): ?>
            <a href="<?= attr($punkt['ziel']) ?>"><?= h($punkt['label']) ?></a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <div class="footer-bottom">
      <span>© <?= date('Y') ?> <?= h(get($s, 'firma.name')) ?> · Alle Rechte vorbehalten</span>
      <span><?= h(get($s, 'oeffnungszeiten.text')) ?></span>
    </div>
    <?php /* Solange die Sticky-Leiste sichtbar ist, verdeckt sie sonst die
            letzte Fusszeile. main.js setzt dafuer eine Klasse am body. */ ?>
    <div class="footer-sticky-platz" aria-hidden="true"></div>
  </div>
</footer>

<!-- Sticky request bar -->
<div class="sticky-bar" id="sticky-bar" data-ab-scroll="<?= attr((string) get($s, 'sticky_bar.ab_scroll', 600)) ?>">
  <div class="wrap">
    <div class="info">
      <?= swash() ?>
      <span class="headline"><?= h(get($s, 'sticky_bar.headline')) ?></span>
      <span class="subline"><?= h(get($s, 'sticky_bar.subline')) ?></span>
    </div>
    <div class="actions">
      <a href="tel:<?= attr(get($s, 'kontakt.telefon_link')) ?>" class="btn btn-outline"><?= h(get($s, 'kontakt.telefon')) ?></a>
      <a href="/kontakt/#anfrage" class="btn btn-red">Termin anfragen</a>
    </div>
  </div>
</div>

<script src="<?= attr(asset('js/main.js')) ?>" defer></script>
</body>
</html>
