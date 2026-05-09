<?php

namespace App\Models;

use CodeIgniter\Model;

class TagsModel extends Model
{
    protected $table = 'services_tags'; // Table storing unique tag names
    protected $primaryKey = 'id';

    protected $allowedFields = ['name']; // Only the 'name' field exists
}