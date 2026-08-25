# Logo — Ausgangsmaterial

| Datei | Was es ist |
|---|---|
| `logo-original.jpg` | 17.08.2026 · JPEG, 934 × 107 px, weißer Hintergrund |
| `logo-original-attrappe.svg` | **kein Vektor** — dasselbe JPEG in einem SVG-Rahmen |
| `logo-2026-08-25.jpg` | 25.08.2026 · JPEG, 1000 × 114 px, sauber freigestellt |

Eine zweite, damals nachgereichte Fassung (`logo-neu-test.svg`) enthielt
dasselbe JPEG byte-für-byte identisch (SHA-256
`a84c16af98348a40b32a394ca1730b8a55ad76c7e2de95527ff13d2ce10aa358`) und ist
deshalb nicht aufgehoben.

## Der Vektor ist nachgezeichnet, nicht original

Eine echte Vektordatei hat der Kunde nicht. Aus `logo-2026-08-25.jpg` ist
deshalb einer nachgezeichnet worden — die Wortmarke besteht fast nur aus
geraden Kanten, dafür reicht das Rasterbild.

Weg: vierfach hochskalieren, in zwei Farbmasken zerlegen (`#231F20` für die
Schrift, `#E01B22` für das Parallelogramm), den Rand zwischen gefüllten und
leeren Bildpunkten als Kantenzug einsammeln, zu geschlossenen Schleifen
verketten und die Treppenstufen mit Douglas-Peucker zu Geraden zusammenfassen.

**Gegenprobe:** SVG und Rasterbild bei 1000, 512, 128 und 64 px nebeneinander
gerendert. Bei voller Größe weichen **0,14 %** der Bildpunkte ab; darunter ist
das SVG schärfer als das herunterskalierte JPEG.

Im Einsatz:

| Datei | Wo |
|---|---|
| `public/assets/logo/reutter-weiss.svg` | Kopf, Fuß, Panel — alle drei auf dunklem Grund |
| `public/assets/logo/reutter.svg` | dunkle Fassung für helle Flächen, noch ungenutzt |
| `public/assets/favicon.svg` | das Parallelogramm, jetzt aus demselben Pfad |
| `public/assets/logo/*.webp` | Rasterfassungen, bleiben für Fälle ohne SVG |

**Was der nachgezeichnete Vektor nicht ersetzt:** die Originaldatei aus dem
Grafikprogramm. Für Favicon, Website und Schneidplotter reicht er; für großen
Druck ist die Datei des Werbetechnikers weiterhin besser. Meist hat sie, wer
die Fahrzeugbeschriftung gemacht hat.
