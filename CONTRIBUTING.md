# Contributing

## Dev Environment Setup

See [CLAUDE.md](CLAUDE.md) for how to start MariaDB, run the dev server, and import the database. Do not repeat those steps here.

## Branching

- Branch from `main` for every change: `git checkout -b feature/short-description`
- Submit changes as a pull request targeting `main`
- Keep branches focused — one logical change per PR

## Commit Style

- Use the imperative mood: "Add quote expiry command" not "Added…" or "Adding…"
- Keep the subject line under 72 characters
- Add a body paragraph if the _why_ is not obvious from the diff

## Tests

Tests live in `tests/unit/` and use PHPUnit. The test suite runs against SQLite3 in-memory — no running MariaDB instance is required.

```bash
# Run the full test suite
php vendor/bin/phpunit --testdox

# Run a single file
php vendor/bin/phpunit tests/unit/SomeTest.php
```

- Add a test for every new public method or bug fix
- Name test classes `<Subject>Test` and place them in `tests/unit/`
- Use descriptive method names: `test_build_returns_error_when_service_unavailable`

## Syntax and Linting

```bash
# Fast syntax check (~5 s) — run this before every commit
find app -name "*.php" -exec php -l {} \;

# Full CS-fixer dry-run (slow — can take several minutes on the full app/ tree)
php vendor/bin/php-cs-fixer fix --dry-run --diff app/
```

Prefer `php -l` for quick pre-commit checks. Run the CS-fixer only when you want to audit or fix style across a wider area.

## Caveats

- **Kint must be version 5.1.1** (not 6.x). If you see `Class "Kint\Zval\InstanceValue" not found`, delete `vendor/` and re-run `composer install`.
- `php-cs-fixer` on the full `app/` tree can hang for several minutes — use `php -l` for routine checks.
