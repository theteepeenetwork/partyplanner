-- Migration: fulfillment type + per-item extras pricing (idempotent)
--
-- Uses `event_marketplace_add_column_if_missing` from database_update.sql.
-- Run database_update.sql first on a fresh database so that procedure exists.
-- Safe to run multiple times: existing columns are skipped.

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
