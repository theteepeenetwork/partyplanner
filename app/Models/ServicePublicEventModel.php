<?php
namespace App\Models;

use CodeIgniter\Model;

class ServicePublicEventModel extends Model
{
    protected $table = 'service_public_event_data';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'service_id',
        'commission_percentage',
        'license',
        'attendance_threshold',
        'max_pitch_fee',
    ];
}