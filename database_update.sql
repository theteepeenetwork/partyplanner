-- ============================================================
-- Event Marketplace — Database Update Script
-- Run this against your existing event_marketplace database
-- All statements are idempotent (safe to run multiple times)
--
-- Column changes use a helper procedure because standard MySQL
-- does not support "ALTER TABLE ... ADD COLUMN IF NOT EXISTS"
-- (that syntax is MariaDB-only). Import/run the whole file.
-- ============================================================

-- ------------------------------------------------------------
-- Portable conditional ADD COLUMN (MySQL has no IF NOT EXISTS on ADD COLUMN)
-- ------------------------------------------------------------
DROP PROCEDURE IF EXISTS `event_marketplace_add_column_if_missing`;
DELIMITER $$
CREATE PROCEDURE `event_marketplace_add_column_if_missing`(
  IN p_table VARCHAR(64),
  IN p_column VARCHAR(64),
  IN p_definition VARCHAR(4096)
)
BEGIN
  DECLARE col_count INT DEFAULT 0;

  SELECT COUNT(*) INTO col_count
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = p_table
    AND COLUMN_NAME = p_column;

  IF col_count = 0 THEN
    SET @evm_sql := CONCAT('ALTER TABLE `', p_table, '` ADD COLUMN ', p_definition);
    PREPARE evm_stmt FROM @evm_sql;
    EXECUTE evm_stmt;
    DEALLOCATE PREPARE evm_stmt;
  END IF;
END$$
DELIMITER ;
-- ============================================================
-- TABLE: categories — add parent_id for nested category tree
-- ============================================================
CALL `event_marketplace_add_column_if_missing`('categories', 'parent_id', '`parent_id` int(11) DEFAULT NULL AFTER `id`');

-- ============================================================
-- TABLE: services — add all columns used by the application
-- ============================================================
CALL `event_marketplace_add_column_if_missing`('services', 'short_description', '`short_description` varchar(500) DEFAULT NULL AFTER `description`');
CALL `event_marketplace_add_column_if_missing`('services', 'category_id', '`category_id` int(11) DEFAULT NULL AFTER `price`');
CALL `event_marketplace_add_column_if_missing`('services', 'subcategory_id', '`subcategory_id` int(11) DEFAULT NULL AFTER `category_id`');
CALL `event_marketplace_add_column_if_missing`('services', 'third_category_id', '`third_category_id` int(11) DEFAULT NULL AFTER `subcategory_id`');
CALL `event_marketplace_add_column_if_missing`('services', 'latitude', '`latitude` decimal(10,8) DEFAULT NULL');
CALL `event_marketplace_add_column_if_missing`('services', 'longitude', '`longitude` decimal(11,8) DEFAULT NULL');
CALL `event_marketplace_add_column_if_missing`('services', 'deleted_at', '`deleted_at` datetime DEFAULT NULL');
CALL `event_marketplace_add_column_if_missing`('services', 'free_coverage_radius', '`free_coverage_radius` int(11) DEFAULT NULL');
CALL `event_marketplace_add_column_if_missing`('services', 'paid_coverage_radius', '`paid_coverage_radius` int(11) DEFAULT NULL');
CALL `event_marketplace_add_column_if_missing`('services', 'travel_fee_per_km', '`travel_fee_per_km` decimal(10,2) DEFAULT NULL');
CALL `event_marketplace_add_column_if_missing`('services', 'cancellation_policy', '`cancellation_policy` text DEFAULT NULL');
CALL `event_marketplace_add_column_if_missing`('services', 'service_tags', '`service_tags` text DEFAULT NULL');
CALL `event_marketplace_add_column_if_missing`('services', 'service_location', '`service_location` varchar(255) DEFAULT NULL');
CALL `event_marketplace_add_column_if_missing`('services', 'all_travel_included', '`all_travel_included` tinyint(1) DEFAULT 0');
CALL `event_marketplace_add_column_if_missing`('services', 'no_travel_limit', '`no_travel_limit` tinyint(1) DEFAULT 0');
CALL `event_marketplace_add_column_if_missing`('services', 'event_types', '`event_types` text DEFAULT NULL');
CALL `event_marketplace_add_column_if_missing`('services', 'commission_percentage', '`commission_percentage` decimal(5,2) DEFAULT NULL');
CALL `event_marketplace_add_column_if_missing`('services', 'license', '`license` varchar(255) DEFAULT NULL');
CALL `event_marketplace_add_column_if_missing`('services', 'attendance_thresholds', '`attendance_thresholds` text DEFAULT NULL');
CALL `event_marketplace_add_column_if_missing`('services', 'max_pitch_fees', '`max_pitch_fees` text DEFAULT NULL');
CALL `event_marketplace_add_column_if_missing`('services', 'status', '`status` varchar(20) DEFAULT ''active''');
CALL `event_marketplace_add_column_if_missing`('services', 'created_at', '`created_at` datetime DEFAULT CURRENT_TIMESTAMP');
CALL `event_marketplace_add_column_if_missing`('services', 'updated_at', '`updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');

-- ============================================================
-- TABLE: events — add all columns for event creation flow
-- ============================================================
CALL `event_marketplace_add_column_if_missing`('events', 'user_id', '`user_id` int(11) DEFAULT NULL AFTER `id`');
-- Legacy schemas may lack `category`; `event_type` uses AFTER `category` so add this first.
CALL `event_marketplace_add_column_if_missing`('events', 'category', '`category` varchar(255) DEFAULT NULL');
CALL `event_marketplace_add_column_if_missing`('events', 'event_type', '`event_type` varchar(100) DEFAULT NULL AFTER `category`');
CALL `event_marketplace_add_column_if_missing`('events', 'guest_count', '`guest_count` int(11) DEFAULT NULL AFTER `event_type`');
CALL `event_marketplace_add_column_if_missing`('events', 'venue_name', '`venue_name` varchar(255) DEFAULT NULL AFTER `location`');
CALL `event_marketplace_add_column_if_missing`('events', 'postcode', '`postcode` varchar(20) DEFAULT NULL AFTER `venue_name`');
CALL `event_marketplace_add_column_if_missing`('events', 'town_city', '`town_city` varchar(255) DEFAULT NULL AFTER `postcode`');
CALL `event_marketplace_add_column_if_missing`('events', 'indoor_outdoor', '`indoor_outdoor` varchar(20) DEFAULT NULL AFTER `town_city`');
CALL `event_marketplace_add_column_if_missing`('events', 'budget_min', '`budget_min` decimal(10,2) DEFAULT NULL AFTER `indoor_outdoor`');
CALL `event_marketplace_add_column_if_missing`('events', 'budget_max', '`budget_max` decimal(10,2) DEFAULT NULL AFTER `budget_min`');
CALL `event_marketplace_add_column_if_missing`('events', 'style_theme', '`style_theme` varchar(255) DEFAULT NULL AFTER `budget_max`');
CALL `event_marketplace_add_column_if_missing`('events', 'notes', '`notes` text DEFAULT NULL AFTER `style_theme`');
CALL `event_marketplace_add_column_if_missing`('events', 'status', '`status` varchar(20) DEFAULT ''active'' AFTER `notes`');
CALL `event_marketplace_add_column_if_missing`('events', 'created_at', '`created_at` datetime DEFAULT CURRENT_TIMESTAMP');
CALL `event_marketplace_add_column_if_missing`('events', 'event_setting', '`event_setting` varchar(20) NOT NULL DEFAULT ''private'' COMMENT ''public vs private pricing path'' AFTER `guest_count`');
CALL `event_marketplace_add_column_if_missing`('events', 'organiser_pitch_fee', '`organiser_pitch_fee` decimal(10,2) DEFAULT NULL COMMENT ''Actual pitch/stand fee for public events'' AFTER `event_setting`');
CALL `event_marketplace_add_column_if_missing`('events', 'latitude', '`latitude` decimal(10,8) DEFAULT NULL AFTER `town_city`');
CALL `event_marketplace_add_column_if_missing`('events', 'longitude', '`longitude` decimal(11,8) DEFAULT NULL AFTER `latitude`');

-- ============================================================
-- TABLE: booking_items — add pricing and package columns
-- ============================================================
CALL `event_marketplace_add_column_if_missing`('booking_items', 'package_name', '`package_name` varchar(255) DEFAULT NULL AFTER `quantity`');
CALL `event_marketplace_add_column_if_missing`('booking_items', 'guest_count', '`guest_count` int(11) DEFAULT NULL AFTER `package_name`');
CALL `event_marketplace_add_column_if_missing`('booking_items', 'price', '`price` decimal(10,2) DEFAULT NULL AFTER `guest_count`');
CALL `event_marketplace_add_column_if_missing`('booking_items', 'created_at', '`created_at` datetime DEFAULT CURRENT_TIMESTAMP');

-- ============================================================
-- TABLE: payments — add payment type and description
-- ============================================================
CALL `event_marketplace_add_column_if_missing`('payments', 'payment_type', '`payment_type` varchar(50) DEFAULT ''deposit'' AFTER `payment_method`');
CALL `event_marketplace_add_column_if_missing`('payments', 'description', '`description` varchar(255) DEFAULT NULL AFTER `payment_type`');

-- ============================================================
-- TABLE: services_private_event_pricing — add pricing_type
-- ============================================================
CALL `event_marketplace_add_column_if_missing`('services_private_event_pricing', 'pricing_type', '`pricing_type` varchar(50) DEFAULT NULL AFTER `service_id`');

-- ============================================================
-- TABLE: services_guest_based_pricing — add required columns
-- ============================================================
CALL `event_marketplace_add_column_if_missing`('services_guest_based_pricing', 'private_event_pricing_id', '`private_event_pricing_id` int(11) DEFAULT NULL AFTER `service_id`');
CALL `event_marketplace_add_column_if_missing`('services_guest_based_pricing', 'min_guest', '`min_guest` int(11) DEFAULT NULL');
CALL `event_marketplace_add_column_if_missing`('services_guest_based_pricing', 'max_guest', '`max_guest` int(11) DEFAULT NULL');
CALL `event_marketplace_add_column_if_missing`('services_guest_based_pricing', 'guest_price', '`guest_price` decimal(10,2) DEFAULT NULL');

-- ============================================================
-- TABLE: services_custom_duration_pricing — add required columns
-- ============================================================
CALL `event_marketplace_add_column_if_missing`('services_custom_duration_pricing', 'private_event_pricing_id', '`private_event_pricing_id` int(11) DEFAULT NULL AFTER `service_id`');
CALL `event_marketplace_add_column_if_missing`('services_custom_duration_pricing', 'duration_type', '`duration_type` varchar(20) DEFAULT NULL');
CALL `event_marketplace_add_column_if_missing`('services_custom_duration_pricing', 'duration', '`duration` int(11) DEFAULT NULL');

-- ============================================================
-- TABLE: services_tiered_packages_pricing — add required columns
-- ============================================================
CALL `event_marketplace_add_column_if_missing`('services_tiered_packages_pricing', 'private_event_pricing_id', '`private_event_pricing_id` int(11) DEFAULT NULL AFTER `service_id`');
CALL `event_marketplace_add_column_if_missing`('services_tiered_packages_pricing', 'package_description', '`package_description` text DEFAULT NULL');
CALL `event_marketplace_add_column_if_missing`('services_tiered_packages_pricing', 'package_price', '`package_price` decimal(10,2) DEFAULT NULL');

-- ============================================================
-- TABLE: services_locations — add coverage and travel columns
-- ============================================================
CALL `event_marketplace_add_column_if_missing`('services_locations', 'service_location', '`service_location` varchar(255) DEFAULT NULL AFTER `service_id`');
CALL `event_marketplace_add_column_if_missing`('services_locations', 'all_travel_included', '`all_travel_included` tinyint(1) DEFAULT 0');
CALL `event_marketplace_add_column_if_missing`('services_locations', 'no_travel_limit', '`no_travel_limit` tinyint(1) DEFAULT 0');
CALL `event_marketplace_add_column_if_missing`('services_locations', 'free_coverage_radius', '`free_coverage_radius` int(11) DEFAULT NULL');
CALL `event_marketplace_add_column_if_missing`('services_locations', 'paid_coverage_radius', '`paid_coverage_radius` int(11) DEFAULT NULL');
CALL `event_marketplace_add_column_if_missing`('services_locations', 'travel_fee_per_km', '`travel_fee_per_km` decimal(10,2) DEFAULT NULL');

-- ============================================================
-- TABLE: services_cancellation_policies — add cancellation_policy column
-- ============================================================
CALL `event_marketplace_add_column_if_missing`('services_cancellation_policies', 'cancellation_policy', '`cancellation_policy` text DEFAULT NULL AFTER `policy`');

-- ============================================================
-- NEW TABLE: service_images
-- ============================================================
CREATE TABLE IF NOT EXISTS `service_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_id` int(11) NOT NULL,
  `image_path` varchar(500) DEFAULT NULL,
  `thumbnail_path` varchar(500) DEFAULT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- NEW TABLE: bookings
-- ============================================================
CREATE TABLE IF NOT EXISTS `bookings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `event_id` int(11) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `payment_intent_id` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- NEW TABLE: booking_items
-- ============================================================
CREATE TABLE IF NOT EXISTS `booking_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `package_name` varchar(255) DEFAULT NULL,
  `guest_count` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- NEW TABLE: payments
-- ============================================================
CREATE TABLE IF NOT EXISTS `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) NOT NULL,
  `payment_intent_id` varchar(255) DEFAULT NULL,
  `payment_status` varchar(50) DEFAULT 'pending',
  `amount_paid` decimal(10,2) DEFAULT NULL,
  `currency` varchar(10) DEFAULT 'gbp',
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_type` varchar(50) DEFAULT 'deposit',
  `description` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- NEW TABLE: carts
-- ============================================================
CREATE TABLE IF NOT EXISTS `carts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `event_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- NEW TABLE: event_basket_items
-- ============================================================
CREATE TABLE IF NOT EXISTS `event_basket_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `package_name` varchar(255) DEFAULT NULL,
  `extras` text DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `deposit_amount` decimal(10,2) DEFAULT NULL,
  `estimated_total` decimal(10,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `quote_breakdown` text DEFAULT NULL COMMENT 'JSON line items for estimated_total',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `event_id` (`event_id`),
  KEY `user_id` (`user_id`),
  KEY `service_id` (`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CALL `event_marketplace_add_column_if_missing`('event_basket_items', 'quote_breakdown', '`quote_breakdown` text DEFAULT NULL COMMENT ''JSON line items for estimated_total'' AFTER `notes`');

-- ============================================================
-- NEW TABLE: chat_rooms
-- ============================================================
CREATE TABLE IF NOT EXISTS `chat_rooms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vendor_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `service_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- NEW TABLE: chat_messages
-- ============================================================
CREATE TABLE IF NOT EXISTS `chat_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `chat_room_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- NEW TABLE: favourites
-- ============================================================
CREATE TABLE IF NOT EXISTS `favourites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_service` (`user_id`, `service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- NEW TABLE: subcategories
-- ============================================================
CREATE TABLE IF NOT EXISTS `subcategories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- NEW TABLE: unavailable_dates
-- ============================================================
CREATE TABLE IF NOT EXISTS `unavailable_dates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_id` int(11) DEFAULT NULL,
  `vendor_id` int(11) DEFAULT NULL,
  `date` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- NEW TABLE: service_availability
-- ============================================================
CREATE TABLE IF NOT EXISTS `service_availability` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_id` int(11) NOT NULL,
  `day_of_week` varchar(20) DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- NEW TABLE: service_time_blocks
-- ============================================================
CREATE TABLE IF NOT EXISTS `service_time_blocks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_id` int(11) NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- NEW TABLE: service_public_event_data
-- ============================================================
CREATE TABLE IF NOT EXISTS `service_public_event_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_id` int(11) NOT NULL,
  `data` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- NEW TABLE: services_public_event_pricing
-- ============================================================
CREATE TABLE IF NOT EXISTS `services_public_event_pricing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_id` int(11) NOT NULL,
  `commission_percentage` decimal(5,2) DEFAULT NULL,
  `min_attendance` int(11) DEFAULT NULL,
  `max_attendance` int(11) DEFAULT NULL,
  `max_pitch_fee` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- NEW TABLE: services_corporate_event_pricing
-- ============================================================
CREATE TABLE IF NOT EXISTS `services_corporate_event_pricing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_id` int(11) NOT NULL,
  `pricing_details` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- NEW TABLE: services_tags
-- ============================================================
CREATE TABLE IF NOT EXISTS `services_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- NEW TABLE: services_service_tags
-- ============================================================
CREATE TABLE IF NOT EXISTS `services_service_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- NEW TABLE: services_event_types
-- ============================================================
CREATE TABLE IF NOT EXISTS `services_event_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_id` int(11) NOT NULL,
  `event_type` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- NEW TABLE: services_optional_extras
-- ============================================================
CREATE TABLE IF NOT EXISTS `services_optional_extras` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CALL `event_marketplace_add_column_if_missing`('services_optional_extras', 'quantity', '`quantity` int(11) DEFAULT 1 AFTER `description`');

-- ============================================================
-- SEED: comprehensive categories (keep in sync with categories_comprehensive.sql)
-- ============================================================
INSERT INTO `categories` (`id`, `parent_id`, `name`) VALUES
(1, NULL, 'Catering & Food'),
(2, NULL, 'Bars & Drinks'),
(3, NULL, 'Cakes & Desserts'),
(4, NULL, 'Photography & Videography'),
(5, NULL, 'Music & DJs'),
(6, NULL, 'Entertainment & Performers'),
(7, NULL, 'Children’s Parties'),
(8, NULL, 'Venues & Accommodation'),
(9, NULL, 'Event Planning & Coordination'),
(10, NULL, 'Decorations & Venue Styling'),
(11, NULL, 'Flowers & Floristry'),
(12, NULL, 'Furniture & Equipment Hire'),
(13, NULL, 'Lighting, Sound & Production'),
(14, NULL, 'Transport'),
(15, NULL, 'Beauty, Hair & Makeup'),
(16, NULL, 'Stationery, Signage & Printing'),
(17, NULL, 'Photo Booths & Interactive Experiences'),
(18, NULL, 'Marquees & Outdoor Events'),
(19, NULL, 'Staffing, Security & Event Support'),
(20, NULL, 'Gifts, Favours & Personalised Items'),
(21, NULL, 'Wellbeing, Experiences & Activities'),
(22, NULL, 'Seasonal & Themed Events'),
(23, NULL, 'Business, Corporate & Brand Events'),
(24, NULL, 'Pets & Animal Experiences'),
(25, NULL, 'Other Services'),
(100, 1, 'Wedding catering'),
(101, 1, 'Corporate catering'),
(102, 1, 'Buffet catering'),
(103, 1, 'Plated meals'),
(104, 1, 'Street food vendors'),
(105, 1, 'Food trucks'),
(106, 1, 'BBQ catering'),
(107, 1, 'Hog roast'),
(108, 1, 'Afternoon tea'),
(109, 1, 'Grazing tables'),
(110, 1, 'Canapés'),
(111, 1, 'Private chefs'),
(112, 1, 'Festival catering'),
(113, 1, 'Mobile pizza'),
(114, 1, 'Fish and chips'),
(115, 1, 'Asian cuisine'),
(116, 1, 'Caribbean cuisine'),
(117, 1, 'Mediterranean cuisine'),
(118, 1, 'Vegan catering'),
(119, 1, 'Halal catering'),
(120, 1, 'Gluten-free catering'),
(200, 2, 'Mobile bars'),
(201, 2, 'Cocktail bars'),
(202, 2, 'Gin bars'),
(203, 2, 'Prosecco vans'),
(204, 2, 'Beer and ale bars'),
(205, 2, 'Wine bars'),
(206, 2, 'Coffee carts'),
(207, 2, 'Tea and hot drinks'),
(208, 2, 'Smoothie and juice bars'),
(209, 2, 'Mocktail bars'),
(210, 2, 'Bar staff'),
(211, 2, 'Drinks packages'),
(212, 2, 'Champagne towers'),
(213, 2, 'Water stations'),
(300, 3, 'Wedding cakes'),
(301, 3, 'Birthday cakes'),
(302, 3, 'Cupcakes'),
(303, 3, 'Dessert tables'),
(304, 3, 'Doughnut walls'),
(305, 3, 'Sweet carts'),
(306, 3, 'Chocolate fountains'),
(307, 3, 'Ice cream vans'),
(308, 3, 'Waffle and crepe stations'),
(309, 3, 'Pick and mix'),
(310, 3, 'Brownies and traybakes'),
(311, 3, 'Macarons'),
(312, 3, 'Cake pops'),
(313, 3, 'Cheesecake towers'),
(400, 4, 'Wedding photography'),
(401, 4, 'Event photography'),
(402, 4, 'Corporate photography'),
(403, 4, 'Brand photography'),
(404, 4, 'Party photography'),
(405, 4, 'Videography'),
(406, 4, 'Wedding videography'),
(407, 4, 'Drone photography'),
(408, 4, 'Drone videography'),
(409, 4, 'Social media content creators'),
(410, 4, 'Live streaming'),
(411, 4, 'Second shooters'),
(412, 4, 'Engagement shoots'),
(413, 4, 'Photo editing and retouching'),
(500, 5, 'Wedding DJs'),
(501, 5, 'Party DJs'),
(502, 5, 'Corporate DJs'),
(503, 5, 'Live bands'),
(504, 5, 'Solo singers'),
(505, 5, 'Acoustic performers'),
(506, 5, 'String quartets'),
(507, 5, 'Saxophonists'),
(508, 5, 'Pianists'),
(509, 5, 'Harpists'),
(510, 5, 'Ceilidh bands'),
(511, 5, 'Tribute acts'),
(512, 5, 'Choirs'),
(513, 5, 'Karaoke'),
(514, 5, 'Silent disco'),
(515, 5, 'Brass bands'),
(600, 6, 'Magicians'),
(601, 6, 'Comedians'),
(602, 6, 'Caricaturists'),
(603, 6, 'Circus performers'),
(604, 6, 'Fire performers'),
(605, 6, 'Dancers'),
(606, 6, 'Singing waiters'),
(607, 6, 'Actors and characters'),
(608, 6, 'Lookalikes'),
(609, 6, 'Drag performers'),
(610, 6, 'Burlesque performers'),
(611, 6, 'Close-up entertainment'),
(612, 6, 'Casino tables'),
(613, 6, 'Murder mystery'),
(614, 6, 'Hypnotists'),
(615, 6, 'Mind readers'),
(616, 6, 'Live event artists'),
(700, 7, 'Bouncy castles'),
(701, 7, 'Soft play'),
(702, 7, 'Mascots'),
(703, 7, 'Face painting'),
(704, 7, 'Glitter tattoos'),
(705, 7, 'Balloon modellers'),
(706, 7, 'Children’s discos'),
(707, 7, 'Party entertainers'),
(708, 7, 'Science parties'),
(709, 7, 'Craft parties'),
(710, 7, 'Princess parties'),
(711, 7, 'Superhero parties'),
(712, 7, 'Gaming parties'),
(713, 7, 'Petting zoos'),
(714, 7, 'Pony parties'),
(715, 7, 'Inflatable obstacle courses'),
(716, 7, 'Mini fairground rides'),
(800, 8, 'Wedding venues'),
(801, 8, 'Party venues'),
(802, 8, 'Corporate venues'),
(803, 8, 'Conference venues'),
(804, 8, 'Outdoor venues'),
(805, 8, 'Barn venues'),
(806, 8, 'Hotel venues'),
(807, 8, 'Community halls'),
(808, 8, 'Sports clubs'),
(809, 8, 'Restaurants and private dining'),
(810, 8, 'Unique venues'),
(811, 8, 'Accommodation'),
(812, 8, 'Glamping'),
(813, 8, 'Group stays'),
(814, 8, 'Venue finding'),
(900, 9, 'Wedding planners'),
(901, 9, 'Event planners'),
(902, 9, 'Corporate event planners'),
(903, 9, 'Party planners'),
(904, 9, 'On-the-day coordination'),
(905, 9, 'Venue styling coordination'),
(906, 9, 'Supplier sourcing'),
(907, 9, 'Budget planning'),
(908, 9, 'Timelines and schedules'),
(909, 9, 'Toastmasters'),
(910, 9, 'Master of ceremonies'),
(911, 9, 'Celebrants'),
(912, 9, 'Wedding consultancy'),
(1000, 10, 'Venue dressing'),
(1001, 10, 'Balloon styling'),
(1002, 10, 'Backdrops'),
(1003, 10, 'Flower walls'),
(1004, 10, 'Sequin walls'),
(1005, 10, 'Table centrepieces'),
(1006, 10, 'Chair covers and sashes'),
(1007, 10, 'Table linen'),
(1008, 10, 'Aisle décor'),
(1009, 10, 'Ceremony arches'),
(1010, 10, 'Themed props'),
(1011, 10, 'Neon signs'),
(1012, 10, 'Illuminated letters'),
(1013, 10, 'LED dance floors'),
(1014, 10, 'Candy carts styling'),
(1015, 10, 'Ceiling drapes'),
(1016, 10, 'Room transformations'),
(1100, 11, 'Wedding florists'),
(1101, 11, 'Bouquets'),
(1102, 11, 'Buttonholes'),
(1103, 11, 'Table flowers'),
(1104, 11, 'Flower arches'),
(1105, 11, 'Funeral flowers'),
(1106, 11, 'Corporate flowers'),
(1107, 11, 'Dried flowers'),
(1108, 11, 'Artificial flowers'),
(1109, 11, 'Flower crowns'),
(1110, 11, 'Floral installations'),
(1111, 11, 'Seasonal arrangements'),
(1200, 12, 'Tables and chairs'),
(1201, 12, 'Chair hire'),
(1202, 12, 'Tableware'),
(1203, 12, 'Glassware'),
(1204, 12, 'Crockery'),
(1205, 12, 'Cutlery'),
(1206, 12, 'Linen hire'),
(1207, 12, 'Lounge furniture'),
(1208, 12, 'Outdoor furniture'),
(1209, 12, 'Dance floors'),
(1210, 12, 'Staging'),
(1211, 12, 'Bars and counters'),
(1212, 12, 'Garden games'),
(1213, 12, 'Heating'),
(1214, 12, 'Generators'),
(1215, 12, 'Toilets'),
(1216, 12, 'Baby and toddler equipment'),
(1217, 12, 'Event décor props'),
(1300, 13, 'Sound systems'),
(1301, 13, 'Lighting hire'),
(1302, 13, 'Uplighting'),
(1303, 13, 'Disco lighting'),
(1304, 13, 'Stage lighting'),
(1305, 13, 'PA systems'),
(1306, 13, 'Microphones'),
(1307, 13, 'Projectors and screens'),
(1308, 13, 'LED screens'),
(1309, 13, 'AV technicians'),
(1310, 13, 'Staging production'),
(1311, 13, 'Live streaming equipment'),
(1312, 13, 'Special effects'),
(1313, 13, 'Cold sparks'),
(1314, 13, 'Confetti cannons'),
(1315, 13, 'Smoke and haze machines'),
(1316, 13, 'Snow machines'),
(1317, 13, 'Projection mapping'),
(1400, 14, 'Wedding cars'),
(1401, 14, 'Classic cars'),
(1402, 14, 'Luxury cars'),
(1403, 14, 'Limousines'),
(1404, 14, 'Party buses'),
(1405, 14, 'Minibuses'),
(1406, 14, 'Coaches'),
(1407, 14, 'Executive transport'),
(1408, 14, 'Horse and carriage'),
(1409, 14, 'Vintage buses'),
(1410, 14, 'Airport transfers'),
(1411, 14, 'Shuttle services'),
(1412, 14, 'Chauffeurs'),
(1413, 14, 'Novelty transport'),
(1500, 15, 'Bridal makeup'),
(1501, 15, 'Bridal hair'),
(1502, 15, 'Party makeup'),
(1503, 15, 'Prom makeup'),
(1504, 15, 'Special occasion makeup'),
(1505, 15, 'Hair styling'),
(1506, 15, 'Barbers'),
(1507, 15, 'Nail technicians'),
(1508, 15, 'Lash technicians'),
(1509, 15, 'Brows'),
(1510, 15, 'Tanning'),
(1511, 15, 'Beauty packages'),
(1512, 15, 'Mobile beauty services'),
(1513, 15, 'Grooming services'),
(1600, 16, 'Wedding invitations'),
(1601, 16, 'Save the dates'),
(1602, 16, 'Menus'),
(1603, 16, 'Place cards'),
(1604, 16, 'Table plans'),
(1605, 16, 'Order of service'),
(1606, 16, 'Welcome signs'),
(1607, 16, 'Banners'),
(1608, 16, 'Business event signage'),
(1609, 16, 'Printed programmes'),
(1610, 16, 'Thank-you cards'),
(1611, 16, 'Personalised stickers'),
(1612, 16, 'Vinyl decals'),
(1613, 16, 'Large format printing'),
(1614, 16, 'Digital invitations'),
(1700, 17, 'Photo booths'),
(1701, 17, 'Magic mirrors'),
(1702, 17, '360 video booths'),
(1703, 17, 'Selfie pods'),
(1704, 17, 'Audio guestbooks'),
(1705, 17, 'Video guestbooks'),
(1706, 17, 'Roaming photo booths'),
(1707, 17, 'Green screen booths'),
(1708, 17, 'GIF booths'),
(1709, 17, 'Interactive games'),
(1710, 17, 'AR experiences'),
(1711, 17, 'Guestbook stations'),
(1800, 18, 'Marquees'),
(1801, 18, 'Stretch tents'),
(1802, 18, 'Tipis'),
(1803, 18, 'Bell tents'),
(1804, 18, 'Gazebos'),
(1805, 18, 'Outdoor flooring'),
(1806, 18, 'Marquee lighting'),
(1807, 18, 'Outdoor heating'),
(1808, 18, 'Outdoor bars'),
(1809, 18, 'Outdoor kitchens'),
(1810, 18, 'Power and generators'),
(1811, 18, 'Portable toilets'),
(1812, 18, 'Fencing and barriers'),
(1813, 18, 'Weather cover'),
(1814, 18, 'Outdoor furniture packages'),
(1900, 19, 'Waiting staff'),
(1901, 19, 'Bar staff'),
(1902, 19, 'Event hosts'),
(1903, 19, 'Security staff'),
(1904, 19, 'Door staff'),
(1905, 19, 'Stewards'),
(1906, 19, 'First aid cover'),
(1907, 19, 'Cleaners'),
(1908, 19, 'Event setup crew'),
(1909, 19, 'Porters'),
(1910, 19, 'Parking attendants'),
(1911, 19, 'Cloakroom staff'),
(1912, 19, 'Technical crew'),
(1913, 19, 'Toilet attendants'),
(1914, 19, 'Waste management'),
(2000, 20, 'Wedding favours'),
(2001, 20, 'Party bags'),
(2002, 20, 'Personalised gifts'),
(2003, 20, 'Corporate gifts'),
(2004, 20, 'Engraved items'),
(2005, 20, 'Printed clothing'),
(2006, 20, 'Personalised glassware'),
(2007, 20, 'Candles'),
(2008, 20, 'Keepsakes'),
(2009, 20, 'Gift hampers'),
(2010, 20, 'Welcome packs'),
(2011, 20, 'Bridesmaid gifts'),
(2012, 20, 'Groomsmen gifts'),
(2013, 20, 'Teacher and school gifts'),
(2100, 21, 'Yoga sessions'),
(2101, 21, 'Wellness workshops'),
(2102, 21, 'Massage therapists'),
(2103, 21, 'Mindfulness sessions'),
(2104, 21, 'Craft workshops'),
(2105, 21, 'Paint and sip'),
(2106, 21, 'Cooking classes'),
(2107, 21, 'Dance classes'),
(2108, 21, 'Team building activities'),
(2109, 21, 'Escape room experiences'),
(2110, 21, 'Sports activities'),
(2111, 21, 'Outdoor adventures'),
(2112, 21, 'Pamper parties'),
(2113, 21, 'Tarot and fortune telling'),
(2200, 22, 'Christmas events'),
(2201, 22, 'Halloween events'),
(2202, 22, 'Easter events'),
(2203, 22, 'New Year events'),
(2204, 22, 'Summer parties'),
(2205, 22, 'Winter wonderland'),
(2206, 22, 'Santa visits'),
(2207, 22, 'Grotto experiences'),
(2208, 22, 'Themed décor'),
(2209, 22, 'Themed performers'),
(2210, 22, 'Festival themes'),
(2211, 22, 'School fairs'),
(2212, 22, 'Proms'),
(2213, 22, 'Graduation events'),
(2300, 23, 'Conference production'),
(2301, 23, 'Exhibition stands'),
(2302, 23, 'Brand activations'),
(2303, 23, 'Product launches'),
(2304, 23, 'Award ceremonies'),
(2305, 23, 'Networking events'),
(2306, 23, 'Team building'),
(2307, 23, 'Corporate hospitality'),
(2308, 23, 'Promotional staff'),
(2309, 23, 'Branded merchandise'),
(2310, 23, 'Step and repeat walls'),
(2311, 23, 'Press walls'),
(2312, 23, 'Registration desks'),
(2313, 23, 'Delegate management'),
(2400, 24, 'Petting zoos'),
(2401, 24, 'Birds of prey'),
(2402, 24, 'Pony rides'),
(2403, 24, 'Alpacas'),
(2404, 24, 'Therapy animals'),
(2405, 24, 'Dog chaperones'),
(2406, 24, 'Wedding pet sitters'),
(2407, 24, 'Animal encounters'),
(2408, 24, 'Mobile farms'),
(2500, 25, 'Bespoke services'),
(2501, 25, 'Not sure / help me choose'),
(2502, 25, 'Other event supplier')
ON DUPLICATE KEY UPDATE `parent_id`=VALUES(`parent_id`), `name`=VALUES(`name`);

-- ============================================================
-- TABLE: users — allow admin role
-- ============================================================
ALTER TABLE users MODIFY COLUMN `role` ENUM('customer','vendor','admin') NOT NULL;

-- ============================================================
-- TABLE: users — password reset (MVP: opaque token + expiry; HTTPS-only)
-- ============================================================
CALL `event_marketplace_add_column_if_missing`('users', 'password_reset_token', '`password_reset_token` varchar(128) DEFAULT NULL');
CALL `event_marketplace_add_column_if_missing`('users', 'password_reset_expires_at', '`password_reset_expires_at` datetime DEFAULT NULL');

-- ============================================================
-- TABLE: cms_pages — editable public pages
-- ============================================================
CREATE TABLE IF NOT EXISTS `cms_pages` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(191) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` varchar(500) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'draft',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: chat_rooms — moderation flag
-- ============================================================
CALL `event_marketplace_add_column_if_missing`('chat_rooms', 'flagged_for_review', '`flagged_for_review` tinyint(1) NOT NULL DEFAULT 0 AFTER `service_id`');

-- ============================================================
-- TABLE: chat_messages — profanity / language moderation
-- ============================================================
CALL `event_marketplace_add_column_if_missing`('chat_messages', 'original_message', '`original_message` text DEFAULT NULL AFTER `message`');
CALL `event_marketplace_add_column_if_missing`('chat_messages', 'moderation_status', '`moderation_status` varchar(20) NOT NULL DEFAULT ''clean'' AFTER `original_message`');
CALL `event_marketplace_add_column_if_missing`('chat_messages', 'admin_note', '`admin_note` text DEFAULT NULL AFTER `moderation_status`');
CALL `event_marketplace_add_column_if_missing`('chat_messages', 'profanity_matches', '`profanity_matches` varchar(500) DEFAULT NULL AFTER `admin_note`');
CALL `event_marketplace_add_column_if_missing`('chat_messages', 'reviewed_by', '`reviewed_by` int(11) DEFAULT NULL AFTER `profanity_matches`');
CALL `event_marketplace_add_column_if_missing`('chat_messages', 'reviewed_at', '`reviewed_at` datetime DEFAULT NULL AFTER `reviewed_by`');

DROP PROCEDURE IF EXISTS `event_marketplace_add_column_if_missing`;

-- ============================================================
-- CMS: default published homepage (editable in Admin → Pages)
-- ============================================================
INSERT INTO `cms_pages` (`slug`, `title`, `content`, `status`, `created_at`, `updated_at`) VALUES
('homepage', 'Welcome', '<p class="lead">Plan your celebration with trusted local vendors.</p><p><em>This block is editable in <strong>Admin → Pages → homepage</strong>.</em></p>', 'published', NOW(), NOW())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `content` = VALUES(`content`), `status` = 'published', `updated_at` = NOW();

-- ============================================================
-- Done! Your database is now up to date.
-- ============================================================
SELECT 'Database update complete!' AS status;
