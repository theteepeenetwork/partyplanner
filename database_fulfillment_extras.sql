-- Migration: fulfillment type + per-item extras pricing
-- Run once against the live database.

-- 1. Add fulfillment and postal delivery fields to services_locations
ALTER TABLE `services_locations`
  ADD COLUMN `fulfillment_type` ENUM('in_person','postal','both') NOT NULL DEFAULT 'in_person' AFTER `service_id`,
  ADD COLUMN `postal_fee` DECIMAL(8,2) NULL AFTER `travel_fee_per_km`,
  ADD COLUMN `free_postage_above` DECIMAL(8,2) NULL AFTER `postal_fee`,
  ADD COLUMN `delivery_lead_time_days` INT UNSIGNED NULL AFTER `free_postage_above`;

-- 2. Add per-item pricing fields to services_optional_extras
ALTER TABLE `services_optional_extras`
  ADD COLUMN `pricing_type` ENUM('flat','per_item') NOT NULL DEFAULT 'flat' AFTER `price`,
  ADD COLUMN `min_quantity` INT UNSIGNED NULL AFTER `pricing_type`,
  ADD COLUMN `max_quantity` INT UNSIGNED NULL AFTER `min_quantity`,
  ADD COLUMN `unit_label` VARCHAR(50) NULL AFTER `max_quantity`;
