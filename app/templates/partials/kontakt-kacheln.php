<?php
/**
 * Die zwei grossen Kontaktkacheln: Mobil (rot) und Werkstatt.
 *
 * Steht dort, wo frueher ein Formular stand — im Kontaktband am Fuss jeder
 * Seite, im Kontaktabschnitt der Startseite und oben auf der Kontaktseite.
 * Die zweite Kachel war bis November 2026 WhatsApp; jetzt steht dort die
 * Festnetznummer der Werkstatt, die vorher nur klein darunter stand.
 */
$s         = site();
$mobil     = anruf();
$werkstatt = festnetz();
?>
<div class="kontakt-kacheln">
  <a class="kontakt-kachel ist-anruf" href="<?= attr($mobil['ziel']) ?>">
    <span class="kachel-icon"><?= icon('telefon') ?></span>
    <span class="kachel-text">
      <span class="kachel-label">Mobil anrufen</span>
      <span class="kachel-wert"><?= h($mobil['anzeige']) ?></span>
      <span class="kachel-zusatz"><?= h(get($s, 'oeffnungszeiten.text')) ?> · Einschätzung kostenlos</span>
    </span>
    <span class="kachel-pfeil" aria-hidden="true">→</span>
  </a>
  <a class="kontakt-kachel ist-festnetz" href="<?= attr($werkstatt['ziel']) ?>">
    <span class="kachel-icon"><?= icon('werkstatt') ?></span>
    <span class="kachel-text">
      <span class="kachel-label">Werkstatt · Festnetz</span>
      <span class="kachel-wert"><?= h($werkstatt['anzeige']) ?></span>
      <span class="kachel-zusatz">Direkt in der Halle in Korntal</span>
    </span>
    <span class="kachel-pfeil" aria-hidden="true">→</span>
  </a>
</div>
