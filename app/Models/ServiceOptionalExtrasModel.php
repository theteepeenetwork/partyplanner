<?php

namespace App\Models;

use CodeIgniter\Model;

class ServiceOptionalExtrasModel extends Model
{
    protected $table = 'services_optional_extras'; // Table name
    protected $primaryKey = 'id';                 // Primary key

    // Define the allowed fields for mass assignment
    protected $allowedFields = [
        'service_id',
        'name',
        'description',
        'price',
        'pricing_type',
        'min_quantity',
        'max_quantity',
        'unit_label',
    ];

    protected $useTimestamps = false;

    // Validation rules for the model
    protected $validationRules = [
        'service_id'   => 'permit_empty|integer',
        'name'         => 'permit_empty|string|max_length[255]',
        'description'  => 'permit_empty|string',
        'price'        => 'permit_empty|decimal|greater_than_equal_to[0]',
        'pricing_type' => 'permit_empty|in_list[flat,per_item]',
        'min_quantity' => 'permit_empty|integer|greater_than_equal_to[1]',
        'max_quantity' => 'permit_empty|integer|greater_than_equal_to[1]',
        'unit_label'   => 'permit_empty|string|max_length[50]',
    ];

    protected $validationMessages = [
        'name' => ['max_length' => 'The name cannot exceed 255 characters.'],
        'price' => [
            'decimal'               => 'Price must be a valid decimal number.',
            'greater_than_equal_to' => 'Price must be 0 or more.',
        ],
    ];

    // Skip validation if set to true
    protected $skipValidation = false;

    // Custom validation logic
    public function optionalExtraValidation(string $field, array $data): bool
    {
        $name = $data['name'] ?? '';
        $description = $data['description'] ?? '';
        $price = $data['price'] ?? '';

        // If any field is filled, ensure all fields are filled
        if (!empty($name) || !empty($description) || !empty($price)) {
            return !empty($name) && !empty($description) && !empty($price);
        }

        return true; // No fields filled, valid
    }
}
