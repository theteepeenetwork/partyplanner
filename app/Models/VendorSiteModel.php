<?php

namespace App\Models;

use CodeIgniter\Model;

class VendorSiteModel extends Model
{
    protected $table         = 'vendor_sites';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'vendor_id',
        'subdomain',
        'business_name',
        'logo_path',
        'primary_color',
        'secondary_color',
        'about_text',
        'phone',
        'status',
    ];
    protected $useTimestamps = true;

    public const STATUS_ACTIVE    = 'active';
    public const STATUS_SUSPENDED = 'suspended';

    /**
     * The tenant filter's lookup: an *active* site for the given subdomain,
     * or null. Suspended sites are deliberately not returned — tenant
     * resolution fails closed on them.
     */
    public function findActiveBySubdomain(string $subdomain): ?array
    {
        $subdomain = strtolower(trim($subdomain));
        if ($subdomain === '') {
            return null;
        }

        $site = $this->where('subdomain', $subdomain)
            ->where('status', self::STATUS_ACTIVE)
            ->first();

        return $site ?: null;
    }
}
