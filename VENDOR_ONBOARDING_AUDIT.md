# Vendor Onboarding Audit

A code- and schema-grounded audit of whether the marketplace's vendor
onboarding can represent the full range of UK event suppliers. Every claim
below was verified against the live schema and the controllers/views/models in
this repository.

---

## Executive summary

The platform has a **strong taxonomy** and a **solid set of structured pricing
models**, but vendor onboarding has three systemic gaps that block or weaken
support for many supplier types:

1. **No way to declare operational requirements / logistics / capacity.** A
   service cannot state that it needs power, water, vehicle access, is
   indoor/outdoor only, brings its own equipment, needs setup/breakdown time,
   or has a minimum/maximum headcount. This blocks fairground rides,
   inflatables, food trucks, mobile bars, LED dance floors, marquees, AV/
   production and security/staffing from representing themselves accurately.
2. **No "request a quote" / bespoke pricing path.** Every service must commit
   to one structured pricing model. Suppliers who genuinely cannot give instant
   pricing (production companies, luxury planners, AV/staging, bespoke security)
   have no honest option.
3. **Availability is unmanageable.** The `service_availability` and
   `unavailable_dates` tables exist but have **no reachable UI** — the
   `addAvailability()` controller method is not routed, and the vendor calendar
   is read-only. Vendors cannot publish available/blackout dates, setup/
   breakdown windows, or lead-time.

Customer discovery is also thin: there is **no price, capacity, location,
availability or event-type filter** — only category + keyword + sort.

None of these require per-supplier custom code to fix; they are
schema + form + filter gaps.

---

## Phase 1 — Supplier support matrix

### Fully supported today

Suppliers that fit a single structured pricing model, attend in person on a
single day, and have no special site requirements:

- DJs, bands, musicians (fixed/package or duration)
- Photographers, videographers (package or duration)
- Magicians, children's entertainers, face painters, balloon artists (fixed/package)
- Celebrants (fixed/package)
- Photo booths (duration)
- Cake makers / favours / stationery (quantity, postal supported)

### Partially supported

Work via an existing model but cannot express critical site needs:

| Supplier | Works via | Missing |
| --- | --- | --- |
| Caterers | guest-based ✅ | dietary/min-spend, power/water, staff count |
| Food trucks / ice-cream vans | quantity or fixed | **power, vehicle access, pitch space, setup time** |
| Mobile bars | package/fixed | **power, water, licensing, space** |
| LED dance floor / lighting | package/quantity | **power load, setup/breakdown, indoor/outdoor** |
| Marquee companies | tiered packages ✅ | **site access, ground type, setup days, capacity** |
| Furniture / chair-cover hire | quantity ✅ | delivery/collection windows, min order |
| Florists / stylists | package/quantity | setup time, venue access |
| Transport providers | fixed/duration | vehicle type, distance, capacity |
| Festival operators | public-event pitch ✅ | **multi-day, power, water, vehicle access** |

### Poorly supported (and why)

| Supplier | Why it fails today |
| --- | --- |
| **Conference / AV / production companies** | Bespoke scoped jobs — **no custom-quote path**; structured models force a fake price. No setup/derig/crew/power fields. |
| **Workshop / team-building hosts** | No **per-session rate** as a first-class concept (duration approximates); no **min/max participants** capacity; no requirements (space, AV). |
| **Security companies / event staff** | No **per-staff × hours** model; no SIA-licence/compliance field (only corporate DBS/PLI); no min-staffing. |
| **Fairground rides / inflatables** | No **power, footprint/space, vehicle access, setup/breakdown, safety-cert** fields — these are make-or-break and simply cannot be entered. |
| **Luxury wedding planners** | Pricing is typically % of budget or bespoke — **no custom-quote**; planners can't list without a fake fixed price. |
| **Multi-day suppliers (exhibitions, festivals)** | No **multi-day pricing**; quote/booking assume a single `event.date`. |

---

## Phase 2 — Pricing model review

**Present (6 structured models + travel):**

| Need | Model | Status |
| --- | --- | --- |
| Fixed fee | `services.price` (fallback only) | ⚠️ not a first-class model |
| Guest-based | `guest_based_pricing` (per-head, tiered bands) | ✅ |
| Tiered (by guest band) | same | ✅ |
| Duration / hourly / day rate | `custom_duration_pricing` (`duration_type` hour/day) | ✅ |
| Package (Bronze/Silver/Gold) | `tiered_packages_pricing` | ✅ |
| Quantity (per chair, per item) | `quantity_based_pricing` | ✅ |
| Distance / travel | `services_locations` (`free`/`paid_coverage_radius`, `travel_fee_per_km`) | ✅ |
| Public pitch / attendance | `public_event_pricing` (`attendance_thresholds`, `max_pitch_fees`) | ✅ |
| Corporate modifiers | `services_corporate_event_pricing` (JSON: VAT, PO, surcharge, compliance) | ✅ |

**Absent:**

- **Custom quote / POA / enquiry-only** — highest-impact gap; unblocks
  production, planners, AV, bespoke security. *(No `pricing_type` literal, no
  table, no UI.)*
- **Session rate** — only approximated by duration "hours".
- **Seasonal (peak/off-peak)** pricing.
- **Venue-based** pricing (different price per venue).
- **True multi-day** pricing (festivals/exhibitions; engine assumes one date).
- **Hybrid** models (e.g. guest-based + per-hour premium).

> Constraint to respect: `CLAUDE.md` states *"Every service uses exactly one
> pricing model"* and *"Existing pricing models should be extended before
> creating new ones."* New models should be added as additional first-class
> `pricing_type` values, not bolted onto existing ones.

---

## Phase 3 — Service configuration review

| Capability | Status | Evidence |
| --- | --- | --- |
| Coverage radius + travel fee | ✅ | `services_locations`: `free/paid_coverage_radius`, `travel_fee_per_km` |
| Nationwide | ✅ (approx) | `no_travel_limit` flag |
| **Counties / regions** | ❌ | radius/lat-long only |
| Fulfilment (in-person/postal/both) | ✅ | `fulfillment_type` |
| **Available / unavailable dates** | ❌ (no UI) | tables exist; `addAvailability()` **unrouted**; calendar read-only; `unavailable_dates` referenced in **no** controller/view |
| **Setup / breakdown time** | ❌ | not captured anywhere |
| **Min / max capacity (guests)** | ❌ (implicit only) | only via pricing-tier bands |
| Event types (broad) | ⚠️ | only `public`/`private`/`corporate` buckets, hardcoded in `service_create_step2.php` |
| **Specific event types** (wedding/birthday/festival/school/charity) | ❌ | exist only on `events.event_type`, not selectable on services |
| **Requirements** (power/water/vehicle access/indoor-outdoor/own equipment) | ❌ | only *corporate* compliance (PLI/DBS/PAT/risk/method) exists |
| **Logistics** (setup/breakdown duration, min notice) | ❌ | none |
| Optional extras | ✅ | `services_optional_extras` (flat/per_item, min/max, unit_label) |
| Cancellation policy | ⚠️ | free-text only; no structured refund tiers |

---

## Phase 4 — Category & taxonomy review

**Strong and already scalable.**

- Single data-driven adjacency tree: `categories(id, parent_id, name)` —
  **23 top-level + ~1,400 child rows across 3 levels**; services reference
  `category_id`, `subcategory_id`, `third_category_id`.
- No hardcoded category arrays in controllers/views; `CategoryModel` provides
  `getRootCategories()`, `getSelfAndDescendantIds()`, `validateAssignment()`.
- New supplier types are added by inserting rows — **no code change needed**.

**Minor issues:**

- Legacy `subcategories` table is created but **unused** (all logic uses the
  `categories` tree) — remove or stop shipping it to avoid confusion.
- Tags are **dual-stored**: free-text `services.service_tags` *and* a
  `services_service_tags` junction — but **search only reads the text column**;
  the junction table is effectively dead. No tag facets, no autocomplete.
- `categories` has no `slug` / `sort_order` / `is_active` / `description`.

---

## Phase 5 — Customer discovery review

`Service_Controller::browse()` accepts: `category`, `subcategory`,
`third_category`, `q` (title/description/tags/category-name LIKE), `sort`
(newest/price/title), and an event-scoped travel-radius filter when `event_id`
is present.

**Absent filters (all high-value):**

- ❌ **Price / budget range**
- ❌ **Capacity / guest count**
- ❌ **Location / postcode** (only post-filter when an event is selected)
- ❌ **Availability / date**
- ❌ **Event type** (can't filter by public/private/corporate or specific type)
- ❌ **Requirements / attributes** (e.g. "includes travel", "outdoor", "powered")
- ❌ **Tag facets**
- ❌ Ratings / trust signals (not implemented at all)

---

## Recommended implementation roadmap (prioritised)

Ordered by *impact ÷ risk*. Each is additive and backwards-compatible.

| # | Improvement | Unblocks | Risk |
| --- | --- | --- | --- |
| **1** | **Service requirements, capacity & logistics fields** (power/water/vehicle-access/indoor-outdoor/own-equipment, min/max capacity, setup/breakdown minutes, min notice days) | fairground, inflatables, food trucks, bars, dance floors, marquees, AV, security, workshops | **Low** (additive columns + form + display) |
| 2 | **Custom-quote / enquiry-only pricing mode** | production, planners, AV, bespoke security | Medium (touches quote/booking) |
| 3 | **Discovery filters**: price range, capacity, event-type | all customers | Low–Med |
| 4 | **Specific event-type taxonomy** (data-driven; vendor-selectable + customer-filterable) | weddings/festivals/school/charity targeting | Medium |
| 5 | **Availability management UI** (wire up `unavailable_dates`, setup/breakdown, lead-time) | every date-sensitive supplier | Medium |
| 6 | Seasonal / venue / multi-day pricing models | festivals, exhibitions, seasonal trades | Higher (engine + quote changes) |
| 7 | Tag facets + region/county coverage + structured cancellation tiers | refinement | Low–Med |

**Item 1 is implemented in this change set** (see the implementation summary at
the end / commit). Items 2–7 are recommended follow-ups, each suitable for its
own focused PR.
