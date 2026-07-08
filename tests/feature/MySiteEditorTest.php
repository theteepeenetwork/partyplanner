<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Controllers\Profile;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * Vendor "My site" appearance editor (D2): a vendor edits only the
 * presentation fields of their own vendor_sites row. Covers:
 *  - GET renders the editor for a vendor that has a site
 *  - a vendor with no site row sees the empty state, not the editor
 *  - POST persists colours/about/phone (normalising shorthand hex)
 *  - an invalid hex colour is rejected without writing
 *  - subdomain / status / vendor_id are never writable through this form
 *
 * @internal
 */
final class MySiteEditorTest extends CIUnitTestCase
{
    use ControllerTestTrait;
    use DatabaseTestTrait;

    // Run App migrations too (real vendor_sites table), like the tenant tests.
    protected $namespace;

    protected function setUp(): void
    {
        parent::setUp();
        session()->destroy();
    }

    private function seedVendor(string $status = 'approved'): int
    {
        $this->db->table('users')->insert([
            'name'          => 'Editor Vendor',
            'username'      => 'mysite_' . uniqid(),
            'email'         => uniqid('mysite_') . '@example.test',
            'password'      => 'hash',
            'role'          => 'vendor',
            'vendor_status' => $status,
        ]);

        return (int) $this->db->insertID();
    }

    private function seedSite(int $vendorId, array $overrides = []): int
    {
        $this->db->table('vendor_sites')->insert(array_merge([
            'vendor_id'       => $vendorId,
            'subdomain'       => 'editor' . $vendorId,
            'business_name'   => 'Editor Events',
            'primary_color'   => '#1c4a36',
            'secondary_color' => '#b98c2a',
            'status'          => 'active',
        ], $overrides));

        return (int) $this->db->insertID();
    }

    private function loginAs(int $vendorId): void
    {
        session()->set(['user_id' => $vendorId, 'role' => 'vendor']);
    }

    public function testEditorRendersForVendorWithSite(): void
    {
        $vendorId = $this->seedVendor();
        $this->seedSite($vendorId);
        $this->loginAs($vendorId);

        $result = $this->controller(Profile::class)->execute('mySite');

        $result->assertOK();
        $result->assertSee('Publish changes');
        $result->assertSee('Live preview');
        $result->assertSee('Colour theme');
        $result->assertSee('Warm editorial'); // a preset theme option renders
        $result->assertSee('editor' . $vendorId . '.partysmith.co.uk');
    }

    public function testVendorWithoutSiteSeesEmptyState(): void
    {
        $vendorId = $this->seedVendor();
        $this->loginAs($vendorId);

        $result = $this->controller(Profile::class)->execute('mySite');

        $result->assertOK();
        $result->assertSee("isn't set up yet");
        $result->assertDontSee('Publish changes');
    }

    public function testPostPersistsThemeAndFields(): void
    {
        $vendorId = $this->seedVendor();
        $siteId   = $this->seedSite($vendorId);
        $this->loginAs($vendorId);

        $request = service('request');
        $request->setMethod('POST');
        $request->setGlobal('post', [
            'theme'      => 'teal',
            'about_text' => 'Two brothers, one van.',
            'phone'      => '07700 900123',
        ]);

        $result = $this->withRequest($request)->controller(Profile::class)->execute('mySite');
        $result->assertRedirectTo('/profile/my-site');

        $row = $this->db->table('vendor_sites')->where('id', $siteId)->get()->getRowArray();
        $this->assertSame('teal', $row['theme']);
        $this->assertSame('Two brothers, one van.', $row['about_text']);
        $this->assertSame('07700 900123', $row['phone']);
    }

    public function testInvalidThemeIsRejectedWithoutWriting(): void
    {
        $vendorId = $this->seedVendor();
        $siteId   = $this->seedSite($vendorId, ['theme' => 'warm']);
        $this->loginAs($vendorId);

        $request = service('request');
        $request->setMethod('POST');
        $request->setGlobal('post', ['theme' => 'neon-disco']);

        $result = $this->withRequest($request)->controller(Profile::class)->execute('mySite');
        $result->assertRedirectTo('/profile/my-site');
        $this->assertNotNull(session()->getFlashdata('error'));

        $row = $this->db->table('vendor_sites')->where('id', $siteId)->get()->getRowArray();
        $this->assertSame('warm', $row['theme']); // unchanged
    }

    public function testSubdomainAndStatusAreNotWritable(): void
    {
        $vendorId = $this->seedVendor();
        $siteId   = $this->seedSite($vendorId, ['subdomain' => 'locked' . $vendorId, 'status' => 'active']);
        $this->loginAs($vendorId);

        $request = service('request');
        $request->setMethod('POST');
        $request->setGlobal('post', [
            'theme'     => 'graphite',
            'subdomain' => 'hijacked',
            'status'    => 'suspended',
            'vendor_id' => 999999,
        ]);

        $this->withRequest($request)->controller(Profile::class)->execute('mySite');

        $row = $this->db->table('vendor_sites')->where('id', $siteId)->get()->getRowArray();
        $this->assertSame('locked' . $vendorId, $row['subdomain']);
        $this->assertSame('active', $row['status']);
        $this->assertSame($vendorId, (int) $row['vendor_id']);
    }
}
