<?php

namespace App\Controllers;

use App\Libraries\TenantContext;
use App\Models\CategoryModel;
use App\Models\ServiceImageModel;
use App\Models\ServiceModel;
use App\Models\ServiceOptionalExtrasModel;
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
            'trust'    => $this->vendorTrust($tenant->vendorId()),
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

        $extras = (new ServiceOptionalExtrasModel())
            ->where('service_id', (int) $service['id'])
            ->findAll();

        return view('tenant/service', [
            'site'         => $tenant->site(),
            'vendor'       => $tenant->vendor(),
            'service'      => $service,
            'categoryName' => (new CategoryModel())->getServiceCategoryLabel($service),
            'extras'       => $extras,
            'trust'        => $this->vendorTrust($tenant->vendorId(), (int) $service['id']),
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

    /**
     * Social-proof figures for the storefront hero: the vendor's average
     * review rating and a confirmed-bookings count. Read-only aggregates —
     * the same reviews/booking_items tables the marketplace already reads.
     * When $serviceId is given, the booking count is for that service only.
     *
     * @return array{rating: float|null, reviews: int, bookings: int}
     */
    private function vendorTrust(int $vendorId, ?int $serviceId = null): array
    {
        $db = \Config\Database::connect();

        $rating  = null;
        $reviews = 0;
        if ($db->tableExists('reviews')) {
            $row = $db->table('reviews')
                ->select('AVG(rating) AS avg_rating, COUNT(*) AS cnt')
                ->where('vendor_id', $vendorId)
                ->get()->getRowArray();
            if ($row && $row['cnt'] > 0) {
                $rating  = round((float) $row['avg_rating'], 1);
                $reviews = (int) $row['cnt'];
            }
        }

        $bookings = 0;
        if ($db->tableExists('booking_items') && $db->tableExists('services')) {
            $builder = $db->table('booking_items')
                ->join('services', 'services.id = booking_items.service_id')
                ->where('services.vendor_id', $vendorId)
                ->whereIn('booking_items.status', ['accepted', 'confirmed']);
            if ($serviceId !== null) {
                $builder->where('booking_items.service_id', $serviceId);
            }
            $bookings = (int) $builder->countAllResults();
        }

        return ['rating' => $rating, 'reviews' => $reviews, 'bookings' => $bookings];
    }
}
