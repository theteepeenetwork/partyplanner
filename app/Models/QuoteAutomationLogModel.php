<?php

namespace App\Models;

use CodeIgniter\Model;

class QuoteAutomationLogModel extends Model
{
    protected $table = 'quote_automation_log';
    protected $primaryKey = 'id';
    protected $allowedFields = ['booking_item_id', 'action', 'details'];
}
