<?php
/**
 * Die zwei grossen Kontaktkacheln: Anruf (rot) und WhatsApp.
 *
 * Steht dort, wo frueher ein Formular stand — im Kontaktband am Fuss jeder
 * Seite, im Kontaktabschnitt der Startseite und oben auf der Kontaktseite.
 * Darunter, klein, die Werkstattnummer als zweiter Anschluss.
 *
 * @var string $leistung  Name der Leistung fuer die WhatsApp-Nachricht
 */
$s        = site();
$a        = anruf();
$leistung = $leistung ?? '';
?>
<div class="kontakt-kacheln">
  <a class="kontakt-kachel ist-anruf" href="<?= attr($a['ziel']) ?>">
    <span class="kachel-icon"><?= icon('telefon') ?></span>
    <span class="kachel-text">
      <span class="kachel-label">Direkt anrufen</span>
      <span class="kachel-wert"><?= h($a['anzeige']) ?></span>
      <span class="kachel-zusatz"><?= h(get($s, 'oeffnungszeiten.text')) ?> · Einschätzung kostenlos</span>
    </span>
    <span class="kachel-pfeil" aria-hidden="true">→</span>
  </a>
  <a class="kontakt-kachel ist-whatsapp" href="<?= attr(whatsapp_link($leistung)) ?>" target="_blank" rel="noopener noreferrer">
    <span class="kachel-icon"><?= icon('whatsapp') ?></span>
    <span class="kachel-text">
      <span class="kachel-label">WhatsApp</span>
      <span class="kachel-wert">Fotos schicken</span>
      <span class="kachel-zusatz">Zwei Bilder bei Tageslicht reichen meist.</span>
    </span>
    <span class="kachel-pfeil" aria-hidden="true">→</span>
  </a>
  <p class="kachel-werkstatt">
    Werkstatt (Festnetz):
    <a href="tel:<?= attr(get($s, 'kontakt.telefon_link')) ?>"><?= h(get($s, 'kontakt.telefon')) ?></a>
  </p>
</div>
