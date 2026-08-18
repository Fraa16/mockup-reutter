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
 * @var bool   $zeigeLeiste       Sticky-Leiste. Auf Kontakt aus, weil sie dort
 *                                zum Formular schickt, das schon offen daliegt;
 *                                auf /danke/ aus, weil die Anfrage gerade
 *                                heraus ist. Die Rufnummer bleibt in beiden
 *                                Faellen im Kopf und im Fuss erreichbar.
 * @var string $ctaUeberschrift   Ueberschrift ueber der Kurzanfrage.
 */
$s = site();

$zeigeBewertungen = $zeigeBewertungen ?? true;
$zeigeFormular    = $zeigeFormular    ?? true;
$zeigeLeiste      = $zeigeLeiste      ?? true;
$ctaUeberschrift  = $ctaUeberschrift  ?? 'Sagen Sie uns, was ansteht.';

?>
</main>

<?php if ($zeigeBewertungen): ?>
<!-- Bewertungen -->
<section class="reviews-section">
  <div class="wrap">
    <?php partial('bewertungen', ['ebene' => 'h2']); ?>
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
        <?php /* Im Fuss ist Platz — hier steht das vollständige Logo mit den
                Karosserielinien, nicht nur die Buchstaben. */ ?>
        <img class="logo-bild ist-gross" src="<?= attr(asset('logo/reutter-weiss.webp')) ?>"
             alt="Fahrzeugpflege Reutter" width="934" height="107" loading="lazy">
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

<?php if ($zeigeLeiste): ?>
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
<?php endif; ?>

<script src="<?= attr(asset('js/main.js')) ?>" defer></script>
</body>
</html>
