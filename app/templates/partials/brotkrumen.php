<?php
/**
 * Brotkrumen im Kopfbereich der Unterseiten.
 *
 * @var list<array{label:string,ziel?:string}> $pfade  Letzter Eintrag ohne
 *                                                     'ziel' ist die aktuelle Seite.
 */
$pfade = $pfade ?? [];
?>
<nav class="brotkrumen" aria-label="Brotkrumennavigation">
  <?php foreach ($pfade as $i => $p): ?>
    <?php if ($i > 0): ?><span class="trenner" aria-hidden="true">/</span><?php endif; ?>
    <?php if (isset($p['ziel'])): ?>
      <a href="<?= attr($p['ziel']) ?>"><?= h($p['label']) ?></a>
    <?php else: ?>
      <span aria-current="page"><?= h($p['label']) ?></span>
    <?php endif; ?>
  <?php endforeach; ?>
</nav>
