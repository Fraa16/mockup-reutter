# Reutter Fahrzeugpflege — Unterseiten

_Continued 2026-08-14_

---

## User

Baue nun (separat) die restlichen Unterseiten:

- Leistungen-Hub + 6 Unterreiter
- Galerie
- Kontakt
- Rechtliches (Impressum, Datenschutz, AGB)

You can ask me a bunch of questions before building

## Assistant

Asked 12 questions. Answers:

- **file_structure:** Eine Datei pro Seite — echte Links dazwischen
- **service_page_shape:** Hero → Problem → Ablauf in Schritten → Vorher/Nachher → Preisrahmen → FAQ → Anfrage
- **hub_layout:** decide for me → chose a numbered workshop-style index list with a preview image that swaps on hover
- **prices:** Keine Preise — nur "Festpreis nach Begutachtung"
- **faq:** Ja, 4–6 Fragen je Seite (technically plausible drafts, client to confirm)
- **gallery_shape:** decide for me → chose 3 case studies with their own sliders + a filtered 20-tile grid
- **gallery_count:** 20 image slots
- **contact_page:** 3-step form, map + directions, opening hours table, WhatsApp/callback, insurance notice
- **legal_source:** Client delivers final texts — build layout with marked placeholders only
- **legal_impressum:** (left empty — address still missing)
- **shared_sections:** Sticky enquiry bar, reviews, short enquiry form at page end, related services

Built: shared `Seitenkopf` / `Seitenfuss` components, the Leistungen hub, six service pages, Galerie, Kontakt, and three legal scaffolds. Wired the existing homepage's nav and footer to the new pages (previously anchor-only links).

## User

PS: I noticed that all service pages follow the same structure - this shouldn't be. It looks like an amateur template setup. Please make sure each service page has different components somehow. Remember, you are the expert!

## Assistant

Rewrote all six service pages so each has its own structure and its own core interactive module, rather than one shared skeleton:

- **Exterieur** — clear-coat cross-section reacting to the chosen polish stage; wax vs. sealant vs. ceramic comparison table. Hero split left/right.
- **Interieur** — six-zone interior selector with detail panel and per-zone limits; drying-day timeline. Hero with a 3-image strip.
- **Dellen** — feasibility check, then a schematic top-down panel map with per-panel dent counts and difficulty. Three photo stations instead of a slider. Full-bleed hero.
- **Lackierarbeiten** — fingernail depth test driving a layered paint cross-section; four-step colour-matching sequence. Hero with paint swatch row.
- **Leder** — material check (Echtleder / Kunstleder / Alcantara), four damage grades as a tab strip with honest expectations. Hero mirrored to the right, macro shots beside the slider.
- **Ozon** — odour-source diagnosis changing the recommended order of work; 5-phase lock-out timeline; a "what ozone cannot do" section. Deliberately no before/after slider, with the reason stated on the page.

FAQ layout also varies per page (accordion on three, two-column static on two) so the pages don't read as one template.

## User

Export to the existing repo, leaving the implemented homepage untouched.
