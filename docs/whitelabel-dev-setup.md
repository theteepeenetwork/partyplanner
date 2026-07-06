# White-label vendor sites — local dev setup

Phase 1 of the white-label plan (see `whitelabel-build-plan.md`, T1–T4) serves a
vendor's branded storefront on `<subdomain>.<base domain>`:

- **Production:** `*.partysmith.co.uk`
- **Local dev:** `*.partyplanner.test`

The main domain (and `www.`, and any unrelated host such as `partyplanner.home`)
keeps serving the marketplace exactly as before — tenant routing only activates
for true subdomains of the configured base domain, and it **fails closed**:
unknown subdomain, suspended site, or a missing/non-vendor account all 404. On a
tenant host only the storefront routes exist; every marketplace URL 404s.

## 1. Configure the base domain

Add to `.env` (defaults to `partysmith.co.uk` when unset):

```
tenant.baseDomain = partyplanner.test
```

## 2. Resolve `*.partyplanner.test` to your machine

`/etc/hosts` can't wildcard, so either list the subdomains you use:

```
127.0.0.1  partyplanner.test
127.0.0.1  vendorone.partyplanner.test
```

…or wildcard with dnsmasq (macOS):

```bash
brew install dnsmasq
echo 'address=/.partyplanner.test/127.0.0.1' >> "$(brew --prefix)/etc/dnsmasq.conf"
sudo brew services start dnsmasq
sudo mkdir -p /etc/resolver
echo 'nameserver 127.0.0.1' | sudo tee /etc/resolver/test
```

If the app runs in Docker behind a virtual host, the vhost/proxy must also
accept the wildcard server name (e.g. `server_name partyplanner.test *.partyplanner.test;`)
and forward the original `Host:` header.

## 3. Migrate and seed a tenant

```bash
php spark migrate                      # creates vendor_sites (T1)
php spark db:seed QASeeder             # QA vendors/services (if not already done)
php spark db:seed VendorSiteSeeder     # site 'vendorone' on vendor1@v.com, active
```

The seeded site uses a deliberately non-marketplace palette (plum/amber) so the
branding override is visible at a glance.

## 4. Try it

- `http://vendorone.partyplanner.test/` — branded storefront: that vendor's
  active services only, business name as `<title>`, palette from
  `vendor_sites.primary_color`/`secondary_color`, "Powered by PartySmith" in
  the footer.
- `http://vendorone.partyplanner.test/service/<id>` — service detail; 404
  unless the service belongs to the tenant vendor and is active.
- `http://anythingelse.partyplanner.test/` — 404 (unknown subdomain).
- `http://partyplanner.test/` (or `partyplanner.home`) — marketplace, unchanged.

Without DNS/vhost changes you can smoke-test with curl and a Host header
against any running instance, e.g. `php spark serve --port 8081` then:

```bash
curl -H "Host: vendorone.partyplanner.test" http://127.0.0.1:8081/
curl -H "Host: vendorone.partyplanner.test" http://127.0.0.1:8081/browse-services   # 404
curl -H "Host: partyplanner.test"           http://127.0.0.1:8081/browse-services   # marketplace
```

## Suspending a site

```sql
UPDATE vendor_sites SET status = 'suspended' WHERE subdomain = 'vendorone';
```

The storefront 404s immediately (a polite "site unavailable" page is Phase 2,
T10).

## How tenant resolution works (for maintainers)

- `app/Libraries/TenantHost.php` — pure host parser (`tenant.baseDomain` env).
- `app/Config/Routes.php` — on a tenant host, registers only the tenant route
  group and returns; otherwise marketplace routes register untouched. Note:
  this is host-conditional, so a shared/warmed route cache must not be reused
  across hosts (no route caching is configured in this project).
- `app/Filters/VendorTenant.php` — subdomain → active `vendor_sites` row →
  vendor account; 404 on any miss; on success populates `service('tenant')`.
- `app/Libraries/TenantContext.php` — per-request tenant registry;
  `assertOwns($record)` is the single ownership guard (404 on foreign rows).
- `app/Controllers/TenantController.php` + `app/Views/tenant*` — storefront
  pages on `ServiceModel::publicCatalogue()` pinned to the tenant vendor.
- Tests: `tests/unit/TenantHostTest.php`,
  `tests/feature/TenantStorefrontTest.php` (Host-header-driven, full stack).
