<?php
/**
 * Bestaetigung nach dem Formularversand. Ziel des Redirects nach dem POST
 * (Post-Redirect-Get), damit ein Neuladen die Anfrage nicht doppelt schickt.
 *
 * Steht nicht im Design-Bundle — aus vorhandenen Bausteinen gebaut.
 * Bewusst ohne Kurzanfrage im Fuss: wer gerade abgeschickt hat, soll nicht
 * gleich das naechste Formular sehen.
 */
$s = site();

partial('kopf', [
    'titel'        => 'Anfrage ist raus — Smartrepair Reutter',
    'beschreibung' => 'Ihre Anfrage ist bei uns eingegangen. Wir melden uns, sobald es die Werkstatt zulässt.',
    'aktiv'        => '',
]);
?>

<section class="danke">
  <div class="accent-bar" aria-hidden="true"></div>
  <div class="wrap">
    <div class="kicker"><?= swash() ?><span class="label">Anfrage eingegangen</span></div>
    <h1>Danke — wir haben Ihre Anfrage.</h1>
    <p class="lead">
      Wir sehen sie uns an und melden uns, sobald es die Werkstatt zulässt. Wenn es
      eilig ist, rufen Sie an — telefonisch sind wir am schnellsten erreichbar.
    </p>

    <div class="danke-wege">
      <a class="danke-weg" href="tel:<?= attr(get($s, 'kontakt.telefon_link')) ?>">
        <span class="weg-label">Werkstatt</span>
        <span class="weg-wert"><?= h(get($s, 'kontakt.telefon')) ?></span>
      </a>
      <a class="danke-weg" href="tel:<?= attr(get($s, 'kontakt.mobil_link')) ?>">
        <span class="weg-label">Mobil</span>
        <span class="weg-wert"><?= h(get($s, 'kontakt.mobil')) ?></span>
      </a>
      <a class="danke-weg" href="mailto:<?= attr(get($s, 'kontakt.email')) ?>">
        <span class="weg-label">E-Mail</span>
        <span class="weg-wert"><?= h(get($s, 'kontakt.email')) ?></span>
      </a>
    </div>

    <?php /* Weiterlesen statt Sackgasse — die drei haeufigsten Ziele. */ ?>
    <div class="danke-weiter">
      <div class="weiter-titel">Solange Sie hier sind</div>
      <div class="weiter-links">
        <a href="/leistungen/">Alle Leistungen ansehen</a>
        <a href="/galerie/">Arbeiten in der Galerie</a>
        <a href="/">Zurück zur Startseite</a>
      </div>
    </div>
  </div>
</section>

<?php partial('fuss', ['zeigeFormular' => false, 'zeigeLeiste' => false]); ?>
