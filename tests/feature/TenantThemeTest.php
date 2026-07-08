<?php

declare(strict_types=1);

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * The selected colour theme is applied as a body class by the shared tenant
 * header, so it themes the storefront AND every downstream page (service,
 * checkout, confirmation) in one switch. An unset/invalid theme resolves to the
 * default so the palette is always valid.
 *
 * @internal
 */
final class TenantThemeTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    private const BASE_DOMAIN = 'partyplanner.test';

    protected $namespace;
    private int $serviceId;

    protected function setUp(): void
    {
        parent::setUp();
        putenv('tenant.baseDomain=' . self::BASE_DOMAIN);
        $_ENV['tenant.baseDomain']    = self::BASE_DOMAIN;
        $_SERVER['tenant.baseDomain'] = self::BASE_DOMAIN;
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        putenv('tenant.baseDomain');
        unset($_ENV['tenant.baseDomain'], $_SERVER['tenant.baseDomain'], $_SERVER['HTTP_HOST']);
        // These tests end on a tenant host; revert the shared routes service to
        // the marketplace so a later test file doesn't inherit tenant routing.
        service('routes')->resetRoutes();
    }

    private function seedSite(?string $theme): void
    {
        $row = ['name' => 'V', 'username' => 'v_' . uniqid(), 'email' => uniqid('v_') . '@e.test', 'password' => 'h', 'role' => 'vendor'];
        if ($this->db->fieldExists('vendor_status', 'users')) {
            $row['vendor_status'] = 'approved';
        }
        $this->db->table('users')->insert($row);
        $vendorId = (int) $this->db->insertID();

        // Two services → multi-service lander (exercises tenant/home).
        foreach (['Photo Booth', 'Bar Hire'] as $t) {
            $this->db->table('services')->insert(['vendor_id' => $vendorId, 'title' => $t, 'status' => 'active']);
        }
        $this->serviceId = (int) $this->db->insertID();

        $this->db->table('vendor_sites')->insert([
            'vendor_id' => $vendorId, 'subdomain' => 'themed',
            'business_name' => 'Themed Events', 'status' => 'active', 'theme' => $theme,
        ]);
    }

    private function onTenant(): void
    {
        $_SERVER['HTTP_HOST'] = 'themed.' . self::BASE_DOMAIN;
        service('routes')->resetRoutes();
    }

    public function testStorefrontUsesTheSelectedTheme(): void
    {
        $this->seedSite('teal');
        $this->onTenant();

        $this->get('/')->assertSee('sf-theme-teal');
    }

    public function testThemeFlowsToDownstreamPages(): void
    {
        $this->seedSite('warm');
        $this->onTenant();

        // The service page is a checkout-flow page; it shares the header, so the
        // theme carries through the whole journey.
        $this->get('/service/' . $this->serviceId)->assertSee('sf-theme-warm');
    }

    public function testUnsetThemeFallsBackToDefault(): void
    {
        $this->seedSite(null);
        $this->onTenant();

        $result = $this->get('/');
        $result->assertSee('sf-theme-clean');
        $result->assertDontSee('sf-theme-teal');
    }

    public function testInvalidStoredThemeFallsBackToDefault(): void
    {
        $this->seedSite('bogus-theme');
        $this->onTenant();

        $this->get('/')->assertSee('sf-theme-clean');
    }
}
