<?php
/**
 * Fehlerseite (404 und, waehrend der Entwicklung, 503 fuer fehlende Templates).
 *
 * @var string      $titel
 * @var int         $code
 * @var string|null $notiz
 */
partial('kopf', [
    'titel'        => $titel . ' — ' . get(site(), 'firma.name'),
    'beschreibung' => 'Diese Seite existiert nicht.',
]);
?>
<section class="sub-hero">
  <div class="scrim" aria-hidden="true"></div>
  <div class="accent-bar" aria-hidden="true"></div>
  <div class="wrap">
    <div class="kicker"><?= swash() ?><span class="label">Fehler <?= h((string) $code) ?></span></div>
    <h1><?= h($titel) ?></h1>
    <p class="lead">
      Die Adresse stimmt nicht mehr oder hat sich geändert. Über das Menü kommen Sie
      zurück — oder rufen Sie einfach an, das geht meistens schneller.
    </p>
    <?php if (!empty($notiz)): ?>
    <p class="fineprint"><?= h($notiz) ?></p>
    <?php endif; ?>
    <div class="cta-row">
      <a href="/" class="btn btn-red">Zur Startseite <span class="btn-arrow" aria-hidden="true">→</span></a>
      <a href="tel:<?= attr(get(site(), 'kontakt.telefon_link')) ?>" class="btn btn-outline"><?= h(get(site(), 'kontakt.telefon')) ?></a>
    </div>
  </div>
</section>
<?php partial('fuss'); ?>
