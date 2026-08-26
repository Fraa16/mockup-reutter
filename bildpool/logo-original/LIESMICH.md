# Logo — Ausgangsmaterial

| Datei | Was es ist |
|---|---|
| `logo-original.jpg` | 17.08.2026 · JPEG, 934 × 107 px |
| `logo-original-attrappe.svg` | **kein Vektor** — dasselbe JPEG in einem SVG-Rahmen |
| `logo-2026-08-25.jpg` | 25.08.2026 · JPEG, 1000 × 114 px, Bildfläche 1000 × 79 |
| `logo-2026-08-26-canva.pdf` | 26.08.2026 · **kein Vektor** — Rasterbild in einem PDF-Rahmen |
| `logo-2026-08-26-aus-pdf.png` | daraus herausgelöst: 1699 × 132 px, verlustfrei — **die beste Vorlage** |

Eine weitere Fassung (`logo-neu-test.svg`) enthielt dasselbe JPEG wie
`logo-original.jpg` byte-für-byte identisch (SHA-256
`a84c16af98348a40b32a394ca1730b8a55ad76c7e2de95527ff13d2ce10aa358`) und ist
deshalb nicht aufgehoben.

## Beide „Vektordateien" sind keine

Weder die `.svg` noch das `.pdf` enthalten Pfade. Nachgewiesen:

- **`logo-original-attrappe.svg`** — ein `<image>` mit base64, dazu drei
  `<path>`, die nur Beschneidungsrahmen sind
- **`logo-2026-08-26-canva.pdf`** — Producer `Canva`, ein
  `/XObject /Subtype /Image` (1702 × 198, 8 Bit RGB) und ein Seiteninhalt mit
  **null Zeichenbefehlen**: keine `m`, keine `l`, keine `c`. Nur weiße
  Rechtecke und ein `Do` für das Bild. Auch keine Schrift, also kein Text in
  Kurven

**Selbst prüfen:** Datei im Texteditor öffnen. Steht `<image` und `base64`
drin, ist es eine Attrappe. Bei PDF hilft die Dateigröße als Hinweis — ein
Vektorlogo dieser Art wiegt wenige Kilobyte, hier sind es 33.

## Der Vektor ist nachgezeichnet

Aus `logo-2026-08-26-aus-pdf.png`. Weg: vierfach hochskalieren, in zwei
Farbmasken zerlegen (`#231F20` Schrift, `#E01B22` Parallelogramm), den Rand
zwischen gefüllten und leeren Bildpunkten als Kantenzug einsammeln, zu
geschlossenen Schleifen verketten und die Treppenstufen mit Douglas-Peucker
zu Geraden zusammenfassen.

**Gemessen gegen das Rasterbild aus dem PDF**, beide Fassungen bei 1699 × 132
gerendert und binarisiert:

| Nachzeichnung aus | abweichende Bildpunkte |
|---|---|
| `logo-2026-08-25.jpg` (1000 × 79, JPEG) | 8653 · **3,858 %** |
| `logo-2026-08-26-aus-pdf.png` (1699 × 132, verlustfrei) | 953 · **0,425 %** |

Die neunfach genauere Fassung ist im Einsatz. Der Canva-Export hatte unten
einen schwachen Grauschleier (`#DEDEDE`, 9833 Bildpunkte) — der gehört nicht
zum Logo und ist vor dem Nachzeichnen auf Weiß gezogen worden.

Im Einsatz:

| Datei | Wo |
|---|---|
| `public/assets/logo/reutter-weiss.svg` | Kopf, Fuß, Panel — alle auf dunklem Grund |
| `public/assets/logo/reutter.svg` | dunkle Fassung für helle Flächen |
| `public/assets/favicon.svg` | das Parallelogramm, aus demselben Pfad |
| `public/assets/logo/*.webp` | Rasterfassungen, bleiben für Fälle ohne SVG |

**Was weiterhin fehlt:** die Originaldatei aus dem Grafikprogramm. Für
Favicon, Website und Schneidplotter reicht die Nachzeichnung; für großen Druck
ist das Original besser. Meist hat es, wer die Fahrzeugbeschriftung gemacht
hat — nicht Canva.
