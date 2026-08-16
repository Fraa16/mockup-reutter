<?php
/**
 * Vertrauensleiste unter dem Hero. Wird auch auf den Leistungsseiten benutzt.
 *
 * @var list<array{titel:string,text:string}> $punkte
 */
$punkte = $punkte ?? content('home')['trust'];
?>
<section class="trust-strip">
  <div class="wrap">
    <div class="grid">
      <?php foreach ($punkte as $punkt): ?>
      <div class="item">
        <?= swash() ?>
        <div>
          <h3><?= h($punkt['titel']) ?></h3>
          <p><?= h($punkt['text']) ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
