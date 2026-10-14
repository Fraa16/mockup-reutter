<?php
/**
 * Kontaktband im Fussbereich: Anruf und WhatsApp.
 *
 * Hier stand bis Oktober 2026 ein Kurzformular mit vier Feldern. Der Inhaber
 * will aber angerufen werden — Formulare und E-Mails moechte er nicht als
 * Weg anbieten. Deshalb jetzt dieselben zwei Kacheln wie auf der Kontaktseite.
 * Das ausfuehrliche Formular gibt es nur noch ganz unten auf /kontakt/.
 *
 * Der Dateiname bleibt, damit fuss.php und die Sprungmarke #kurzanfrage in
 * alten Verweisen weiter passen.
 *
 * @var string $ueberschrift
 * @var string $leistung  fuer die WhatsApp-Nachricht
 */
$ueberschrift = $ueberschrift ?? 'Sagen Sie uns, was ansteht.';
$leistung     = $leistung ?? '';
?>
<section class="kurzanfrage" id="kurzanfrage">
  <div class="deco" aria-hidden="true"></div>
  <div class="wrap">
    <div class="kurzanfrage-grid">
      <div class="kurzanfrage-intro">
        <div class="kicker"><?= swash() ?><span class="label">Direkt sprechen</span></div>
        <h2><?= h($ueberschrift) ?></h2>
        <p>Rufen Sie an und beschreiben Sie kurz, worum es geht — am Telefon klärt sich
           das meiste in ein paar Minuten. Oder schicken Sie Fotos per WhatsApp, dann
           können wir meist schon etwas dazu sagen.</p>
      </div>

      <?php partial('kontakt-kacheln', ['leistung' => $leistung]); ?>
    </div>
  </div>
</section>
