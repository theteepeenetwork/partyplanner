-- Event Marketplace Database Schema
-- Updated: 2026-05-10
-- Compatible with: MariaDB 10.x / MySQL 5.7+
-- PHP Version: 8.1+

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------
-- Database: `event_marketplace`
-- --------------------------------------------------------

-- --------------------------------------------------------
-- Table: users
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('customer','vendor','admin') NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `users` (`id`, `name`, `username`, `email`, `password`, `role`) VALUES
(3, 'Mark Pearson', 'm.pearson1', 'markyj@zoho.com', '$2y$10$OKp.uCxz/4jW3FbMxjpiEesYTJkx4pHBoSlGsZQ3CEstqgHpJU/DK', 'vendor'),
(4, 'mark90', 'mark90', 'markjpearson@me.com', '$2y$10$FeGW7V5CBkb9suZ2jQdqEevA/2y0iakVRfkVDY3BGEQ42GkzXvy0q', 'customer')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

INSERT INTO `users` (`id`, `name`, `username`, `email`, `password`, `role`) VALUES
(1, 'Site Admin', 'admin', 'admin@example.test', '$2y$10$i7T5IbGkzDuPTrHvstBG5OeaFHRTbHdMDGloA8N049zWjZIoEc8Ze', 'admin'),
(6, 'QA Customer', 'qa_customer', 'qa.customer@example.test', '$2y$10$i7T5IbGkzDuPTrHvstBG5OeaFHRTbHdMDGloA8N049zWjZIoEc8Ze', 'customer')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `username`=VALUES(`username`), `email`=VALUES(`email`), `password`=VALUES(`password`), `role`=VALUES(`role`);

-- --------------------------------------------------------
-- Table: categories
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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

-- --------------------------------------------------------
-- Table: subcategories
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `subcategories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: services
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vendor_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `short_description` varchar(500) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `category_id` int(11) DEFAULT NULL,
  `subcategory_id` int(11) DEFAULT NULL,
  `third_category_id` int(11) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `free_coverage_radius` int(11) DEFAULT NULL,
  `paid_coverage_radius` int(11) DEFAULT NULL,
  `travel_fee_per_km` decimal(10,2) DEFAULT NULL,
  `cancellation_policy` text DEFAULT NULL,
  `service_tags` text DEFAULT NULL,
  `service_location` varchar(255) DEFAULT NULL,
  `all_travel_included` tinyint(1) DEFAULT 0,
  `no_travel_limit` tinyint(1) DEFAULT 0,
  `event_types` text DEFAULT NULL,
  `commission_percentage` decimal(5,2) DEFAULT NULL,
  `license` varchar(255) DEFAULT NULL,
  `attendance_thresholds` text DEFAULT NULL,
  `max_pitch_fees` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'active',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `vendor_id` (`vendor_id`),
  CONSTRAINT `services_ibfk_1` FOREIGN KEY (`vendor_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `services` (`id`, `vendor_id`, `title`, `description`, `image`, `price`, `status`, `cancellation_policy`) VALUES
(2, 3, 'Sweetie Sweet Cart', 'Indulge your sweet tooth at the Sweetie Cart Candy Stall, a whimsical haven for candy lovers of all ages!', '1716936655_2a9474e339e1b2141db3.jpg', 150.00, 'active', 'Cancel up to 14 days before for a full refund of your deposit.'),
(3, 3, 'Sweetie Sweet Cart', 'Indulge your sweet tooth at the Sweetie Cart Candy Stall, a whimsical haven for candy lovers of all ages!', '1716936809_ea5338b2e4ba5823d2f9.jpg', 150.00, 'active', 'Cancel up to 14 days before for a full refund of your deposit.'),
(4, 3, 'Mr Beatys Burgers', 'BurgerBurgerBurgerBurgerBurgerBurgerBurger', '1716937372_8e6a7964ed534149d3cb.jpeg', 240.00, 'active', '48 hours notice required for deposit refund.'),
(5, 3, 'Dinky Donuts', 'Delight in the irresistible aroma and melt-in-your-mouth goodness of Dinky Donuts!', '1716937744_df87fb10763e1b292fb8.jpeg', 90.00, 'active', 'Cancel up to 7 days before for a full refund.'),
(90, 3, '(Inactive QA) Vintage photobooth', 'Seeded inactive listing for vendor dashboard QA (services tab + filters).', NULL, 320.00, 'inactive', 'Full refund up to 30 days before the event.')
ON DUPLICATE KEY UPDATE `title`=VALUES(`title`), `status`=VALUES(`status`), `cancellation_policy`=VALUES(`cancellation_policy`);

-- --------------------------------------------------------
-- Table: service_images
-- --------------------------------------------------------

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

INSERT INTO `service_images` (`id`, `service_id`, `image_path`, `thumbnail_path`, `is_primary`) VALUES
(2, 2, 'uploads/services/1716936655_2a9474e339e1b2141db3.jpg', 'uploads/services/thumb_1716936655_2a9474e339e1b2141db3.jpg', 1),
(3, 3, 'uploads/services/1716936809_ea5338b2e4ba5823d2f9.jpg', 'uploads/services/thumb_1716936809_ea5338b2e4ba5823d2f9.jpg', 1),
(4, 4, 'uploads/services/1716937372_8e6a7964ed534149d3cb.jpeg', 'uploads/services/thumb_1716937372_8e6a7964ed534149d3cb.jpeg', 1),
(5, 5, 'uploads/services/1716937744_df87fb10763e1b292fb8.jpeg', 'uploads/services/thumb_1716937744_df87fb10763e1b292fb8.jpeg', 1)
ON DUPLICATE KEY UPDATE `image_path`=VALUES(`image_path`), `thumbnail_path`=VALUES(`thumbnail_path`), `is_primary`=VALUES(`is_primary`);

-- --------------------------------------------------------
-- Table: events
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `vendor_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `date` date DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `venue_name` varchar(255) DEFAULT NULL,
  `postcode` varchar(20) DEFAULT NULL,
  `town_city` varchar(255) DEFAULT NULL,
  `indoor_outdoor` varchar(20) DEFAULT NULL,
  `budget_min` decimal(10,2) DEFAULT NULL,
  `budget_max` decimal(10,2) DEFAULT NULL,
  `style_theme` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'active',
  `category` varchar(255) DEFAULT NULL,
  `event_type` varchar(100) DEFAULT NULL,
  `guest_count` int(11) DEFAULT NULL,
  `event_setting` varchar(20) NOT NULL DEFAULT 'private',
  `organiser_pitch_fee` decimal(10,2) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `vendor_id` (`vendor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: bookings
-- --------------------------------------------------------

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

-- --------------------------------------------------------
-- Table: booking_items
-- --------------------------------------------------------

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

-- --------------------------------------------------------
-- Table: payments
-- --------------------------------------------------------

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

-- --------------------------------------------------------
-- Table: event_basket_items
-- --------------------------------------------------------

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
  `quote_breakdown` text DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `event_id` (`event_id`),
  KEY `user_id` (`user_id`),
  KEY `service_id` (`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: carts (legacy)
-- --------------------------------------------------------

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

-- --------------------------------------------------------
-- Table: chat_rooms
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `chat_rooms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vendor_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `service_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: chat_messages
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `chat_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `chat_room_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `original_message` text DEFAULT NULL,
  `moderation_status` varchar(20) NOT NULL DEFAULT 'clean',
  `admin_note` text DEFAULT NULL,
  `profanity_matches` varchar(500) DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: favourites
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `favourites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_service` (`user_id`, `service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: unavailable_dates
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `unavailable_dates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_id` int(11) DEFAULT NULL,
  `vendor_id` int(11) DEFAULT NULL,
  `date` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: service_availability
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `service_availability` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_id` int(11) NOT NULL,
  `day_of_week` varchar(20) DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: service_time_blocks
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `service_time_blocks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_id` int(11) NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: service_public_event_data
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `service_public_event_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_id` int(11) NOT NULL,
  `data` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: services_locations
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `services_locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_id` int(11) NOT NULL,
  `service_location` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `all_travel_included` tinyint(1) DEFAULT 0,
  `no_travel_limit` tinyint(1) DEFAULT 0,
  `free_coverage_radius` int(11) DEFAULT NULL,
  `paid_coverage_radius` int(11) DEFAULT NULL,
  `travel_fee_per_km` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: services_tags
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `services_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: services_service_tags
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `services_service_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: services_event_types
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `services_event_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_id` int(11) NOT NULL,
  `event_type` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: services_guest_based_pricing
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `services_guest_based_pricing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_id` int(11) NOT NULL,
  `private_event_pricing_id` int(11) DEFAULT NULL,
  `min_guest` int(11) DEFAULT NULL,
  `max_guest` int(11) DEFAULT NULL,
  `guest_price` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: services_custom_duration_pricing
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `services_custom_duration_pricing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_id` int(11) NOT NULL,
  `private_event_pricing_id` int(11) DEFAULT NULL,
  `duration_type` varchar(20) DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: services_tiered_packages_pricing
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `services_tiered_packages_pricing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_id` int(11) NOT NULL,
  `private_event_pricing_id` int(11) DEFAULT NULL,
  `package_name` varchar(255) DEFAULT NULL,
  `package_description` text DEFAULT NULL,
  `package_price` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: services_private_event_pricing
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `services_private_event_pricing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_id` int(11) NOT NULL,
  `pricing_type` varchar(50) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: services_cancellation_policies
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `services_cancellation_policies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_id` int(11) NOT NULL,
  `policy` text DEFAULT NULL,
  `cancellation_policy` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: services_public_event_pricing
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `services_public_event_pricing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_id` int(11) NOT NULL,
  `commission_percentage` decimal(5,2) DEFAULT NULL,
  `min_attendance` int(11) DEFAULT NULL,
  `max_attendance` int(11) DEFAULT NULL,
  `max_pitch_fee` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: services_corporate_event_pricing
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `services_corporate_event_pricing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_id` int(11) NOT NULL,
  `pricing_details` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: services_optional_extras
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `services_optional_extras` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- QA / demo data (events, bookings, messaging, favourites)
-- Idempotent via ON DUPLICATE KEY UPDATE on primary keys.
-- Test accounts: username admin or qa_customer — password: TestPass123!
-- --------------------------------------------------------

INSERT INTO `events` (`id`, `user_id`, `title`, `description`, `date`, `location`, `event_type`, `guest_count`, `status`) VALUES
(501, 6, 'QA Sample Wedding', 'Seeded private event for dashboard and booking QA.', '2026-09-15', 'Manchester Town Hall', 'Wedding', 80, 'active')
ON DUPLICATE KEY UPDATE `title`=VALUES(`title`), `description`=VALUES(`description`), `date`=VALUES(`date`), `location`=VALUES(`location`);

INSERT INTO `bookings` (`id`, `user_id`, `event_id`, `status`) VALUES
(501, 6, 501, 'pending')
ON DUPLICATE KEY UPDATE `user_id`=VALUES(`user_id`), `event_id`=VALUES(`event_id`), `status`=VALUES(`status`);

INSERT INTO `booking_items` (`id`, `booking_id`, `service_id`, `quantity`, `price`, `status`) VALUES
(501, 501, 2, 1, 150.00, 'pending'),
(502, 501, 5, 1, 90.00, 'accepted')
ON DUPLICATE KEY UPDATE `price`=VALUES(`price`), `status`=VALUES(`status`);

INSERT INTO `payments` (`id`, `booking_id`, `payment_status`, `amount_paid`, `description`, `payment_type`) VALUES
(501, 501, 'succeeded', 75.00, 'QA seed deposit', 'deposit')
ON DUPLICATE KEY UPDATE `payment_status`=VALUES(`payment_status`), `amount_paid`=VALUES(`amount_paid`);

INSERT INTO `favourites` (`user_id`, `service_id`) VALUES (6, 4)
ON DUPLICATE KEY UPDATE `user_id`=VALUES(`user_id`);

INSERT INTO `chat_rooms` (`id`, `vendor_id`, `customer_id`, `service_id`) VALUES
(501, 3, 6, 2)
ON DUPLICATE KEY UPDATE `vendor_id`=VALUES(`vendor_id`), `customer_id`=VALUES(`customer_id`), `service_id`=VALUES(`service_id`);

INSERT INTO `chat_messages` (`id`, `chat_room_id`, `sender_id`, `receiver_id`, `message`, `is_read`, `moderation_status`) VALUES
(501, 501, 6, 3, 'Hi — confirming our sweet cart for the wedding. Thanks!', 0, 'clean'),
(502, 501, 3, 6, 'Thanks, we will confirm closer to the date.', 1, 'clean')
ON DUPLICATE KEY UPDATE `message`=VALUES(`message`), `is_read`=VALUES(`is_read`);

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
