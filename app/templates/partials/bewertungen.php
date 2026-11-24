<?php
/**
 * Kundenbewertungen — Kopfzeile mit dem Google-Schnitt und darunter die Zitate.
 *
 * Stand zweimal fast gleich in der Datei: einmal im Abschnitt „Betrieb" der
 * Startseite, einmal im geteilten Fuss fuer alle anderen Seiten. Unterschied
 * war allein die Ueberschriftenebene — auf der Startseite sitzt der Block in
 * einer Sektion, die ihre h2 schon hat, deshalb dort h3.
 *
 * Aufgefallen ist die Doppelung, als der Link zum Google-Profil nur an einer
 * der beiden Stellen erschien.
 *
 * @var string $ebene  'h2' oder 'h3'
 */
$s        = site();
$ebene    = in_array($ebene ?? 'h2', ['h2', 'h3'], true) ? $ebene : 'h2';
?>
<div class="reviews-head">
  <<?= $ebene ?>><?= h(get($s, 'bewertungen.titel', 'Was Kunden schreiben')) ?></<?= $ebene ?>>

  <?php /* Seit November 2026 das runde Siegel statt der Zeile mit Sternen.
          Es bleibt ein gewoehnlicher Link zum Google-Profil: beim
          Seitenaufruf geht nichts an Google, die strikte CSP bleibt gueltig
          und es braucht keinen Einwilligungsbanner. Wer klickt, kann die Zahl
          dort nachpruefen. */ ?>
  <?php partial('bewertungs-siegel', ['klasse' => 'ist-bewertungen']); ?>
</div>

<?php /* Eine Spur statt eines festen Dreierrasters. Gewischt und gescrollt
        wird allein ueber CSS — die Knoepfe und Punkte darunter setzt das
        Skript ein, damit ohne JavaScript keine toten Bedienelemente
        herumstehen. */ ?>
<div class="review-schieber" data-karussell>
  <div class="review-grid" role="group" tabindex="0"
       aria-label="Kundenbewertungen, seitlich scrollbar">
    <?php foreach (get($s, 'bewertungen.eintraege', []) as $r): ?>
    <div class="review-card">
      <span class="stars" aria-hidden="true">★★★★★</span>
      <p>„<?= h($r['text']) ?>"</p>
      <div class="author">
        <?= swash() ?>
        <span class="name"><?= h($r['name']) ?></span>
        <?php /* Echte Rezensionen tragen keinen Ort. Steht nichts dabei,
                darf auch der Trenner nicht dastehen. */ ?>
        <?php if (($auftrag = (string) ($r['auftrag'] ?? '')) !== ''): ?>
        <span class="loc">· <?= h($auftrag) ?></span>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
