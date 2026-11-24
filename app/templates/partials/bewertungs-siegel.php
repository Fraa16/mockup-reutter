<?php
/**
 * Rundes Bewertungssiegel: Schnitt und Anzahl aus dem Google-Profil.
 *
 * Seit November 2026, auf Wunsch des Betriebs „etwas wie ein Siegel" statt
 * der schlichten Zeile mit Sternen. Gezeichnet als Inline-SVG: Der Schriftring
 * laeuft auf einem Kreispfad, die Schrift kommt per CSS aus den eigenen
 * Webfonts — es wird nichts nachgeladen, die CSP bleibt unberuehrt.
 *
 * Bewusst ohne Google-Logo: Das Zeichen ist eine Marke und darf nicht als
 * Guetesiegel auftreten. Der Name steht als Wort im Ring — das ist eine
 * Quellenangabe, und der Link fuehrt zum echten Profil, wo jeder die Zahl
 * nachpruefen kann.
 *
 * Kann mehrfach auf einer Seite stehen (Startseite: Kopf und Betrieb). Die
 * Kennung des Kreispfads wird deshalb hochgezaehlt.
 *
 * @var string $klasse  zusaetzliche Klasse fuer den Einsatzort
 */
static $nr = 0;
$nr++;
$s       = site();
$klasse  = trim('bewertungs-siegel ' . ($klasse ?? ''));
$schnitt = (string) get($s, 'kennzahlen.google_bewertung.wert', '');
$anzahl  = (string) get($s, 'kennzahlen.google_anzahl.wert', '');
$profil  = (string) get($s, 'kennzahlen.google_profil_url', '');
$pfad    = 'siegel-ring-' . $nr;

if ($schnitt === '') {
    return;
}

/* Fuer Screenreader in einem Satz. „300+" wird zu „über 300". */
$anzahlText = str_ends_with($anzahl, '+') ? 'über ' . rtrim($anzahl, '+') : $anzahl;
$satz       = $schnitt . ' von 5 Sternen' . ($anzahl !== '' ? ' bei ' . $anzahlText . ' Google-Bewertungen' : ' auf Google');
$ring       = 'GOOGLE-BEWERTUNGEN · SMARTREPAIR REUTTER · ';

$tag   = $profil !== '' ? 'a' : 'div';
$attrs = $profil !== ''
    ? ' href="' . attr($profil) . '" target="_blank" rel="noopener noreferrer nofollow"'
    : '';
?>
<<?= $tag ?> class="<?= attr($klasse) ?>"<?= $attrs ?>>
  <svg class="siegel-bild" viewBox="0 0 200 200" width="200" height="200" aria-hidden="true" focusable="false">
    <defs>
      <path id="<?= attr($pfad) ?>" d="M100,100 m-80,0 a80,80 0 1,1 160,0 a80,80 0 1,1 -160,0"/>
    </defs>
    <circle class="siegel-grund" cx="100" cy="100" r="97"/>
    <circle class="siegel-zacken" cx="100" cy="100" r="92"/>
    <g class="siegel-ring">
      <text class="siegel-ringtext"><textPath href="#<?= attr($pfad) ?>" textLength="498" lengthAdjust="spacing"><?= h($ring) ?></textPath></text>
    </g>
    <circle class="siegel-innen" cx="100" cy="100" r="64"/>
    <text class="siegel-wert" x="100" y="106" text-anchor="middle"><?= h($schnitt) ?></text>
    <text class="siegel-sterne" x="100" y="128" text-anchor="middle">★★★★★</text>
    <?php if ($anzahl !== ''): ?>
    <text class="siegel-anzahl" x="100" y="146" text-anchor="middle"><?= h($anzahl) ?> Bewertungen</text>
    <?php endif; ?>
  </svg>
  <span class="visually-hidden"><?= h($satz) ?><?= $profil !== '' ? ' — Profil bei Google, öffnet in neuem Tab' : '' ?></span>
</<?= $tag ?>>
