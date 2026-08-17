<?php
/**
 * Vertrauensleiste unter dem Hero. Wird auch auf den Leistungsseiten benutzt.
 *
 * @var list<array{titel:string,text:string}> $punkte
 */
$punkte = $punkte ?? content('home')['trust'];
?>
<section class="trust-strip" aria-labelledby="trust-titel">
  <div class="wrap">
    <?php /* Der Entwurf zeigt hier keine Überschrift — die vier Punkte stehen
            für sich. Ohne eine sprang die Startseite aber von der h1 direkt
            auf h3, und wer die Seite vorlesen lässt, bekommt vier
            zusammenhanglose Titel. Deshalb eine Überschrift, die nur
            Vorleseprogramme hören. Optisch ändert sich nichts. */ ?>
    <h2 class="visually-hidden" id="trust-titel">Was den Betrieb ausmacht</h2>
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
