<?php
/**
 * Vorher/Nachher-Regler. Vier der sechs Leistungsseiten benutzen ihn — anders
 * als die Kernmodule ist er tatsaechlich derselbe Baustein, deshalb geteilt.
 *
 * Die Startseite hat eine eigene Fassung mit Fallauswahl; diese hier zeigt
 * genau ein Paar.
 *
 * @var array<string,mixed> $v  Abschnitt 'vergleich' der Seiteninhalte
 */
?>
<div class="ba-frame" data-start="<?= attr((string) ($v['start_position'] ?? 50)) ?>">
  <div class="layer layer-before">
    <img class="slot-img" src="<?= attr(upload($v['vorher'])) ?>" alt="<?= attr($v['vorher_alt']) ?>"
         width="1448" height="1086" loading="lazy">
  </div>
  <div class="layer layer-after">
    <img class="slot-img" src="<?= attr(upload($v['nachher'])) ?>" alt="<?= attr($v['nachher_alt']) ?>"
         width="1448" height="1086" loading="lazy">
  </div>
  <div class="ba-handle" aria-hidden="true">
    <div class="grip"><span>←</span><span>→</span></div>
  </div>
  <div class="ba-label before">Vorher</div>
  <div class="ba-label after">Nachher</div>
</div>
