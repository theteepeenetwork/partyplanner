-- Vendor quote automation schema (idempotent)
-- Run after event_marketplace.sql (base tables must already exist).
-- Self-contained: defines its own copy of the column helper so it no longer
-- depends on database_update.sql leaving the procedure in place.

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

CALL `event_marketplace_add_column_if_missing`('services_locations', 'strict_travel_radius', '`strict_travel_radius` TINYINT(1) NOT NULL DEFAULT 0 AFTER `travel_fee_per_km`');
CALL `event_marketplace_add_column_if_missing`('booking_items', 'quote_breakdown', '`quote_breakdown` JSON NULL AFTER `price`');
CALL `event_marketplace_add_column_if_missing`('booking_items', 'quote_warnings', '`quote_warnings` JSON NULL AFTER `quote_breakdown`');
CALL `event_marketplace_add_column_if_missing`('booking_items', 'extras_snapshot', '`extras_snapshot` JSON NULL AFTER `quote_warnings`');
CALL `event_marketplace_add_column_if_missing`('bookings', 'payment_intent_id', '`payment_intent_id` VARCHAR(255) NULL AFTER `status`');
CALL `event_marketplace_add_column_if_missing`('bookings', 'balance_due', '`balance_due` DECIMAL(10,2) NULL AFTER `payment_intent_id`');
CALL `event_marketplace_add_column_if_missing`('bookings', 'payment_plan', '`payment_plan` VARCHAR(32) NULL DEFAULT ''single'' AFTER `balance_due`');

CREATE TABLE IF NOT EXISTS `vendor_quote_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vendor_id` int(11) NOT NULL,
  `service_id` int(11) DEFAULT NULL,
  `auto_accept_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `max_auto_accept_amount` decimal(10,2) DEFAULT NULL,
  `require_within_travel_radius` tinyint(1) NOT NULL DEFAULT 1,
  `min_lead_days` int(11) NOT NULL DEFAULT 0,
  `allowed_event_settings` json DEFAULT NULL,
  `blackout_respect` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `vendor_id` (`vendor_id`),
  KEY `service_id` (`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `quote_automation_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_item_id` int(11) NOT NULL,
  `action` varchar(64) NOT NULL,
  `details` json DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `booking_item_id` (`booking_item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `vendor_quotes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_item_id` int(11) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `status` enum('draft','sent','accepted','declined','expired') NOT NULL DEFAULT 'draft',
  `lines` json NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `vendor_notes` text DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `booking_item_id` (`booking_item_id`),
  KEY `vendor_id` (`vendor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `vendor_message_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vendor_id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `body` text NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `vendor_id` (`vendor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `payment_schedules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) NOT NULL,
  `due_date` date NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` varchar(32) NOT NULL DEFAULT 'pending',
  `stripe_payment_intent_id` varchar(255) DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `booking_id` (`booking_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `quote_analytics_daily` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vendor_id` int(11) NOT NULL,
  `service_id` int(11) DEFAULT NULL,
  `metric_date` date NOT NULL,
  `quotes_generated` int(11) NOT NULL DEFAULT 0,
  `quotes_accepted` int(11) NOT NULL DEFAULT 0,
  `auto_accepted` int(11) NOT NULL DEFAULT 0,
  `avg_total` decimal(10,2) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `vendor_service_date` (`vendor_id`,`service_id`,`metric_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Clean up the helper procedure now that all columns are in place.
DROP PROCEDURE IF EXISTS `event_marketplace_add_column_if_missing`;
