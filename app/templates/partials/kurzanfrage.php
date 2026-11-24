<?php
/**
 * Kontaktband im Fussbereich: Mobil- und Werkstattnummer.
 *
 * Hier stand bis Oktober 2026 ein Kurzformular mit vier Feldern. Der Inhaber
 * will aber angerufen werden — Formulare und E-Mails moechte er nicht als
 * Weg anbieten. Deshalb jetzt dieselben zwei Kacheln wie auf der Kontaktseite.
 * (Bis November 2026 war die zweite Kachel WhatsApp.)
 * Das ausfuehrliche Formular gibt es nur noch ganz unten auf /kontakt/.
 *
 * Der Dateiname bleibt, damit fuss.php und die Sprungmarke #kurzanfrage in
 * alten Verweisen weiter passen.
 *
 * @var string $ueberschrift
 */
$ueberschrift = $ueberschrift ?? 'Sagen Sie uns, was ansteht.';
?>
<section class="kurzanfrage" id="kurzanfrage">
  <div class="deco" aria-hidden="true"></div>
  <div class="wrap">
    <div class="kurzanfrage-grid">
      <div class="kurzanfrage-intro">
        <div class="kicker"><?= swash() ?><span class="label">Direkt sprechen</span></div>
        <h2><?= h($ueberschrift) ?></h2>
        <p>Rufen Sie an und beschreiben Sie kurz, worum es geht — mobil oder direkt in
           der Werkstatt. Am Telefon klärt sich das meiste in ein paar Minuten.</p>
      </div>

      <?php partial('kontakt-kacheln'); ?>
    </div>
  </div>
</section>
