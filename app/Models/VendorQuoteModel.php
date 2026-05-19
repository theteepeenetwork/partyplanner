<?php

namespace App\Models;

use CodeIgniter\Model;

class VendorQuoteModel extends Model
{
    protected $table = 'vendor_quotes';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'booking_item_id', 'vendor_id', 'status', 'lines', 'total', 'vendor_notes', 'expires_at',
    ];
}
