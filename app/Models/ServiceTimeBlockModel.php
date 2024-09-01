<?php 
namespace App\Models;

use CodeIgniter\Model;

class ServiceTimeBlockModel extends Model
{
    protected $table = 'service_time_blocks';
    protected $primaryKey = 'id';
    protected $allowedFields = ['service_id', 'time_length', 'created_at', 'updated_at'];
}
