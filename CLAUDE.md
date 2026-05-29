# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

UK-based event services marketplace built on **CodeIgniter 4** (PHP). Customers plan events and receive automated quotes from vendors based on structured pricing rules. Vendors accept/decline bookings without manual quoting.

## Commands

### Dev Server

```bash
# First time only
cp -n env.example .env

# Start MariaDB (no password, database: event_marketplace)
sudo mkdir -p /run/mysqld && sudo chown mysql:mysql /run/mysqld
sudo mysqld_safe --skip-grant-tables &

# Start app (use cloud env to prevent Kint injecting into HTML)
CI_ENVIRONMENT=cloud php spark serve --host 127.0.0.1 --port 8888
```

### Tests

```bash
composer install        # required first; must be dev deps (Kint 5.1.1, PHPUnit)
php vendor/bin/phpunit --testdox

# Single test file
php vendor/bin/phpunit --testdox tests/unit/EventBookingQuoteTest.php
```

Tests use SQLite3 in-memory — MySQL does not need to run for unit tests.

### Linting / Syntax

```bash
# Fast syntax check (~5 seconds)
find app -name "*.php" -exec php -l {} \;

# Dry-run style check (slow on full app/ scan)
php vendor/bin/php-cs-fixer fix --dry-run --diff app/
```

### Scheduled Commands (Spark CLI)

```bash
php spark quote:remind-pending
php spark quote:expire-stale
```
These are in `app/Commands/`.

## Architecture

### Framework

CI4 is bundled in `system/` (not Composer-managed). The `createvent/` directory at the root is a legacy copy — ignore it.

### Database Setup Order

Run SQL files in this order on a fresh database:

1. `database_update.sql` — adds columns/tables (`ADD COLUMN IF NOT EXISTS` safe)
2. `event_marketplace.sql` — full schema + seed data (~26 tables)
3. `database_quote_automation.sql` — quote breakdown, auto-accept settings, counter-offers, analytics
4. `database_fulfillment_extras.sql`, `database_quantity_pricing.sql` — additional features
5. `categories_comprehensive.sql` — category seed data

### Key App Layers

- **Controllers** (`app/Controllers/`) — HTTP entry points; `BaseController` extends CI4's base
- **Models** (`app/Models/`) — ~30 models covering services, bookings, quotes, chat, payments, CMS
- **Libraries** (`app/Libraries/`) — business logic: `EventBookingQuote`, `EventQuoteBuilder`, `VendorQuoteAutomation`, `QuoteNotifier`, `StripeCheckoutHelper`, `UKAddressGeocoder`, etc.
- **Views** (`app/Views/`) — PHP templates; service creation uses three separate views: `service_create_public.php`, `service_create_private.php`, `service_create_corporate.php`
- **Commands** (`app/Commands/`) — Spark CLI commands for quote lifecycle
- **Config** (`app/Config/`) — `Routes.php`, `Branding.php`, `SiteNav.php` are the most project-specific configs

### Core Domain Concepts

- **Event** — a customer's planned occasion (wedding, birthday, etc.) with guest count, date, location
- **Service** — a vendor listing with one pricing model: guest-based, duration-based, or tiered packages
- **Quote** — auto-generated price for a service against an event; stored in `booking_items` with `quote_breakdown` JSON
- **Booking** — confirmed quote; deposit is **15%** (legacy cart used 10%)
- **Basket/Cart** — `EventBasketItemModel` / `CartModel` — pre-booking stage

### Pricing Models

Services choose exactly one: guest-based ranges (`ServiceGuestBasedPricingModel`), custom duration blocks (`ServiceCustomDurationPricingModel`), quantity pricing (`ServiceQuantityPricingModel`), or tiered packages (`ServiceTieredPackagesPricingModel`). Optional extras are always additive.

### Payments

Stripe is optional — set `STRIPE_SECRET_KEY`, `STRIPE_PUBLISHABLE_KEY`, `STRIPE_WEBHOOK_SECRET` in `.env`. The app runs fully without them for browsing, registration, login, and service creation. `WebhookController` handles Stripe events.

### Coverage / Location

`UKAddressGeocoder` + `ServiceLocationModel` — vendors set a base location and radius. `ServiceAvailabilityChecker` validates coverage before a quote is generated.

## Key Caveats

- If `Class "Kint\Zval\InstanceValue" not found`: remove `vendor/` and run `composer install` (Kint must be exactly **5.1.1**)
- `baseURL` in `app/Config/App.php` defaults to `http://partyplanner.test/`; override in `.env` for cloud/agent environments
- `php-cs-fixer` on `app/` can hang for several minutes — prefer the `php -l` syntax check for quick validation
