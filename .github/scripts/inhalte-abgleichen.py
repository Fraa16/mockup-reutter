#!/usr/bin/env python3
"""Entscheidet, welche Inhaltsdateien und Fotos auf den Server duerfen.

Laeuft im Hochlade-Workflow nach dem Code. Bekommt den Stand des Servers
(heruntergeladene JSON-Dateien und die Dateiliste von uploads/) und schreibt
eine Liste von lftp-Befehlen, die nur das hochladen, was gefahrlos geht.

Der Grundsatz ist derselbe wie beim Code: Diese Automatik loescht nichts. Dazu
kommt ein zweiter, wegen des Panels: Sie ueberschreibt nichts, was dort
jemand geaendert hat.

Texte (data/content/*.json), je Datei:

  - fehlt auf dem Server          -> hochladen
  - gleich wie im Repository      -> nichts zu tun
  - auf dem Server kaputt (kein   -> hochladen. Das Panel schreibt nie
    gueltiges JSON)                  ungueltiges JSON; so etwas bleibt nur
                                     nach einem abgebrochenen Hochladen liegen
                                     und wuerde sonst nie mehr repariert.
  - entspricht einer frueheren    -> hochladen. Dann hat niemand im Panel
    Fassung aus dem Repository       etwas geaendert, der Server ist nur alt.
  - alles andere                  -> stehen lassen und warnen. Das ist eine
                                     Aenderung aus dem Panel; die gilt.

„Fruehere Fassung" heisst: Der Inhalt der Datei auf dem Server ist Byte fuer
Byte gleich einer Fassung, die irgendwann im Repository stand. Verglichen wird
ueber die Git-Pruefsumme, die Geschichte kommt aus dem vollstaendigen Checkout.
So braucht es keinen Merkzettel auf dem Server, der verloren gehen koennte.

Fotos (public/uploads/):

Hochgeladen wird ein Foto nur, wenn es auf dem Server fehlt UND einer der
Texte, die danach live sind, es benutzt. Ein Foto, das Daniel im Panel
geloescht hat, taucht so nicht wieder auf — es steht in seinen Texten nicht
mehr. Vorhandene Fotos werden nie ersetzt: Die Dateinamen tragen eine
Zufallskennung, gleicher Name heisst gleiches Bild.

Nur Python-Standardbibliothek, damit nichts veraltet.
"""

from __future__ import annotations

import argparse
import hashlib
import json
import re
import subprocess
import sys
from pathlib import Path

# Nur solche Namen gelangen in die lftp-Befehle. Alles andere wird gemeldet
# und uebersprungen — ein Name mit Leerzeichen oder Semikolon koennte sonst den
# Befehl veraendern.
SICHERER_NAME = re.compile(r'^[a-z0-9][a-z0-9._-]*$')
BILDNAME      = re.compile(r'\.(webp|jpe?g|png)$', re.IGNORECASE)
BREITEN       = (400, 640, 1024, 1440, 1920)   # wie BILD_BREITEN in app/lib/images.php


def git_blob(daten: bytes) -> str:
    """Pruefsumme, unter der Git denselben Inhalt speichern wuerde."""
    return hashlib.sha1(b'blob %d\0' % len(daten) + daten).hexdigest()


def fruehere_fassungen(pfad: str) -> set[str]:
    """Alle Pruefsummen, die diese Datei je im Repository hatte."""
    ausgabe = subprocess.run(
        ['git', 'log', '--format=', '--raw', '--no-abbrev', '--no-renames', '--', pfad],
        check=True, capture_output=True, text=True,
    ).stdout
    blobs: set[str] = set()
    for zeile in ausgabe.splitlines():
        # :100644 100644 <alt> <neu> M\tpfad
        if not zeile.startswith(':'):
            continue
        teile = zeile.split()
        for blob in teile[2:4]:
            if not set(blob) <= {'0'}:
                blobs.add(blob)
    return blobs


def bildnamen(wert: object) -> set[str]:
    """Alle Bilddateinamen in einem JSON-Baum — nur der Name, ohne Pfad."""
    gefunden: set[str] = set()
    if isinstance(wert, dict):
        for v in wert.values():
            gefunden |= bildnamen(v)
    elif isinstance(wert, list):
        for v in wert:
            gefunden |= bildnamen(v)
    elif isinstance(wert, str) and BILDNAME.search(wert) and '://' not in wert:
        gefunden.add(wert.rsplit('/', 1)[-1])
    return gefunden


def liste_lesen(pfad: Path) -> set[str]:
    """Dateiliste von lftp (cls -1). Ordner enden auf /, die zaehlen nicht."""
    if not pfad.is_file():
        return set()
    return {z.strip() for z in pfad.read_text(encoding='utf-8', errors='replace').splitlines()
            if z.strip() and not z.strip().endswith('/')}


def main() -> int:
    a = argparse.ArgumentParser(description=__doc__.splitlines()[0])
    a.add_argument('--repo-inhalt',   default='data/content')
    a.add_argument('--repo-fotos',    default='public/uploads')
    a.add_argument('--server-inhalt', required=True, help='heruntergeladene JSON-Dateien des Servers')
    a.add_argument('--server-fotos',  required=True, help='cls -1 von neu/web/uploads/')
    a.add_argument('--server-cache',  required=True, help='cls -1 von neu/web/uploads/cache/')
    a.add_argument('--ziel-inhalt',   default='neu/data/content')
    a.add_argument('--ziel-fotos',    default='neu/web/uploads')
    a.add_argument('--befehle',       required=True, help='hierhin kommen die lftp-Befehle')
    a.add_argument('--bericht',       required=True, help='hierhin kommt der Bericht (Markdown)')
    o = a.parse_args()

    repo_inhalt   = Path(o.repo_inhalt)
    repo_fotos    = Path(o.repo_fotos)
    server_inhalt = Path(o.server_inhalt)
    fotos_da      = liste_lesen(Path(o.server_fotos))
    cache_da      = liste_lesen(Path(o.server_cache))

    hoch_inhalt: list[tuple[str, str]] = []   # (Datei, Grund)
    stehen: list[str] = []                    # im Panel geaendert
    aktuell: list[str] = []
    warnungen: list[str] = []
    live: dict[str, object] = {}              # was nach dem Lauf auf dem Server gilt

    # --- Texte -------------------------------------------------------------
    for datei in sorted(repo_inhalt.glob('*.json')):
        name = datei.name
        if not SICHERER_NAME.match(name):
            warnungen.append(f'{name}: ungewoehnlicher Dateiname, nicht uebertragen.')
            continue
        neu = datei.read_bytes()
        try:
            live_neu = json.loads(neu)
        except ValueError:
            warnungen.append(f'{name}: im Repository kein gueltiges JSON, nicht uebertragen.')
            continue

        auf_server = server_inhalt / name
        if not auf_server.is_file():
            hoch_inhalt.append((name, 'fehlte auf dem Server'))
            live[name] = live_neu
            continue

        alt = auf_server.read_bytes()
        if alt == neu:
            aktuell.append(name)
            live[name] = live_neu
            continue

        try:
            live_alt = json.loads(alt)
        except ValueError:
            hoch_inhalt.append((name, 'war auf dem Server beschaedigt'))
            live[name] = live_neu
            continue

        if git_blob(alt) in fruehere_fassungen(str(datei)):
            hoch_inhalt.append((name, 'Server hatte einen aelteren Stand'))
            live[name] = live_neu
        else:
            stehen.append(name)
            live[name] = live_alt

    # Dateien, die nur auf dem Server liegen, gelten auch — ihre Fotos zaehlen.
    for datei in sorted(server_inhalt.glob('*.json')):
        if datei.name not in live:
            try:
                live[datei.name] = json.loads(datei.read_bytes())
            except ValueError:
                pass

    # --- Fotos ---------------------------------------------------------------
    hoch_fotos: list[str] = []
    hoch_cache: list[str] = []
    gebraucht: set[str] = set()
    for inhalt in live.values():
        gebraucht |= bildnamen(inhalt)

    for bild in sorted(gebraucht):
        if not SICHERER_NAME.match(bild):
            warnungen.append(f'Foto „{bild}": ungewoehnlicher Name, nicht uebertragen.')
            continue
        quelle = repo_fotos / bild
        if bild not in fotos_da:
            if quelle.is_file():
                hoch_fotos.append(bild)
            else:
                warnungen.append(f'Foto {bild} wird benutzt, fehlt aber auf dem Server und im Repository.')
                continue
        # Verkleinerte Fassungen nachziehen, die fehlen — auch fuer Fotos, die
        # schon da sind. Ohne sie liefert die Seite das grosse Original aus.
        stamm = bild.rsplit('.', 1)[0]
        for breite in BREITEN:
            klein = f'{stamm}-{breite}.webp'
            if klein not in cache_da and (repo_fotos / 'cache' / klein).is_file():
                hoch_cache.append(klein)

    # --- Befehle -------------------------------------------------------------
    # Erst die Fotos, dann die Texte: Sonst stuende kurz ein Text online, der
    # auf ein Foto zeigt, das noch unterwegs ist.
    befehle: list[str] = []
    for bild in hoch_fotos:
        befehle.append(f'put -O {o.ziel_fotos} {repo_fotos}/{bild}')
    for klein in hoch_cache:
        befehle.append(f'put -O {o.ziel_fotos}/cache {repo_fotos}/cache/{klein}')
    for name, _ in hoch_inhalt:
        befehle.append(f'put -O {o.ziel_inhalt} {repo_inhalt}/{name}')
    Path(o.befehle).write_text(''.join(b + ';\n' for b in befehle), encoding='utf-8')

    # --- Bericht ---------------------------------------------------------------
    z: list[str] = ['## Texte und Fotos', '']
    if not befehle and not stehen and not warnungen:
        z.append(f'Alles aktuell — {len(aktuell)} Texte, nichts zu uebertragen.')
    else:
        if hoch_inhalt:
            z.append('**Texte, die hochgeladen werden:**')
            z += [f'- `{n}` — {g}' for n, g in hoch_inhalt]
            z.append('')
        if hoch_fotos or hoch_cache:
            z.append(f'**Fotos:** {len(hoch_fotos)} neu, dazu {len(hoch_cache)} verkleinerte Fassungen.')
            z += [f'- `{b}`' for b in hoch_fotos]
            z.append('')
        if stehen:
            z.append('**Im Panel geaendert — bleiben, wie sie auf dem Server sind:**')
            z += [f'- `{n}`' for n in stehen]
            z.append('')
            z.append('Die Fassung aus dem Repository ist dort nicht online. Soll sie es sein: '
                     'die Datei vom Server holen, die Aenderung darin nachtragen, ins Repository '
                     'uebernehmen — dann geht sie beim naechsten Lauf von selbst hoch.')
            z.append('')
        if aktuell:
            z.append(f'Unveraendert: {len(aktuell)} Texte.')
        if warnungen:
            z.append('')
            z.append('**Hinweise:**')
            z += [f'- {w}' for w in warnungen]
    Path(o.bericht).write_text('\n'.join(z) + '\n', encoding='utf-8')

    # Ins Protokoll: kurz, und Warnungen so, dass GitHub sie oben anzeigt.
    print('\n'.join(z))
    for n in stehen:
        print(f'::warning::{n} wurde im Panel geaendert und bleibt, wie es auf dem Server ist. '
              'Die Fassung aus dem Repository ist nicht online.')
    for w in warnungen:
        print(f'::warning::{w}')
    return 0


if __name__ == '__main__':
    sys.exit(main())
