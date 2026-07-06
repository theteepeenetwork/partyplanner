<?php

declare(strict_types=1);

namespace Tests\Feature;

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * End-to-end tenant routing/isolation (T2+T3), driven through the real
 * Routes.php + VendorTenant filter + TenantController stack by varying the
 * request Host:
 *
 *  - tenant home lists ONLY that vendor's active, non-deleted services
 *  - another vendor's service ID on the tenant service route → 404
 *  - unknown subdomain → 404, suspended site → 404
 *  - marketplace routes are not reachable on a tenant host (fail closed)
 *  - the main domain (and www) keeps serving the marketplace
 *
 * @internal
 */
final class TenantStorefrontTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    // Run migrations from ALL namespaces: Tests\Support provides the minimal
    // users/services tables (guarded), then the real App CreateVendorSites
    // migration builds vendor_sites — the production migration is what's
    // under test here, not a test double.
    protected $namespace = null;

    private const BASE_DOMAIN = 'partyplanner.test';

    private int $tenantVendorId;
    private int $otherVendorId;
    private int $tenantServiceId;
    private int $otherServiceId;
    private int $inactiveServiceId;
    private int $deletedServiceId;

    protected function setUp(): void
    {
        parent::setUp();

        putenv('tenant.baseDomain=' . self::BASE_DOMAIN);
        $_ENV['tenant.baseDomain']    = self::BASE_DOMAIN;
        $_SERVER['tenant.baseDomain'] = self::BASE_DOMAIN;

        $this->tenantVendorId = $this->seedVendor('Tenant Vendor', 'wl_tenant');
        $this->otherVendorId  = $this->seedVendor('Other Vendor', 'wl_other');

        $this->tenantServiceId   = $this->seedService($this->tenantVendorId, 'Tenant Gazebo Hire');
        $this->otherServiceId    = $this->seedService($this->otherVendorId, 'Rival Bouncy Castle');
        $this->inactiveServiceId = $this->seedService($this->tenantVendorId, 'Paused Chocolate Fountain', 'inactive');
        $this->deletedServiceId  = $this->seedService($this->tenantVendorId, 'Retired Candy Floss Cart', 'active', '2026-01-01 00:00:00');

        $this->db->table('vendor_sites')->insert([
            'vendor_id'     => $this->tenantVendorId,
            'subdomain'     => 'vendorone',
            'business_name' => 'Vendor One Events',
            'phone'         => '020 7946 0958',
            'status'        => 'active',
        ]);
        $this->db->table('vendor_sites')->insert([
            'vendor_id'     => $this->otherVendorId,
            'subdomain'     => 'sleepy',
            'business_name' => 'Sleepy Suspended Site',
            'status'        => 'suspended',
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        putenv('tenant.baseDomain');
        unset($_ENV['tenant.baseDomain'], $_SERVER['tenant.baseDomain'], $_SERVER['HTTP_HOST']);
    }

    private function seedVendor(string $name, string $username): int
    {
        $row = [
            'name'     => $name,
            'username' => $username,
            'email'    => $username . '@example.test',
            'password' => 'hash',
            'role'     => 'vendor',
        ];
        // Merge-proof with the vendor-vetting branch: approve when the column exists.
        if ($this->db->fieldExists('vendor_status', 'users')) {
            $row['vendor_status'] = 'approved';
        }
        $this->db->table('users')->insert($row);

        return (int) $this->db->insertID();
    }

    private function seedService(int $vendorId, string $title, string $status = 'active', ?string $deletedAt = null): int
    {
        $this->db->table('services')->insert([
            'vendor_id'         => $vendorId,
            'title'             => $title,
            'short_description' => $title . ' short blurb',
            'status'            => $status,
            'deleted_at'        => $deletedAt,
        ]);

        return (int) $this->db->insertID();
    }

    /**
     * Both Routes.php and the VendorTenant filter resolve the tenant from
     * $_SERVER['HTTP_HOST'], so pinning it here drives the whole stack.
     *
     * The shared routes service was already populated at PHPUnit bootstrap
     * (CLI has no Host header → marketplace routes), so reset it to make
     * call() re-evaluate Routes.php against this host.
     */
    private function onHost(string $host): void
    {
        $_SERVER['HTTP_HOST'] = $host;
        service('routes')->resetRoutes();
    }

    // ── Tenant host ─────────────────────────────────────────────────────

    public function testTenantHomeListsOnlyTenantVendorActiveServices(): void
    {
        $this->onHost('vendorone.' . self::BASE_DOMAIN);
        $result = $this->get('/');

        $result->assertStatus(200);
        $result->assertSee('Vendor One Events');
        $result->assertSee('Tenant Gazebo Hire');
        $result->assertDontSee('Rival Bouncy Castle');       // other vendor
        $result->assertDontSee('Paused Chocolate Fountain'); // inactive
        $result->assertDontSee('Retired Candy Floss Cart');  // soft-deleted
    }

    public function testTenantServicePageRendersOwnService(): void
    {
        $this->onHost('vendorone.' . self::BASE_DOMAIN);
        $result = $this->get('/service/' . $this->tenantServiceId);

        $result->assertStatus(200);
        $result->assertSee('Tenant Gazebo Hire');
    }

    // In ENVIRONMENT=testing CodeIgniter rethrows PageNotFoundException
    // (display404errors) instead of rendering the 404 page, so "→ 404" is
    // asserted as the exception here. Production renders these as HTTP 404.

    public function testOtherVendorsServiceIdIs404OnTenantHost(): void
    {
        $this->onHost('vendorone.' . self::BASE_DOMAIN);
        $this->expectException(PageNotFoundException::class);
        $this->get('/service/' . $this->otherServiceId);
    }

    public function testInactiveOwnServiceIs404OnTenantHost(): void
    {
        $this->onHost('vendorone.' . self::BASE_DOMAIN);
        $this->expectException(PageNotFoundException::class);
        $this->get('/service/' . $this->inactiveServiceId);
    }

    public function testSoftDeletedOwnServiceIs404OnTenantHost(): void
    {
        $this->onHost('vendorone.' . self::BASE_DOMAIN);
        $this->expectException(PageNotFoundException::class);
        $this->get('/service/' . $this->deletedServiceId);
    }

    public function testUnknownSubdomainIs404(): void
    {
        $this->onHost('nosuchvendor.' . self::BASE_DOMAIN);
        $this->expectException(PageNotFoundException::class);
        $this->get('/');
    }

    public function testSuspendedSiteIs404(): void
    {
        $this->onHost('sleepy.' . self::BASE_DOMAIN);
        $this->expectException(PageNotFoundException::class);
        $this->get('/');
    }

    public function testMarketplaceRoutesAreNotReachableOnTenantHost(): void
    {
        $this->onHost('vendorone.' . self::BASE_DOMAIN);
        $this->expectException(PageNotFoundException::class);
        $this->get('/browse-services');
    }

    // ── Marketplace hosts stay untouched ────────────────────────────────

    public function testMainDomainServesMarketplace(): void
    {
        $this->onHost(self::BASE_DOMAIN);
        $result = $this->get('/login');

        $result->assertStatus(200);
        $result->assertDontSee('Vendor One Events');
    }

    public function testWwwServesMarketplace(): void
    {
        $this->onHost('www.' . self::BASE_DOMAIN);
        $this->get('/login')->assertStatus(200);
    }

    public function testUnrelatedHostServesMarketplace(): void
    {
        // The current live host must keep behaving as the marketplace.
        $this->onHost('partyplanner.home');
        $this->get('/login')->assertStatus(200);
    }

    public function testTenantServiceRouteDoesNotExistOnMainDomain(): void
    {
        $this->onHost(self::BASE_DOMAIN);
        $this->expectException(PageNotFoundException::class);
        $this->get('/service/' . $this->tenantServiceId);
    }
}
