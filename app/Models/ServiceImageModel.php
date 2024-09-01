<?php

namespace App\Models;

use CodeIgniter\Model;

class ServiceImageModel extends Model
{
    protected $table = 'service_images';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'service_id',
        'image_path',
        'thumbnail_path',
        'is_primary'
    ];

    protected $useTimestamps = true; // Optional: Use this if you want to automatically handle created_at and updated_at fields
}

