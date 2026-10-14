/**
 * Prueft die echte Website im Browser — so, wie Besucher sie sehen.
 *
 * Entstanden aus einem Fehler, der monatelang unbemerkt blieb: Die
 * Sicherheitsregel in public/.htaccess verwarf auf dem Server jedes
 * style="…" im HTML. Lokal fiel das nie auf, weil dort kein Apache laeuft
 * und die Datei nicht gelesen wird. Live lagen alle Marker auf dem
 * Fahrzeugfoto uebereinander ausserhalb des Bildes.
 *
 * Solche Fehler sieht nur ein Browser gegen den echten Server. Dieses Skript
 * oeffnet jede Seite aus der Sitemap auf Desktop- und Handybreite und meldet:
 *   - Seiten, die nicht mit 200 antworten
 *   - Meldungen, die der Browser als Fehler protokolliert — darunter alles,
 *     was die Sicherheitsregel blockiert
 *   - Dateien, die nicht geladen werden (Bilder, CSS, Schriften, Skripte)
 *   - Bilder, die leer bleiben
 *   - PHP-Warnungen im Seitentext
 *   - auf der Startseite: Marker, die nicht auf dem Foto stehen
 *
 * Aufruf:  node .github/scripts/live-pruefung.mjs https://smartrepair-reutter.de
 * Ergebnis steht im Protokoll und in der Zusammenfassung des Laufs.
 * Der Lauf schlaegt fehl, sobald etwas gefunden wird.
 */
import { chromium } from 'playwright';
import { appendFileSync } from 'node:fs';

const basis = (process.argv[2] || 'https://smartrepair-reutter.de').replace(/\/+$/, '');

const breiten = [
  { name: 'Desktop', viewport: { width: 1440, height: 900 } },
  { name: 'Handy',   viewport: { width: 390,  height: 844 } },
];

// Der Panel-Zugang steht nicht in der Sitemap, wird aber genauso geprueft.
const zusaetzlich = ['/admin/'];

const PHP_MELDUNG = /<b>(?:Warning|Notice|Deprecated|Fatal error|Parse error)<\/b>:|(?:Warning|Notice|Deprecated|Fatal error|Parse error): .{0,300} on line \d+/;

async function seitenliste() {
  try {
    const antwort = await fetch(basis + '/sitemap.xml');
    const xml = await antwort.text();
    const pfade = [...xml.matchAll(/<loc>([^<]+)<\/loc>/g)].map((m) => new URL(m[1]).pathname);
    if (pfade.length > 0) {
      return [...new Set([...pfade, ...zusaetzlich])];
    }
  } catch (fehler) {
    console.log(`Sitemap nicht lesbar (${fehler.message}) — pruefe nur die Startseite.`);
  }
  return ['/', ...zusaetzlich];
}

async function pruefeSeite(kontext, pfad, breite) {
  const seite = await kontext.newPage();
  const probleme = [];

  seite.on('console', (m) => {
    // Chrome nennt bei jeder blockierten Angabe deren Pruefsumme. Ohne sie
    // sind die Meldungen gleich und lassen sich unten zusammenzaehlen.
    if (m.type() === 'error') {
      probleme.push(`Browser meldet: ${m.text().replace(/'sha256-[^']+'/g, "'sha256-…'").slice(0, 300)}`);
    }
  });
  seite.on('pageerror', (f) => probleme.push(`Skriptfehler: ${String(f.message).slice(0, 300)}`));
  seite.on('response', (r) => {
    if (r.status() >= 400) probleme.push(`${r.status()} für ${r.url().replace(basis, '')}`);
  });
  seite.on('requestfailed', (r) => {
    probleme.push(`Nicht geladen: ${r.url().replace(basis, '')} (${r.failure()?.errorText ?? 'unbekannt'})`);
  });

  let status = 0;
  try {
    const antwort = await seite.goto(basis + pfad, { waitUntil: 'networkidle', timeout: 45000 });
    status = antwort ? antwort.status() : 0;
  } catch (fehler) {
    probleme.push(`Seite nicht abrufbar: ${fehler.message.split('\n')[0]}`);
    await seite.close();
    return { status, probleme };
  }
  if (status !== 200) probleme.push(`Seite antwortet mit ${status}`);

  // Einmal ganz nach unten, damit auch die spaet ladenden Bilder kommen.
  await seite.evaluate(async () => {
    for (let y = 0; y < document.body.scrollHeight; y += 500) {
      window.scrollTo(0, y);
      await new Promise((r) => setTimeout(r, 80));
    }
  });
  // Dann warten, bis jedes sichtbare Bild fertig ist — geladen oder
  // gescheitert. Ohne das blieb das fehlende Kartenbild auf der Startseite
  // unentdeckt: Es steht ganz unten und war beim Pruefen noch unterwegs.
  // Bilder in ausgeblendeten Tafeln laden nie und zaehlen deshalb nicht.
  await seite.waitForFunction(
    () => [...document.images].filter((b) => b.getClientRects().length > 0).every((b) => b.complete),
    null,
    { timeout: 15000 },
  ).catch(() => probleme.push('Bilder nach 15 Sekunden noch nicht fertig geladen'));
  await seite.waitForTimeout(300);

  const leer = await seite.$$eval('img', (bilder) => bilder
    .filter((b) => b.complete && b.naturalWidth === 0)
    .map((b) => b.getAttribute('src')));
  for (const src of leer) probleme.push(`Bild bleibt leer: ${src}`);

  const html = await seite.content();
  const php = html.match(PHP_MELDUNG);
  if (php) probleme.push(`PHP-Meldung im Seitentext: ${php[0].replace(/<[^>]+>/g, '').slice(0, 200)}`);

  if (pfad === '/') {
    const lage = await seite.evaluate(() => {
      const foto = document.querySelector('.hotspot-visual');
      const marker = [...document.querySelectorAll('.hotspot-dot')];
      if (!foto || marker.length === 0) return null;
      const f = foto.getBoundingClientRect();
      const punkte = marker.map((m) => {
        const r = m.getBoundingClientRect();
        return { x: r.left + r.width / 2, y: r.top + r.height / 2 };
      });
      const ausserhalb = punkte.filter((p) => p.x < f.left || p.x > f.right || p.y < f.top || p.y > f.bottom).length;
      const verschieden = new Set(punkte.map((p) => `${Math.round(p.x)}/${Math.round(p.y)}`)).size;
      return { anzahl: punkte.length, ausserhalb, verschieden };
    });
    if (lage && lage.ausserhalb > 0) probleme.push(`${lage.ausserhalb} von ${lage.anzahl} Markern liegen außerhalb des Fahrzeugfotos`);
    if (lage && lage.anzahl > 1 && lage.verschieden === 1) probleme.push(`Alle ${lage.anzahl} Marker liegen übereinander`);
  }

  await seite.close();
  // Doppelte Meldungen (etwa dieselbe blockierte Angabe an mehreren
  // Elementen) einmal zaehlen, aber die Anzahl dazuschreiben.
  const gezaehlt = new Map();
  for (const p of probleme) gezaehlt.set(p, (gezaehlt.get(p) ?? 0) + 1);
  return { status, probleme: [...gezaehlt].map(([p, n]) => (n > 1 ? `${p} (${n}×)` : p)) };
}

const browser = await chromium.launch();
const pfade = await seitenliste();
const zeilen = [];
let summe = 0;

console.log(`Prüfe ${pfade.length} Seiten auf ${basis}\n`);
for (const breite of breiten) {
  const kontext = await browser.newContext({ viewport: breite.viewport });
  for (const pfad of pfade) {
    const { status, probleme } = await pruefeSeite(kontext, pfad, breite);
    summe += probleme.length;
    const marke = probleme.length === 0 ? 'ok' : `${probleme.length} Problem(e)`;
    console.log(`${breite.name.padEnd(8)} ${String(status).padEnd(4)} ${pfad.padEnd(42)} ${marke}`);
    for (const p of probleme) console.log(`           - ${p}`);
    zeilen.push(`| ${breite.name} | \`${pfad}\` | ${status} | ${probleme.length === 0 ? '✓' : probleme.map((p) => p.replace(/\|/g, '\\|')).join('<br>')} |`);
  }
  await kontext.close();
}
await browser.close();

const fazit = summe === 0
  ? `Alles in Ordnung: ${pfade.length} Seiten, je Desktop und Handy.`
  : `${summe} Problem(e) gefunden.`;
console.log(`\n${fazit}`);

if (process.env.GITHUB_STEP_SUMMARY) {
  appendFileSync(process.env.GITHUB_STEP_SUMMARY, [
    `## Live-Prüfung ${basis}`, '', fazit, '',
    '| Breite | Seite | Status | Befund |', '|---|---|---|---|', ...zeilen, '',
  ].join('\n'));
}

process.exit(summe === 0 ? 0 : 1);
