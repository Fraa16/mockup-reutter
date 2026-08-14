<?php
/**
 * Kurzanfrage im Fussbereich — vier Felder statt der drei Schritte auf der
 * Kontaktseite. Schickt an dieselbe Route wie das ausfuehrliche Formular,
 * nur mit weniger Feldern; die Validierung sitzt serverseitig (P4).
 *
 * @var string $ueberschrift
 */
$s = site();
$leistungen = content('leistungen')['eintraege'];
$ueberschrift = $ueberschrift ?? 'Sagen Sie uns, was ansteht.';
?>
<section class="kurzanfrage" id="kurzanfrage">
  <div class="deco" aria-hidden="true"></div>
  <div class="wrap">
    <div class="kurzanfrage-grid">
      <div class="kurzanfrage-intro">
        <div class="kicker"><?= swash() ?><span class="label">Kurz anfragen</span></div>
        <h2><?= h($ueberschrift) ?></h2>
        <p>Vier Felder genügen. Wenn Sie es genauer beschreiben wollen, nehmen Sie das
           ausführliche Formular auf der Kontaktseite.</p>
        <div class="kurzanfrage-links">
          <a href="/kontakt/#anfrage">Ausführliches Formular →</a>
          <a href="tel:<?= attr(get($s, 'kontakt.telefon_link')) ?>"><?= h(get($s, 'kontakt.telefon')) ?></a>
        </div>
      </div>

      <div class="kurzanfrage-karte">
        <form method="post" action="/kontakt/#anfrage">
          <?php /* Spamfalle: echte Menschen sehen das Feld nie, Bots fuellen es aus. */ ?>
          <div class="hp-feld" aria-hidden="true">
            <label>Firma <input type="text" name="firma_zusatz" tabindex="-1" autocomplete="off"></label>
          </div>
          <input type="hidden" name="gestartet" value="<?= attr((string) time()) ?>">
          <input type="hidden" name="herkunft" value="kurzanfrage">

          <div class="field-grid">
            <label class="field">
              <span>Name <em aria-hidden="true">*</em></span>
              <input type="text" name="name" placeholder="Vor- und Nachname" autocomplete="name" required>
            </label>
            <label class="field">
              <span>Telefon</span>
              <input type="tel" name="telefon" placeholder="0170 000 0000" autocomplete="tel">
            </label>
            <label class="field">
              <span>Fahrzeug</span>
              <input type="text" name="fahrzeug" placeholder="z. B. Audi A4, 2018">
            </label>
            <label class="field">
              <span>Leistung</span>
              <select name="leistung">
                <option value="">Bitte wählen</option>
                <?php foreach ($leistungen as $l): ?>
                  <option value="<?= attr($l['chip']) ?>"><?= h($l['chip']) ?></option>
                <?php endforeach; ?>
              </select>
            </label>
          </div>

          <div class="kurzanfrage-fuss">
            <label class="consent">
              <input type="checkbox" name="datenschutz" value="1" required>
              <span>Ich habe die <a href="/datenschutz/">Datenschutzerklärung</a> gelesen.</span>
            </label>
            <button type="submit" class="btn btn-red">
              Anfrage senden <span class="btn-arrow" aria-hidden="true">→</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</section>
