<?php

namespace App\Models;

use CodeIgniter\Model;

class VendorQuoteSettingsModel extends Model
{
    protected $table = 'vendor_quote_settings';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'vendor_id', 'service_id', 'auto_accept_enabled', 'max_auto_accept_amount',
        'require_within_travel_radius', 'min_lead_days', 'allowed_event_settings', 'blackout_respect',
    ];

    /**
     * Service-specific settings override vendor defaults.
     *
     * @return array<string,mixed>|null
     */
    public function resolveForService(int $vendorId, int $serviceId): ?array
    {
        $serviceRow = $this->where('vendor_id', $vendorId)->where('service_id', $serviceId)->first();
        if ($serviceRow) {
            return $serviceRow;
        }

        return $this->where('vendor_id', $vendorId)->where('service_id', null)->first()
            ?: $this->where('vendor_id', $vendorId)->where('service_id', 0)->first();
    }
}
