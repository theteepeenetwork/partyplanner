-- ============================================================
-- Event Marketplace — Database Update Script
-- Run this against your existing event_marketplace database
-- All statements are idempotent (safe to run multiple times)
-- ============================================================

-- ============================================================
-- TABLE: categories — add parent_id for nested category tree
-- ============================================================
ALTER TABLE categories ADD COLUMN IF NOT EXISTS `parent_id` int(11) DEFAULT NULL AFTER `id`;

-- ============================================================
-- TABLE: services — add all columns used by the application
-- ============================================================
ALTER TABLE services ADD COLUMN IF NOT EXISTS `short_description` varchar(500) DEFAULT NULL AFTER `description`;
ALTER TABLE services ADD COLUMN IF NOT EXISTS `category_id` int(11) DEFAULT NULL AFTER `price`;
ALTER TABLE services ADD COLUMN IF NOT EXISTS `subcategory_id` int(11) DEFAULT NULL AFTER `category_id`;
ALTER TABLE services ADD COLUMN IF NOT EXISTS `third_category_id` int(11) DEFAULT NULL AFTER `subcategory_id`;
ALTER TABLE services ADD COLUMN IF NOT EXISTS `latitude` decimal(10,8) DEFAULT NULL;
ALTER TABLE services ADD COLUMN IF NOT EXISTS `longitude` decimal(11,8) DEFAULT NULL;
ALTER TABLE services ADD COLUMN IF NOT EXISTS `deleted_at` datetime DEFAULT NULL;
ALTER TABLE services ADD COLUMN IF NOT EXISTS `free_coverage_radius` int(11) DEFAULT NULL;
ALTER TABLE services ADD COLUMN IF NOT EXISTS `paid_coverage_radius` int(11) DEFAULT NULL;
ALTER TABLE services ADD COLUMN IF NOT EXISTS `travel_fee_per_km` decimal(10,2) DEFAULT NULL;
ALTER TABLE services ADD COLUMN IF NOT EXISTS `cancellation_policy` text DEFAULT NULL;
ALTER TABLE services ADD COLUMN IF NOT EXISTS `service_tags` text DEFAULT NULL;
ALTER TABLE services ADD COLUMN IF NOT EXISTS `service_location` varchar(255) DEFAULT NULL;
ALTER TABLE services ADD COLUMN IF NOT EXISTS `all_travel_included` tinyint(1) DEFAULT 0;
ALTER TABLE services ADD COLUMN IF NOT EXISTS `no_travel_limit` tinyint(1) DEFAULT 0;
ALTER TABLE services ADD COLUMN IF NOT EXISTS `event_types` text DEFAULT NULL;
ALTER TABLE services ADD COLUMN IF NOT EXISTS `commission_percentage` decimal(5,2) DEFAULT NULL;
ALTER TABLE services ADD COLUMN IF NOT EXISTS `license` varchar(255) DEFAULT NULL;
ALTER TABLE services ADD COLUMN IF NOT EXISTS `attendance_thresholds` text DEFAULT NULL;
ALTER TABLE services ADD COLUMN IF NOT EXISTS `max_pitch_fees` text DEFAULT NULL;
ALTER TABLE services ADD COLUMN IF NOT EXISTS `status` varchar(20) DEFAULT 'active';
ALTER TABLE services ADD COLUMN IF NOT EXISTS `created_at` datetime DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE services ADD COLUMN IF NOT EXISTS `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- ============================================================
-- TABLE: events — add all columns for event creation flow
-- ============================================================
ALTER TABLE events ADD COLUMN IF NOT EXISTS `user_id` int(11) DEFAULT NULL AFTER `id`;
ALTER TABLE events ADD COLUMN IF NOT EXISTS `event_type` varchar(100) DEFAULT NULL AFTER `category`;
ALTER TABLE events ADD COLUMN IF NOT EXISTS `guest_count` int(11) DEFAULT NULL AFTER `event_type`;
ALTER TABLE events ADD COLUMN IF NOT EXISTS `venue_name` varchar(255) DEFAULT NULL AFTER `location`;
ALTER TABLE events ADD COLUMN IF NOT EXISTS `postcode` varchar(20) DEFAULT NULL AFTER `venue_name`;
ALTER TABLE events ADD COLUMN IF NOT EXISTS `town_city` varchar(255) DEFAULT NULL AFTER `postcode`;
ALTER TABLE events ADD COLUMN IF NOT EXISTS `indoor_outdoor` varchar(20) DEFAULT NULL AFTER `town_city`;
ALTER TABLE events ADD COLUMN IF NOT EXISTS `budget_min` decimal(10,2) DEFAULT NULL AFTER `indoor_outdoor`;
ALTER TABLE events ADD COLUMN IF NOT EXISTS `budget_max` decimal(10,2) DEFAULT NULL AFTER `budget_min`;
ALTER TABLE events ADD COLUMN IF NOT EXISTS `style_theme` varchar(255) DEFAULT NULL AFTER `budget_max`;
ALTER TABLE events ADD COLUMN IF NOT EXISTS `notes` text DEFAULT NULL AFTER `style_theme`;
ALTER TABLE events ADD COLUMN IF NOT EXISTS `status` varchar(20) DEFAULT 'active' AFTER `notes`;
ALTER TABLE events ADD COLUMN IF NOT EXISTS `created_at` datetime DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE events ADD COLUMN IF NOT EXISTS `event_setting` varchar(20) NOT NULL DEFAULT 'private' COMMENT 'public vs private pricing path' AFTER `guest_count`;
ALTER TABLE events ADD COLUMN IF NOT EXISTS `organiser_pitch_fee` decimal(10,2) DEFAULT NULL COMMENT 'Actual pitch/stand fee for public events' AFTER `event_setting`;
ALTER TABLE events ADD COLUMN IF NOT EXISTS `latitude` decimal(10,8) DEFAULT NULL AFTER `town_city`;
ALTER TABLE events ADD COLUMN IF NOT EXISTS `longitude` decimal(11,8) DEFAULT NULL AFTER `latitude`;

-- ============================================================
-- TABLE: booking_items — add pricing and package columns
-- ============================================================
ALTER TABLE booking_items ADD COLUMN IF NOT EXISTS `package_name` varchar(255) DEFAULT NULL AFTER `quantity`;
ALTER TABLE booking_items ADD COLUMN IF NOT EXISTS `guest_count` int(11) DEFAULT NULL AFTER `package_name`;
ALTER TABLE booking_items ADD COLUMN IF NOT EXISTS `price` decimal(10,2) DEFAULT NULL AFTER `guest_count`;
ALTER TABLE booking_items ADD COLUMN IF NOT EXISTS `created_at` datetime DEFAULT CURRENT_TIMESTAMP;

-- ============================================================
-- TABLE: payments — add payment type and description
-- ============================================================
ALTER TABLE payments ADD COLUMN IF NOT EXISTS `payment_type` varchar(50) DEFAULT 'deposit' AFTER `payment_method`;
ALTER TABLE payments ADD COLUMN IF NOT EXISTS `description` varchar(255) DEFAULT NULL AFTER `payment_type`;

-- ============================================================
-- TABLE: services_private_event_pricing — add pricing_type
-- ============================================================
ALTER TABLE services_private_event_pricing ADD COLUMN IF NOT EXISTS `pricing_type` varchar(50) DEFAULT NULL AFTER `service_id`;

-- ============================================================
-- TABLE: services_guest_based_pricing — add required columns
-- ============================================================
ALTER TABLE services_guest_based_pricing ADD COLUMN IF NOT EXISTS `private_event_pricing_id` int(11) DEFAULT NULL AFTER `service_id`;
ALTER TABLE services_guest_based_pricing ADD COLUMN IF NOT EXISTS `min_guest` int(11) DEFAULT NULL;
ALTER TABLE services_guest_based_pricing ADD COLUMN IF NOT EXISTS `max_guest` int(11) DEFAULT NULL;
ALTER TABLE services_guest_based_pricing ADD COLUMN IF NOT EXISTS `guest_price` decimal(10,2) DEFAULT NULL;

-- ============================================================
-- TABLE: services_custom_duration_pricing — add required columns
-- ============================================================
ALTER TABLE services_custom_duration_pricing ADD COLUMN IF NOT EXISTS `private_event_pricing_id` int(11) DEFAULT NULL AFTER `service_id`;
ALTER TABLE services_custom_duration_pricing ADD COLUMN IF NOT EXISTS `duration_type` varchar(20) DEFAULT NULL;
ALTER TABLE services_custom_duration_pricing ADD COLUMN IF NOT EXISTS `duration` int(11) DEFAULT NULL;

-- ============================================================
-- TABLE: services_tiered_packages_pricing — add required columns
-- ============================================================
ALTER TABLE services_tiered_packages_pricing ADD COLUMN IF NOT EXISTS `private_event_pricing_id` int(11) DEFAULT NULL AFTER `service_id`;
ALTER TABLE services_tiered_packages_pricing ADD COLUMN IF NOT EXISTS `package_description` text DEFAULT NULL;
ALTER TABLE services_tiered_packages_pricing ADD COLUMN IF NOT EXISTS `package_price` decimal(10,2) DEFAULT NULL;

-- ============================================================
-- TABLE: services_locations — add coverage and travel columns
-- ============================================================
ALTER TABLE services_locations ADD COLUMN IF NOT EXISTS `service_location` varchar(255) DEFAULT NULL AFTER `service_id`;
ALTER TABLE services_locations ADD COLUMN IF NOT EXISTS `all_travel_included` tinyint(1) DEFAULT 0;
ALTER TABLE services_locations ADD COLUMN IF NOT EXISTS `no_travel_limit` tinyint(1) DEFAULT 0;
ALTER TABLE services_locations ADD COLUMN IF NOT EXISTS `free_coverage_radius` int(11) DEFAULT NULL;
ALTER TABLE services_locations ADD COLUMN IF NOT EXISTS `paid_coverage_radius` int(11) DEFAULT NULL;
ALTER TABLE services_locations ADD COLUMN IF NOT EXISTS `travel_fee_per_km` decimal(10,2) DEFAULT NULL;

-- ============================================================
-- TABLE: services_cancellation_policies — add cancellation_policy column
-- ============================================================
ALTER TABLE services_cancellation_policies ADD COLUMN IF NOT EXISTS `cancellation_policy` text DEFAULT NULL AFTER `policy`;

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

ALTER TABLE event_basket_items ADD COLUMN IF NOT EXISTS `quote_breakdown` text DEFAULT NULL COMMENT 'JSON line items for estimated_total' AFTER `notes`;

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

ALTER TABLE services_optional_extras ADD COLUMN IF NOT EXISTS `quantity` int(11) DEFAULT 1 AFTER `description`;

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
ALTER TABLE chat_rooms ADD COLUMN IF NOT EXISTS `flagged_for_review` tinyint(1) NOT NULL DEFAULT 0 AFTER `service_id`;

-- ============================================================
-- TABLE: chat_messages — profanity / language moderation
-- ============================================================
ALTER TABLE chat_messages ADD COLUMN IF NOT EXISTS `original_message` text DEFAULT NULL AFTER `message`;
ALTER TABLE chat_messages ADD COLUMN IF NOT EXISTS `moderation_status` varchar(20) NOT NULL DEFAULT 'clean' AFTER `original_message`;
ALTER TABLE chat_messages ADD COLUMN IF NOT EXISTS `admin_note` text DEFAULT NULL AFTER `moderation_status`;
ALTER TABLE chat_messages ADD COLUMN IF NOT EXISTS `profanity_matches` varchar(500) DEFAULT NULL AFTER `admin_note`;
ALTER TABLE chat_messages ADD COLUMN IF NOT EXISTS `reviewed_by` int(11) DEFAULT NULL AFTER `profanity_matches`;
ALTER TABLE chat_messages ADD COLUMN IF NOT EXISTS `reviewed_at` datetime DEFAULT NULL AFTER `reviewed_by`;

-- ============================================================
-- CMS: default published homepage (editable in Admin → Pages)
-- ============================================================
INSERT INTO `cms_pages` (`slug`, `title`, `content`, `status`, `created_at`, `updated_at`) VALUES
('homepage', 'Welcome', '<p class="lead">Plan your celebration with trusted local vendors.</p><p><em>This block is editable in <strong>Admin → Pages → homepage</strong>.</em></p>', 'published', NOW(), NOW())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `content` = VALUES(`content`), `status` = 'published', `updated_at` = NOW();

-- ============================================================
-- SEED: QA / smoke-test accounts (password: TestPass123!)
-- Same bcrypt as event_marketplace.sql; safe to re-run (updates row).
-- ============================================================
INSERT INTO `users` (`id`, `name`, `username`, `email`, `password`, `role`) VALUES
(1, 'Site Admin', 'admin', 'admin@example.test', '$2y$10$i7T5IbGkzDuPTrHvstBG5OeaFHRTbHdMDGloA8N049zWjZIoEc8Ze', 'admin'),
(6, 'QA Customer', 'qa_customer', 'qa.customer@example.test', '$2y$10$i7T5IbGkzDuPTrHvstBG5OeaFHRTbHdMDGloA8N049zWjZIoEc8Ze', 'customer'),
(7, 'QA Vendor', 'qa_vendor', 'qa.vendor@example.test', '$2y$10$i7T5IbGkzDuPTrHvstBG5OeaFHRTbHdMDGloA8N049zWjZIoEc8Ze', 'vendor')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `username`=VALUES(`username`), `email`=VALUES(`email`), `password`=VALUES(`password`), `role`=VALUES(`role`);

-- ============================================================
-- Done! Your database is now up to date.
-- ============================================================
SELECT 'Database update complete!' AS status;
