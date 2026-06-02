# Handoff: For Your Events — `/profile` Dashboard Redesign

## Overview

Redesign of the two role-based dashboards rendered at `/profile` in the PartyPlanner ("For Your Events") app:

- **Customer dashboard** (`Refined` direction) — the planner's hub: stat tiles, per-event countdown cards, an attention queue, an events list, payment summary, and messages.
- **Vendor dashboard** (`Command Centre` direction) — a supplier ops console: a compact KPI strip, a prioritised action queue, a service-health panel, and a sticky right rail (payouts, upcoming, activity, quick actions).

The core goal was to replace the generic Bootstrap-blue styling on the current dashboard with the warm terracotta/cream brand used across the rest of the site, and to improve hierarchy and density.

## About the Design Files

The files in this bundle are **design references created in HTML/React (JSX via Babel)** — prototypes showing the intended look and behaviour. They are **not** production code to copy directly.

The target codebase is **CodeIgniter 4 (PHP) with server-rendered views and Bootstrap 5**. The task is to **recreate these designs in that existing environment**:

- Rebuild the markup in the existing dashboard view partials (`app/Views/dashboard/customer_main.php`, `app/Views/dashboard/vendor_main.php` and the tab partials).
- Add the new styles to `public/assets/css/dashboard.css` (or a new `dashboard-redesign.css`), reusing the existing CSS-variable approach where present.
- Wire the existing controller data (`Profile` / `VendorProfileController`) into the new layout. The sample data in `data.js` is illustrative only — map each field to the real model output.

The React component split (one file per dashboard) is just for the prototype; in CodeIgniter these become PHP partials.

## Fidelity

**High-fidelity.** Final colours, typography, spacing, radii, and component states are specified below and should be reproduced precisely. Use the codebase's existing Bootstrap grid/utilities where convenient, but match the visual spec — do not fall back to default Bootstrap card/colour styling.

---

## Design Tokens

All tokens are scoped under `.fye` in the prototype (`profile/brand.css`). Port them to `:root` or a dashboard-scoped block.

### Colours

| Token | Value | Use |
|---|---|---|
| `--paper` | `#F6F1EB` | Page background (cream) |
| `--paper-2` | `#F1E9DD` | Track/placeholder fill |
| `--card` | `#FFFFFF` | Card surface |
| `--card-warm` | `#FBF6EF` | Warm card surface (lead/attention rows) |
| `--ink` | `#2A2320` | Primary text |
| `--ink-2` | `#6E6258` | Secondary text |
| `--ink-3` | `#9C9085` | Tertiary/faint text |
| `--line` | `#ECE2D4` | Hairline borders |
| `--line-2` | `#E3D7C5` | Stronger hairline |
| `--terra` | `#B66A4D` | Brand accent (terracotta) |
| `--terra-deep` | `#9E573D` | Accent text / emphasis |
| `--terra-tint` | `#F4E5DC` | Accent tint backgrounds |
| `--sage` | `oklch(0.60 0.085 150)` | Success / confirmed |
| `--sage-tint` | `oklch(0.95 0.028 150)` | Success tint |
| `--gold` | `oklch(0.62 0.085 78)` | Pending / warning |
| `--gold-tint` | `oklch(0.95 0.030 78)` | Pending tint |
| `--slate` | `oklch(0.58 0.075 250)` | Info / action |
| `--slate-tint` | `oklch(0.95 0.022 250)` | Info tint |
| `--plum` | `oklch(0.56 0.085 358)` | Declined |
| `--plum-tint` | `oklch(0.95 0.024 358)` | Declined tint |

> Note: the accent palette deliberately avoids any blue except the muted `--slate` for "info". Remove the current `#1976d2`-style blues entirely.

### Typography

| Role | Family | Notes |
|---|---|---|
| Display / headings | `'Fraunces', Georgia, serif` | weights 500/600; used at 19–40px, letter-spacing `-.01em`/`-.02em`; italic variant for emphasis |
| UI / body | `'Manrope', system-ui, sans-serif` | weights 400–800 |
| Numerics / meta | `'Spline Sans Mono', ui-monospace, monospace` | KPI values, timestamps |

Numbers use `font-variant-numeric: tabular-nums` (`.num`).

### Radii & shadow

| Token | Value |
|---|---|
| `--r-lg` | `18px` (large cards) |
| `--r-md` | `12px` (tiles, list cards, panels) |
| `--r-sm` | `8px` (quick-action chips) |
| `--shadow-soft` | `0 1px 2px rgba(42,35,32,.04), 0 10px 30px rgba(42,35,32,.06)` (hover lift) |

### Status pills (`.pill`)

Pill = inline-flex, 11.5px/700, padding `3px 9px`, radius `999px`, with a 6px leading dot in `currentColor`.

- `.confirmed` → sage on sage-tint
- `.pending` → gold on gold-tint
- `.accepted` → terra-deep on terra-tint
- `.declined` → plum on plum-tint
- `.action` → slate on slate-tint

### Buttons (`.btn`)

13px/700, padding `9px 15px`, radius `10px`.

- `.primary` → bg `--terra`, white text
- `.ghost` → transparent, `--ink` text, `1px` border `--line-2`
- `.sm` modifier → padding `7px 12px`, 12.5px

---

## Shared chrome (both dashboards)

**Top bar** (`.fye-top`): white `#FFFDFB`, 16px/32px padding, bottom hairline. Left = stacked uppercase logo "FOR **YOUR** EVENTS" (the word "YOUR" in terracotta). Right = nav links (14px/600 `--ink-2`), a terracotta CTA pill, and a 38px circular avatar (terra-tint bg, initials).

**Tab bar** (`.fye-tabs`): white, padding `0 32px`, bottom hairline. Each tab 14px/600 `--ink-2`, padding `14px 16px`. Active tab: `--terra-deep` text, 2.5px `--terra` bottom border, weight 700.

- Customer tabs: **Main** · My events · Bookings · Messages · Payments · Favourites
- Vendor tabs: **Main** · Services · Bookings · Calendar · Host profile · Quotes
- (Both dashboards show the tab bar; "Main" is active.)

Icons throughout are **Font Awesome 6** solid (`fa-solid fa-*`).

---

## Screen 1 — Customer Dashboard (Refined)

**Purpose:** the planner's at-a-glance hub for everything across their events.

**Layout:** full-width body `.ra-body` (padding `28px 32px 36px`) under top bar + tabs.

1. **Header** — Fraunces h1 "Welcome back, Amara 👋" (30px/600) + one-line intro paragraph (`--ink-2`, max-width 640).
2. **Countdown cards** `.ra-countdowns` — 3-equal-column grid, gap 14px. One card per event, **sorted soonest-first**.
3. **Stat tiles** `.ra-stats` — 5-equal-column grid, gap 14px.
4. **Main grid** `.ra-grid` — two columns `1.65fr / 1fr`, gap 20px, items aligned to top.

### Countdown card (`.cd`)

Per-event card, white, `--r-md`, padding `16px 18px 15px`, flex column.

- Top row: a type `.pill.accepted` (e.g. "Wedding") and, **only on the soonest card**, a `.cd-flag` ("→ Up next", 10.5px/800 uppercase `--terra-deep`).
- Title: 15px/800, line-height 1.25.
- Countdown: `.cd-num` — big Fraunces number (40px/600 `--terra-deep`) + "days to go" (12.5px/600 `--ink-2`), baseline-aligned.
- Meta: date · location (12px `--ink-2`) with a leading terracotta calendar icon, separated by a top hairline (padding-top 11px).
- **Lead card** (`.cd.lead`, the soonest): warm bg `--card-warm`, `--line-2` border, and a 4px terracotta left edge (`::before`).

Sample order/values (from June 2 2026): Studio Summer Social **53** (lead) → Amara & Daniel's Wedding **73** → Mum's 60th Birthday **123**. Compute `days` server-side as whole days from today to the event date.

### Stat tile (`.ra-stat`)

White, `--r-md`, padding `16px 16px 15px`. A 40px rounded icon chip (tone-tinted) on top, then a 28px/800 value, then a 12px uppercase label. Five tiles: Pending (gold), Accepted (sage), Awaiting pay (terra), Confirmed (slate), Declined (plum).

### Left column (`.ra-col`)

- **Needs your attention** card (`.card`, white, `--r-lg`, padding 22px). Fraunces h2 (19px) with terracotta icon. Then attention rows `.att`: warm bg, hairline with a 3px tone-coloured left border, a 36px tinted icon chip, title (13.5px/700) + description (12.5px `--ink-2`), and a right-aligned `.btn.ghost.sm` CTA. Four rows: vendor accepted (sage/Review), deposit required (gold/Pay now), new messages (slate/Open), request declined (terra/Browse).
- **My events** card. Header with title+subtitle on the left and a `.btn.primary.sm` "＋ New event" on the right. Each event `.ev` (hairline, `--r-md`, padding `16px 18px`, hover → `--shadow-soft`): title (16px/800) + meta row (date / location / guests, 12.5px, terracotta icons) on the left, a type pill on the right; then a progress block `.ev-prog` ("Key services booked" label + `n/max` + 6px terracotta progress bar); then an estimated-spend line when cost > 0.

### Right column (`.ra-col`)

- **Payment summary** card — key/value rows `.kv` (hairline-separated): Deposits paid, Remaining balance, and a `.kv.total` Total event spend (16px, `--terra-deep`).
- **Messages** card — rows `.msg`: 38px circular avatar (terra-tint, initials), sender (13.5px/700) + truncated snippet (12.5px `--ink-2`, ellipsis), right-aligned timestamp with an 8px terracotta unread dot when unread.

> The earlier "Planning progress" checklist card was intentionally removed — do not reintroduce it.

---

## Screen 2 — Vendor Dashboard (Command Centre)

**Purpose:** a dense ops console for suppliers to action requests fast.

**Layout:** under top bar + tabs, body `.cc-body` is a two-column grid `1fr / 320px` filling height.

- **Left** `.cc-main` (padding `24px 28px 32px`).
- **Right** `.cc-rail` (sticky rail, `#FFFDFB`, left hairline, padding `24px 22px`, 22px gap between blocks).

### Left column

1. **Head** `.cc-head` — Fraunces h1 "Kitchen command centre" (24px) + mono meta line ("N requests open · next payout 04 Jun").
2. **KPI strip** `.cc-kpis` — 5 cells in a single bordered, rounded, divided row. Each cell: tiny uppercase label with leading terracotta icon, a 24px mono value, and a coloured delta (`.up` sage / `.dn` plum / `.fl` faint). KPIs: Open req (5, "2 urgent"), Upcoming (8, "+3 booked"), This month (£6.5k, "▲ 41%"), Avg reply (3h, "top 18%"), Views (1.2k, "▲ 12%").
3. **Action queue panel** `.cc-panel` — header with title + a terracotta count badge `.ct` and filter chips (All/Urgent/This week). Each row `.cc-row` is a grid `4px | 38px | 1fr | auto | auto | auto`: a priority bar (`.hi` terra / `.md` gold / `.lo` line), a 38px tinted icon chip, title (13.5px/700) + meta (12px `--ink-2`), a mono date, a mono value, and a `.btn.primary.sm` "Accept". Hover → `--card-warm`. Five request rows from the data.
4. **Service-health panel** `.cc-panel` — rows showing each service, its all-time bookings, a completeness progress bar (sage when 4/4, gold otherwise), and an `n/4` mono count.

### Right rail (`.cc-rail`)

Each block: 11px/800 uppercase `--ink-3` heading, then content.

- **Payouts** — a `.cc-gauge` (settled label + value, an 8px segmented track, then Pending and Next-payout key/values).
- **Upcoming** — `.cc-mini` rows: a 44px date chip (mono day, terracotta month) + event name (12.5px/700) + customer · venue (11.5px `--ink-2`).
- **Recent activity** — `.act` rows: a small tone dot + text (12.5px), hairline-separated.
- **Quick actions** — `.cc-quick` 2-col grid of bordered chips, each with a terracotta icon over a 12px/700 label: Add service, Availability, Messages, Analytics.

---

## Interactions & Behaviour

The prototype is static, but the intended behaviour:

- **Tabs** switch dashboard sub-views (existing routing — keep current tab targets).
- **Attention / action rows**: CTA buttons link to the relevant flow (pay deposit, review booking, open messages, browse suppliers).
- **Accept / Send quote / Decline** on vendor request rows hit the existing booking-response endpoints.
- **Event cards / request rows** are clickable through to their detail pages.
- **Hover**: event cards and action rows lift / warm-fill (`--shadow-soft` / `--card-warm`).
- **Countdowns** recompute daily server-side; sort ascending by days remaining; the minimum gets the "Up next" lead treatment.
- No custom animations beyond CSS hover transitions (`box-shadow .15s`).

## State / Data mapping

Map real controller output to these fields (sample shapes in `data.js`):

- Customer: `stats` (pending/accepted/awaiting/confirmed/declined counts), `events[]` (title, date, loc, type, guests, booked, max, cost, **days**), `attention[]`, `messages[]`, `money` (deposits/remaining/total).
- Vendor: `stats` (pending/upcoming/earnings/services/views/response), `requests[]` (who, ev, svc, date, guests, val, prio, when), `upcoming[]`, `services[]` (with desc/img/price/policy booleans for the health bar + bookings count), `activity[]`, `payouts` (settled/pending/next).

## Assets

- **Fonts:** Fraunces, Manrope, Spline Sans Mono (Google Fonts). Already loadable via `<link>`; self-host if the app does.
- **Icons:** Font Awesome 6 solid.
- **Images:** none required for these two screens (the chosen directions use no photography). Avatars are initials in tinted circles.

## Files in this bundle

- `For Your Events - profile redesign.html` — entry point (loads everything; a pan/zoom canvas showing both dashboards).
- `brand.css` — all design tokens + component styles (the source of truth for the spec above).
- `data.js` — illustrative sample data shapes.
- `helpers.jsx` — shared top bar + tab bar components.
- `customerA.jsx` — Customer · Refined dashboard.
- `vendorC.jsx` — Vendor · Command Centre dashboard.
- `app.jsx` — canvas assembly (prototype scaffolding only — ignore for production).
- `design-canvas.jsx` — prototype canvas component (ignore for production).

> Only `customerA.jsx`, `vendorC.jsx`, and `brand.css` describe the production UI. The other files are prototype scaffolding.
