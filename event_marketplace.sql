-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 10, 2026 at 07:22 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `event_marketplace`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `status` enum('pending','accepted','rejected') DEFAULT 'pending',
  `payment_intent_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `event_id`, `status`, `payment_intent_id`, `created_at`, `updated_at`) VALUES
(86, 4, 9, 'accepted', 0, '2024-09-08 12:19:19', '2024-09-29 08:36:13'),
(87, 4, 12, 'pending', 0, '2024-09-09 19:46:05', '2024-09-09 19:46:05'),
(88, 4, 9, 'pending', 0, '2024-09-09 19:46:07', '2024-09-09 19:46:07'),
(89, 4, 9, 'pending', 0, '2024-09-09 19:52:14', '2024-09-09 19:52:14'),
(90, 4, 9, 'pending', 0, '2024-09-10 19:55:12', '2024-09-10 19:55:12'),
(91, 4, 12, 'pending', 0, '2024-09-10 19:55:12', '2024-09-10 19:55:12'),
(92, 4, 13, 'pending', 0, '2024-09-12 20:04:31', '2024-09-12 20:04:31');

-- --------------------------------------------------------

--
-- Table structure for table `booking_items`
--

CREATE TABLE `booking_items` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `status` enum('pending','accepted','rejected') NOT NULL DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `carts`
--

CREATE TABLE `carts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `start_time` time NOT NULL,
  `end_time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `parent_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `parent_id`) VALUES
(1, 'Food & Catering', NULL),
(2, 'Photography & Videography', NULL),
(3, 'Travel & Transportation', NULL),
(4, 'Beauty & Wellness', NULL),
(5, 'Entertainment & Performances', NULL),
(6, 'Floral & Event Decor', NULL),
(7, 'Stationery & Invitations', NULL),
(8, 'Gifts & Favours', NULL),
(9, 'Event Planning & Coordination', NULL),
(10, 'Event Staffing', NULL),
(11, 'Media & Live Streaming', NULL),
(12, 'Childcare & Family Services', NULL),
(13, 'Corporate Event Services', NULL),
(14, 'Religious & Cultural Services', NULL),
(16, 'Private Event Catering', 1),
(17, 'Corporate Event Catering', 1),
(18, 'Specialty Catering', 1),
(19, 'Wedding Catering', 16),
(20, 'Birthday & Celebration Catering', 16),
(21, 'Christening & Religious Event Catering', 16),
(22, 'Holiday Event Catering', 16),
(23, 'Corporate Lunch Catering', 17),
(24, 'Conference Catering', 17),
(25, 'Gala & Awards Catering', 17),
(26, 'Product Launch Catering', 17),
(27, 'Buffet Catering', 18),
(28, 'BBQ & Outdoor Catering', 18),
(29, 'Vegan & Organic Catering', 18),
(30, 'Kosher Catering', 18),
(31, 'Halal Catering', 18),
(32, 'Dessert-Only Catering', 18),
(33, 'Sweet Carts & Candy Buffets', 18),
(34, 'Photographers', 2),
(35, 'Videographers', 2),
(36, 'Photo Booth Rentals', 2),
(37, 'Drone Photography & Videography', 2),
(38, 'Wedding Photography', 34),
(39, 'Event Photography', 34),
(40, 'Corporate Photography', 34),
(41, 'Portrait Photography', 34),
(42, 'Wedding Videography', 35),
(43, 'Event Videography', 35),
(44, 'Corporate Videography', 35),
(45, 'Cinematic Event Videography', 35),
(46, 'Car Rentals', 3),
(47, 'Group Transportation', 3),
(48, 'Private Charters', 3),
(49, 'Shuttle Services', 3),
(50, 'Luxury Car Rentals', 46),
(51, 'Vintage Car Rentals', 46),
(52, 'SUV Rentals', 46),
(53, 'Electric Vehicle Rentals', 46),
(54, 'Motorcycle Rentals', 46),
(55, 'Party Buses', 47),
(56, 'Limousines', 47),
(57, 'Coach Rentals', 47),
(58, 'Airport Transfers', 47),
(59, 'Helicopter Charters', 48),
(60, 'Private Jet Charters', 48),
(61, 'Yacht & Boat Charters', 48),
(62, 'Makeup & Hair Styling', 4),
(63, 'Spa & Wellness', 4),
(64, 'Bridal & Groom Services', 4),
(65, 'Fitness & Nutrition', 4),
(66, 'Bridal Makeup', 62),
(67, 'Bridal Hair Styling', 62),
(68, 'Airbrush Makeup', 62),
(69, 'Hair Extensions', 62),
(70, 'On-Site Spa Services', 63),
(71, 'Massage Therapists', 63),
(72, 'Lash Extensions', 63),
(73, 'Tanning Services', 63),
(74, 'Bridal Dress Designers', 64),
(75, 'Groomsmen Attire', 64),
(76, 'Bridal Fitness Programs', 64),
(77, 'Cosmetic Dentistry', 64),
(78, 'Live Bands & Musicians', 5),
(79, 'DJs & Music Services', 5),
(80, 'Specialty Performers', 5),
(81, 'Interactive Entertainment', 5),
(82, 'Wedding Bands', 78),
(83, 'Corporate Event Bands', 78),
(84, 'Solo Musicians', 78),
(85, 'String Quartets', 78),
(86, 'Wedding DJs', 79),
(87, 'Corporate Event DJs', 79),
(88, 'Club DJs', 79),
(89, 'Karaoke DJs', 79),
(90, 'Magicians', 80),
(91, 'Aerial Performers', 80),
(92, 'Comedians', 80),
(93, 'Fire Eaters', 80),
(94, 'Photo Booths', 81),
(95, 'Caricature Artists', 81),
(96, 'Dance Instructors', 81),
(97, 'Interactive Games', 81),
(98, 'Floral Arrangements', 6),
(99, 'Event Decor Rentals', 6),
(100, 'Lighting & Design', 6),
(101, 'Themed Decorations', 6),
(102, 'Wedding Bouquets', 98),
(103, 'Centerpieces', 98),
(104, 'Ceremony Flowers', 98),
(105, 'Reception Flowers', 98),
(106, 'Floral Installations', 98),
(107, 'Flower Crowns', 98),
(108, 'Table Settings & Linens', 99),
(109, 'Backdrop Rentals', 99),
(110, 'Chair Covers', 99),
(111, 'Aisle Decorations', 99),
(112, 'Stage Decorations', 99),
(113, 'LED Dance Floors', 100),
(114, 'Mood Lighting', 100),
(115, 'Ceiling Draping', 100),
(116, 'Custom Signage', 100),
(117, 'Balloon Decorations', 101),
(118, 'Themed Event Props', 101),
(119, 'Photo Booth Backdrops', 101),
(120, 'Table Numbers & Signs', 101),
(121, 'Custom Invitations', 7),
(122, 'Event Signage', 7),
(123, 'Wedding Stationery', 7),
(124, 'Event Menus & Programs', 7),
(125, 'Save the Dates', 121),
(126, 'Wedding Invitations', 121),
(127, 'Corporate Invitations', 121),
(128, 'Birthday & Celebration Invitations', 121),
(129, 'Seating Charts', 122),
(130, 'Escort Cards', 122),
(131, 'Welcome Signs', 122),
(132, 'Directional Signage', 122),
(133, 'Table Numbers', 123),
(134, 'Place Cards', 123),
(135, 'Thank You Cards', 123),
(136, 'Order of Service Programs', 123),
(137, 'Event Favours', 8),
(138, 'Custom Gifts', 8),
(139, 'Corporate Gifts', 8),
(140, 'Personalised Stationery', 8),
(141, 'Wedding Favours', 137),
(142, 'Party Favours', 137),
(143, 'Corporate Event Favours', 137),
(144, 'Holiday Event Favours', 137),
(145, 'Custom Packaging', 138),
(146, 'Personalised Gifts', 138),
(147, 'Gift Boxes', 138),
(148, 'Bridal Party Gifts', 138),
(149, 'Full-Service Planning', 9),
(150, 'Day-of Coordination', 9),
(151, 'Destination Event Planning', 9),
(152, 'Partial Planning Services', 9),
(153, 'Wedding Planning', 149),
(154, 'Corporate Event Planning', 149),
(155, 'Social Event Planning', 149),
(156, 'Charity Event Planning', 149),
(157, 'Destination Wedding Planning', 151),
(158, 'Corporate Destination Planning', 151),
(159, 'Elopement Planning', 151),
(160, 'Honeymoon Planning', 151),
(161, 'Bartenders & Beverage Services', 10),
(162, 'Waitstaff Services', 10),
(163, 'Event Security', 10),
(164, 'Specialty Event Staff', 10),
(165, 'Cocktail Bartenders', 161),
(166, 'Wine & Champagne Service', 161),
(167, 'Coffee & Tea Service', 161),
(168, 'Non-Alcoholic Beverage Stations', 161),
(169, 'Valet Parking Services', 164),
(170, 'Event Ushers', 164),
(171, 'Event Hosts', 164),
(172, 'Stagehands & Technical Crew', 164),
(173, 'Photography', 11),
(174, 'Videography', 11),
(175, 'Drone Videography', 11),
(176, 'Live Streaming Services', 11),
(177, 'Wedding Videography', 174),
(178, 'Corporate Event Videography', 174),
(179, 'Cinematic Event Videography', 174),
(180, 'Documentary-Style Videography', 174),
(181, 'Childcare Providers', 12),
(182, 'Family-Friendly Activities', 12),
(183, 'Children’s Entertainers', 12),
(184, 'Babysitting Services', 12),
(185, 'Face Painting', 182),
(186, 'Balloon Artists', 182),
(187, 'Puppet Shows', 182),
(188, 'Craft Stations', 182),
(189, 'Conference & Meeting Planning', 13),
(190, 'Exhibition & Trade Show Services', 13),
(191, 'Corporate Event Catering', 13),
(192, 'Corporate Event Entertainment', 13),
(193, 'Corporate DJs', 192),
(194, 'Corporate Speakers', 192),
(195, 'Corporate Performances', 192),
(196, 'Team-Building Activities', 192),
(197, 'Religious Event Planning', 14),
(198, 'Cultural Event Services', 14),
(199, 'Kosher Event Catering', 14),
(200, 'Halal Event Catering', 14),
(201, 'Cultural Performers', 198),
(202, 'Cultural Music Services', 198),
(203, 'Traditional Dress Services', 198),
(204, 'Cultural Decor Services', 198),
(205, 'Luxury Coach Rentals', 47),
(206, 'Male Grooming Services', 4),
(207, 'Special Effects Makeup', 62),
(208, 'Event Hosts/MCs', 5),
(209, 'AV Technicians', 80),
(210, 'Custom Event Backdrops', 99),
(211, 'Ice Sculptures', 101),
(212, 'Digital Invitations & Event Websites', 7),
(213, 'Eco-Friendly Event Planning', 9),
(214, 'Post-Event Editing Services', 11),
(215, 'Event Childcare Staff', 10),
(216, 'Special Needs Childcare', 12),
(217, 'Virtual Event Services', 13),
(300, 'Game Rentals', 99),
(301, 'Shuffleboard', 300),
(302, 'Table Football', 300),
(303, 'Air Hockey', 300),
(304, 'Ping Pong Tables', 300),
(305, 'Arcade Games', 300),
(306, 'Giant Lawn Games', 300),
(307, 'Interactive Entertainment', 5),
(308, 'Shuffleboard', 307),
(309, 'Table Football', 307),
(310, 'Air Hockey', 307),
(311, 'Ping Pong Tables', 307),
(312, 'Arcade Games', 307),
(313, 'Lawn Games', 307);

-- --------------------------------------------------------

--
-- Table structure for table `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` int(11) NOT NULL,
  `chat_room_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `chat_messages`
--

INSERT INTO `chat_messages` (`id`, `chat_room_id`, `sender_id`, `receiver_id`, `message`, `is_read`, `created_at`) VALUES
(19, 7, 4, 3, 'Why did you delete my event?', 0, '2024-08-14 13:25:28'),
(20, 7, 3, 4, 'because you\'re a cunt', 0, '2024-08-14 13:25:45'),
(21, 19, 4, 3, '1', 0, '2024-08-15 11:56:55'),
(22, 7, 4, 3, '2', 0, '2024-08-15 11:57:01'),
(23, 20, 4, 3, '3', 0, '2024-08-15 11:57:19'),
(24, 20, 3, 4, 'gfedgf', 0, '2024-08-15 11:57:38'),
(25, 22, 3, 4, 'Do you want tuna sushi?\r\n', 0, '2024-08-15 16:57:11'),
(26, 22, 4, 3, 'Yes please. ', 0, '2024-08-15 16:57:22'),
(27, 19, 4, 3, 'Hello', 0, '2024-09-15 19:21:38');

-- --------------------------------------------------------

--
-- Table structure for table `chat_rooms`
--

CREATE TABLE `chat_rooms` (
  `id` int(11) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `service_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `chat_rooms`
--

INSERT INTO `chat_rooms` (`id`, `vendor_id`, `customer_id`, `created_at`, `service_id`) VALUES
(3, 3, 4, '2024-08-14 12:01:59', 0),
(4, 3, 4, '2024-08-14 13:10:52', 0),
(5, 3, 4, '2024-08-14 13:18:13', 0),
(6, 3, 4, '2024-08-14 13:23:17', 0),
(7, 3, 4, '2024-08-14 14:25:20', 22),
(8, 3, 4, '2024-08-14 13:29:43', 0),
(9, 3, 4, '2024-08-14 13:35:07', 0),
(10, 3, 4, '2024-08-14 13:50:06', 0),
(11, 3, 4, '2024-08-14 13:53:09', 0),
(12, 3, 4, '2024-08-14 14:08:35', 0),
(13, 3, 4, '2024-08-14 14:48:26', 0),
(14, 3, 4, '2024-08-14 15:09:09', 0),
(15, 3, 4, '2024-08-14 15:10:11', 0),
(16, 3, 4, '2024-08-14 15:25:36', 0),
(17, 3, 4, '2024-08-14 15:46:39', 0),
(18, 3, 4, '2024-08-15 11:56:18', 0),
(19, 3, 4, '2024-08-15 12:56:51', 1056),
(20, 3, 4, '2024-08-15 12:57:16', 1055),
(21, 3, 4, '2024-08-15 16:56:06', 0),
(22, 3, 4, '2024-08-15 17:56:58', 1054),
(23, 3, 4, '2024-09-01 14:54:06', 0),
(24, 3, 4, '2024-09-01 15:52:48', 0),
(25, 3, 4, '2024-09-02 20:13:08', 0),
(26, 3, 4, '2024-09-05 19:48:37', 0),
(27, 3, 4, '2024-09-05 19:51:09', 0),
(28, 3, 4, '2024-09-05 20:15:31', 0),
(29, 3, 4, '2024-09-05 20:23:43', 0),
(30, 3, 4, '2024-09-05 20:24:35', 0),
(31, 3, 4, '2024-09-07 09:52:19', 0),
(32, 3, 4, '2024-09-08 08:36:27', 0),
(33, 3, 4, '2024-09-08 09:51:48', 0),
(34, 3, 4, '2024-09-08 09:58:03', 0),
(35, 3, 4, '2024-09-08 10:00:15', 0),
(36, 3, 4, '2024-09-08 10:11:35', 0),
(37, 3, 4, '2024-09-08 10:22:40', 0),
(38, 3, 4, '2024-09-08 10:53:08', 0),
(39, 3, 4, '2024-09-08 11:41:20', 0),
(40, 3, 4, '2024-09-08 11:44:51', 0),
(41, 3, 4, '2024-09-08 11:48:47', 0),
(42, 3, 4, '2024-09-08 11:52:42', 0),
(43, 3, 4, '2024-09-08 11:59:45', 0),
(44, 3, 4, '2024-09-08 12:03:20', 0),
(45, 3, 4, '2024-09-08 12:05:42', 0),
(46, 3, 4, '2024-09-08 12:06:02', 0),
(47, 3, 4, '2024-09-08 12:06:46', 0),
(48, 3, 4, '2024-09-08 12:06:54', 0),
(49, 3, 4, '2024-09-08 12:09:40', 0),
(50, 3, 4, '2024-09-08 12:09:48', 0),
(51, 3, 4, '2024-09-08 12:09:55', 0),
(52, 3, 4, '2024-09-08 12:11:11', 0),
(53, 3, 4, '2024-09-08 12:19:19', 0),
(54, 3, 4, '2024-09-10 19:55:12', 0),
(55, 3, 4, '2024-09-10 19:55:12', 0),
(56, 3, 4, '2024-09-12 20:04:31', 0);

-- --------------------------------------------------------

--
-- Table structure for table `duration_pricing`
--

CREATE TABLE `duration_pricing` (
  `id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `duration_label` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `ceremony_type` enum('wedding','party','corporate','other') NOT NULL,
  `location` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `user_id`, `title`, `ceremony_type`, `location`, `date`, `created_at`, `updated_at`) VALUES
(9, 4, 'My Wedding', 'wedding', 'Darlington', '2024-10-17', '2024-08-14 10:29:57', '2024-08-14 10:29:57'),
(12, 4, 'Christening', 'other', 'Newcastle', '2024-11-15', '2024-09-08 19:53:44', '2024-09-08 19:53:44'),
(13, 4, 'Batmitspha', 'party', 'Darlington', '2024-09-26', '2024-09-12 21:02:46', '2024-09-12 21:02:46');

-- --------------------------------------------------------

--
-- Table structure for table `optional_extras`
--

CREATE TABLE `optional_extras` (
  `id` int(11) NOT NULL,
  `service_id` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `optional_extras`
--

INSERT INTO `optional_extras` (`id`, `service_id`, `name`, `price`) VALUES
(67, 1101, 'Test Value 8', 0.00),
(68, 1102, 'Test Value 8', 0.00),
(69, 1103, 'Test Value 8', 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `payment_intent_id` varchar(255) NOT NULL,
  `payment_status` enum('pending','succeeded','failed','canceled') DEFAULT 'pending',
  `amount_paid` decimal(10,2) NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'GBP',
  `payment_method` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `booking_id`, `payment_intent_id`, `payment_status`, `amount_paid`, `currency`, `payment_method`, `created_at`, `updated_at`) VALUES
(22, 86, 'pi_3Pwk5DD4HOXG2l6U0FFVEYEr', 'succeeded', 15.00, 'GBP', 'card', '2024-09-08 12:19:19', '2024-09-08 12:19:19'),
(23, 87, 'pi_3PxDXHD4HOXG2l6U0C0AibyS', 'pending', 123.00, 'GBP', 'card', '2024-09-09 19:46:07', '2024-09-09 19:46:07'),
(24, 88, 'pi_3PxDXJD4HOXG2l6U2eI4fNf1', 'pending', 123.00, 'GBP', 'card', '2024-09-09 19:46:08', '2024-09-09 19:46:08'),
(25, 89, 'pi_3PxDdDD4HOXG2l6U26JjuJVe', 'pending', 123.00, 'GBP', 'card', '2024-09-09 19:52:14', '2024-09-09 19:52:14'),
(26, 90, 'pi_3Pxa8qD4HOXG2l6U0lLKWHuo', 'succeeded', 24.30, 'GBP', 'card', '2024-09-10 19:55:12', '2024-09-10 19:55:12'),
(27, 91, 'pi_3Pxa8qD4HOXG2l6U0lLKWHuo', 'succeeded', 12.30, 'GBP', 'card', '2024-09-10 19:55:12', '2024-09-10 19:55:12'),
(28, 92, 'pi_3PyJFTD4HOXG2l6U0SKQlr94', 'succeeded', 42.30, 'GBP', 'card', '2024-09-12 20:04:31', '2024-09-12 20:04:31');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `short_description` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `subcategory_id` int(11) DEFAULT NULL,
  `third_category_id` int(11) DEFAULT NULL,
  `cancellation_policy` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('active','inactive') DEFAULT 'inactive',
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `vendor_id`, `title`, `short_description`, `description`, `category_id`, `subcategory_id`, `third_category_id`, `cancellation_policy`, `created_at`, `updated_at`, `status`, `deleted_at`) VALUES
(68, 3, 'Elegant LED Dance Floor', 'A stunning centerpiece for any event.', 'Make your event unforgettable with our state-of-the-art LED dance floor. Featuring customizable colors and patterns, this dance floor adds a dazzling touch to weddings, corporate events, and private parties. Easy to install and highly durable, it creates a vibrant atmosphere where your guests can dance the night away. Perfect for adding glamour and sophistication to any venue.', 6, 100, 113, NULL, '2025-01-04 11:20:09', '2025-12-13 10:06:52', 'inactive', NULL),
(87, 3, 'Giant LOVE Letters', 'Create the perfect romantic atmosphere.', 'Our giant LOVE letters are the perfect addition to weddings, engagements, and romantic events. Standing tall with bright LED lighting, these letters create a captivating backdrop for photos and add a magical ambiance to any venue. Easy to transport and set up, they can be placed indoors or outdoors to suit your event\'s needs. Let these elegant letters set the mood for love and celebration.', 6, 99, 210, NULL, '2025-01-05 14:18:19', '2025-01-05 15:28:00', 'active', NULL),
(92, 3, 'Vintage Photo Booth', 'Capture timeless memories.', 'Add a touch of nostalgia to your event with our vintage photo booth. Perfect for weddings, parties, and corporate events, this photo booth comes with a variety of fun props and instant printouts for your guests to enjoy. The stylish design and high-quality camera ensure that every memory is captured in stunning detail. Easy to set up and use, this booth is guaranteed to be a hit with guests of all ages.', 2, 36, 0, NULL, '2025-01-05 15:37:58', '2025-01-06 20:34:09', 'active', NULL),
(93, 3, 'Luxury Wedding Car', 'Arrive in style.', 'Arrive at your wedding or special occasion in ultimate luxury with our premium car service. Choose from a selection of classic and modern vehicles, including limousines, vintage cars, and luxury sedans. Our professional drivers ensure a smooth and comfortable ride, making your journey as memorable as the event itself. Perfect for adding a touch of elegance and sophistication to your special day.', 3, 46, 50, NULL, '2025-01-05 19:29:47', '2025-01-06 20:34:06', 'active', NULL),
(94, 3, 'Live Jazz Band', 'Live music to elevate your event.', 'Set the perfect mood for your event with our professional live jazz band. Featuring talented musicians with years of experience, our band can play a wide range of jazz styles, from smooth and sultry to upbeat and lively. Ideal for weddings, corporate events, and private parties, their captivating performances will leave a lasting impression on your guests.', 5, 78, 82, NULL, '2025-01-05 19:37:02', '2025-01-06 20:34:04', 'active', NULL),
(95, 3, 'Elegant Chair Covers', 'Enhance your seating arrangements.', 'Transform your venue\'s seating with our elegant chair covers. Available in a variety of colors and styles, these covers are designed to complement any event theme. Whether you\'re hosting a wedding, gala, or corporate event, our chair covers provide a polished and sophisticated look. Easy to install and made from high-quality materials, they add a touch of elegance to any setting.', 6, 99, 110, NULL, '2025-01-05 19:39:35', '2025-01-06 20:34:02', 'active', NULL),
(96, 3, 'Chocolate Fountain', 'Indulge your guests.', 'Treat your guests to a decadent chocolate fountain experience. Perfect for weddings, parties, and corporate events, our fountain comes with a variety of dippable treats like strawberries, marshmallows, and pretzels. Made from high-quality stainless steel, it ensures smooth and consistent chocolate flow, creating an indulgent centerpiece for your dessert table.', 1, 16, 19, NULL, '2025-01-05 19:59:10', '2025-01-06 20:34:00', 'active', NULL),
(98, 3, 'Luxury Marquee', 'Host your event in style.', 'Our luxury marquees provide a spacious and elegant setting for weddings, parties, and corporate events. Available in various sizes, they include lighting, flooring, and weatherproofing. Customizable to fit your theme and equipped with modern amenities, these marquees ensure comfort and style.', 2, 34, 38, NULL, '2025-01-06 20:32:11', '2025-01-06 20:33:37', 'active', NULL),
(99, 3, 'Mobile Bar Service', 'Professional bar service on the go.', 'Our mobile bar service brings a fully stocked bar and professional bartenders to your event. Offering a wide selection of drinks, including cocktails, beer, and wine, this service is ideal for weddings, parties, and corporate gatherings. Customizable drink menus and themed setups are available.', 10, 161, 165, NULL, '2025-01-06 20:47:43', '2025-01-06 20:53:00', 'active', NULL),
(101, 3, 'Outdoor Movie Screen', 'A cinematic experience under the stars.', 'Host a movie night or presentation with our outdoor movie screen package. Includes a high-quality projector, sound system, and weatherproof screen. Ideal for family gatherings, corporate events, or community movie nights.', 11, 176, 0, NULL, '2025-12-14 18:24:48', '2025-12-14 18:24:48', 'inactive', NULL),
(102, 3, 'Giant LOVE Letters', 'Create the perfect romantic atmosphere.', 'Our giant LOVE letters are the perfect addition to weddings, engagements, and romantic events. Standing tall with bright LED lighting, these letters create a captivating backdrop for photos and add a magical ambiance to any venue. Easy to transport and set up, they can be placed indoors or outdoors to suit your event\'s needs. Let these elegant letters set the mood for love and celebration.', 1, 16, 19, NULL, '2025-12-14 20:30:09', '2025-12-14 20:30:09', 'inactive', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `services_cancellation_policies`
--

CREATE TABLE `services_cancellation_policies` (
  `id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `cancellation_policy` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services_cancellation_policies`
--

INSERT INTO `services_cancellation_policies` (`id`, `service_id`, `cancellation_policy`, `created_at`, `updated_at`) VALUES
(1, 27, 'Testing 1 2 3', '2024-12-29 20:19:15', '2024-12-29 20:19:15'),
(2, 28, 'test trest test', '2024-12-30 08:30:00', '2024-12-30 08:30:00'),
(3, 29, 'test trest test', '2024-12-30 08:31:54', '2024-12-30 08:31:54'),
(4, 30, 'test trest test', '2024-12-30 08:32:07', '2024-12-30 08:32:07'),
(5, 37, 'No cancellation within 7days of the event.', '2024-12-31 13:41:51', '2024-12-31 13:41:51'),
(6, 38, 'No cancellation within 7days of the event.', '2024-12-31 13:42:07', '2024-12-31 13:42:07'),
(7, 47, 'ewfd ea ghfsg hdsag es', '2024-12-31 13:55:00', '2024-12-31 13:55:00'),
(8, 48, 'ewfd ea ghfsg hdsag es', '2024-12-31 13:55:08', '2024-12-31 13:55:08'),
(9, 49, 'fdf dzagfdag rfahgfeasgfa greag ea', '2024-12-31 14:02:09', '2024-12-31 14:02:09'),
(10, 51, 'dafdagf eargrreeafg reageragr', '2024-12-31 14:14:25', '2024-12-31 14:14:25'),
(11, 68, 'Cancellation Policy – [Company Name]\r\n\r\nPlease be advised that cancellations made up to [48 hours] before a scheduled appointment via [email, text, phone call] will be processed without a penalty.\r\n\r\nCancellations made [48 hours] or less before an appointment will be subject to a charge of [half of the service rate]. This includes appointments where our service provider is unable to access the property, is turned away, or the client is a no-show.\r\n\r\nIf [Company Name] cancels an appointment with less than [48 hours notice], a new appointment will be scheduled without penalty to the client, subject to availability.', '2025-01-04 11:20:09', '2025-01-04 11:20:09'),
(12, 87, 'Cancellation Policy – [Company Name]\r\n\r\nPlease be advised that cancellations made up to [48 hours] before a scheduled appointment via [email, text, phone call] will be processed without a penalty.\r\n\r\nCancellations made [48 hours] or less before an appointment will be subject to a charge of [half of the service rate]. This includes appointments where our service provider is unable to access the property, is turned away, or the client is a no-show.\r\n\r\nIf [Company Name] cancels an appointment with less than [48 hours notice], a new appointment will be scheduled without penalty to the client, subject to availability.', '2025-01-05 14:18:20', '2025-01-05 14:18:20'),
(15, 90, 'Cancellation Policy – [Company Name]\r\n\r\nPlease be advised that cancellations made up to [48 hours] before a scheduled appointment via [email, text, phone call] will be processed without a penalty.\r\n\r\nCancellations made [48 hours] or less before an appointment will be subject to a charge of [half of the service rate]. This includes appointments where our service provider is unable to access the property, is turned away, or the client is a no-show.\r\n\r\nIf [Company Name] cancels an appointment with less than [48 hours notice], a new appointment will be scheduled without penalty to the client, subject to availability.', '2025-01-05 15:31:34', '2025-01-05 15:31:34'),
(16, 91, 'Cancellation Policy – [Company Name]\r\n\r\nPlease be advised that cancellations made up to [48 hours] before a scheduled appointment via [email, text, phone call] will be processed without a penalty.\r\n\r\nCancellations made [48 hours] or less before an appointment will be subject to a charge of [half of the service rate]. This includes appointments where our service provider is unable to access the property, is turned away, or the client is a no-show.\r\n\r\nIf [Company Name] cancels an appointment with less than [48 hours notice], a new appointment will be scheduled without penalty to the client, subject to availability.', '2025-01-05 15:32:04', '2025-01-05 15:32:04'),
(17, 92, 'Cancellation Policy – [Company Name]\r\n\r\nPlease be advised that cancellations made up to [48 hours] before a scheduled appointment via [email, text, phone call] will be processed without a penalty.\r\n\r\nCancellations made [48 hours] or less before an appointment will be subject to a charge of [half of the service rate]. This includes appointments where our service provider is unable to access the property, is turned away, or the client is a no-show.\r\n\r\nIf [Company Name] cancels an appointment with less than [48 hours notice], a new appointment will be scheduled without penalty to the client, subject to availability.', '2025-01-05 15:37:58', '2025-01-05 15:37:58'),
(18, 93, 'Cancellation Policy – [Company Name]\r\n\r\nPlease be advised that cancellations made up to [48 hours] before a scheduled appointment via [email, text, phone call] will be processed without a penalty.\r\n\r\nCancellations made [48 hours] or less before an appointment will be subject to a charge of [half of the service rate]. This includes appointments where our service provider is unable to access the property, is turned away, or the client is a no-show.\r\n\r\nIf [Company Name] cancels an appointment with less than [48 hours notice], a new appointment will be scheduled without penalty to the client, subject to availability.', '2025-01-05 19:29:47', '2025-01-05 19:29:47'),
(19, 94, 'Cancellation Policy – [Company Name]\r\n\r\nPlease be advised that cancellations made up to [48 hours] before a scheduled appointment via [email, text, phone call] will be processed without a penalty.\r\n\r\nCancellations made [48 hours] or less before an appointment will be subject to a charge of [half of the service rate]. This includes appointments where our service provider is unable to access the property, is turned away, or the client is a no-show.\r\n\r\nIf [Company Name] cancels an appointment with less than [48 hours notice], a new appointment will be scheduled without penalty to the client, subject to availability.', '2025-01-05 19:37:02', '2025-01-05 19:37:02'),
(20, 95, 'Cancellation Policy – [Company Name]\r\n\r\nPlease be advised that cancellations made up to [48 hours] before a scheduled appointment via [email, text, phone call] will be processed without a penalty.\r\n\r\nCancellations made [48 hours] or less before an appointment will be subject to a charge of [half of the service rate]. This includes appointments where our service provider is unable to access the property, is turned away, or the client is a no-show.\r\n\r\nIf [Company Name] cancels an appointment with less than [48 hours notice], a new appointment will be scheduled without penalty to the client, subject to availability.', '2025-01-05 19:39:35', '2025-01-05 19:39:35'),
(21, 96, 'Cancellation Policy – [Company Name]\r\n\r\nPlease be advised that cancellations made up to [48 hours] before a scheduled appointment via [email, text, phone call] will be processed without a penalty.\r\n\r\nCancellations made [48 hours] or less before an appointment will be subject to a charge of [half of the service rate]. This includes appointments where our service provider is unable to access the property, is turned away, or the client is a no-show.\r\n\r\nIf [Company Name] cancels an appointment with less than [48 hours notice], a new appointment will be scheduled without penalty to the client, subject to availability.', '2025-01-05 19:59:11', '2025-01-05 19:59:11'),
(23, 98, 'Cancellation Policy – [Company Name]\r\n\r\nPlease be advised that cancellations made up to [48 hours] before a scheduled appointment via [email, text, phone call] will be processed without a penalty.\r\n\r\nCancellations made [48 hours] or less before an appointment will be subject to a charge of [half of the service rate]. This includes appointments where our service provider is unable to access the property, is turned away, or the client is a no-show.\r\n\r\nIf [Company Name] cancels an appointment with less than [48 hours notice], a new appointment will be scheduled without penalty to the client, subject to availability.', '2025-01-06 20:32:11', '2025-01-06 20:32:11'),
(24, 99, 'Cancellation Policy – [Company Name]\r\n\r\nPlease be advised that cancellations made up to [48 hours] before a scheduled appointment via [email, text, phone call] will be processed without a penalty.\r\n\r\nCancellations made [48 hours] or less before an appointment will be subject to a charge of [half of the service rate]. This includes appointments where our service provider is unable to access the property, is turned away, or the client is a no-show.\r\n\r\nIf [Company Name] cancels an appointment with less than [48 hours notice], a new appointment will be scheduled without penalty to the client, subject to availability.', '2025-01-06 20:47:43', '2025-01-06 20:47:43'),
(26, 101, 'Cancellation Policy – [Company Name]\r\n\r\nPlease be advised that cancellations made up to [48 hours] before a scheduled appointment via [email, text, phone call] will be processed without a penalty.\r\n\r\nCancellations made [48 hours] or less before an appointment will be subject to a charge of [half of the service rate]. This includes appointments where our service provider is unable to access the property, is turned away, or the client is a no-show.\r\n\r\nIf [Company Name] cancels an appointment with less than [48 hours notice], a new appointment will be scheduled without penalty to the client, subject to availability.', '2025-12-14 18:24:48', '2025-12-14 18:24:48'),
(27, 102, 'Cancellation Policy – [Company Name]\r\n\r\nPlease be advised that cancellations made up to [48 hours] before a scheduled appointment via [email, text, phone call] will be processed without a penalty.\r\n\r\nCancellations made [48 hours] or less before an appointment will be subject to a charge of [half of the service rate]. This includes appointments where our service provider is unable to access the property, is turned away, or the client is a no-show.\r\n\r\nIf [Company Name] cancels an appointment with less than [48 hours notice], a new appointment will be scheduled without penalty to the client, subject to availability.', '2025-12-14 20:30:09', '2025-12-14 20:30:09');

-- --------------------------------------------------------

--
-- Table structure for table `services_corporate_event_pricing`
--

CREATE TABLE `services_corporate_event_pricing` (
  `id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `pricing_details` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services_corporate_event_pricing`
--

INSERT INTO `services_corporate_event_pricing` (`id`, `service_id`, `pricing_details`) VALUES
(1, 101, '{\"corporate_enabled\":1,\"corporate_invoice_supported\":1,\"corporate_po_supported\":0,\"corporate_payment_terms\":[\"due_on_booking\"],\"corporate_accounts_email\":\"markyj@zoho.com\",\"corporate_vat_registered\":0,\"corporate_vat_number\":\"\",\"corporate_pli_level\":\"2m\",\"corporate_risk_assessment\":1,\"corporate_method_statement\":1,\"corporate_pat_testing\":\"yes\",\"corporate_dbs\":\"yes\",\"corporate_surcharge_type\":\"none\",\"corporate_surcharge_value\":\"\",\"corporate_invoice_fee\":\"\",\"corporate_min_spend\":\"\"}');

-- --------------------------------------------------------

--
-- Table structure for table `services_custom_duration_pricing`
--

CREATE TABLE `services_custom_duration_pricing` (
  `id` int(11) NOT NULL,
  `private_event_pricing_id` int(11) NOT NULL,
  `duration_type` text NOT NULL,
  `duration` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services_custom_duration_pricing`
--

INSERT INTO `services_custom_duration_pricing` (`id`, `private_event_pricing_id`, `duration_type`, `duration`, `price`) VALUES
(6, 28, 'hour', 0, 0.00),
(7, 28, 'hour', 0, 0.00),
(8, 28, 'hour', 0, 0.00),
(9, 28, 'hour', 0, 0.00),
(10, 28, 'hour', 0, 0.00),
(11, 29, 'hour', 0, 0.00),
(12, 29, 'hour', 0, 0.00),
(13, 29, 'hour', 0, 0.00),
(14, 29, 'hour', 0, 0.00),
(15, 29, 'hour', 0, 0.00),
(24, 38, 'hour', 0, 0.00),
(25, 38, 'hour', 0, 0.00),
(26, 38, 'hour', 0, 0.00),
(27, 38, 'hour', 0, 0.00),
(28, 39, 'hour', 0, 0.00),
(29, 39, 'hour', 0, 0.00),
(30, 39, 'hour', 0, 0.00),
(31, 39, 'hour', 0, 0.00),
(32, 40, 'hour', 1, 150.00),
(33, 40, 'hour', 2, 200.00),
(34, 40, 'hour', 3, 250.00),
(35, 40, 'day', 1, 400.00),
(36, 40, 'day', 2, 500.00),
(42, 42, 'hour', 1, 80.00),
(43, 42, 'hour', 2, 120.00),
(44, 42, 'hour', 3, 180.00),
(45, 42, 'day', 1, 400.00),
(46, 42, 'day', 2, 700.00),
(53, 52, 'day', 1, 350.00),
(126, 71, 'hour', 1, 1.00),
(127, 71, 'hour', 2, 2.00),
(128, 71, 'hour', 3, 4.00),
(129, 71, 'day', 1, 3.00),
(137, 74, 'hour', 3, 100.00),
(138, 74, 'hour', 4, 120.00),
(139, 74, 'hour', 5, 140.00),
(140, 75, 'hour', 3, 100.00),
(141, 75, 'hour', 4, 120.00),
(142, 75, 'hour', 5, 140.00),
(143, 76, 'hour', 3, 100.00),
(144, 76, 'hour', 4, 120.00),
(145, 76, 'hour', 5, 140.00),
(146, 78, 'hour', 1, 150.00),
(147, 78, 'hour', 2, 200.00),
(148, 78, 'hour', 3, 300.00),
(149, 83, 'hour', 2, 150.00),
(150, 83, 'hour', 3, 200.00),
(151, 83, 'hour', 4, 250.00),
(152, 83, 'hour', 5, 300.00),
(153, 83, 'day', 1, 600.00);

-- --------------------------------------------------------

--
-- Table structure for table `services_event_types`
--

CREATE TABLE `services_event_types` (
  `id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `event_type` enum('public','private','corporate') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services_event_types`
--

INSERT INTO `services_event_types` (`id`, `service_id`, `event_type`) VALUES
(1, 18, 'public'),
(2, 18, 'private'),
(15, 25, 'public'),
(16, 25, 'private'),
(18, 27, 'private'),
(19, 28, 'private'),
(20, 29, 'private'),
(21, 30, 'private'),
(27, 37, 'private'),
(28, 38, 'private'),
(37, 47, 'private'),
(38, 48, 'private'),
(39, 49, 'private'),
(41, 51, 'private'),
(51, 68, 'private'),
(70, 87, 'private'),
(73, 90, 'private'),
(74, 91, 'private'),
(75, 92, 'private'),
(76, 93, 'private'),
(77, 94, 'private'),
(78, 95, 'private'),
(79, 96, 'public'),
(80, 96, 'private'),
(82, 98, 'private'),
(83, 99, 'public'),
(84, 99, 'private'),
(86, 101, 'corporate'),
(87, 102, 'public');

-- --------------------------------------------------------

--
-- Table structure for table `services_guest_based_pricing`
--

CREATE TABLE `services_guest_based_pricing` (
  `id` int(11) NOT NULL,
  `private_event_pricing_id` int(11) NOT NULL,
  `min_guest` int(11) NOT NULL,
  `max_guest` int(11) NOT NULL,
  `guest_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services_guest_based_pricing`
--

INSERT INTO `services_guest_based_pricing` (`id`, `private_event_pricing_id`, `min_guest`, `max_guest`, `guest_price`) VALUES
(11, 17, 1, 10, 43.00),
(12, 17, 11, 25, 50.00),
(13, 79, 1, 25, 25.00),
(14, 79, 26, 50, 22.50),
(15, 79, 51, 75, 19.00),
(16, 79, 76, 100, 17.00),
(17, 80, 1, 50, 3.00),
(18, 80, 51, 100, 2.75),
(19, 80, 101, 150, 2.50);

-- --------------------------------------------------------

--
-- Table structure for table `services_locations`
--

CREATE TABLE `services_locations` (
  `id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `service_location` varchar(255) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `all_travel_included` tinyint(1) DEFAULT 0,
  `no_travel_limit` tinyint(1) DEFAULT 0,
  `free_coverage_radius` int(11) DEFAULT NULL,
  `paid_coverage_radius` int(11) DEFAULT NULL,
  `travel_fee_per_km` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services_locations`
--

INSERT INTO `services_locations` (`id`, `service_id`, `service_location`, `latitude`, `longitude`, `all_travel_included`, `no_travel_limit`, `free_coverage_radius`, `paid_coverage_radius`, `travel_fee_per_km`, `created_at`, `updated_at`) VALUES
(9, 9, 'Darlington DL2, UK', 54.57596370, -1.67758100, 1, 0, 23, NULL, NULL, '2024-12-28 09:27:36', '2024-12-28 09:27:36'),
(10, 10, 'Darlington, UK', 54.52361000, -1.55945800, 1, 0, 34, NULL, NULL, '2024-12-28 10:30:05', '2024-12-28 10:30:05'),
(11, 13, 'Darlington, UK', 54.52361000, -1.55945800, 1, 0, 23, NULL, NULL, '2024-12-28 12:17:33', '2024-12-28 12:17:33'),
(12, 14, 'Darlington, UK', 54.52361000, -1.55945800, 1, 0, 3, NULL, NULL, '2024-12-28 12:21:10', '2024-12-28 12:21:10'),
(13, 15, 'Darlington, UK', 54.52361000, -1.55945800, 1, 0, 34, NULL, NULL, '2024-12-28 12:23:35', '2024-12-28 12:23:35'),
(14, 17, 'Testour, Tunisia', 36.54990000, 9.44226640, 1, 0, 23, NULL, NULL, '2024-12-28 13:23:49', '2024-12-28 13:23:49'),
(15, 18, 'Darlington DL2, UK', 54.57596370, -1.67758100, 1, 0, 40, NULL, NULL, '2024-12-29 17:44:07', '2024-12-29 17:44:07'),
(16, 25, 'Darlington DL2, UK', 54.57596370, -1.67758100, 1, 0, 40, NULL, NULL, '2024-12-29 18:07:05', '2024-12-29 18:07:05'),
(18, 27, 'Darlington, UK', 54.52361000, -1.55945800, 1, 0, 23, NULL, NULL, '2024-12-29 20:19:15', '2024-12-29 20:19:15'),
(19, 28, 'Darlington, UK', 54.52361000, -1.55945800, 1, 0, 23, NULL, NULL, '2024-12-30 08:30:00', '2024-12-30 08:30:00'),
(20, 29, 'Darlington, UK', 54.52361000, -1.55945800, 1, 0, 23, NULL, NULL, '2024-12-30 08:31:54', '2024-12-30 08:31:54'),
(21, 30, 'Darlington, UK', 54.52361000, -1.55945800, 1, 0, 23, NULL, NULL, '2024-12-30 08:32:07', '2024-12-30 08:32:07'),
(22, 37, 'Lincoln, UK', 53.23068800, -0.54057900, 1, 0, 75, NULL, NULL, '2024-12-31 13:41:51', '2024-12-31 13:41:51'),
(23, 38, 'Lincoln, UK', 53.23068800, -0.54057900, 1, 0, 75, NULL, NULL, '2024-12-31 13:42:07', '2024-12-31 13:42:07'),
(24, 47, 'Darlington DL2, UK', 54.57596370, -1.67758100, 0, 0, 10, 50, 2.00, '2024-12-31 13:55:00', '2024-12-31 13:55:00'),
(25, 48, 'Darlington DL2, UK', 54.57596370, -1.67758100, 0, 0, 10, 50, 2.00, '2024-12-31 13:55:08', '2024-12-31 13:55:08'),
(26, 49, 'Darlington DL2 2ZF, UK', 54.54295390, -1.59361020, 0, 0, 10, 25, 2.00, '2024-12-31 14:02:09', '2024-12-31 14:02:09'),
(27, 51, 'Darlington, UK', 54.52361000, -1.55945800, 0, 0, 10, 30, 2.00, '2024-12-31 14:14:25', '2024-12-31 14:14:25'),
(33, 68, 'Darlington DL2, UK', 54.57596370, -1.67758100, 1, 0, 60, NULL, NULL, '2025-01-04 11:20:09', '2025-01-04 11:20:09'),
(52, 87, 'Newcastle upon Tyne, UK', 54.97825200, -1.61778000, 0, 1, 40, NULL, 2.00, '2025-01-05 14:18:20', '2025-01-05 14:18:20'),
(55, 90, 'Darlington, UK', 54.52361000, -1.55945800, 1, 0, 60, NULL, NULL, '2025-01-05 15:31:34', '2025-01-05 15:31:34'),
(56, 91, 'Darlington, UK', 54.52361000, -1.55945800, 1, 0, 60, NULL, NULL, '2025-01-05 15:32:04', '2025-01-05 15:32:04'),
(57, 92, 'Darlington DL2 2ZF, UK', 54.54295390, -1.59361020, 1, 0, 60, NULL, NULL, '2025-01-05 15:37:58', '2025-01-05 15:37:58'),
(58, 93, 'Darlington, UK', 54.52361000, -1.55945800, 0, 1, 60, NULL, 1.85, '2025-01-05 19:29:47', '2025-01-05 19:29:47'),
(59, 94, 'Darlington DL2 2ZF, UK', 54.54295390, -1.59361020, 0, 0, 60, 100, 1.95, '2025-01-05 19:37:02', '2025-01-05 19:37:02'),
(60, 95, 'Lincoln LN5, UK', 53.10902040, -0.57474370, 1, 0, 150, NULL, NULL, '2025-01-05 19:39:35', '2025-01-05 19:39:35'),
(61, 96, 'Darlington DL2 2ZF, UK', 54.54295390, -1.59361020, 0, 1, 0, NULL, 1.50, '2025-01-05 19:59:11', '2025-01-05 19:59:11'),
(63, 98, 'Barnard Castle, UK', 54.54528400, -1.92374100, 0, 1, 0, NULL, 2.85, '2025-01-06 20:32:11', '2025-01-06 20:32:11'),
(64, 99, 'Consett, UK', 54.85179700, -1.83302600, 0, 1, 80, NULL, 1.85, '2025-01-06 20:47:43', '2025-01-06 20:47:43'),
(66, 101, 'Darlington DL2, UK', 54.57596370, -1.67758100, 1, 0, 10, NULL, NULL, '2025-12-14 18:24:48', '2025-12-14 18:24:48'),
(67, 102, 'Darlington, UK', 54.52361000, -1.55945800, 0, 1, 0, NULL, 1.00, '2025-12-14 20:30:09', '2025-12-14 20:30:09');

-- --------------------------------------------------------

--
-- Table structure for table `services_optional_extras`
--

CREATE TABLE `services_optional_extras` (
  `id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `quantity` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services_optional_extras`
--

INSERT INTO `services_optional_extras` (`id`, `service_id`, `name`, `description`, `price`, `created_at`, `updated_at`, `quantity`) VALUES
(1, 30, 'Additional hours', '', 25.00, '2024-12-30 08:32:07', '2024-12-30 08:32:07', NULL),
(2, 30, 'Custom props or green screen designs', '', 45.00, '2024-12-30 08:32:07', '2024-12-30 08:32:07', NULL),
(3, 37, 'Floral arrangements or garlands to drape over the letters.', '', 35.00, '2024-12-31 13:41:51', '2024-12-31 13:41:51', NULL),
(4, 37, 'Add-on signage (e.g., couple’s initials, wedding date, or hashtag).', '', 35.00, '2024-12-31 13:41:51', '2024-12-31 13:41:51', NULL),
(5, 38, 'Floral arrangements or garlands to drape over the letters.', '', 35.00, '2024-12-31 13:42:07', '2024-12-31 13:42:07', NULL),
(6, 38, 'Add-on signage (e.g., couple’s initials, wedding date, or hashtag).', '', 35.00, '2024-12-31 13:42:07', '2024-12-31 13:42:07', NULL),
(7, 47, 'LED Dance Floor: Add an illuminated dance floor for a fun and interactive element.', '', 80.00, '2024-12-31 13:55:00', '2024-12-31 13:55:00', NULL),
(8, 47, 'Smoke or Haze Machine: Create a dramatic effect on the dance floor.', '', 65.00, '2024-12-31 13:55:00', '2024-12-31 13:55:00', NULL),
(9, 48, 'LED Dance Floor: Add an illuminated dance floor for a fun and interactive element.', '', 80.00, '2024-12-31 13:55:08', '2024-12-31 13:55:08', NULL),
(10, 48, 'Smoke or Haze Machine: Create a dramatic effect on the dance floor.', '', 65.00, '2024-12-31 13:55:08', '2024-12-31 13:55:08', NULL),
(11, 49, 'LED Dance Floor: Add an illuminated dance floor for a fun and interactive element.', '', 50.00, '2024-12-31 14:02:09', '2024-12-31 14:02:09', NULL),
(12, 49, 'Smoke or Haze Machine: Create a dramatic effect on the dance floor.', '', 75.00, '2024-12-31 14:02:09', '2024-12-31 14:02:09', NULL),
(13, 51, 'dsfa', '', 34.00, '2024-12-31 14:14:25', '2024-12-31 14:14:25', NULL),
(17, 68, 'Custom Colours', 'Choose a colour scheme that matches your event theme.', 50.00, '2025-01-04 11:20:09', '2025-01-04 11:20:09', NULL),
(18, 68, 'Extended Rental Period', 'Keep the dance floor for an additional day.', 100.00, '2025-01-04 11:20:09', '2025-01-04 11:20:09', NULL),
(19, 68, 'Setup Assistance', 'On-site staff to assist with setup and breakdown.', 75.00, '2025-01-04 11:20:09', '2025-01-04 11:20:09', NULL),
(21, 87, 'Floral Garland', 'Add a decorative floral garland to the letters.', 25.00, '2025-01-05 14:18:20', '2025-01-05 14:18:20', NULL),
(22, 87, 'Outdoor Setup', 'Weatherproof setup for outdoor events.', 30.00, '2025-01-05 14:18:20', '2025-01-05 14:18:20', NULL),
(23, 87, 'Custom Letter Colours', 'Choose colours to match your event theme.', 20.00, '2025-01-05 14:18:20', '2025-01-05 14:18:20', NULL),
(30, 90, 'Custom Backdrop', 'Personalised backdrop with event name or logo.', 50.00, '2025-01-05 15:31:34', '2025-01-05 15:31:34', NULL),
(31, 90, 'Digital Copy of Photos', 'Receive all photos on a USB or via cloud link.', 30.00, '2025-01-05 15:31:34', '2025-01-05 15:31:34', NULL),
(32, 90, 'Extra Printouts', 'Unlimited printouts for guests during the event.', 40.00, '2025-01-05 15:31:34', '2025-01-05 15:31:34', NULL),
(33, 91, 'Custom Backdrop', 'Personalised backdrop with event name or logo.', 50.00, '2025-01-05 15:32:04', '2025-01-05 15:32:04', NULL),
(34, 91, 'Digital Copy of Photos', 'Receive all photos on a USB or via cloud link.', 30.00, '2025-01-05 15:32:04', '2025-01-05 15:32:04', NULL),
(35, 91, 'Extra Printouts', 'Unlimited printouts for guests during the event.', 40.00, '2025-01-05 15:32:04', '2025-01-05 15:32:04', NULL),
(36, 92, 'Custom Backdrop', 'Personalised backdrop with event name or logo.', 50.00, '2025-01-05 15:37:58', '2025-01-05 15:37:58', NULL),
(37, 92, 'Digital Copy of Photos', 'Receive all photos on a USB or via cloud link.', 30.00, '2025-01-05 15:37:58', '2025-01-05 15:37:58', NULL),
(38, 92, 'Extra Printouts', 'Unlimited printouts for guests during the event.', 40.00, '2025-01-05 15:37:58', '2025-01-05 15:37:58', NULL),
(39, 93, 'Champagne Package', 'Includes chilled champagne and glasses.', 75.00, '2025-01-05 19:29:47', '2025-01-05 19:29:47', NULL),
(40, 93, 'Decorated Car', 'Custom ribbons and flowers to match your theme.', 50.00, '2025-01-05 19:29:47', '2025-01-05 19:29:47', NULL),
(41, 93, 'Extended Hours', 'Additional hire time for the car.', 100.00, '2025-01-05 19:29:47', '2025-01-05 19:29:47', NULL),
(42, 93, 'Additional cars', 'Additional cars for other part members.', 400.00, '2025-01-05 19:29:47', '2025-01-05 19:29:47', NULL),
(43, 94, 'Extended Performance', 'Add an extra hour of music.', 200.00, '2025-01-05 19:37:02', '2025-01-05 19:37:02', NULL),
(44, 94, 'Custom Playlist', 'Request specific songs or styles.', 100.00, '2025-01-05 19:37:02', '2025-01-05 19:37:02', NULL),
(45, 94, 'Meet and Greet', 'Guests can meet the band after the performance.', 50.00, '2025-01-05 19:37:02', '2025-01-05 19:37:02', NULL),
(46, 95, 'Custom Colours', 'Chair covers in colours of your choice.', 20.00, '2025-01-05 19:39:35', '2025-01-05 19:39:35', NULL),
(47, 95, 'Sashes', 'Add matching or contrasting sashes for extra style.', 15.00, '2025-01-05 19:39:35', '2025-01-05 19:39:35', NULL),
(48, 95, 'Setup Service', 'Our team will install and remove the covers.', 250.00, '2025-01-05 19:39:35', '2025-01-05 19:39:35', NULL),
(49, 96, 'Additional Dips', 'Extra treats such as brownies and fruit skewers.', 30.00, '2025-01-05 19:59:11', '2025-01-05 19:59:11', NULL),
(50, 96, 'White Chocolate Option', 'Replace milk chocolate with white chocolate.', 25.00, '2025-01-05 19:59:11', '2025-01-05 19:59:11', NULL),
(51, 96, 'Extra Servings', 'Increase the quantity to serve more guests.', 50.00, '2025-01-05 19:59:11', '2025-01-05 19:59:11', NULL),
(55, 98, 'Heating', 'Provide heaters for colder weather.', 100.00, '2025-01-06 20:32:11', '2025-01-06 20:32:11', NULL),
(56, 98, 'Decor Package', 'Include decorative elements to match your theme.', 150.00, '2025-01-06 20:32:11', '2025-01-06 20:32:11', NULL),
(57, 98, 'Catering Area', 'Additional space for catering setup.', 200.00, '2025-01-06 20:32:11', '2025-01-06 20:32:11', NULL),
(58, 99, 'Custom Cocktails', 'Create signature cocktails for your event.', 50.00, '2025-01-06 20:47:43', '2025-01-06 20:47:43', NULL),
(59, 99, 'Extended Hours', 'Additional bar service time.', 100.00, '2025-01-06 20:47:43', '2025-01-06 20:47:43', NULL),
(60, 99, 'Themed Bar Setup', 'Decorate the bar to match your theme.', 75.00, '2025-01-06 20:47:43', '2025-01-06 20:47:43', NULL),
(64, 102, 'Additional lighting unit', 'An extra professional lighting fixture added to improve coverage across larger venues or darker areas. Ideal for bigger dancefloors or venues with limited ambient lighting', 50.00, '2025-12-14 20:30:09', '2025-12-14 20:30:09', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `services_private_event_pricing`
--

CREATE TABLE `services_private_event_pricing` (
  `id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `pricing_type` enum('guest_based_pricing','custom_duration_pricing','tiered_packages_pricing') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services_private_event_pricing`
--

INSERT INTO `services_private_event_pricing` (`id`, `service_id`, `pricing_type`) VALUES
(9, 9, 'custom_duration_pricing'),
(10, 18, 'guest_based_pricing'),
(17, 25, 'guest_based_pricing'),
(19, 27, 'tiered_packages_pricing'),
(20, 28, 'tiered_packages_pricing'),
(21, 29, 'tiered_packages_pricing'),
(22, 30, 'tiered_packages_pricing'),
(28, 37, 'custom_duration_pricing'),
(29, 38, 'custom_duration_pricing'),
(38, 47, 'custom_duration_pricing'),
(39, 48, 'custom_duration_pricing'),
(40, 49, 'custom_duration_pricing'),
(42, 51, 'custom_duration_pricing'),
(52, 68, 'custom_duration_pricing'),
(71, 87, 'custom_duration_pricing'),
(74, 90, 'custom_duration_pricing'),
(75, 91, 'custom_duration_pricing'),
(76, 92, 'custom_duration_pricing'),
(77, 93, 'tiered_packages_pricing'),
(78, 94, 'custom_duration_pricing'),
(79, 95, 'guest_based_pricing'),
(80, 96, 'guest_based_pricing'),
(82, 98, 'tiered_packages_pricing'),
(83, 99, 'custom_duration_pricing');

-- --------------------------------------------------------

--
-- Table structure for table `services_public_event_pricing`
--

CREATE TABLE `services_public_event_pricing` (
  `id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `commission_percentage` decimal(5,2) DEFAULT NULL,
  `min_attendance` int(11) DEFAULT NULL,
  `max_attendance` int(11) DEFAULT NULL,
  `max_pitch_fee` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services_public_event_pricing`
--

INSERT INTO `services_public_event_pricing` (`id`, `service_id`, `commission_percentage`, `min_attendance`, `max_attendance`, `max_pitch_fee`) VALUES
(1, 102, NULL, 5, 10, 500.00);

-- --------------------------------------------------------

--
-- Table structure for table `services_service_tags`
--

CREATE TABLE `services_service_tags` (
  `id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services_service_tags`
--

INSERT INTO `services_service_tags` (`id`, `service_id`, `tag_id`) VALUES
(4, 27, 4),
(5, 27, 5),
(6, 27, 6),
(7, 28, 7),
(8, 28, 8),
(9, 28, 6),
(10, 29, 7),
(11, 29, 8),
(12, 29, 6),
(13, 30, 7),
(14, 30, 8),
(15, 30, 6),
(34, 37, 27),
(35, 37, 28),
(36, 37, 29),
(37, 38, 27),
(38, 38, 28),
(39, 38, 29),
(64, 47, 54),
(65, 47, 55),
(66, 47, 56),
(67, 48, 54),
(68, 48, 55),
(69, 48, 56),
(70, 49, 54),
(71, 49, 55),
(72, 49, 56),
(76, 51, 55),
(77, 51, 59),
(78, 51, 60),
(124, 68, 91),
(125, 68, 92),
(126, 68, 27),
(181, 87, 27),
(182, 87, 28),
(183, 87, 29),
(190, 90, 4),
(191, 90, 8),
(192, 90, 94),
(193, 91, 4),
(194, 91, 8),
(195, 91, 94),
(196, 92, 4),
(197, 92, 8),
(198, 92, 94),
(199, 93, 27),
(200, 93, 95),
(201, 93, 96),
(202, 94, 55),
(203, 94, 59),
(204, 94, 60),
(205, 95, 97),
(206, 95, 98),
(207, 95, 28),
(208, 96, 99),
(209, 96, 100),
(210, 96, 101),
(214, 98, 104),
(215, 98, 105),
(216, 98, 106),
(217, 99, 107),
(218, 99, 108),
(219, 99, 109),
(223, 101, 113),
(224, 101, 114),
(225, 101, 115),
(226, 102, 27),
(227, 102, 28),
(228, 102, 29);

-- --------------------------------------------------------

--
-- Table structure for table `services_tags`
--

CREATE TABLE `services_tags` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services_tags`
--

INSERT INTO `services_tags` (`id`, `name`) VALUES
(60, 'band'),
(107, 'bar'),
(8, 'booth'),
(5, 'booths'),
(111, 'bounce'),
(95, 'car'),
(103, 'carnival'),
(97, 'chair'),
(99, 'chocolate'),
(98, 'covers'),
(91, 'dance'),
(28, 'decoration'),
(101, 'dessert'),
(54, 'DJ'),
(108, 'drinks'),
(106, 'event'),
(100, 'fountain'),
(102, 'games'),
(110, 'inflatable'),
(59, 'jazz'),
(112, 'kids'),
(92, 'LED'),
(29, 'love'),
(105, 'luxury'),
(104, 'marquee'),
(109, 'mobile'),
(113, 'movie'),
(55, 'music'),
(114, 'outdoor'),
(56, 'party'),
(4, 'photo'),
(6, 'photobooth'),
(7, 'photos'),
(115, 'screen'),
(96, 'transport'),
(94, 'vintage'),
(27, 'wedding');

-- --------------------------------------------------------

--
-- Table structure for table `services_tiered_packages_pricing`
--

CREATE TABLE `services_tiered_packages_pricing` (
  `id` int(11) NOT NULL,
  `private_event_pricing_id` int(11) NOT NULL,
  `package_name` varchar(255) NOT NULL,
  `package_description` text NOT NULL,
  `package_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services_tiered_packages_pricing`
--

INSERT INTO `services_tiered_packages_pricing` (`id`, `private_event_pricing_id`, `package_name`, `package_description`, `package_price`) VALUES
(7, 20, 'Classic Memories', '2 hours of photo booth service Unlimited 4x6 prints Basic prop set (hats, glasses, signs) Digital gallery delivered post-event', 250.00),
(8, 20, 'Glam & Glow', '4 hours of booth service Unlimited prints with custom border and logo Themed props, curated to your event LED-lit booth for that extra wow factor Online album for social sharing', 350.00),
(9, 20, 'Red Carpet Experience', '6 hours of booth service Complete custom branding on prints and booth backdrop VIP props (feather boas, top hats, custom signage) On-site photographer to capture candid moments outside the booth Premium photo album or guest book Live social media integration', 450.00),
(10, 21, 'Classic Memories', '2 hours of photo booth service Unlimited 4x6 prints Basic prop set (hats, glasses, signs) Digital gallery delivered post-event', 250.00),
(11, 21, 'Glam & Glow', '4 hours of booth service Unlimited prints with custom border and logo Themed props, curated to your event LED-lit booth for that extra wow factor Online album for social sharing', 350.00),
(12, 21, 'Red Carpet Experience', '6 hours of booth service Complete custom branding on prints and booth backdrop VIP props (feather boas, top hats, custom signage) On-site photographer to capture candid moments outside the booth Premium photo album or guest book Live social media integration', 450.00),
(13, 22, 'Classic Memories', '2 hours of photo booth service Unlimited 4x6 prints Basic prop set (hats, glasses, signs) Digital gallery delivered post-event', 250.00),
(14, 22, 'Glam & Glow', '4 hours of booth service Unlimited prints with custom border and logo Themed props, curated to your event LED-lit booth for that extra wow factor Online album for social sharing', 350.00),
(15, 22, 'Red Carpet Experience', '6 hours of booth service Complete custom branding on prints and booth backdrop VIP props (feather boas, top hats, custom signage) On-site photographer to capture candid moments outside the booth Premium photo album or guest book Live social media integration', 450.00),
(16, 77, 'Standard', 'Includes transportation from one location to the venue.', 400.00),
(17, 77, 'Premium', 'Round trip and multiple pickups/drop-offs', 600.00),
(18, 77, 'Deluxe', ' Full-day hire with chauffeur services, champagne, and decorations.', 800.00),
(24, 82, '12.5m²', '25 guests', 800.00),
(25, 82, '20m²', '40 guests', 1200.00),
(26, 82, '25m²', '50 guests', 1400.00),
(27, 82, '50m²', '100', 2000.00),
(28, 82, '75m²', '150', 2300.00);

-- --------------------------------------------------------

--
-- Table structure for table `service_images`
--

CREATE TABLE `service_images` (
  `id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `thumbnail_path` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_primary` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `service_images`
--

INSERT INTO `service_images` (`id`, `service_id`, `image_path`, `thumbnail_path`, `created_at`, `updated_at`, `is_primary`) VALUES
(206, 68, 'uploads/services/1735988119_d83adeb47688289c69a5.jpg', 'uploads/services/thumbnails/thumb_1735988119_d83adeb47688289c69a5.jpg', '2025-01-04 11:20:09', '2025-01-06 20:54:43', 1),
(207, 68, 'uploads/services/1735988119_e66070f1b2e312fa46d2.jpg', 'uploads/services/thumbnails/thumb_1735988119_e66070f1b2e312fa46d2.jpg', '2025-01-04 11:20:09', '2025-01-06 20:54:53', 0),
(244, 87, 'uploads/services/1736065387_2854e1735d8b6d18cd4a.jpg', 'uploads/services/thumbnails/thumb_1736065387_2854e1735d8b6d18cd4a.jpg', '2025-01-05 14:18:20', '2025-01-06 20:54:58', 1),
(245, 87, 'uploads/services/1736065387_0781e62ad503443dd333.jpg', 'uploads/services/thumbnails/thumb_1736065387_0781e62ad503443dd333.jpg', '2025-01-05 14:18:20', '2025-01-07 19:10:04', 0),
(254, 92, 'uploads/services/1736091435_2712281c7e45f8672fab.jpg', 'uploads/services/thumbnails/thumb_1736091435_2712281c7e45f8672fab.jpg', '2025-01-05 15:37:58', '2025-01-06 20:55:09', 1),
(255, 92, 'uploads/services/1736091435_0297b3addf09add66f13.jpg', 'uploads/services/thumbnails/thumb_1736091435_0297b3addf09add66f13.jpg', '2025-01-05 15:37:58', '2025-01-05 15:37:58', 0),
(256, 93, 'uploads/services/1736101211_d7d65d01ec895126c5cc.jpg', 'uploads/services/thumbnails/thumb_1736101211_d7d65d01ec895126c5cc.jpg', '2025-01-05 19:29:47', '2025-01-06 20:55:16', 1),
(257, 93, 'uploads/services/1736101211_d4605173ce33fb7d7379.jpg', 'uploads/services/thumbnails/thumb_1736101211_d4605173ce33fb7d7379.jpg', '2025-01-05 19:29:47', '2025-01-05 19:29:47', 0),
(258, 94, 'uploads/services/1736105724_bdc43ac99fa24e059d74.jpg', 'uploads/services/thumbnails/thumb_1736105724_bdc43ac99fa24e059d74.jpg', '2025-01-05 19:37:02', '2025-01-06 20:55:20', 1),
(259, 94, 'uploads/services/1736105724_809ddd949711c40f5754.jpg', 'uploads/services/thumbnails/thumb_1736105724_809ddd949711c40f5754.jpg', '2025-01-05 19:37:02', '2025-01-05 19:37:02', 0),
(260, 95, 'uploads/services/1736105863_13abe0f6b14de44d5e1c.jpg', 'uploads/services/thumbnails/thumb_1736105863_13abe0f6b14de44d5e1c.jpg', '2025-01-05 19:39:35', '2025-01-06 20:55:26', 1),
(261, 95, 'uploads/services/1736105863_0e905955f8882c330c24.jpg', 'uploads/services/thumbnails/thumb_1736105863_0e905955f8882c330c24.jpg', '2025-01-05 19:39:35', '2025-01-05 19:39:35', 0),
(262, 96, 'uploads/services/1736106432_b8738d07eed2158b1462.jpg', 'uploads/services/thumbnails/thumb_1736106432_b8738d07eed2158b1462.jpg', '2025-01-05 19:59:11', '2025-01-06 20:55:31', 1),
(263, 96, 'uploads/services/1736106432_97f04892635c72142ff3.jpg', 'uploads/services/thumbnails/thumb_1736106432_97f04892635c72142ff3.jpg', '2025-01-05 19:59:11', '2025-01-05 19:59:11', 0),
(265, 98, 'uploads/services/1736193052_d2a7fd8f72f18a766c53.jpg', 'uploads/services/thumbnails/thumb_1736193052_d2a7fd8f72f18a766c53.jpg', '2025-01-06 20:32:11', '2025-01-06 20:55:39', 1),
(266, 98, 'uploads/services/1736193052_6a5ab419e52b2c88c0b7.jpg', 'uploads/services/thumbnails/thumb_1736193052_6a5ab419e52b2c88c0b7.jpg', '2025-01-06 20:32:11', '2025-01-06 20:32:11', 0),
(267, 99, 'uploads/services/1736196056_1bad840c756121d89f41.jpg', 'uploads/services/thumbnails/thumb_1736196056_1bad840c756121d89f41.jpg', '2025-01-06 20:47:43', '2025-01-06 20:55:44', 1),
(268, 99, 'uploads/services/1736196056_7f58942888f4fad7e349.jpg', 'uploads/services/thumbnails/thumb_1736196056_7f58942888f4fad7e349.jpg', '2025-01-06 20:47:43', '2025-01-06 20:47:43', 0),
(269, 99, 'uploads/services/1736196056_3ea18e7d22b3914e8843.jpg', 'uploads/services/thumbnails/thumb_1736196056_3ea18e7d22b3914e8843.jpg', '2025-01-06 20:47:43', '2025-01-06 20:47:43', 0),
(272, 101, 'uploads/services/1765736637_33ef78ebf42d7c504ab1.jpg', 'uploads/services/thumbnails/thumb_1765736637_33ef78ebf42d7c504ab1.jpg', '2025-12-14 18:24:48', '2025-12-14 18:24:48', 1),
(273, 101, 'uploads/services/1765736637_f699faf761fad33a63d9.jpg', 'uploads/services/thumbnails/thumb_1765736637_f699faf761fad33a63d9.jpg', '2025-12-14 18:24:48', '2025-12-14 18:24:48', 0),
(274, 101, 'uploads/services/1765736647_15eb6be0a182de1785fc.jpg', 'uploads/services/thumbnails/thumb_1765736647_15eb6be0a182de1785fc.jpg', '2025-12-14 18:24:48', '2025-12-14 18:24:48', 0),
(275, 101, 'uploads/services/1765736647_4059071eb51eee66ba8b.jpg', 'uploads/services/thumbnails/thumb_1765736647_4059071eb51eee66ba8b.jpg', '2025-12-14 18:24:48', '2025-12-14 18:24:48', 0),
(276, 102, 'uploads/services/1765737995_b3ee47a8b8fa16b61a7e.jpg', 'uploads/services/thumbnails/thumb_1765737995_b3ee47a8b8fa16b61a7e.jpg', '2025-12-14 20:30:09', '2025-12-14 20:30:09', 1),
(277, 102, 'uploads/services/1765737995_7d1c20e58b2934aab686.jpg', 'uploads/services/thumbnails/thumb_1765737995_7d1c20e58b2934aab686.jpg', '2025-12-14 20:30:09', '2025-12-14 20:30:09', 0);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('customer','vendor') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `username`, `email`, `password`, `role`) VALUES
(3, 'Mark Pearson', 'Vendor', 'vendor@vendor.com', '$2y$10$OKp.uCxz/4jW3FbMxjpiEesYTJkx4pHBoSlGsZQ3CEstqgHpJU/DK', 'vendor'),
(4, 'customer', 'Customer', 'customer@customer.com', '$2y$10$FeGW7V5CBkb9suZ2jQdqEevA/2y0iakVRfkVDY3BGEQ42GkzXvy0q', 'customer');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `booking_items`
--
ALTER TABLE `booking_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Indexes for table `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `service_id` (`service_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `chat_room_id` (`chat_room_id`);

--
-- Indexes for table `chat_rooms`
--
ALTER TABLE `chat_rooms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vendor_id` (`vendor_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `duration_pricing`
--
ALTER TABLE `duration_pricing`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_id` (`service_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `optional_extras`
--
ALTER TABLE `optional_extras`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_id` (`service_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_id` (`service_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `services_cancellation_policies`
--
ALTER TABLE `services_cancellation_policies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_id` (`service_id`);

--
-- Indexes for table `services_corporate_event_pricing`
--
ALTER TABLE `services_corporate_event_pricing`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_id` (`service_id`);

--
-- Indexes for table `services_custom_duration_pricing`
--
ALTER TABLE `services_custom_duration_pricing`
  ADD PRIMARY KEY (`id`),
  ADD KEY `private_event_pricing_id` (`private_event_pricing_id`);

--
-- Indexes for table `services_event_types`
--
ALTER TABLE `services_event_types`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_id` (`service_id`);

--
-- Indexes for table `services_guest_based_pricing`
--
ALTER TABLE `services_guest_based_pricing`
  ADD PRIMARY KEY (`id`),
  ADD KEY `private_event_pricing_id` (`private_event_pricing_id`);

--
-- Indexes for table `services_locations`
--
ALTER TABLE `services_locations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_id` (`service_id`);

--
-- Indexes for table `services_optional_extras`
--
ALTER TABLE `services_optional_extras`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_id` (`service_id`);

--
-- Indexes for table `services_private_event_pricing`
--
ALTER TABLE `services_private_event_pricing`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_id` (`service_id`);

--
-- Indexes for table `services_public_event_pricing`
--
ALTER TABLE `services_public_event_pricing`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_id` (`service_id`);

--
-- Indexes for table `services_service_tags`
--
ALTER TABLE `services_service_tags`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_id` (`service_id`),
  ADD KEY `tag_id` (`tag_id`);

--
-- Indexes for table `services_tags`
--
ALTER TABLE `services_tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `services_tiered_packages_pricing`
--
ALTER TABLE `services_tiered_packages_pricing`
  ADD PRIMARY KEY (`id`),
  ADD KEY `private_event_pricing_id` (`private_event_pricing_id`);

--
-- Indexes for table `service_images`
--
ALTER TABLE `service_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_id` (`service_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

--
-- AUTO_INCREMENT for table `booking_items`
--
ALTER TABLE `booking_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;

--
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=314;

--
-- AUTO_INCREMENT for table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `chat_rooms`
--
ALTER TABLE `chat_rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `duration_pricing`
--
ALTER TABLE `duration_pricing`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `optional_extras`
--
ALTER TABLE `optional_extras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;

--
-- AUTO_INCREMENT for table `services_cancellation_policies`
--
ALTER TABLE `services_cancellation_policies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `services_corporate_event_pricing`
--
ALTER TABLE `services_corporate_event_pricing`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `services_custom_duration_pricing`
--
ALTER TABLE `services_custom_duration_pricing`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=157;

--
-- AUTO_INCREMENT for table `services_event_types`
--
ALTER TABLE `services_event_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- AUTO_INCREMENT for table `services_guest_based_pricing`
--
ALTER TABLE `services_guest_based_pricing`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `services_locations`
--
ALTER TABLE `services_locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT for table `services_optional_extras`
--
ALTER TABLE `services_optional_extras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `services_private_event_pricing`
--
ALTER TABLE `services_private_event_pricing`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=85;

--
-- AUTO_INCREMENT for table `services_public_event_pricing`
--
ALTER TABLE `services_public_event_pricing`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `services_service_tags`
--
ALTER TABLE `services_service_tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=229;

--
-- AUTO_INCREMENT for table `services_tags`
--
ALTER TABLE `services_tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=116;

--
-- AUTO_INCREMENT for table `services_tiered_packages_pricing`
--
ALTER TABLE `services_tiered_packages_pricing`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `service_images`
--
ALTER TABLE `service_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=278;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`);

--
-- Constraints for table `booking_items`
--
ALTER TABLE `booking_items`
  ADD CONSTRAINT `booking_items_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`),
  ADD CONSTRAINT `booking_items_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Constraints for table `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `carts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `carts_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`),
  ADD CONSTRAINT `carts_ibfk_3` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`);

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD CONSTRAINT `chat_messages_ibfk_1` FOREIGN KEY (`chat_room_id`) REFERENCES `chat_rooms` (`id`);

--
-- Constraints for table `chat_rooms`
--
ALTER TABLE `chat_rooms`
  ADD CONSTRAINT `chat_rooms_ibfk_1` FOREIGN KEY (`vendor_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `chat_rooms_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `duration_pricing`
--
ALTER TABLE `duration_pricing`
  ADD CONSTRAINT `duration_pricing_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `optional_extras`
--
ALTER TABLE `optional_extras`
  ADD CONSTRAINT `optional_extras_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `services_cancellation_policies`
--
ALTER TABLE `services_cancellation_policies`
  ADD CONSTRAINT `services_cancellation_policies_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `services_corporate_event_pricing`
--
ALTER TABLE `services_corporate_event_pricing`
  ADD CONSTRAINT `services_corporate_event_pricing_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `services_custom_duration_pricing`
--
ALTER TABLE `services_custom_duration_pricing`
  ADD CONSTRAINT `services_custom_duration_pricing_ibfk_1` FOREIGN KEY (`private_event_pricing_id`) REFERENCES `services_private_event_pricing` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `services_event_types`
--
ALTER TABLE `services_event_types`
  ADD CONSTRAINT `services_event_types_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `services_guest_based_pricing`
--
ALTER TABLE `services_guest_based_pricing`
  ADD CONSTRAINT `services_guest_based_pricing_ibfk_1` FOREIGN KEY (`private_event_pricing_id`) REFERENCES `services_private_event_pricing` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `services_locations`
--
ALTER TABLE `services_locations`
  ADD CONSTRAINT `services_locations_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `services_optional_extras`
--
ALTER TABLE `services_optional_extras`
  ADD CONSTRAINT `services_optional_extras_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `services_private_event_pricing`
--
ALTER TABLE `services_private_event_pricing`
  ADD CONSTRAINT `services_private_event_pricing_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `services_public_event_pricing`
--
ALTER TABLE `services_public_event_pricing`
  ADD CONSTRAINT `services_public_event_pricing_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `services_service_tags`
--
ALTER TABLE `services_service_tags`
  ADD CONSTRAINT `services_service_tags_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `services_service_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `services_tags` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `services_tiered_packages_pricing`
--
ALTER TABLE `services_tiered_packages_pricing`
  ADD CONSTRAINT `services_tiered_packages_pricing_ibfk_1` FOREIGN KEY (`private_event_pricing_id`) REFERENCES `services_private_event_pricing` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `service_images`
--
ALTER TABLE `service_images`
  ADD CONSTRAINT `service_images_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
