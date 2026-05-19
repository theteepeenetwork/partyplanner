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
-- SEED: comprehensive categories (3-level; sync with categories_comprehensive.sql)
-- ============================================================
INSERT INTO `categories` (`id`, `parent_id`, `name`) VALUES
(1, NULL, 'Catering & Drinks'),
(2, NULL, 'Cakes & Desserts'),
(3, NULL, 'Photography & Videography'),
(4, NULL, 'Music & DJs'),
(5, NULL, 'Entertainment'),
(6, NULL, 'Children’s Parties'),
(7, NULL, 'Decorations & Styling'),
(8, NULL, 'Flowers & Plants'),
(9, NULL, 'Furniture & Equipment Hire'),
(10, NULL, 'Lighting & Special Effects'),
(11, NULL, 'Photo Booths & Experiences'),
(12, NULL, 'Transport'),
(13, NULL, 'Beauty & Personal Care'),
(14, NULL, 'Stationery & Printing'),
(15, NULL, 'Gifts & Favours'),
(16, NULL, 'Event Planning & Support'),
(17, NULL, 'Marquees & Outdoor Events'),
(18, NULL, 'Audio Visual & Production'),
(19, NULL, 'Venue Hire'),
(20, NULL, 'Activities & Experiences'),
(21, NULL, 'Staffing & Security'),
(22, NULL, 'Accommodation & Travel Support'),
(23, NULL, 'Other Services'),
(100, 1, 'Full-service catering'),
(101, 1, 'Mobile food vendors'),
(102, 1, 'Outdoor & casual catering'),
(103, 1, 'Drinks & bars'),
(104, 1, 'Specialist food'),
(105, 2, 'Cakes'),
(106, 2, 'Dessert services'),
(107, 2, 'Personalised treats'),
(108, 3, 'Photography'),
(109, 3, 'Videography'),
(110, 3, 'Specialist imaging'),
(111, 4, 'DJs'),
(112, 4, 'Live music'),
(113, 4, 'Specialist music'),
(114, 5, 'Performers'),
(115, 5, 'Interactive entertainment'),
(116, 5, 'Shows & acts'),
(117, 6, 'Children’s entertainers'),
(118, 6, 'Activities'),
(119, 6, 'Play hire'),
(120, 7, 'Venue styling'),
(121, 7, 'Decor hire'),
(122, 7, 'Signage & statement pieces'),
(123, 8, 'Floristry'),
(124, 8, 'Floral installations'),
(125, 8, 'Plant hire'),
(126, 9, 'Furniture'),
(127, 9, 'Tableware'),
(128, 9, 'Event equipment'),
(129, 10, 'Lighting'),
(130, 10, 'Dance floors'),
(131, 10, 'Special effects'),
(132, 11, 'Photo booths'),
(133, 11, 'Video booths'),
(134, 11, 'Guest experiences'),
(135, 12, 'Wedding transport'),
(136, 12, 'Guest transport'),
(137, 12, 'Specialist transport'),
(138, 13, 'Makeup'),
(139, 13, 'Hair'),
(140, 13, 'Wellbeing'),
(141, 14, 'Invitations'),
(142, 14, 'On-the-day stationery'),
(143, 14, 'Printed materials'),
(144, 15, 'Guest favours'),
(145, 15, 'Personalised gifts'),
(146, 15, 'Corporate gifts'),
(147, 16, 'Planning'),
(148, 16, 'Operational support'),
(149, 16, 'Admin services'),
(150, 17, 'Structures'),
(151, 17, 'Outdoor setup'),
(152, 17, 'Weather support'),
(153, 18, 'Sound'),
(154, 18, 'Visuals'),
(155, 18, 'Production'),
(156, 19, 'Traditional venues'),
(157, 19, 'Corporate venues'),
(158, 19, 'Unusual venues'),
(159, 20, 'Creative activities'),
(160, 20, 'Competitive activities'),
(161, 20, 'Animal experiences'),
(162, 21, 'Event staff'),
(163, 21, 'Security'),
(164, 21, 'Specialist staff'),
(165, 22, 'Accommodation'),
(166, 22, 'Travel support'),
(167, 23, 'Specialist services'),
(168, 23, 'Digital services'),
(169, 23, 'Bespoke services'),
(1000, 100, 'Wedding catering'),
(1001, 100, 'Corporate catering'),
(1002, 100, 'Private dining'),
(1003, 100, 'Formal plated meals'),
(1004, 100, 'Family-style sharing meals'),
(1005, 100, 'Buffet catering'),
(1006, 100, 'Canapé receptions'),
(1007, 100, 'Funeral catering'),
(1008, 101, 'Food trucks'),
(1009, 101, 'Burger vans'),
(1010, 101, 'Pizza vans'),
(1011, 101, 'Fish and chip vans'),
(1012, 101, 'Taco stalls'),
(1013, 101, 'Greek food stalls'),
(1014, 101, 'Asian street food stalls'),
(1015, 101, 'Caribbean food stalls'),
(1016, 102, 'BBQ catering'),
(1017, 102, 'Hog roast'),
(1018, 102, 'Paella catering'),
(1019, 102, 'Picnic catering'),
(1020, 102, 'Grazing tables'),
(1021, 102, 'Street food stalls'),
(1022, 102, 'Festival catering'),
(1023, 103, 'Mobile bars'),
(1024, 103, 'Cocktail bars'),
(1025, 103, 'Mocktail bars'),
(1026, 103, 'Coffee vans'),
(1027, 103, 'Prosecco vans'),
(1028, 103, 'Gin bars'),
(1029, 103, 'Beer bars'),
(1030, 103, 'Champagne service'),
(1031, 104, 'Afternoon tea'),
(1032, 104, 'Breakfast catering'),
(1033, 104, 'Brunch catering'),
(1034, 104, 'Dessert tables'),
(1035, 104, 'Sweet carts'),
(1036, 104, 'Ice cream vans'),
(1037, 104, 'Popcorn carts'),
(1038, 104, 'Doughnut walls'),
(1039, 105, 'Wedding cakes'),
(1040, 105, 'Birthday cakes'),
(1041, 105, 'Christening cakes'),
(1042, 105, 'Corporate cakes'),
(1043, 105, 'Cupcake towers'),
(1044, 105, 'Cake pops'),
(1045, 105, 'Tray bakes'),
(1046, 106, 'Dessert tables'),
(1047, 106, 'Macaron towers'),
(1048, 106, 'Chocolate fountains'),
(1049, 106, 'Waffle stations'),
(1050, 106, 'Crepe stations'),
(1051, 106, 'Ice cream stations'),
(1052, 106, 'Sweet buffets'),
(1053, 107, 'Personalised biscuits'),
(1054, 107, 'Branded cupcakes'),
(1055, 107, 'Wedding favours'),
(1056, 107, 'Edible prints'),
(1057, 107, 'Children’s party treats'),
(1058, 108, 'Wedding photography'),
(1059, 108, 'Party photography'),
(1060, 108, 'Corporate photography'),
(1061, 108, 'Event photography'),
(1062, 108, 'Family photography'),
(1063, 108, 'School prom photography'),
(1064, 108, 'Sports event photography'),
(1065, 109, 'Wedding videography'),
(1066, 109, 'Event videography'),
(1067, 109, 'Corporate videography'),
(1068, 109, 'Highlight films'),
(1069, 109, 'Full ceremony filming'),
(1070, 109, 'Social media reels'),
(1071, 109, 'Live streaming'),
(1072, 110, 'Drone photography'),
(1073, 110, 'Drone videography'),
(1074, 110, 'Content creators'),
(1075, 110, 'Same-day edits'),
(1076, 110, 'Photo printing stations'),
(1077, 110, 'Roaming photographers'),
(1078, 111, 'Wedding DJs'),
(1079, 111, 'Party DJs'),
(1080, 111, 'Corporate DJs'),
(1081, 111, 'Silent disco DJs'),
(1082, 111, 'Children’s DJs'),
(1083, 111, 'Club-style DJs'),
(1084, 111, 'Karaoke DJs'),
(1085, 112, 'Function bands'),
(1086, 112, 'Wedding bands'),
(1087, 112, 'Solo singers'),
(1088, 112, 'Acoustic performers'),
(1089, 112, 'String quartets'),
(1090, 112, 'Saxophonists'),
(1091, 112, 'Pianists'),
(1092, 112, 'Harpists'),
(1093, 113, 'Ceilidh bands'),
(1094, 113, 'Tribute acts'),
(1095, 113, 'Choirs'),
(1096, 113, 'Brass bands'),
(1097, 113, 'Mariachi bands'),
(1098, 113, 'Steel bands'),
(1099, 113, 'Opera singers'),
(1100, 114, 'Magicians'),
(1101, 114, 'Comedians'),
(1102, 114, 'Caricaturists'),
(1103, 114, 'Singing waiters'),
(1104, 114, 'Circus performers'),
(1105, 114, 'Fire performers'),
(1106, 114, 'Stilt walkers'),
(1107, 114, 'Living statues'),
(1108, 115, 'Casino tables'),
(1109, 115, 'Race nights'),
(1110, 115, 'Murder mystery events'),
(1111, 115, 'Quiz hosts'),
(1112, 115, 'Game show hosts'),
(1113, 115, 'Bingo hosts'),
(1114, 115, 'Escape room experiences'),
(1115, 116, 'Dancers'),
(1116, 116, 'Drag performers'),
(1117, 116, 'Burlesque performers'),
(1118, 116, 'Tribute shows'),
(1119, 116, 'Theatre performers'),
(1120, 116, 'LED performers'),
(1121, 116, 'Aerial performers'),
(1122, 117, 'Party entertainers'),
(1123, 117, 'Character appearances'),
(1124, 117, 'Mascots'),
(1125, 117, 'Magicians for children'),
(1126, 117, 'Balloon modellers'),
(1127, 117, 'Puppet shows'),
(1128, 117, 'Storytelling sessions'),
(1129, 118, 'Face painting'),
(1130, 118, 'Glitter tattoos'),
(1131, 118, 'Craft parties'),
(1132, 118, 'Slime parties'),
(1133, 118, 'Science parties'),
(1134, 118, 'Lego parties'),
(1135, 118, 'Gaming parties'),
(1136, 118, 'Pamper parties'),
(1137, 119, 'Soft play hire'),
(1138, 119, 'Bouncy castles'),
(1139, 119, 'Inflatable slides'),
(1140, 119, 'Ball pits'),
(1141, 119, 'Toddler play zones'),
(1142, 119, 'Garden games'),
(1143, 119, 'Mini discos'),
(1144, 120, 'Full venue styling'),
(1145, 120, 'Wedding styling'),
(1146, 120, 'Corporate styling'),
(1147, 120, 'Party styling'),
(1148, 120, 'Table styling'),
(1149, 120, 'Ceremony styling'),
(1150, 120, 'Reception styling'),
(1151, 121, 'Backdrops'),
(1152, 121, 'Flower walls'),
(1153, 121, 'Balloon arches'),
(1154, 121, 'Balloon garlands'),
(1155, 121, 'Centrepieces'),
(1156, 121, 'Aisle decor'),
(1157, 121, 'Themed props'),
(1158, 121, 'Sequin walls'),
(1159, 122, 'Welcome signs'),
(1160, 122, 'Table plans'),
(1161, 122, 'Neon signs'),
(1162, 122, 'Acrylic signs'),
(1163, 122, 'Wooden signs'),
(1164, 122, 'Mirror signs'),
(1165, 122, 'Personalised banners'),
(1166, 123, 'Wedding florists'),
(1167, 123, 'Event florists'),
(1168, 123, 'Corporate flowers'),
(1169, 123, 'Funeral flowers'),
(1170, 123, 'Bouquets'),
(1171, 123, 'Buttonholes'),
(1172, 123, 'Floral centrepieces'),
(1173, 124, 'Floral arches'),
(1174, 124, 'Hanging flowers'),
(1175, 124, 'Flower walls'),
(1176, 124, 'Aisle flowers'),
(1177, 124, 'Staircase flowers'),
(1178, 124, 'Table garlands'),
(1179, 125, 'Indoor plant hire'),
(1180, 125, 'Outdoor plant hire'),
(1181, 125, 'Tree hire'),
(1182, 125, 'Living walls'),
(1183, 125, 'Potted plant displays'),
(1184, 126, 'Chair hire'),
(1185, 126, 'Table hire'),
(1186, 126, 'Lounge furniture'),
(1187, 126, 'Outdoor furniture'),
(1188, 126, 'Bar furniture'),
(1189, 126, 'Children’s furniture'),
(1190, 126, 'Rustic furniture'),
(1191, 127, 'Crockery hire'),
(1192, 127, 'Cutlery hire'),
(1193, 127, 'Glassware hire'),
(1194, 127, 'Linen hire'),
(1195, 127, 'Napkin hire'),
(1196, 127, 'Charger plates'),
(1197, 127, 'Serving equipment'),
(1198, 128, 'Gazebo hire'),
(1199, 128, 'Generator hire'),
(1200, 128, 'Heating hire'),
(1201, 128, 'Cooling fans'),
(1202, 128, 'Queue barriers'),
(1203, 128, 'Coat rails'),
(1204, 128, 'Dance barriers'),
(1205, 129, 'Uplighting'),
(1206, 129, 'Festoon lighting'),
(1207, 129, 'Fairy lights'),
(1208, 129, 'Stage lighting'),
(1209, 129, 'Outdoor lighting'),
(1210, 129, 'Mood lighting'),
(1211, 129, 'Moving head lights'),
(1212, 130, 'LED dance floors'),
(1213, 130, 'White dance floors'),
(1214, 130, 'Black dance floors'),
(1215, 130, 'Rustic dance floors'),
(1216, 130, 'Personalised dance floors'),
(1217, 131, 'Cold spark machines'),
(1218, 131, 'Confetti cannons'),
(1219, 131, 'Smoke machines'),
(1220, 131, 'Dry ice effects'),
(1221, 131, 'CO2 jets'),
(1222, 131, 'Bubble machines'),
(1223, 131, 'Snow machines'),
(1224, 132, 'Classic photo booths'),
(1225, 132, 'Open-air photo booths'),
(1226, 132, 'Magic mirror booths'),
(1227, 132, 'Selfie pods'),
(1228, 132, 'Roaming photo booths'),
(1229, 132, 'Green screen booths'),
(1230, 133, '360 video booths'),
(1231, 133, 'Video guest books'),
(1232, 133, 'Slow-motion booths'),
(1233, 133, 'Confessional booths'),
(1234, 133, 'TikTok-style booths'),
(1235, 134, 'Audio guest books'),
(1236, 134, 'Polaroid stations'),
(1237, 134, 'Guest book stations'),
(1238, 134, 'Interactive walls'),
(1239, 134, 'Hashtag printers'),
(1240, 135, 'Wedding cars'),
(1241, 135, 'Classic cars'),
(1242, 135, 'Vintage cars'),
(1243, 135, 'Luxury cars'),
(1244, 135, 'Horse and carriage'),
(1245, 135, 'Campervan hire'),
(1246, 135, 'Limousine hire'),
(1247, 136, 'Minibus hire'),
(1248, 136, 'Coach hire'),
(1249, 136, 'Shuttle buses'),
(1250, 136, 'Taxi coordination'),
(1251, 136, 'Accessible transport'),
(1252, 136, 'Airport transfers'),
(1253, 137, 'Prom cars'),
(1254, 137, 'Supercar hire'),
(1255, 137, 'Motorcycle escort'),
(1256, 137, 'Tractor rides'),
(1257, 137, 'Novelty vehicles'),
(1258, 138, 'Bridal makeup'),
(1259, 138, 'Party makeup'),
(1260, 138, 'Prom makeup'),
(1261, 138, 'Special effects makeup'),
(1262, 138, 'Makeup trials'),
(1263, 138, 'Group makeup bookings'),
(1264, 139, 'Bridal hair'),
(1265, 139, 'Hair styling'),
(1266, 139, 'Prom hair'),
(1267, 139, 'Hair trials'),
(1268, 139, 'Mobile hairdressers'),
(1269, 139, 'Children’s hair styling'),
(1270, 140, 'Mobile massage'),
(1271, 140, 'Nail technicians'),
(1272, 140, 'Spray tanning'),
(1273, 140, 'Pamper parties'),
(1274, 140, 'Men’s grooming'),
(1275, 140, 'Skincare treatments'),
(1276, 141, 'Wedding invitations'),
(1277, 141, 'Birthday invitations'),
(1278, 141, 'Corporate invitations'),
(1279, 141, 'Save the dates'),
(1280, 141, 'RSVP cards'),
(1281, 141, 'Digital invitations'),
(1282, 142, 'Order of service'),
(1283, 142, 'Menus'),
(1284, 142, 'Place cards'),
(1285, 142, 'Table numbers'),
(1286, 142, 'Table plans'),
(1287, 142, 'Welcome boards'),
(1288, 143, 'Banners'),
(1289, 143, 'Posters'),
(1290, 143, 'Flyers'),
(1291, 143, 'Programmes'),
(1292, 143, 'Branded signage'),
(1293, 143, 'Stickers and labels'),
(1294, 144, 'Wedding favours'),
(1295, 144, 'Christening favours'),
(1296, 144, 'Birthday favours'),
(1297, 144, 'Corporate favours'),
(1298, 144, 'Personalised sweets'),
(1299, 144, 'Mini gifts'),
(1300, 145, 'Engraved gifts'),
(1301, 145, 'Printed gifts'),
(1302, 145, 'Photo gifts'),
(1303, 145, 'Personalised clothing'),
(1304, 145, 'Keepsake boxes'),
(1305, 145, 'Custom illustrations'),
(1306, 146, 'Branded merchandise'),
(1307, 146, 'Staff gifts'),
(1308, 146, 'Client gifts'),
(1309, 146, 'Award gifts'),
(1310, 146, 'Welcome packs'),
(1311, 147, 'Wedding planners'),
(1312, 147, 'Party planners'),
(1313, 147, 'Corporate event planners'),
(1314, 147, 'On-the-day coordinators'),
(1315, 147, 'Venue finding'),
(1316, 147, 'Supplier sourcing'),
(1317, 148, 'Event managers'),
(1318, 148, 'Toastmasters'),
(1319, 148, 'Masters of ceremonies'),
(1320, 148, 'Stewards'),
(1321, 148, 'Ticketing support'),
(1322, 148, 'Registration desk staff'),
(1323, 149, 'Guest list management'),
(1324, 149, 'RSVP management'),
(1325, 149, 'Budget planning'),
(1326, 149, 'Risk assessments'),
(1327, 149, 'Event schedules'),
(1328, 150, 'Marquees'),
(1329, 150, 'Tipis'),
(1330, 150, 'Stretch tents'),
(1331, 150, 'Yurts'),
(1332, 150, 'Gazebos'),
(1333, 150, 'Clearspan structures'),
(1334, 150, 'Pagodas'),
(1335, 151, 'Outdoor flooring'),
(1336, 151, 'Temporary toilets'),
(1337, 151, 'Outdoor bars'),
(1338, 151, 'Outdoor kitchens'),
(1339, 151, 'Fencing'),
(1340, 151, 'Trackway'),
(1341, 151, 'Power distribution'),
(1342, 152, 'Patio heaters'),
(1343, 152, 'Blanket hire'),
(1344, 152, 'Umbrella hire'),
(1345, 152, 'Wet weather covers'),
(1346, 152, 'Cooling systems'),
(1347, 153, 'PA systems'),
(1348, 153, 'Microphone hire'),
(1349, 153, 'Sound engineers'),
(1350, 153, 'Background music systems'),
(1351, 153, 'Conference audio'),
(1352, 153, 'Wireless microphones'),
(1353, 154, 'Projector hire'),
(1354, 154, 'Screens'),
(1355, 154, 'LED screens'),
(1356, 154, 'TV hire'),
(1357, 154, 'Presentation equipment'),
(1358, 154, 'Video walls'),
(1359, 155, 'Stage hire'),
(1360, 155, 'Staging crews'),
(1361, 155, 'Lighting technicians'),
(1362, 155, 'Hybrid event support'),
(1363, 155, 'Live streaming production'),
(1364, 155, 'Technical event management'),
(1365, 156, 'Wedding venues'),
(1366, 156, 'Hotels'),
(1367, 156, 'Country houses'),
(1368, 156, 'Barn venues'),
(1369, 156, 'Village halls'),
(1370, 156, 'Community centres'),
(1371, 156, 'Restaurants'),
(1372, 157, 'Conference centres'),
(1373, 157, 'Meeting rooms'),
(1374, 157, 'Training venues'),
(1375, 157, 'Exhibition spaces'),
(1376, 157, 'Networking venues'),
(1377, 158, 'Outdoor venues'),
(1378, 158, 'Historic venues'),
(1379, 158, 'Museums'),
(1380, 158, 'Galleries'),
(1381, 158, 'Sports clubs'),
(1382, 158, 'Warehouses'),
(1383, 158, 'Gardens'),
(1384, 159, 'Art workshops'),
(1385, 159, 'Craft workshops'),
(1386, 159, 'Flower crown workshops'),
(1387, 159, 'Pottery painting'),
(1388, 159, 'Life drawing'),
(1389, 159, 'Cooking classes'),
(1390, 160, 'Inflatable games'),
(1391, 160, 'Garden games'),
(1392, 160, 'Archery'),
(1393, 160, 'Laser tag'),
(1394, 160, 'Axe throwing'),
(1395, 160, 'Sports day games'),
(1396, 160, 'Team building games'),
(1397, 161, 'Petting zoos'),
(1398, 161, 'Pony parties'),
(1399, 161, 'Bird of prey displays'),
(1400, 161, 'Reptile encounters'),
(1401, 161, 'Animal therapy visits'),
(1402, 162, 'Waiting staff'),
(1403, 162, 'Bar staff'),
(1404, 162, 'Hosts and hostesses'),
(1405, 162, 'Cloakroom staff'),
(1406, 162, 'Cleaning staff'),
(1407, 162, 'Kitchen porters'),
(1408, 163, 'Door supervisors'),
(1409, 163, 'Event security'),
(1410, 163, 'Crowd management'),
(1411, 163, 'Car park marshals'),
(1412, 163, 'Overnight security'),
(1413, 164, 'First aiders'),
(1414, 164, 'Medics'),
(1415, 164, 'Fire marshals'),
(1416, 164, 'Chaperones'),
(1417, 164, 'Accessibility support staff'),
(1418, 165, 'Hotel room blocks'),
(1419, 165, 'Guest houses'),
(1420, 165, 'Glamping accommodation'),
(1421, 165, 'Serviced apartments'),
(1422, 165, 'Group accommodation'),
(1423, 166, 'Travel coordination'),
(1424, 166, 'Guest transport planning'),
(1425, 166, 'Airport pickup coordination'),
(1426, 166, 'Itinerary planning'),
(1427, 166, 'Local area guides'),
(1428, 167, 'Celebrants'),
(1429, 167, 'Registrars'),
(1430, 167, 'Interpreters'),
(1431, 167, 'Pet sitters'),
(1432, 167, 'Childcare services'),
(1433, 167, 'Cleaning services'),
(1434, 168, 'Event websites'),
(1435, 168, 'Online invitations'),
(1436, 168, 'Ticketing platforms'),
(1437, 168, 'QR code check-in'),
(1438, 168, 'Digital seating plans'),
(1439, 169, 'Custom requests'),
(1440, 169, 'Unusual suppliers'),
(1441, 169, 'Multi-service packages'),
(1442, 169, 'Not listed elsewhere')
ON DUPLICATE KEY UPDATE `parent_id`=VALUES(`parent_id`), `name`=VALUES(`name`);

-- ============================================================
-- TABLE: users — login username (legacy schemas may lack this column)
-- ============================================================
CALL `event_marketplace_add_column_if_missing`('users', 'username', '`username` varchar(255) DEFAULT NULL AFTER `name`');
UPDATE `users` SET `username` = CONCAT('user', `id`) WHERE `username` IS NULL OR TRIM(`username`) = '';

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

INSERT INTO `cms_pages` (`slug`, `title`, `content`, `meta_title`, `meta_description`, `status`, `created_at`, `updated_at`) VALUES
('about', 'About us', '<p>We connect people planning celebrations with trusted event suppliers.</p>', 'About — For Your Events', 'Learn about the For Your Events marketplace.', 'published', NOW(), NOW()),
('how-it-works', 'How it works', '<p class="lead">Plan your event and book suppliers in a few clear steps.</p><h2 class="h4 mt-4">For customers</h2><ol><li><strong>Create your event</strong> — date, location, guest count, and the type of occasion.</li><li><strong>Browse services</strong> — search and filter by category, compare listings, and save favourites.</li><li><strong>Add to your plan</strong> — add services to your event basket and send booking requests to vendors.</li><li><strong>Stay in control</strong> — track pending, accepted, and declined requests in My Bookings, message vendors from Messages, and review payments in Payments.</li></ol><h2 class="h4 mt-4">For vendors</h2><ol><li><strong>Register as a vendor</strong> and build your service listings with clear pricing and policies.</li><li><strong>Respond to bookings</strong> from your dashboard; accept or decline with one click.</li><li><strong>Use your calendar</strong> to see upcoming work tied to customer events.</li></ol><p class="mt-3 mb-0 text-muted">Administrators can refine this text under <strong>Admin → Pages</strong>.</p>', 'How it works', 'How the For Your Events marketplace works for customers and vendors.', 'published', NOW(), NOW()),
('contact', 'Contact', '<p>Email us at <strong>support@example.com</strong> (replace with your live support address).</p>', 'Contact', 'Contact For Your Events.', 'published', NOW(), NOW()),
('vendor-info', 'Information for vendors', '<p>List your services, respond to booking requests, and grow your event business from a single dashboard.</p>', 'For vendors', 'Vendor information for the marketplace.', 'published', NOW(), NOW()),
('faq', 'Frequently asked questions', '<h5>How do I book a service?</h5><p>Add services to your event basket, send booking requests, and complete payment when suppliers confirm.</p><h5 class="mt-4">How do I message a vendor?</h5><p>Messaging opens once you have an eligible booking for that service.</p>', 'FAQ', 'Common questions about For Your Events.', 'published', NOW(), NOW())
ON DUPLICATE KEY UPDATE
  `title` = VALUES(`title`),
  `content` = VALUES(`content`),
  `meta_title` = VALUES(`meta_title`),
  `meta_description` = VALUES(`meta_description`),
  `status` = 'published',
  `updated_at` = NOW();

-- ============================================================
-- Done! Your database is now up to date.
-- ============================================================
SELECT 'Database update complete!' AS status;
