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

**First-time setup** — import in this exact order (later files `ALTER` tables created by earlier ones). **All files are required** — the seeders and the service-creation wizard write columns/tables added by every one of them (an un-imported file makes `db:seed` roll back). The runtime is dockerized, so pipe each file into the `mariadb` container:

```bash
for f in event_marketplace database_update database_quote_automation \
         database_fulfillment_extras database_quantity_pricing database_service_requirements; do
  docker exec -i mariadb mariadb --default-character-set=utf8mb4 event_marketplace < "$f.sql"
done
```

**Key tables:**

| Table | Purpose |
| --- | --- |
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

**Credentials** — the app runs in Docker; the DB host is the `mariadb` service, not localhost. The real credentials live in `.env` (already configured locally); the password is **not** blank. Defaults:

```
database.default.hostname = mariadb
database.default.database  = event_marketplace
database.default.username  = root
database.default.password  = <set in .env>
database.default.DBDriver  = MySQLi
database.default.port      = 3306
```

## Test Accounts & Seed Data

All passwords: `password`

| Role | Email |
| --- | --- |
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
# The app runs in Docker behind a virtual host at http://partyplanner.home/ — no spark serve needed.
# There is no host PHP/Composer; run every php/spark/composer command inside the container:
#   docker exec partyplanner php spark <command>

# Fast validation
docker exec partyplanner sh -c 'find app -name "*.php" -exec php -l {} \;'

# Run all tests
docker exec partyplanner php vendor/bin/phpunit --testdox

# Run a single test file
docker exec partyplanner php vendor/bin/phpunit tests/unit/SomeTest.php

# Scheduled commands
docker exec partyplanner php spark quote:remind-pending
docker exec partyplanner php spark quote:expire-stale
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

* The sole creation path is the multi-step wizard at `/service/create`, driven by
  `Service_Controller` with step views in `app/Views/service_create/service_create_step{1..6}.php`
  (plus `service_review.php` and the `wizard_rail`/`wizard_nav` partials).
* Public, private and corporate are **event-type branches within that one wizard**, not
  separate controllers/files. They remain conceptually distinct (separate pricing/config) and
  must not be conflated — e.g. public and private pricing stay separate systems.
* The old single-page `/service/list` SPA and the legacy `service_create_public/private/corporate.php`
  files were removed; do not reintroduce them. Do not replace the wizard without explicit instruction.

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

* Committing and pushing to the designated working branch is allowed. Open PRs as drafts.
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

---

# Web Team — operating protocol

This project runs a **human-orchestrated** agent team. You (the lead session) are the Orchestrator: you pick what to work on, scope it, and delegate. Three subagents live in `.claude/agents/`: `ux-auditor`, `builder`, `verifier`.

## The loop (follow on any improvement task)

1. Read `partysmith-findings.md` (grounded state, money-risk ranked), then `partysmith-backlog.md` (the epics). Work top-down, respecting the dependency order: **Admin → Vendor onboarding → Customer booking → Stripe → Interactions.**
2. To find work → delegate to **ux-auditor**; it appends prioritised findings to `BACKLOG.md`.
3. To build an approved, scoped task → write acceptance criteria, then delegate to **builder**.
4. To confirm done → delegate to **verifier**. Only the verifier marks a task complete. On FAIL it returns a specific gap → back to the builder.

## Definition of Done ("task completed" benchmark)

Done only when the verifier confirms: every objective gate passes (php-cs-fixer clean · `composer test` green with no regressions · no console errors · a11y AA and Perf ≥ 90 on touched surfaces) AND the quality rubric clears (mean ≥ 4.0, no dimension < 3). Stop on the exit conditions (5 cycles, < 0.3 rubric gain, or no net change) and escalate.

## Human-gated — never let the team act autonomously here
* Anything touching pricing, deposits, the quote pipeline, or Stripe — you approve before build and review the diff before ship.
* Net-new features — the team proposes; you choose.

## Current priorities (from partysmith-findings.md)

1. **F1** — consolidate the deposit path (EventController 15% vs legacy cart 10%); retire/redirect the legacy cart. *[your decision first]*
2. **F2** — delete the orphaned £15 `PaymentController::createPaymentIntent` stub route.
3. **F3** — `VendorQuoteAutomation` branch tests (7/8 outcomes untested) + replace the string-matched travel guard with a structured code.
4. **F4** — confirm the sandbox Stripe end-to-end path; add refund/cancel webhook events once the refund policy is set.
5. **F5** — fill `EventQuoteBuilder` coverage.

## House rules (enforced by auditor + verifier)

No visible card borders · fixed explicit guest ranges, never "up to" · service-detail short/long description beneath customisation options with a reviews placeholder · script accent (Mr Dafoe vs Caveat) unresolved — flag, don't pick.

## Invoking the agents

Claude Code auto-delegates by the `description` field, or call explicitly, e.g. *"Use the ux-auditor on the vendor wizard"* → review findings → *"Use the builder on F3"* → *"Use the verifier on that change."* Run `/agents` to confirm all three are loaded.
