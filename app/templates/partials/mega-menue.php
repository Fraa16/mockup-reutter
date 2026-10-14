<?php
/**
 * Aufklappmenue „Leistungen" im Kopf jeder Seite.
 *
 * Am Schreibtisch eine Tafel unter dem Kopf ueber die ganze Breite: links
 * was fuer alle Leistungen gilt, in der Mitte die sieben Seiten, rechts ein
 * Foto, das beim Ueberfahren mitwechselt — dasselbe Prinzip wie der Index
 * auf /leistungen/. Auf dem Handy klappt die Liste im Menuepanel auf, ohne
 * Foto und ohne Einleitung.
 *
 * „Leistungen" selbst bleibt ein Link auf die Uebersicht; aufgeklappt wird
 * ueber den Winkel daneben. So fuehrt der Punkt ohne JavaScript weiter dorthin
 * wie bisher, und mit der Tastatur ist beides erreichbar. Am Schreibtisch
 * oeffnet die Tafel ausserdem beim Ueberfahren mit der Maus (reines CSS).
 * main.js schaltet aria-expanded, schliesst mit Esc und beim Klick daneben.
 *
 * Die Fotos laden erst beim ersten Aufklappen: Solange die Tafel zu ist, steht
 * sie auf display:none, und dort laedt der Browser keine Bilder mit
 * loading="lazy".
 *
 * @var array<string,mixed> $punkt      Navigationspunkt aus site.json
 * @var bool                $ist_aktiv  Seite liegt unter /leistungen/
 */
$liste = leistungen_mit_zusatz();
$hub   = content('leistungen-hub');
$pfad  = seo_pfad();
$anruf = anruf();

/* Die Vorschau zeigt zuerst die Leistung, auf deren Seite man gerade ist. */
$start = 0;
foreach ($liste as $i => $l) {
    if ($pfad === '/leistungen/' . $l['slug'] . '/') {
        $start = $i;
    }
}
?>
<div class="nav-punkt hat-mega" data-mega>
  <a href="<?= attr($punkt['ziel']) ?>" class="nav-link<?= $ist_aktiv ? ' is-active' : '' ?>"<?= $pfad === $punkt['ziel'] ? ' aria-current="page"' : '' ?>><?= h($punkt['label']) ?></a>
  <button type="button" class="mega-schalter" aria-expanded="false" aria-controls="mega-leistungen" aria-label="Untermenü <?= attr($punkt['label']) ?>">
    <?= icon('aufklappen') ?>
  </button>

  <div class="mega" id="mega-leistungen" data-mega-tafel>
    <div class="wrap mega-grid">
      <div class="mega-intro">
        <div class="kicker"><?= swash() ?><span class="label"><?= h($punkt['label']) ?></span></div>
        <p class="mega-titel"><?= h((string) count($liste)) ?> Leistungen, ein Ansprechpartner.</p>
        <?php if (($gilt = get($hub, 'hero.gilt', [])) !== []): ?>
        <ul class="mega-gilt">
          <?php foreach ($gilt as $g): ?>
          <li><?= swash() ?><span><?= h($g) ?></span></li>
          <?php endforeach; ?>
        </ul>
        <?php endif; ?>
        <a class="mega-anruf" href="<?= attr($anruf['ziel']) ?>">
          <?= icon('telefon') ?>
          <span><span class="mega-anruf-klein">Fragen? Direkt anrufen</span><?= h($anruf['anzeige']) ?></span>
        </a>
      </div>

      <ul class="mega-liste">
        <?php foreach ($liste as $i => $l): ?>
        <?php $hier = $pfad === '/leistungen/' . $l['slug'] . '/'; ?>
        <li>
          <a class="mega-eintrag<?= $hier ? ' is-hier' : '' ?>" href="/leistungen/<?= attr($l['slug']) ?>/" data-mega-eintrag="<?= $i ?>"<?= $hier ? ' aria-current="page"' : '' ?>>
            <span class="mega-num"><?= h($l['num']) ?></span>
            <span class="mega-text">
              <span class="mega-name"><?= h($l['seitenname']) ?></span>
              <span class="mega-worte"><?= h(implode(' · ', array_slice($l['schlagworte'] ?? [], 0, 2))) ?></span>
            </span>
            <span class="mega-pfeil" aria-hidden="true">→</span>
          </a>
        </li>
        <?php endforeach; ?>
        <li class="mega-alle-platz">
          <a class="mega-alle" href="<?= attr($punkt['ziel']) ?>"<?= $pfad === $punkt['ziel'] ? ' aria-current="page"' : '' ?>>
            Alle Leistungen im Überblick <span aria-hidden="true">→</span>
          </a>
        </li>
      </ul>

      <?php /* Reine Begleitung: jede Zeile nennt ihre Leistung schon selbst.
              Deshalb aus dem Vorlesefluss genommen. */ ?>
      <div class="mega-vorschau" aria-hidden="true">
        <div class="mega-bilder">
          <?php foreach ($liste as $i => $l): ?>
          <span class="mega-bild<?= $i === $start ? ' is-aktiv' : '' ?>" data-mega-bild="<?= $i ?>">
            <?= bild((string) $l['bild'], '', ['class' => 'slot-img', 'sizes' => '340px']) ?>
          </span>
          <?php endforeach; ?>
          <span class="mega-verlauf"></span>
        </div>
        <?php foreach ($liste as $i => $l): ?>
        <div class="mega-info<?= $i === $start ? ' is-aktiv' : '' ?>" data-mega-info="<?= $i ?>">
          <span class="mega-tag"><?= h($l['num']) ?> — <?= h($l['tag']) ?></span>
          <p><?= h($l['kurz']) ?></p>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>
