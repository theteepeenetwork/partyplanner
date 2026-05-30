<?php

namespace App\Database\Seeds;

use CodeIgniter\CLI\CLI;
use CodeIgniter\Database\Seeder;

/**
 * QA seeder: a complete, self-contained data set for browser QA.
 *
 * Creates 2 customers, 2 vendors and 4 active, fully-configured services
 * (one per private pricing model) plus matching demo events so every service
 * is quoteable immediately with a deterministic, documented output.
 *
 * The accounts use the same logins documented in TEST_ACCOUNTS.md.
 *
 * Run with:   php spark db:seed QASeeder
 * Safe to run repeatedly — it removes its own previous rows first.
 *
 * See QA_SEED.md for credentials and expected quote outputs.
 */
class QASeeder extends Seeder
{
    /** Shared password for every seeded account. */
    private const PASSWORD = 'password';

    /** Demo event coordinates (central London). Services cover this for £0 travel. */
    private const LAT = 51.50330000;
    private const LON = -0.11960000;

    /** Seeded account logins (kept in sync with TEST_ACCOUNTS.md). */
    private const CUSTOMERS = [
        ['name' => 'Customer One', 'username' => 'customer1', 'email' => 'customer1@c.com'],
        ['name' => 'Customer Two', 'username' => 'customer2', 'email' => 'customer2@c.com'],
    ];
    private const VENDORS = [
        ['name' => 'Vendor One', 'username' => 'vendor1', 'email' => 'vendor1@v.com'],
        ['name' => 'Vendor Two', 'username' => 'vendor2', 'email' => 'vendor2@v.com'],
    ];

    public function run(): void
    {
        $hash = password_hash(self::PASSWORD, PASSWORD_DEFAULT);

        $this->cleanup();

        // --- Users ----------------------------------------------------------
        $customerIds = [];
        foreach (self::CUSTOMERS as $c) {
            $customerIds[] = $this->insertUser($c, 'customer', $hash);
        }
        $vendorIds = [];
        foreach (self::VENDORS as $v) {
            $vendorIds[] = $this->insertUser($v, 'vendor', $hash);
        }

        // --- Services (one per private pricing model) -----------------------
        // Vendor One owns the guest-based and duration-based services.
        $this->seedGuestBasedService($vendorIds[0], 'uploads/services/1735988119_d83adeb47688289c69a5.jpg');
        $this->seedDurationService($vendorIds[0], 'uploads/services/1736065387_0781e62ad503443dd333.jpg');
        // Vendor Two owns the package-based and quantity-based services.
        $this->seedPackageService($vendorIds[1], 'uploads/services/1736087953_ca438efc6da58afad47d.jpg');
        $this->seedQuantityService($vendorIds[1], 'uploads/services/1736091435_0297b3addf09add66f13.jpg');

        // --- Demo events (carry coordinates so travel resolves to £0) --------
        $this->insertEvent($customerIds[0], 'QA Summer Wedding', 'Wedding', 80, '+90 days');
        $this->insertEvent($customerIds[1], 'QA Birthday Party', 'Birthday', 120, '+120 days');

        if (is_cli()) {
            CLI::write('QA data seeded: '
                . count($customerIds) . ' customers, '
                . count($vendorIds) . ' vendors, 4 services, 2 events.', 'green');
        }
    }

    /**
     * Remove any previously-seeded QA rows so the seeder can be re-run safely.
     */
    private function cleanup(): void
    {
        $emails = array_merge(
            array_column(self::CUSTOMERS, 'email'),
            array_column(self::VENDORS, 'email')
        );

        $userIds = array_column(
            $this->db->table('users')->select('id')->whereIn('email', $emails)->get()->getResultArray(),
            'id'
        );
        if ($userIds === []) {
            return;
        }

        // Service ids owned by the seeded vendors (children removed via service_id).
        $serviceIds = array_column(
            $this->db->table('services')->select('id')->whereIn('vendor_id', $userIds)->get()->getResultArray(),
            'id'
        );

        // Customer-side rows first.
        $this->db->table('event_basket_items')->whereIn('user_id', $userIds)->delete();
        $this->db->table('events')->whereIn('user_id', $userIds)->delete();

        if ($serviceIds !== []) {
            foreach ([
                'service_images', 'services_event_types', 'services_locations',
                'services_optional_extras', 'services_guest_based_pricing',
                'services_custom_duration_pricing', 'services_tiered_packages_pricing',
                'services_quantity_pricing', 'services_private_event_pricing',
            ] as $table) {
                $this->db->table($table)->whereIn('service_id', $serviceIds)->delete();
            }
            $this->db->table('services')->whereIn('id', $serviceIds)->delete();
        }

        $this->db->table('users')->whereIn('id', $userIds)->delete();
    }

    /**
     * @param array{name:string,username:string,email:string} $user
     */
    private function insertUser(array $user, string $role, string $hash): int
    {
        $this->db->table('users')->insert([
            'name'     => $user['name'],
            'username' => $user['username'],
            'email'    => $user['email'],
            'password' => $hash,
            'role'     => $role,
        ]);

        return (int) $this->db->insertID();
    }

    /**
     * Insert the base service row plus its image, event-type and coverage rows,
     * returning the new service id and its private-pricing parent id.
     *
     * @return array{0:int,1:int} [serviceId, privatePricingId]
     */
    private function seedServiceShell(
        int $vendorId,
        string $title,
        string $shortDescription,
        string $description,
        float $basePrice,
        int $categoryId,
        string $imagePath,
        string $pricingType
    ): array {
        $now = date('Y-m-d H:i:s');

        $this->db->table('services')->insert([
            'vendor_id'           => $vendorId,
            'title'               => $title,
            'short_description'   => $shortDescription,
            'description'         => $description,
            'image'               => str_replace('uploads/', '', $imagePath),
            'price'               => $basePrice,
            'category_id'         => $categoryId,
            'service_location'    => 'London',
            'latitude'            => self::LAT,
            'longitude'           => self::LON,
            'event_types'         => 'private',
            'cancellation_policy' => 'Full refund if cancelled at least 14 days before the event date.',
            'status'              => 'active',
            'created_at'          => $now,
            'updated_at'          => $now,
        ]);
        $serviceId = (int) $this->db->insertID();

        // Images (full image reused as the thumbnail; primary first).
        $this->db->table('service_images')->insert([
            'service_id'     => $serviceId,
            'image_path'     => $imagePath,
            'thumbnail_path' => $imagePath,
            'is_primary'     => 1,
        ]);

        // Event-type mapping: available for private events.
        $this->db->table('services_event_types')->insert([
            'service_id' => $serviceId,
            'event_type' => 'private',
        ]);

        // Coverage: real coordinates, generous free radius so the demo events
        // (same coordinates) incur £0 travel with no warnings.
        $this->db->table('services_locations')->insert([
            'service_id'           => $serviceId,
            'fulfillment_type'     => 'in_person',
            'service_location'     => 'London',
            'location'             => 'London',
            'latitude'             => self::LAT,
            'longitude'            => self::LON,
            'all_travel_included'  => 0,
            'no_travel_limit'      => 0,
            'free_coverage_radius' => 80,
            'paid_coverage_radius' => 120,
            'travel_fee_per_km'    => 1.50,
            'strict_travel_radius' => 0,
        ]);

        // Private pricing parent row.
        $this->db->table('services_private_event_pricing')->insert([
            'service_id'   => $serviceId,
            'pricing_type' => $pricingType,
            'price'        => $basePrice,
            'description'  => 'QA seeded ' . $pricingType,
        ]);
        $privatePricingId = (int) $this->db->insertID();

        return [$serviceId, $privatePricingId];
    }

    private function seedGuestBasedService(int $vendorId, string $image): void
    {
        [$serviceId, $pricingId] = $this->seedServiceShell(
            $vendorId,
            'QA Catering Co — Buffet & Grazing',
            'Per-guest catering with tiered pricing by headcount.',
            'Seasonal grazing tables and hot buffets priced per guest. Tiered rates reward larger parties.',
            6.00,
            1, // Catering & Drinks
            $image,
            'guest_based_pricing'
        );

        foreach ([
            ['min' => 1,   'max' => 50,   'price' => 8.00],
            ['min' => 51,  'max' => 150,  'price' => 6.00],
            ['min' => 151, 'max' => 1000, 'price' => 5.00],
        ] as $tier) {
            $this->db->table('services_guest_based_pricing')->insert([
                'service_id'               => $serviceId,
                'private_event_pricing_id' => $pricingId,
                'min_guest'                => $tier['min'],
                'max_guest'                => $tier['max'],
                'guest_price'              => $tier['price'],
            ]);
        }

        $this->addExtra($serviceId, 'Prosecco reception (per guest)', 4.50, 'per_item', 'guests');
    }

    private function seedDurationService(int $vendorId, string $image): void
    {
        [$serviceId, $pricingId] = $this->seedServiceShell(
            $vendorId,
            'QA Snapshot — Photo Booth Hire',
            'Photo booth hire charged by session length.',
            'Open-air photo booth with props, instant prints and an attendant. Choose a session length.',
            250.00,
            11, // Photo Booths & Experiences
            $image,
            'custom_duration_pricing'
        );

        // Inserted cheapest-first so the default (first row) is the 3-hour slot.
        foreach ([
            ['type' => 'hour', 'duration' => 3, 'price' => 250.00],
            ['type' => 'hour', 'duration' => 5, 'price' => 400.00],
            ['type' => 'hour', 'duration' => 8, 'price' => 600.00],
        ] as $row) {
            $this->db->table('services_custom_duration_pricing')->insert([
                'service_id'               => $serviceId,
                'private_event_pricing_id' => $pricingId,
                'duration_type'            => $row['type'],
                'duration'                 => $row['duration'],
                'price'                    => $row['price'],
            ]);
        }

        $this->addExtra($serviceId, 'Guest book album', 35.00, 'flat', null);
    }

    private function seedPackageService(int $vendorId, string $image): void
    {
        [$serviceId, $pricingId] = $this->seedServiceShell(
            $vendorId,
            'QA Grand Marquees — Marquee Packages',
            'Marquee hire in Bronze, Silver and Gold packages.',
            'Weatherproof marquees with flooring, lighting and furniture. Pick the package that fits your event.',
            750.00,
            16, // Marquees & Outdoor Events
            $image,
            'tiered_packages_pricing'
        );

        // Inserted cheapest-first so the default (first row) is the Bronze package.
        foreach ([
            ['name' => 'Bronze', 'desc' => 'Up to 60 guests, basic lighting.',        'price' => 750.00],
            ['name' => 'Silver', 'desc' => 'Up to 120 guests, lighting and flooring.', 'price' => 1200.00],
            ['name' => 'Gold',   'desc' => 'Up to 200 guests, full styling package.',  'price' => 1800.00],
        ] as $pkg) {
            $this->db->table('services_tiered_packages_pricing')->insert([
                'service_id'               => $serviceId,
                'private_event_pricing_id' => $pricingId,
                'package_name'             => $pkg['name'],
                'package_description'      => $pkg['desc'],
                'package_price'            => $pkg['price'],
            ]);
        }

        $this->addExtra($serviceId, 'Festoon lighting upgrade', 150.00, 'flat', null);
    }

    private function seedQuantityService(int $vendorId, string $image): void
    {
        [$serviceId, $pricingId] = $this->seedServiceShell(
            $vendorId,
            'QA Comfort Hire — Event Chair Hire',
            'Chiavari chair hire priced per chair.',
            'Elegant Chiavari chairs delivered, set up and collected. Priced per chair, minimum 50.',
            4.00,
            9, // Furniture & Equipment Hire
            $image,
            'quantity_based_pricing'
        );

        // Single open-ended band (no max) so the default order quantity (the
        // band minimum, 50) does not trip a near-edge warning.
        $this->db->table('services_quantity_pricing')->insert([
            'service_id'               => $serviceId,
            'private_event_pricing_id' => $pricingId,
            'unit_price'               => 4.00,
            'min_quantity'             => 50,
            'max_quantity'             => null,
            'unit_label'               => 'chairs',
        ]);

        $this->addExtra($serviceId, 'Chair sash (per chair)', 1.25, 'per_item', 'chairs');
    }

    private function addExtra(int $serviceId, string $name, float $price, string $pricingType, ?string $unitLabel): void
    {
        $this->db->table('services_optional_extras')->insert([
            'service_id'   => $serviceId,
            'name'         => $name,
            'price'        => $price,
            'pricing_type' => $pricingType,
            'unit_label'   => $unitLabel,
            'description'  => 'Optional add-on (not included in the base quote).',
        ]);
    }

    private function insertEvent(int $customerId, string $title, string $eventType, int $guestCount, string $dateModifier): void
    {
        $this->db->table('events')->insert([
            'user_id'       => $customerId,
            'title'         => $title,
            'event_type'    => $eventType,
            'date'          => date('Y-m-d', strtotime($dateModifier)),
            'guest_count'   => $guestCount,
            'event_setting' => 'private',
            'location'      => 'London, SW1A 1AA',
            'postcode'      => 'SW1A 1AA',
            'town_city'     => 'London',
            'latitude'      => self::LAT,
            'longitude'     => self::LON,
            'status'        => 'active',
            'created_at'    => date('Y-m-d H:i:s'),
        ]);
    }
}
