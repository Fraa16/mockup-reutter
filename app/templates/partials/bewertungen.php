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
$kennzahl = static fn (string $k): string => (string) get(site(), "kennzahlen.$k.wert", '');
$profil   = (string) get($s, 'kennzahlen.google_profil_url', '');
$zeile    = $kennzahl('google_bewertung') . ' auf Google · ' . $kennzahl('google_anzahl') . ' Bewertungen';
?>
<div class="reviews-head">
  <<?= $ebene ?>><?= h(get($s, 'bewertungen.titel', 'Was Kunden schreiben')) ?></<?= $ebene ?>>

  <?php /* Die Bewertung war eine Behauptung ohne Nachweis — die Profil-URL
          stand in site.json und wurde nirgends benutzt. Als Link kann sie
          jeder nachpruefen, und die drei Zitate darunter stehen nicht mehr
          allein da. Ein gewoehnlicher Link, kein eingebetteter Dienst: beim
          Seitenaufruf geht nichts an Google, die strikte CSP bleibt gueltig
          und es braucht weiterhin keinen Einwilligungsbanner. */ ?>
  <div class="rating">
    <span class="stars" aria-hidden="true">★★★★★</span>
    <?php if ($profil !== ''): ?>
    <a class="count" href="<?= attr($profil) ?>" rel="noopener noreferrer nofollow" target="_blank">
      <?= h($zeile) ?><span class="visually-hidden"> — Profil bei Google, öffnet in neuem Tab</span>
      <span aria-hidden="true">↗</span>
    </a>
    <?php else: ?>
    <span class="count"><?= h($zeile) ?></span>
    <?php endif; ?>
  </div>
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
