-- Migration: quantity-based primary private event pricing (idempotent)
-- Run after database_update.sql on existing databases.
-- Safe to run multiple times.

CREATE TABLE IF NOT EXISTS `services_quantity_pricing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_id` int(11) NOT NULL,
  `private_event_pricing_id` int(11) DEFAULT NULL,
  `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `min_quantity` int(11) NOT NULL DEFAULT 1,
  `max_quantity` int(11) DEFAULT NULL,
  `unit_label` varchar(50) NOT NULL DEFAULT 'items',
  PRIMARY KEY (`id`),
  KEY `service_id` (`service_id`),
  KEY `private_event_pricing_id` (`private_event_pricing_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
