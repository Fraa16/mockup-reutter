<?php
/**
 * Seitenfuss: Footer, Sticky-Anfrageleiste, Skripte.
 */
$s = site();
$leistungen = content('leistungen')['eintraege'];
?>
</main>

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
          <?php foreach ($leistungen as $l): ?>
            <?php /* Bereiche ohne eigene Seite zeigen auf den Abschnitt, zu dem sie gehoeren. */ ?>
            <?php $ziel = $l['eigene_seite']
                ? '/leistungen/' . $l['slug'] . '/'
                : '/leistungen/' . $l['gehoert_zu'] . '/#' . $l['slug']; ?>
            <a href="<?= attr($ziel) ?>"><?= h($l['titel']) ?></a>
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
  </div>
</footer>

<!-- Sticky request bar -->
<div class="sticky-bar" id="sticky-bar" data-ab-scroll="<?= attr((string) get($s, 'sticky_bar.ab_scroll', 720)) ?>">
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
