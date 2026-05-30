# CLAUDE.md

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

## Test customers
Customer 1
email: customer1@c.com
password: password

Customer 2
email: customer2@c.com
password: password

## Test vendors
Vendor 1
email: vendor1@v.com
password: password

Vendor 2
email: vendor2@v.com
password: password


## Test customers
Customer 1
email: customer1@c.com
password: password

Customer 2
email: customer2@c.com
password: password

## Test vendors
Vendor 1
email: vendor1@v.com
password: password

Vendor 2
email: vendor2@v.com
password: password