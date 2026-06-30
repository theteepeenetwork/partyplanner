# Partysmith — Website Overview

**Partysmith** is a **UK event-services marketplace** built on CodeIgniter 4
(PHP 8.3+, MariaDB, Bootstrap, vanilla JS). Tagline: _"The UK event marketplace ·
expertly made."_ Customers create an event, browse vetted suppliers, and receive
**automated, structured quotes** instantly — rather than the usual back-and-forth
enquiry model. Vendors define their pricing rules once and the platform
auto-calculates quotes and can even auto-accept bookings.

---

## Who it's for — three roles

| Role | What they do |
| --- | --- |
| **Customer** | Plan an event, discover suppliers, receive instant quotes, book with deposit protection. |
| **Vendor** | List services, set structured pricing & coverage, receive and auto-handle booking requests. |
| **Admin** | Vet vendors and moderate services, bookings, reviews, and messages via the `/admin` back office. |

---

## Core journeys

1. **Customer browse → book:** homepage hero search (occasion · category ·
   location · date) → filtered browse → service detail (gallery, pricing,
   reviews) → add to event basket → checkout with a **15% deposit** (legacy cart
   uses 10%) via Stripe.
2. **Vendor onboarding:** register → get vetted → a **6-step service-creation
   wizard** (basics → location/fulfilment → pricing → availability → gallery →
   review & publish) → manage from a vendor dashboard (bookings, calendar,
   earnings, quote settings).
3. **Messaging & reviews:** booking-gated vendor↔customer chat; customers review
   after the event; ratings surface on service pages.

---

## The standout feature — quote automation

This is the product's USP. A pricing pipeline (`EventQuoteBuilder` →
`EventBookingQuote`) computes a line-item quote from guest count, duration,
packages, quantity, optional extras, and travel/coverage rules. Then
**`VendorQuoteAutomation`** applies each vendor's rules (auto-accept toggle, max
amount cap, travel-radius, min lead days, blackout dates, allowed event settings)
to accept or decline automatically — with an audit log
(`quote_automation_log`).

Scheduled commands manage the quote lifecycle:

```bash
php spark quote:remind-pending   # remind vendors about unactioned quotes
php spark quote:expire-stale     # expire old unactioned quotes
```

---

## Pricing — six independent models

Every service uses **exactly one** pricing model. Existing models are extended
before new ones are created.

| Model | DB Table | How it works |
| --- | --- | --- |
| **Guest-based** | `services_guest_based_pricing` | Price tiers by fixed guest-count ranges (e.g. 1–50, 51–100). |
| **Duration / time-block** | `services_custom_duration_pricing` | Hourly, daily, or block rates. |
| **Tiered packages** | `services_tiered_packages_pricing` | Named packages (Standard / Premium / Deluxe) with set inclusions. |
| **Quantity / unit** | `services_quantity_pricing` | Per-unit pricing with min/max quantities. |
| **Public event** | `services_public_event_pricing` | Commission %, attendance range, max pitch fee. |
| **Private event** | `services_private_event_pricing` | Parent row linking to a guest/duration/package/quantity tier. |

Plus **optional extras** (`services_optional_extras`, flat or per-item),
capacity limits, setup/breakdown time, and per-km travel fees. Public and
private pricing are intentionally separate systems.

---

## Data model (highlights)

| Concern | Key tables |
| --- | --- |
| Accounts & access | `users` (`customer` / `vendor` / `admin`) |
| Events | `events`, `event_types` |
| Catalogue | `services`, `service_images`, `services_locations`, `services_tags`, `categories` |
| Pricing | the six pricing tables above + `services_optional_extras` |
| Booking & orders | `bookings`, `booking_items` (pricing stored as `quote_breakdown` JSON), `event_basket_items`, legacy `carts` |
| Quote automation | `vendor_quotes`, `vendor_quote_settings`, `quote_automation_log` |
| Payments | `payments`, `payment_schedules` |
| Availability & coverage | `service_availability`, `unavailable_dates`, `services_locations` |
| Messaging & reputation | `chat_rooms`, `chat_messages`, `reviews`, `vendor_message_templates` |
| Platform | `favourites`, `cms_pages` |

---

## Architecture & access control

- **MVC with thin controllers**; business logic lives in `app/Libraries/`
  (e.g. `EventQuoteBuilder`, `EventBookingQuote`, `VendorQuoteAutomation`,
  `ServiceAvailabilityChecker`, `StripeCheckoutHelper`, `QuoteNotifier`).
- **Role enforcement** via the `AdminAuth` filter on `/admin/*` plus controller
  guards (`requireCustomerAccount()`, `requireVendor()`, `requireCustomer()`)
  that re-check the database role on each request — so role promotions take
  effect live without a session restart.
- **Stripe is optional** — the entire site (browsing, registration, service
  creation) functions without keys; payment actions are simply disabled.
- **Vendor coverage** (base location, free/paid radius, per-km travel fee) feeds
  search results and quote eligibility.

---

## Brand

- **Palette:** deep forest-green `#1C4A36` + forge-gold `#E2B860` on warm ivory
  `#F6F3EC`.
- **Logo:** the **"P.S." monogram** tile (green ground, cream letters, gold
  dots).
- **Typography:** **Hanken Grotesk** for body/UI, **Newsreader** serif for
  headings, **Caveat** as the script accent (founder's note).
- **Feel:** rounded cards (22px), pill buttons, soft shadows — premium and
  trustworthy.

> **Note:** the launch brief (§5) specifies **Mr Dafoe** as the script accent,
> whereas the live brand currently uses **Caveat**. This discrepancy is
> unresolved.

---

## Public route map (overview)

| Area | Examples | Access |
| --- | --- | --- |
| Public | `/`, `/browse-services`, `/service/view/:id`, `/about`, `/how-it-works`, `/faq` | None |
| Auth | `/register`, `/register/vendor`, `/login`, `/forgot-password` | None |
| Customer | `/event/create`, `/event/basket/:id`, `/event/checkout/:id`, `/profile/my-bookings` | Customer |
| Vendor | `/service/create`, `/profile/bookings`, `/profile/calendar`, `/profile/earnings`, `/profile/quote-settings` | Vendor |
| Admin | `/admin`, `/admin/vendors`, `/admin/services`, `/admin/bookings`, `/admin/reviews`, `/admin/messages` | Admin |
| Webhooks / payments | `/webhook/stripe`, `/payment/createPaymentIntent` | Stripe signature |
