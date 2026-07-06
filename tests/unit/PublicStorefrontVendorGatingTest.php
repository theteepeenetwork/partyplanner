<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Controllers\Service_Controller;
use App\Models\ServiceModel;
use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * B1 gap fix: public storefront surfaces (browse/search catalogue query,
 * the service detail page, and the public vendor profile) must exclude a
 * pending/rejected vendor's services, even though nothing about the
 * `services` row itself changed (still `status = 'active'`).
 *
 * Covers:
 *  - ServiceModel::approvedVendorOnly() — the single reusable scope used by
 *    Service_Controller::applyPublicServiceCatalogFilters() (browse/search)
 *    and Home::index() (homepage spotlight), and vendorProfile()'s grid.
 *  - Service_Controller::view() — direct-URL access to a non-approved
 *    vendor's service redirects like "service not found" (mirrors the
 *    existing !$service branch). The approved-vendor happy path is proved
 *    at the UserModel::isVendorApproved() unit level rather than by
 *    rendering view() end-to-end, since that view's full data assembly
 *    pulls in ~8 pricing/location/review tables unrelated to this fix.
 *  - Service_Controller::vendorProfile() — direct-URL access to a
 *    non-approved vendor's public profile 404s (mirrors the existing
 *    role-check 404 branch).
 *  - The vendor's own dashboard (Profile::services(), queried directly via
 *    ServiceModel — not through the new scope) still lists their services
 *    regardless of vendor_status.
 *
 * @internal
 */
final class PublicStorefrontVendorGatingTest extends CIUnitTestCase
{
    use ControllerTestTrait;
    use DatabaseTestTrait;

    protected $namespace = 'Tests\Support';

    private function seedVendor(string $vendorStatus): int
    {
        $userModel = new UserModel();

        return (int) $userModel->insert([
            'name'          => ucfirst($vendorStatus) . ' Vendor',
            'username'      => 'psv_' . $vendorStatus . '_' . uniqid(),
            'email'         => uniqid('psv_' . $vendorStatus . '_') . '@example.com',
            'password'      => 'hash',
            'role'          => 'vendor',
            'vendor_status' => $vendorStatus,
        ], true);
    }

    private function seedService(int $vendorId, string $status = 'active'): int
    {
        $serviceModel = new ServiceModel();

        return (int) $serviceModel->insert([
            'vendor_id' => $vendorId,
            'title'     => 'Storefront Gating Test Service',
            'status'    => $status,
        ], true);
    }

    // ---------------------------------------------------------------
    // ServiceModel::approvedVendorOnly() — the shared listing scope
    // ---------------------------------------------------------------

    public function testApprovedVendorOnlyIncludesApprovedVendorServices(): void
    {
        $vendorId  = $this->seedVendor('approved');
        $serviceId = $this->seedService($vendorId);

        $ids = array_column(
            (new ServiceModel())->approvedVendorOnly()->findAll(),
            'id'
        );

        $this->assertContains($serviceId, $ids);
    }

    public function testApprovedVendorOnlyExcludesRejectedVendorServices(): void
    {
        $vendorId  = $this->seedVendor('rejected');
        $serviceId = $this->seedService($vendorId);

        $ids = array_column(
            (new ServiceModel())->approvedVendorOnly()->findAll(),
            'id'
        );

        $this->assertNotContains($serviceId, $ids);
    }

    public function testApprovedVendorOnlyExcludesPendingVendorServices(): void
    {
        $vendorId  = $this->seedVendor('pending');
        $serviceId = $this->seedService($vendorId);

        $ids = array_column(
            (new ServiceModel())->approvedVendorOnly()->findAll(),
            'id'
        );

        $this->assertNotContains($serviceId, $ids);
    }

    public function testApprovedVendorOnlyComposesWithOtherWhereClauses(): void
    {
        // Mirrors applyPublicServiceCatalogFilters(): status='active' AND
        // deleted_at IS NULL AND approvedVendorOnly(), all on one builder.
        $approvedVendorId = $this->seedVendor('approved');
        $rejectedVendorId = $this->seedVendor('rejected');
        $activeApprovedId = $this->seedService($approvedVendorId, 'active');
        $activeRejectedId = $this->seedService($rejectedVendorId, 'active');

        $ids = array_column(
            (new ServiceModel())
                ->where('status', 'active')
                ->where('deleted_at', null)
                ->approvedVendorOnly()
                ->findAll(),
            'id'
        );

        $this->assertContains($activeApprovedId, $ids);
        $this->assertNotContains($activeRejectedId, $ids);
    }

    // ---------------------------------------------------------------
    // UserModel::isVendorApproved() — used to gate view()
    // ---------------------------------------------------------------

    public function testIsVendorApprovedTrueOnlyForApprovedVendor(): void
    {
        $approved = $this->seedVendor('approved');
        $pending  = $this->seedVendor('pending');
        $rejected = $this->seedVendor('rejected');

        $userModel = new UserModel();
        $this->assertTrue($userModel->isVendorApproved($approved));
        $this->assertFalse($userModel->isVendorApproved($pending));
        $this->assertFalse($userModel->isVendorApproved($rejected));
    }

    public function testIsVendorApprovedFalseForMissingUser(): void
    {
        $this->assertFalse((new UserModel())->isVendorApproved(999999));
    }

    // ---------------------------------------------------------------
    // Service_Controller::view() — direct URL to a service detail page
    // ---------------------------------------------------------------

    public function testRejectedVendorServiceViewRedirectsAsNotFound(): void
    {
        $vendorId  = $this->seedVendor('rejected');
        $serviceId = $this->seedService($vendorId);

        $result = $this->withUri('http://example.com/service/view/' . $serviceId)
            ->controller(Service_Controller::class)
            ->execute('view', $serviceId);

        $result->assertRedirect();
        $result->assertRedirectTo('/browse-services');
        $this->assertSame('Service not found.', session()->getFlashdata('error'));
    }

    public function testPendingVendorServiceViewRedirectsAsNotFound(): void
    {
        $vendorId  = $this->seedVendor('pending');
        $serviceId = $this->seedService($vendorId);

        $result = $this->withUri('http://example.com/service/view/' . $serviceId)
            ->controller(Service_Controller::class)
            ->execute('view', $serviceId);

        $result->assertRedirect();
        $result->assertRedirectTo('/browse-services');
        $this->assertSame('Service not found.', session()->getFlashdata('error'));
    }

    // ---------------------------------------------------------------
    // Service_Controller::vendorProfile() — public vendor storefront page
    // ---------------------------------------------------------------

    public function testApprovedVendorProfileRenders(): void
    {
        $vendorId = $this->seedVendor('approved');
        $this->seedService($vendorId);

        $result = $this->withUri('http://example.com/vendor/' . $vendorId)
            ->controller(Service_Controller::class)
            ->execute('vendorProfile', $vendorId);

        $result->assertOK();
    }

    public function testRejectedVendorProfileIs404(): void
    {
        $vendorId = $this->seedVendor('rejected');
        $this->seedService($vendorId);

        $result = $this->withUri('http://example.com/vendor/' . $vendorId)
            ->controller(Service_Controller::class)
            ->execute('vendorProfile', $vendorId);

        $result->assertStatus(404);
    }

    public function testPendingVendorProfileIs404(): void
    {
        $vendorId = $this->seedVendor('pending');
        $this->seedService($vendorId);

        $result = $this->withUri('http://example.com/vendor/' . $vendorId)
            ->controller(Service_Controller::class)
            ->execute('vendorProfile', $vendorId);

        $result->assertStatus(404);
    }

    // ---------------------------------------------------------------
    // Vendor's own dashboard must be unaffected (Profile::services())
    // ---------------------------------------------------------------

    public function testPendingVendorOwnServiceStillReturnedByDirectVendorIdQuery(): void
    {
        // Profile::services() queries ServiceModel::where('vendor_id', ...)
        // directly — deliberately bypassing approvedVendorOnly() — so a
        // pending/rejected vendor still sees their own listings on their
        // dashboard even though public surfaces hide them.
        $vendorId  = $this->seedVendor('pending');
        $serviceId = $this->seedService($vendorId);

        $ownServices = (new ServiceModel())
            ->where('vendor_id', $vendorId)
            ->where('deleted_at', null)
            ->findAll();

        $this->assertContains($serviceId, array_column($ownServices, 'id'));
    }

    // ---------------------------------------------------------------
    // Direct-URL guard: add-to-basket refuses non-approved vendors
    // ---------------------------------------------------------------

    public function testAddToBasketRefusesRejectedVendorService(): void
    {
        $vendorId  = $this->seedVendor('rejected');
        $serviceId = $this->seedService($vendorId);

        $customerId = (int) (new UserModel())->insert([
            'name'     => 'Basket Guard Customer',
            'username' => 'psv_cust_' . uniqid(),
            'email'    => uniqid('psv_cust_') . '@example.com',
            'password' => 'hash',
            'role'     => 'customer',
        ], true);

        $eventId = (int) (new \App\Models\EventModel())->insert([
            'user_id'     => $customerId,
            'title'       => 'Basket Guard Event',
            'date'        => date('Y-m-d', strtotime('+30 days')),
            'guest_count' => 20,
        ], true);

        session()->set(['user_id' => $customerId, 'role' => 'customer']);
        $this->request->setGlobal('get', ['event_id' => (string) $eventId]);

        $result = $this->withUri('http://example.com/event/add-to-basket/' . $serviceId)
            ->controller(\App\Controllers\EventController::class)
            ->execute('addToBasket', $serviceId);

        $result->assertRedirectTo('/browse-services');

        $rows = (new \App\Models\EventBasketItemModel())
            ->where('event_id', $eventId)
            ->where('service_id', $serviceId)
            ->findAll();
        $this->assertSame([], $rows, 'Rejected vendor service must not be added to a basket via direct URL.');
    }
}
