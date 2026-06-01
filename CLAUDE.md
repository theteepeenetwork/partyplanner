# CLAUDE.md

## Tech Stack

* **PHP 8.3+** (local runtime is 8.4)
* **CodeIgniter 4** — MVC framework; `php spark` is the CLI
* **MariaDB / MySQL** — database name `event_marketplace`
* **Bootstrap** — CSS framework for all views
* **Vanilla JavaScript** — no frontend build step; no npm
* **Stripe** — optional payment integration (`stripe/stripe-php ^15.8`)

## Database

**Name:** `event_marketplace`

**First-time setup** — import in this exact order (later files `ALTER` tables created by earlier ones):

```bash
mysql --default-character-set=utf8mb4 event_marketplace < event_marketplace.sql
mysql --default-character-set=utf8mb4 event_marketplace < database_update.sql
mysql --default-character-set=utf8mb4 event_marketplace < database_quote_automation.sql
mysql --default-character-set=utf8mb4 event_marketplace < database_fulfillment_extras.sql  # optional
mysql --default-character-set=utf8mb4 event_marketplace < database_quantity_pricing.sql    # optional
mysql --default-character-set=utf8mb4 event_marketplace < database_service_requirements.sql
```

**Key tables:**

| Table | Purpose |
|---|---|
| `users` | All accounts; `role` enum: `customer`, `vendor`, `admin` |
| `services` | Vendor service listings |
| `events` | Customer events (guests, date, location) |
| `bookings` | Confirmed bookings |
| `booking_items` | Line items within a booking; `quote_breakdown` JSON holds pricing detail |
| `event_basket_items` | Items staged for checkout (one active basket per event) |
| `payments` | Payment records |
| `vendor_quotes` | Auto-generated quotes sent to customers |
| `vendor_quote_settings` | Per-vendor quote automation config |
| `services_guest_based_pricing` | Guest-count pricing tiers |
| `services_custom_duration_pricing` | Duration-based pricing tiers |
| `services_tiered_packages_pricing` | Package-based pricing |
| `services_quantity_pricing` | Quantity/unit pricing |
| `services_private_event_pricing` | Private event pricing parent rows |
| `services_optional_extras` | Add-on extras for services |
| `services_locations` | Vendor coverage areas (lat/lng, radius, travel fees) |
| `chat_rooms` / `chat_messages` | Vendor–customer messaging |

**Credentials** (`.env` defaults match):

```
database.app.hostname = 127.0.0.1
database.app.database = event_marketplace
database.app.username = root
database.app.password =
```

## Test Accounts & Seed Data

All passwords: `password`

| Role | Email |
|---|---|
| Customer | `customer1@c.com` |
| Customer | `customer2@c.com` |
| Vendor | `vendor1@v.com` |
| Vendor | `vendor2@v.com` |

Run the QA seeder before browser testing (idempotent — safe to re-run):

```bash
php spark db:seed QASeeder
```

See `QA_SEED.md` for expected quote outputs per service/event combination.

## Linting & Code Style

```bash
# Syntax check all PHP files (fast)
find app -name "*.php" -exec php -l {} \;

# PHP CS Fixer (uses codeigniter/coding-standard)
./vendor/bin/php-cs-fixer fix app/
./vendor/bin/php-cs-fixer fix --dry-run --diff app/   # check only
```

## Project Scripts

```bash
scripts/bootstrap.sh   # cp env.example .env, composer install, spark migrate
scripts/safe-git.sh    # git operations wrapper
```

## Workflow

* ALWAYS run `php -l` on modified PHP files before claiming work is complete.
* ALWAYS test affected pages in the browser after making changes.
* Do not mark tasks complete based solely on code inspection.
* Prefer fixing issues immediately rather than creating TODOs.
* Never modify unrelated files.

## Development Commands

```bash
# Start development server
CI_ENVIRONMENT=cloud php spark serve --host 127.0.0.1 --port 8888

# Fast validation
find app -name "*.php" -exec php -l {} \;

# Run all tests
php vendor/bin/phpunit --testdox

# Run a single test file
php vendor/bin/phpunit tests/unit/SomeTest.php

# Scheduled commands
php spark quote:remind-pending
php spark quote:expire-stale
```

## Architecture

* Controllers should remain thin.
* Business logic belongs in `app/Libraries/`.
* Do not duplicate pricing calculations across controllers.
* Use existing pricing models before creating new pricing systems.
* Follow existing patterns before introducing new architecture.
* Admin functionality belongs under `/admin/`.

## Pricing System

* Every service uses exactly one pricing model.
* Existing pricing models should be extended before creating new ones.
* Booking items store pricing details in `quote_breakdown` JSON.
* Public and private pricing are intentionally separate systems.

## Service Creation

* `service_create_public.php`
* `service_create_private.php`
* `service_create_corporate.php`

These files are intentionally separate. Do not merge them unless explicitly instructed.

## UI Rules

* This is a marketplace, not an event planning agency.
* Users book services, not consultations.
* Vendor and customer journeys must remain distinct.
* Premium appearance must not reduce usability.
* Reuse existing UI patterns before creating new ones.

## Testing Rules

* When a route returns 404, determine whether the page should exist before reporting a bug.
* If a missing page is clearly intended by the application flow, create the page rather than logging an issue.
* Verify browser console errors after significant UI changes.

## Gotchas

* Event checkout uses a 15% deposit.
* Legacy cart uses a 10% deposit.
* Stripe is optional and the application must function without Stripe keys.
* `CI_ENVIRONMENT=cloud` disables the debug toolbar.
* Kint must remain on version 5.1.1.
* Vendor coverage areas affect service availability and search results.
* Customer and vendor permissions are separate and must remain separate.

```

## Additional Context

See:

- @README.md for project overview
- @app/Config/Routes.php for route definitions
- @composer.json for dependencies and scripts
```

## Workflow Expectations

* Do not commit, push, or open PRs unless I explicitly ask.
* Before any git write action, explain what will change.
* Prefer batching related shell commands into one script or one chained command.
* Use existing project scripts before writing one-off Bash.
* Do not recreate this CLAUDE.md unless I ask. Edit it instead.
* For setup tasks, use the bootstrap script.
* For review tasks, use the review script.
* For git tasks, use the safe git script.

## QA Test Data

Before browser QA, ensure local test data exists.

Use the project seeder to create:
* test customers
* test vendors
* quoteable services
* event-type mappings
* structured pricing rows

Do not rely on manually created accounts or incomplete seed data.
