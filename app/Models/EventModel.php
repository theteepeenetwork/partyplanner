<?php

namespace App\Models;

use CodeIgniter\Model;

class EventModel extends Model
{
    protected $table = 'events';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'user_id', 'title', 'description', 'date', 'location',
        'venue_name', 'postcode', 'town_city', 'indoor_outdoor',
        'budget_min', 'budget_max', 'style_theme', 'notes', 'status',
        'category', 'event_type', 'guest_count', 'created_at',
    ];
    protected $useTimestamps = false;
}
