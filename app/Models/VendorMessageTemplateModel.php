<?php

namespace App\Models;

use CodeIgniter\Model;

class VendorMessageTemplateModel extends Model
{
    protected $table = 'vendor_message_templates';
    protected $primaryKey = 'id';
    protected $allowedFields = ['vendor_id', 'name', 'body'];
}
