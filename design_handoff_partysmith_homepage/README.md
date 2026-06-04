# Handoff: Partysmith Homepage — "Coral & Marigold" rebrand

## Overview

This is a **visual rebrand of the existing marketplace homepage** (`app/Views/home.php`)
for the project currently titled *For Your Events* / `theteepeenetwork/partyplanner`.
The product, page structure, copy and flows are **unchanged** — this handoff swaps the
**brand and colour system** to **Partysmith** in the **"Coral & Marigold"** palette, and
introduces the **"P.S." postscript** brand voice.

Nothing about the marketplace logic changes: same hero search, same occasion pills, same
"how it works", featured suppliers, category tiles, vendor CTA and footer. Only the
**identity layer** changes — logo, colours, type, and a handful of "P.S." voice lines.

> Scope: **homepage + sitewide header/footer brand** only. Browse Services, dashboards,
> service pages etc. are out of scope here (the colour tokens below are sitewide-ready,
> but this README documents the homepage).

## About the Design Files

The files in this bundle are a **design reference created in static HTML/CSS** — a
prototype showing the intended look, not production code to ship as-is. The target app is
**CodeIgniter 4 / PHP** with server-rendered views and **plain CSS** (plus Bootstrap 5
utilities). Your task is to **recreate this design in the existing CodeIgniter views and
CSS**, reusing the established patterns there:

- `app/Views/home.php` — homepage markup (sections already match this design 1:1)
- `app/Views/header.php` — sitewide navbar + `<head>` (logo + fonts live here)
- `app/Views/footer.php` — sitewide footer
- `public/assets/css/style.css` — sitewide tokens/navbar/footer
- `public/assets/css/home.css` — homepage-specific styles (loaded only when `$isHomePage`)

Because the existing `home.php` **already has every section this design shows** (the markup
was the basis for this mock), the cleanest implementation is mostly a **CSS + token +
logo** change, not a markup rewrite. Map the prototype's classes onto the existing ones
rather than replacing the PHP wholesale.

## Fidelity

**High-fidelity.** Exact colours, typography, spacing, radii and shadows are specified
below and in `Website Mockup.html`. Recreate to match. Supplier card *content* (names,
prices, ratings) in the mock is **placeholder** — keep the real `$services` controller
data; only the styling changes.

---

## Brand changes at a glance

| | Before (*For Your Events*) | After (**Partysmith**) |
|---|---|---|
| Name | "For Your Events" | "Partysmith" |
| Logo | stacked text "For **Your** / Events" | **P.S. monogram tile** + "Partysmith" wordmark |
| Display font | Playfair Display / Fraunces | **Hanken Grotesk** (600/700) |
| Body/UI font | Manrope / DM Sans | **Hanken Grotesk** (400–600) |
| Script accent | — | **Mr Dafoe** (the "P.S." voice only) |
| Primary accent | terracotta `#B66A4D` | **Coral `#D8503C`** |
| Secondary accent | — | **Marigold `#E0A94F`** |
| Text / dark ground | `#3A312D` / `#221B18` | **Plum Ink `#2A2026`** |
| Page background | cream `#F6F1EB` | **Warm Ivory `#F6F1E9`** |
| `theme-color` meta | `#3A312D` | `#2A2026` |

---

## Design Tokens

Define these as the sitewide CSS custom properties (replace the existing `:root` palette in
`style.css`). Names below are the prototype's; map to existing token names where they exist.

### Colour

| Token | Hex | Role |
|---|---|---|
| `--paper` | `#F6F1E9` | Page background (Warm Ivory) |
| `--paper-2` | `#F1EADD` | Alt section surface / pills / icon tiles |
| `--paper-3` | `#ECE2D2` | Deeper sand (optional) |
| `--ink` | `#2A2026` | Body text + dark grounds (Plum Ink) |
| `--ink-soft` | `rgba(42,32,38,0.62)` | Secondary text |
| `--ink-faint` | `rgba(42,32,38,0.40)` | Tertiary text / field labels |
| `--coral` | `#D8503C` | **Primary** — buttons, eyebrows, links, card category |
| `--coral-deep` | `#BE4030` | Button hover, "view details" link |
| `--marigold` | `#E0A94F` | **Secondary** — icon glints, star ratings, script voice on dark |
| `--wax` | `#C0473E` | The "P.S." note (script voice on light) |
| `--line` | `rgba(42,32,38,0.13)` | Borders |
| `--line-soft` | `rgba(42,32,38,0.07)` | Hairlines |
| `--white` | `#FFFFFF` | Cards, search panel, button text on coral |

### Accessibility (WCAG, verified)

- Plum Ink text on Warm Ivory → **13.5:1** (AA). Use for all body copy.
- White text on Coral button → **4.1:1** → **AA Large only**. Fine for buttons/large
  labels; **do not** use coral for body-size text on white. (Card category labels are
  uppercase 11px/700 on light — acceptable as they're bold display, but if you want strict
  AA there, use `--coral-deep`.)
- Coral-deep on white → ~5.0:1 (AA) — used for the "View details" link.
- Marigold is decorative (icons, stars, script on dark) — **never** body text on light.
- Script "P.S." voice uses `--wax` on light grounds, `--marigold` on the plum-ink ground.

### Typography

Load in `header.php` `<head>` (replace the Playfair/Fraunces/Manrope links):

```html
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700;800&family=Mr+Dafoe&display=swap" rel="stylesheet">
```

| Use | Family | Weight | Size (desktop) | Tracking |
|---|---|---|---|---|
| `--sans` (everything) | Hanken Grotesk | 400–700 | — | — |
| `--script` (P.S. voice only) | Mr Dafoe | 400 | 24–38px | normal |
| H1 hero | Hanken Grotesk | 700 | `clamp(34px,5.6vw,60px)` | `-0.03em`, line-height 1.04 |
| Section heading | Hanken Grotesk | 700 | `clamp(27px,3.6vw,42px)` | `-0.025em`, line-height 1.06 |
| Eyebrow | Hanken Grotesk | 700 | 12.5px | `0.16em`, uppercase, `--coral` |
| Card title | Hanken Grotesk | 600 | 18px | `-0.015em` |
| Body / lead | Hanken Grotesk | 400 | `clamp(16px,1.8vw,19px)` | line-height 1.6 |
| Buttons / nav | Hanken Grotesk | 600 / 500 | 15px | — |

### Radius / Shadow / Layout

- `--r-card: 16px` · pill buttons `--r-btn: 100px` · search panel `20px` · vendor block `24px`
- `--shadow-soft: 0 10px 30px rgba(42,32,38,0.08)` (cards at rest)
- `--shadow-lift: 0 18px 44px rgba(42,32,38,0.16)` (card hover, search panel)
- Container: `max-width: 1200px`, side padding `clamp(18px,4vw,40px)`
- Sticky nav height `70px`; `backdrop-filter: blur(10px)` over translucent paper
- Section vertical rhythm: `clamp(54px,7vw,96px)`

---

## The "P.S." logo mark

A rounded tile containing the **P.S.** monogram, set beside the **Partysmith** wordmark.
This replaces the `.navbar-brand` text logo in `header.php` and the `<h5>For Your Events</h5>`
brand block in `footer.php`.

**Markup (nav):**
```html
<a class="brand" href="/">
  <span class="ps">P<span class="dot">.</span>S<span class="dot">.</span></span>
  <span class="name">Partysmith</span>
</a>
```

**CSS:**
```css
.brand { display: flex; align-items: center; gap: 11px; text-decoration: none; }
.brand .ps {
  width: 40px; height: 40px; border-radius: 11px;
  background: var(--coral); color: var(--paper);
  display: flex; align-items: center; justify-content: center; flex: none;
  font-weight: 700; font-size: 21px; letter-spacing: -0.04em; line-height: 1;
}
.brand .ps .dot { color: var(--ink); }          /* the periods are plum-ink, not coral */
.brand .name { font-weight: 600; font-size: 20px; letter-spacing: -0.025em; }
```

> **Critical detail:** the `P`, the two periods, and the `S` are inline spans on **one
> line**. Use `display:flex; align-items:center; justify-content:center` on the tile (NOT
> `display:grid` / `place-items`, which makes the four glyphs stack vertically). The dots
> are coloured `--ink` for the two-tone "P.S." read. The favicon is an inline SVG of the
> same tile (coral bg, ivory letters, plum dots) — see `<link rel="icon">` in the mock.

A monochrome fallback (single-colour tile, all glyphs one colour) must also hold for tiny /
one-colour contexts — keep the same flex-row construction.

---

## Screens / Views

### Screen: Homepage (`home.php`)

One scrolling page. Sections top → bottom (each maps to an existing `home.php` section):

#### 1. Sitewide nav (`header.php`)
- Sticky, 70px, translucent Warm Ivory + blur, 1px soft bottom border.
- Left: P.S. brand mark (above). Right: text links **Find Suppliers** (active) ·
  **For Vendors** · **Inspiration**, then **My Account** (text link), then **Start
  Planning** (coral pill button). Mobile ≤680px: links + account hide, hamburger shows.
- Link colour `--ink-soft`, hover/active `--ink`. (Keep the existing role-based nav logic
  from `header.php`; only restyle.)

#### 2. Hero (`.hero-section`)
- Full-bleed background **photo** with a plum-ink gradient scrim
  `linear-gradient(180deg, rgba(42,32,38,.78), rgba(42,32,38,.62) 45%, rgba(42,32,38,.70))`
  so white text stays legible (verified). Asset: `assets/hero.jpg`.
- Centered copy, max-width 760px, white:
  - Eyebrow (marigold, uppercase): "The UK event marketplace, expertly made"
  - H1: "Plan your whole event in one organised place"
  - Sublead (ivory 82%): "Find vetted suppliers, compare services and get structured quotes — booked and paid in one calm place."
  - **Script P.S. line** (Mr Dafoe, marigold): "P.S. leave the planning to us."
- **Search panel** — white card, radius 20, `--shadow-lift`, max-width 720, left-aligned:
  - **Occasion pills** (radio group): Wedding (checked) · Birthday · Corporate ·
    Christening. Rest state = `--paper-2` bg / `--ink-soft` text; **checked = `--coral`
    bg / white**. Radius pill, 8×16px.
  - **Fields row** grid `1fr 1fr 1.2fr auto`: Date (`<input type=date>`), Guests
    (`<select>`: Any number / Up to 50 / Around 100 / Around 150 / 250+), Location
    (`<input>` placeholder "e.g. Leeds"), **Get quotes** coral button (magnifier icon).
    Field labels: 11px/700/uppercase/`--ink-faint`. Inputs: white, 1.5px `--line`, radius
    12; focus = coral border + `0 0 0 3px rgba(216,80,60,.14)` ring.
    Mobile ≤680px: fields collapse to `1fr 1fr`, button spans full width.
  - Reassurance line under the panel (ivory): "Free to use · No obligation · One place for
    every supplier", each with a marigold check icon.

#### 3. Trust strip (`.home-trust-strip`)
- **Plum-ink** full-width band, ivory text. 4-up grid (→ 2-up ≤980px).
- Each item: 42px rounded icon tile, `rgba(224,169,79,.16)` bg + **marigold** icon; bold
  ivory title + 62%-ivory subtext. Same four items/copy as current `home.php`
  (Create one event / Compare in one place / Messages & bookings organised / For every
  occasion). Icons: `fa-calendar-plus`, `fa-layer-group`, `fa-comments`, `fa-calendar-days`.

#### 4. How it works (`.how-it-works`)
- Centered header: eyebrow "How it works" (coral), heading "Everything in one organised
  place", lead. 4 step cards (→ 2-up ≤980px).
- Card: white, 1px `--line`, radius 16, `--shadow-soft`, 28×24 padding. Inside: 52px
  rounded icon tile (`--paper-2` bg, **coral** icon), step number (11.5px/700/uppercase,
  **marigold**), H3 (600/19px), description (`--ink-soft`). Icons + copy unchanged from
  current view (`fa-calendar-plus`, `fa-compass`, `fa-file-invoice-dollar`,
  `fa-clipboard-list`).

#### 5. Featured suppliers (`.section-surface-alt` band)
- Alt surface (`--paper-2`). Header row: left = eyebrow "Featured suppliers" + heading "A
  glimpse of who you can find" + lead; right = **Browse all suppliers** coral button.
- 3-up card grid (→ 2-up ≤980px → 1-up ≤680px). **Supplier card** (`.service-card`):
  - Media: aspect 4/3 `object-fit:cover`; hover scales image to 1.05 (0.5s). Optional
    **Verified** pill top-left: `rgba(42,32,38,.78)` bg, white text, **marigold** check icon.
  - Body: category (11px/700/uppercase **coral**), title (600/18), meta row =
    location (`fa-location-dot` coral icon) + "From £…" price.
    Description 14px `--ink-soft`.
  - Footer (top hairline): star rating (**marigold** stars, bold value, faint review
    count) on the left, "View details →" (`--coral-deep`, arrow nudges on hover) on right.
  - Card hover: `translateY(-5px)` + `--shadow-lift`.
  - **Keep real `$services` data** — the three cards in the mock are illustrative only.

#### 6. Categories (`.category-card-container`)
- Centered header: eyebrow "Explore services", heading "Everything you need for your
  event", lead. Grid 4-up (→ 2-up). Tiles use existing `$homeCategoryTiles` images.
- Tile: aspect ~3/3.4, radius 16, image `object-fit:cover` (hover 1.06), bottom
  plum-ink gradient, white label bottom-left (600/17). Below grid: centered **Browse all
  suppliers** ghost button (transparent, 1.5px `--line`, hover border `--ink`).
- Assets provided: `venues, catering, photography, flowers, entertainment, beauty,
  transport, hero` (the last reused as "Planning & support").

#### 7. Vendor CTA (`.vendor-cta-section`)
- **Plum-ink** rounded block (radius 24), 2-col (copy | photo; stacks ≤980px, photo on top).
- Copy side: eyebrow "For suppliers" (**marigold**), heading "Are you an event supplier?"
  (ivory), lead (72% ivory), then **Become a vendor** (coral) + **Learn how it works**
  (ghost-light: transparent, 1.5px translucent-ivory border) buttons.
- Media side: `assets/flowers.jpg` (or any supplier photo), full-cover.

#### 8. Final CTA (`.home-final-cta`)
- **Coral gradient** band `linear-gradient(135deg, #D8503C, #BE4030)`, white, centered.
- Script line (Mr Dafoe, 95% ivory): "P.S. you're going to love this." Heading "Ready to
  start planning?" lead, then **Create your event** (light button) + **Browse suppliers**
  (ghost-light) buttons.

#### 9. Footer (`footer.php`)
- Plum-ink. 4-col grid (2fr/1fr/1fr/1fr → 2-up ≤980px → 1-up ≤680px):
  - Brand col: P.S. mark + "Partysmith", about paragraph (60% ivory), then a **marigold
    script** "P.S. leave the planning to us."
  - "Quick links", "Popular services", "For vendors" — same link sets as current footer.
- Bottom bar (top border): "© 2026 Partysmith. All rights reserved." + 3 social icon
  circles (Instagram / Facebook / Pinterest; hover fill **coral**).

---

## Interactions & Behavior

- **Nav:** sticky on scroll; translucent + blur. Mobile hamburger toggles the link list
  (reuse the existing Bootstrap `navbar-collapse` behaviour in `header.php`).
- **Occasion pills:** native radio group; checked pill = coral. Submit posts to
  `browse-services` (keep the existing `home.php` form `action`/`method` and field names —
  `occasion`, `date`, `guests`, `location`).
- **Buttons:** `translateY(-1px)` on hover; primary darkens coral→`--coral-deep`.
- **Cards / category tiles:** lift + inner-image zoom on hover (transition 0.2s card,
  0.5s image). "View details" arrow translates `+3px` on hover.
- **Inputs:** coral focus ring (above).
- No JS state beyond the existing view behaviour; this is a presentational rebrand.
- **Responsive breakpoints:** 980px (4→2 col grids; vendor stacks) and 680px (nav
  collapses, search fields 2-col, supplier cards 1-col).

## State Management

None new. Preserve all existing controller-driven data and session/role logic in
`home.php` / `header.php` / `footer.php` (role-based nav items, `$services`,
`$homeCategoryTiles`, `$cmsHome`, auth-aware CTAs). This change is **styling + brand only**.

## Assets

In `assets/` (sourced from the repo's own `public/assets/images/category-*.jpg` — i.e. you
already have these in production):

| File | Origin | Used for |
|---|---|---|
| `hero.jpg` | `category-event-planning-support.jpg` (1535×1024) | Hero background + "Planning & support" tile |
| `venues.jpg` | `category-venues.jpg` | Category tile |
| `catering.jpg` | `category-catering-drinks.jpg` | Category tile + supplier card |
| `photography.jpg` | `category-photography-video.jpg` | Category tile + supplier card |
| `flowers.jpg` | `category-flowers-styling.jpg` | Category tile + vendor CTA photo |
| `entertainment.jpg` | `category-entertainment.jpg` | Category tile + supplier card |
| `beauty.jpg` | `category-beauty-personal-care.jpg` | Category tile |
| `transport.jpg` | `category-transport-cars.jpg` | Category tile |

- **Icons:** Font Awesome 6.5.1 (already loaded sitewide).
- **Hero:** ideally replace with a dedicated branded hero shot; if so, keep the plum-ink
  scrim for text contrast.
- The **favicon** is an inline SVG in the mock's `<head>` — reproduce as
  `public/favicon.svg` (coral tile, ivory `P S`, plum-ink dots).

## Files

- `Website Mockup.html` — the authoritative hi-fi reference (all tokens, markup and CSS
  inline; open in a browser to inspect exact values with devtools).
- `assets/` — the 8 images above.

### Target files to edit in the real app
- `app/Views/header.php` — fonts, `theme-color`, `.navbar-brand` → P.S. mark.
- `app/Views/footer.php` — brand block → P.S. mark + script line.
- `app/Views/home.php` — markup already matches; minimal/no structural change.
- `public/assets/css/style.css` — replace `:root` palette tokens + navbar/footer styles.
- `public/assets/css/home.css` — homepage section styles to match this reference.
- (Rename product strings "For Your Events" → "Partysmith" across views, titles, OG meta.)
