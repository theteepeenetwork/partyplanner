# AGENTS.md

## Cursor Cloud specific instructions

### Architecture

This is a PHP CodeIgniter 4 event services marketplace. The CI4 framework is bundled directly in the `system/` directory (not installed via Composer). The app uses MariaDB/MySQL for persistence and PHP's built-in server for development.

### Starting services

1. **MariaDB**: `sudo mysqld_safe --skip-grant-tables &` (runs as root, no password, database: `event_marketplace`)
2. **Dev server**: `php spark serve --port 8888` from the workspace root

### Running tests

```bash
php vendor/bin/phpunit --testdox
```

Tests use SQLite3 in-memory (configured in `app/Config/Database.php` under the `tests` group), so MySQL does not need to be running for unit tests.

### Linting

No `.php-cs-fixer.dist.php` config exists yet, but the tool is available:
```bash
php vendor/bin/php-cs-fixer fix --dry-run --diff app/
```

For basic syntax checks: `find app -name "*.php" -exec php -l {} \;`

### Key caveats

- The `composer.json` originally referenced `codeigniter/framework` which is the CI3 package and doesn't exist at v4.5.0. This dependency was removed since CI4 is bundled in `system/`.
- The SQL dump (`event_marketplace.sql`) only has 3 tables (users, services, events). The app requires ~25 tables. The full schema must be created from the model definitions (see the database setup section in the update script or import the complete schema).
- The `createvent/` directory at the workspace root is an earlier/legacy copy of the same app — it can be ignored for development purposes.
- Stripe payment features require `STRIPE_SECRET_KEY`, `STRIPE_PUBLISHABLE_KEY`, and `STRIPE_WEBHOOK_SECRET` env vars, but the app runs without them for browsing, registration, login, and service creation.
- The app is configured to run on port 8888 (`app/Config/App.php` sets `baseURL` to `http://localhost:8888/`).
