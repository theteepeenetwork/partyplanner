<?php namespace App\Models;

use CodeIgniter\Model;

class EventModel extends Model
{
    protected $table            = 'events';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['user_id', 'title', 'ceremony_type', 'location', 'date'];
}
