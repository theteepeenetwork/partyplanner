<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Controllers\Admin\Vendors as AdminVendorsController;
use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * Admin\Vendors::approve()/reject() handler coverage, exercised directly via
 * ControllerTestTrait (bypasses routing/adminauth/csrf — those are already
 * exercised in production admin paths; transition depth is covered in
 * VendorVettingTest).
 *
 * @internal
 */
final class VendorGatingAdminHandlersTest extends CIUnitTestCase
{
    use ControllerTestTrait;
    use DatabaseTestTrait;

    protected $namespace = 'Tests\Support';

    private function seedVendor(): int
    {
        $userModel = new UserModel();

        return (int) $userModel->insert([
            'name'          => 'Vendor Admin Handler Test',
            'username'      => 'vaht_' . uniqid(),
            'email'         => uniqid('vaht_') . '@example.com',
            'password'      => 'hash',
            'role'          => 'vendor',
            'vendor_status' => 'pending',
        ], true);
    }

    public function testAdminApproveHandlerWritesStatusAndAudit(): void
    {
        $vendorId = $this->seedVendor();
        $adminId  = 5001;
        session()->set(['user_id' => $adminId, 'role' => 'admin']);

        $result = $this->withUri('http://example.com/admin/vendors/' . $vendorId . '/approve')
            ->controller(AdminVendorsController::class)
            ->execute('approve', $vendorId);

        $result->assertRedirect();

        $vendor = (new UserModel())->find($vendorId);
        $this->assertSame('approved', $vendor['vendor_status']);
        $this->assertSame($adminId, (int) $vendor['vendor_status_reviewed_by']);
        $this->assertNotNull($vendor['vendor_status_reviewed_at']);
    }

    public function testAdminRejectHandlerWritesStatusAndAudit(): void
    {
        $vendorId = $this->seedVendor();
        $adminId  = 5002;
        session()->set(['user_id' => $adminId, 'role' => 'admin']);

        $this->request->setGlobal('post', ['reason' => 'Missing insurance documents.']);

        $result = $this->withUri('http://example.com/admin/vendors/' . $vendorId . '/reject')
            ->controller(AdminVendorsController::class)
            ->execute('reject', $vendorId);

        $result->assertRedirect();

        $vendor = (new UserModel())->find($vendorId);
        $this->assertSame('rejected', $vendor['vendor_status']);
        $this->assertSame('Missing insurance documents.', $vendor['vendor_status_reason']);
        $this->assertSame($adminId, (int) $vendor['vendor_status_reviewed_by']);
    }

    public function testAdminRejectHandlerWithEmptyReasonReturnsError(): void
    {
        $vendorId = $this->seedVendor();
        $adminId  = 5003;
        session()->set(['user_id' => $adminId, 'role' => 'admin']);

        $this->request->setGlobal('post', ['reason' => '']);

        $result = $this->withUri('http://example.com/admin/vendors/' . $vendorId . '/reject')
            ->controller(AdminVendorsController::class)
            ->execute('reject', $vendorId);

        $result->assertRedirect();

        $vendor = (new UserModel())->find($vendorId);
        $this->assertSame('pending', $vendor['vendor_status']);
    }

    /**
     * View-markup coverage for the admin vetting queue (index): pending rows
     * expose Approve/Reject inline forms, a Pending badge and the live
     * pending count pill.
     */
    public function testAdminVendorsIndexShowsPendingBadgeAndActionsForPendingVendor(): void
    {
        $vendorId = $this->seedVendor();
        session()->set(['user_id' => 9001, 'role' => 'admin']);

        $result = $this->withUri('http://example.com/admin/vendors')
            ->controller(AdminVendorsController::class)
            ->execute('index');

        $result->assertOK();
        $result->assertSee('Pending');
        $result->assertSee('Approve');
        $result->assertSee('Reject');
        $result->assertSee('Vendor Admin Handler Test');
    }

    public function testAdminVendorsIndexShowsRejectedReasonAndReapproveAction(): void
    {
        $vendorId = $this->seedVendor();
        (new UserModel())->update($vendorId, [
            'vendor_status'        => 'rejected',
            'vendor_status_reason' => 'Missing insurance documents.',
        ]);
        session()->set(['user_id' => 9002, 'role' => 'admin']);

        $result = $this->withUri('http://example.com/admin/vendors')
            ->controller(AdminVendorsController::class)
            ->execute('index');

        $result->assertOK();
        $result->assertSee('Rejected');
        $result->assertSee('Missing insurance documents.');
        $result->assertSee('Re-approve');
    }

    public function testAdminVendorsIndexApprovedVendorHasNoVettingActions(): void
    {
        $vendorId = $this->seedVendor();
        (new UserModel())->update($vendorId, ['vendor_status' => 'approved']);
        session()->set(['user_id' => 9003, 'role' => 'admin']);

        $result = $this->withUri('http://example.com/admin/vendors')
            ->controller(AdminVendorsController::class)
            ->execute('index');

        $result->assertOK();
        $result->assertSee('Approved');
        $result->assertDontSee('Re-approve');
    }

    public function testAdminVendorsIndexStatusFilterNarrowsResults(): void
    {
        $userModel = new UserModel();
        $pendingId = $this->seedVendor();
        $approvedId = $this->seedVendor();
        $userModel->update($approvedId, ['vendor_status' => 'approved']);
        session()->set(['user_id' => 9004, 'role' => 'admin']);

        // The filter must include the pending vendor and exclude the approved
        // one — assert on their unique emails, not the shared display name.
        $pendingEmail = $userModel->find($pendingId)['email'];
        $approvedEmail = $userModel->find($approvedId)['email'];

        $this->request->setGlobal('get', ['status' => 'pending']);

        $result = $this->withUri('http://example.com/admin/vendors?status=pending')
            ->controller(AdminVendorsController::class)
            ->execute('index');

        $result->assertOK();
        $result->assertSee($pendingEmail);
        $result->assertDontSee($approvedEmail);
    }
}
