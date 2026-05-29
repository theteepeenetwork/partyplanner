# Architecture

> For dev environment setup, database import order, and commands, see [CLAUDE.md](../CLAUDE.md).

## System Overview

Party Planner is a UK-based event services marketplace built on PHP CodeIgniter 4. Customers create events and receive automated quotes from vendors; vendors define pricing rules once and the platform handles the rest. See [README.md](../README.md) for a user-facing description.

## Component Map

```
HTTP Request
    │
    ▼
Controllers (app/Controllers/)
    │  Validate input, call Libraries/Models, render Views
    │
    ├──▶ Libraries (app/Libraries/)
    │        Business logic: quote building, availability, Stripe, notifications
    │        These classes are instantiated directly (not via CI4 DI container)
    │
    ├──▶ Models (app/Models/)
    │        One model per DB table; thin wrappers around CI4 Model base class
    │
    └──▶ Views (app/Views/)
             PHP view files; shared pieces live in components/ and partials/
```

Admin controllers live under `app/Controllers/Admin/` and are protected by an admin-only filter.

Route definitions are centralised in `app/Config/Routes.php`.

## Quote Pipeline

```
EventQuoteBuilder::build()
    │  Loads all pricing data for the service, merges location info,
    │  calls ServiceAvailabilityChecker, then delegates to EventBookingQuote::calculate()
    │  Returns: {lines, total, warnings, errors, distance_km}
    ▼
VendorQuoteAutomation::evaluateAfterCheckout()
    │  Reads vendor auto-accept rules (VendorQuoteSettingsModel)
    │  Checks amount cap, travel radius, event setting, lead time, blackout dates
    │  If all rules pass: marks booking_item status = 'accepted', writes automation log
    ▼
QuoteNotifier
    │  sendVendorNewQuoteNotification() — chat message + email to vendor
    │  sendCustomerQuoteConfirmed()     — chat message + email to customer
    ▼
QuoteAnalyticsRecorder
    │  Logs quote outcome for analytics/reporting
```

Booking items store the full breakdown as a `quote_breakdown` JSON column so the itemised quote is preserved at booking time.

## Pricing Model Matrix

| Pricing Model | DB Table | Model Class |
|---|---|---|
| Guest-based | `service_guest_based_pricing` | `ServiceGuestBasedPricingModel` |
| Custom duration / time-block | `service_custom_duration_pricing` | `ServiceCustomDurationPricingModel` |
| Tiered packages | `service_tiered_packages_pricing` | `ServiceTieredPackagesPricingModel` |
| Quantity-based | `service_quantity_pricing` | `ServiceQuantityPricingModel` |
| Public event pricing | `service_public_event_pricing` | `ServicePublicEventPricingModel` |
| Private pricing (wrapper) | `service_private_pricing` | `ServicePrivatePricingModel` |

Each service selects exactly one pricing model. The private pricing row acts as a parent record linking to guest-based, duration, package, or quantity tiers. Corporate event pricing is stored as a JSON blob in `services_corporate_event_pricing`.

## Payment Flow

| Checkout path | Deposit | Notes |
|---|---|---|
| Event checkout (primary) | **15%** of quote total | Uses `StripeCheckoutHelper` |
| Legacy cart | **10%** of cart total | Older flow, still supported |

Stripe is optional — the app functions without Stripe keys for browsing, registration, and service creation. Configure via environment variables `STRIPE_SECRET_KEY`, `STRIPE_PUBLISHABLE_KEY`, and `STRIPE_WEBHOOK_SECRET`.

## Auth Model

Authentication is session-based (CI4 `session` service). Two roles are assigned at registration:

- **customer** — can create events, browse services, submit quote requests, and pay
- **vendor** — can create and manage services, configure pricing, accept/reject quotes

Admin functionality lives under the `/admin/` route prefix and is protected by a separate filter that checks for an admin flag on the user record.

## CLI Commands (Spark)

| Command | Purpose |
|---|---|
| `php spark quote:remind-pending` | Sends reminder notifications for quotes awaiting vendor response |
| `php spark quote:expire-stale` | Marks old unactioned quotes as expired |

Source files live in `app/Commands/`. These are intended to be run on a cron schedule.
