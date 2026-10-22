<?php
/**
 * Vorher/Nachher-Regler. Vier der sechs Leistungsseiten benutzen ihn — anders
 * als die Kernmodule ist er tatsaechlich derselbe Baustein, deshalb geteilt.
 *
 * Die Startseite hat eine eigene Fassung mit Fallauswahl; diese hier zeigt
 * genau ein Paar.
 *
 * Wer ihn einbindet, fragt vorher vergleich_echt(): Ohne zwei verschiedene
 * Fotos entfaellt der ganze Abschnitt, nicht nur der Regler.
 *
 * Kleine Vorlagen — etwa die 600 × 450 Pixel grossen Fotos von Daniels alter
 * Seite — bekommen einen schmaleren Rahmen im Seitenverhaeltnis 4:3. Ueber die
 * volle Breite gezogen waeren sie mehr als doppelt vergroessert und unscharf,
 * und der flache Rahmen schnitte oben und unten fast die Haelfte weg.
 *
 * @var array<string,mixed> $v  Abschnitt 'vergleich' der Seiteninhalte
 */
$breiten = array_filter([
    bild_quellen(ltrim((string) $v['vorher'], '/'))['breite'],
    bild_quellen(ltrim((string) $v['nachher'], '/'))['breite'],
]);
$klein = $breiten !== [] && min($breiten) < 1200;
?>
<div class="ba-frame<?= $klein ? ' ist-klein' : '' ?>" data-start="<?= attr((string) ($v['start_position'] ?? 50)) ?>">
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
