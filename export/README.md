# CODING AGENTS: READ THIS FIRST

This is a **handoff bundle** from Claude Design (claude.ai/design).

A user mocked up designs in HTML/CSS/JS using an AI design tool, then exported this bundle so a coding agent can implement the designs for real.

## What changed in this update

The first bundle contained the **homepage only** — already implemented at the repo root (`index.html`, `css/`, `js/`, `img/`). **Do not touch that implementation.**

This update adds **11 new pages** plus two shared layout components. Nothing that already exists in the repo was modified.

| New page | File |
| --- | --- |
| Leistungen (hub) | `project/Leistungen.dc.html` |
| Fahrzeugpflege Exterieur | `project/Leistung Exterieur.dc.html` |
| Fahrzeugpflege Interieur | `project/Leistung Interieur.dc.html` |
| Dellen & Hagelschaden | `project/Leistung Dellen.dc.html` |
| Lackierarbeiten | `project/Leistung Lackierarbeiten.dc.html` |
| Lederreparatur | `project/Leistung Leder.dc.html` |
| Ozonbehandlung | `project/Leistung Ozon.dc.html` |
| Galerie | `project/Galerie.dc.html` |
| Kontakt | `project/Kontakt.dc.html` |
| Impressum | `project/Impressum.dc.html` |
| Datenschutzerklärung | `project/Datenschutz.dc.html` |
| AGB | `project/AGB.dc.html` |

## Read these two files first

`project/Seitenkopf.dc.html` and `project/Seitenfuss.dc.html` are the **shared header and footer**. Every page imports them. Build them once as real components — do not duplicate the markup per page.

- `Seitenkopf` takes an `active` prop (`leistungen` | `galerie` | `betrieb` | `kontakt`) that underlines the current nav item.
- `Seitenfuss` takes `ctaHeadline` (string) and `showForm` (boolean — `false` on Kontakt and the legal pages, because those have their own form or none). It also owns the sticky enquiry bar that appears after 600px of scroll.

## Important implementation notes

**Every service page is deliberately different.** They do NOT share a template — that was an explicit requirement. Each one has its own core interactive module:

- **Exterieur** — clear-coat cross-section that redraws as you switch between one/two/three-stage polish, plus a wax/sealant/ceramic comparison table.
- **Interieur** — six-zone interior selector (list left, detail panel right), plus a same-day timeline of the drying process.
- **Dellen** — feasibility check (4 cards), then a schematic top-down vehicle map: click a panel, see its dent count, disassembly effort and difficulty. Three photo stations instead of a before/after slider.
- **Lackierarbeiten** — fingernail depth test (3 states) driving a layered paint cross-section, plus a four-step colour-matching sequence.
- **Leder** — four damage grades as a tab strip, each with its own repair steps and an honest "what to expect" note. Macro shots beside the slider.
- **Ozon** — odour-source diagnosis (5 chips) that changes the recommended order of work, plus a 5-phase timeline showing when the vehicle is locked. **No before/after slider by design** — a smell cannot be photographed, and the page says so.

**Placeholders that must be filled before launch:**

- All images are `<image-slot>` drop targets. Each has a unique `id` and a German placeholder describing the required shot.
- **Kontakt page:** the street address is missing — marked with a red dashed notice. Public-transport directions are also placeholder text.
- **Impressum / Datenschutz / AGB:** structure and table of contents only. Every section is a marked "Text einsetzen" field. These must be filled with the client's legally reviewed texts.
- The three review quotes, `25 Jahre`, `4,9` and `87 Bewertungen` are unverified placeholders (exposed as tweaks on the homepage).
- FAQ answers across all service pages are technically plausible drafts written by the design assistant — the client must confirm they match their actual working practice.

## Design tokens (as used across every page)

```
Red (accent)      #D80D18   hover #F2323C
Near-black        #0A0B0D
Dark panel        #101216   form fields #16191E
Light background  #F5F6F8   white #FFFFFF
Body text light   #B4BAC2 / #AFB5BD / #8E959F / #7C838D
Body text dark    #2B3038 / #41474F / #61686F
Hairlines         rgba(255,255,255,.13) dark · rgba(10,11,13,.13) light
Headings          Saira 800, letter-spacing -.03em
Body              Barlow 400/500/600
Content width     1440px, 64px side padding
```

Two recurring brand devices, both taken from the logo:

1. **The sheared parallelogram** — `width:16px;height:8px;background:#D80D18;transform:skewX(-20deg)`. Used as the eyebrow marker, list bullet and section accent.
2. **The clipped button corner** — `clip-path:polygon(11px 0,100% 0,100% calc(100% - 11px),calc(100% - 11px) 100%,0 100%,0 11px)`. On every primary button.

The wordmark replaces the first "T" of REUTTER with a red sheared block. See the header component.

## About the design files

The design medium is **HTML/CSS/JS** — these are prototypes, not production code. Your job is to **recreate them pixel-perfectly** in whatever technology makes sense for the target codebase (React, Vue, native, whatever fits). Match the visual output; don't copy the prototype's internal structure unless it happens to fit.

**Don't render these files in a browser or take screenshots unless the user asks you to.** Everything you need — dimensions, colors, layout rules — is spelled out in the source. Read the HTML and CSS directly; a screenshot won't tell you anything they don't.

**If anything is ambiguous, ask the user to confirm before you start implementing.** It's much cheaper to clarify scope up front than to build the wrong thing.

## Bundle contents

- `README.md` — this file
- `chats/` — conversation transcripts (read these!)
- `project/` — the `Reutter Fahrzeugpflege Modern Redesign` project files (HTML prototypes, assets, components)
- `index.html`, `css/`, `js/`, `img/` — the already-implemented homepage. Untouched by this update.
