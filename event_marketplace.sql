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
  `password_reset_token` varchar(128) DEFAULT NULL,
  `password_reset_expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `users` (`id`, `name`, `username`, `email`, `password`, `role`) VALUES
(3, 'Mark Pearson', 'm.pearson1', 'markyj@zoho.com', '$2y$10$i7T5IbGkzDuPTrHvstBG5OeaFHRTbHdMDGloA8N049zWjZIoEc8Ze', 'vendor'),
(4, 'mark90', 'mark90', 'markjpearson@me.com', '$2y$10$i7T5IbGkzDuPTrHvstBG5OeaFHRTbHdMDGloA8N049zWjZIoEc8Ze', 'customer')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `password`=VALUES(`password`), `role`=VALUES(`role`);

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
  PRIMARY KEY (`id`),
  KEY `idx_categories_parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `categories` (`id`, `parent_id`, `name`) VALUES
(1, NULL, 'Catering & Food'),
(2, NULL, 'Bars & Drinks'),
(3, NULL, 'Cakes & Desserts'),
(4, NULL, 'Photography & Videography'),
(5, NULL, 'Music & DJs'),
(6, NULL, 'Entertainment & Performers'),
(7, NULL, 'Children’s Parties'),
(8, NULL, 'Venues & Accommodation'),
(9, NULL, 'Event Planning & Coordination'),
(10, NULL, 'Decorations & Venue Styling'),
(11, NULL, 'Flowers & Floristry'),
(12, NULL, 'Furniture & Equipment Hire'),
(13, NULL, 'Lighting, Sound & Production'),
(14, NULL, 'Transport'),
(15, NULL, 'Beauty, Hair & Makeup'),
(16, NULL, 'Stationery, Signage & Printing'),
(17, NULL, 'Photo Booths & Interactive Experiences'),
(18, NULL, 'Marquees & Outdoor Events'),
(19, NULL, 'Staffing, Security & Event Support'),
(20, NULL, 'Gifts, Favours & Personalised Items'),
(21, NULL, 'Wellbeing, Experiences & Activities'),
(22, NULL, 'Seasonal & Themed Events'),
(23, NULL, 'Business, Corporate & Brand Events'),
(24, NULL, 'Pets & Animal Experiences'),
(25, NULL, 'Other Services'),
(100, 1, 'Wedding catering'),
(101, 1, 'Corporate catering'),
(102, 1, 'Buffet catering'),
(103, 1, 'Plated meals'),
(104, 1, 'Street food vendors'),
(105, 1, 'Food trucks'),
(106, 1, 'BBQ catering'),
(107, 1, 'Hog roast'),
(108, 1, 'Afternoon tea'),
(109, 1, 'Grazing tables'),
(110, 1, 'Canapés'),
(111, 1, 'Private chefs'),
(112, 1, 'Festival catering'),
(113, 1, 'Mobile pizza'),
(114, 1, 'Fish and chips'),
(115, 1, 'Asian cuisine'),
(116, 1, 'Caribbean cuisine'),
(117, 1, 'Mediterranean cuisine'),
(118, 1, 'Vegan catering'),
(119, 1, 'Halal catering'),
(120, 1, 'Gluten-free catering'),
(200, 2, 'Mobile bars'),
(201, 2, 'Cocktail bars'),
(202, 2, 'Gin bars'),
(203, 2, 'Prosecco vans'),
(204, 2, 'Beer and ale bars'),
(205, 2, 'Wine bars'),
(206, 2, 'Coffee carts'),
(207, 2, 'Tea and hot drinks'),
(208, 2, 'Smoothie and juice bars'),
(209, 2, 'Mocktail bars'),
(210, 2, 'Bar staff'),
(211, 2, 'Drinks packages'),
(212, 2, 'Champagne towers'),
(213, 2, 'Water stations'),
(300, 3, 'Wedding cakes'),
(301, 3, 'Birthday cakes'),
(302, 3, 'Cupcakes'),
(303, 3, 'Dessert tables'),
(304, 3, 'Doughnut walls'),
(305, 3, 'Sweet carts'),
(306, 3, 'Chocolate fountains'),
(307, 3, 'Ice cream vans'),
(308, 3, 'Waffle and crepe stations'),
(309, 3, 'Pick and mix'),
(310, 3, 'Brownies and traybakes'),
(311, 3, 'Macarons'),
(312, 3, 'Cake pops'),
(313, 3, 'Cheesecake towers'),
(400, 4, 'Wedding photography'),
(401, 4, 'Event photography'),
(402, 4, 'Corporate photography'),
(403, 4, 'Brand photography'),
(404, 4, 'Party photography'),
(405, 4, 'Videography'),
(406, 4, 'Wedding videography'),
(407, 4, 'Drone photography'),
(408, 4, 'Drone videography'),
(409, 4, 'Social media content creators'),
(410, 4, 'Live streaming'),
(411, 4, 'Second shooters'),
(412, 4, 'Engagement shoots'),
(413, 4, 'Photo editing and retouching'),
(500, 5, 'Wedding DJs'),
(501, 5, 'Party DJs'),
(502, 5, 'Corporate DJs'),
(503, 5, 'Live bands'),
(504, 5, 'Solo singers'),
(505, 5, 'Acoustic performers'),
(506, 5, 'String quartets'),
(507, 5, 'Saxophonists'),
(508, 5, 'Pianists'),
(509, 5, 'Harpists'),
(510, 5, 'Ceilidh bands'),
(511, 5, 'Tribute acts'),
(512, 5, 'Choirs'),
(513, 5, 'Karaoke'),
(514, 5, 'Silent disco'),
(515, 5, 'Brass bands'),
(600, 6, 'Magicians'),
(601, 6, 'Comedians'),
(602, 6, 'Caricaturists'),
(603, 6, 'Circus performers'),
(604, 6, 'Fire performers'),
(605, 6, 'Dancers'),
(606, 6, 'Singing waiters'),
(607, 6, 'Actors and characters'),
(608, 6, 'Lookalikes'),
(609, 6, 'Drag performers'),
(610, 6, 'Burlesque performers'),
(611, 6, 'Close-up entertainment'),
(612, 6, 'Casino tables'),
(613, 6, 'Murder mystery'),
(614, 6, 'Hypnotists'),
(615, 6, 'Mind readers'),
(616, 6, 'Live event artists'),
(700, 7, 'Bouncy castles'),
(701, 7, 'Soft play'),
(702, 7, 'Mascots'),
(703, 7, 'Face painting'),
(704, 7, 'Glitter tattoos'),
(705, 7, 'Balloon modellers'),
(706, 7, 'Children’s discos'),
(707, 7, 'Party entertainers'),
(708, 7, 'Science parties'),
(709, 7, 'Craft parties'),
(710, 7, 'Princess parties'),
(711, 7, 'Superhero parties'),
(712, 7, 'Gaming parties'),
(713, 7, 'Petting zoos'),
(714, 7, 'Pony parties'),
(715, 7, 'Inflatable obstacle courses'),
(716, 7, 'Mini fairground rides'),
(800, 8, 'Wedding venues'),
(801, 8, 'Party venues'),
(802, 8, 'Corporate venues'),
(803, 8, 'Conference venues'),
(804, 8, 'Outdoor venues'),
(805, 8, 'Barn venues'),
(806, 8, 'Hotel venues'),
(807, 8, 'Community halls'),
(808, 8, 'Sports clubs'),
(809, 8, 'Restaurants and private dining'),
(810, 8, 'Unique venues'),
(811, 8, 'Accommodation'),
(812, 8, 'Glamping'),
(813, 8, 'Group stays'),
(814, 8, 'Venue finding'),
(900, 9, 'Wedding planners'),
(901, 9, 'Event planners'),
(902, 9, 'Corporate event planners'),
(903, 9, 'Party planners'),
(904, 9, 'On-the-day coordination'),
(905, 9, 'Venue styling coordination'),
(906, 9, 'Supplier sourcing'),
(907, 9, 'Budget planning'),
(908, 9, 'Timelines and schedules'),
(909, 9, 'Toastmasters'),
(910, 9, 'Master of ceremonies'),
(911, 9, 'Celebrants'),
(912, 9, 'Wedding consultancy'),
(1000, 10, 'Venue dressing'),
(1001, 10, 'Balloon styling'),
(1002, 10, 'Backdrops'),
(1003, 10, 'Flower walls'),
(1004, 10, 'Sequin walls'),
(1005, 10, 'Table centrepieces'),
(1006, 10, 'Chair covers and sashes'),
(1007, 10, 'Table linen'),
(1008, 10, 'Aisle décor'),
(1009, 10, 'Ceremony arches'),
(1010, 10, 'Themed props'),
(1011, 10, 'Neon signs'),
(1012, 10, 'Illuminated letters'),
(1013, 10, 'LED dance floors'),
(1014, 10, 'Candy carts styling'),
(1015, 10, 'Ceiling drapes'),
(1016, 10, 'Room transformations'),
(1100, 11, 'Wedding florists'),
(1101, 11, 'Bouquets'),
(1102, 11, 'Buttonholes'),
(1103, 11, 'Table flowers'),
(1104, 11, 'Flower arches'),
(1105, 11, 'Funeral flowers'),
(1106, 11, 'Corporate flowers'),
(1107, 11, 'Dried flowers'),
(1108, 11, 'Artificial flowers'),
(1109, 11, 'Flower crowns'),
(1110, 11, 'Floral installations'),
(1111, 11, 'Seasonal arrangements'),
(1200, 12, 'Tables and chairs'),
(1201, 12, 'Chair hire'),
(1202, 12, 'Tableware'),
(1203, 12, 'Glassware'),
(1204, 12, 'Crockery'),
(1205, 12, 'Cutlery'),
(1206, 12, 'Linen hire'),
(1207, 12, 'Lounge furniture'),
(1208, 12, 'Outdoor furniture'),
(1209, 12, 'Dance floors'),
(1210, 12, 'Staging'),
(1211, 12, 'Bars and counters'),
(1212, 12, 'Garden games'),
(1213, 12, 'Heating'),
(1214, 12, 'Generators'),
(1215, 12, 'Toilets'),
(1216, 12, 'Baby and toddler equipment'),
(1217, 12, 'Event décor props'),
(1300, 13, 'Sound systems'),
(1301, 13, 'Lighting hire'),
(1302, 13, 'Uplighting'),
(1303, 13, 'Disco lighting'),
(1304, 13, 'Stage lighting'),
(1305, 13, 'PA systems'),
(1306, 13, 'Microphones'),
(1307, 13, 'Projectors and screens'),
(1308, 13, 'LED screens'),
(1309, 13, 'AV technicians'),
(1310, 13, 'Staging production'),
(1311, 13, 'Live streaming equipment'),
(1312, 13, 'Special effects'),
(1313, 13, 'Cold sparks'),
(1314, 13, 'Confetti cannons'),
(1315, 13, 'Smoke and haze machines'),
(1316, 13, 'Snow machines'),
(1317, 13, 'Projection mapping'),
(1400, 14, 'Wedding cars'),
(1401, 14, 'Classic cars'),
(1402, 14, 'Luxury cars'),
(1403, 14, 'Limousines'),
(1404, 14, 'Party buses'),
(1405, 14, 'Minibuses'),
(1406, 14, 'Coaches'),
(1407, 14, 'Executive transport'),
(1408, 14, 'Horse and carriage'),
(1409, 14, 'Vintage buses'),
(1410, 14, 'Airport transfers'),
(1411, 14, 'Shuttle services'),
(1412, 14, 'Chauffeurs'),
(1413, 14, 'Novelty transport'),
(1500, 15, 'Bridal makeup'),
(1501, 15, 'Bridal hair'),
(1502, 15, 'Party makeup'),
(1503, 15, 'Prom makeup'),
(1504, 15, 'Special occasion makeup'),
(1505, 15, 'Hair styling'),
(1506, 15, 'Barbers'),
(1507, 15, 'Nail technicians'),
(1508, 15, 'Lash technicians'),
(1509, 15, 'Brows'),
(1510, 15, 'Tanning'),
(1511, 15, 'Beauty packages'),
(1512, 15, 'Mobile beauty services'),
(1513, 15, 'Grooming services'),
(1600, 16, 'Wedding invitations'),
(1601, 16, 'Save the dates'),
(1602, 16, 'Menus'),
(1603, 16, 'Place cards'),
(1604, 16, 'Table plans'),
(1605, 16, 'Order of service'),
(1606, 16, 'Welcome signs'),
(1607, 16, 'Banners'),
(1608, 16, 'Business event signage'),
(1609, 16, 'Printed programmes'),
(1610, 16, 'Thank-you cards'),
(1611, 16, 'Personalised stickers'),
(1612, 16, 'Vinyl decals'),
(1613, 16, 'Large format printing'),
(1614, 16, 'Digital invitations'),
(1700, 17, 'Photo booths'),
(1701, 17, 'Magic mirrors'),
(1702, 17, '360 video booths'),
(1703, 17, 'Selfie pods'),
(1704, 17, 'Audio guestbooks'),
(1705, 17, 'Video guestbooks'),
(1706, 17, 'Roaming photo booths'),
(1707, 17, 'Green screen booths'),
(1708, 17, 'GIF booths'),
(1709, 17, 'Interactive games'),
(1710, 17, 'AR experiences'),
(1711, 17, 'Guestbook stations'),
(1800, 18, 'Marquees'),
(1801, 18, 'Stretch tents'),
(1802, 18, 'Tipis'),
(1803, 18, 'Bell tents'),
(1804, 18, 'Gazebos'),
(1805, 18, 'Outdoor flooring'),
(1806, 18, 'Marquee lighting'),
(1807, 18, 'Outdoor heating'),
(1808, 18, 'Outdoor bars'),
(1809, 18, 'Outdoor kitchens'),
(1810, 18, 'Power and generators'),
(1811, 18, 'Portable toilets'),
(1812, 18, 'Fencing and barriers'),
(1813, 18, 'Weather cover'),
(1814, 18, 'Outdoor furniture packages'),
(1900, 19, 'Waiting staff'),
(1901, 19, 'Bar staff'),
(1902, 19, 'Event hosts'),
(1903, 19, 'Security staff'),
(1904, 19, 'Door staff'),
(1905, 19, 'Stewards'),
(1906, 19, 'First aid cover'),
(1907, 19, 'Cleaners'),
(1908, 19, 'Event setup crew'),
(1909, 19, 'Porters'),
(1910, 19, 'Parking attendants'),
(1911, 19, 'Cloakroom staff'),
(1912, 19, 'Technical crew'),
(1913, 19, 'Toilet attendants'),
(1914, 19, 'Waste management'),
(2000, 20, 'Wedding favours'),
(2001, 20, 'Party bags'),
(2002, 20, 'Personalised gifts'),
(2003, 20, 'Corporate gifts'),
(2004, 20, 'Engraved items'),
(2005, 20, 'Printed clothing'),
(2006, 20, 'Personalised glassware'),
(2007, 20, 'Candles'),
(2008, 20, 'Keepsakes'),
(2009, 20, 'Gift hampers'),
(2010, 20, 'Welcome packs'),
(2011, 20, 'Bridesmaid gifts'),
(2012, 20, 'Groomsmen gifts'),
(2013, 20, 'Teacher and school gifts'),
(2100, 21, 'Yoga sessions'),
(2101, 21, 'Wellness workshops'),
(2102, 21, 'Massage therapists'),
(2103, 21, 'Mindfulness sessions'),
(2104, 21, 'Craft workshops'),
(2105, 21, 'Paint and sip'),
(2106, 21, 'Cooking classes'),
(2107, 21, 'Dance classes'),
(2108, 21, 'Team building activities'),
(2109, 21, 'Escape room experiences'),
(2110, 21, 'Sports activities'),
(2111, 21, 'Outdoor adventures'),
(2112, 21, 'Pamper parties'),
(2113, 21, 'Tarot and fortune telling'),
(2200, 22, 'Christmas events'),
(2201, 22, 'Halloween events'),
(2202, 22, 'Easter events'),
(2203, 22, 'New Year events'),
(2204, 22, 'Summer parties'),
(2205, 22, 'Winter wonderland'),
(2206, 22, 'Santa visits'),
(2207, 22, 'Grotto experiences'),
(2208, 22, 'Themed décor'),
(2209, 22, 'Themed performers'),
(2210, 22, 'Festival themes'),
(2211, 22, 'School fairs'),
(2212, 22, 'Proms'),
(2213, 22, 'Graduation events'),
(2300, 23, 'Conference production'),
(2301, 23, 'Exhibition stands'),
(2302, 23, 'Brand activations'),
(2303, 23, 'Product launches'),
(2304, 23, 'Award ceremonies'),
(2305, 23, 'Networking events'),
(2306, 23, 'Team building'),
(2307, 23, 'Corporate hospitality'),
(2308, 23, 'Promotional staff'),
(2309, 23, 'Branded merchandise'),
(2310, 23, 'Step and repeat walls'),
(2311, 23, 'Press walls'),
(2312, 23, 'Registration desks'),
(2313, 23, 'Delegate management'),
(2400, 24, 'Petting zoos'),
(2401, 24, 'Birds of prey'),
(2402, 24, 'Pony rides'),
(2403, 24, 'Alpacas'),
(2404, 24, 'Therapy animals'),
(2405, 24, 'Dog chaperones'),
(2406, 24, 'Wedding pet sitters'),
(2407, 24, 'Animal encounters'),
(2408, 24, 'Mobile farms'),
(2500, 25, 'Bespoke services'),
(2501, 25, 'Not sure / help me choose'),
(2502, 25, 'Other event supplier')
ON DUPLICATE KEY UPDATE `parent_id`=VALUES(`parent_id`), `name`=VALUES(`name`);

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

INSERT INTO `services` (`id`, `vendor_id`, `title`, `description`, `image`, `price`, `category_id`, `subcategory_id`, `third_category_id`, `status`, `cancellation_policy`) VALUES
(2, 3, 'Sweetie Sweet Cart', 'Indulge your sweet tooth at the Sweetie Cart Candy Stall, a whimsical haven for candy lovers of all ages!', '1716936655_2a9474e339e1b2141db3.jpg', 150.00, 3, 305, NULL, 'active', 'Cancel up to 14 days before for a full refund of your deposit.'),
(3, 3, 'Sweetie Sweet Cart', 'Indulge your sweet tooth at the Sweetie Cart Candy Stall, a whimsical haven for candy lovers of all ages!', '1716936809_ea5338b2e4ba5823d2f9.jpg', 150.00, 3, 305, NULL, 'active', 'Cancel up to 14 days before for a full refund of your deposit.'),
(4, 3, 'Mr Beatys Burgers', 'BurgerBurgerBurgerBurgerBurgerBurgerBurger', '1716937372_8e6a7964ed534149d3cb.jpeg', 240.00, 1, 105, NULL, 'active', '48 hours notice required for deposit refund.'),
(5, 3, 'Dinky Donuts', 'Delight in the irresistible aroma and melt-in-your-mouth goodness of Dinky Donuts!', '1716937744_df87fb10763e1b292fb8.jpeg', 90.00, 3, 304, NULL, 'active', 'Cancel up to 7 days before for a full refund.'),
(90, 3, '(Inactive QA) Vintage photobooth', 'Seeded inactive listing for vendor dashboard QA (services tab + filters).', NULL, 320.00, 17, 1700, NULL, 'inactive', 'Full refund up to 30 days before the event.')
ON DUPLICATE KEY UPDATE `title`=VALUES(`title`), `status`=VALUES(`status`), `cancellation_policy`=VALUES(`cancellation_policy`), `category_id`=VALUES(`category_id`), `subcategory_id`=VALUES(`subcategory_id`), `third_category_id`=VALUES(`third_category_id`);

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
-- Test accounts (password TestPass123!): admin, qa_customer, m.pearson1 (vendor), mark90 (customer)
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
