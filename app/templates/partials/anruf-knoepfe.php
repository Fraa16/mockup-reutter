<?php
/**
 * Die zwei Nummern als Knopfpaar: zuerst mobil, daneben die Werkstatt.
 *
 * Der Inhaber will Anrufe — Formulare und E-Mails nicht. Deshalb steht dieses
 * Paar an jeder Stelle, an der frueher „Termin anfragen" oder „Einschätzung
 * anfragen" zum Formular fuehrte. Ein Formular gibt es nur noch ganz unten
 * auf der Kontaktseite. Bis November 2026 war der zweite Knopf WhatsApp; den
 * Weg bietet der Betrieb nicht mehr an, und die Festnetznummer sollte
 * sichtbarer werden.
 *
 * Gibt nur die zwei Links aus, ohne Rahmen: Die Templates behalten ihre
 * eigenen Reihen (cta-row, leistung-hero-cta …), damit sich das Layout der
 * Abschnitte nicht aendert.
 *
 * @var string $groesse       'gross' zweizeilig fuer Kopfbereiche,
 *                            'kompakt' einzeilig fuer Abschnitte
 * @var string $anruf_klasse  Knopffarbe des Anrufs: btn-red, btn-dark, btn-black
 * @var string $zweit_klasse  Werkstattknopf: btn-outline auf dunklem oder
 *                            rotem Grund, btn-linie auf hellem
 */
$groesse     = $groesse      ?? 'gross';
$anrufKlasse = $anruf_klasse ?? 'btn-red';
$zweitKlasse = $zweit_klasse ?? 'btn-outline';
$mobil       = anruf();
$werkstatt   = festnetz();
?>
<a href="<?= attr($mobil['ziel']) ?>" class="btn <?= attr($anrufKlasse) ?> btn-anruf">
  <?= icon('telefon') ?>
  <?php if ($groesse === 'gross'): ?>
  <span class="btn-zeilen"><span class="btn-klein">Mobil anrufen</span><span class="btn-nummer"><?= h($mobil['anzeige']) ?></span></span>
  <?php else: ?>
  <span class="btn-nummer"><?= h($mobil['anzeige']) ?></span>
  <?php endif; ?>
</a>
<a href="<?= attr($werkstatt['ziel']) ?>" class="btn <?= attr($zweitKlasse) ?> btn-festnetz">
  <?= icon('werkstatt') ?>
  <?php if ($groesse === 'gross'): ?>
  <span class="btn-zeilen"><span class="btn-klein">Werkstatt · Festnetz</span><span class="btn-nummer"><?= h($werkstatt['anzeige']) ?></span></span>
  <?php else: ?>
  <span class="btn-nummer"><?= h($werkstatt['anzeige']) ?></span>
  <?php endif; ?>
</a>
