-- ============================================================
-- Event Marketplace — Reviews seed data
-- ============================================================
-- Sample rows for the `reviews` table (star rating + title + comment).
--
-- Prerequisites (run these first, in order):
--   1. event_marketplace.sql        — base schema
--   2. database_update.sql          — creates the `reviews` table
--   3. some bookings on PAST events — e.g. `php spark db:seed QASeeder`
--
-- Import:
--   mysql --default-character-set=utf8mb4 event_marketplace < database_reviews_seed.sql
--
-- Notes:
--   * Reviews must reference a real booking line, so this script DERIVES the
--     foreign keys (customer/vendor/service) from existing booking_items rather
--     than hardcoding auto-increment ids.
--   * Only eligible bookings are reviewed: accepted/confirmed lines whose event
--     date has already passed (the same rule the app enforces).
--   * Idempotent: `reviews.booking_item_id` is UNIQUE, so re-running this file
--     updates the existing rows via ON DUPLICATE KEY UPDATE instead of
--     creating duplicates.
-- ============================================================

INSERT INTO `reviews`
    (`booking_item_id`, `customer_id`, `vendor_id`, `service_id`, `rating`, `title`, `comment`, `flagged`)
SELECT
    bi.id,
    b.user_id,
    s.vendor_id,
    s.id,
    -- Spread ratings 3–5 deterministically so averages look realistic.
    CASE (bi.id % 5)
        WHEN 0 THEN 5
        WHEN 1 THEN 4
        WHEN 2 THEN 5
        WHEN 3 THEN 4
        ELSE 3
    END,
    CASE (bi.id % 5)
        WHEN 0 THEN 'Absolutely brilliant'
        WHEN 1 THEN 'Great from start to finish'
        WHEN 2 THEN 'Made our event'
        WHEN 3 THEN 'Would book again'
        ELSE 'Good value for money'
    END,
    CASE (bi.id % 5)
        WHEN 0 THEN 'Everything was perfect. Our guests are still talking about it weeks later.'
        WHEN 1 THEN 'Professional, friendly and reliable. Setup was quick and the quality was excellent.'
        WHEN 2 THEN 'Exactly what we hoped for. Communication beforehand was clear and helpful.'
        WHEN 3 THEN 'Really pleased with how it went. A couple of small hiccups but handled well.'
        ELSE 'Solid service for the price. Turned up on time and did the job.'
    END,
    0
FROM `booking_items` bi
JOIN `bookings` b ON b.id = bi.booking_id
JOIN `events`   e ON e.id = b.event_id
JOIN `services` s ON s.id = bi.service_id
WHERE bi.status IN ('accepted', 'confirmed')
  AND e.`date` < CURDATE()
ON DUPLICATE KEY UPDATE
    `rating`  = VALUES(`rating`),
    `title`   = VALUES(`title`),
    `comment` = VALUES(`comment`),
    `flagged` = VALUES(`flagged`);

SELECT CONCAT('Reviews seeded. Total rows: ', COUNT(*)) AS status FROM `reviews`;
