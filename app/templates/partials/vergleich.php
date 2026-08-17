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
    <?= bild($v['vorher'], $v['vorher_alt'], [
        'class' => 'slot-img',
        'sizes' => '(max-width: 980px) 92vw, 55vw',
    ]) ?>
  </div>
  <div class="layer layer-after">
    <?= bild($v['nachher'], $v['nachher_alt'], [
        'class' => 'slot-img',
        'sizes' => '(max-width: 980px) 92vw, 55vw',
    ]) ?>
  </div>
  <div class="ba-handle" aria-hidden="true">
    <div class="grip"><span>←</span><span>→</span></div>
  </div>
  <div class="ba-label before">Vorher</div>
  <div class="ba-label after">Nachher</div>
</div>
