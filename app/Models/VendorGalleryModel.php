<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Storefront "Recent events" gallery photos — a dedicated upload source,
 * distinct from service-card imagery.
 */
class VendorGalleryModel extends Model
{
    protected $table         = 'vendor_site_gallery';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['vendor_id', 'image_path', 'sort_order'];
    protected $useTimestamps = false;

    /**
     * Image paths for a vendor's gallery, oldest first.
     *
     * @return list<array<string,mixed>>
     */
    public function forVendor(int $vendorId): array
    {
        if (! $this->db->tableExists($this->table)) {
            return [];
        }

        return $this->where('vendor_id', $vendorId)
            ->orderBy('sort_order', 'ASC')->orderBy('id', 'ASC')
            ->findAll();
    }
}
