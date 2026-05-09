<?php

namespace App\Models;

use CodeIgniter\Model;

class ServiceTagsModel extends Model
{
    protected $table = 'services_service_tags'; // Join table linking services and tags
    protected $primaryKey = 'id';

    protected $allowedFields = ['service_id', 'tag_id']; // Only service_id and tag_id exist
}

