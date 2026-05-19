<?php

namespace App\Libraries;

use App\Models\CategoryModel;
use Config\Database;

class CustomerDashboardRecommendations
{
    /** @var array<int, array{icon: string, border: string, btn: string}> */
    private const ROOT_PRESENTATION = [
        1  => ['icon' => 'fa-utensils', 'border' => 'border-success', 'btn' => 'btn-outline-success'],
        2  => ['icon' => 'fa-birthday-cake', 'border' => 'border-danger', 'btn' => 'btn-outline-danger'],
        3  => ['icon' => 'fa-camera', 'border' => 'border-info', 'btn' => 'btn-outline-info'],
        4  => ['icon' => 'fa-music', 'border' => 'border-warning', 'btn' => 'btn-outline-warning'],
        5  => ['icon' => 'fa-theater-masks', 'border' => 'border-primary', 'btn' => 'btn-outline-primary'],
        6  => ['icon' => 'fa-child', 'border' => 'border-secondary', 'btn' => 'btn-outline-secondary'],
        7  => ['icon' => 'fa-palette', 'border' => 'border-info', 'btn' => 'btn-outline-info'],
        8  => ['icon' => 'fa-seedling', 'border' => 'border-success', 'btn' => 'btn-outline-success'],
        9  => ['icon' => 'fa-chair', 'border' => 'border-secondary', 'btn' => 'btn-outline-secondary'],
        10 => ['icon' => 'fa-lightbulb', 'border' => 'border-warning', 'btn' => 'btn-outline-warning'],
    ];

    private const DEFAULT_PRESENTATION = ['icon' => 'fa-star', 'border' => 'border-primary', 'btn' => 'btn-outline-primary'];

    /**
     * Root categories the customer has not yet booked on any event.
     *
     * @return list<array{id: int, name: string, icon: string, border: string, btn: string, browse_url: string}>
     */
    public static function forUser(int $userId, CategoryModel $categoryModel, int $limit = 3): array
    {
        $bookedRootIds = self::bookedRootCategoryIds($userId);
        $roots         = $categoryModel->getRootCategories();
        $out           = [];

        foreach ($roots as $root) {
            $id = (int) $root['id'];
            if (in_array($id, $bookedRootIds, true)) {
                continue;
            }
            $style = self::ROOT_PRESENTATION[$id] ?? self::DEFAULT_PRESENTATION;
            $out[] = [
                'id'         => $id,
                'name'       => (string) $root['name'],
                'icon'       => $style['icon'],
                'border'     => $style['border'],
                'btn'        => $style['btn'],
                'browse_url' => site_url('browse-services?category=' . $id),
            ];
            if (count($out) >= $limit) {
                break;
            }
        }

        return $out;
    }

    /**
     * @return list<int>
     */
    private static function bookedRootCategoryIds(int $userId): array
    {
        $db = Database::connect();
        if (! $db->tableExists('booking_items') || ! $db->tableExists('services')) {
            return [];
        }

        $rows = $db->table('booking_items')
            ->select('services.category_id')
            ->join('bookings', 'bookings.id = booking_items.booking_id')
            ->join('events', 'events.id = bookings.event_id')
            ->join('services', 'services.id = booking_items.service_id')
            ->where('events.user_id', $userId)
            ->where('services.category_id IS NOT NULL', null, false)
            ->groupBy('services.category_id')
            ->get()
            ->getResultArray();

        $ids = [];
        foreach ($rows as $row) {
            $catId = (int) ($row['category_id'] ?? 0);
            if ($catId > 0) {
                $ids[] = $catId;
            }
        }

        return array_values(array_unique($ids));
    }
}
