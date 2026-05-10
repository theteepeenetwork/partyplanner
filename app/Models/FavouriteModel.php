<?php

namespace App\Models;

use CodeIgniter\Model;

class FavouriteModel extends Model
{
    protected $table = 'favourites';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id', 'service_id', 'created_at'];
    protected $useTimestamps = false;
}
