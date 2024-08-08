<?php namespace App\Models;

use CodeIgniter\Model;

class ServiceModel extends Model
{
    protected $table = 'services';
    protected $primaryKey = 'id';
    protected $allowedFields = ['vendor_id', 'title', 'description', 'image', 'price', 'short_description', 'category_id', 'subcategory_id', 'deleted_at'];
}
