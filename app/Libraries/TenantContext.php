<?php

namespace App\Libraries;

use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * Per-request tenant registry, shared via service('tenant').
 *
 * The VendorTenant filter activates it after resolving the subdomain to an
 * active vendor_sites row + vendor account; controllers and views then read
 * the tenant from here instead of re-parsing the host or passing rows around.
 * On the marketplace it simply stays inactive.
 */
final class TenantContext
{
    private ?array $site   = null;
    private ?array $vendor = null;

    public function activate(array $site, array $vendor): void
    {
        $this->site   = $site;
        $this->vendor = $vendor;
    }

    public function isActive(): bool
    {
        return $this->site !== null && $this->vendor !== null;
    }

    public function site(): ?array
    {
        return $this->site;
    }

    public function vendor(): ?array
    {
        return $this->vendor;
    }

    public function vendorId(): ?int
    {
        return $this->vendor !== null ? (int) $this->vendor['id'] : null;
    }

    public function subdomain(): ?string
    {
        return $this->site['subdomain'] ?? null;
    }

    /**
     * Ownership guard for tenant-reachable records: 404 unless the given row
     * (anything carrying a vendor_id, e.g. a service) belongs to the tenant
     * vendor. Returns the row so lookups can chain through it.
     *
     * @param array|null $record null (lookup missed) also 404s — fail closed
     */
    public function assertOwns(?array $record): array
    {
        if (
            ! $this->isActive()
            || $record === null
            || (int) ($record['vendor_id'] ?? 0) !== $this->vendorId()
        ) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $record;
    }
}
