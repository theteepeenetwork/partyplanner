-- Migration: fulfillment type + per-item extras pricing (idempotent)
--
-- Run after event_marketplace.sql (base tables must already exist).
-- Self-contained: defines its own copy of the column helper so it no longer
-- depends on database_update.sql leaving the procedure in place.
-- Safe to run multiple times: existing columns are skipped.

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

-- 1. Fulfillment and postal delivery on services_locations
CALL `event_marketplace_add_column_if_missing`('services_locations', 'fulfillment_type', '`fulfillment_type` ENUM(''in_person'',''postal'',''both'') NOT NULL DEFAULT ''in_person'' AFTER `service_id`');
CALL `event_marketplace_add_column_if_missing`('services_locations', 'postal_fee', '`postal_fee` DECIMAL(8,2) NULL AFTER `travel_fee_per_km`');
CALL `event_marketplace_add_column_if_missing`('services_locations', 'free_postage_above', '`free_postage_above` DECIMAL(8,2) NULL AFTER `postal_fee`');
CALL `event_marketplace_add_column_if_missing`('services_locations', 'delivery_lead_time_days', '`delivery_lead_time_days` INT UNSIGNED NULL AFTER `free_postage_above`');

-- 2. Per-item optional extra pricing on services_optional_extras
CALL `event_marketplace_add_column_if_missing`('services_optional_extras', 'pricing_type', '`pricing_type` ENUM(''flat'',''per_item'') NOT NULL DEFAULT ''flat'' AFTER `price`');
CALL `event_marketplace_add_column_if_missing`('services_optional_extras', 'min_quantity', '`min_quantity` INT UNSIGNED NULL AFTER `pricing_type`');
CALL `event_marketplace_add_column_if_missing`('services_optional_extras', 'max_quantity', '`max_quantity` INT UNSIGNED NULL AFTER `min_quantity`');
CALL `event_marketplace_add_column_if_missing`('services_optional_extras', 'unit_label', '`unit_label` VARCHAR(50) NULL AFTER `max_quantity`');

-- Clean up the helper procedure now that all columns are in place.
DROP PROCEDURE IF EXISTS `event_marketplace_add_column_if_missing`;
