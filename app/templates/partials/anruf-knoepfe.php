<?php
/**
 * Die zwei Kontaktwege als Knopfpaar: zuerst anrufen, daneben WhatsApp.
 *
 * Der Inhaber will Anrufe, hoechstens noch WhatsApp — Formulare und E-Mails
 * nicht. Deshalb steht dieses Paar an jeder Stelle, an der frueher
 * „Termin anfragen" oder „Einschätzung anfragen" zum Formular fuehrte. Ein
 * Formular gibt es nur noch ganz unten auf der Kontaktseite.
 *
 * Gibt nur die zwei Links aus, ohne Rahmen: Die Templates behalten ihre
 * eigenen Reihen (cta-row, leistung-hero-cta …), damit sich das Layout der
 * Abschnitte nicht aendert.
 *
 * @var string $groesse      'gross' zweizeilig fuer Kopfbereiche,
 *                           'kompakt' einzeilig fuer Abschnitte
 * @var string $anruf_klasse Knopffarbe des Anrufs: btn-red, btn-dark, btn-black
 * @var string $wa_klasse    btn-outline auf dunklem oder rotem Grund,
 *                           btn-linie auf hellem
 * @var string $leistung     Name der Leistung — steht dann schon in der
 *                           WhatsApp-Nachricht
 */
$groesse     = $groesse      ?? 'gross';
$anrufKlasse = $anruf_klasse ?? 'btn-red';
$waKlasse    = $wa_klasse    ?? 'btn-outline';
$leistung    = $leistung     ?? '';
$a           = anruf();
?>
<a href="<?= attr($a['ziel']) ?>" class="btn <?= attr($anrufKlasse) ?> btn-anruf">
  <?= icon('telefon') ?>
  <?php if ($groesse === 'gross'): ?>
  <span class="btn-zeilen"><span class="btn-klein">Direkt anrufen</span><span class="btn-nummer"><?= h($a['anzeige']) ?></span></span>
  <?php else: ?>
  <span class="btn-nummer"><?= h($a['anzeige']) ?></span>
  <?php endif; ?>
</a>
<a href="<?= attr(whatsapp_link($leistung)) ?>" class="btn <?= attr($waKlasse) ?> btn-whatsapp" target="_blank" rel="noopener noreferrer">
  <?= icon('whatsapp') ?>
  <?php if ($groesse === 'gross'): ?>
  <span class="btn-zeilen"><span class="btn-klein">Fotos schicken</span><span>WhatsApp</span></span>
  <?php else: ?>
  <span>WhatsApp</span>
  <?php endif; ?>
</a>
