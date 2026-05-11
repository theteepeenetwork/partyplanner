<?php

namespace App\Models;

use CodeIgniter\Model;

class CmsPageModel extends Model
{
    protected $table            = 'cms_pages';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
    protected $allowedFields    = [
        'slug',
        'title',
        'content',
        'meta_title',
        'meta_description',
        'status',
    ];
}
