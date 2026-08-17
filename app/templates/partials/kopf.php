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
 * @var string|null $lcp_sizes    dessen sizes-Angabe, muss zum <img> passen
 * @var string|null $og_bild      Vorschaubild beim Teilen; ohne Angabe das
 *                                Hero-Bild der Startseite
 * @var string|null $og_bild_alt  dessen Beschreibung
 * @var list<array>|null $jsonld  strukturierte Daten fuer diese Seite
 */
$s = site();
$titel        = $titel        ?? get($s, 'firma.name');
$beschreibung = $beschreibung ?? '';
$aktiv        = $aktiv        ?? '';
$lcp_bild     = $lcp_bild     ?? null;
$lcp_sizes    = $lcp_sizes    ?? null;
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
<?php
  /* Das Vorladen muss dieselben Groessen kennen wie das <img> weiter unten.
     Sonst holt der Browser hier die grosse Fassung und im Seitenkoerper noch
     einmal die kleine — das Bild waere doppelt geladen. */
  $lcpQuellen = bild_quellen(ltrim($lcp_bild, '/'));
  $lcpSizes   = $lcp_sizes ?? '(max-width: 980px) 100vw, 50vw';
?>
<link rel="preload" href="<?= attr(upload($lcp_bild)) ?>" as="image" fetchpriority="high"
      <?php if ($lcpQuellen['srcset'] !== ''): ?>imagesrcset="<?= attr($lcpQuellen['srcset']) ?>" imagesizes="<?= attr($lcpSizes) ?>"<?php endif; ?>>
<?php endif; ?>
<link rel="stylesheet" href="<?= attr(asset('css/styles.css')) ?>">
<link rel="icon" href="<?= attr(asset('favicon.svg')) ?>" type="image/svg+xml">

<?php
  /* Die kanonische Adresse verhindert, dass dieselbe Seite unter mehreren
     Schreibweisen im Index landet. Der Pfad kommt aus dem Router, damit hier
     nicht geraten wird. */
  $kanonisch = absolut(seo_pfad());
  $ogBild    = seo_vorschaubild($og_bild ?? null);
?>
<link rel="canonical" href="<?= attr($kanonisch) ?>">
<?php if (!seo_indexierbar()): ?>
<?php /* Vorschau auf Vercel oder lokal — die soll nicht in den Index. Sobald
        die echte Domain hierher zeigt, faellt diese Zeile von selbst weg. */ ?>
<meta name="robots" content="noindex, nofollow">
<?php endif; ?>

<meta property="og:type" content="website">
<meta property="og:locale" content="de_DE">
<meta property="og:site_name" content="<?= attr(get($s, 'firma.name')) ?>">
<meta property="og:title" content="<?= attr($titel) ?>">
<meta property="og:description" content="<?= attr($beschreibung) ?>">
<meta property="og:url" content="<?= attr($kanonisch) ?>">
<?php if ($ogBild !== ''): ?>
<meta property="og:image" content="<?= attr($ogBild) ?>">
<meta property="og:image:alt" content="<?= attr($og_bild_alt ?? get($s, 'firma.name')) ?>">
<meta name="twitter:card" content="summary_large_image">
<?php endif; ?>

<?= seo_jsonld_ausgeben(...($jsonld ?? [])) ?>
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
