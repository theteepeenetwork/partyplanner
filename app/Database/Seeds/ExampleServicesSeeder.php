<?php

namespace App\Database\Seeds;

use CodeIgniter\CLI\CLI;
use CodeIgniter\Database\Seeder;

/**
 * Example services seeder: a broad, realistic data set for search and event QA.
 *
 * Seeds four North East England vendors and ~20 active, fully-configured
 * services spanning many categories, every private pricing model and a range
 * of travel / fulfilment options, plus public-event listings with attendance
 * bands. Two demo customers and three North East events (private + public) are
 * created so every service is immediately searchable and quoteable.
 *
 * Images are copied from the repo's "test images/" directory into
 * public/uploads/services/ at run time (they are reused freely and do not need
 * to match the service — they exist purely for browser testing).
 *
 * Run with:   php spark db:seed ExampleServicesSeeder
 * Safe to run repeatedly — it removes its own previously-seeded rows first and
 * is independent of QASeeder (different accounts, different region).
 */
class ExampleServicesSeeder extends Seeder
{
    /** Shared password for every seeded account. */
    private const PASSWORD = 'password';

    /** Source directory for test images (repo root) and the public destination. */
    private const IMAGE_SRC_DIR  = ROOTPATH . 'test images/';
    private const IMAGE_DEST_DIR = ROOTPATH . 'public/uploads/services/';

    /** North East England vendors (cleanup is scoped to these emails). */
    private const VENDORS = [
        'tyne'    => ['name' => 'Tyne & Wear Events Co',          'username' => 'ne_tyne',    'email' => 'ne.tyneandwear@example.com'],
        'durham'  => ['name' => 'Durham Celebrations',            'username' => 'ne_durham',  'email' => 'ne.durham@example.com'],
        'tees'    => ['name' => 'Teesside Party Hire',            'username' => 'ne_tees',    'email' => 'ne.teesside@example.com'],
        'north'   => ['name' => 'Northumberland Marquees & Styling', 'username' => 'ne_north', 'email' => 'ne.northumberland@example.com'],
    ];

    /** Demo customers (scoped for cleanup). */
    private const CUSTOMERS = [
        ['name' => 'NE Customer One', 'username' => 'ne_customer1', 'email' => 'ne.customer1@example.com'],
        ['name' => 'NE Customer Two', 'username' => 'ne_customer2', 'email' => 'ne.customer2@example.com'],
    ];

    /** North East England town coordinates used to place services and events. */
    private const TOWNS = [
        'Newcastle upon Tyne' => [54.97830000, -1.61780000, 'NE1 1AA'],
        'Gateshead'           => [54.95260000, -1.60330000, 'NE8 1AA'],
        'Sunderland'          => [54.90690000, -1.38380000, 'SR1 1AA'],
        'Durham'              => [54.77610000, -1.57330000, 'DH1 1AA'],
        'Middlesbrough'       => [54.57420000, -1.23500000, 'TS1 1AA'],
        'Darlington'          => [54.52350000, -1.55980000, 'DL1 1AA'],
        'Stockton-on-Tees'    => [54.57050000, -1.31870000, 'TS18 1AA'],
        'South Shields'       => [54.99960000, -1.43260000, 'NE33 1AA'],
        'Morpeth'             => [55.16830000, -1.69120000, 'NE61 1AA'],
        'Hexham'              => [54.97090000, -2.10150000, 'NE46 1AA'],
        'Alnwick'             => [55.41290000, -1.70600000, 'NE66 1AA'],
    ];

    public function run(): void
    {
        $hash = password_hash(self::PASSWORD, PASSWORD_DEFAULT);

        $this->cleanup();

        // --- Users ----------------------------------------------------------
        $vendorIds = [];
        foreach (self::VENDORS as $key => $v) {
            $vendorIds[$key] = $this->insertUser($v, 'vendor', $hash);
        }
        $customerIds = [];
        foreach (self::CUSTOMERS as $c) {
            $customerIds[] = $this->insertUser($c, 'customer', $hash);
        }

        // --- Services -------------------------------------------------------
        $count = 0;
        foreach ($this->serviceDefinitions() as $svc) {
            $svc['vendor_id'] = $vendorIds[$svc['vendor']];
            $this->seedService($svc);
            $count++;
        }

        // --- Demo North East events (carry coordinates for travel resolution)
        $this->insertEvent($customerIds[0], 'Newcastle Quayside Wedding', 'Wedding', 90, '+90 days', 'private', 'Newcastle upon Tyne');
        $this->insertEvent($customerIds[1], 'Durham 30th Birthday Bash', 'Birthday', 60, '+60 days', 'private', 'Durham');
        $this->insertEvent($customerIds[0], 'Sunderland Summer Food Festival', 'Festival', 2000, '+150 days', 'public', 'Sunderland');

        if (is_cli()) {
            CLI::write(
                'Example data seeded: ' . count($vendorIds) . ' NE vendors, '
                . count($customerIds) . ' customers, ' . $count . ' services, 3 events.',
                'green'
            );
        }
    }

    // -----------------------------------------------------------------------
    // Service catalogue
    // -----------------------------------------------------------------------

    /**
     * Every example service. Each entry is fully self-describing: category,
     * event types, pricing model + rows, travel/fulfilment and optional extras.
     *
     * @return array<int,array<string,mixed>>
     */
    private function serviceDefinitions(): array
    {
        return [
            // ============ PUBLIC + PRIVATE listings ============
            [
                'vendor' => 'tyne', 'town' => 'Newcastle upon Tyne',
                'title' => 'Quayside Mobile Cocktail Bar',
                'short' => 'Stylish mobile bar serving cocktails, beers and mocktails.',
                'desc'  => 'A fully-stocked mobile bar with experienced mixologists. Perfect for weddings, parties and festivals across the North East. Choose a hire length and add upgrades.',
                'category' => 1, 'subcategory' => 103, 'third' => 1024,
                'event_types' => ['public', 'private'],
                'base_price' => 450.00,
                'pricing_type' => 'custom_duration_pricing',
                'duration' => [
                    ['type' => 'hour', 'duration' => 3, 'price' => 450.00],
                    ['type' => 'hour', 'duration' => 5, 'price' => 650.00],
                    ['type' => 'hour', 'duration' => 8, 'price' => 950.00],
                ],
                'public' => ['commission' => 12.00, 'bands' => [
                    ['min' => 1, 'max' => 250, 'fee' => 350.00],
                    ['min' => 251, 'max' => 1000, 'fee' => 600.00],
                    ['min' => 1001, 'max' => 5000, 'fee' => 1200.00],
                ]],
                'travel' => ['fulfillment' => 'in_person', 'free' => 25, 'paid' => 60, 'fee_per_km' => 2.00, 'strict' => 0],
                'extras' => [
                    ['Signature cocktail upgrade', 3.50, 'per_item', 'guests', 'Premium cocktail menu, priced per guest.'],
                    ['Copper glassware hire', 120.00, 'flat', null, 'Full set of copper-style serving glassware.'],
                ],
                'images' => ['bar.jpg', 'bar2.jpg'],
            ],
            [
                'vendor' => 'durham', 'town' => 'Durham',
                'title' => 'Northern Soul Jazz Band',
                'short' => 'Live jazz, soul and swing for ceremonies, receptions and events.',
                'desc'  => 'An award-winning live band covering jazz standards, soul classics and modern crowd-pleasers. Configurable from an intimate trio to a full nine-piece with brass.',
                'category' => 4, 'subcategory' => 112, 'third' => null,
                'event_types' => ['public', 'private'],
                'base_price' => 850.00,
                'pricing_type' => 'tiered_packages_pricing',
                'packages' => [
                    ['Trio', 'Vocals, piano and double bass — up to 2 x 45 min sets.', 850.00],
                    ['Quartet', 'Trio plus saxophone — up to 3 x 45 min sets.', 1250.00],
                    ['Full Band', 'Nine-piece with brass section and DJ between sets.', 2200.00],
                ],
                'public' => ['commission' => 10.00, 'bands' => [
                    ['min' => 1, 'max' => 500, 'fee' => 900.00],
                    ['min' => 501, 'max' => 3000, 'fee' => 1500.00],
                ]],
                'travel' => ['fulfillment' => 'in_person', 'all_included' => 1],
                'extras' => [
                    ['Additional 45 minute set', 250.00, 'flat', null, 'Extra live set beyond the package allowance.'],
                    ['Festoon-lit stage backdrop', 180.00, 'flat', null, 'Styled stage backdrop with warm festoon lighting.'],
                ],
                'images' => ['Jazz band.jpg', 'jazz band2.jpg'],
            ],
            [
                'vendor' => 'tyne', 'town' => 'Gateshead',
                'title' => 'Mr Marvello — Strolling Magician',
                'short' => 'Sleight-of-hand and close-up magic that mingles with your guests.',
                'desc'  => 'Jaw-dropping close-up magic and mind-reading performed table to table. Ideal as a drinks-reception ice-breaker or a public-event headline act.',
                'category' => 5, 'subcategory' => 114, 'third' => null,
                'event_types' => ['public', 'private'],
                'base_price' => 300.00,
                'pricing_type' => 'custom_duration_pricing',
                'duration' => [
                    ['type' => 'hour', 'duration' => 1, 'price' => 300.00],
                    ['type' => 'hour', 'duration' => 2, 'price' => 500.00],
                    ['type' => 'hour', 'duration' => 3, 'price' => 650.00],
                ],
                'public' => ['commission' => 8.00, 'bands' => [
                    ['min' => 1, 'max' => 400, 'fee' => 400.00],
                    ['min' => 401, 'max' => 2000, 'fee' => 750.00],
                ]],
                'travel' => ['fulfillment' => 'in_person', 'no_limit' => 1, 'free' => 30, 'fee_per_km' => 1.20],
                'extras' => [
                    ['Bespoke trick with your branding', 95.00, 'flat', null, 'A custom routine featuring your logo or message.'],
                ],
                'images' => ['magician.jpg', 'magician2.jpg', 'magician3.jpg', 'magician4.jpg'],
            ],
            [
                'vendor' => 'tees', 'town' => 'Middlesbrough',
                'title' => 'Tyne Open-Air Cinema',
                'short' => 'Big-screen outdoor cinema hire with sound, seating and screen.',
                'desc'  => 'Turn any space into an open-air cinema. Inflatable screens from 4m to 12m with full PA, projection and optional deckchair seating. Great for private film nights or public screenings.',
                'category' => 20, 'subcategory' => 159, 'third' => null,
                'event_types' => ['public', 'private'],
                'base_price' => 700.00,
                'pricing_type' => 'tiered_packages_pricing',
                'packages' => [
                    ['Garden Screen (4m)', 'Up to 50 viewers, screen, projector and PA.', 700.00],
                    ['Festival Screen (8m)', 'Up to 250 viewers, HD projection and line-array PA.', 1400.00],
                    ['Stadium Screen (12m)', 'Up to 800 viewers, full production and crew.', 2800.00],
                ],
                'public' => ['commission' => 15.00, 'bands' => [
                    ['min' => 1, 'max' => 300, 'fee' => 500.00],
                    ['min' => 301, 'max' => 1500, 'fee' => 1100.00],
                ]],
                'travel' => ['fulfillment' => 'in_person', 'free' => 20, 'paid' => 80, 'fee_per_km' => 2.50, 'strict' => 0],
                'extras' => [
                    ['Gourmet popcorn (per guest)', 2.00, 'per_item', 'guests', 'Freshly popped popcorn served per guest.'],
                    ['Deckchair seating (per chair)', 3.50, 'per_item', 'chairs', 'Striped deckchairs delivered and set out.'],
                ],
                'images' => ['cinema1.jpg', 'cinema2.jpg', 'cinema3.jpg'],
            ],
            [
                'vendor' => 'durham', 'town' => 'Durham',
                'title' => 'Bounce Kingdom Inflatables',
                'short' => 'Bouncy castles and inflatable fun for parties and fetes.',
                'desc'  => 'Safety-tested inflatables for children\'s parties, school fairs and public family days. Fully insured with trained attendants on larger units.',
                'category' => 6, 'subcategory' => 119, 'third' => null,
                'event_types' => ['public', 'private'],
                'base_price' => 120.00,
                'pricing_type' => 'custom_duration_pricing',
                'duration' => [
                    ['type' => 'hour', 'duration' => 4, 'price' => 120.00],
                    ['type' => 'hour', 'duration' => 6, 'price' => 160.00],
                    ['type' => 'day', 'duration' => 1, 'price' => 220.00],
                ],
                'public' => ['commission' => 10.00, 'bands' => [
                    ['min' => 1, 'max' => 500, 'fee' => 250.00],
                    ['min' => 501, 'max' => 2000, 'fee' => 450.00],
                ]],
                'travel' => ['fulfillment' => 'in_person', 'free' => 15, 'paid' => 35, 'fee_per_km' => 1.50, 'strict' => 1],
                'extras' => [
                    ['Trained attendant (per hour)', 18.00, 'per_item', 'hours', 'A trained attendant to supervise the inflatable.'],
                    ['Soft-play add-on', 75.00, 'flat', null, 'Toddler soft-play set delivered alongside the inflatable.'],
                ],
                'images' => ['bouncycastle.jpg', 'bouncycastle2.jpg'],
            ],

            // ============ PUBLIC only (traders) ============
            [
                'vendor' => 'tees', 'town' => 'Stockton-on-Tees',
                'title' => 'Geordie Street Food Co',
                'short' => 'Street-food trader for festivals, markets and public events.',
                'desc'  => 'A converted horsebox slinging loaded fries, gourmet burgers and vegan wraps. Available as a paid pitch at festivals and public events across the region.',
                'category' => 1, 'subcategory' => 101, 'third' => 1021,
                'event_types' => ['public'],
                'base_price' => 0.00,
                'pricing_type' => null,
                'public' => ['commission' => 18.00, 'bands' => [
                    ['min' => 1, 'max' => 1000, 'fee' => 300.00],
                    ['min' => 1001, 'max' => 5000, 'fee' => 650.00],
                    ['min' => 5001, 'max' => 20000, 'fee' => 1400.00],
                ]],
                'travel' => ['fulfillment' => 'in_person', 'free' => 40, 'paid' => 120, 'fee_per_km' => 1.80, 'strict' => 0],
                'extras' => [],
                'images' => ['bar3.jpg'],
            ],
            [
                'vendor' => 'tyne', 'town' => 'South Shields',
                'title' => 'Big Top Funfair Games',
                'short' => 'Hook-a-duck, coconut shy and fairground stalls for public days.',
                'desc'  => 'Traditional fairground game stalls operated by our team. Pay-to-play or pre-paid wristband options for family fun days, school fetes and public festivals.',
                'category' => 20, 'subcategory' => 160, 'third' => null,
                'event_types' => ['public'],
                'base_price' => 0.00,
                'pricing_type' => null,
                'public' => ['commission' => 20.00, 'bands' => [
                    ['min' => 1, 'max' => 800, 'fee' => 200.00],
                    ['min' => 801, 'max' => 4000, 'fee' => 500.00],
                ]],
                'travel' => ['fulfillment' => 'in_person', 'free' => 30, 'paid' => 90, 'fee_per_km' => 1.60, 'strict' => 0],
                'extras' => [],
                'images' => ['games.jpg'],
            ],

            // ============ PRIVATE: guest-based pricing ============
            [
                'vendor' => 'durham', 'town' => 'Durham',
                'title' => 'County Durham Grazing Tables',
                'short' => 'Abundant grazing tables and buffets priced per guest.',
                'desc'  => 'Seasonal grazing tables piled with artisan cheeses, charcuterie, breads and dips. Priced per guest with tiered rates that reward larger parties.',
                'category' => 1, 'subcategory' => 102, 'third' => 1020,
                'event_types' => ['private'],
                'base_price' => 9.00,
                'pricing_type' => 'guest_based_pricing',
                'guest' => [
                    ['min' => 1, 'max' => 40, 'price' => 12.00],
                    ['min' => 41, 'max' => 120, 'price' => 9.00],
                    ['min' => 121, 'max' => 1000, 'price' => 7.50],
                ],
                'travel' => ['fulfillment' => 'in_person', 'free' => 20, 'paid' => 70, 'fee_per_km' => 1.40, 'strict' => 0],
                'extras' => [
                    ['Prosecco reception (per guest)', 4.50, 'per_item', 'guests', 'A glass of prosecco served on arrival, per guest.'],
                    ['Whole baked Camembert wheel', 45.00, 'flat', null, 'A sharing centrepiece of warm baked Camembert.'],
                ],
                'images' => ['ChatGPT Image Jun 1, 2026 at 04_51_08 PM.jpg'],
            ],
            [
                'vendor' => 'tees', 'town' => 'Darlington',
                'title' => 'Belgian Chocolate Fountain Co',
                'short' => 'Flowing chocolate fountains with dippers, priced per guest.',
                'desc'  => 'A showstopping chocolate fountain with a generous spread of fresh fruit, marshmallows and treats for dipping. Attended throughout and priced per guest.',
                'category' => 2, 'subcategory' => 106, 'third' => null,
                'event_types' => ['private'],
                'base_price' => 5.00,
                'pricing_type' => 'guest_based_pricing',
                'guest' => [
                    ['min' => 1, 'max' => 60, 'price' => 6.50],
                    ['min' => 61, 'max' => 150, 'price' => 5.00],
                    ['min' => 151, 'max' => 600, 'price' => 4.25],
                ],
                'travel' => ['fulfillment' => 'in_person', 'free' => 25, 'paid' => 60, 'fee_per_km' => 1.30, 'strict' => 0],
                'extras' => [
                    ['White chocolate second fountain', 150.00, 'flat', null, 'An additional white-chocolate fountain alongside the dark.'],
                ],
                'images' => ['Chocolate fountain.jpg', 'chocolate fountain2.jpg'],
            ],

            // ============ PRIVATE: duration pricing ============
            [
                'vendor' => 'tyne', 'town' => 'Newcastle upon Tyne',
                'title' => 'Vintage Photo Booth Hire',
                'short' => 'Enclosed vintage photo booth with props and instant prints.',
                'desc'  => 'A retro-styled enclosed booth with unlimited instant prints, a prop box and a friendly attendant. Choose a session length to suit your event.',
                'category' => 11, 'subcategory' => 132, 'third' => null,
                'event_types' => ['private'],
                'base_price' => 295.00,
                'pricing_type' => 'custom_duration_pricing',
                'duration' => [
                    ['type' => 'hour', 'duration' => 3, 'price' => 295.00],
                    ['type' => 'hour', 'duration' => 4, 'price' => 350.00],
                    ['type' => 'hour', 'duration' => 6, 'price' => 480.00],
                ],
                'travel' => ['fulfillment' => 'in_person', 'free' => 20, 'paid' => 65, 'fee_per_km' => 1.75, 'strict' => 0],
                'extras' => [
                    ['Leather guest book album', 35.00, 'flat', null, 'A keepsake album of duplicate prints with guest messages.'],
                    ['Extra print copies (per print)', 0.50, 'per_item', 'prints', 'Additional duplicate prints during the session.'],
                ],
                'images' => ['Vintage photobooth.jpg', 'vINTAGE pHOTOBOOTH2.jpg'],
            ],
            [
                'vendor' => 'tyne', 'town' => 'Gateshead',
                'title' => 'Premier Wedding DJ & Disco',
                'short' => 'Professional wedding DJ with full lighting and PA, by the hour.',
                'desc'  => 'An experienced wedding and party DJ with a curated playlist, dancefloor lighting and a crisp sound system. Booked by session length with optional upgrades.',
                'category' => 4, 'subcategory' => 111, 'third' => null,
                'event_types' => ['private'],
                'base_price' => 350.00,
                'pricing_type' => 'custom_duration_pricing',
                'duration' => [
                    ['type' => 'hour', 'duration' => 4, 'price' => 350.00],
                    ['type' => 'hour', 'duration' => 5, 'price' => 425.00],
                    ['type' => 'hour', 'duration' => 7, 'price' => 575.00],
                ],
                'travel' => ['fulfillment' => 'in_person', 'free' => 30, 'paid' => 75, 'fee_per_km' => 1.50, 'strict' => 0],
                'extras' => [
                    ['Sparkular cold-spark machines (pair)', 220.00, 'flat', null, 'Indoor-safe cold-spark fountains for the first dance.'],
                    ['Ceremony & drinks PA set-up', 120.00, 'flat', null, 'Additional discreet PA for the ceremony and reception.'],
                ],
                'images' => ['led2.jpg'],
            ],

            // ============ PRIVATE: tiered packages pricing ============
            [
                'vendor' => 'north', 'town' => 'Morpeth',
                'title' => 'Grand Marquee Hire',
                'short' => 'Weatherproof marquees in Bronze, Silver and Gold packages.',
                'desc'  => 'Elegant clear-span marquees with flooring, lighting and furniture. Pick the package that fits your guest count — we handle delivery, build and take-down.',
                'category' => 17, 'subcategory' => 150, 'third' => null,
                'event_types' => ['private'],
                'base_price' => 950.00,
                'pricing_type' => 'tiered_packages_pricing',
                'packages' => [
                    ['Bronze', 'Up to 60 guests, hard flooring and basic lighting.', 950.00],
                    ['Silver', 'Up to 120 guests, lighting, flooring and furniture.', 1650.00],
                    ['Gold', 'Up to 200 guests, full styling, dancefloor and bar tent.', 2750.00],
                ],
                'travel' => ['fulfillment' => 'in_person', 'free' => 30, 'paid' => 100, 'fee_per_km' => 2.20, 'strict' => 0],
                'extras' => [
                    ['Festoon lighting upgrade', 180.00, 'flat', null, 'Warm festoon lighting throughout the marquee.'],
                    ['Clear roof panels', 350.00, 'flat', null, 'Upgrade to clear roof sections for a starlit feel.'],
                ],
                'images' => ['marquee.jpg', 'marquee2.jpg'],
            ],
            [
                'vendor' => 'tyne', 'town' => 'Sunderland',
                'title' => 'LED Dance Floor Hire',
                'short' => 'Light-up LED dance floors in three sizes.',
                'desc'  => 'A dazzling LED starlit or sequence dancefloor, professionally installed and removed. Choose the floor size to match your venue and guest numbers.',
                'category' => 10, 'subcategory' => 130, 'third' => null,
                'event_types' => ['private'],
                'base_price' => 380.00,
                'pricing_type' => 'tiered_packages_pricing',
                'packages' => [
                    ['12ft x 12ft', 'Up to 60 dancers, white starlit finish.', 380.00],
                    ['16ft x 16ft', 'Up to 120 dancers, starlit or sequence.', 520.00],
                    ['20ft x 20ft', 'Up to 200 dancers, full sequence light show.', 720.00],
                ],
                'travel' => ['fulfillment' => 'in_person', 'free' => 20, 'paid' => 50, 'fee_per_km' => 1.90, 'strict' => 1],
                'extras' => [
                    ['Illuminated initials (per letter)', 45.00, 'per_item', 'letters', 'Light-up initials placed at the edge of the floor.'],
                ],
                'images' => ['LED dancefloor.jpg'],
            ],

            // ============ PRIVATE: quantity / per-item pricing ============
            [
                'vendor' => 'durham', 'town' => 'Durham',
                'title' => 'Chair Cover & Sash Hire',
                'short' => 'Fitted chair covers delivered, fitted and collected, per chair.',
                'desc'  => 'Crisp fitted chair covers with a coloured sash of your choice. Priced per chair with a 30-chair minimum; we fit and collect.',
                'category' => 7, 'subcategory' => 121, 'third' => null,
                'event_types' => ['private'],
                'base_price' => 2.75,
                'pricing_type' => 'quantity_based_pricing',
                'quantity' => ['unit_price' => 2.75, 'min' => 30, 'max' => null, 'unit_label' => 'chairs'],
                'travel' => ['fulfillment' => 'in_person', 'free' => 20, 'paid' => 60, 'fee_per_km' => 1.20, 'strict' => 0],
                'extras' => [
                    ['Coloured sash upgrade (per chair)', 0.75, 'per_item', 'chairs', 'Premium satin or organza sash per chair.'],
                ],
                'images' => ['Chair Covers.jpg', 'chair covers2.jpg'],
            ],
            [
                'vendor' => 'north', 'town' => 'Alnwick',
                'title' => 'Light-Up Letters & Numbers',
                'short' => 'Giant 4ft illuminated letters and numbers, priced per character.',
                'desc'  => 'Make a statement with 4ft warm-white light-up letters and numbers. Priced per character with delivery and set-up across Northumberland and beyond.',
                'category' => 7, 'subcategory' => 122, 'third' => null,
                'event_types' => ['private'],
                'base_price' => 45.00,
                'pricing_type' => 'quantity_based_pricing',
                'quantity' => ['unit_price' => 45.00, 'min' => 2, 'max' => 12, 'unit_label' => 'characters'],
                'travel' => ['fulfillment' => 'in_person', 'free' => 25, 'paid' => 80, 'fee_per_km' => 1.60, 'strict' => 0],
                'extras' => [
                    ['Colour-changing bulbs upgrade', 60.00, 'flat', null, 'Switch from warm white to remote-controlled colour bulbs.'],
                ],
                'images' => ['lettering.jpg', 'lettering2.jpg'],
            ],
            [
                'vendor' => 'tees', 'town' => 'Middlesbrough',
                'title' => 'Personalised Wedding Favours',
                'short' => 'Bespoke favours posted UK-wide, priced per favour.',
                'desc'  => 'Hand-finished personalised favours — seed cards, mini candles or sweet jars — printed with your names and date. Posted directly to you, priced per favour.',
                'category' => 15, 'subcategory' => 144, 'third' => null,
                'event_types' => ['private'],
                'base_price' => 1.80,
                'pricing_type' => 'quantity_based_pricing',
                'quantity' => ['unit_price' => 1.80, 'min' => 25, 'max' => null, 'unit_label' => 'favours'],
                'travel' => [
                    'fulfillment' => 'postal',
                    'postal_fee' => 6.95, 'free_postage_above' => 150.00, 'lead_time' => 10,
                ],
                'extras' => [
                    ['Gift box upgrade (per favour)', 0.60, 'per_item', 'favours', 'Kraft gift box with ribbon, per favour.'],
                ],
                'images' => ['ChatGPT Image Jun 1, 2026 at 04_51_08 PM.jpg'],
            ],
            [
                'vendor' => 'tyne', 'town' => 'Newcastle upon Tyne',
                'title' => 'Photo Booth Prop Packs',
                'short' => 'Themed prop packs to hire or have posted, priced per pack.',
                'desc'  => 'Curated photo-prop packs — signs, glasses, hats and boas — by theme. Collect in person around Tyneside or have them posted, priced per pack.',
                'category' => 11, 'subcategory' => 134, 'third' => null,
                'event_types' => ['private'],
                'base_price' => 15.00,
                'pricing_type' => 'quantity_based_pricing',
                'quantity' => ['unit_price' => 15.00, 'min' => 1, 'max' => 20, 'unit_label' => 'packs'],
                'travel' => [
                    'fulfillment' => 'both',
                    'free' => 15, 'paid' => 40, 'fee_per_km' => 1.00, 'strict' => 0,
                    'postal_fee' => 4.50, 'free_postage_above' => 80.00, 'lead_time' => 5,
                ],
                'extras' => [
                    ['Custom hashtag sign', 18.00, 'flat', null, 'A printed sign featuring your event hashtag.'],
                ],
                'images' => ['photo props.jpg'],
            ],

            // ============ PRIVATE: custom quote (price on request) ============
            [
                'vendor' => 'north', 'town' => 'Alnwick',
                'title' => 'Classic Wedding Car Hire',
                'short' => 'Chauffeured vintage and classic wedding cars — quoted per booking.',
                'desc'  => 'A hand-picked fleet of vintage and classic cars with a uniformed chauffeur. Every wedding is different, so we quote each booking individually based on cars, mileage and timings.',
                'category' => 12, 'subcategory' => 135, 'third' => null,
                'event_types' => ['private'],
                'base_price' => 0.00,
                'pricing_type' => 'custom_quote',
                'travel' => ['fulfillment' => 'in_person', 'no_limit' => 1, 'free' => 40, 'fee_per_km' => 1.50],
                'extras' => [
                    ['Ribbon & bow styling', 35.00, 'flat', null, 'Coordinated ribbons and bows in your colours.'],
                ],
                'images' => ['Car.jpg', 'Car2.jpg'],
            ],
            [
                'vendor' => 'north', 'town' => 'Hexham',
                'title' => 'Bespoke Luxury Event Styling',
                'short' => 'Full design and styling for weddings and events — price on request.',
                'desc'  => 'From mood-board to install, our stylists craft a complete look — backdrops, tablescapes, florals and signage. Bespoke by nature, so each project is quoted to your brief.',
                'category' => 7, 'subcategory' => 120, 'third' => null,
                'event_types' => ['private'],
                'base_price' => 0.00,
                'pricing_type' => 'custom_quote',
                'travel' => ['fulfillment' => 'in_person', 'free' => 30, 'paid' => 120, 'fee_per_km' => 2.00, 'strict' => 0],
                'extras' => [
                    ['On-the-day styling assistant', 180.00, 'flat', null, 'A stylist on hand throughout the event day.'],
                ],
                'images' => ['lettering2.jpg'],
            ],
            [
                'vendor' => 'tyne', 'town' => 'Newcastle upon Tyne',
                'title' => 'Full-Service Event Production',
                'short' => 'Staging, sound, lighting and AV production — quoted bespoke.',
                'desc'  => 'Technical production for conferences, awards nights and large private events: staging, line-array sound, intelligent lighting and screens. Scoped and quoted per event.',
                'category' => 18, 'subcategory' => 155, 'third' => null,
                'event_types' => ['private', 'corporate'],
                'base_price' => 0.00,
                'pricing_type' => 'custom_quote',
                'travel' => ['fulfillment' => 'in_person', 'all_included' => 1],
                'extras' => [
                    ['Show caller / event director', 450.00, 'flat', null, 'A dedicated show caller to run the running order.'],
                ],
                'images' => ['LED dancefloor.jpg'],
            ],
        ];
    }

    // -----------------------------------------------------------------------
    // Insert helpers
    // -----------------------------------------------------------------------

    /**
     * Remove any previously-seeded rows so the seeder can be re-run safely.
     */
    private function cleanup(): void
    {
        $emails = array_merge(
            array_column(self::VENDORS, 'email'),
            array_column(self::CUSTOMERS, 'email')
        );

        $userIds = array_column(
            $this->db->table('users')->select('id')->whereIn('email', $emails)->get()->getResultArray(),
            'id'
        );
        if ($userIds === []) {
            return;
        }

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
                'services_public_event_pricing',
            ] as $table) {
                if ($this->db->tableExists($table)) {
                    $this->db->table($table)->whereIn('service_id', $serviceIds)->delete();
                }
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
     * Insert a complete service: base row, images, event types, location,
     * pricing (private and/or public) and optional extras.
     *
     * @param array<string,mixed> $svc
     */
    private function seedService(array $svc): void
    {
        $now  = date('Y-m-d H:i:s');
        [$lat, $lon] = self::TOWNS[$svc['town']];
        $eventTypes  = $svc['event_types'];
        $travel      = $svc['travel'];

        // Copy and resolve the primary image first (used for services.image).
        $imagePaths = [];
        foreach ($svc['images'] as $src) {
            $path = $this->copyImage($src);
            if ($path !== null) {
                $imagePaths[] = $path;
            }
        }
        $primary = $imagePaths[0] ?? null;

        $this->db->table('services')->insert([
            'vendor_id'           => $svc['vendor_id'],
            'title'               => $svc['title'],
            'short_description'   => $svc['short'],
            'description'         => $svc['desc'],
            'image'               => $primary !== null ? str_replace('uploads/', '', $primary) : null,
            'price'               => $svc['base_price'],
            'category_id'         => $svc['category'],
            'subcategory_id'      => $svc['subcategory'] ?? null,
            'third_category_id'   => $svc['third'] ?? null,
            'service_location'    => $svc['town'],
            'latitude'            => $lat,
            'longitude'           => $lon,
            'event_types'         => implode(',', $eventTypes),
            'free_coverage_radius' => $travel['free'] ?? null,
            'paid_coverage_radius' => $travel['paid'] ?? null,
            'travel_fee_per_km'   => $travel['fee_per_km'] ?? null,
            'all_travel_included' => $travel['all_included'] ?? 0,
            'no_travel_limit'     => $travel['no_limit'] ?? 0,
            'commission_percentage' => $svc['public']['commission'] ?? null,
            'cancellation_policy' => 'Full refund if cancelled at least 14 days before the event date.',
            'status'              => 'active',
            'created_at'          => $now,
            'updated_at'          => $now,
        ]);
        $serviceId = (int) $this->db->insertID();

        // Images (gallery; first is primary, image reused as its own thumbnail).
        foreach ($imagePaths as $i => $path) {
            $this->db->table('service_images')->insert([
                'service_id'     => $serviceId,
                'image_path'     => $path,
                'thumbnail_path' => $path,
                'is_primary'     => $i === 0 ? 1 : 0,
            ]);
        }

        // Event-type opt-in rows (drives search filtering).
        foreach ($eventTypes as $type) {
            $this->db->table('services_event_types')->insert([
                'service_id' => $serviceId,
                'event_type' => $type,
            ]);
        }

        $this->seedLocation($serviceId, $svc['town'], $lat, $lon, $travel);

        if (in_array('private', $eventTypes, true) && !empty($svc['pricing_type'])) {
            $this->seedPrivatePricing($serviceId, $svc);
        }

        if (in_array('public', $eventTypes, true) && !empty($svc['public'])) {
            $this->seedPublicPricing($serviceId, $svc['public']);
        }

        foreach ($svc['extras'] as $extra) {
            $this->addExtra($serviceId, $extra[0], $extra[1], $extra[2], $extra[3], $extra[4]);
        }
    }

    /**
     * @param array<string,mixed> $travel
     */
    private function seedLocation(int $serviceId, string $town, float $lat, float $lon, array $travel): void
    {
        $this->db->table('services_locations')->insert([
            'service_id'            => $serviceId,
            'fulfillment_type'      => $travel['fulfillment'] ?? 'in_person',
            'service_location'      => $town,
            'location'              => $town,
            'latitude'              => $lat,
            'longitude'             => $lon,
            'all_travel_included'   => $travel['all_included'] ?? 0,
            'no_travel_limit'       => $travel['no_limit'] ?? 0,
            'free_coverage_radius'  => $travel['free'] ?? null,
            'paid_coverage_radius'  => $travel['paid'] ?? null,
            'travel_fee_per_km'     => $travel['fee_per_km'] ?? null,
            'strict_travel_radius'  => $travel['strict'] ?? 0,
            'postal_fee'            => $travel['postal_fee'] ?? null,
            'free_postage_above'    => $travel['free_postage_above'] ?? null,
            'delivery_lead_time_days' => $travel['lead_time'] ?? null,
        ]);
    }

    /**
     * @param array<string,mixed> $svc
     */
    private function seedPrivatePricing(int $serviceId, array $svc): void
    {
        $this->db->table('services_private_event_pricing')->insert([
            'service_id'   => $serviceId,
            'pricing_type' => $svc['pricing_type'],
            'price'        => $svc['base_price'] > 0 ? $svc['base_price'] : null,
            'description'  => 'Example seeded ' . $svc['pricing_type'],
        ]);
        $pricingId = (int) $this->db->insertID();

        switch ($svc['pricing_type']) {
            case 'guest_based_pricing':
                foreach ($svc['guest'] as $tier) {
                    $this->db->table('services_guest_based_pricing')->insert([
                        'service_id'               => $serviceId,
                        'private_event_pricing_id' => $pricingId,
                        'min_guest'                => $tier['min'],
                        'max_guest'                => $tier['max'],
                        'guest_price'              => $tier['price'],
                    ]);
                }
                break;

            case 'custom_duration_pricing':
                foreach ($svc['duration'] as $row) {
                    $this->db->table('services_custom_duration_pricing')->insert([
                        'service_id'               => $serviceId,
                        'private_event_pricing_id' => $pricingId,
                        'duration_type'            => $row['type'],
                        'duration'                 => $row['duration'],
                        'price'                    => $row['price'],
                    ]);
                }
                break;

            case 'tiered_packages_pricing':
                foreach ($svc['packages'] as $pkg) {
                    $this->db->table('services_tiered_packages_pricing')->insert([
                        'service_id'               => $serviceId,
                        'private_event_pricing_id' => $pricingId,
                        'package_name'             => $pkg[0],
                        'package_description'      => $pkg[1],
                        'package_price'            => $pkg[2],
                    ]);
                }
                break;

            case 'quantity_based_pricing':
                $q = $svc['quantity'];
                $this->db->table('services_quantity_pricing')->insert([
                    'service_id'               => $serviceId,
                    'private_event_pricing_id' => $pricingId,
                    'unit_price'               => $q['unit_price'],
                    'min_quantity'             => $q['min'],
                    'max_quantity'             => $q['max'],
                    'unit_label'               => $q['unit_label'],
                ]);
                break;

            case 'custom_quote':
                // Price on request — the parent row alone signals the model;
                // no structured child rows are required.
                break;
        }
    }

    /**
     * @param array{commission:float,bands:array<int,array{min:int,max:int,fee:float}>} $public
     */
    private function seedPublicPricing(int $serviceId, array $public): void
    {
        foreach ($public['bands'] as $band) {
            $this->db->table('services_public_event_pricing')->insert([
                'service_id'            => $serviceId,
                'commission_percentage' => $public['commission'] ?? null,
                'min_attendance'        => $band['min'],
                'max_attendance'        => $band['max'],
                'max_pitch_fee'         => $band['fee'],
            ]);
        }
    }

    private function addExtra(int $serviceId, string $name, float $price, string $pricingType, ?string $unitLabel, string $description): void
    {
        $this->db->table('services_optional_extras')->insert([
            'service_id'   => $serviceId,
            'name'         => $name,
            'price'        => $price,
            'pricing_type' => $pricingType,
            'unit_label'   => $unitLabel,
            'description'  => $description,
        ]);
    }

    private function insertEvent(int $customerId, string $title, string $eventType, int $guestCount, string $dateModifier, string $setting, string $town): void
    {
        [$lat, $lon, $postcode] = self::TOWNS[$town];

        $this->db->table('events')->insert([
            'user_id'       => $customerId,
            'title'         => $title,
            'event_type'    => $eventType,
            'date'          => date('Y-m-d', strtotime($dateModifier)),
            'guest_count'   => $guestCount,
            'event_setting' => $setting,
            'location'      => $town . ', ' . $postcode,
            'postcode'      => $postcode,
            'town_city'     => $town,
            'latitude'      => $lat,
            'longitude'     => $lon,
            'status'        => 'active',
            'created_at'    => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Copy a test image into public/uploads/services/ with a stable, unique
     * name and return its public-relative path (uploads/services/...), or null
     * if the source image is missing.
     */
    private function copyImage(string $sourceName): ?string
    {
        $source = self::IMAGE_SRC_DIR . $sourceName;
        if (!is_file($source)) {
            if (is_cli()) {
                CLI::write('  ! missing test image: ' . $sourceName, 'yellow');
            }

            return null;
        }

        // Deterministic destination name so re-runs overwrite rather than pile up.
        $ext  = pathinfo($sourceName, PATHINFO_EXTENSION) ?: 'jpg';
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '_', pathinfo($sourceName, PATHINFO_FILENAME)));
        $dest = 'example_' . trim($slug, '_') . '.' . $ext;

        if (!is_dir(self::IMAGE_DEST_DIR)) {
            @mkdir(self::IMAGE_DEST_DIR, 0775, true);
        }
        @copy($source, self::IMAGE_DEST_DIR . $dest);

        return 'uploads/services/' . $dest;
    }
}
