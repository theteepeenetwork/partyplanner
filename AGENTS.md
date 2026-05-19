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
- Local dev uses `http://partyplanner.test/` (`app/Config/App.php` `baseURL`). Cloud/agent environments may use `php spark serve --port 8888` with a matching `baseURL` if needed.
- `php-cs-fixer` can be extremely slow on a full `app/` scan (may hang for several minutes). For quick linting, prefer `find app -name "*.php" -exec php -l {} \;` which completes in ~5 seconds.
- The `event_marketplace.sql` dump is idempotent (uses `CREATE TABLE IF NOT EXISTS`) and includes all ~26 tables plus seed data. However, its INSERT statements reference columns (e.g. `moderation_status` on `chat_messages`) that are only added by `database_update.sql`. The correct import order is: run `database_update.sql` first (it uses `ADD COLUMN IF NOT EXISTS` / `CREATE TABLE IF NOT EXISTS` so it's safe on a fresh DB), then run `event_marketplace.sql`.
- MariaDB needs `/run/mysqld` to exist before starting: `sudo mkdir -p /run/mysqld && sudo chown mysql:mysql /run/mysqld`.
