# Reutter Fahrzeugpflege website redesign

_Started 2026-07-27 12:57 UTC_

---

## User

create a modern, full UI mockup for the new website of Reutter Fahrzeugpflege. Attached you can see the old website, which is uncompleted and amateur. What we want is the same DNA, but for a modern and professional website. 

I do NOT want anything typical AI slop or simple drag and drop style - real agency level mockup

## Assistant

I'll explore the design system first, then ask a few questions.

_[tool: list_files]_

_[tool: list_files]_

The attached design-system project is empty, so I'll build from the old site's brand DNA (black aggressive italic wordmark, red diamond accent, silver/graphite). A few questions first:

_[tool: questions_v2]_

## User

Questions answered:
- scope: All of the above (full site mockup)
- assets: Photos exist but not yet — use placeholders
- audience: Private car owners (Pflege, Aufbereitung), exclusive car owners
- primary_action: Online-Terminanfrage / Formular
- tone: Handwerklich & direkt — Meisterbetrieb, kein Marketing-Sprech
- visual_intensity: Split: dunkle Hero-/Galerie-Bereiche, helle Inhaltsbereiche
- services_depth: Interaktiver Fahrzeug-Hotspot: Bereich anklicken → Leistung
- proof: Google-Bewertungen, Jahre Erfahrung / Meisterbetrieb, Vorher/Nachher-Bilder
- interactions: Vorher/Nachher-Slider (draggable), Scroll-getriebene Reveals / Parallax, Sticky Anfrage-Leiste / Kontaktbalken
- variations: Volle Seite + Varianten einzelner Sektionen
- viewport: Desktop first (1440)

## Assistant

_[tool: copy_starter_component]_

Design system project is empty, so I'm building from the old site's brand DNA. Writing the mockup now.

_[tool: dc_write]_

_[tool: dc_html_str_replace]_

_[tool: dc_html_str_replace]_

_[tool: ready_for_verification]_

Full desktop mockup is up — same DNA (schwarz/rot, das gescherte Parallelogramm aus dem Logo als durchgehendes Akzentelement), aber als dunkel/hell gesplittete Agentur-Seite.

**Was drin ist:** Utility-Bar → Sticky Header → Hero mit Parallax → Vertrauensleiste → interaktiver Fahrzeug-Hotspot (7 Bereiche, Panel rechts, „Diese Leistung anfragen" wählt die Leistung im Formular vor) → Vorher/Nachher-Slider (ziehbar, 3 Fälle) → Ablauf → Galerie → mobiler Service → Betrieb + Bewertungen → 3-Schritt-Anfrage → Footer inkl. aller Rechtsseiten. Sticky Anfrage-Leiste ab 720px Scroll.

**Zwei Dinge, die von Ihnen kommen müssen:**
- Alle Bilder sind Drop-Slots — einfach Fotos draufziehen, sie bleiben gespeichert.
- Erfundene Zahlen/Texte, die ich nicht prüfen konnte: **25 Jahre**, **4,9 / 87 Bewertungen** (beide als Tweaks einstellbar) und die **drei Bewertungstexte** — die müssen durch echte Google-Zitate ersetzt werden.

Als nächstes sinnvoll: Mobile-Ansichten der Kernsektionen, oder Varianten des Hero und des Leistungs-Moduls.

