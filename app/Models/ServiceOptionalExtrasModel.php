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
        'quantity', // Add this field
    ];

    protected $useTimestamps = false;

    // Validation rules for the model
    protected $validationRules = [
        'service_id' => 'permit_empty|integer',
        'name' => 'permit_empty|string|max_length[255]',
        'description' => 'permit_empty|string',
        'price' => 'permit_empty|decimal|greater_than_equal_to[0]',
        'quantity' => 'permit_empty|integer|greater_than_equal_to[0]',
    ];

    // Validation messages for custom error handling
    protected $validationMessages = [
        'name' => [
            'max_length' => 'The name cannot exceed 255 characters.',
        ],
        'price' => [
            'decimal' => 'Price must be a valid decimal number.',
            'greater_than_equal_to' => 'Price must be 0 or more.',
        ],
        'quantity' => [
            'integer' => 'Quantity must be a valid integer.',
            'greater_than_equal_to' => 'Quantity must be 0 or more.',
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
