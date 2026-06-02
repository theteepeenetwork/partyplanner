# Handoff: For Your Events — `/profile` Journey (Customer + Vendor)

## Overview

This is a clickable, hi-fidelity prototype of the **For Your Events** signed-in `/profile` experience — the private hub a user lands in after logging in. It is a **two-sided product**: one role-switchable shell serving two distinct journeys:

- **Customer** (event host, "Amara Okafor") — plans celebrations, requests suppliers, tracks bookings, pays deposits, messages vendors.
- **Vendor** (supplier business, "The Roaming Kitchen") — receives requests, sends quotes, manages service listings, tracks earnings & availability.

Both roles share one chrome (top bar + tabs) and one design system. A role switch in the top bar toggles between them. The prototype covers **15 screens** plus detail views, all navigable.

## About the Design Files

The files in this bundle are **design references created in HTML/React-via-Babel** — a prototype that demonstrates the intended look, layout, content, and click-through behaviour. **They are not production code to ship directly.** The Babel-in-browser setup, the `window.PAGES` global router, and the inline `data.js` mock layer are prototyping conveniences, not architectural recommendations.

Your task is to **recreate these designs in the target codebase's environment** using its established patterns — real routing, real components, a real data/API layer, and the codebase's styling approach. If no front-end environment exists yet, choose an appropriate stack (e.g. React + a router + a CSS solution) and implement there. Lift exact values (colors, type, spacing, copy) from this bundle; re-implement structure idiomatically.

## Fidelity

**High-fidelity.** Colors, typography, spacing, border-radii, shadows, hover states and copy are all final and intentional. Recreate the UI to match. All design tokens are defined as CSS custom properties in `brand.css` (see **Design Tokens** below) — port them to your system's token mechanism.

The only intentionally *low*-fi elements are **image placeholders** (striped `.ph` blocks with a monospace caption like "cover photo" / "service photo" / venue name). These mark where real photography goes — replace with real `<img>`/upload components. There is **no real backend**: forms are non-functional mockups, the message composer is a static affordance, and all numbers come from `data.js`.

---

## Architecture of the Prototype (for orientation)

| File | Role |
|---|---|
| `For Your Events - profile journey.html` | Entry point. Loads fonts (Google: Fraunces, Manrope, Spline Sans Mono), Font Awesome 6.5.1, `brand.css`, `proto.css`, then React + Babel, then the scripts below. |
| `brand.css` | **Design system** — all tokens + shared chrome (top bar, tabs, pills, buttons) + the three dashboard "direction" component libraries (`ra-`, `ed-`, `cc-` prefixes). Scoped under `.fye`. |
| `proto.css` | Prototype-specific layout (page scaffold, list rows, detail two-column, message thread, tables, calendar, galleries). Loaded after `brand.css`, reuses its tokens. |
| `data.js` | Mock data layer — `window.PROTO.customer` and `window.PROTO.vendor`. Replace with real API/state. |
| `shell.jsx` | Router (hash-based), `TopBar`, role-aware `Tabs`, shared UI helpers (`StatusPill`, `Avatar`, `money`, `PH`, `Back`, `Link`). |
| `app.jsx` | Root component — resolves the current route to a page via `window.PAGES`. |
| `c-pages-1.jsx` | Customer: Dashboard, My Events, Event Detail. |
| `c-pages-2.jsx` | Customer: Bookings, Booking Detail, Payments, Messages, Favourites, Browse, New Event. |
| `v-pages-1.jsx` | Vendor: Dashboard (command centre), Requests/Bookings, Request Detail. |
| `v-pages-2.jsx` | Vendor: Services, Service Editor, Earnings, Calendar, Messages, Host Profile. |

**Routing model:** hash routes, `#/<role>/<page>/<param>`. `c/` = customer, `v/` = vendor. Detail routes carry an id (`c/event/wedding`, `c/booking/b1`, `v/request/r1`, `v/service/s1`). Default route is `c/dashboard`. Detail routes map back to their parent tab for active-state highlighting (see the `parent` map in `shell.jsx`).

---

## Shared Chrome (present on every screen)

### Top bar (`.fye-top`)

- Full-width, `#FFFDFB` background, 1px bottom border (`--line`), padding `16px 32px`, space-between.
- **Left — logo (`.fye-logo`):** stacked two-line lockup. Line 1 "For **Your**" ("Your" in `--terra`), line 2 "Events" in `--ink-3`. Uppercase, 800 weight, letter-spacing `.06em`. Links to the role's dashboard.
- **Right (`.fye-topnav`):** a **role switch** segmented control, a message envelope icon link, and a circular avatar (`--terra-tint` bg, `--terra-deep` text, initials — "AO" customer / "RK" vendor).
- **Role switch (`.role-switch`):** pill container (`--paper-2`), two buttons "Customer" (champagne-glasses icon) / "Vendor" (store icon). Active button: white bg, `--terra-deep` text, soft shadow. Switching navigates to that role's dashboard. *This is a prototype affordance — in production a user has one role (or an account-level toggle), not an in-bar switch.*

### Tabs (`.fye-tabs`)

- Horizontal tab row, `#FFFDFB`, 1px bottom border, padding `0 32px`, 14px tabs with a 2.5px bottom-border active indicator in `--terra` and `--terra-deep` text.
- **Customer tabs:** Main · My events · Bookings · Payments · Messages · Favourites
- **Vendor tabs:** Main · Bookings · Services · Calendar · Earnings · Host profile

---

## Screens / Views

> Layout note: standard content pages use `.page` (max-width 1180px, centred, padding `28px 32px 48px`; `.page-wide` = 1260px). Detail pages use `.detail` — a CSS grid `1.7fr 1fr`, gap 22px, collapsing to one column below 1000px.

### CUSTOMER

#### 1. Customer Dashboard — `c/dashboard` (`CustomerDashboard`)

The customer's home. Sections top→bottom:

- **Greeting** (`.ra-head`): "Welcome back, Amara 👋" in Fraunces 30px/600, plus a one-line subhead in `--ink-2`.
- **Event countdown cards** (`.ra-countdowns`, 3-col grid): one card per event, sorted soonest-first. Each shows event-type pill, title, a big **days-to-go** number (Fraunces 40px, `--terra-deep`), and a date·location meta line. The soonest event gets `.cd.lead` — warm bg + a 4px `--terra` left bar + an "↗ Up next" flag. Cards link to event detail.
- **Stat row** (`.ra-stats`, 5-col): Pending / Accepted / Awaiting pay / Confirmed / Declined, each with a tinted square icon (tones: gold, sage, terra, slate, plum), a big count, and a label. Each links to a filtered list. Counts derive from booking statuses.
- **Two-column grid** (`.ra-grid`, `1.65fr 1fr`):
  - Left: **"Needs your attention"** card — list of `.att` rows (colored left-border, tinted icon, title + description, ghost CTA button). Below it, **"My events"** card with a "New event" primary button and per-event rows (`.ev`) showing title, meta (date/location/guests), type pill, and a "Suppliers booked X of N" progress bar.
  - Right: **"Payment summary"** card (key/value rows: deposits paid, remaining balance, total spend — total row emphasised in `--terra-deep`), and a **"Messages"** card (3 most-recent threads, avatar + name + snippet + time, unread dot in `--terra`).

#### 2. My Events — `c/events` (`CustomerEvents`)

- Page head with title + subhead + "New event" primary button.
- **Gallery grid** (`.gal`, 3-col → 2-col under 1000px) of event cards (`.gcard`): striped image placeholder (venue name as caption), type pill + days-remaining, title, date line, and a "X of N booked / £budget" progress bar. Cards link to event detail.

#### 3. Event Detail — `c/event/:id` (`CustomerEvent`)

- Back link → "All events".
- **Hero band** (`.hero-band`): full-width striped image placeholder (190px), then body with type pill, event title (Fraunces 30px), meta row (date / location / guests / days-to-go), and an "Add a service" primary button.
- **Detail two-column:**
  - Left: **"Booked suppliers"** card (`.icard`) — supplier rows (`.srow`: icon, vendor name, category·service, status pill + amount), linking to booking detail. Below, **"Still to arrange"** card — all planning categories as pills; covered ones green (`confirmed`) with a check, uncovered ones slate (`action`) with a plus.
  - Right: **"Budget"** card — committed amount (Fraunces 32px) of total, a stacked progress track (paid = sage, due = gold, unallocated = `--paper-2`) with a legend, and a "View payments" button. Below, **"At a glance"** key/value card (guests, suppliers booked, awaiting action, days remaining).

#### 4. Bookings — `c/bookings` (`CustomerBookings`)

- Page head + "Find suppliers" primary button.
- Bookings grouped by status into labelled sections (`.glabel`: icon + label + hairline + count): **Accepted — action needed**, **Pending — awaiting vendor**, **Confirmed**, **Declined**. Each booking is a list row (`.lrow`, grid `42px 1fr auto auto`): square avatar, vendor name + "category · service — *event*", status pill, amount. Links to booking detail.

#### 5. Booking Detail — `c/booking/:id` (`CustomerBooking`)

- Back → "All bookings". Detail two-column:
  - Left: **booking card** — avatar + vendor + "category · for *event*" + status pill; a **quote breakdown** (`.quote`, dashed border, warm bg): service line, deposit (15%), balance-on-the-day, bold total. Then a contextual **actions** row that changes by status: Accepted→"Pay deposit"; Pending→"Awaiting vendor reply"; Confirmed→"Confirmed & paid"; always a "Message vendor" ghost button if a thread exists; Declined→"Find alternative". Below, a **"Recent messages"** card showing the last 2 bubbles + "Open conversation".
  - Right: **"Event"** card (links to the event) and **"What happens next"** explanatory card whose copy varies by status.

#### 6. Payments — `c/payments` (`CustomerPayments`)

- Title + subhead.
- **Mini-stat row** (`.minis`, 4-col): Deposits paid (sage) / Due now (gold) / Remaining balance / Total event spend — each big Fraunces number + label.
- If deposits are due: a **gold call-out card** ("N deposits due — £X", "Pay all deposits" primary button).
- **Payments table** (`.ptable`): columns Supplier (name + service) · Event · Status (pill) · Deposit (right) · Balance (right) · action ("Receipt" link if paid, "Pay" primary button if due). Row hover = warm bg.

#### 7. Messages — `c/messages/:thread?` (`CustomerMessages` → `ConvView`)

- Title + a **thread panel** (`.thread`, grid `280px 1fr`, collapses under 1000px):
  - Left list (`.thread-list`): thread items (avatar + name + last-message preview); active item highlighted `--terra-tint`.
  - Right conversation: header (avatar + name + role/event), body of chat **bubbles** (`.bubble.them` = white, left-aligned; `.bubble.me` = `--terra` fill white text, right-aligned; each with a timestamp), and a **composer** (`.conv-input`: pill "Write a message…" + send button — visual only).

#### 8. Favourites — `c/favourites` (`CustomerFavourites`)

- "Saved suppliers" title + subhead. `.gal` grid of supplier cards: image placeholder, name, "category · location", price-from + star rating (filled `--gold`, empty faint). Links to Browse.

#### 9. Browse / Find Suppliers — `c/browse` (`CustomerBrowse`)

- Back → Dashboard. Title + subhead. A **filter chip row** (All / Catering / Entertainment / Flowers / Photography / Bar / Cake — "All" active in `--terra`). `.gal` grid of supplier cards as above, each with a "Request a quote" ghost button.

#### 10. New Event — `c/event/new` (`CustomerNewEvent`)

- Narrow page (max 640px). Back → My events. A **form card** with mock fields (Event name, type, date, location, guests, budget — each a label + placeholder-styled input box) and a full-width "Create event" primary button. *Inputs are non-functional placeholders — wire to real form controls.*

### VENDOR

#### 11. Vendor Dashboard — `v/dashboard` (`VendorDashboard`)

The vendor's **command centre** — denser, ops-console aesthetic (`cc-` styles). Layout `.cc-body` = grid `1fr 320px` (main + sticky rail), min-height `calc(100vh - 110px)`.

- **Head:** "Kitchen command centre" (Fraunces 24px) + a monospace meta line ("N requests open · next payout 04 Jun").
- **KPI strip** (`.cc-kpis`, 5-col, bordered, dividers between): Open req / Upcoming / This month (£6.5k) / Avg reply / Views — each a label (uppercase, with icon), a **monospace value** (24px), and a delta in sage/plum. Each links somewhere.
- **"Requests to action" panel** (`.cc-panel`): panel header with a count badge + "View all". Rows (`.cc-row`, grid `4px 38px 1fr auto auto auto`): a priority bar (hi=terra / md=gold / lo=line), tinted icon, "service · N covers" + "customer — event", date (mono), value, and a status pill. Links to request detail.
- **"Service health" panel:** one row per service — priority bar (sage if complete else gold), title + "N bookings all-time", a completeness progress bar, and "X/4" (mono). Links to service editor.
- **Right rail** (`.cc-rail`, `#FFFDFB`, left border): **Payouts** gauge (settled bar + pending + next-payout date), **Upcoming** mini-rows (date chip + event + customer·covers), **Recent activity** feed (colored dot + text, each linking), and a 2×2 **Quick actions** grid (Add service / Availability / Messages / Analytics).

#### 12. Requests & Bookings — `v/bookings` (`VendorBookings`)

- Head + "Calendar" ghost button. Requests grouped by status (`.glabel` sections): **New — needs your response**, **Quoted — awaiting customer**, **Confirmed & upcoming**, **Declined / expired**. Each row (`.lrow`, grid `52px 1fr auto auto auto`): a **date chip** (`.dchip`: month + day), "service · N covers" + "customer — event", an "Urgent" pill if high-priority & new, status pill, value. Links to request detail.

#### 13. Request Detail — `v/request/:id` (`VendorRequest`)

- Back → Requests & bookings. Detail two-column:
  - Left: **request card** — customer avatar + name + event + status pill; meta row (service / date / guests); a **quote breakdown** (`N × £perHead`, "Service & setup included", bold quote total); contextual **actions**: New→"Accept request" + "Send custom quote" + "Decline" (danger, right-aligned); Quoted→"Awaiting customer" + "Edit quote"; Confirmed→"Confirmed booking"; "Message" if thread exists. Below, a **"Conversation"** card (last 3 bubbles + open link).
  - Right: **"Customer"** key/value card (name, event, requested-when, quote-expires) and a **"Tip"** card (response-time conversion nudge).

#### 14. My Services — `v/services` (`VendorServices`)

- Head + "Add a service" primary button. `.gal` grid of service cards: photo placeholder (or a gold "no image" block if none), title + "X/4" completeness pill, "from £N per head · N bookings", and a completeness progress bar (sage if 4/4 else gold). Links to service editor.

#### 15. Service Editor — `v/service/:id` (and `v/service/new`) (`VendorService`)

- Narrow page (max 760px). Back → My services. Head shows the service title (or "New service") + a "Live" pill when editing. A **form card** of fields, each with a label + a **Complete / To do** status pill: Service title, Description (textarea), Price-from + Unit (2-col), a **Photos** 4-slot grid (filled placeholders or dashed "+" add-slots), Cancellation policy. Footer: "Save service" primary + "Preview" ghost. *Form is visual — wire to real inputs/upload.*

#### 16. Earnings & Payouts — `v/earnings` (`VendorEarnings`)

- Title + subhead. **Mini-stat row** (This month £6.5k ▲41% / Settled 90 days / Pending payout / Avg per month). Detail two-column:
  - Left: **"Six-month trend"** card — a simple **bar chart** built from divs (per-month bars, current month emphasised `--terra`, others `--terra-tint`, value label above, month label below). 160px tall. *Re-implement with the codebase's charting lib if available, or keep as CSS bars.*
  - Right: **"Payout history"** card — rows with a status icon (settled=sage check / pending=gold clock), amount + reference, date.

#### 17. Calendar & Availability — `v/calendar` (`VendorCalendar`)

- Head + "Block dates" ghost button. A **legend** (Confirmed booking = terra / Pencilled enquiry = gold-tint). Two month grids side-by-side (`.detail` `1fr 1fr`): each a `.cal` card with a 7-column day grid. Cells: booked (`B`) = `--terra` fill white + "Booked" tag; pencilled (`P`) = gold-tint + "Hold" tag; normal days bordered. Leading blanks pad to the correct weekday (`first` offset in data, week starts Monday).

#### 18. Vendor Messages — `v/messages/:thread?` (`VendorMessages` → `ConvView`)

- Same `ConvView` component as customer messages, fed vendor threads. Header sub shows the event instead of vendor role.

#### 19. Host Profile — `v/host` (`VendorHost`)

- Head + "View public page" ghost button. **Hero band** with a cover-photo placeholder (170px) and a body row: a large 72px avatar overlapping the cover (-54px margin, 4px white border), business name (Fraunces 26px), meta (Caterer / location / ★★★★★ 4.9 (48)), and an "Edit profile" primary button. Below: **analytics mini-stats** (Profile views ▲12% / Enquiry→booking % / Avg response / Repeat customers), then a "Services on your profile" section (`.sec-h` heading + "Manage" link) and a `.gal` grid of service cards.

---

## Interactions & Behavior

- **Navigation:** hash-based routing. Every card/row/stat that links uses an `<a href="#/route">`. On route change the app scrolls to top. Implement with the codebase's router; preserve the parent-tab highlighting for detail routes.
- **Role switching:** the top-bar segmented control swaps the entire shell (tabs + content) between customer and vendor. In production this is likely an account-level mode, not a free toggle — confirm with product.
- **Status-driven UI:** booking/request **action rows and explanatory copy change based on status** (accepted/pending/confirmed/declined for customer; new/quoted/confirmed/declined for vendor). This conditional logic is core to the design — preserve it. See `CustomerBooking`, `VendorRequest`.
- **Derived counts:** dashboard stats and KPIs are computed from the booking/request collections (`statusCounts`, `vCounts`). Recompute from real data.
- **Hover states:** list rows (`.lrow`, `.ev`, `.gcard`) raise a soft shadow + darken border on hover; table rows tint warm; `cc-row` tints warm. Transitions ~`.15s`.
- **Progress bars:** "suppliers booked X of N", budget allocation (stacked paid/due/unallocated), and service-listing completeness (X/4) are all width-percentage fills.
- **Responsive:** `.detail` and `.thread` collapse to single column below **1000px**; `.minis`, `.gal`, `.ed-thumb` drop to 2 columns. The vendor command-centre `cc-body` grid is not given a mobile breakpoint in the prototype — design a stacked mobile layout if mobile is in scope.
- **No animations** beyond hover transitions. No loading or error states are designed — add them per the codebase's conventions.

## State Management

State needed for a real build:

- **Auth/role:** current user, role (customer vs vendor), profile (name, initials, avatar).
- **Customer domain:** events (title, type, date, location, guests, budget), bookings (vendor, category, service, status, amount, deposit, depositPaid, linked event, linked thread), planning categories per event, message threads, payment summary (deposits/total derived).
- **Vendor domain:** incoming requests (customer, event, service, date, guests, value, status, priority), service listings (title, description, price, unit, photos, policy, completeness flags, bookings count), message threads, earnings series, payouts (date, amount, status, reference), calendar marks (booked/held days), analytics (views, conversion, response time, repeat %).
- **Status transitions:** customer booking `pending → accepted → confirmed` (or `declined`); vendor request `new → quoted → confirmed` (or `declined`). Deposit payment flips `depositPaid` and confirms.
- **Data fetching:** all of the above is currently static in `data.js` — replace with real API calls. Forms (new event, service editor, message composer, pay deposit) are non-functional and need real submission handling.

## Design Tokens

All defined as CSS custom properties on `.fye` in `brand.css`. Port to the target system.

**Surfaces**

| Token | Value |
|---|---|
| `--paper` | `#F6F1EB` (app background) |
| `--paper-2` | `#F1E9DD` (insets, tracks) |
| `--card` | `#FFFFFF` |
| `--card-warm` | `#FBF6EF` |
| (chrome bg) | `#FFFDFB` (top bar, tabs, thread list, rail) |

**Ink / lines**

| Token | Value |
|---|---|
| `--ink` | `#2A2320` |
| `--ink-2` | `#6E6258` |
| `--ink-3` | `#9C9085` |
| `--line` | `#ECE2D4` |
| `--line-2` | `#E3D7C5` |

**Brand accent — terracotta**

| Token | Value |
|---|---|
| `--terra` | `#B66A4D` |
| `--terra-deep` | `#9E573D` |
| `--terra-tint` | `#F4E5DC` |

**Harmonious accents** (equal L/C in OKLCH, hue varied — used for status & stat tones)

| Token | Value |
|---|---|
| `--sage` / `--sage-tint` | `oklch(0.60 0.085 150)` / `oklch(0.95 0.028 150)` |
| `--gold` / `--gold-tint` | `oklch(0.62 0.085 78)` / `oklch(0.95 0.030 78)` |
| `--slate` / `--slate-tint` | `oklch(0.58 0.075 250)` / `oklch(0.95 0.022 250)` |
| `--plum` / `--plum-tint` | `oklch(0.56 0.085 358)` / `oklch(0.95 0.024 358)` |

**Status → color mapping** (`.pill`): confirmed→sage · pending→gold · accepted→terra · declined→plum · action/new→slate.

**Typography**

| Token | Stack | Use |
|---|---|---|
| `--display` | `'Fraunces', Georgia, serif` | Headings, big numbers, titles |
| `--sans` | `'Manrope', system-ui, sans-serif` | Body / UI |
| `--mono` | `'Spline Sans Mono', ui-monospace, monospace` | KPIs, dates, metrics, placeholder captions |

Common sizes: page title 30px/600 Fraunces; section heads 18–19px Fraunces/600; body 13.5–14.5px Manrope; labels 11–12.5px (often 700–800 weight, uppercase with letter-spacing for eyebrows/labels). Big stat numbers 28–60px Fraunces/600 with negative tracking. (Fonts loaded via Google Fonts in the HTML head.)

**Radii & shadow**

| Token | Value |
|---|---|
| `--r-lg` | `18px` |
| `--r-md` | `12px` |
| `--r-sm` | `8px` |
| `--shadow-soft` | `0 1px 2px rgba(42,35,32,.04), 0 10px 30px rgba(42,35,32,.06)` |

**Buttons** (`.btn`): `.primary` = terra fill / white; `.ghost` = transparent / `--line-2` border; `.danger` = transparent / plum; size modifiers `.sm`, `.lg`, `.block`.

## Assets

- **Icons:** Font Awesome 6.5.1 (solid) via CDN. Each usage is a `fa-solid fa-*` class — see the JSX for exact icon names per context. Swap for the codebase's icon set, keeping semantic intent.
- **Fonts:** Fraunces, Manrope, Spline Sans Mono via Google Fonts (weights in the HTML `<link>`).
- **Imagery:** none real. Every photo is a striped `.ph` placeholder with a monospace caption naming what belongs there (cover photo, service photo, venue name, category). Replace with real images / upload components.
- **No logos or raster assets** are included — the "For Your Events" wordmark is pure CSS type.

> Note: this `/profile` experience is part of an existing product. If a brand system already exists in the target codebase, reconcile these tokens with it rather than introducing them blindly.

## Screenshots

Reference captures of every key screen live in `screenshots/` (1× device pixel, full prototype width):

| File | Screen |
|---|---|
| `01-customer-dashboard.png` | Customer Dashboard |
| `02-customer-event-detail.png` | Customer Event Detail |
| `03-customer-bookings.png` | Customer Bookings (grouped by status) |
| `04-customer-booking-detail.png` | Customer Booking Detail |
| `05-customer-payments.png` | Customer Payments |
| `06-customer-messages.png` | Customer Messages |
| `07-vendor-dashboard.png` | Vendor Dashboard (command centre) |
| `08-vendor-bookings.png` | Vendor Requests & Bookings |
| `09-vendor-request-detail.png` | Vendor Request Detail |
| `10-vendor-services.png` | Vendor My Services |
| `11-vendor-earnings.png` | Vendor Earnings & Payouts |
| `12-vendor-calendar.png` | Vendor Calendar & Availability |
| `13-vendor-host-profile.png` | Vendor Host Profile |

Screenshots show the above-the-fold state of each screen; open the HTML for full scroll and interaction.

## Files

All design-reference files are in this folder:

- `For Your Events - profile journey.html` — open this to view the full prototype.
- `brand.css`, `proto.css` — styles / design system.
- `data.js` — mock data.
- `shell.jsx`, `app.jsx`, `c-pages-1.jsx`, `c-pages-2.jsx`, `v-pages-1.jsx`, `v-pages-2.jsx` — router + screens.

To run the prototype as-is: serve this folder over HTTP and open the HTML file (the role switch is in the top-right of the top bar; default view is the customer dashboard).
