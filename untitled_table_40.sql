-- -------------------------------------------------------------
-- TablePlus 6.9.6(676)
--
-- https://tableplus.com/
--
-- Database: event_marketplace
-- Generation Time: 2026-06-01 23:20:46.2710
-- -------------------------------------------------------------


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


DROP TABLE IF EXISTS `untitled_table_40`;
;

DROP TABLE IF EXISTS `booking_items`;
CREATE TABLE `booking_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `booking_id` int NOT NULL,
  `service_id` int NOT NULL,
  `quantity` int DEFAULT '1',
  `package_name` varchar(255) DEFAULT NULL,
  `guest_count` int DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `quote_breakdown` json DEFAULT NULL,
  `quote_warnings` json DEFAULT NULL,
  `extras_snapshot` json DEFAULT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=506 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `bookings`;
CREATE TABLE `bookings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `event_id` int DEFAULT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `payment_intent_id` varchar(255) DEFAULT NULL,
  `balance_due` decimal(10,2) DEFAULT NULL,
  `payment_plan` varchar(32) DEFAULT 'single',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=503 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `carts`;
CREATE TABLE `carts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `service_id` int NOT NULL,
  `event_id` int DEFAULT NULL,
  `quantity` int DEFAULT '1',
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `parent_id` int DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_categories_parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1443 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `chat_messages`;
CREATE TABLE `chat_messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `chat_room_id` int NOT NULL,
  `sender_id` int NOT NULL,
  `receiver_id` int DEFAULT NULL,
  `message` text,
  `original_message` text,
  `moderation_status` varchar(20) NOT NULL DEFAULT 'clean',
  `admin_note` text,
  `profanity_matches` varchar(500) DEFAULT NULL,
  `reviewed_by` int DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=507 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `chat_rooms`;
CREATE TABLE `chat_rooms` (
  `id` int NOT NULL AUTO_INCREMENT,
  `vendor_id` int NOT NULL,
  `customer_id` int NOT NULL,
  `service_id` int DEFAULT NULL,
  `flagged_for_review` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=505 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `cms_pages`;
CREATE TABLE `cms_pages` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(191) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` varchar(500) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'draft',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=74 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `event_basket_items`;
CREATE TABLE `event_basket_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `event_id` int NOT NULL,
  `user_id` int NOT NULL,
  `service_id` int NOT NULL,
  `vendor_id` int NOT NULL,
  `package_name` varchar(255) DEFAULT NULL,
  `extras` text,
  `quantity` int DEFAULT '1',
  `unit_price` decimal(10,2) DEFAULT NULL,
  `deposit_amount` decimal(10,2) DEFAULT NULL,
  `estimated_total` decimal(10,2) DEFAULT NULL,
  `notes` text,
  `quote_breakdown` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `event_id` (`event_id`),
  KEY `user_id` (`user_id`),
  KEY `service_id` (`service_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `events`;
CREATE TABLE `events` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `vendor_id` int DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text,
  `date` date DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `venue_name` varchar(255) DEFAULT NULL,
  `postcode` varchar(20) DEFAULT NULL,
  `town_city` varchar(255) DEFAULT NULL,
  `indoor_outdoor` varchar(20) DEFAULT NULL,
  `budget_min` decimal(10,2) DEFAULT NULL,
  `budget_max` decimal(10,2) DEFAULT NULL,
  `style_theme` varchar(255) DEFAULT NULL,
  `notes` text,
  `status` varchar(20) DEFAULT 'active',
  `category` varchar(255) DEFAULT NULL,
  `event_type` varchar(100) DEFAULT NULL,
  `guest_count` int DEFAULT NULL,
  `event_setting` varchar(20) NOT NULL DEFAULT 'private',
  `organiser_pitch_fee` decimal(10,2) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `vendor_id` (`vendor_id`)
) ENGINE=InnoDB AUTO_INCREMENT=531 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `favourites`;
CREATE TABLE `favourites` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `service_id` int NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_service` (`user_id`,`service_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `version` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `class` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `group` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `namespace` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `time` int NOT NULL,
  `batch` int unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `payment_schedules`;
CREATE TABLE `payment_schedules` (
  `id` int NOT NULL AUTO_INCREMENT,
  `booking_id` int NOT NULL,
  `due_date` date NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` varchar(32) NOT NULL DEFAULT 'pending',
  `stripe_payment_intent_id` varchar(255) DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `booking_id` (`booking_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `booking_id` int NOT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=503 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `quote_analytics_daily`;
CREATE TABLE `quote_analytics_daily` (
  `id` int NOT NULL AUTO_INCREMENT,
  `vendor_id` int NOT NULL,
  `service_id` int DEFAULT NULL,
  `metric_date` date NOT NULL,
  `quotes_generated` int NOT NULL DEFAULT '0',
  `quotes_accepted` int NOT NULL DEFAULT '0',
  `auto_accepted` int NOT NULL DEFAULT '0',
  `avg_total` decimal(10,2) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `vendor_service_date` (`vendor_id`,`service_id`,`metric_date`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `quote_automation_log`;
CREATE TABLE `quote_automation_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `booking_item_id` int NOT NULL,
  `action` varchar(64) NOT NULL,
  `details` json DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `booking_item_id` (`booking_item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `service_availability`;
CREATE TABLE `service_availability` (
  `id` int NOT NULL AUTO_INCREMENT,
  `service_id` int NOT NULL,
  `day_of_week` varchar(20) DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `service_images`;
CREATE TABLE `service_images` (
  `id` int NOT NULL AUTO_INCREMENT,
  `service_id` int NOT NULL,
  `image_path` varchar(500) DEFAULT NULL,
  `thumbnail_path` varchar(500) DEFAULT NULL,
  `is_primary` tinyint(1) DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=114 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `service_public_event_data`;
CREATE TABLE `service_public_event_data` (
  `id` int NOT NULL AUTO_INCREMENT,
  `service_id` int NOT NULL,
  `data` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `service_time_blocks`;
CREATE TABLE `service_time_blocks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `service_id` int NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `services`;
CREATE TABLE `services` (
  `id` int NOT NULL AUTO_INCREMENT,
  `vendor_id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text,
  `short_description` varchar(500) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `min_capacity` int DEFAULT NULL,
  `max_capacity` int DEFAULT NULL,
  `setup_minutes` int DEFAULT NULL,
  `breakdown_minutes` int DEFAULT NULL,
  `min_notice_days` int DEFAULT NULL,
  `space_required` varchar(120) DEFAULT NULL,
  `indoor_outdoor` enum('indoor','outdoor','both') NOT NULL DEFAULT 'both',
  `power_required` tinyint(1) NOT NULL DEFAULT '0',
  `water_required` tinyint(1) NOT NULL DEFAULT '0',
  `vehicle_access_required` tinyint(1) NOT NULL DEFAULT '0',
  `equipment_provided` tinyint(1) NOT NULL DEFAULT '0',
  `category_id` int DEFAULT NULL,
  `subcategory_id` int DEFAULT NULL,
  `third_category_id` int DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `free_coverage_radius` int DEFAULT NULL,
  `paid_coverage_radius` int DEFAULT NULL,
  `travel_fee_per_km` decimal(10,2) DEFAULT NULL,
  `cancellation_policy` text,
  `service_tags` text,
  `service_location` varchar(255) DEFAULT NULL,
  `all_travel_included` tinyint(1) DEFAULT '0',
  `no_travel_limit` tinyint(1) DEFAULT '0',
  `event_types` text,
  `commission_percentage` decimal(5,2) DEFAULT NULL,
  `license` varchar(255) DEFAULT NULL,
  `attendance_thresholds` text,
  `max_pitch_fees` text,
  `status` varchar(20) DEFAULT 'active',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `vendor_id` (`vendor_id`),
  CONSTRAINT `services_ibfk_1` FOREIGN KEY (`vendor_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=224 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `services_cancellation_policies`;
CREATE TABLE `services_cancellation_policies` (
  `id` int NOT NULL AUTO_INCREMENT,
  `service_id` int NOT NULL,
  `policy` text,
  `cancellation_policy` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `services_corporate_event_pricing`;
CREATE TABLE `services_corporate_event_pricing` (
  `id` int NOT NULL AUTO_INCREMENT,
  `service_id` int NOT NULL,
  `pricing_details` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `services_custom_duration_pricing`;
CREATE TABLE `services_custom_duration_pricing` (
  `id` int NOT NULL AUTO_INCREMENT,
  `service_id` int NOT NULL,
  `private_event_pricing_id` int DEFAULT NULL,
  `duration_type` varchar(20) DEFAULT NULL,
  `duration` int DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=103 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `services_event_types`;
CREATE TABLE `services_event_types` (
  `id` int NOT NULL AUTO_INCREMENT,
  `service_id` int NOT NULL,
  `event_type` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=170 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `services_guest_based_pricing`;
CREATE TABLE `services_guest_based_pricing` (
  `id` int NOT NULL AUTO_INCREMENT,
  `service_id` int NOT NULL,
  `private_event_pricing_id` int DEFAULT NULL,
  `min_guest` int DEFAULT NULL,
  `max_guest` int DEFAULT NULL,
  `guest_price` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `services_locations`;
CREATE TABLE `services_locations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `service_id` int NOT NULL,
  `fulfillment_type` enum('in_person','postal','both') NOT NULL DEFAULT 'in_person',
  `service_location` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `all_travel_included` tinyint(1) DEFAULT '0',
  `no_travel_limit` tinyint(1) DEFAULT '0',
  `free_coverage_radius` int DEFAULT NULL,
  `paid_coverage_radius` int DEFAULT NULL,
  `travel_fee_per_km` decimal(10,2) DEFAULT NULL,
  `strict_travel_radius` tinyint(1) NOT NULL DEFAULT '0',
  `postal_fee` decimal(8,2) DEFAULT NULL,
  `free_postage_above` decimal(8,2) DEFAULT NULL,
  `delivery_lead_time_days` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=129 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `services_optional_extras`;
CREATE TABLE `services_optional_extras` (
  `id` int NOT NULL AUTO_INCREMENT,
  `service_id` int NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `pricing_type` enum('flat','per_item') NOT NULL DEFAULT 'flat',
  `min_quantity` int unsigned DEFAULT NULL,
  `max_quantity` int unsigned DEFAULT NULL,
  `unit_label` varchar(50) DEFAULT NULL,
  `description` text,
  `quantity` int DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=160 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `services_private_event_pricing`;
CREATE TABLE `services_private_event_pricing` (
  `id` int NOT NULL AUTO_INCREMENT,
  `service_id` int NOT NULL,
  `pricing_type` varchar(50) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `description` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=119 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `services_public_event_pricing`;
CREATE TABLE `services_public_event_pricing` (
  `id` int NOT NULL AUTO_INCREMENT,
  `service_id` int NOT NULL,
  `commission_percentage` decimal(5,2) DEFAULT NULL,
  `min_attendance` int DEFAULT NULL,
  `max_attendance` int DEFAULT NULL,
  `max_pitch_fee` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=82 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `services_quantity_pricing`;
CREATE TABLE `services_quantity_pricing` (
  `id` int NOT NULL AUTO_INCREMENT,
  `service_id` int NOT NULL,
  `private_event_pricing_id` int DEFAULT NULL,
  `unit_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `min_quantity` int NOT NULL DEFAULT '1',
  `max_quantity` int DEFAULT NULL,
  `unit_label` varchar(50) NOT NULL DEFAULT 'items',
  PRIMARY KEY (`id`),
  KEY `service_id` (`service_id`),
  KEY `private_event_pricing_id` (`private_event_pricing_id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `services_service_tags`;
CREATE TABLE `services_service_tags` (
  `id` int NOT NULL AUTO_INCREMENT,
  `service_id` int NOT NULL,
  `tag_id` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `services_tags`;
CREATE TABLE `services_tags` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `services_tiered_packages_pricing`;
CREATE TABLE `services_tiered_packages_pricing` (
  `id` int NOT NULL AUTO_INCREMENT,
  `service_id` int NOT NULL,
  `private_event_pricing_id` int DEFAULT NULL,
  `package_name` varchar(255) DEFAULT NULL,
  `package_description` text,
  `package_price` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=81 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `subcategories`;
CREATE TABLE `subcategories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category_id` int DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `unavailable_dates`;
CREATE TABLE `unavailable_dates` (
  `id` int NOT NULL AUTO_INCREMENT,
  `service_id` int DEFAULT NULL,
  `vendor_id` int DEFAULT NULL,
  `date` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('customer','vendor','admin') NOT NULL,
  `password_reset_token` varchar(128) DEFAULT NULL,
  `password_reset_expires_at` datetime DEFAULT NULL,
  `host_bio` text,
  `host_tagline` varchar(255) DEFAULT NULL,
  `host_quote` text,
  `host_plays` text,
  `host_photo_path` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=93 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `vendor_message_templates`;
CREATE TABLE `vendor_message_templates` (
  `id` int NOT NULL AUTO_INCREMENT,
  `vendor_id` int NOT NULL,
  `name` varchar(120) NOT NULL,
  `body` text NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `vendor_id` (`vendor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `vendor_quote_settings`;
CREATE TABLE `vendor_quote_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `vendor_id` int NOT NULL,
  `service_id` int DEFAULT NULL,
  `auto_accept_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `max_auto_accept_amount` decimal(10,2) DEFAULT NULL,
  `require_within_travel_radius` tinyint(1) NOT NULL DEFAULT '1',
  `min_lead_days` int NOT NULL DEFAULT '0',
  `allowed_event_settings` json DEFAULT NULL,
  `blackout_respect` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `vendor_id` (`vendor_id`),
  KEY `service_id` (`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `vendor_quotes`;
CREATE TABLE `vendor_quotes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `booking_item_id` int NOT NULL,
  `vendor_id` int NOT NULL,
  `status` enum('draft','sent','accepted','declined','expired') NOT NULL DEFAULT 'draft',
  `lines` json NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `vendor_notes` text,
  `expires_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `booking_item_id` (`booking_item_id`),
  KEY `vendor_id` (`vendor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `booking_items` (`id`, `booking_id`, `service_id`, `quantity`, `package_name`, `guest_count`, `price`, `quote_breakdown`, `quote_warnings`, `extras_snapshot`, `status`, `start_time`, `end_time`, `created_at`) VALUES
(501, 501, 2, 1, NULL, NULL, 150.00, NULL, NULL, NULL, 'pending', NULL, NULL, '2026-06-01 22:14:13'),
(502, 501, 5, 1, NULL, NULL, 90.00, NULL, NULL, NULL, 'accepted', NULL, NULL, '2026-06-01 22:14:13');

INSERT INTO `bookings` (`id`, `user_id`, `event_id`, `status`, `start_time`, `end_time`, `payment_intent_id`, `balance_due`, `payment_plan`, `created_at`, `updated_at`) VALUES
(501, 6, 501, 'pending', NULL, NULL, NULL, NULL, 'single', '2026-06-01 22:14:13', '2026-06-01 22:14:13');

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
(1406, 162, 'Cleaning staff');

INSERT INTO `categories` (`id`, `parent_id`, `name`) VALUES
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
(1442, 169, 'Not listed elsewhere');

INSERT INTO `chat_messages` (`id`, `chat_room_id`, `sender_id`, `receiver_id`, `message`, `original_message`, `moderation_status`, `admin_note`, `profanity_matches`, `reviewed_by`, `reviewed_at`, `is_read`, `created_at`) VALUES
(501, 501, 6, 3, 'Hi — confirming our sweet cart for the wedding. Thanks!', NULL, 'clean', NULL, NULL, NULL, NULL, 0, '2026-06-01 22:14:13'),
(502, 501, 3, 6, 'Thanks, we will confirm closer to the date.', NULL, 'clean', NULL, NULL, NULL, NULL, 1, '2026-06-01 22:14:13');

INSERT INTO `chat_rooms` (`id`, `vendor_id`, `customer_id`, `service_id`, `flagged_for_review`, `created_at`) VALUES
(501, 3, 6, 2, 0, '2026-06-01 22:14:13');

INSERT INTO `cms_pages` (`id`, `slug`, `title`, `content`, `meta_title`, `meta_description`, `status`, `created_at`, `updated_at`) VALUES
(1, 'homepage', 'Welcome', '<p class=\"lead\">Plan your celebration with trusted local vendors.</p><p><em>This block is editable in <strong>Admin → Pages → homepage</strong>.</em></p>', '', '', 'published', '2026-05-15 12:14:24', '2026-06-01 22:14:51'),
(3, 'about', 'About us', '<p>We connect people planning celebrations with trusted event suppliers.</p>', 'About — For Your Events', 'Learn about the For Your Events marketplace.', 'published', '2026-05-19 12:10:58', '2026-06-01 22:14:51'),
(4, 'how-it-works', 'How it works', '<p class=\"lead\">Plan your event and book suppliers in a few clear steps.</p><h2 class=\"h4 mt-4\">For customers</h2><ol><li><strong>Create your event</strong> — date, location, guest count, and the type of occasion.</li><li><strong>Browse services</strong> — search and filter by category, compare listings, and save favourites.</li><li><strong>Add to your plan</strong> — add services to your event basket and send booking requests to vendors.</li><li><strong>Stay in control</strong> — track pending, accepted, and declined requests in My Bookings, message vendors from Messages, and review payments in Payments.</li></ol><h2 class=\"h4 mt-4\">For vendors</h2><ol><li><strong>Register as a vendor</strong> and build your service listings with clear pricing and policies.</li><li><strong>Respond to bookings</strong> from your dashboard; accept or decline with one click.</li><li><strong>Use your calendar</strong> to see upcoming work tied to customer events.</li></ol><p class=\"mt-3 mb-0 text-muted\">Administrators can refine this text under <strong>Admin → Pages</strong>.</p>', 'How it works', 'How the For Your Events marketplace works for customers and vendors.', 'published', '2026-05-19 12:10:58', '2026-06-01 22:14:51'),
(5, 'contact', 'Contact', '<p>Email us at <strong>support@example.com</strong> (replace with your live support address).</p>', 'Contact', 'Contact For Your Events.', 'published', '2026-05-19 12:10:58', '2026-06-01 22:14:51'),
(6, 'vendor-info', 'Information for vendors', '<p>List your services, respond to booking requests, and grow your event business from a single dashboard.</p>', 'For vendors', 'Vendor information for the marketplace.', 'published', '2026-05-19 12:10:58', '2026-06-01 22:14:51'),
(7, 'faq', 'Frequently asked questions', '<h5>How do I book a service?</h5><p>Add services to your event basket, send booking requests, and complete payment when suppliers confirm.</p><h5 class=\"mt-4\">How do I message a vendor?</h5><p>Messaging opens once you have an eligible booking for that service.</p>', 'FAQ', 'Common questions about For Your Events.', 'published', '2026-05-19 12:10:58', '2026-06-01 22:14:51');

INSERT INTO `event_basket_items` (`id`, `event_id`, `user_id`, `service_id`, `vendor_id`, `package_name`, `extras`, `quantity`, `unit_price`, `deposit_amount`, `estimated_total`, `notes`, `quote_breakdown`, `created_at`, `updated_at`) VALUES
(10, 517, 65, 204, 87, 'Duration (3 hour(s))', '[]', 1, 570.00, 85.50, 570.00, NULL, '{\"lines\":[{\"code\":\"duration\",\"label\":\"Duration (3 hour(s))\",\"amount\":450},{\"code\":\"travel\",\"label\":\"Travel (60.0 billable km within 25–85 km zone × £2.00 \\/ km)\",\"amount\":120}],\"warnings\":[\"Venue is about 399.0 km away; this exceeds the vendor’s quoted maximum service radius (85 km). Confirm availability.\"],\"distance_km\":399}', '2026-06-01 18:13:23', '2026-06-01 18:13:23');

INSERT INTO `events` (`id`, `user_id`, `vendor_id`, `title`, `description`, `date`, `location`, `venue_name`, `postcode`, `town_city`, `indoor_outdoor`, `budget_min`, `budget_max`, `style_theme`, `notes`, `status`, `category`, `event_type`, `guest_count`, `event_setting`, `organiser_pitch_fee`, `latitude`, `longitude`, `price`, `created_at`) VALUES
(501, 6, NULL, 'QA Sample Wedding', 'Seeded private event for dashboard and booking QA.', '2026-09-15', 'Manchester Town Hall', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, 'Wedding', 80, 'private', NULL, NULL, NULL, NULL, '2026-06-01 22:14:13'),
(517, 65, NULL, 'QA Summer Wedding', NULL, '2026-08-30', 'London, SW1A 1AA', NULL, 'SW1A 1AA', 'London', NULL, NULL, NULL, NULL, NULL, 'active', NULL, 'Wedding', 80, 'private', NULL, 51.50330000, -0.11960000, NULL, '2026-06-01 18:03:23'),
(518, 66, NULL, 'QA Birthday Party', NULL, '2026-09-29', 'London, SW1A 1AA', NULL, 'SW1A 1AA', 'London', NULL, NULL, NULL, NULL, NULL, 'active', NULL, 'Birthday', 120, 'private', NULL, 51.50330000, -0.11960000, NULL, '2026-06-01 18:03:23'),
(528, 91, NULL, 'Newcastle Quayside Wedding', NULL, '2026-08-30', 'Newcastle upon Tyne, NE1 1AA', NULL, 'NE1 1AA', 'Newcastle upon Tyne', NULL, NULL, NULL, NULL, NULL, 'active', NULL, 'Wedding', 90, 'private', NULL, 54.97830000, -1.61780000, NULL, '2026-06-01 18:11:11'),
(529, 92, NULL, 'Durham 30th Birthday Bash', NULL, '2026-07-31', 'Durham, DH1 1AA', NULL, 'DH1 1AA', 'Durham', NULL, NULL, NULL, NULL, NULL, 'active', NULL, 'Birthday', 60, 'private', NULL, 54.77610000, -1.57330000, NULL, '2026-06-01 18:11:11'),
(530, 91, NULL, 'Sunderland Summer Food Festival', NULL, '2026-10-29', 'Sunderland, SR1 1AA', NULL, 'SR1 1AA', 'Sunderland', NULL, NULL, NULL, NULL, NULL, 'active', NULL, 'Festival', 2000, 'public', NULL, 54.90690000, -1.38380000, NULL, '2026-06-01 18:11:11');

INSERT INTO `favourites` (`id`, `user_id`, `service_id`, `created_at`) VALUES
(4, 6, 4, '2026-06-01 22:14:13');

INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES
(4, '2026-05-11-100000', 'App\\Database\\Migrations\\AdminBackendSchema', 'default', 'App', 1780345077, 1),
(5, '2026-05-11-204257', 'App\\Database\\Migrations\\CreateBookingsTable', 'default', 'App', 1780345077, 1),
(6, '2026-05-12-120000', 'App\\Database\\Migrations\\ChatMessageModeration', 'default', 'App', 1780345077, 1),
(7, '2026-06-01-120000', 'App\\Database\\Migrations\\VendorHostProfile', 'default', 'App', 1780345077, 1);

INSERT INTO `payments` (`id`, `booking_id`, `payment_intent_id`, `payment_status`, `amount_paid`, `currency`, `payment_method`, `payment_type`, `description`, `created_at`, `updated_at`) VALUES
(501, 501, NULL, 'succeeded', 75.00, 'gbp', NULL, 'deposit', 'QA seed deposit', '2026-06-01 22:14:13', '2026-06-01 22:14:13');

INSERT INTO `service_images` (`id`, `service_id`, `image_path`, `thumbnail_path`, `is_primary`, `created_at`, `updated_at`) VALUES
(2, 2, 'uploads/services/1716936655_2a9474e339e1b2141db3.jpg', 'uploads/services/thumb_1716936655_2a9474e339e1b2141db3.jpg', 1, '2026-06-01 22:14:13', '2026-06-01 22:14:13'),
(3, 3, 'uploads/services/1716936809_ea5338b2e4ba5823d2f9.jpg', 'uploads/services/thumb_1716936809_ea5338b2e4ba5823d2f9.jpg', 1, '2026-06-01 22:14:13', '2026-06-01 22:14:13'),
(4, 4, 'uploads/services/1716937372_8e6a7964ed534149d3cb.jpeg', 'uploads/services/thumb_1716937372_8e6a7964ed534149d3cb.jpeg', 1, '2026-06-01 22:14:13', '2026-06-01 22:14:13'),
(5, 5, 'uploads/services/1716937744_df87fb10763e1b292fb8.jpeg', 'uploads/services/thumb_1716937744_df87fb10763e1b292fb8.jpeg', 1, '2026-06-01 22:14:13', '2026-06-01 22:14:13'),
(76, 140, 'uploads/services/1735988119_d83adeb47688289c69a5.jpg', 'uploads/services/1735988119_d83adeb47688289c69a5.jpg', 1, '2026-06-01 19:03:23', '2026-06-01 19:03:23'),
(77, 141, 'uploads/services/1736065387_0781e62ad503443dd333.jpg', 'uploads/services/1736065387_0781e62ad503443dd333.jpg', 1, '2026-06-01 19:03:23', '2026-06-01 19:03:23'),
(78, 142, 'uploads/services/1736087953_ca438efc6da58afad47d.jpg', 'uploads/services/1736087953_ca438efc6da58afad47d.jpg', 1, '2026-06-01 19:03:23', '2026-06-01 19:03:23'),
(79, 143, 'uploads/services/1736091435_0297b3addf09add66f13.jpg', 'uploads/services/1736091435_0297b3addf09add66f13.jpg', 1, '2026-06-01 19:03:23', '2026-06-01 19:03:23'),
(80, 204, 'uploads/services/example_bar.jpg', 'uploads/services/example_bar.jpg', 1, '2026-06-01 19:11:11', '2026-06-01 19:11:11'),
(81, 204, 'uploads/services/example_bar2.jpg', 'uploads/services/example_bar2.jpg', 0, '2026-06-01 19:11:11', '2026-06-01 19:11:11'),
(82, 205, 'uploads/services/example_jazz_band.jpg', 'uploads/services/example_jazz_band.jpg', 1, '2026-06-01 19:11:11', '2026-06-01 19:11:11'),
(83, 205, 'uploads/services/example_jazz_band2.jpg', 'uploads/services/example_jazz_band2.jpg', 0, '2026-06-01 19:11:11', '2026-06-01 19:11:11'),
(84, 206, 'uploads/services/example_magician.jpg', 'uploads/services/example_magician.jpg', 1, '2026-06-01 19:11:11', '2026-06-01 19:11:11'),
(85, 206, 'uploads/services/example_magician2.jpg', 'uploads/services/example_magician2.jpg', 0, '2026-06-01 19:11:11', '2026-06-01 19:11:11'),
(86, 206, 'uploads/services/example_magician3.jpg', 'uploads/services/example_magician3.jpg', 0, '2026-06-01 19:11:11', '2026-06-01 19:11:11'),
(87, 206, 'uploads/services/example_magician4.jpg', 'uploads/services/example_magician4.jpg', 0, '2026-06-01 19:11:11', '2026-06-01 19:11:11'),
(88, 207, 'uploads/services/example_cinema1.jpg', 'uploads/services/example_cinema1.jpg', 1, '2026-06-01 19:11:11', '2026-06-01 19:11:11'),
(89, 207, 'uploads/services/example_cinema2.jpg', 'uploads/services/example_cinema2.jpg', 0, '2026-06-01 19:11:11', '2026-06-01 19:11:11'),
(90, 207, 'uploads/services/example_cinema3.jpg', 'uploads/services/example_cinema3.jpg', 0, '2026-06-01 19:11:11', '2026-06-01 19:11:11'),
(91, 208, 'uploads/services/example_bouncycastle.jpg', 'uploads/services/example_bouncycastle.jpg', 1, '2026-06-01 19:11:11', '2026-06-01 19:11:11'),
(92, 208, 'uploads/services/example_bouncycastle2.jpg', 'uploads/services/example_bouncycastle2.jpg', 0, '2026-06-01 19:11:11', '2026-06-01 19:11:11'),
(93, 209, 'uploads/services/example_bar3.jpg', 'uploads/services/example_bar3.jpg', 1, '2026-06-01 19:11:11', '2026-06-01 19:11:11'),
(94, 210, 'uploads/services/example_games.jpg', 'uploads/services/example_games.jpg', 1, '2026-06-01 19:11:11', '2026-06-01 19:11:11'),
(95, 211, 'uploads/services/example_chatgpt_image_jun_1_2026_at_04_51_08_pm.jpg', 'uploads/services/example_chatgpt_image_jun_1_2026_at_04_51_08_pm.jpg', 1, '2026-06-01 19:11:11', '2026-06-01 19:11:11'),
(96, 212, 'uploads/services/example_chocolate_fountain.jpg', 'uploads/services/example_chocolate_fountain.jpg', 1, '2026-06-01 19:11:11', '2026-06-01 19:11:11'),
(97, 212, 'uploads/services/example_chocolate_fountain2.jpg', 'uploads/services/example_chocolate_fountain2.jpg', 0, '2026-06-01 19:11:11', '2026-06-01 19:11:11'),
(98, 213, 'uploads/services/example_vintage_photobooth.jpg', 'uploads/services/example_vintage_photobooth.jpg', 1, '2026-06-01 19:11:11', '2026-06-01 19:11:11'),
(99, 213, 'uploads/services/example_vintage_photobooth2.jpg', 'uploads/services/example_vintage_photobooth2.jpg', 0, '2026-06-01 19:11:11', '2026-06-01 19:11:11'),
(100, 214, 'uploads/services/example_led2.jpg', 'uploads/services/example_led2.jpg', 1, '2026-06-01 19:11:11', '2026-06-01 19:11:11'),
(101, 215, 'uploads/services/example_marquee.jpg', 'uploads/services/example_marquee.jpg', 1, '2026-06-01 19:11:11', '2026-06-01 19:11:11'),
(102, 215, 'uploads/services/example_marquee2.jpg', 'uploads/services/example_marquee2.jpg', 0, '2026-06-01 19:11:11', '2026-06-01 19:11:11'),
(103, 216, 'uploads/services/example_led_dancefloor.jpg', 'uploads/services/example_led_dancefloor.jpg', 1, '2026-06-01 19:11:11', '2026-06-01 19:11:11'),
(104, 217, 'uploads/services/example_chair_covers.jpg', 'uploads/services/example_chair_covers.jpg', 1, '2026-06-01 19:11:11', '2026-06-01 19:11:11'),
(105, 217, 'uploads/services/example_chair_covers2.jpg', 'uploads/services/example_chair_covers2.jpg', 0, '2026-06-01 19:11:11', '2026-06-01 19:11:11'),
(106, 218, 'uploads/services/example_lettering.jpg', 'uploads/services/example_lettering.jpg', 1, '2026-06-01 19:11:11', '2026-06-01 19:11:11'),
(107, 218, 'uploads/services/example_lettering2.jpg', 'uploads/services/example_lettering2.jpg', 0, '2026-06-01 19:11:11', '2026-06-01 19:11:11'),
(108, 219, 'uploads/services/example_chatgpt_image_jun_1_2026_at_04_51_08_pm.jpg', 'uploads/services/example_chatgpt_image_jun_1_2026_at_04_51_08_pm.jpg', 1, '2026-06-01 19:11:11', '2026-06-01 19:11:11'),
(109, 220, 'uploads/services/example_photo_props.jpg', 'uploads/services/example_photo_props.jpg', 1, '2026-06-01 19:11:11', '2026-06-01 19:11:11'),
(110, 221, 'uploads/services/example_car.jpg', 'uploads/services/example_car.jpg', 1, '2026-06-01 19:11:11', '2026-06-01 19:11:11'),
(111, 221, 'uploads/services/example_car2.jpg', 'uploads/services/example_car2.jpg', 0, '2026-06-01 19:11:11', '2026-06-01 19:11:11'),
(112, 222, 'uploads/services/example_lettering2.jpg', 'uploads/services/example_lettering2.jpg', 1, '2026-06-01 19:11:11', '2026-06-01 19:11:11'),
(113, 223, 'uploads/services/example_led_dancefloor.jpg', 'uploads/services/example_led_dancefloor.jpg', 1, '2026-06-01 19:11:11', '2026-06-01 19:11:11');

INSERT INTO `services` (`id`, `vendor_id`, `title`, `description`, `short_description`, `image`, `price`, `min_capacity`, `max_capacity`, `setup_minutes`, `breakdown_minutes`, `min_notice_days`, `space_required`, `indoor_outdoor`, `power_required`, `water_required`, `vehicle_access_required`, `equipment_provided`, `category_id`, `subcategory_id`, `third_category_id`, `latitude`, `longitude`, `deleted_at`, `free_coverage_radius`, `paid_coverage_radius`, `travel_fee_per_km`, `cancellation_policy`, `service_tags`, `service_location`, `all_travel_included`, `no_travel_limit`, `event_types`, `commission_percentage`, `license`, `attendance_thresholds`, `max_pitch_fees`, `status`, `created_at`, `updated_at`) VALUES
(2, 3, 'Sweetie Sweet Cart', 'Indulge your sweet tooth at the Sweetie Cart Candy Stall, a whimsical haven for candy lovers of all ages!', NULL, '1716936655_2a9474e339e1b2141db3.jpg', 150.00, NULL, NULL, NULL, NULL, NULL, NULL, 'both', 0, 0, 0, 0, 1, 104, 1035, NULL, NULL, NULL, NULL, NULL, NULL, 'Cancel up to 14 days before for a full refund of your deposit.', NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, 'active', '2026-06-01 22:14:13', '2026-06-01 22:14:13'),
(3, 3, 'Sweetie Sweet Cart', 'Indulge your sweet tooth at the Sweetie Cart Candy Stall, a whimsical haven for candy lovers of all ages!', NULL, '1716936809_ea5338b2e4ba5823d2f9.jpg', 150.00, NULL, NULL, NULL, NULL, NULL, NULL, 'both', 0, 0, 0, 0, 1, 104, 1035, NULL, NULL, NULL, NULL, NULL, NULL, 'Cancel up to 14 days before for a full refund of your deposit.', NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, 'active', '2026-06-01 22:14:13', '2026-06-01 22:14:13'),
(4, 3, 'Mr Beatys Burgers', 'BurgerBurgerBurgerBurgerBurgerBurgerBurger', NULL, '1716937372_8e6a7964ed534149d3cb.jpeg', 240.00, NULL, NULL, NULL, NULL, NULL, NULL, 'both', 0, 0, 0, 0, 1, 101, 1009, NULL, NULL, NULL, NULL, NULL, NULL, '48 hours notice required for deposit refund.', NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, 'active', '2026-06-01 22:14:13', '2026-06-01 22:14:13'),
(5, 3, 'Dinky Donuts', 'Delight in the irresistible aroma and melt-in-your-mouth goodness of Dinky Donuts!', NULL, '1716937744_df87fb10763e1b292fb8.jpeg', 90.00, NULL, NULL, NULL, NULL, NULL, NULL, 'both', 0, 0, 0, 0, 1, 104, 1038, NULL, NULL, NULL, NULL, NULL, NULL, 'Cancel up to 7 days before for a full refund.', NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, 'active', '2026-06-01 22:14:13', '2026-06-01 22:14:13'),
(90, 3, '(Inactive QA) Vintage photobooth', 'Seeded inactive listing for vendor dashboard QA (services tab + filters).', NULL, NULL, 320.00, NULL, NULL, NULL, NULL, NULL, NULL, 'both', 0, 0, 0, 0, 11, 132, 1224, NULL, NULL, NULL, NULL, NULL, NULL, 'Full refund up to 30 days before the event.', NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, 'inactive', '2026-06-01 22:14:13', '2026-06-01 22:14:13'),
(140, 67, 'QA Catering Co — Buffet & Grazing', 'Seasonal grazing tables and hot buffets priced per guest. Tiered rates reward larger parties.', 'Per-guest catering with tiered pricing by headcount.', 'services/1735988119_d83adeb47688289c69a5.jpg', 6.00, NULL, NULL, NULL, NULL, NULL, NULL, 'both', 0, 0, 0, 0, 1, NULL, NULL, 51.50330000, -0.11960000, NULL, NULL, NULL, NULL, 'Full refund if cancelled at least 14 days before the event date.', NULL, 'London', 0, 0, 'private', NULL, NULL, NULL, NULL, 'active', '2026-06-01 18:03:23', '2026-06-01 18:03:23'),
(141, 67, 'QA Snapshot — Photo Booth Hire', 'Open-air photo booth with props, instant prints and an attendant. Choose a session length.', 'Photo booth hire charged by session length.', 'services/1736065387_0781e62ad503443dd333.jpg', 250.00, NULL, NULL, NULL, NULL, NULL, NULL, 'both', 0, 0, 0, 0, 11, NULL, NULL, 51.50330000, -0.11960000, NULL, NULL, NULL, NULL, 'Full refund if cancelled at least 14 days before the event date.', NULL, 'London', 0, 0, 'private', NULL, NULL, NULL, NULL, 'active', '2026-06-01 18:03:23', '2026-06-01 18:03:23'),
(142, 68, 'QA Grand Marquees — Marquee Packages', 'Weatherproof marquees with flooring, lighting and furniture. Pick the package that fits your event.', 'Marquee hire in Bronze, Silver and Gold packages.', 'services/1736087953_ca438efc6da58afad47d.jpg', 750.00, NULL, NULL, NULL, NULL, NULL, NULL, 'both', 0, 0, 0, 0, 16, NULL, NULL, 51.50330000, -0.11960000, NULL, NULL, NULL, NULL, 'Full refund if cancelled at least 14 days before the event date.', NULL, 'London', 0, 0, 'private', NULL, NULL, NULL, NULL, 'active', '2026-06-01 18:03:23', '2026-06-01 18:03:23'),
(143, 68, 'QA Comfort Hire — Event Chair Hire', 'Elegant Chiavari chairs delivered, set up and collected. Priced per chair, minimum 50.', 'Chiavari chair hire priced per chair.', 'services/1736091435_0297b3addf09add66f13.jpg', 4.00, NULL, NULL, NULL, NULL, NULL, NULL, 'both', 0, 0, 0, 0, 9, NULL, NULL, 51.50330000, -0.11960000, NULL, NULL, NULL, NULL, 'Full refund if cancelled at least 14 days before the event date.', NULL, 'London', 0, 0, 'private', NULL, NULL, NULL, NULL, 'active', '2026-06-01 18:03:23', '2026-06-01 18:03:23'),
(204, 87, 'Quayside Mobile Cocktail Bar', 'A fully-stocked mobile bar with experienced mixologists. Perfect for weddings, parties and festivals across the North East. Choose a hire length and add upgrades.', 'Stylish mobile bar serving cocktails, beers and mocktails.', 'services/example_bar.jpg', 450.00, NULL, NULL, NULL, NULL, NULL, NULL, 'both', 0, 0, 0, 0, 1, 103, 1024, 54.97830000, -1.61780000, NULL, 25, 60, 2.00, 'Full refund if cancelled at least 14 days before the event date.', NULL, 'Newcastle upon Tyne', 0, 0, 'public,private', 12.00, NULL, NULL, NULL, 'active', '2026-06-01 18:11:11', '2026-06-01 18:11:11'),
(205, 88, 'Northern Soul Jazz Band', 'An award-winning live band covering jazz standards, soul classics and modern crowd-pleasers. Configurable from an intimate trio to a full nine-piece with brass.', 'Live jazz, soul and swing for ceremonies, receptions and events.', 'services/example_jazz_band.jpg', 850.00, NULL, NULL, NULL, NULL, NULL, NULL, 'both', 0, 0, 0, 0, 4, 112, NULL, 54.77610000, -1.57330000, NULL, NULL, NULL, NULL, 'Full refund if cancelled at least 14 days before the event date.', NULL, 'Durham', 1, 0, 'public,private', 10.00, NULL, NULL, NULL, 'active', '2026-06-01 18:11:11', '2026-06-01 18:11:11'),
(206, 87, 'Mr Marvello — Strolling Magician', 'Jaw-dropping close-up magic and mind-reading performed table to table. Ideal as a drinks-reception ice-breaker or a public-event headline act.', 'Sleight-of-hand and close-up magic that mingles with your guests.', 'services/example_magician.jpg', 300.00, NULL, NULL, NULL, NULL, NULL, NULL, 'both', 0, 0, 0, 0, 5, 114, NULL, 54.95260000, -1.60330000, NULL, 30, NULL, 1.20, 'Full refund if cancelled at least 14 days before the event date.', NULL, 'Gateshead', 0, 1, 'public,private', 8.00, NULL, NULL, NULL, 'active', '2026-06-01 18:11:11', '2026-06-01 18:11:11'),
(207, 89, 'Tyne Open-Air Cinema', 'Turn any space into an open-air cinema. Inflatable screens from 4m to 12m with full PA, projection and optional deckchair seating. Great for private film nights or public screenings.', 'Big-screen outdoor cinema hire with sound, seating and screen.', 'services/example_cinema1.jpg', 700.00, NULL, NULL, NULL, NULL, NULL, NULL, 'both', 0, 0, 0, 0, 20, 159, NULL, 54.57420000, -1.23500000, NULL, 20, 80, 2.50, 'Full refund if cancelled at least 14 days before the event date.', NULL, 'Middlesbrough', 0, 0, 'public,private', 15.00, NULL, NULL, NULL, 'active', '2026-06-01 18:11:11', '2026-06-01 18:11:11'),
(208, 88, 'Bounce Kingdom Inflatables', 'Safety-tested inflatables for children\'s parties, school fairs and public family days. Fully insured with trained attendants on larger units.', 'Bouncy castles and inflatable fun for parties and fetes.', 'services/example_bouncycastle.jpg', 120.00, NULL, NULL, NULL, NULL, NULL, NULL, 'both', 0, 0, 0, 0, 6, 119, NULL, 54.77610000, -1.57330000, NULL, 15, 35, 1.50, 'Full refund if cancelled at least 14 days before the event date.', NULL, 'Durham', 0, 0, 'public,private', 10.00, NULL, NULL, NULL, 'active', '2026-06-01 18:11:11', '2026-06-01 18:11:11'),
(209, 89, 'Geordie Street Food Co', 'A converted horsebox slinging loaded fries, gourmet burgers and vegan wraps. Available as a paid pitch at festivals and public events across the region.', 'Street-food trader for festivals, markets and public events.', 'services/example_bar3.jpg', 0.00, NULL, NULL, NULL, NULL, NULL, NULL, 'both', 0, 0, 0, 0, 1, 101, 1021, 54.57050000, -1.31870000, NULL, 40, 120, 1.80, 'Full refund if cancelled at least 14 days before the event date.', NULL, 'Stockton-on-Tees', 0, 0, 'public', 18.00, NULL, NULL, NULL, 'active', '2026-06-01 18:11:11', '2026-06-01 18:11:11'),
(210, 87, 'Big Top Funfair Games', 'Traditional fairground game stalls operated by our team. Pay-to-play or pre-paid wristband options for family fun days, school fetes and public festivals.', 'Hook-a-duck, coconut shy and fairground stalls for public days.', 'services/example_games.jpg', 0.00, NULL, NULL, NULL, NULL, NULL, NULL, 'both', 0, 0, 0, 0, 20, 160, NULL, 54.99960000, -1.43260000, NULL, 30, 90, 1.60, 'Full refund if cancelled at least 14 days before the event date.', NULL, 'South Shields', 0, 0, 'public', 20.00, NULL, NULL, NULL, 'active', '2026-06-01 18:11:11', '2026-06-01 18:11:11'),
(211, 88, 'County Durham Grazing Tables', 'Seasonal grazing tables piled with artisan cheeses, charcuterie, breads and dips. Priced per guest with tiered rates that reward larger parties.', 'Abundant grazing tables and buffets priced per guest.', 'services/example_chatgpt_image_jun_1_2026_at_04_51_08_pm.jpg', 9.00, NULL, NULL, NULL, NULL, NULL, NULL, 'both', 0, 0, 0, 0, 1, 102, 1020, 54.77610000, -1.57330000, NULL, 20, 70, 1.40, 'Full refund if cancelled at least 14 days before the event date.', NULL, 'Durham', 0, 0, 'private', NULL, NULL, NULL, NULL, 'active', '2026-06-01 18:11:11', '2026-06-01 18:11:11'),
(212, 89, 'Belgian Chocolate Fountain Co', 'A showstopping chocolate fountain with a generous spread of fresh fruit, marshmallows and treats for dipping. Attended throughout and priced per guest.', 'Flowing chocolate fountains with dippers, priced per guest.', 'services/example_chocolate_fountain.jpg', 5.00, NULL, NULL, NULL, NULL, NULL, NULL, 'both', 0, 0, 0, 0, 2, 106, NULL, 54.52350000, -1.55980000, NULL, 25, 60, 1.30, 'Full refund if cancelled at least 14 days before the event date.', NULL, 'Darlington', 0, 0, 'private', NULL, NULL, NULL, NULL, 'active', '2026-06-01 18:11:11', '2026-06-01 18:11:11'),
(213, 87, 'Vintage Photo Booth Hire', 'A retro-styled enclosed booth with unlimited instant prints, a prop box and a friendly attendant. Choose a session length to suit your event.', 'Enclosed vintage photo booth with props and instant prints.', 'services/example_vintage_photobooth.jpg', 295.00, NULL, NULL, NULL, NULL, NULL, NULL, 'both', 0, 0, 0, 0, 11, 132, NULL, 54.97830000, -1.61780000, NULL, 20, 65, 1.75, 'Full refund if cancelled at least 14 days before the event date.', NULL, 'Newcastle upon Tyne', 0, 0, 'private', NULL, NULL, NULL, NULL, 'active', '2026-06-01 18:11:11', '2026-06-01 18:11:11'),
(214, 87, 'Premier Wedding DJ & Disco', 'An experienced wedding and party DJ with a curated playlist, dancefloor lighting and a crisp sound system. Booked by session length with optional upgrades.', 'Professional wedding DJ with full lighting and PA, by the hour.', 'services/example_led2.jpg', 350.00, NULL, NULL, NULL, NULL, NULL, NULL, 'both', 0, 0, 0, 0, 4, 111, NULL, 54.95260000, -1.60330000, NULL, 30, 75, 1.50, 'Full refund if cancelled at least 14 days before the event date.', NULL, 'Gateshead', 0, 0, 'private', NULL, NULL, NULL, NULL, 'active', '2026-06-01 18:11:11', '2026-06-01 18:11:11'),
(215, 90, 'Grand Marquee Hire', 'Elegant clear-span marquees with flooring, lighting and furniture. Pick the package that fits your guest count — we handle delivery, build and take-down.', 'Weatherproof marquees in Bronze, Silver and Gold packages.', 'services/example_marquee.jpg', 950.00, NULL, NULL, NULL, NULL, NULL, NULL, 'both', 0, 0, 0, 0, 17, 150, NULL, 55.16830000, -1.69120000, NULL, 30, 100, 2.20, 'Full refund if cancelled at least 14 days before the event date.', NULL, 'Morpeth', 0, 0, 'private', NULL, NULL, NULL, NULL, 'active', '2026-06-01 18:11:11', '2026-06-01 18:11:11'),
(216, 87, 'LED Dance Floor Hire', 'A dazzling LED starlit or sequence dancefloor, professionally installed and removed. Choose the floor size to match your venue and guest numbers.', 'Light-up LED dance floors in three sizes.', 'services/example_led_dancefloor.jpg', 380.00, NULL, NULL, NULL, NULL, NULL, NULL, 'both', 0, 0, 0, 0, 10, 130, NULL, 54.90690000, -1.38380000, NULL, 20, 50, 1.90, 'Full refund if cancelled at least 14 days before the event date.', NULL, 'Sunderland', 0, 0, 'private', NULL, NULL, NULL, NULL, 'active', '2026-06-01 18:11:11', '2026-06-01 18:11:11'),
(217, 88, 'Chair Cover & Sash Hire', 'Crisp fitted chair covers with a coloured sash of your choice. Priced per chair with a 30-chair minimum; we fit and collect.', 'Fitted chair covers delivered, fitted and collected, per chair.', 'services/example_chair_covers.jpg', 2.75, NULL, NULL, NULL, NULL, NULL, NULL, 'both', 0, 0, 0, 0, 7, 121, NULL, 54.77610000, -1.57330000, NULL, 20, 60, 1.20, 'Full refund if cancelled at least 14 days before the event date.', NULL, 'Durham', 0, 0, 'private', NULL, NULL, NULL, NULL, 'active', '2026-06-01 18:11:11', '2026-06-01 18:11:11'),
(218, 90, 'Light-Up Letters & Numbers', 'Make a statement with 4ft warm-white light-up letters and numbers. Priced per character with delivery and set-up across Northumberland and beyond.', 'Giant 4ft illuminated letters and numbers, priced per character.', 'services/example_lettering.jpg', 45.00, NULL, NULL, NULL, NULL, NULL, NULL, 'both', 0, 0, 0, 0, 7, 122, NULL, 55.41290000, -1.70600000, NULL, 25, 80, 1.60, 'Full refund if cancelled at least 14 days before the event date.', NULL, 'Alnwick', 0, 0, 'private', NULL, NULL, NULL, NULL, 'active', '2026-06-01 18:11:11', '2026-06-01 18:11:11'),
(219, 89, 'Personalised Wedding Favours', 'Hand-finished personalised favours — seed cards, mini candles or sweet jars — printed with your names and date. Posted directly to you, priced per favour.', 'Bespoke favours posted UK-wide, priced per favour.', 'services/example_chatgpt_image_jun_1_2026_at_04_51_08_pm.jpg', 1.80, NULL, NULL, NULL, NULL, NULL, NULL, 'both', 0, 0, 0, 0, 15, 144, NULL, 54.57420000, -1.23500000, NULL, NULL, NULL, NULL, 'Full refund if cancelled at least 14 days before the event date.', NULL, 'Middlesbrough', 0, 0, 'private', NULL, NULL, NULL, NULL, 'active', '2026-06-01 18:11:11', '2026-06-01 18:11:11'),
(220, 87, 'Photo Booth Prop Packs', 'Curated photo-prop packs — signs, glasses, hats and boas — by theme. Collect in person around Tyneside or have them posted, priced per pack.', 'Themed prop packs to hire or have posted, priced per pack.', 'services/example_photo_props.jpg', 15.00, NULL, NULL, NULL, NULL, NULL, NULL, 'both', 0, 0, 0, 0, 11, 134, NULL, 54.97830000, -1.61780000, NULL, 15, 40, 1.00, 'Full refund if cancelled at least 14 days before the event date.', NULL, 'Newcastle upon Tyne', 0, 0, 'private', NULL, NULL, NULL, NULL, 'active', '2026-06-01 18:11:11', '2026-06-01 18:11:11'),
(221, 90, 'Classic Wedding Car Hire', 'A hand-picked fleet of vintage and classic cars with a uniformed chauffeur. Every wedding is different, so we quote each booking individually based on cars, mileage and timings.', 'Chauffeured vintage and classic wedding cars — quoted per booking.', 'services/example_car.jpg', 0.00, NULL, NULL, NULL, NULL, NULL, NULL, 'both', 0, 0, 0, 0, 12, 135, NULL, 55.41290000, -1.70600000, NULL, 40, NULL, 1.50, 'Full refund if cancelled at least 14 days before the event date.', NULL, 'Alnwick', 0, 1, 'private', NULL, NULL, NULL, NULL, 'active', '2026-06-01 18:11:11', '2026-06-01 18:11:11'),
(222, 90, 'Bespoke Luxury Event Styling', 'From mood-board to install, our stylists craft a complete look — backdrops, tablescapes, florals and signage. Bespoke by nature, so each project is quoted to your brief.', 'Full design and styling for weddings and events — price on request.', 'services/example_lettering2.jpg', 0.00, NULL, NULL, NULL, NULL, NULL, NULL, 'both', 0, 0, 0, 0, 7, 120, NULL, 54.97090000, -2.10150000, NULL, 30, 120, 2.00, 'Full refund if cancelled at least 14 days before the event date.', NULL, 'Hexham', 0, 0, 'private', NULL, NULL, NULL, NULL, 'active', '2026-06-01 18:11:11', '2026-06-01 18:11:11'),
(223, 87, 'Full-Service Event Production', 'Technical production for conferences, awards nights and large private events: staging, line-array sound, intelligent lighting and screens. Scoped and quoted per event.', 'Staging, sound, lighting and AV production — quoted bespoke.', 'services/example_led_dancefloor.jpg', 0.00, NULL, NULL, NULL, NULL, NULL, NULL, 'both', 0, 0, 0, 0, 18, 155, NULL, 54.97830000, -1.61780000, NULL, NULL, NULL, NULL, 'Full refund if cancelled at least 14 days before the event date.', NULL, 'Newcastle upon Tyne', 1, 0, 'private,corporate', NULL, NULL, NULL, NULL, 'active', '2026-06-01 18:11:11', '2026-06-01 18:11:11');

INSERT INTO `services_custom_duration_pricing` (`id`, `service_id`, `private_event_pricing_id`, `duration_type`, `duration`, `price`) VALUES
(40, 141, 44, 'hour', 3, 250.00),
(41, 141, 44, 'hour', 5, 400.00),
(42, 141, 44, 'hour', 8, 600.00),
(88, 204, 101, 'hour', 3, 450.00),
(89, 204, 101, 'hour', 5, 650.00),
(90, 204, 101, 'hour', 8, 950.00),
(91, 206, 103, 'hour', 1, 300.00),
(92, 206, 103, 'hour', 2, 500.00),
(93, 206, 103, 'hour', 3, 650.00),
(94, 208, 105, 'hour', 4, 120.00),
(95, 208, 105, 'hour', 6, 160.00),
(96, 208, 105, 'day', 1, 220.00),
(97, 213, 108, 'hour', 3, 295.00),
(98, 213, 108, 'hour', 4, 350.00),
(99, 213, 108, 'hour', 6, 480.00),
(100, 214, 109, 'hour', 4, 350.00),
(101, 214, 109, 'hour', 5, 425.00),
(102, 214, 109, 'hour', 7, 575.00);

INSERT INTO `services_event_types` (`id`, `service_id`, `event_type`) VALUES
(62, 140, 'private'),
(63, 141, 'private'),
(64, 142, 'private'),
(65, 143, 'private'),
(144, 204, 'public'),
(145, 204, 'private'),
(146, 205, 'public'),
(147, 205, 'private'),
(148, 206, 'public'),
(149, 206, 'private'),
(150, 207, 'public'),
(151, 207, 'private'),
(152, 208, 'public'),
(153, 208, 'private'),
(154, 209, 'public'),
(155, 210, 'public'),
(156, 211, 'private'),
(157, 212, 'private'),
(158, 213, 'private'),
(159, 214, 'private'),
(160, 215, 'private'),
(161, 216, 'private'),
(162, 217, 'private'),
(163, 218, 'private'),
(164, 219, 'private'),
(165, 220, 'private'),
(166, 221, 'private'),
(167, 222, 'private'),
(168, 223, 'private'),
(169, 223, 'corporate');

INSERT INTO `services_guest_based_pricing` (`id`, `service_id`, `private_event_pricing_id`, `min_guest`, `max_guest`, `guest_price`) VALUES
(22, 140, 43, 1, 50, 8.00),
(23, 140, 43, 51, 150, 6.00),
(24, 140, 43, 151, 1000, 5.00),
(43, 211, 106, 1, 40, 12.00),
(44, 211, 106, 41, 120, 9.00),
(45, 211, 106, 121, 1000, 7.50),
(46, 212, 107, 1, 60, 6.50),
(47, 212, 107, 61, 150, 5.00),
(48, 212, 107, 151, 600, 4.25);

INSERT INTO `services_locations` (`id`, `service_id`, `fulfillment_type`, `service_location`, `location`, `latitude`, `longitude`, `all_travel_included`, `no_travel_limit`, `free_coverage_radius`, `paid_coverage_radius`, `travel_fee_per_km`, `strict_travel_radius`, `postal_fee`, `free_postage_above`, `delivery_lead_time_days`) VALUES
(45, 140, 'in_person', 'London', 'London', 51.50330000, -0.11960000, 0, 0, 80, 120, 1.50, 0, NULL, NULL, NULL),
(46, 141, 'in_person', 'London', 'London', 51.50330000, -0.11960000, 0, 0, 80, 120, 1.50, 0, NULL, NULL, NULL),
(47, 142, 'in_person', 'London', 'London', 51.50330000, -0.11960000, 0, 0, 80, 120, 1.50, 0, NULL, NULL, NULL),
(48, 143, 'in_person', 'London', 'London', 51.50330000, -0.11960000, 0, 0, 80, 120, 1.50, 0, NULL, NULL, NULL),
(109, 204, 'in_person', 'Newcastle upon Tyne', 'Newcastle upon Tyne', 54.97830000, -1.61780000, 0, 0, 25, 60, 2.00, 0, NULL, NULL, NULL),
(110, 205, 'in_person', 'Durham', 'Durham', 54.77610000, -1.57330000, 1, 0, NULL, NULL, NULL, 0, NULL, NULL, NULL),
(111, 206, 'in_person', 'Gateshead', 'Gateshead', 54.95260000, -1.60330000, 0, 1, 30, NULL, 1.20, 0, NULL, NULL, NULL),
(112, 207, 'in_person', 'Middlesbrough', 'Middlesbrough', 54.57420000, -1.23500000, 0, 0, 20, 80, 2.50, 0, NULL, NULL, NULL),
(113, 208, 'in_person', 'Durham', 'Durham', 54.77610000, -1.57330000, 0, 0, 15, 35, 1.50, 1, NULL, NULL, NULL),
(114, 209, 'in_person', 'Stockton-on-Tees', 'Stockton-on-Tees', 54.57050000, -1.31870000, 0, 0, 40, 120, 1.80, 0, NULL, NULL, NULL),
(115, 210, 'in_person', 'South Shields', 'South Shields', 54.99960000, -1.43260000, 0, 0, 30, 90, 1.60, 0, NULL, NULL, NULL),
(116, 211, 'in_person', 'Durham', 'Durham', 54.77610000, -1.57330000, 0, 0, 20, 70, 1.40, 0, NULL, NULL, NULL),
(117, 212, 'in_person', 'Darlington', 'Darlington', 54.52350000, -1.55980000, 0, 0, 25, 60, 1.30, 0, NULL, NULL, NULL),
(118, 213, 'in_person', 'Newcastle upon Tyne', 'Newcastle upon Tyne', 54.97830000, -1.61780000, 0, 0, 20, 65, 1.75, 0, NULL, NULL, NULL),
(119, 214, 'in_person', 'Gateshead', 'Gateshead', 54.95260000, -1.60330000, 0, 0, 30, 75, 1.50, 0, NULL, NULL, NULL),
(120, 215, 'in_person', 'Morpeth', 'Morpeth', 55.16830000, -1.69120000, 0, 0, 30, 100, 2.20, 0, NULL, NULL, NULL),
(121, 216, 'in_person', 'Sunderland', 'Sunderland', 54.90690000, -1.38380000, 0, 0, 20, 50, 1.90, 1, NULL, NULL, NULL),
(122, 217, 'in_person', 'Durham', 'Durham', 54.77610000, -1.57330000, 0, 0, 20, 60, 1.20, 0, NULL, NULL, NULL),
(123, 218, 'in_person', 'Alnwick', 'Alnwick', 55.41290000, -1.70600000, 0, 0, 25, 80, 1.60, 0, NULL, NULL, NULL),
(124, 219, 'postal', 'Middlesbrough', 'Middlesbrough', 54.57420000, -1.23500000, 0, 0, NULL, NULL, NULL, 0, 6.95, 150.00, 10),
(125, 220, 'both', 'Newcastle upon Tyne', 'Newcastle upon Tyne', 54.97830000, -1.61780000, 0, 0, 15, 40, 1.00, 0, 4.50, 80.00, 5),
(126, 221, 'in_person', 'Alnwick', 'Alnwick', 55.41290000, -1.70600000, 0, 1, 40, NULL, 1.50, 0, NULL, NULL, NULL),
(127, 222, 'in_person', 'Hexham', 'Hexham', 54.97090000, -2.10150000, 0, 0, 30, 120, 2.00, 0, NULL, NULL, NULL),
(128, 223, 'in_person', 'Newcastle upon Tyne', 'Newcastle upon Tyne', 54.97830000, -1.61780000, 1, 0, NULL, NULL, NULL, 0, NULL, NULL, NULL);

INSERT INTO `services_optional_extras` (`id`, `service_id`, `name`, `price`, `pricing_type`, `min_quantity`, `max_quantity`, `unit_label`, `description`, `quantity`) VALUES
(52, 140, 'Prosecco reception (per guest)', 4.50, 'per_item', NULL, NULL, 'guests', 'Optional add-on (not included in the base quote).', 1),
(53, 141, 'Guest book album', 35.00, 'flat', NULL, NULL, NULL, 'Optional add-on (not included in the base quote).', 1),
(54, 142, 'Festoon lighting upgrade', 150.00, 'flat', NULL, NULL, NULL, 'Optional add-on (not included in the base quote).', 1),
(55, 143, 'Chair sash (per chair)', 1.25, 'per_item', NULL, NULL, 'chairs', 'Optional add-on (not included in the base quote).', 1),
(134, 204, 'Signature cocktail upgrade', 3.50, 'per_item', NULL, NULL, 'guests', 'Premium cocktail menu, priced per guest.', 1),
(135, 204, 'Copper glassware hire', 120.00, 'flat', NULL, NULL, NULL, 'Full set of copper-style serving glassware.', 1),
(136, 205, 'Additional 45 minute set', 250.00, 'flat', NULL, NULL, NULL, 'Extra live set beyond the package allowance.', 1),
(137, 205, 'Festoon-lit stage backdrop', 180.00, 'flat', NULL, NULL, NULL, 'Styled stage backdrop with warm festoon lighting.', 1),
(138, 206, 'Bespoke trick with your branding', 95.00, 'flat', NULL, NULL, NULL, 'A custom routine featuring your logo or message.', 1),
(139, 207, 'Gourmet popcorn (per guest)', 2.00, 'per_item', NULL, NULL, 'guests', 'Freshly popped popcorn served per guest.', 1),
(140, 207, 'Deckchair seating (per chair)', 3.50, 'per_item', NULL, NULL, 'chairs', 'Striped deckchairs delivered and set out.', 1),
(141, 208, 'Trained attendant (per hour)', 18.00, 'per_item', NULL, NULL, 'hours', 'A trained attendant to supervise the inflatable.', 1),
(142, 208, 'Soft-play add-on', 75.00, 'flat', NULL, NULL, NULL, 'Toddler soft-play set delivered alongside the inflatable.', 1),
(143, 211, 'Prosecco reception (per guest)', 4.50, 'per_item', NULL, NULL, 'guests', 'A glass of prosecco served on arrival, per guest.', 1),
(144, 211, 'Whole baked Camembert wheel', 45.00, 'flat', NULL, NULL, NULL, 'A sharing centrepiece of warm baked Camembert.', 1),
(145, 212, 'White chocolate second fountain', 150.00, 'flat', NULL, NULL, NULL, 'An additional white-chocolate fountain alongside the dark.', 1),
(146, 213, 'Leather guest book album', 35.00, 'flat', NULL, NULL, NULL, 'A keepsake album of duplicate prints with guest messages.', 1),
(147, 213, 'Extra print copies (per print)', 0.50, 'per_item', NULL, NULL, 'prints', 'Additional duplicate prints during the session.', 1),
(148, 214, 'Sparkular cold-spark machines (pair)', 220.00, 'flat', NULL, NULL, NULL, 'Indoor-safe cold-spark fountains for the first dance.', 1),
(149, 214, 'Ceremony & drinks PA set-up', 120.00, 'flat', NULL, NULL, NULL, 'Additional discreet PA for the ceremony and reception.', 1),
(150, 215, 'Festoon lighting upgrade', 180.00, 'flat', NULL, NULL, NULL, 'Warm festoon lighting throughout the marquee.', 1),
(151, 215, 'Clear roof panels', 350.00, 'flat', NULL, NULL, NULL, 'Upgrade to clear roof sections for a starlit feel.', 1),
(152, 216, 'Illuminated initials (per letter)', 45.00, 'per_item', NULL, NULL, 'letters', 'Light-up initials placed at the edge of the floor.', 1),
(153, 217, 'Coloured sash upgrade (per chair)', 0.75, 'per_item', NULL, NULL, 'chairs', 'Premium satin or organza sash per chair.', 1),
(154, 218, 'Colour-changing bulbs upgrade', 60.00, 'flat', NULL, NULL, NULL, 'Switch from warm white to remote-controlled colour bulbs.', 1),
(155, 219, 'Gift box upgrade (per favour)', 0.60, 'per_item', NULL, NULL, 'favours', 'Kraft gift box with ribbon, per favour.', 1),
(156, 220, 'Custom hashtag sign', 18.00, 'flat', NULL, NULL, NULL, 'A printed sign featuring your event hashtag.', 1),
(157, 221, 'Ribbon & bow styling', 35.00, 'flat', NULL, NULL, NULL, 'Coordinated ribbons and bows in your colours.', 1),
(158, 222, 'On-the-day styling assistant', 180.00, 'flat', NULL, NULL, NULL, 'A stylist on hand throughout the event day.', 1),
(159, 223, 'Show caller / event director', 450.00, 'flat', NULL, NULL, NULL, 'A dedicated show caller to run the running order.', 1);

INSERT INTO `services_private_event_pricing` (`id`, `service_id`, `pricing_type`, `price`, `description`) VALUES
(43, 140, 'guest_based_pricing', 6.00, 'QA seeded guest_based_pricing'),
(44, 141, 'custom_duration_pricing', 250.00, 'QA seeded custom_duration_pricing'),
(45, 142, 'tiered_packages_pricing', 750.00, 'QA seeded tiered_packages_pricing'),
(46, 143, 'quantity_based_pricing', 4.00, 'QA seeded quantity_based_pricing'),
(101, 204, 'custom_duration_pricing', 450.00, 'Example seeded custom_duration_pricing'),
(102, 205, 'tiered_packages_pricing', 850.00, 'Example seeded tiered_packages_pricing'),
(103, 206, 'custom_duration_pricing', 300.00, 'Example seeded custom_duration_pricing'),
(104, 207, 'tiered_packages_pricing', 700.00, 'Example seeded tiered_packages_pricing'),
(105, 208, 'custom_duration_pricing', 120.00, 'Example seeded custom_duration_pricing'),
(106, 211, 'guest_based_pricing', 9.00, 'Example seeded guest_based_pricing'),
(107, 212, 'guest_based_pricing', 5.00, 'Example seeded guest_based_pricing'),
(108, 213, 'custom_duration_pricing', 295.00, 'Example seeded custom_duration_pricing'),
(109, 214, 'custom_duration_pricing', 350.00, 'Example seeded custom_duration_pricing'),
(110, 215, 'tiered_packages_pricing', 950.00, 'Example seeded tiered_packages_pricing'),
(111, 216, 'tiered_packages_pricing', 380.00, 'Example seeded tiered_packages_pricing'),
(112, 217, 'quantity_based_pricing', 2.75, 'Example seeded quantity_based_pricing'),
(113, 218, 'quantity_based_pricing', 45.00, 'Example seeded quantity_based_pricing'),
(114, 219, 'quantity_based_pricing', 1.80, 'Example seeded quantity_based_pricing'),
(115, 220, 'quantity_based_pricing', 15.00, 'Example seeded quantity_based_pricing'),
(116, 221, 'custom_quote', NULL, 'Example seeded custom_quote'),
(117, 222, 'custom_quote', NULL, 'Example seeded custom_quote'),
(118, 223, 'custom_quote', NULL, 'Example seeded custom_quote');

INSERT INTO `services_public_event_pricing` (`id`, `service_id`, `commission_percentage`, `min_attendance`, `max_attendance`, `max_pitch_fee`) VALUES
(66, 204, 12.00, 1, 250, 350.00),
(67, 204, 12.00, 251, 1000, 600.00),
(68, 204, 12.00, 1001, 5000, 1200.00),
(69, 205, 10.00, 1, 500, 900.00),
(70, 205, 10.00, 501, 3000, 1500.00),
(71, 206, 8.00, 1, 400, 400.00),
(72, 206, 8.00, 401, 2000, 750.00),
(73, 207, 15.00, 1, 300, 500.00),
(74, 207, 15.00, 301, 1500, 1100.00),
(75, 208, 10.00, 1, 500, 250.00),
(76, 208, 10.00, 501, 2000, 450.00),
(77, 209, 18.00, 1, 1000, 300.00),
(78, 209, 18.00, 1001, 5000, 650.00),
(79, 209, 18.00, 5001, 20000, 1400.00),
(80, 210, 20.00, 1, 800, 200.00),
(81, 210, 20.00, 801, 4000, 500.00);

INSERT INTO `services_quantity_pricing` (`id`, `service_id`, `private_event_pricing_id`, `unit_price`, `min_quantity`, `max_quantity`, `unit_label`) VALUES
(10, 143, 46, 4.00, 50, NULL, 'chairs'),
(23, 217, 112, 2.75, 30, NULL, 'chairs'),
(24, 218, 113, 45.00, 2, 12, 'characters'),
(25, 219, 114, 1.80, 25, NULL, 'favours'),
(26, 220, 115, 15.00, 1, 20, 'packs');

INSERT INTO `services_tiered_packages_pricing` (`id`, `service_id`, `private_event_pricing_id`, `package_name`, `package_description`, `package_price`) VALUES
(30, 142, 45, 'Bronze', 'Up to 60 guests, basic lighting.', 750.00),
(31, 142, 45, 'Silver', 'Up to 120 guests, lighting and flooring.', 1200.00),
(32, 142, 45, 'Gold', 'Up to 200 guests, full styling package.', 1800.00),
(69, 205, 102, 'Trio', 'Vocals, piano and double bass — up to 2 x 45 min sets.', 850.00),
(70, 205, 102, 'Quartet', 'Trio plus saxophone — up to 3 x 45 min sets.', 1250.00),
(71, 205, 102, 'Full Band', 'Nine-piece with brass section and DJ between sets.', 2200.00),
(72, 207, 104, 'Garden Screen (4m)', 'Up to 50 viewers, screen, projector and PA.', 700.00),
(73, 207, 104, 'Festival Screen (8m)', 'Up to 250 viewers, HD projection and line-array PA.', 1400.00),
(74, 207, 104, 'Stadium Screen (12m)', 'Up to 800 viewers, full production and crew.', 2800.00),
(75, 215, 110, 'Bronze', 'Up to 60 guests, hard flooring and basic lighting.', 950.00),
(76, 215, 110, 'Silver', 'Up to 120 guests, lighting, flooring and furniture.', 1650.00),
(77, 215, 110, 'Gold', 'Up to 200 guests, full styling, dancefloor and bar tent.', 2750.00),
(78, 216, 111, '12ft x 12ft', 'Up to 60 dancers, white starlit finish.', 380.00),
(79, 216, 111, '16ft x 16ft', 'Up to 120 dancers, starlit or sequence.', 520.00),
(80, 216, 111, '20ft x 20ft', 'Up to 200 dancers, full sequence light show.', 720.00);

INSERT INTO `users` (`id`, `name`, `username`, `email`, `password`, `role`, `password_reset_token`, `password_reset_expires_at`, `host_bio`, `host_tagline`, `host_quote`, `host_plays`, `host_photo_path`) VALUES
(1, 'Site Admin', 'admin', 'admin@example.test', '$2y$10$i7T5IbGkzDuPTrHvstBG5OeaFHRTbHdMDGloA8N049zWjZIoEc8Ze', 'admin', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 'Mark Pearson', 'm.pearson1', 'markyj@zoho.com', '$2y$10$i7T5IbGkzDuPTrHvstBG5OeaFHRTbHdMDGloA8N049zWjZIoEc8Ze', 'vendor', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, 'mark90', 'mark90', 'markjpearson@me.com', '$2y$10$i7T5IbGkzDuPTrHvstBG5OeaFHRTbHdMDGloA8N049zWjZIoEc8Ze', 'customer', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(6, 'QA Customer', 'qa_customer', 'qa.customer@example.test', '$2y$10$i7T5IbGkzDuPTrHvstBG5OeaFHRTbHdMDGloA8N049zWjZIoEc8Ze', 'customer', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(65, 'Customer One', 'customer1', 'customer1@c.com', '$2y$12$X5PRgFOrj0095psgAkJZWOaFt9B.BgRK6gr0lXwSFxpcTjxGjAOyG', 'customer', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(66, 'Customer Two', 'customer2', 'customer2@c.com', '$2y$12$X5PRgFOrj0095psgAkJZWOaFt9B.BgRK6gr0lXwSFxpcTjxGjAOyG', 'customer', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(67, 'Vendor One', 'vendor1', 'vendor1@v.com', '$2y$12$X5PRgFOrj0095psgAkJZWOaFt9B.BgRK6gr0lXwSFxpcTjxGjAOyG', 'vendor', NULL, NULL, 'Test test test testTest test test testTest test test testTest test test testTest test test testTest test test testTest test test testTest test test testTest test test testTest test test test', 'Test test test test', 'Test test test testTest test test testTest test test testTest test test test', '[\"Weddings\",\"birthdays\",\"corporate events\"]', 'uploads/vendor_photos/vendor_67_1780345514.jpg'),
(68, 'Vendor Two', 'vendor2', 'vendor2@v.com', '$2y$12$X5PRgFOrj0095psgAkJZWOaFt9B.BgRK6gr0lXwSFxpcTjxGjAOyG', 'vendor', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(87, 'Tyne & Wear Events Co', 'ne_tyne', 'ne.tyneandwear@example.com', '$2y$12$6/f/VBleABNuX7B39t/s/OtqRg6e3ycNqEylraF7TozFm4iwOSn72', 'vendor', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(88, 'Durham Celebrations', 'ne_durham', 'ne.durham@example.com', '$2y$12$6/f/VBleABNuX7B39t/s/OtqRg6e3ycNqEylraF7TozFm4iwOSn72', 'vendor', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(89, 'Teesside Party Hire', 'ne_tees', 'ne.teesside@example.com', '$2y$12$6/f/VBleABNuX7B39t/s/OtqRg6e3ycNqEylraF7TozFm4iwOSn72', 'vendor', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(90, 'Northumberland Marquees & Styling', 'ne_north', 'ne.northumberland@example.com', '$2y$12$6/f/VBleABNuX7B39t/s/OtqRg6e3ycNqEylraF7TozFm4iwOSn72', 'vendor', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(91, 'NE Customer One', 'ne_customer1', 'ne.customer1@example.com', '$2y$12$6/f/VBleABNuX7B39t/s/OtqRg6e3ycNqEylraF7TozFm4iwOSn72', 'customer', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(92, 'NE Customer Two', 'ne_customer2', 'ne.customer2@example.com', '$2y$12$6/f/VBleABNuX7B39t/s/OtqRg6e3ycNqEylraF7TozFm4iwOSn72', 'customer', NULL, NULL, NULL, NULL, NULL, NULL, NULL);



/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;