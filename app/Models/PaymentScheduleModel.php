<?php

namespace App\Models;

use CodeIgniter\Model;

class PaymentScheduleModel extends Model
{
    protected $table = 'payment_schedules';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'booking_id', 'due_date', 'amount', 'status', 'stripe_payment_intent_id', 'paid_at',
    ];
}
