<?php
/**
 * Roter Banner "Mobiler Service". Startseite und jede Leistungsseite.
 */
$s = site();
$b = $b ?? content('home')['mobiler_service'];
?>
<section class="mobile-banner">
  <div class="deco" aria-hidden="true"></div>
  <div class="wrap">
    <div class="copy">
      <div class="eyebrow"><?= h($b['eyebrow']) ?></div>
      <h2><?= h($b['titel']) ?></h2>
      <p><?= h($b['text']) ?></p>
    </div>
    <div class="actions">
      <a href="/kontakt/#anfrage" class="btn btn-dark">Mobilen Termin anfragen</a>
      <a href="tel:<?= attr(get($s, 'kontakt.mobil_link')) ?>" class="btn btn-outline">Mobil <?= h(get($s, 'kontakt.mobil')) ?></a>
    </div>
  </div>
</section>
