<?php

namespace App\Filters;

use App\Libraries\TenantHost;
use App\Models\UserModel;
use App\Models\VendorSiteModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Resolves the vendor tenant for white-label subdomain hosts and fails
 * closed: unknown subdomain, suspended site, or a missing/non-vendor (or
 * unapproved, where vetting exists) account all 404. On the main domain the
 * filter is a no-op — marketplace requests never reach the lookup.
 *
 * On success the site + vendor rows are exposed via service('tenant').
 */
class VendorTenant implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $subdomain = TenantHost::current();
        if ($subdomain === null) {
            return; // marketplace host — bypass entirely
        }

        // findActiveBySubdomain() already excludes suspended sites, so both
        // "unknown subdomain" and "suspended" land here.
        $site = (new VendorSiteModel())->findActiveBySubdomain($subdomain);
        if ($site === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $vendor = (new UserModel())->find((int) $site['vendor_id']);
        if ($vendor === null || ($vendor['role'] ?? '') !== 'vendor') {
            throw PageNotFoundException::forPageNotFound();
        }

        // Forward-compatible with vendor vetting: if the users table carries
        // vendor_status, a non-approved vendor's storefront goes dark too.
        if (array_key_exists('vendor_status', $vendor) && $vendor['vendor_status'] !== 'approved') {
            throw PageNotFoundException::forPageNotFound();
        }

        service('tenant')->activate($site, $vendor);
        // NB: redirects inside tenant controllers must be built from the
        // request Host (TenantContext::url()) — site_url()/redirect() resolve
        // against the marketplace baseURL because the tenant subdomain is not
        // an allowedHostname, so a bare redirect()->to('/x') hops domains.
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
