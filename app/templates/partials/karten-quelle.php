<?php
/**
 * Quellenangabe auf einem Kartenbild. Bei Karten von OpenStreetMap ist sie
 * Pflicht (Lizenz CC BY-SA, Daten ODbL): sichtbar, beim Bild, mit Verweis.
 * Text und Ziel stehen im Inhalt, damit ein Kartenbild aus anderer Quelle
 * auch die richtige Angabe bekommt.
 *
 * @var array<string,mixed> $karte  Abschnitt anfahrt.karte aus kontakt.json
 */
$quelle = trim((string) ($karte['quelle'] ?? ''));
if ($quelle === '') {
    return;
}
$link = trim((string) ($karte['quelle_link'] ?? ''));
// Nur echte Webadressen — ein javascript:-Link aus dem Panel wuerde sonst
// auf jeder Seite mit Karte ausgefuehrt.
if (!preg_match('#^https?://#i', $link)) {
    $link = '';
}
?>
<?php if ($link !== ''): ?>
<a class="karten-quelle" href="<?= attr($link) ?>" rel="noopener noreferrer"><?= h($quelle) ?></a>
<?php else: ?>
<span class="karten-quelle"><?= h($quelle) ?></span>
<?php endif; ?>
