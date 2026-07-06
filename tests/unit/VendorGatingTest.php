<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * Route-gating matrix for the vendor vetting queue (B1): pending/rejected
 * vendors are bounced to /profile (which renders the under-review/rejected
 * dashboard state) instead of a bare redirect/403; approved vendors and
 * customers are unaffected; gating re-checks the DB per request so an
 * approval takes effect without re-login.
 *
 * Admin approve/reject handler coverage lives in VendorGatingAdminHandlersTest
 * (ControllerTestTrait), since that trait collides with FeatureTestTrait on
 * withBody() when combined in one class.
 *
 * @internal
 */
final class VendorGatingTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $namespace = 'Tests\Support';

    private function seedUser(string $role, string $vendorStatus = 'approved', ?string $reason = null): int
    {
        $userModel = new UserModel();

        return (int) $userModel->insert([
            'name'                 => ucfirst($role) . ' Gating Test',
            'username'             => $role . '_' . uniqid(),
            'email'                => uniqid($role . '_') . '@example.com',
            'password'             => 'hash',
            'role'                 => $role,
            'vendor_status'        => $role === 'vendor' ? $vendorStatus : 'pending',
            'vendor_status_reason' => $reason,
        ], true);
    }

    public function testPendingVendorGetProfileServicesRedirectsToProfile(): void
    {
        $vendorId = $this->seedUser('vendor', 'pending');

        $result = $this->withSession(['user_id' => $vendorId, 'role' => 'vendor'])->get('profile/services');

        $result->assertRedirectTo('/profile');
    }

    public function testPendingVendorGetServiceCreateRedirectsToProfile(): void
    {
        $vendorId = $this->seedUser('vendor', 'pending');

        $result = $this->withSession(['user_id' => $vendorId, 'role' => 'vendor'])->get('service/create');

        $result->assertRedirectTo('/profile');
    }

    public function testRejectedVendorGetProfileEarningsRedirectsToProfile(): void
    {
        $vendorId = $this->seedUser('vendor', 'rejected', 'Missing documentation.');

        $result = $this->withSession(['user_id' => $vendorId, 'role' => 'vendor'])->get('profile/earnings');

        $result->assertRedirectTo('/profile');
    }

    public function testApprovedVendorGetProfileServicesIsOk(): void
    {
        $vendorId = $this->seedUser('vendor', 'approved');

        $result = $this->withSession(['user_id' => $vendorId, 'role' => 'vendor'])->get('profile/services');

        $result->assertOK();
    }

    public function testPendingVendorGetProfileShowsUnderReviewMarker(): void
    {
        $vendorId = $this->seedUser('vendor', 'pending');

        $result = $this->withSession(['user_id' => $vendorId, 'role' => 'vendor'])->get('profile');

        $result->assertOK();
        $result->assertSee('under review');
    }

    public function testRejectedVendorGetProfileShowsSeededReason(): void
    {
        $vendorId = $this->seedUser('vendor', 'rejected', 'Missing documentation.');

        $result = $this->withSession(['user_id' => $vendorId, 'role' => 'vendor'])->get('profile');

        $result->assertOK();
        $result->assertSee('Missing documentation.');
    }

    public function testCustomerGetProfileEventsIsOk(): void
    {
        $customerId = $this->seedUser('customer');

        $result = $this->withSession(['user_id' => $customerId, 'role' => 'customer'])->get('profile/events');

        $result->assertOK();
    }

    public function testCustomerGetProfileServicesRedirectsWithVendorOnlyError(): void
    {
        $customerId = $this->seedUser('customer');

        $result = $this->withSession(['user_id' => $customerId, 'role' => 'customer'])->get('profile/services');

        $result->assertRedirectTo('/profile');
        $this->assertSame(
            'This area is only available to vendor accounts.',
            session()->getFlashdata('error'),
        );
    }

    public function testGuestGetProfileServicesRedirectsToLogin(): void
    {
        $result = $this->withSession([])->get('profile/services');

        $result->assertRedirectTo('/login');
    }

    public function testApprovalTakesEffectWithoutReLogin(): void
    {
        $vendorId = $this->seedUser('vendor', 'pending');

        $first = $this->withSession(['user_id' => $vendorId, 'role' => 'vendor'])->get('profile/services');
        $first->assertRedirectTo('/profile');

        // Flip the DB row directly (as the admin approve action would) — session untouched.
        (new UserModel())->update($vendorId, ['vendor_status' => 'approved']);

        $second = $this->withSession(['user_id' => $vendorId, 'role' => 'vendor'])->get('profile/services');
        $second->assertOK();
    }

    public function testRegistrationDefaultsNewVendorToPending(): void
    {
        // Insert via UserModel omitting vendor_status to assert the fail-safe DB/column default,
        // matching the behaviour Register::createVendor() relies on for real signups.
        $userModel = new UserModel();
        $id        = $userModel->insert([
            'name'     => 'Fresh Vendor',
            'username' => 'fresh_vendor_' . uniqid(),
            'email'    => uniqid('fresh_vendor_') . '@example.com',
            'password' => 'hash',
            'role'     => 'vendor',
        ], true);

        $vendor = $userModel->find((int) $id);
        $this->assertSame('pending', $vendor['vendor_status']);
    }
}
