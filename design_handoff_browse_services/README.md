# Handoff: Browse Services — Direction A ("Event Picker leads")

## Overview

A redesign of the **Browse Services** page for *For Your Events* (a party/event
planning marketplace). The page lets a user browse supplier services (venues,
catering, entertainment, etc.) and **add them to a specific event's basket**.

The core idea of Direction A: **the event you are shopping for is the primary
decision on the page.** The user first picks which of their events they're
planning, then browses and adds services to that event. The selected event's
basket context stays visible the whole time — first as the expanded selected
card, then as a slim sticky "Shopping for…" strip once the user scrolls into the
results. There is exactly **one** site navbar; the page never shows two stacked
bars.

This replaces an older page that was too complex (a multi-level category cascade
filter form, price min/max, guest count, event-type selectors all shown at once)
and that buried the "which event am I shopping for" choice.

## About the Design Files

The files in this bundle are **design references created in HTML/React-via-Babel** —
a prototype showing the intended look and behaviour, **not** production code to
ship as-is. They use in-browser Babel and global-scope components purely so the
prototype runs from a static file.

Your task is to **recreate this design in the target codebase** (the real app is
a **CodeIgniter 4 / PHP** application using server-rendered PHP views with plain
CSS — see "Target Environment" below), using its established patterns. Treat the
React component structure here as a description of the UI's anatomy and state, not
as the framework you must use. If you are implementing in a JS framework instead,
the component breakdown maps over directly.

## Target Environment

The production app (`theteepeenetwork/partyplanner`) is **CodeIgniter 4**:

- Views are PHP files in `app/Views/` (e.g. the current `browse_services.php`),
  composed with `<?= $this->include('header') ?>` / `footer`.
- Styling is plain CSS in `public/assets/css/` (`style.css`, page-specific files).
  **No Tailwind, no React in the current page.** Bootstrap utility classes appear
  in some markup (`d-flex`, `flex-wrap`).
- The services list, categories, filters, sort and pagination are produced by
  `Service_Controller` and passed to the view.
- Images already exist at `public/assets/images/category-*.jpg` (the same ones
  bundled here).

**Recommended implementation path:** rebuild `browse_services.php` markup to match
this layout, move the styles into a new `public/assets/css/browse-services.css`
(adapt the bundled `browse.css`), and add a small amount of vanilla JS (or Alpine,
if the project uses it) for: event switching, add-to-basket, the favourite toggle,
and the scroll-triggered context strip. Keep the heavy filtering server-side as it
is today; the redesign mainly changes *presentation and hierarchy*, not the query.

## Fidelity

**High-fidelity.** Final colours, typography, spacing, radii, shadows and
interactions are all specified below and present in `browse.css`. Recreate the UI
to match. The service data in `data.js` is **mock/placeholder** — wire the real
controller data in its place (mapping notes in "Data Model").

---

## Screen: Browse Services (single page, responsive)

### Purpose

Pick the event being planned, then browse services and add them to that event's
basket. Works as one scrolling page on both desktop and mobile.

### Page anatomy (top → bottom)

1. **Site nav** (`Nav`) — slim top bar, 64px tall desktop / 56px mobile. Logo
   left; links right (Find Suppliers [active], My Events, Inspiration, Basket(n),
   "Start Planning" CTA). On mobile, links collapse to a single basket button.
   *This is the ONLY persistent bar.*
2. **Condensed context strip** (`.ppa-condensed`) — **not rendered at rest.** It
   mounts only after the user scrolls the page body past ~150px, and unmounts when
   they scroll back above ~90px. ~57px tall, white background, bottom border +
   soft shadow, slides down via a 0.26s keyframe. Contents, left→right:
   event-type icon chip · "SHOPPING FOR" eyebrow + event title (click = event
   switcher dropdown) · right-aligned "N services / est. £X" · "View basket" button.
3. **Step 1 — the picker (hero band)** — warm gradient background
   (`--pp-warm` → `--pp-cream`), bottom hairline. Eyebrow "Step 1 · Who are we
   shopping for?", H1 "Pick the event you're planning." Below: a responsive grid
   of **event pick cards** + a dashed "New event" tile.
4. **Step 2 — browse & add** — eyebrow "Step 2 · Add services to [event]".
   Toolbar row: search input (grows) · "Filters" button · sort `<select>`
   (desktop only). Then a horizontally-scrollable row of **category chips**
   (All + 8 categories). Then a heading row ("All services" / category name +
   "N found"). Then the **service card grid** (3-up desktop, 1-up mobile).
5. **Toast** (`.pp-toast`) — bottom-centre confirmation ("Added "X" to [event]").

### Component: Event pick card (`EventPickCard`)

A selectable card representing one of the user's events.

- **Container:** border-radius 16px, padding 15×16px. Inactive: background
  `rgba(255,255,255,.55)`, border `1.5px var(--pp-line)`. **Active:** background
  `#fff`, border `1.5px var(--pp-terracotta)`, plus focus-ring shadow
  `0 0 0 3px rgba(182,106,77,.16)` and `--pp-shadow-lift`. Transition `all .2s`.
- **Top row:** 44×44px rounded icon tile (radius 13). Active tile = solid event
  colour with white icon; inactive = tint `color-mix(in srgb, <eventColor>, #fff 85%)`
  with coloured icon. Then a text block (flex:1, min-width:0):
  - Type label: 10px / 800 / uppercase / 0.1em tracking / `--pp-muted`.
  - "N added" badge (only if basket non-empty): 10.5px/800, colour
    `--pp-terracotta-deep` on `rgba(182,106,77,.14)`, pill.
  - Title: Fraunces 600, 16.5px, line-height 1.2, **truncated** (nowrap +
    ellipsis, relies on min-width:0 parent).
  - Meta line: 12.5px `--pp-muted`, **nowrap+ellipsis**, calendar-day + date,
    user-group + guest count, gap 10px.
  - Trailing 22px check circle, top-aligned. Active = solid `--pp-terracotta`
    with white check; inactive = empty `2px var(--pp-line)` ring.
- **Selected-only footer** (the basket carrier — replaces the old dark bar):
  appears only on the active card. Top hairline border, padding-top 12px, space-
  between row: left = "est. **£total**" (or muted "Basket empty — add services
  below" when empty); right = primary **View basket** button (disabled when empty).

Event colours/icons (`EVENT_THEME` in `directionA.jsx`):

| Type | Icon (Font Awesome) | Colour |
|---|---|---|
| Wedding | `fa-heart` | `#B66A4D` |
| Corporate | `fa-briefcase` | `#6E7E5B` |
| Birthday | `fa-cake-candles` | `#7A5A78` |
| (fallback) Event | `fa-calendar-day` | `#B66A4D` |

**New event tile:** dashed `1.5px var(--pp-line)` border, radius 16, min-height
76, terracotta text, plus-icon + "New event", stretches to card height.

### Component: Service card (`ServiceCard`, in `components.jsx`)

- Container: white, radius 16 (`--pp-r-card`), 1px hairline border, `--pp-shadow-soft`.
  Hover: `translateY(-5px)` + `--pp-shadow-lift`; inner image scales to 1.05
  (0.5s ease). Flex column.
- **Media:** aspect-ratio 4/3, `object-fit: cover`. Overlays: category pill
  (top-left, 11px/700 uppercase, `rgba(34,27,24,.72)` bg, white, backdrop-blur);
  favourite button (top-right, 34px circle, white bg → terracotta when on).
- **Body:** padding 14×15×15. Title Fraunces 600 17px. Location line 12.5px
  `--pp-muted`, location-dot icon in terracotta. Rating row (see below).
- **Footer (margin-top auto):** price left (800, 16px; `<small>` unit 12px muted),
  **Add** button right. Add button default = warm bg, terracotta-deep text,
  `1.5px rgba(182,106,77,.35)` border; hover = solid terracotta/white; **added**
  state = solid green `#2E7D55`/white with check + label "Added".
- Rating (`Stars`): solid star `#E0A458`, bold value, muted "(reviews)". Hidden
  when the `.pp-no-ratings` class is on an ancestor.

### Component: Category chips (`CategoryChips`)

Pill buttons, 8px×15px, radius pill, white bg + 1.5px hairline. Default text
`--pp-dark`; hover → terracotta. **Selected** = solid `--pp-ink` bg, white text.
Leading icon per category. "All" chip first. Row scrolls horizontally on overflow.

### Component: Search bar (`SearchBar`)

Pill, 48px tall, white, 1.5px hairline; focus-within → terracotta border +
`0 0 0 4px rgba(182,106,77,.13)` ring. Magnifier icon, placeholder
"Search services, vendors, styles…".

### Component: Event switcher menu (`EventMenu`)

Dropdown opened from the event title in the condensed strip. White, radius 14,
`--pp-shadow-pop`, min-width 260. "YOUR EVENTS" label, one row per event (radio
dot + name + "date · guests · N in basket"), divider, dashed "Create a new event"
row. Closes on outside-click.

---

## Interactions & Behaviour

- **Select event:** click a pick card → it becomes active (expands footer); the
  condensed strip (when visible) and Step 2 heading update to that event.
- **Add to event:** click a service card's Add button → service id is appended to
  the active event's basket (no dupes); button flips to green "Added"; toast
  fires "Added "<title>" to <event>" for ~2.2s. Disabled/relabelled "Add" with no
  active event.
- **Favourite:** heart toggles per-service (local UI state only in the prototype).
- **Scroll-triggered context strip:** listen to the scrolling container's
  `scrollTop`. Show the strip when `scrollTop > 150`; hide when `scrollTop < 90`
  (hysteresis prevents flicker). Mount/unmount (don't animate `height` — that was
  unreliable; the prototype conditionally renders + slides in with the `ppaSlide`
  keyframe: opacity 0→1, translateY -100%→0, 0.26s ease).
- **Event switcher:** click event title in the strip → `EventMenu` dropdown; pick
  another event or "Create a new event".
- **Create event:** "New event" tile / menu row → creates an event and makes it
  active. (Prototype seeds a placeholder title/date/guests; in production open the
  real create-event flow or inline form.)
- **Filters / Sort:** present as controls; the heavy filtering stays server-side.
  "Filters" should open a drawer (not yet designed — see "Open follow-ups").
- **Search:** filters the visible list by title/vendor/category/location substring
  (prototype does it client-side; production should hit the existing controller).
- **Responsive:** `mobile` prop switches layouts — nav collapses; picker cards
  become an 84%-width horizontal swipe row; service grid goes 1-up; strip hides
  the "View basket" label, keeping the icon.

## State Management

Centralised in the `useShopping` hook (`components.jsx`). Needed state:

- `events[]` — each `{ id, title, dateLabel, guests, type, basket:number[] }`.
- `activeId` — currently selected event id; `activeEvent` derived.
- `favs` — map of serviceId→bool (UI only).
- `toast` — transient message string (auto-clears ~2.2s).
- Derived: `basketServices` (resolve ids→services), `total` (sum of `lineValue`).
Component-local state in `DirectionA`: `cat` (active category id|null), `q`
(search string), `menu` (switcher open), `scrolled` (strip visibility).

`lineValue(service, guests)`: `pp` unit → price × guests; `hr` → price × 4
(assumed booking block); flat → price. `money()` formats GBP, no decimals.

## Design Tokens (from `browse.css` `:root`)

**Colour**

- `--pp-dark #3A312D` · `--pp-ink #221B18` (text)
- `--pp-terracotta #B66A4D` (accent) · `--pp-terracotta-deep #9E573D`
- `--pp-cream #F6F1EB` (page) · `--pp-warm #F5EFE6` (panels) · `--pp-sand #EDE5D8`
- `--pp-white #fff` · `--pp-muted #6F665F`
- `--pp-line rgba(58,49,45,.12)` · `--pp-line-soft rgba(58,49,45,.07)`
- Success (Added): `#2E7D55` · Rating star: `#E0A458`
- Event accents: see `EVENT_THEME` table above.

**Typography**

- Display: **Fraunces** (opsz, weights 500/600) — headings, titles, totals.
- Sans/UI: **Manrope** (400–800) — body, labels, buttons.
- Mono: **Spline Sans Mono** (available; not heavily used).
- Scale in use: H1 32px desktop / 25 mobile (Fraunces 600, -0.01em). H2 22.
  Card title 17. Body 14–15. Eyebrow 11px/800/0.16em uppercase. Meta 12.5.

**Radius** `--pp-r-card 16` · `--pp-r-btn 11` · `--pp-r-pill 999`
**Shadow** soft `0 8px 30px rgba(34,27,24,.08)` · lift `0 14px 40px rgba(34,27,24,.15)`
· pop `0 20px 56px rgba(34,27,24,.22)`
**Spacing:** grid gaps 12–18px; section padding 24–28px desktop / 16–20 mobile.

## Data Model (mock → real)

`data.js` defines the shape; map to the real `Service_Controller` output:

- **categories**: `{id, name, icon, img}`. Real categories come from the DB;
  reuse the bundled `category-*.jpg` images and pick Font Awesome icons.
- **services**: `{id, title, vendor, cat, price, unit('from'|'pp'|'hr'),
  rating, reviews, loc, img, catName}`. Map to real service rows; `img` should be
  the supplier's photo (fall back to `category-*.jpg` then
  `fallback-service-card.jpg`).
- **events**: `{id, title, date, dateLabel, guests, type, basket:[serviceId]}`.
  Comes from the logged-in user's events; `basket` is the per-event saved
  services. `type` drives the icon/colour (`EVENT_THEME`).

## Assets

All in `public/assets/images/` (bundled, sourced from the existing repo):
`category-venues.jpg`, `category-catering-drinks.jpg`, `category-entertainment.jpg`,
`category-photography-video.jpg`, `category-flowers-styling.jpg`,
`category-beauty-personal-care.jpg`, `category-transport-cars.jpg`,
`category-event-planning-support.jpg`, and `fallback-service-card.jpg`.
Icons: **Font Awesome 6.5.1** (free solid/regular). Fonts: Google Fonts
(Manrope, Fraunces, Spline Sans Mono).

## Files in this bundle

- `Direction A — Standalone.html` — open this to see the design running on its own
  (flip `mobile` / `startEmpty` in the inline `Host` component). Needs network for
  CDN React/Babel/fonts/icons.
- `directionA.jsx` — Direction A layout: `EVENT_THEME`, `EventPickCard`,
  `DirectionA` (picker, condensed strip, scroll logic, Step 2).
- `components.jsx` — shared primitives + `useShopping` state hook: `ServiceCard`,
  `SearchBar`, `CategoryChips`, `EventMenu`, `Toast`, `Nav`, `Stars`,
  `money/lineValue/priceText/filterServices`.
- `browse.css` — all design tokens and component styles (authoritative for
  colours/spacing/shadows/states).
- `data.js` — mock categories/services/events (replace with real data).
- `public/assets/images/` — category + fallback imagery.

## Open follow-ups (not yet designed)

- **Filters drawer** (advanced filters behind the "Filters" button).
- **Basket / request-quotes** screen.
- **No-event create flow** (inline form vs. modal) — prototype currently seeds a
  placeholder event.
Ask the design owner before inventing these.
