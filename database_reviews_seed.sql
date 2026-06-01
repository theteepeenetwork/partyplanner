-- ============================================================
-- Event Marketplace — Reviews demo seed (events + bookings + reviews)
-- ============================================================
-- Creates PAST events, confirmed bookings + booking lines, and reviews so
-- the review feature has data to display straight away.
--
-- Everything is resolved dynamically against your existing data — it picks
-- real customers and a spread of active services, so no ids are hardcoded.
--
-- Prerequisites:
--   * event_marketplace.sql + database_update.sql imported
--   * at least one customer and some active services already exist
--
-- Import:
--   mysql --default-character-set=utf8mb4 event_marketplace < database_reviews_seed.sql
--
-- Idempotent: re-running deletes its own previously-seeded rows first
-- (events/bookings/booking_items are tagged by title; reviews cascade off
-- them), then recreates them — so it never piles up duplicates.
-- ============================================================

-- Safety net: create the reviews table if database_update.sql wasn't run.
CREATE TABLE IF NOT EXISTS `reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_item_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `rating` tinyint(1) NOT NULL,
  `title` varchar(150) NOT NULL,
  `comment` text NOT NULL,
  `flagged` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_booking_item` (`booking_item_id`),
  KEY `idx_vendor` (`vendor_id`),
  KEY `idx_service` (`service_id`),
  KEY `idx_customer` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----- 1. Remove any previous seed rows (idempotent) -------------------
DELETE r FROM `reviews` r
  JOIN `booking_items` bi ON bi.id = r.booking_item_id
  JOIN `bookings` b       ON b.id = bi.booking_id
  JOIN `events` e         ON e.id = b.event_id
 WHERE e.title LIKE 'Seed past event svc %';

DELETE bi FROM `booking_items` bi
  JOIN `bookings` b ON b.id = bi.booking_id
  JOIN `events` e   ON e.id = b.event_id
 WHERE e.title LIKE 'Seed past event svc %';

DELETE b FROM `bookings` b
  JOIN `events` e ON e.id = b.event_id
 WHERE e.title LIKE 'Seed past event svc %';

DELETE FROM `events` WHERE title LIKE 'Seed past event svc %';

-- ----- 2. Pick two customers + a spread of up to 20 active services -----
SET @c_a := (SELECT id FROM `users` WHERE role = 'customer' ORDER BY id LIMIT 1);
SET @c_b := COALESCE((SELECT id FROM `users` WHERE role = 'customer' ORDER BY id LIMIT 1 OFFSET 1), @c_a);

DROP TEMPORARY TABLE IF EXISTS `_seed_svc`;
CREATE TEMPORARY TABLE `_seed_svc` AS
SELECT service_id, vendor_id, ROW_NUMBER() OVER (ORDER BY service_id) AS rn
FROM (
    SELECT s.id AS service_id, s.vendor_id
    FROM `services` s
    WHERE s.status = 'active' AND s.deleted_at IS NULL
    ORDER BY s.id
    LIMIT 20
) t;

-- ----- 3. Past events (alternating between the two customers) -----------
INSERT INTO `events`
    (`user_id`, `title`, `description`, `date`, `status`, `event_type`,
     `guest_count`, `event_setting`, `location`, `town_city`, `postcode`, `latitude`, `longitude`)
SELECT
    IF(rn % 2 = 0, @c_b, @c_a),
    CONCAT('Seed past event svc ', service_id),
    'Seeded past event used to attach a demo review.',
    (CURDATE() - INTERVAL (10 + rn) DAY),
    'active', 'Wedding', 80, 'private',
    'London, SW1A 1AA', 'London', 'SW1A 1AA', 51.50330000, -0.11960000
FROM `_seed_svc`;

-- ----- 4. One confirmed booking per seeded event -----------------------
INSERT INTO `bookings` (`user_id`, `event_id`, `status`)
SELECT e.user_id, e.id, 'confirmed'
FROM `events` e
WHERE e.title LIKE 'Seed past event svc %';

-- ----- 5. One confirmed booking line per booking (the reviewed service) -
INSERT INTO `booking_items` (`booking_id`, `service_id`, `quantity`, `price`, `status`)
SELECT b.id, CAST(SUBSTRING_INDEX(e.title, ' ', -1) AS UNSIGNED), 1, 250.00, 'confirmed'
FROM `bookings` b
JOIN `events` e ON e.id = b.event_id
WHERE e.title LIKE 'Seed past event svc %';

-- ----- 6. A review per booking line (vendor derived from the service) ---
INSERT INTO `reviews`
    (`booking_item_id`, `customer_id`, `vendor_id`, `service_id`, `rating`, `title`, `comment`, `flagged`)
SELECT
    bi.id, b.user_id, s.vendor_id, s.id,
    3 + (bi.id % 3),
    ELT(1 + (bi.id % 5),
        'Absolutely brilliant', 'Highly recommend', 'Made our day',
        'Would book again', 'Great value for money'),
    ELT(1 + (bi.id % 5),
        'Everything was perfect from start to finish. Our guests loved it.',
        'Professional, friendly and turned up right on time.',
        'Exactly what we hoped for and lovely to deal with throughout.',
        'Really pleased with how it went — would happily use again.',
        'Solid, reliable service and great value for the price.'),
    0
FROM `booking_items` bi
JOIN `bookings` b ON b.id = bi.booking_id
JOIN `events`   e ON e.id = b.event_id
JOIN `services` s ON s.id = bi.service_id
WHERE e.title LIKE 'Seed past event svc %'
ON DUPLICATE KEY UPDATE
    `rating`  = VALUES(`rating`),
    `title`   = VALUES(`title`),
    `comment` = VALUES(`comment`);

DROP TEMPORARY TABLE IF EXISTS `_seed_svc`;

SELECT CONCAT('Seeded reviews. Total rows now: ', COUNT(*),
              ' across ', COUNT(DISTINCT vendor_id), ' vendors.') AS status
FROM `reviews`;
