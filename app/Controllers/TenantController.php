<?php

namespace App\Controllers;

use App\Libraries\TenantContext;
use App\Models\CategoryModel;
use App\Models\ServiceImageModel;
use App\Models\ServiceModel;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * White-label storefront (T3): the customer-facing pages served on a vendor's
 * tenant subdomain. Every query is pinned to the tenant vendor resolved by
 * the VendorTenant filter; ownership checks go through
 * TenantContext::assertOwns() rather than inline vendor_id comparisons.
 */
class TenantController extends BaseController
{
    public function home()
    {
        $tenant = $this->requireTenant();

        $serviceModel = new ServiceModel();
        $services     = $serviceModel->publicCatalogue()
            ->where('vendor_id', $tenant->vendorId())
            ->orderBy('id', 'DESC')
            ->findAll();

        $imageModel    = new ServiceImageModel();
        $categoryModel = new CategoryModel();

        foreach ($services as &$service) {
            $service['images'] = $imageModel
                ->where(['service_id' => $service['id'], 'is_primary' => 1])
                ->findAll();
            $service['category_name'] = $categoryModel->getServiceCategoryLabel($service);
        }
        unset($service);

        return view('tenant/home', [
            'site'     => $tenant->site(),
            'vendor'   => $tenant->vendor(),
            'services' => $services,
        ]);
    }

    public function service($id)
    {
        $tenant = $this->requireTenant();

        $service = (new ServiceModel())->getServiceWithImages((int) $id);
        $service = $tenant->assertOwns($service);

        // Only publicly listable services are reachable on the storefront —
        // the same closed rule as ServiceModel::publicCatalogue().
        if (($service['status'] ?? 'active') !== 'active' || ($service['deleted_at'] ?? null) !== null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('tenant/service', [
            'site'         => $tenant->site(),
            'vendor'       => $tenant->vendor(),
            'service'      => $service,
            'categoryName' => (new CategoryModel())->getServiceCategoryLabel($service),
            'pageTitle'    => $service['title'],
        ]);
    }

    /**
     * Tenant pages must never render without a tenant resolved by the
     * VendorTenant filter (e.g. a route misconfiguration) — fail closed.
     */
    private function requireTenant(): TenantContext
    {
        $tenant = service('tenant');
        if (! $tenant->isActive()) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $tenant;
    }
}
