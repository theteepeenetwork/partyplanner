<?php

namespace App\Models;

use CodeIgniter\Model;

class ServiceTimeBlockModel extends Model
{
    protected $table = 'service_time_blocks';
    protected $primaryKey = 'id';
    protected $allowedFields = ['service_id', 'start_time', 'end_time', 'price'];

    /**
     * @return list<array<string,mixed>>
     */
    public function getByServiceId(int $serviceId): array
    {
        return $this->where('service_id', $serviceId)
            ->orderBy('start_time', 'ASC')
            ->findAll();
    }

    /**
     * Replace all time blocks for a service.
     *
     * @param list<array{start_time?: string, end_time?: string, price?: float|string}> $blocks
     */
    public function saveForService(int $serviceId, array $blocks): void
    {
        $this->where('service_id', $serviceId)->delete();

        foreach ($blocks as $block) {
            if (!is_array($block)) {
                continue;
            }
            $start = trim((string) ($block['start_time'] ?? ''));
            $end = trim((string) ($block['end_time'] ?? ''));
            $price = $block['price'] ?? null;
            if ($start === '' || $end === '' || $price === null || $price === '') {
                continue;
            }

            $this->insert([
                'service_id' => $serviceId,
                'start_time' => $start,
                'end_time' => $end,
                'price' => (float) $price,
            ]);
        }
    }
}
