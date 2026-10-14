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
      <?php partial('anruf-knoepfe', ['groesse' => 'kompakt', 'anruf_klasse' => 'btn-dark', 'leistung' => $leistung ?? '']); ?>
    </div>
  </div>
</section>
