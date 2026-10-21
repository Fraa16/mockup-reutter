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
import { chromium, request } from 'playwright';
import { appendFileSync, readFileSync } from 'node:fs';

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

/**
 * Kommt beim Besucher die CSS- und JS-Datei an, die im Repository steht?
 *
 * Entstanden am 16.10.2026: Nach dem Hochladen zeigte die Seite das neue HTML
 * mit der alten styles.css — das Aufklappmenue lag offen und ungestaltet
 * ueber dem Hero. Die Pruefung unten fand nichts, weil eine veraltete
 * Stildatei keinen Fehler wirft. Deshalb hier der direkte Vergleich: Adresse
 * aus der Startseite lesen, Datei abrufen, Inhalt mit dem Repository
 * vergleichen. Die Antwortkoepfe stehen im Protokoll, damit sichtbar wird,
 * wer eine alte Fassung ausliefert — Server, Zwischenspeicher oder Browser.
 */
async function auslieferungPruefen() {
  const probleme = [];
  let html = '';
  try {
    html = await (await fetch(basis + '/', { cache: 'no-store' })).text();
  } catch (fehler) {
    return [`Startseite fuer den Dateivergleich nicht abrufbar: ${fehler.message}`];
  }
  const dateien = [
    ['css/styles.css', /href="(\/assets\/css\/styles\.css[^"]*)"/],
    ['js/main.js', /src="(\/assets\/js\/main\.js[^"]*)"/],
  ];
  for (const [datei, muster] of dateien) {
    const treffer = html.match(muster);
    if (!treffer) {
      probleme.push(`${datei}: kein Verweis in der Startseite gefunden`);
      continue;
    }
    const adresse = treffer[1].replace(/&amp;/g, '&');
    let soll = '';
    try {
      soll = readFileSync(new URL(`../../public/assets/${datei}`, import.meta.url), 'utf8');
    } catch {
      console.log(`${datei}: Repository-Fassung nicht lesbar — Vergleich uebersprungen.`);
      continue;
    }
    for (const [name, url] of [['wie verlinkt', adresse], ['am Zwischenspeicher vorbei', adresse + (adresse.includes('?') ? '&' : '?') + 'frisch=' + Date.now()]]) {
      const antwort = await fetch(basis + url, { cache: 'no-store' });
      const ist = await antwort.text();
      const koepfe = ['cache-control', 'expires', 'age', 'last-modified', 'etag', 'server', 'x-cache', 'cf-cache-status', 'via']
        .map((k) => [k, antwort.headers.get(k)]).filter(([, v]) => v).map(([k, v]) => `${k}=${v}`).join(' · ');
      const gleich = ist === soll;
      console.log(`${datei} (${name}): ${url} → ${antwort.status}, ${ist.length} Zeichen, ${gleich ? 'wie im Repository' : `ABWEICHEND (Repository ${soll.length} Zeichen)`}`);
      console.log(`    ${koepfe}`);
      if (!gleich) probleme.push(`${datei} ${name}: ausgeliefert wird nicht die Fassung aus dem Repository (${ist.length} statt ${soll.length} Zeichen)`);
    }
  }
  return probleme;
}

/**
 * Inhaltsabgleich: Stehen auf dem Server die Texte aus dem Repository?
 *
 * Inhalte (data/content/) laedt das Hochladen bewusst nicht mit hoch — sie
 * gehen von Hand per FileZilla auf den Server, und nach der Uebergabe pflegt
 * Daniel sie im Panel. Ob ein Upload angekommen ist, liess sich bisher nur
 * durch Hinsehen klaeren.
 *
 * Dafuer rendert der Lauf dieselben Seiten zusaetzlich aus dem Repository
 * (PHP-Server im Workflow, Adresse in REPO_STAND) und vergleicht die Texte
 * beider Fassungen: was im Repository steht, live aber fehlt, und umgekehrt.
 * Weil beide Seiten durch denselben Code laufen, bleiben nur Unterschiede im
 * Inhalt uebrig.
 *
 * Nur ein Hinweis, kein Fehler: Nach der Uebergabe ist der Server im Recht,
 * und Abweichungen sind dann gewollt.
 */
const REPO_STAND = (process.env.REPO_STAND || '').replace(/\/+$/, '');

// Welche Inhaltsdatei hinter einer Seite steht — nur fuer die Meldung.
const INHALTSDATEI = {
  '/': 'home.json, leistungen.json, site.json',
  '/leistungen/': 'leistungen-hub.json',
  '/galerie/': 'galerie.json',
  '/kontakt/': 'kontakt.json',
  '/impressum/': 'impressum.json',
  '/datenschutz/': 'datenschutz.json',
  '/agb/': 'agb.json',
  '/widerruf/': 'widerruf.json',
};
const inhaltsdatei = (pfad) => INHALTSDATEI[pfad]
  ?? (pfad.startsWith('/leistungen/') ? `leistung-${pfad.split('/')[2]}.json` : 'site.json');

function entschluesseln(t) {
  return t
    .replace(/&nbsp;/g, ' ')
    .replace(/&lt;/g, '<').replace(/&gt;/g, '>')
    .replace(/&quot;/g, '"').replace(/&#0?39;/g, "'").replace(/&#x27;/gi, "'")
    .replace(/&#(\d+);/g, (_, n) => String.fromCodePoint(Number(n)))
    .replace(/&amp;/g, '&');
}

// Alle Textstuecke einer Seite: Text zwischen den Tags, dazu Bildbeschreibungen
// und die Beschreibung fuer Google. Skripte, Stile und Kommentare fallen weg.
function texte(html) {
  const ohne = html
    .replace(/<script[\s\S]*?<\/script>/gi, ' ')
    .replace(/<style[\s\S]*?<\/style>/gi, ' ')
    .replace(/<!--[\s\S]*?-->/g, ' ');
  const stuecke = ohne.split(/<[^>]+>/);
  for (const m of ohne.matchAll(/\salt="([^"]*)"/g)) stuecke.push(m[1]);
  const meta = ohne.match(/<meta name="description" content="([^"]*)"/);
  if (meta) stuecke.push(meta[1]);
  return new Set(stuecke
    .map((t) => entschluesseln(t).replace(/\s+/g, ' ').trim())
    .filter((t) => t.length >= 3));
}

async function inhaltAbgleichen(pfade) {
  const zeilen = [];
  if (!REPO_STAND) return zeilen;
  console.log('\nInhaltsabgleich (Repository gegen Server, nur Hinweis):');
  /* Playwrights eigener HTTP-Client statt fetch: Gegen den PHP-Testserver
     brach fetch (undici in Node 22) beim zweiten Lauf mit einem internen
     Fehler ab — ausserhalb jedes try, der ganze Lauf war rot. Abgefragt
     wird nacheinander, der Testserver bearbeitet ohnehin eine Anfrage nach
     der anderen. */
  const http = await request.newContext({ extraHTTPHeaders: { 'Cache-Control': 'no-cache' } });
  const holen = async (url) => {
    const antwort = await http.get(url, { timeout: 30000 });
    return { status: antwort.status(), html: await antwort.text() };
  };
  for (const pfad of pfade.filter((p) => !p.startsWith('/admin'))) {
    let lokal;
    let live;
    try {
      const a = await holen(REPO_STAND + pfad);
      const b = await holen(basis + pfad);
      if (a.status !== 200 || b.status !== 200) {
        console.log(`  ${pfad.padEnd(42)} nicht verglichen (Repository ${a.status}, live ${b.status})`);
        zeilen.push(`| \`${pfad}\` | – | nicht verglichen (Repository ${a.status}, live ${b.status}) |`);
        continue;
      }
      [lokal, live] = [texte(a.html), texte(b.html)];
    } catch (fehler) {
      console.log(`  ${pfad.padEnd(42)} nicht verglichen: ${String(fehler.message).split('\n')[0]}`);
      zeilen.push(`| \`${pfad}\` | – | nicht verglichen |`);
      continue;
    }
    const fehlt = [...lokal].filter((t) => !live.has(t));
    const nurLive = [...live].filter((t) => !lokal.has(t));
    const kurz = (t) => (t.length > 90 ? t.slice(0, 87) + '…' : t);
    if (fehlt.length === 0 && nurLive.length === 0) {
      console.log(`  ${pfad.padEnd(42)} wie im Repository`);
      zeilen.push(`| \`${pfad}\` | ${inhaltsdatei(pfad)} | ✓ wie im Repository |`);
      continue;
    }
    console.log(`  ${pfad.padEnd(42)} ABWEICHEND (${inhaltsdatei(pfad)}): ${fehlt.length} Text(e) aus dem Repository fehlen live, ${nurLive.length} stehen nur live`);
    for (const t of fehlt.slice(0, 6)) console.log(`      fehlt live:  ${kurz(t)}`);
    for (const t of nurLive.slice(0, 6)) console.log(`      nur live:    ${kurz(t)}`);
    const beispiel = (liste) => liste.slice(0, 2).map((t) => `„${kurz(t).replace(/\|/g, '\\|')}“`).join('<br>');
    zeilen.push(`| \`${pfad}\` | ${inhaltsdatei(pfad)} | ⚠️ ${fehlt.length} fehlen live, ${nurLive.length} nur live`
      + (fehlt.length ? `<br>fehlt live: ${beispiel(fehlt)}` : '')
      + (nurLive.length ? `<br>nur live: ${beispiel(nurLive)}` : '') + ' |');
  }
  await http.dispose();
  return zeilen;
}

const browser = await chromium.launch();
const pfade = await seitenliste();
const zeilen = [];
let summe = 0;

const auslieferung = await auslieferungPruefen();
summe += auslieferung.length;
for (const p of auslieferung) console.log(`Auslieferung: ${p}`);
zeilen.push(`| – | Dateien | – | ${auslieferung.length === 0 ? '✓ CSS und JS wie im Repository' : auslieferung.map((p) => p.replace(/\|/g, '\\|')).join('<br>')} |`);
console.log('');

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

const abgleich = await inhaltAbgleichen(pfade);

const fazit = summe === 0
  ? `Alles in Ordnung: ${pfade.length} Seiten, je Desktop und Handy.`
  : `${summe} Problem(e) gefunden.`;
console.log(`\n${fazit}`);

if (process.env.GITHUB_STEP_SUMMARY) {
  appendFileSync(process.env.GITHUB_STEP_SUMMARY, [
    `## Live-Prüfung ${basis}`, '', fazit, '',
    '| Breite | Seite | Status | Befund |', '|---|---|---|---|', ...zeilen, '',
    ...(abgleich.length ? [
      '### Inhalte: Repository gegen Server', '',
      'Nur ein Hinweis. Abweichungen heißen: Eine Inhaltsdatei auf dem Server ist nicht auf dem Stand des Repositorys — noch nicht hochgeladen, oder im Panel bewusst geändert.', '',
      '| Seite | Inhaltsdatei | Befund |', '|---|---|---|', ...abgleich, '',
    ] : []),
  ].join('\n'));
}

process.exit(summe === 0 ? 0 : 1);
