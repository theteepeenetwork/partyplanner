-- Service requirements, capacity & logistics (idempotent)
--
-- Adds operational fields to `services` so suppliers such as food trucks,
-- mobile bars, fairground rides, inflatables, marquees, AV/production and
-- security can declare what they need on site, how many guests they serve,
-- and how long they take to set up / break down.
--
-- Run after event_marketplace.sql (base tables must already exist).
-- Self-contained: defines its own copy of the column helper, so it does not
-- depend on database_update.sql leaving the procedure in place.
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

-- Capacity (overall headcount the service can serve, independent of pricing bands)
CALL `event_marketplace_add_column_if_missing`('services', 'min_capacity', '`min_capacity` INT NULL AFTER `price`');
CALL `event_marketplace_add_column_if_missing`('services', 'max_capacity', '`max_capacity` INT NULL AFTER `min_capacity`');

-- Logistics
CALL `event_marketplace_add_column_if_missing`('services', 'setup_minutes', '`setup_minutes` INT NULL AFTER `max_capacity`');
CALL `event_marketplace_add_column_if_missing`('services', 'breakdown_minutes', '`breakdown_minutes` INT NULL AFTER `setup_minutes`');
CALL `event_marketplace_add_column_if_missing`('services', 'min_notice_days', '`min_notice_days` INT NULL AFTER `breakdown_minutes`');

-- On-site requirements
CALL `event_marketplace_add_column_if_missing`('services', 'space_required', '`space_required` VARCHAR(120) NULL AFTER `min_notice_days`');
CALL `event_marketplace_add_column_if_missing`('services', 'indoor_outdoor', "`indoor_outdoor` ENUM('indoor','outdoor','both') NOT NULL DEFAULT 'both' AFTER `space_required`");
CALL `event_marketplace_add_column_if_missing`('services', 'power_required', '`power_required` TINYINT(1) NOT NULL DEFAULT 0 AFTER `indoor_outdoor`');
CALL `event_marketplace_add_column_if_missing`('services', 'water_required', '`water_required` TINYINT(1) NOT NULL DEFAULT 0 AFTER `power_required`');
CALL `event_marketplace_add_column_if_missing`('services', 'vehicle_access_required', '`vehicle_access_required` TINYINT(1) NOT NULL DEFAULT 0 AFTER `water_required`');
CALL `event_marketplace_add_column_if_missing`('services', 'equipment_provided', '`equipment_provided` TINYINT(1) NOT NULL DEFAULT 0 AFTER `vehicle_access_required`');

-- Clean up the helper procedure now that all columns are in place.
DROP PROCEDURE IF EXISTS `event_marketplace_add_column_if_missing`;
