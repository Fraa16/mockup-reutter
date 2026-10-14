<?php
/**
 * Seitenfuss. Traegt vier Bloecke, wie im zweiten Design-Bundle vorgegeben:
 * Bewertungen → Kontaktband → Fussbereich → Sticky-Leiste.
 *
 * @var bool   $zeigeBewertungen  Bewertungsblock. Auf der Startseite aus, weil
 *                                die Bewertungen dort schon im Abschnitt
 *                                "Betrieb" stehen — sonst stuenden sie zweimal.
 * @var bool   $zeigeFormular     Kontaktband mit Anruf und WhatsApp (bis
 *                                Oktober 2026 ein Kurzformular, daher der
 *                                Name). Aus auf Startseite und Kontaktseite,
 *                                die dieselben Kacheln schon weiter oben
 *                                zeigen, und auf /danke/.
 * @var bool   $zeigeLeiste       Sticky-Leiste. Auf /danke/ aus, weil die
 *                                Anfrage gerade heraus ist.
 * @var string $ctaUeberschrift   Ueberschrift ueber dem Kontaktband.
 * @var string $leistung          Name der Leistung, steht dann schon in der
 *                                WhatsApp-Nachricht.
 */
$s = site();

$zeigeBewertungen = $zeigeBewertungen ?? true;
$zeigeFormular    = $zeigeFormular    ?? true;
$zeigeLeiste      = $zeigeLeiste      ?? true;
$ctaUeberschrift  = $ctaUeberschrift  ?? 'Sagen Sie uns, was ansteht.';
$leistung         = $leistung         ?? '';
$anruf            = anruf();

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
  <?php partial('kurzanfrage', ['ueberschrift' => $ctaUeberschrift, 'leistung' => $leistung]); ?>
<?php endif; ?>

<!-- Footer -->
<footer class="site-footer">
  <div class="wrap">
    <div class="footer-grid">
      <div class="footer-brand">
        <?php /* Im Fuss ist Platz — hier steht das vollständige Logo mit den
                Karosserielinien, nicht nur die Buchstaben. */ ?>
        <img class="logo-bild ist-gross" src="<?= attr(asset('logo/reutter-weiss.svg')) ?>"
             alt="Smartrepair Reutter" width="1697" height="131" loading="lazy">
        <p><?= h(get($s, 'footer.beschreibung')) ?></p>
        <?php /* Keine E-Mail-Adresse mehr: Der Betrieb will angerufen werden.
                Die Adresse steht weiter im Impressum, wo sie Pflicht ist. */ ?>
        <div class="contact">
          <a href="<?= attr($anruf['ziel']) ?>">Mobil <?= h($anruf['anzeige']) ?></a>
          <a href="<?= attr(whatsapp_link($leistung)) ?>" target="_blank" rel="noopener noreferrer">WhatsApp an dieselbe Nummer</a>
          <a href="tel:<?= attr(get($s, 'kontakt.telefon_link')) ?>">Werkstatt <?= h(get($s, 'kontakt.telefon')) ?></a>
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
<?php /* Anrufleiste — nur auf schmalen Bildschirmen sichtbar, siehe CSS
        und main.js. Serverseitig laesst sich das nicht entscheiden, deshalb
        steht das Markup immer da und wird ab 981 px ausgeblendet. Auf dem
        Handy ist sie der kuerzeste Weg zum Anruf: ein Daumentipp. */ ?>
<div class="sticky-bar" id="sticky-bar" data-ab-scroll="<?= attr((string) get($s, 'sticky_bar.ab_scroll', 600)) ?>">
  <div class="wrap">
    <div class="info">
      <?= swash() ?>
      <span class="headline"><?= h(get($s, 'sticky_bar.headline')) ?></span>
      <span class="subline"><?= h(get($s, 'sticky_bar.subline')) ?></span>
    </div>
    <div class="actions">
      <a href="<?= attr(whatsapp_link($leistung)) ?>" class="btn btn-outline btn-whatsapp" target="_blank" rel="noopener noreferrer"><?= icon('whatsapp') ?><span>WhatsApp</span></a>
      <a href="<?= attr($anruf['ziel']) ?>" class="btn btn-red btn-anruf"><?= icon('telefon') ?><span>Anrufen</span></a>
    </div>
  </div>
</div>
<?php endif; ?>

<script src="<?= attr(asset('js/main.js')) ?>" defer></script>
</body>
</html>
