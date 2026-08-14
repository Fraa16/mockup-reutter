<?php
/**
 * Seitenkopf: <head>, Utility-Bar und Header. Wird von jedem Seitentemplate
 * als erstes eingebunden.
 *
 * @var string      $titel        Seitentitel fuer <title> und og:title
 * @var string      $beschreibung Meta-Description
 * @var string      $aktiv        Schluessel des aktiven Menuepunkts:
 *                                leistungen | galerie | betrieb | kontakt
 * @var string|null $lcp_bild     Bild ueber der Falz, wird vorgeladen
 */
$s = site();
$titel        = $titel        ?? get($s, 'firma.name');
$beschreibung = $beschreibung ?? '';
$aktiv        = $aktiv        ?? '';
$lcp_bild     = $lcp_bild     ?? null;
?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= h($titel) ?></title>
<meta name="description" content="<?= attr($beschreibung) ?>">

<?php /* Die zwei Schnitte, die ueber der Falz gebraucht werden. Der Rest laedt
        nach — so blockiert nichts das erste Rendern. */ ?>
<link rel="preload" href="<?= attr(asset('fonts/saira-800-latin.woff2')) ?>" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="<?= attr(asset('fonts/barlow-400-latin.woff2')) ?>" as="font" type="font/woff2" crossorigin>
<?php if ($lcp_bild !== null): ?>
<link rel="preload" href="<?= attr(upload($lcp_bild)) ?>" as="image" fetchpriority="high">
<?php endif; ?>
<link rel="stylesheet" href="<?= attr(asset('css/styles.css')) ?>">
<link rel="icon" href="<?= attr(asset('favicon.svg')) ?>" type="image/svg+xml">

<meta property="og:type" content="website">
<meta property="og:locale" content="de_DE">
<meta property="og:site_name" content="<?= attr(get($s, 'firma.name')) ?>">
<meta property="og:title" content="<?= attr($titel) ?>">
<meta property="og:description" content="<?= attr($beschreibung) ?>">
</head>
<body>

<a class="skip-link" href="#inhalt">Zum Inhalt springen</a>

<!-- Utility bar -->
<div class="utility-bar">
  <div class="wrap">
    <div class="left">
      <span class="areas"><?= swash() ?><?= h(implode(' · ', get($s, 'einsatzgebiet', []))) ?></span>
      <span class="sep">|</span>
      <span><?= h(get($s, 'utility_bar.zusatz')) ?></span>
    </div>
    <div class="right">
      <span class="hours"><?= h(get($s, 'oeffnungszeiten.text')) ?></span>
      <span class="sep">|</span>
      <a class="phone" href="tel:<?= attr(get($s, 'kontakt.telefon_link')) ?>"><?= h(get($s, 'kontakt.telefon')) ?></a>
    </div>
  </div>
</div>

<!-- Header -->
<header class="site-header">
  <div class="wrap">
    <a href="/" class="logo" aria-label="<?= attr(get($s, 'firma.name')) ?> — zur Startseite">
      <span class="wordmark"><span>REU</span><span>T</span><?= swash() ?><span>T</span><span>ER</span></span>
      <span class="divider"></span>
      <span class="sub">Fahrzeug<br>pflege</span>
    </a>
    <button class="nav-toggle" id="nav-toggle" aria-label="Menü öffnen" aria-expanded="false" aria-controls="main-nav">
      <span></span><span></span><span></span>
    </button>
    <nav class="main-nav" id="main-nav" aria-label="Hauptnavigation">
      <?php foreach (get($s, 'navigation', []) as $punkt): ?>
        <?php $ist_aktiv = $punkt['schluessel'] === $aktiv; ?>
        <a href="<?= attr($punkt['ziel']) ?>" class="nav-link<?= $ist_aktiv ? ' is-active' : '' ?>"<?= $ist_aktiv ? ' aria-current="page"' : '' ?>><?= h($punkt['label']) ?></a>
      <?php endforeach; ?>
      <a href="/kontakt/#anfrage" class="btn btn-red">Termin anfragen</a>
    </nav>
  </div>
</header>

<?php /* Utility-Leiste und Kopf sind fixiert; dieser Abstandhalter haelt den
        Inhalt darunter. Hoehe steckt in --kopf-hoehe, damit die Sprungmarken
        denselben Wert benutzen koennen. */ ?>
<div class="kopf-abstand" aria-hidden="true"></div>

<main id="inhalt">
