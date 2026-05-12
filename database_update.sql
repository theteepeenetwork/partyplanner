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
CALL `event_marketplace_add_column_if_missing`('events', 'description', '`description` text DEFAULT NULL AFTER `title`');
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
-- SEED: default categories (if empty)
-- ============================================================
INSERT INTO `categories` (`id`, `name`) VALUES
(1, 'Catering'),
(2, 'Photography'),
(3, 'Entertainment'),
(4, 'Transport'),
(5, 'Makeup'),
(6, 'Stationery'),
(7, 'Gifts'),
(8, 'LED Dance Floors'),
(9, 'Illuminated Letters'),
(10, 'Chair Covers'),
(11, 'Amusement Rides')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

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
-- CMS: public pages linked from the site header / footer / PublicPage
-- (Without these rows, routes exist but show() throws 404.)
-- ============================================================
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
