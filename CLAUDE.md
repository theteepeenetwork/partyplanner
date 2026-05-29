# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What This Is

A UK-based event services marketplace built on **PHP CodeIgniter 4**. Customers create events, get automated quotes from vendors, and pay via Stripe. Vendors define structured pricing rules once; quotes are generated automatically.

## Starting the Dev Environment

```bash
# 1. MariaDB (run once per session)
sudo mkdir -p /run/mysqld && sudo chown mysql:mysql /run/mysqld
sudo mysqld_safe --skip-grant-tables &

# 2. Dev server (use 'cloud' env to suppress Kint toolbar injecting into HTML)
cp -n env.example .env   # first time only
CI_ENVIRONMENT=cloud php spark serve --host 127.0.0.1 --port 8888
```

Browse at `http://127.0.0.1:8888/`. The `app/Config/App.php` `baseURL` is set to `http://partyplanner.test/` — update it if needed for your environment.

## Database Setup

The correct import order matters:

1. `database_update.sql` — adds columns/tables with `IF NOT EXISTS` guards, safe on fresh DB
2. `event_marketplace.sql` — full schema + seed data (~26 tables)
3. `database_quote_automation.sql` — quote breakdown column, auto-accept settings, counter-offers, analytics
4. `database_fulfillment_extras.sql` / `database_quantity_pricing.sql` — additional pricing features

Database: `event_marketplace` (MariaDB/MySQL). Tests use SQLite3 in-memory — MySQL is not needed for running tests.

## Commands

```bash
# Install dependencies (always with dev packages)
composer install

# Run tests
php vendor/bin/phpunit --testdox

# Run a single test file
php vendor/bin/phpunit tests/unit/SomeTest.php

# Syntax check (fast, ~5s)
find app -name "*.php" -exec php -l {} \;

# Lint dry-run (slow, may take several minutes)
php vendor/bin/php-cs-fixer fix --dry-run --diff app/

# Scheduled CLI commands
php spark quote:remind-pending
php spark quote:expire-stale
```

## Architecture

**Framework**: CI4 is bundled in `system/` — not installed via Composer. `createvent/` at the root is a legacy copy of the app; ignore it.

**Key directories**:
- `app/Controllers/` — main controllers; `app/Controllers/Admin/` — admin panel
- `app/Models/` — one model per DB table, ~30 models covering services, bookings, pricing, chat, payments
- `app/Views/` — PHP view files; `app/Views/components/` and `app/Views/partials/` for shared UI
- `app/Libraries/` — business logic classes (quote building, availability checking, Stripe, geocoding, analytics)
- `app/Commands/` — Spark CLI commands for scheduled quote tasks
- `app/Config/Routes.php` — all route definitions

**Pricing models** (each service picks one): guest-based, duration/time-block, tiered packages, quantity-based, public event pricing, or private pricing. Each has its own Model and DB table (e.g. `ServiceGuestBasedPricingModel`, `ServiceQuantityPricingModel`).

**Quote flow**: `EventQuoteBuilder` (Library) assembles quotes from vendor pricing rules → `VendorQuoteAutomation` handles auto-accept/decline logic → `QuoteNotifier` sends emails → `QuoteAnalyticsRecorder` logs outcomes. Booking items store a `quote_breakdown` JSON column.

**Payment**: Event checkout uses a **15% deposit**; the legacy cart uses 10%. Stripe is optional — the app works without Stripe keys for browsing, registration, and service creation.

**Service creation**: Three separate view files are imported based on context: `service_create_public.php`, `service_create_private.php`, `service_create_corporate.php`. This separation is intentional.

**Auth**: Session-based. Roles: customer and vendor (set at registration). Admin routes are under `/admin/`.

## Caveats

- Kint must be version **5.1.1** (not 6.x). If you see `Class "Kint\Zval\InstanceValue" not found`, delete `vendor/` and re-run `composer install`.
- Stripe env vars: `STRIPE_SECRET_KEY`, `STRIPE_PUBLISHABLE_KEY`, `STRIPE_WEBHOOK_SECRET`.
- `php-cs-fixer` on the full `app/` directory can hang for several minutes — prefer the `php -l` syntax check for quick validation.
