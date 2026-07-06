<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Libraries\VendorVetting;
use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * State-transition coverage for VendorVetting::approve()/reject().
 *
 * @internal
 */
final class VendorVettingTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    private const ADMIN_ID = 9001;

    protected $namespace = 'Tests\Support';

    private function seedVendor(array $overrides = []): int
    {
        $userModel = new UserModel();
        $id        = $userModel->insert(array_merge([
            'name'          => 'Vendor Vetting Test',
            'username'      => 'vvt_' . uniqid(),
            'email'         => uniqid('vvt_') . '@example.com',
            'password'      => 'hash',
            'role'          => 'vendor',
            'vendor_status' => 'pending',
        ], $overrides), true);

        return (int) $id;
    }

    private function seedCustomer(): int
    {
        $userModel = new UserModel();
        $id        = $userModel->insert([
            'name'     => 'Customer Vetting Test',
            'username' => 'cvt_' . uniqid(),
            'email'    => uniqid('cvt_') . '@example.com',
            'password' => 'hash',
            'role'     => 'customer',
        ], true);

        return (int) $id;
    }

    public function testApprovePendingVendor(): void
    {
        $vendorId = $this->seedVendor();

        $result = (new VendorVetting())->approve($vendorId, self::ADMIN_ID);

        $this->assertTrue($result);

        $vendor = (new UserModel())->find($vendorId);
        $this->assertSame('approved', $vendor['vendor_status']);
        $this->assertSame(self::ADMIN_ID, (int) $vendor['vendor_status_reviewed_by']);
        $this->assertNotNull($vendor['vendor_status_reviewed_at']);
        $this->assertNull($vendor['vendor_status_reason']);
    }

    public function testApproveWithOptionalReasonPersistsReason(): void
    {
        $vendorId = $this->seedVendor();

        (new VendorVetting())->approve($vendorId, self::ADMIN_ID, 'Looks good, approved with note.');

        $vendor = (new UserModel())->find($vendorId);
        $this->assertSame('approved', $vendor['vendor_status']);
        $this->assertSame('Looks good, approved with note.', $vendor['vendor_status_reason']);
    }

    public function testRejectPendingVendorPersistsReasonAndAudit(): void
    {
        $vendorId = $this->seedVendor();

        $result = (new VendorVetting())->reject($vendorId, self::ADMIN_ID, 'Incomplete business documentation.');

        $this->assertTrue($result);

        $vendor = (new UserModel())->find($vendorId);
        $this->assertSame('rejected', $vendor['vendor_status']);
        $this->assertSame('Incomplete business documentation.', $vendor['vendor_status_reason']);
        $this->assertSame(self::ADMIN_ID, (int) $vendor['vendor_status_reviewed_by']);
        $this->assertNotNull($vendor['vendor_status_reviewed_at']);
    }

    public function testRejectWithEmptyReasonReturnsFalseAndLeavesRowUnchanged(): void
    {
        $vendorId = $this->seedVendor(['vendor_status' => 'pending']);

        $result = (new VendorVetting())->reject($vendorId, self::ADMIN_ID, '   ');

        $this->assertFalse($result);

        $vendor = (new UserModel())->find($vendorId);
        $this->assertSame('pending', $vendor['vendor_status']);
        $this->assertNull($vendor['vendor_status_reviewed_by']);
    }

    public function testReApproveAfterRejectClearsStaleReasonAndOverwritesAudit(): void
    {
        $vendorId = $this->seedVendor();
        $vetting  = new VendorVetting();

        $vetting->reject($vendorId, self::ADMIN_ID, 'Not eligible yet.');
        $rejectedVendor = (new UserModel())->find($vendorId);
        $this->assertSame('rejected', $rejectedVendor['vendor_status']);

        $secondAdminId = self::ADMIN_ID + 1;
        $vetting->approve($vendorId, $secondAdminId);

        $vendor = (new UserModel())->find($vendorId);
        $this->assertSame('approved', $vendor['vendor_status']);
        $this->assertNull($vendor['vendor_status_reason']);
        $this->assertSame($secondAdminId, (int) $vendor['vendor_status_reviewed_by']);
    }

    public function testApproveOnCustomerIdReturnsFalseAndDoesNotWrite(): void
    {
        $customerId = $this->seedCustomer();

        $result = (new VendorVetting())->approve($customerId, self::ADMIN_ID);

        $this->assertFalse($result);

        $customer = (new UserModel())->find($customerId);
        $this->assertNull($customer['vendor_status_reviewed_by']);
    }

    public function testRejectOnMissingIdReturnsFalse(): void
    {
        $result = (new VendorVetting())->reject(999999, self::ADMIN_ID, 'Any reason');

        $this->assertFalse($result);
    }

    public function testReasonLongerThan2000CharsIsTruncated(): void
    {
        $vendorId   = $this->seedVendor();
        $longReason = str_repeat('a', 2500);

        (new VendorVetting())->reject($vendorId, self::ADMIN_ID, $longReason);

        $vendor = (new UserModel())->find($vendorId);
        $this->assertSame(VendorVetting::MAX_REASON_LENGTH, strlen($vendor['vendor_status_reason']));
    }
}
