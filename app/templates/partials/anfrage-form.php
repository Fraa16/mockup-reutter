<?php
/**
 * Anfrageformular. Startseite und Kontaktseite benutzen dasselbe Markup.
 *
 * Grundsatz: Das Formular funktioniert vollstaendig ohne JavaScript — alle drei
 * Schritte sind sichtbar und werden in einem Rutsch abgeschickt. Erst main.js
 * macht daraus den Stepper aus dem Mockup. Deshalb steht hier kein hidden.
 *
 * @var array<string,mixed>  $a       Konfiguration aus home.json -> anfrage
 * @var array<string,string> $werte   Wiederbefuellung nach Validierungsfehler
 * @var array<string,string> $fehler  Feldname => Meldung
 */
$a      = $a      ?? content('home')['anfrage'];
$werte  = $werte  ?? [];
$fehler = $fehler ?? [];

/** Feldwert nach einem Fehlversuch zurueckschreiben. */
$w = static fn (string $name): string => attr($werte[$name] ?? '');
?>
<div class="request-card">
  <div class="step-tracker" id="step-tracker" aria-hidden="true"></div>

  <?php if ($fehler !== []): ?>
  <div class="form-fehler" role="alert">
    <strong>Da fehlt noch etwas:</strong>
    <ul>
      <?php foreach ($fehler as $meldung): ?><li><?= h($meldung) ?></li><?php endforeach; ?>
    </ul>
  </div>
  <?php endif; ?>

  <form id="request-form" method="post" action="/kontakt/#anfrage" enctype="multipart/form-data">
    <?php /* Spamfalle: echte Menschen sehen das Feld nie, Bots fuellen es aus. */ ?>
    <div class="hp-feld" aria-hidden="true">
      <label>Firma <input type="text" name="firma_zusatz" tabindex="-1" autocomplete="off"></label>
    </div>
    <input type="hidden" name="gestartet" value="<?= attr((string) time()) ?>">

    <fieldset class="form-step" data-step="1">
      <legend class="visually-hidden">Schritt 1 von 3: Fahrzeug</legend>
      <h3>Um welches Fahrzeug geht es?</h3>
      <p>Marke, Modell und Baujahr reichen. Farbe hilft uns bei Lackarbeiten.</p>
      <div class="field-grid">
        <label class="field"><span>Marke</span><input type="text" name="marke" placeholder="z. B. BMW" value="<?= $w('marke') ?>"></label>
        <label class="field"><span>Modell</span><input type="text" name="modell" placeholder="z. B. 3er Touring" value="<?= $w('modell') ?>"></label>
        <label class="field"><span>Baujahr</span><input type="text" name="baujahr" inputmode="numeric" placeholder="2019" value="<?= $w('baujahr') ?>"></label>
        <label class="field"><span>Lackfarbe</span><input type="text" name="lackfarbe" placeholder="Schwarz metallic" value="<?= $w('lackfarbe') ?>"></label>
      </div>
    </fieldset>

    <fieldset class="form-step" data-step="2">
      <legend class="visually-hidden">Schritt 2 von 3: Leistung</legend>
      <h3>Was soll gemacht werden?</h3>
      <p>Mehrfachauswahl. Wenn Sie unsicher sind, schreiben Sie es unten in Worten.</p>
      <?php /* Ohne JS sind das echte Checkboxen. main.js stylt sie zu den Chips
              aus dem Mockup, ohne das Formularverhalten zu aendern. */ ?>
      <div class="chip-row" id="chip-row">
        <?php foreach ($a['chips'] as $i => $chip): ?>
        <label class="chip" for="chip-<?= $i ?>">
          <input type="checkbox" id="chip-<?= $i ?>" name="leistungen[]" value="<?= attr($chip) ?>"
                 <?= in_array($chip, (array) ($werte['leistungen'] ?? []), true) ? 'checked' : '' ?>>
          <?= swash() ?><span><?= h($chip) ?></span>
        </label>
        <?php endforeach; ?>
      </div>
      <label class="field desc-field">
        <span>Beschreibung</span>
        <textarea name="beschreibung" rows="4" placeholder="z. B. Hagelschaden auf Dach und Motorhaube, Fahrzeug stand im Freien, Lack ist nicht aufgeplatzt."><?= h($werte['beschreibung'] ?? '') ?></textarea>
      </label>
      <label class="dropzone" for="fotos">
        <span class="icon" aria-hidden="true">+</span>
        <div>
          <div class="title">Fotos vom Schaden anhängen</div>
          <div class="hint">JPG, PNG oder WebP, bis 3 Bilder, gerne aus zwei Winkeln bei Tageslicht</div>
        </div>
        <input type="file" id="fotos" name="fotos[]" multiple accept="image/jpeg,image/png,image/webp">
      </label>
    </fieldset>

    <fieldset class="form-step" data-step="3">
      <legend class="visually-hidden">Schritt 3 von 3: Kontakt</legend>
      <h3>Wie erreichen wir Sie?</h3>
      <p>Wir rufen an, wenn wir eine Frage haben — sonst kommt die Einschätzung per Mail.</p>
      <div class="field-grid" style="margin-bottom:24px">
        <label class="field<?= isset($fehler['name']) ? ' has-error' : '' ?>">
          <span>Name <em aria-hidden="true">*</em></span>
          <input type="text" name="name" placeholder="Vor- und Nachname" autocomplete="name" required value="<?= $w('name') ?>">
        </label>
        <label class="field<?= isset($fehler['telefon']) ? ' has-error' : '' ?>">
          <span>Telefon</span>
          <input type="tel" name="telefon" placeholder="0170 000 0000" autocomplete="tel" value="<?= $w('telefon') ?>">
        </label>
        <label class="field<?= isset($fehler['email']) ? ' has-error' : '' ?>">
          <span>E-Mail</span>
          <input type="email" name="email" placeholder="name@mail.de" autocomplete="email" value="<?= $w('email') ?>">
        </label>
        <label class="field">
          <span>Wo soll gearbeitet werden</span>
          <select name="ort">
            <?php foreach (['In der Werkstatt', 'Mobil bei mir vor Ort', 'Weiß ich noch nicht'] as $opt): ?>
            <option<?= ($werte['ort'] ?? '') === $opt ? ' selected' : '' ?>><?= h($opt) ?></option>
            <?php endforeach; ?>
          </select>
        </label>
      </div>
      <p class="pflicht-hinweis">Wir brauchen mindestens Telefon <em>oder</em> E-Mail, sonst können wir nicht antworten.</p>
      <label class="consent<?= isset($fehler['datenschutz']) ? ' has-error' : '' ?>">
        <input type="checkbox" name="datenschutz" value="1" required <?= isset($werte['datenschutz']) ? 'checked' : '' ?>>
        <span>Ich habe die <a href="/datenschutz/">Datenschutzerklärung</a> gelesen. Meine Daten werden nur für die Bearbeitung dieser Anfrage genutzt.</span>
      </label>
    </fieldset>

    <div class="form-nav">
      <button type="button" class="btn-back" id="form-prev" hidden>Zurück</button>
      <div class="right">
        <span class="hint" id="form-hint"></span>
        <button type="submit" class="btn btn-red btn-next" id="form-next">Anfrage senden <span class="btn-arrow" aria-hidden="true">→</span></button>
      </div>
    </div>
  </form>
</div>
