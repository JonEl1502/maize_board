-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 16, 2025 at 05:21 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `maize_market`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `created_at`) VALUES
(1, 'Grains', '2025-03-22 11:13:55'),
(2, 'Vegetables', '2025-03-22 11:13:55'),
(3, 'Fruits', '2025-03-22 11:13:55'),
(4, 'Dairy Products', '2025-03-22 11:13:55'),
(5, 'Livestock', '2025-03-22 11:13:55'),
(6, 'Poultry', '2025-03-22 11:13:55'),
(7, 'Legumes', '2025-03-22 11:13:55'),
(8, 'Root Crops', '2025-03-22 11:13:55'),
(9, 'Herbs & Spices', '2025-03-22 11:13:55'),
(10, 'Oilseeds', '2025-03-22 11:13:55');

-- --------------------------------------------------------

--
-- Table structure for table `counties`
--

CREATE TABLE `counties` (
  `id` int(11) NOT NULL,
  `name` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `counties`
--

INSERT INTO `counties` (`id`, `name`) VALUES
(1, 'Mombasa'),
(2, 'Kwale'),
(3, 'Kilifi'),
(4, 'Tana River'),
(5, 'Lamu'),
(6, 'Taita-Taveta'),
(7, 'Garissa'),
(8, 'Wajir'),
(9, 'Mandera'),
(10, 'Marsabit'),
(11, 'Isiolo'),
(12, 'Meru'),
(13, 'Tharaka-Nithi'),
(14, 'Embu'),
(15, 'Kitui'),
(16, 'Machakos'),
(17, 'Makueni'),
(18, 'Nyandarua'),
(19, 'Nyeri'),
(20, 'Kirinyaga'),
(21, 'Murang\'a'),
(22, 'Kiambu'),
(23, 'Turkana'),
(24, 'West Pokot'),
(25, 'Samburu'),
(26, 'Trans Nzoia'),
(27, 'Uasin Gishu'),
(28, 'Elgeyo-Marakwet'),
(29, 'Nandi'),
(30, 'Baringo'),
(31, 'Laikipia'),
(32, 'Nakuru'),
(33, 'Narok'),
(34, 'Kajiado'),
(35, 'Kericho'),
(36, 'Bomet'),
(37, 'Kakamega'),
(38, 'Vihiga'),
(39, 'Bungoma'),
(40, 'Busia'),
(41, 'Siaya'),
(42, 'Kisumu'),
(43, 'Homa Bay'),
(44, 'Migori'),
(45, 'Kisii'),
(46, 'Nyamira'),
(47, 'Nairobi City');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `category_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `image_url`, `created_at`, `updated_at`, `category_id`) VALUES
(1, 'Maize', '', '', '2025-03-20 14:40:38', '2025-03-22 11:14:16', 1),
(2, 'Rice', 'Kenyan Rice', '', '2025-03-20 14:40:38', '2025-03-22 11:14:20', 1);

-- --------------------------------------------------------

--
-- Table structure for table `product_listings`
--

CREATE TABLE `product_listings` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `buyer_id` int(11) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `price_per_quantity` decimal(10,2) NOT NULL,
  `product_image_url` varchar(255) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `quantity_type_id` int(11) NOT NULL,
  `status_id` int(11) DEFAULT 1,
  `mpesa_code` varchar(50) DEFAULT '''''',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_listings`
--

INSERT INTO `product_listings` (`id`, `product_id`, `seller_id`, `buyer_id`, `price`, `price_per_quantity`, `product_image_url`, `quantity`, `quantity_type_id`, `status_id`, `mpesa_code`, `created_at`, `updated_at`) VALUES
(1, 2, 3, NULL, 72029.00, 323.00, '', 221, 3, 1, '', '2025-03-21 08:26:41', '2025-04-16 06:36:01'),
(2, 1, 3, NULL, 510600.00, 2300.00, '', 203, 4, 1, '', '2025-03-21 08:36:37', '2025-04-16 07:21:18'),
(3, 2, 1, NULL, 2250.00, 150.00, '', 15, 6, 1, '', '2025-03-21 08:56:11', '2025-04-02 10:22:53'),
(5, 1, 9, 11, 62970.00, 2099.00, NULL, 30, 3, 3, '\'\'', '2025-04-04 06:12:48', '2025-04-16 07:18:26'),
(6, 2, 9, NULL, 14000.00, 200.00, NULL, 69, 6, 1, '\'\'', '2025-04-16 05:03:22', '2025-04-16 06:36:01');

-- --------------------------------------------------------

--
-- Table structure for table `purchases`
--

CREATE TABLE `purchases` (
  `id` int(11) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `listing_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `mpesa_code` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchases`
--

INSERT INTO `purchases` (`id`, `buyer_id`, `seller_id`, `listing_id`, `quantity`, `total_price`, `mpesa_code`, `created_at`, `updated_at`) VALUES
(1, 8, 3, 2, 2, 4600.00, 'sasad', '2025-03-25 09:24:45', '2025-03-25 09:24:45'),
(2, 8, 3, 2, 5, 11500.00, 'sasad', '2025-03-25 09:24:58', '2025-03-25 09:24:58'),
(3, 8, 1, 3, 1, 150.00, 'das', '2025-03-25 09:25:26', '2025-03-25 09:25:26'),
(4, 8, 1, 3, 3, 450.00, 'da', '2025-03-25 09:25:44', '2025-03-25 09:25:44'),
(5, 8, 3, 2, 2, 4600.00, '212', '2025-03-25 09:27:22', '2025-03-25 09:27:22'),
(6, 8, 3, 2, 1, 2300.00, 'da', '2025-03-25 09:27:31', '2025-03-25 09:27:31'),
(7, 8, 1, 3, 2, 300.00, 'das', '2025-03-25 09:27:42', '2025-03-25 09:27:42'),
(8, 8, 1, 3, 5, 750.00, 'fvdth', '2025-03-25 11:59:34', '2025-03-25 11:59:34'),
(9, 11, 9, 5, 2, 17998.00, '32532fdv2er', '2025-04-15 19:32:52', '2025-04-15 19:32:52'),
(10, 11, 9, 5, 1, 8999.00, '3JHH32fdv2er', '2025-04-15 19:33:20', '2025-04-15 19:33:20'),
(11, 11, 3, 1, 1, 323.00, 'sasad', '2025-04-16 06:35:36', '2025-04-16 06:35:36'),
(12, 11, 3, 2, 1, 2300.00, 'sasad', '2025-04-16 06:35:36', '2025-04-16 06:35:36'),
(13, 11, 3, 1, 1, 323.00, 'fvdth', '2025-04-16 06:36:01', '2025-04-16 06:36:01'),
(14, 11, 9, 6, 1, 200.00, 'fvdth', '2025-04-16 06:36:01', '2025-04-16 06:36:01'),
(15, 9, 3, 2, 1, 2300.00, 'dad', '2025-04-16 07:21:18', '2025-04-16 07:21:18');

-- --------------------------------------------------------

--
-- Table structure for table `quantity_types`
--

CREATE TABLE `quantity_types` (
  `id` int(11) NOT NULL,
  `unit_name` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quantity_types`
--

INSERT INTO `quantity_types` (`id`, `unit_name`, `created_at`, `updated_at`) VALUES
(1, 'Gram', '2025-03-20 17:05:57', '2025-03-20 17:05:57'),
(2, 'Kilogram', '2025-03-20 17:05:57', '2025-03-20 17:05:57'),
(3, 'Bag', '2025-03-20 17:05:57', '2025-03-20 17:05:57'),
(4, 'Tonne', '2025-03-20 17:05:57', '2025-03-20 17:05:57'),
(5, 'Liter', '2025-03-20 17:05:57', '2025-03-20 17:05:57'),
(6, 'Dozen', '2025-03-20 17:05:57', '2025-03-20 17:05:57');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`) VALUES
(1, 'Admin'),
(5, 'Customer'),
(2, 'Farmer'),
(4, 'Retailer'),
(3, 'Wholesaler');

-- --------------------------------------------------------

--
-- Table structure for table `statuses`
--

CREATE TABLE `statuses` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `statuses`
--

INSERT INTO `statuses` (`id`, `name`, `created_at`) VALUES
(1, 'Listed', '2025-03-22 12:47:50'),
(2, 'Spoken For', '2025-03-22 12:47:50'),
(3, 'Paid For', '2025-03-22 12:47:50'),
(4, 'Sold', '2025-03-22 12:47:50');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` varchar(50) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `role_id` int(11) NOT NULL DEFAULT 1,
  `address` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `business_name` varchar(255) DEFAULT NULL,
  `farm_name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `role_id`, `address`, `created_at`, `business_name`, `farm_name`) VALUES
(1, 'John Kabiu Mwaura', 'johnkabiumwaura@gmail.com', '$2y$10$yBkZhjHKlXAXZ5NFiHInfeaZso7WZw0TGFzSK3vR4bX9Rf/jDEwdy', '0714919776', 2, '837', '2025-03-19 02:59:06', NULL, NULL),
(3, 'James Kvali', 'kvali@gmail.com', '$2y$10$loX/SHLZy/8.QPC9NZ0IWOtn4226jbHLcGZbCi7PmzBZue.YfyCRW', '0714999999', 2, '786 gh', '2025-01-23 09:00:07', NULL, NULL),
(8, 'Samuel L Jackson', 'samljack@gmail.com', '$2y$10$A8SRbn2MtcGRbR8kZjUiTOzkrEp/vPtofXjokyR92yos7D2orJj7W', '0733981200', 3, '823 Kikuyu', '2025-03-21 10:57:26', NULL, NULL),
(9, 'Allan Ongeri', 'allan@gmail.com', '$2y$10$efHQKA2JxTa9/QeGPRsHQOueGvltdyOyiOa7laSnATnwx0oPXkwpe', '0712345678', 2, '45 Jew Street, Munich', '2025-04-04 05:07:06', '', 'Duetchland'),
(10, 'Adolf Hitler', 'adolfhilter@gmail.com', '$2y$10$L1kMgy9RA8nxhVzf6Tkx6.noOy9JVHk.iz5uGd/xZWhyC4iXfBzI6', '0712345678', 3, '45 Jew Street, Munich', '2025-04-04 05:15:32', 'Legitimate Wholesalers', ''),
(11, 'Adolf D Hitler', 'adolfhilter1@gmail.com', '$2y$10$jlJx2DIXan8kVKderDS6C.9boLaq3s1I0WnheAnjjqIST40oTmGLm', '0714919776', 5, '45 Jew Street, Munich', '2025-04-15 17:24:55', '', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `counties`
--
ALTER TABLE `counties`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_category` (`category_id`);

--
-- Indexes for table `product_listings`
--
ALTER TABLE `product_listings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`seller_id`),
  ADD KEY `fk_quantity_type` (`quantity_type_id`),
  ADD KEY `fk_status` (`status_id`),
  ADD KEY `fk_buyer` (`buyer_id`);

--
-- Indexes for table `purchases`
--
ALTER TABLE `purchases`
  ADD PRIMARY KEY (`id`),
  ADD KEY `buyer_id` (`buyer_id`),
  ADD KEY `seller_id` (`seller_id`),
  ADD KEY `fk_purchases_listing` (`listing_id`);

--
-- Indexes for table `quantity_types`
--
ALTER TABLE `quantity_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unit_name` (`unit_name`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `statuses`
--
ALTER TABLE `statuses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_users_roles` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `product_listings`
--
ALTER TABLE `product_listings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `purchases`
--
ALTER TABLE `purchases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `quantity_types`
--
ALTER TABLE `quantity_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `statuses`
--
ALTER TABLE `statuses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_listings`
--
ALTER TABLE `product_listings`
  ADD CONSTRAINT `fk_buyer` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_quantity_type` FOREIGN KEY (`quantity_type_id`) REFERENCES `quantity_types` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_status` FOREIGN KEY (`status_id`) REFERENCES `statuses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `product_listings_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_listings_ibfk_2` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `purchases`
--
ALTER TABLE `purchases`
  ADD CONSTRAINT `fk_purchases_listing` FOREIGN KEY (`listing_id`) REFERENCES `product_listings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchases_ibfk_1` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchases_ibfk_2` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_roles` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
