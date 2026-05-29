# API Reference — Library Classes

This document covers the five core Library classes in `app/Libraries/`. For the broader architecture context see [ARCHITECTURE.md](ARCHITECTURE.md).

---

## EventQuoteBuilder

**File:** `app/Libraries/EventQuoteBuilder.php`

Loads all pricing data for a service and delegates to `EventBookingQuote::calculate()` to produce an itemised quote for a service + event pair.

**Constructor:** No dependencies — models and helpers are instantiated internally.

### Public Methods

#### `build(array $service, array $event, ?string $pricingOption, array $selectedExtraIds, array $extraQuantitiesById, ?int $orderQuantity): array`

Fetches pricing tiers, optional extras, location data, and availability for the given service, then returns a quote array with the shape `{lines, total, warnings, errors, distance_km}`.

- `$service` — service row from the DB (must contain `id` and `vendor_id`)
- `$event` — event row (must contain `date` and location fields used for distance calculation)
- `$pricingOption` — selected package/option key (null for guest-based pricing)
- `$selectedExtraIds` — IDs of optional extras the customer chose
- `$extraQuantitiesById` — map of extra ID → quantity for per-item extras
- `$orderQuantity` — quantity for quantity-based pricing; sets `pricingOption` to `qty_N` automatically

Returns `array{lines: list<array{code,label,amount}>, total: float, warnings: list<string>, errors: list<string>, distance_km: ?float}`.

#### `loadCorporatePricing(int $serviceId): ?array`

Fetches the JSON pricing details from `services_corporate_event_pricing` for the given service and returns them as a decoded array, or `null` if none exist.

#### `mergeServiceLocation(array $service, ?array $locationRow): array`

Merges a `service_locations` row onto the service row, falling back to service-level fields for any location key that is missing or empty in the location row.

#### `loadService(int $serviceId): ?array`

Convenience wrapper around `ServiceModel::find()` — returns the service row or `null`.

---

## VendorQuoteAutomation

**File:** `app/Libraries/VendorQuoteAutomation.php`

Evaluates a vendor's auto-accept rules against a completed quote and, if all rules pass, marks the booking item as accepted.

**Constructor:** No dependencies — models are instantiated internally.

### Public Methods

#### `evaluateAfterCheckout(array $bookingItem, array $quote, int $vendorId, int $serviceId): array`

Checks the vendor's `VendorQuoteSettings` record in sequence: auto-accept enabled, no quote errors, amount cap, travel radius warnings, allowed event settings, minimum lead days, and blackout dates. On success it updates `booking_items.status = 'accepted'` and inserts a `QuoteAutomationLog` row.

Returns `array{auto_accepted: bool, reason: string}` where `reason` is one of: `auto_accept_disabled`, `quote_errors`, `over_max_amount`, `travel_warning`, `event_setting_not_allowed`, `insufficient_lead_time`, `unavailable`, `rules_matched`.

---

## StripeCheckoutHelper

**File:** `app/Libraries/StripeCheckoutHelper.php`

Thin wrapper around the Stripe PHP SDK for creating and verifying PaymentIntents in GBP.

**Constructor:** No dependencies.

### Public Methods

#### `isConfigured(): bool`

Returns `true` if the `STRIPE_SECRET_KEY` environment variable is set and non-empty.

#### `createPaymentIntent(int $amountPence, array $metadata): array`

Creates a Stripe PaymentIntent for the given amount (in pence, minimum 50p) with `currency=gbp` and `payment_method_types=['card']`.

Returns `array{success: bool, payment_intent_id?: string, client_secret?: string, error?: string}`.

#### `verifyPaymentIntent(string $paymentIntentId): array`

Retrieves a PaymentIntent from Stripe and checks whether its status is `succeeded` or `processing`.

Returns `array{success: bool, status?: string, error?: string}`.

---

## ServiceAvailabilityChecker

**File:** `app/Libraries/ServiceAvailabilityChecker.php`

Checks whether a service and vendor are free on a given date by consulting the unavailable-dates table and looking for conflicting accepted bookings.

**Constructor:** No dependencies — models are instantiated internally.

### Public Methods

#### `check(int $serviceId, int $vendorId, ?string $eventDate): list<string>`

Looks up `UnavailableDateModel` for a vendor blackout on `$eventDate`, then queries `BookingItemModel` for any non-rejected/non-cancelled booking for the same service on that date.

Returns a list of human-readable error strings; an empty list means the service is available. A `null` or empty `$eventDate` returns an empty list without hitting the database.

---

## QuoteNotifier

**File:** `app/Libraries/QuoteNotifier.php`

Sends quote-related notifications — both in-app chat messages and emails — to vendors and customers when a quote is submitted.

**Constructor:** No dependencies — models and CI4 email service are instantiated internally.

### Public Methods

#### `sendVendorNewQuoteNotification(int $vendorId, int $customerId, int $serviceId, array $bookingItem, array $breakdown): void`

Posts a formatted quote breakdown as a system chat message in the vendor/customer/service chat room and sends the same content by email to the vendor.

#### `sendCustomerQuoteConfirmed(int $customerId, int $vendorId, int $serviceId, array $breakdown): void`

Posts a "quote submitted" chat message to the shared room and emails the customer confirming their booking request was received.
