-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: May 29, 2024 at 05:40 AM
-- Server version: 5.7.39
-- PHP Version: 8.2.0

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
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `vendor_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text,
  `date` date DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text,
  `image` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `vendor_id`, `title`, `description`, `image`, `price`) VALUES
(2, 3, 'Sweetie Sweet Cart', 'Indulge your sweet tooth at the Sweetie Cart Candy Stall, a whimsical haven for candy lovers of all ages! Our charming cart is overflowing with a kaleidoscope of colorful candies, chocolates, and other delectable treats.\r\n\r\nDiscover a delightful assortment of classic favorites and nostalgic candies that will transport you back to childhood. From gummy bears and jelly beans to lollipops and hard candies, we have something to satisfy every craving.\r\n\r\nWe also offer an array of gourmet chocolates, fudge, and other artisanal sweets for those seeking a more sophisticated indulgence. Our selection is constantly changing, so there\'s always something new to discover.\r\n\r\nWhether you\'re looking for a special treat for yourself or a gift for someone special, the Sweetie Cart Candy Stall is the perfect place to find your sugar fix.', '1716936655_2a9474e339e1b2141db3.jpg', '150.00'),
(3, 3, 'Sweetie Sweet Cart', 'Indulge your sweet tooth at the Sweetie Cart Candy Stall, a whimsical haven for candy lovers of all ages! Our charming cart is overflowing with a kaleidoscope of colorful candies, chocolates, and other delectable treats.\r\n\r\nDiscover a delightful assortment of classic favorites and nostalgic candies that will transport you back to childhood. From gummy bears and jelly beans to lollipops and hard candies, we have something to satisfy every craving.\r\n\r\nWe also offer an array of gourmet chocolates, fudge, and other artisanal sweets for those seeking a more sophisticated indulgence. Our selection is constantly changing, so there\'s always something new to discover.\r\n\r\nWhether you\'re looking for a special treat for yourself or a gift for someone special, the Sweetie Cart Candy Stall is the perfect place to find your sugar fix.', '1716936809_ea5338b2e4ba5823d2f9.jpg', '150.00'),
(4, 3, 'Mr Beatys Burgers', 'BurgerBurgerBurgerBurgerBurgerBurgerBurgerBurgerBurgerBurgerBurgerBurgerBurgerBurgerBurgerBurgerBurgerBurgerBurgerBurgerBurgerBurgerBurgerBurgerBurgerBurgerBurgerBurgerBurgerBurgerBurgerBurgerBurgerBurger', '1716937372_8e6a7964ed534149d3cb.jpeg', '240.00'),
(5, 3, 'Dinky Donuts', 'Delight in the irresistible aroma and melt-in-your-mouth goodness of Dinky Donuts! Our adorable kiosk is a haven for donut aficionados, serving up a tantalizing array of handcrafted donuts made fresh daily.\r\n\r\nDiscover a whimsical assortment of flavors that will tickle your taste buds. From classic glazed and cinnamon sugar to creative concoctions like maple bacon and cookies and cream, our donuts are sure to satisfy any sweet tooth.\r\n\r\nWe also offer a variety of vegan and gluten-free options, so everyone can enjoy the simple pleasure of a delicious donut. Our donuts are made with high-quality ingredients and a whole lot of love, resulting in a truly delightful experience.\r\n\r\nWhether you\'re grabbing a quick breakfast on the go or indulging in a sweet treat, Dinky Donuts is the perfect place to get your donut fix.', '1716937744_df87fb10763e1b292fb8.jpeg', '90.00');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `username`, `email`, `password`, `role`) VALUES
(3, 'Mark Pearson', 'm.pearson1', 'markyj@zoho.com', '$2y$10$OKp.uCxz/4jW3FbMxjpiEesYTJkx4pHBoSlGsZQ3CEstqgHpJU/DK', 'vendor'),
(4, 'mark90', 'mark90', 'markjpearson@me.com', '$2y$10$FeGW7V5CBkb9suZ2jQdqEevA/2y0iakVRfkVDY3BGEQ42GkzXvy0q', 'customer');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vendor_id` (`vendor_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vendor_id` (`vendor_id`);

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
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`vendor_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `services_ibfk_1` FOREIGN KEY (`vendor_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
